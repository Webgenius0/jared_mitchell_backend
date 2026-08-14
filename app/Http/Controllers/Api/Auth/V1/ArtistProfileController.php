<?php

namespace App\Http\Controllers\Api\Auth\V1;

use App\Helpers\FileHandle;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ArtistProfileController extends Controller
{
    use ApiResponse;

    /**
     * Store (Create/Initialize) Artist Profile
     */
    public function store(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            if ($user->profile) {
                return $this->update($request);
            }

            $validator = Validator::make($request->all(), [
                'name'               => 'required|string|max:100',
                'username'           => [
                    'required',
                    'string',
                    'min:3',
                    'max:50',
                    'regex:/^@?[a-zA-Z0-9_\s-]+$/',
                    'unique:profiles,username',
                ],
                'email'              => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
                'phone'              => [
                    'nullable',
                    'string',
                    'max:150',
                    Rule::unique('users', 'phone')->ignore($user->id),
                ],
                'address'            => 'nullable|string|max:255',
                'biography'          => 'nullable|string|max:2500',
                'artist_category_id' => 'nullable|exists:artist_categories,id',
                'website_link'       => 'nullable|string|max:255',
                'youtube'            => 'nullable|string|max:255',
                'facebook'           => 'nullable|string|max:255',
                'instagram'          => 'nullable|string|max:255',
                'avatar'             => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed',
                    422
                );
            }

            $validated = $validator->validated();

            DB::beginTransaction();

            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = FileHandle::fileUpload($request->file('avatar'), 'user/avatar');
            }

            $user->update([
                'email'              => strtolower($validated['email']),
                'phone'              => $validated['phone'] ?? $user->phone,
                'artist_category_id' => $validated['artist_category_id'] ?? $user->artist_category_id,
            ]);

            $socialLinks = [
                'youtube'   => $validated['youtube'] ?? '',
                'facebook'  => $validated['facebook'] ?? '',
                'instagram' => $validated['instagram'] ?? '',
            ];

            $slug = Helper::generateSlug($validated['username']);

            $user->profile()->create([
                'name'          => $validated['name'],
                'username'      => $validated['username'],
                'slug'          => $slug,
                'biography'     => $validated['biography'] ?? null,
                'address'       => $validated['address'] ?? null,
                'website_link'  => $validated['website_link'] ?? null,
                'social_links'  => $socialLinks,
                'avatar'        => $avatarPath,
            ]);

            DB::commit();

            return $this->success(
                'Artist profile created successfully',
                new UserResource($user->refresh()->load(['profile', 'artistCategory']))
            );

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Artist profile store error: ' . $e->getMessage());
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to create artist profile',
                500
            );
        }
    }

    /**
     * Update Artist Profile
     */
    public function update(Request $request)
    {
        try {
            $user = auth('api')->user()->load('profile');

            if (!$user) {
                return $this->error(null, 'User not found', 404);
            }

            if (!$user->profile) {
                return $this->store($request);
            }

            $validator = Validator::make($request->all(), [
                'name'               => 'nullable|string|max:100',
                'username'           => [
                    'nullable',
                    'string',
                    'min:3',
                    'max:50',
                    'regex:/^@?[a-zA-Z0-9_\s-]+$/',
                    Rule::unique('profiles', 'username')->ignore($user->profile->id),
                ],
                'email'              => [
                    'nullable',
                    'email',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
                'phone'              => [
                    'nullable',
                    'string',
                    'max:150',
                    Rule::unique('users', 'phone')->ignore($user->id),
                ],
                'address'            => 'nullable|string|max:255',
                'biography'          => 'nullable|string|max:2500',
                'artist_category_id' => 'nullable|exists:artist_categories,id',
                'website_link'       => 'nullable|string|max:255',
                'youtube'            => 'nullable|string|max:255',
                'facebook'           => 'nullable|string|max:255',
                'instagram'          => 'nullable|string|max:255',
                'avatar'             => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed',
                    422
                );
            }

            $validated = $validator->validated();

            DB::beginTransaction();

            $avatarPath = $user->profile->avatar;
            if ($request->hasFile('avatar')) {
                if (!empty($user->profile->avatar) && file_exists(public_path($user->profile->avatar))) {
                    FileHandle::fileDelete(public_path($user->profile->avatar));
                }
                $avatarPath = FileHandle::fileUpload($request->file('avatar'), 'user/avatar');
            }

            $userData = [];
            if (isset($validated['email'])) {
                $userData['email'] = strtolower($validated['email']);
            }
            if (isset($validated['phone'])) {
                $userData['phone'] = $validated['phone'];
            }
            if (isset($validated['artist_category_id'])) {
                $userData['artist_category_id'] = $validated['artist_category_id'];
            }

            if (!empty($userData)) {
                $user->update($userData);
            }

            $profileData = [];
            if (isset($validated['name'])) {
                $profileData['name'] = $validated['name'];
            }
            if (isset($validated['username'])) {
                $profileData['username'] = $validated['username'];
                $profileData['slug'] = Helper::generateSlug($validated['username']);
            }
            if (isset($validated['biography'])) {
                $profileData['biography'] = $validated['biography'];
            }
            if (isset($validated['address'])) {
                $profileData['address'] = $validated['address'];
            }
            if (isset($validated['website_link'])) {
                $profileData['website_link'] = $validated['website_link'];
            }

            $existingSocial = $user->profile->social_links ?? [];
            $socialLinks = [
                'youtube'   => $validated['youtube'] ?? ($existingSocial['youtube'] ?? ''),
                'facebook'  => $validated['facebook'] ?? ($existingSocial['facebook'] ?? ''),
                'instagram' => $validated['instagram'] ?? ($existingSocial['instagram'] ?? ''),
            ];
            $profileData['social_links'] = $socialLinks;
            $profileData['avatar'] = $avatarPath;

            $user->profile->update($profileData);

            DB::commit();

            return $this->success(
                'Artist profile updated successfully',
                new UserResource($user->refresh()->load(['profile', 'artistCategory']))
            );

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Artist profile update error: ' . $e->getMessage());
            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update artist profile',
                500
            );
        }
    }
}
