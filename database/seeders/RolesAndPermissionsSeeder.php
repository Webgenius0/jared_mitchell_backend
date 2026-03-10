<?php

namespace Database\Seeders;

use App\Helpers\FileHandle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions (grouped logically) ───────────────────────────
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage users',

            // Role Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'manage roles',

            // Permission Management
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'manage permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        // ── Roles ─────────────────────────────────────────────────────

        // Super Admin — gets ALL permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin — user + role management
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $admin->syncPermissions([
            'view users', 'create users', 'edit users', 'delete users', 'manage users',
            'view roles', 'create roles', 'edit roles', 'delete roles', 'manage roles',
            'view permissions',
        ]);

        // Manager — view only
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'admin']);
        $manager->syncPermissions([
            'view users',
            'view roles',
            'view permissions',
        ]);

        // User — no special permissions
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'admin']);

        // ── Default Super Admin User ──────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'password'          => Hash::make('12345678'),
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole($superAdmin);

        // Create a profile for the super admin
        if (!$user->profile) {
            $user->profile()->create([
                'name'     => 'Super Admin',
                'username' => FileHandle::generateUsername('Super'),
                'slug'     => FileHandle::generateSlug('Super'),
            ]);
        }

        Role::create(['name' => 'artist', 'guard_name' => 'api']);
        Role::create(['name' => 'member', 'guard_name' => 'api']);
        Role::create(['name' => 'sponsor', 'guard_name' => 'api']);
        Role::create(['name' => 'boss', 'guard_name' => 'api']);
    }
}
