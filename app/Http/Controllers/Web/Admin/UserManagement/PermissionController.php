<?php

namespace App\Http\Controllers\Web\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Helpers\PermissionHelper;
use App\Traits\AdminApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    use AdminApiResponse;

    public function index()
    {
        $permissions = Permission::where('guard_name', 'admin')->orderBy('name')->get();
        $grouped = PermissionHelper::groupPermissionsByModule($permissions);

        // Load role counts for each permission
        $permissions->load('roles');

        return view('pages.admin.user-management.permissions.index', compact('grouped', 'permissions'));
    }

    public function create()
    {
        // Get existing groups for auto-suggest
        $permissions = Permission::where('guard_name', 'admin')->orderBy('name')->get();
        $existingGroups = $permissions->map(function ($p) {
            $words = explode(' ', $p->name);
            return end($words);
        })->unique()->values();

        return view('pages.admin.user-management.permissions.create', compact('existingGroups'));
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = Permission::create([
            'name'       => strtolower($request->validated()['name']),
            'guard_name' => 'admin',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return $this->success('Permission created successfully.', [], route('admin.permissions.index'));
    }

    public function edit(Permission $permission)
    {
        $existingGroups = Permission::where('guard_name', 'admin')->get()->map(function ($p) {
            $words = explode(' ', $p->name);
            return end($words);
        })->unique()->values();

        return view('pages.admin.user-management.permissions.edit', compact('permission', 'existingGroups'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z\s]+$/', Rule::unique('permissions', 'name')->ignore($permission->id)],
        ]);

        $permission->update(['name' => strtolower($validated['name'])]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return $this->success('Permission updated successfully.', [], route('admin.permissions.index'));
    }

    public function destroy(Permission $permission)
    {
        // Prevent deleting if assigned to any role
        if ($permission->roles()->count() > 0) {
            return $this->error('Cannot delete permission that is assigned to roles. Remove it from all roles first.', [], 422);
        }

        $permission->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return $this->success('Permission deleted successfully.');
    }
}
