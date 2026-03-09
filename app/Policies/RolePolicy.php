<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view roles');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('view roles');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create roles');
    }

    public function update(User $user, Role $role): bool
    {
        if (!$user->hasPermissionTo('edit roles')) {
            return false;
        }

        // Cannot edit super-admin role name
        if ($role->name === 'super-admin') {
            return false;
        }

        return true;
    }

    public function delete(User $user, Role $role): bool
    {
        if (!$user->hasPermissionTo('delete roles')) {
            return false;
        }

        // Cannot delete super-admin role
        if ($role->name === 'super-admin') {
            return false;
        }

        return true;
    }
}
