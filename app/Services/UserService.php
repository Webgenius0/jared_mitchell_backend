<?php

namespace App\Services;

use App\Helpers\FileHandle;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function createUser(array $data): User
    {
        $user = User::create([
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'password'          => Hash::make($data['password']),
            'status'            => $data['status'] ?? 'active',
            'email_verified_at' => now(),
        ]);

        // Create profile
        $avatarPath = null;
        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            $avatarPath = $this->handleAvatarUpload($data['avatar']);
        }

        $user->profile()->create([
            'name'     => $data['name'],
            'username' => FileHandle::generateUsername($data['name']),
            'slug'     => FileHandle::generateSlug($data['name']),
            'avatar'   => $avatarPath,
        ]);

        // Assign roles
        if (!empty($data['roles'])) {
            $this->syncUserRoles($user, $data['roles']);
        }

        return $user->load('profile', 'roles');
    }

    public function updateUser(User $user, array $data): User
    {
        $userData = [
            'email'  => $data['email'],
            'phone'  => $data['phone'] ?? $user->phone,
            'status' => $data['status'] ?? $user->status,
        ];

        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $user->update($userData);

        // Update profile
        $profileData = ['name' => $data['name']];

        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            $oldAvatar = $user->profile?->avatar;
            $profileData['avatar'] = $this->handleAvatarUpload($data['avatar'], $oldAvatar);
        }

        if ($user->profile) {
            $user->profile->update($profileData);
        } else {
            $profileData['username'] = FileHandle::generateUsername($data['name']);
            $profileData['slug']     = FileHandle::generateSlug($data['name']);
            $user->profile()->create($profileData);
        }

        // Sync roles
        if (array_key_exists('roles', $data)) {
            $this->syncUserRoles($user, $data['roles'] ?? []);
        }

        return $user->load('profile', 'roles');
    }

    public function deleteUser(User $user): bool
    {
        // Delete avatar file if exists
        if ($user->profile?->avatar) {
            FileHandle::fileDelete($user->profile->avatar);
        }

        return $user->delete();
    }

    public function toggleStatus(User $user): User
    {
        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        return $user;
    }

    public function handleAvatarUpload(?UploadedFile $file, ?string $old = null): ?string
    {
        if (!$file) {
            return null;
        }

        // Delete old avatar
        if ($old) {
            FileHandle::fileDelete($old);
        }

        return FileHandle::fileUpload($file, 'avatars');
    }

    public function syncUserRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }
}
