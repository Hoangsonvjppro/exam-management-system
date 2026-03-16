<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_with_permission_can_view_users(): void
    {
        $admin = Admin::query()->create([
            'name' => 'System Admin',
            'email' => 'system-admin@example.com',
            'password' => 'password',
            'is_active' => true,
            'is_super_admin' => false,
        ]);

        $permission = Permission::query()->create([
            'name' => 'admin.users.view',
            'guard_name' => 'admin',
        ]);

        $admin->givePermissionTo($permission);

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', User::class));
    }

    public function test_admin_without_permission_cannot_view_users(): void
    {
        $admin = Admin::query()->create([
            'name' => 'System Admin',
            'email' => 'readonly-admin@example.com',
            'password' => 'password',
            'is_active' => true,
            'is_super_admin' => false,
        ]);

        $this->assertFalse(Gate::forUser($admin)->allows('viewAny', User::class));
    }

    public function test_system_admin_cannot_update_super_admin_account(): void
    {
        $systemAdmin = Admin::query()->create([
            'name' => 'System Admin',
            'email' => 'system-admin2@example.com',
            'password' => 'password',
            'is_active' => true,
            'is_super_admin' => false,
        ]);

        $superAdmin = Admin::query()->create([
            'name' => 'Root Admin',
            'email' => 'root-admin@example.com',
            'password' => 'password',
            'is_active' => true,
            'is_super_admin' => true,
        ]);

        $permission = Permission::query()->create([
            'name' => 'admin.admins.update',
            'guard_name' => 'admin',
        ]);

        $systemAdmin->givePermissionTo($permission);

        $this->assertFalse(Gate::forUser($systemAdmin)->allows('update', $superAdmin));
    }

    public function test_super_admin_with_permission_can_update_system_admin_account(): void
    {
        $superAdmin = Admin::query()->create([
            'name' => 'Root Admin',
            'email' => 'root-admin2@example.com',
            'password' => 'password',
            'is_active' => true,
            'is_super_admin' => true,
        ]);

        $systemAdmin = Admin::query()->create([
            'name' => 'System Admin',
            'email' => 'system-admin3@example.com',
            'password' => 'password',
            'is_active' => true,
            'is_super_admin' => false,
        ]);

        $permission = Permission::query()->create([
            'name' => 'admin.admins.update',
            'guard_name' => 'admin',
        ]);

        $superAdmin->givePermissionTo($permission);

        $this->assertTrue(Gate::forUser($superAdmin)->allows('update', $systemAdmin));
    }
}
