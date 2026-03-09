<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->superAdmin = User::where('email', 'superadmin@example.com')->first();

        $this->regularUser = User::create([
            'email'             => 'regular@example.com',
            'password'          => bcrypt('password'),
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);
        $this->regularUser->assignRole('user');
        $this->regularUser->profile()->create([
            'name'     => 'Regular User',
            'username' => '@regularuser',
            'slug'     => 'regularuser',
        ]);
    }

    public function test_super_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.admin.user-management.users.index');
    }

    public function test_super_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.users.store'), [
                'name'                  => 'New User',
                'email'                 => 'newuser@example.com',
                'password'              => 'Password1!',
                'password_confirmation' => 'Password1!',
                'status'                => 'active',
                'roles'                 => ['user'],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_super_admin_can_update_user(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->put(route('admin.users.update', $this->regularUser), [
                'name'   => 'Updated Name',
                'email'  => $this->regularUser->email,
                'status' => 'active',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_super_admin_can_delete_user(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->delete(route('admin.users.destroy', $this->regularUser));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted('users', ['id' => $this->regularUser->id]);
    }

    public function test_super_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->delete(route('admin.users.destroy', $this->superAdmin));

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    public function test_user_without_permission_cannot_access_user_management(): void
    {
        // Regular user has no 'manage users' permission
        $response = $this->actingAs($this->regularUser, 'admin')
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_manage_users_permission_can_access(): void
    {
        $adminUser = User::create([
            'email'             => 'admin@example.com',
            'password'          => bcrypt('password'),
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);
        $adminUser->assignRole('admin');
        $adminUser->profile()->create([
            'name'     => 'Admin User',
            'username' => '@adminuser',
            'slug'     => 'adminuser',
        ]);

        $response = $this->actingAs($adminUser, 'admin')
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
    }

    public function test_status_toggle_works_via_ajax(): void
    {
        $this->assertEquals('active', $this->regularUser->status);

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.users.toggle-status', $this->regularUser));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->regularUser->refresh();
        $this->assertEquals('inactive', $this->regularUser->status);
    }
}
