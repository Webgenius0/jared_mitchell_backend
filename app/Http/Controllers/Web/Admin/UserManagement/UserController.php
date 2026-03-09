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

    public function index(Request $request)
    {
        $query = User::with(['profile', 'roles']);

        // Search by name or email
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhereHas('profile', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by role
        if ($role = $request->get('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        // Stats
        $totalUsers    = User::count();
        $activeUsers   = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();
        $totalRoles    = Role::count();

        $roles = Role::orderBy('name')->get();

        return view('pages.admin.user-management.users.index', compact(
            'users', 'roles', 'totalUsers', 'activeUsers', 'inactiveUsers', 'totalRoles'
        ));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('pages.admin.user-management.users.create', compact('roles'));
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

        return view('pages.admin.user-management.users.show', compact('user', 'allPermissions', 'userPermissions'));
    }

    public function edit(User $user)
    {
        $user->load(['profile', 'roles']);
        $roles = Role::orderBy('name')->get();
        $allPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')
            ->orderBy('name')->get();
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        return view('pages.admin.user-management.users.edit', compact('user', 'roles', 'allPermissions', 'userPermissions'));
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
