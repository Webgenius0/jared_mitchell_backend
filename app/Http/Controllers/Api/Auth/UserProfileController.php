<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\FileHandle;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\OrderReview;
use App\Models\SellerEarnings;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserProfileController extends Controller
{
    use ApiResponse;

    /**
     * Get User Profile
     */
    public function profile()
    {
        try {
            $user = auth('api')->user()->load('profile');

            if (!$user) {
                return $this->error(
                    null,
                    'User not found',
                    404
                );
            }

            return $this->success(
                'User profile retrieved successfully',
                new UserResource($user)
            );
        } catch (Exception $e) {
            Log::error('Get profile error: ' . $e->getMessage());
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to retrieve profile',
                500
            );
        }
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        try {

            $user = auth('api')->user()->load('profile');

            if (!$user) {
                return $this->error(
                    null,
                    'User not found',
                    404
                );
            }

            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:100',
                'biography' => 'nullable|string|max:2500',
                'tagline'  => 'nullable|string|max:255',
                'address'  => 'nullable|string|max:255',
                'phone' => [
                    'nullable',
                    'string',
                    'max:150',
                    Rule::unique('users', 'phone')->ignore($user->id),
                ],
                'username' => [
                    'nullable',
                    'string',
                    'min:3',
                    'max:50',
                    'regex:/^@?[a-zA-Z0-9_\s-]+$/',
                    Rule::unique('profiles', 'username')->ignore(optional($user->profile)->id),
                ],
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed',
                    422
                );
            }

            $validated = $validator->validated();

            // Update User Table
            if (isset($validated['phone'])) {
                $user->update([
                    'phone' => $validated['phone']
                ]);
            }

            // Prepare Profile Data
            $profileData = [];

            if (isset($validated['name'])) {
                $profileData['name'] = $validated['name'];
            }

            if (isset($validated['biography'])) {
                $profileData['biography'] = $validated['biography'];
            }

            if (isset($validated['tagline'])) {
                $profileData['tagline'] = $validated['tagline'];
            }

            if (isset($validated['address'])) {
                $profileData['address'] = $validated['address'];
            }

            if (isset($validated['username'])) {

                $profileData['username'] = $validated['username'];
                $profileData['slug'] = Helper::generateSlug($validated['username']);
            }


            // Create or Update Profile
            if (!$user->profile) {
                if (!isset($profileData['username'])) {
                    $profileData['username'] = Helper::generateUsername($validated['name'] ?? 'user');
                    $profileData['slug'] = Helper::generateSlug($profileData['username']);
                }
                $user->profile()->create($profileData);
            } else {
                if (!empty($profileData)) {
                    $user->profile->update($profileData);
                }
            }


            // Reload User
            $user->refresh()->load('profile');

            return $this->success(
                'Profile updated successfully',
                new UserResource($user)
            );
        } catch (Exception $e) {

            Log::error('Update profile error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update profile',
                500
            );
        }
    }

    /**
     * Update User Avatar
     */
    public function updateAvatar(Request $request)
    {
        try {
            $user = auth('api')->user()->load('profile');

            if (!$user) {
                return $this->error(
                    null,
                    'User not found',
                    404
                );
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed',
                    422
                );
            }

            // Ensure profile exists
            if (!$user->profile) {
                return $this->error(
                    null,
                    'Profile not found. Please update your profile first.',
                    404
                );
            }

            // Delete old avatar if exists
            if (!empty($user->profile->avatar) && file_exists(public_path($user->profile->avatar))) {
                FileHandle::fileDelete(public_path($user->profile->avatar));
            }

            // Upload new avatar
            $avatarPath = FileHandle::fileUpload($request->file('avatar'), 'user/avatar');

            // Update profile avatar
            $user->profile->update(['avatar' => $avatarPath]);

            // Reload user with profile
            $user->refresh()->load('profile');

            return $this->success(
                'Avatar updated successfully',
                new UserResource($user)
            );
        } catch (Exception $e) {
            Log::error('Update avatar error: ' . $e->getMessage());
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update avatar',
                500
            );
        }
    }

    /**
     * Delete User Profile
     * @method DELETE
     * @route /api/v1/delete-profile
     * @middleware auth:api
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        try {
            $user = auth('api')->user()->load('profile');

            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            // Confirm password
            if (!Hash::check($request->password, $user->password)) {
                return $this->error(null, 'Invalid password', 403);
            }

            // Delete avatar from storage
            if ($user->profile?->avatar) {
                FileHandle::fileDelete($user->profile->avatar);
            }

            // Logout user
            auth('api')->logout();

            // Permanently delete user (profile auto deleted)
            $user->forceDelete();

            return $this->success('Account deleted successfully');
        } catch (Exception $e) {
            Log::error('Delete profile error: ' . $e->getMessage());
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to delete account',
                500
            );
        }
    }


    /**
     * Change User Password
     * @method POST
     * @route /api/v1/change-password
     * @middleware auth:api
     */
    public function changePassword(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error(
                    null,
                    'User not found',
                    404
                );
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'old_password'     => 'required|string',
                'new_password'     => 'required|string|min:6|max:50',
                'confirm_password' => 'required|string|same:new_password',
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed',
                    422
                );
            }

            // Check if old password is correct
            if (!Hash::check($request->old_password, $user->password)) {
                return $this->error(
                    null,
                    'Old password does not match',
                    400
                );
            }

            // Update with new password
            $user->password = Hash::make($request->new_password);
            $user->save();

            return $this->success(
                'Password changed successfully',
                null
            );
        } catch (Exception $e) {
            Log::error('Change password error: ' . $e->getMessage());
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to change password',
                500
            );
        }
    }
}
