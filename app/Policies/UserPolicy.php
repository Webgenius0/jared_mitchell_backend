<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('view users');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create users');
    }

    public function update(User $user, User $model): bool
    {
        if (!$user->hasPermissionTo('edit users')) {
            return false;
        }

        // Cannot edit super-admin unless you ARE super-admin
        if ($model->hasRole('super-admin') && !$user->hasRole('super-admin')) {
            return false;
        }

        return true;
    }

    public function delete(User $user, User $model): bool
    {
        if (!$user->hasPermissionTo('delete users')) {
            return false;
        }

        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot delete a super-admin
        if ($model->hasRole('super-admin')) {
            return false;
        }

        return true;
    }
}
