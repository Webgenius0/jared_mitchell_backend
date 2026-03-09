<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleService
{
    public function createRole(array $data): Role
    {
        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => 'admin',
        ]);

        if (!empty($data['permissions'])) {
            $this->syncPermissions($role, $data['permissions']);
        }

        return $role->load('permissions');
    }

    public function updateRole(Role $role, array $data): Role
    {
        $role->update(['name' => $data['name']]);

        if (array_key_exists('permissions', $data)) {
            $this->syncPermissions($role, $data['permissions'] ?? []);
        }

        return $role->load('permissions');
    }

    public function deleteRole(Role $role): bool
    {
        return $role->delete();
    }

    public function syncPermissions(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function getPermissionsGrouped(): Collection
    {
        $permissions = Permission::where('guard_name', 'admin')->orderBy('name')->get();

        return $permissions->groupBy(function ($permission) {
            $words = explode(' ', $permission->name);
            return ucfirst(end($words));
        });
    }
}
