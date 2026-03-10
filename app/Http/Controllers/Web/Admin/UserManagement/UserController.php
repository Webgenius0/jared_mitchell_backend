<?php

namespace App\Http\Controllers\Web\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use App\Traits\AdminApiResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use AdminApiResponse;

    public function __construct(protected UserService $userService) {}

    public function index()
    {
        // Stats for the summary cards
        $totalUsers    = User::count();
        $activeUsers   = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();
        $totalRoles    = Role::count();

        // Roles for the filter dropdown
        $roles = Role::orderBy('name')->get();

        return view('web.user-management.users.index', compact(
            'roles', 'totalUsers', 'activeUsers', 'inactiveUsers', 'totalRoles'
        ));
    }

    public function getData(Request $request)
    {
        $query = User::with(['profile', 'roles']);

        // Custom dropdown filters (passed as extra GET params from the DataTable ajax)
        if ($role = $request->get('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return datatables()->of($query)
            ->addIndexColumn()

            ->addColumn('user', function (User $user) {
                $name     = $user->profile?->name ?? 'No name';
                $email    = $user->email;
                $src      = $user->profile?->avatar;
                $initials = collect(explode(' ', $name))
                    ->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))
                    ->take(2)->join('');
                $palette  = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                $color    = $palette[ord(mb_substr($name, 0, 1) ?: 'A') % count($palette)];

                $avatar = $src
                    ? '<div class="avatar-sm"><img src="' . asset('storage/' . $src) . '" alt="' . e($name) . '" class="rounded-circle img-thumbnail avatar-sm"></div>'
                    : '<div class="avatar-sm"><div class="avatar-title rounded-circle bg-' . $color . '-subtle text-' . $color . '">' . e($initials ?: '?') . '</div></div>';

                return '<div class="d-flex align-items-center">'
                    . $avatar
                    . '<div class="ms-3">'
                    . '<h5 class="fs-14 mb-1"><a href="' . route('admin.users.show', $user) . '" class="text-body">' . e($name) . '</a></h5>'
                    . '<p class="text-muted mb-0 fs-12">' . e($email) . '</p>'
                    . '</div></div>';
            })

            ->addColumn('roles', function (User $user) {
                $map = [
                    'super-admin' => 'danger',
                    'admin'       => 'primary',
                    'manager'     => 'warning',
                    'user'        => 'info',
                ];
                return $user->roles->map(function ($role) use ($map) {
                    $c = $map[$role->name] ?? 'secondary';
                    return '<span class="badge bg-' . $c . '-subtle text-' . $c . '">' . e(ucfirst($role->name)) . '</span>';
                })->join(' ') ?: '<span class="text-muted fst-italic">No roles</span>';
            })

            ->addColumn('status', function (User $user) {
                /** @var User $authUser */
                $authUser = auth('admin')->user();
                $checked  = $user->status === 'active' ? 'checked' : '';
                $disabled = ($user->id === $authUser->id
                    || ($user->hasRole('super-admin') && ! $authUser->hasRole('super-admin')))
                    ? 'disabled' : '';

                return '<div class="form-check form-switch form-switch-md d-flex justify-content-center">'
                    . '<input class="form-check-input status-toggle" type="checkbox"'
                    . ' data-user-id="' . $user->id . '"'
                    . ' data-url="' . route('admin.users.toggle-status', $user) . '"'
                    . ' ' . $checked . ' ' . $disabled . '>'
                    . '</div>';
            })

            ->addColumn('action', function (User $user) {
                /** @var User $authUser */
                $authUser  = auth('admin')->user();
                $deletable = ! $user->hasRole('super-admin') && $user->id !== $authUser->id;
                $name      = e($user->profile?->name ?? $user->email);

                $html = '<div class="d-flex gap-2 justify-content-center">'
                    . '<a href="' . route('admin.users.show', $user) . '" class="btn btn-sm btn-soft-info" title="View"><i class="ri-eye-line"></i></a>'
                    . '<a href="' . route('admin.users.edit', $user) . '" class="btn btn-sm btn-soft-primary" title="Edit"><i class="ri-pencil-line"></i></a>';

                if ($deletable) {
                    $html .= '<button type="button" class="btn btn-sm btn-soft-danger btn-delete"'
                        . ' data-url="' . route('admin.users.destroy', $user) . '"'
                        . ' data-name="' . $name . '"'
                        . ' title="Delete"><i class="ri-delete-bin-line"></i></button>';
                }

                return $html . '</div>';
            })

            // Allow full-text search on the virtual "user" column
            ->filterColumn('user', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('email', 'like', "%{$keyword}%")
                      ->orWhereHas('profile', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
                });
            })

            // Allow ordering by profile name when clicking the User column header
            ->orderColumn('user', function ($query, $order) {
                $query->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
                      ->orderBy('profiles.name', $order)
                      ->select('users.*');
            })

            ->rawColumns(['user', 'roles', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('web.user-management.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return $this->success('User created successfully.', [], route('admin.users.index'));
    }

    public function show(User $user)
    {
        $user->load(['profile', 'roles.permissions']);
        $allPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')
            ->orderBy('name')->get();
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        return view('web.user-management.users.show', compact('user', 'allPermissions', 'userPermissions'));
    }

    public function edit(User $user)
    {
        $user->load(['profile', 'roles']);
        $roles = Role::orderBy('name')->get();
        $allPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')
            ->orderBy('name')->get();
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        return view('web.user-management.users.edit', compact('user', 'roles', 'allPermissions', 'userPermissions'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        // Prevent editing super-admin unless you ARE super-admin
        /** @var User $authUser */
        $authUser = auth('admin')->user();
        if ($user->hasRole('super-admin') && !$authUser->hasRole('super-admin')) {
            return $this->error('You cannot edit a super-admin user.', [], 403);
        }

        $user = $this->userService->updateUser($user, $request->validated());

        return $this->success('User updated successfully.', [], route('admin.users.index'));
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth('admin')->id()) {
            return $this->error('You cannot delete your own account.', [], 403);
        }

        // Prevent deleting super-admin
        if ($user->hasRole('super-admin')) {
            return $this->error('Super admin users cannot be deleted.', [], 403);
        }

        $this->userService->deleteUser($user);

        return $this->success('User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        // Prevent toggling own status
        if ($user->id === auth('admin')->id()) {
            return $this->error('You cannot change your own status.', [], 403);
        }

        // Prevent toggling super-admin
        /** @var User $authUser */
        $authUser = auth('admin')->user();
        if ($user->hasRole('super-admin') && !$authUser->hasRole('super-admin')) {
            return $this->error('You cannot change a super-admin\'s status.', [], 403);
        }

        $user = $this->userService->toggleStatus($user);

        return $this->success("User status changed to {$user->status}.", [
            'status' => $user->status,
        ]);
    }

    public function assignRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $this->userService->syncUserRoles($user, $validated['roles'] ?? []);

        return $this->success('Roles updated successfully.');
    }
}
