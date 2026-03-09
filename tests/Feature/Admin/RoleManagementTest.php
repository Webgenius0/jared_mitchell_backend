<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->superAdmin = User::where('email', 'superadmin@example.com')->first();
    }

    public function test_can_create_role_with_permissions(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.roles.store'), [
                'name'        => 'editor',
                'permissions' => ['view users', 'edit users'],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $role = Role::where('name', 'editor')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('view users'));
        $this->assertTrue($role->hasPermissionTo('edit users'));
    }

    public function test_can_update_role_permissions(): void
    {
        $role = Role::where('name', 'manager')->first();

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->put(route('admin.roles.update', $role), [
                'name'        => 'manager',
                'permissions' => ['view users', 'edit users', 'view roles'],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('edit users'));
    }

    public function test_cannot_delete_super_admin_role(): void
    {
        $role = Role::where('name', 'super-admin')->first();

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->delete(route('admin.roles.destroy', $role));

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseHas('roles', ['name' => 'super-admin']);
    }
}
