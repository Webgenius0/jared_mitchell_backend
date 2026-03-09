<?php

namespace App\Http\Controllers\Web\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Services\RoleService;
use App\Traits\AdminApiResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use AdminApiResponse;

    public function __construct(protected RoleService $roleService) {}

    public function index()
    {
        $roles = Role::where('guard_name', 'admin')
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->paginate(15);

        return view('pages.admin.user-management.roles.index', compact('roles'));
    }

    public function create()
    {
        $groupedPermissions = $this->roleService->getPermissionsGrouped();
        return view('pages.admin.user-management.roles.create', compact('groupedPermissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->createRole($request->validated());

        return $this->success('Role created successfully.', [], route('admin.roles.index'));
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $groupedPermissions = $this->roleService->getPermissionsGrouped();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('pages.admin.user-management.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        // Prevent editing super-admin role name
        if ($role->name === 'super-admin' && $request->input('name') !== 'super-admin') {
            return $this->error('The super-admin role name cannot be changed.', [], 403);
        }

        $role = $this->roleService->updateRole($role, $request->validated());

        return $this->success('Role updated successfully.', [], route('admin.roles.index'));
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return $this->error('The super-admin role cannot be deleted.', [], 403);
        }

        $this->roleService->deleteRole($role);

        return $this->success('Role deleted successfully.');
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $this->roleService->syncPermissions($role, $validated['permissions'] ?? []);

        return $this->success('Permissions synced successfully.');
    }
}
