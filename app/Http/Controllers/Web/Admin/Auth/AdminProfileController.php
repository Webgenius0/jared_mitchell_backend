<?php

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    use AdminApiResponse;

    /*
    |--------------------------------------------------------------------------
    | Show Profile Page
    |--------------------------------------------------------------------------
    */
    public function index(): View
    {
        $user    = auth('admin')->user();
        $profile = $user->profile;

        return view('pages.admin.profile.index', compact('user', 'profile'));
    }

    /*
    |--------------------------------------------------------------------------
    | POST  /admin/profile/general
    | Update general info — name, biography, address, phone
    |--------------------------------------------------------------------------
    */
    public function updateGeneral(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'      => ['required', 'string', 'max:100'],
            'biography' => ['nullable', 'string', 'max:500'],
            'address'   => ['nullable', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:20', 'unique:users,phone,' . auth('admin')->id()],
        ], [
            'name.required' => 'Full name is required.',
            'name.max'      => 'Name must not exceed 100 characters.',
            'biography.max' => 'Biography must not exceed 500 characters.',
            'phone.unique'  => 'This phone number is already used by another account.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $user    = auth('admin')->user();
        $profile = $user->profile;

        // ── Update users table ─────────────────────────────────────────────
        $user->update([
            'phone' => $request->phone,
        ]);

        // ── Update profiles table ──────────────────────────────────────────
        $profile->update([
            'name'      => $request->name,
            'biography' => $request->biography,
            'address'   => $request->address,
        ]);

        return $this->success('Profile updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | POST  /admin/profile/avatar
    | Upload / change avatar
    |--------------------------------------------------------------------------
    */

    public function updateAvatar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'avatar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',        // 2 MB
                'dimensions:min_width=50,min_height=50,max_width=2000,max_height=2000',
            ],
        ], [
            'avatar.required'   => 'Please choose an image to upload.',
            'avatar.image'      => 'The file must be an image.',
            'avatar.mimes'      => 'Allowed formats: JPG, JPEG, PNG, WEBP.',
            'avatar.max'        => 'Image size must not exceed 2 MB.',
            'avatar.dimensions' => 'Image dimensions must be between 50×50 and 2000×2000 pixels.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $admin = auth('admin')->user();
        $profile = $admin->profile;

        if (!$profile) {
            $profile = $admin->profile()->create([
                'user_id' => $admin->id,
                'username' => 'admin_' . $admin->id,
                'slug' => 'admin-' . $admin->id,
            ]);
        }

        // ── Delete old avatar ──────────────────────────────────────────────
        if ($profile->avatar) {
            FileHandle::fileDelete($profile->avatar);
        }

        // ── Upload new avatar ──────────────────────────────────────────────
        $path = FileHandle::fileUpload($request->file('avatar'), 'avatars');

        if (! $path) {
            return $this->error('Avatar upload failed. Please try again.', [], 500);
        }

        $profile->update(['avatar' => $path]);

        return $this->success('Avatar updated successfully.', [
            'avatar_url' => asset('storage/' . $path),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE  /admin/profile/avatar
    | Remove avatar → revert to default
    |--------------------------------------------------------------------------
    */

    public function deleteAvatar(): JsonResponse
    {
        $profile = auth('admin')->user()->profile;

        if (! $profile->avatar) {
            return $this->error('No avatar to remove.', [], 422);
        }

        FileHandle::fileDelete($profile->avatar);

        $profile->update(['avatar' => null]);

        return $this->success('Avatar removed successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PATCH  /admin/profile/password
    | Change password
    |--------------------------------------------------------------------------
    */

    public function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password'  => ['required', 'string'],
            'password'          => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
            'password_confirmation' => ['required'],
        ], [
            'current_password.required'  => 'Current password is required.',
            'password.required'          => 'New password is required.',
            'password.min'               => 'New password must be at least 8 characters.',
            'password.confirmed'         => 'Passwords do not match.',
            'password.regex'             => 'Password must include uppercase, lowercase, number, and special character.',
            'password_confirmation.required' => 'Please confirm your new password.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $user = auth('admin')->user();

        // ── Verify current password ────────────────────────────────────────
        if (! Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password is incorrect.', [
                'current_password' => ['Current password is incorrect.'],
            ], 401);
        }

        // ── Prevent same password reuse ────────────────────────────────────
        if (Hash::check($request->password, $user->password)) {
            return $this->error('New password cannot be the same as your current password.', [
                'password' => ['New password cannot be the same as your current password.'],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return $this->success('Password changed successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | POST  /admin/profile/cover
    | Upload / change cover photo
    |--------------------------------------------------------------------------
    */
    public function updateCover(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cover' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',   // 4 MB — cover images are larger
                'dimensions:min_width=800,min_height=200',
            ],
        ], [
            'cover.required'   => 'Please choose an image to upload.',
            'cover.image'      => 'The file must be an image.',
            'cover.mimes'      => 'Allowed formats: JPG, JPEG, PNG, WEBP.',
            'cover.max'        => 'Image size must not exceed 4 MB.',
            'cover.dimensions' => 'Cover image must be at least 800×200 pixels.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $profile = auth('admin')->user()->profile;

        if ($profile->cover) {
            FileHandle::fileDelete($profile->cover);
        }

        $path = FileHandle::fileUpload($request->file('cover'), 'covers');

        if (! $path) {
            return $this->error('Cover upload failed. Please try again.', [], 500);
        }

        $profile->update(['cover' => $path]);

        return $this->success('Cover photo updated successfully.', [
            'cover_url' => asset('storage/' . $path),
        ]);
    }
}
