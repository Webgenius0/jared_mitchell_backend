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
        return view('web.user-management.roles.index');
    }

    public function getData(Request $request)
    {
        $query = Role::withCount(['permissions', 'users']);

        return datatables()->of($query)
            ->addIndexColumn()

            ->addColumn('role', function (Role $role) {
                $map = [
                    'super-admin' => 'danger',
                    'admin'       => 'primary',
                    'manager'     => 'warning',
                    'user'        => 'info',
                ];
                $color  = $map[$role->name] ?? 'secondary';
                $lock   = $role->name === 'super-admin'
                    ? '<i class="ri-lock-line text-danger me-1"></i>'
                    : '';
                $badge  = '<span class="badge bg-' . $color . '-subtle text-' . $color . '">' . e(ucfirst($role->name)) . '</span>';

                return '<div class="d-flex align-items-center gap-2">' . $lock . $badge . '</div>';
            })

            ->addColumn('permissions', function (Role $role) {
                return '<span class="badge bg-info-subtle text-info">' . $role->permissions_count . ' permissions</span>';
            })

            ->addColumn('users', function (Role $role) {
                return '<span class="badge bg-primary-subtle text-primary">' . $role->users_count . ' users</span>';
            })

            ->addColumn('guard', function (Role $role) {
                return '<span class="text-muted">' . e($role->guard_name) . '</span>';
            })

            ->addColumn('action', function (Role $role) {
                $deletable = $role->name !== 'super-admin';
                $name      = e($role->name);

                $html = '<div class="d-flex gap-2 justify-content-center">'
                    . '<a href="' . route('admin.roles.edit', $role) . '" class="btn btn-sm btn-soft-primary" title="Edit"><i class="ri-pencil-line"></i></a>';

                if ($deletable) {
                    $html .= '<button type="button" class="btn btn-sm btn-soft-danger btn-delete"'
                        . ' data-url="' . route('admin.roles.destroy', $role) . '"'
                        . ' data-name="' . $name . '"'
                        . ' title="Delete"><i class="ri-delete-bin-line"></i></button>';
                }

                return $html . '</div>';
            })

            ->filterColumn('role', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })

            ->orderColumn('role', fn ($query, $order) => $query->orderBy('name', $order))

            ->rawColumns(['role', 'permissions', 'users', 'guard', 'action'])
            ->make(true);
    }

    public function create()
    {
        $groupedPermissions = $this->roleService->getPermissionsGrouped();
        return view('web.user-management.roles.create', compact('groupedPermissions'));
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

        return view('web.user-management.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
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
