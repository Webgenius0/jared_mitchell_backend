<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleService
{
    public function createRole(array $data): Role
    {
        DB::beginTransaction();
        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'admin',
        ]);

        if (!empty($data['permissions'])) {
            $this->syncPermissions($role, $data['permissions']);
        }

        DB::commit();
        return $role->load('permissions');
    }

    public function updateRole(Role $role, array $data): Role
    {
        DB::beginTransaction();
        $role->update([
            'name'       => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'admin',
        ]);

        if (array_key_exists('permissions', $data)) {
            $this->syncPermissions($role, $data['permissions'] ?? []);
        }

        DB::commit();
        return $role->load('permissions');
    }

    public function deleteRole(Role $role): bool
    {
        DB::beginTransaction();
        $result = $role->delete();
        DB::commit();
        return $result;
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
