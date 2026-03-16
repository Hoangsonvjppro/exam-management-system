<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_web_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect(route('filament.admin.auth.login', absolute: false));
    }

    public function test_lecturer_cannot_access_admin_panel(): void
    {
        $lecturerRole = Role::query()->create([
            'name' => 'lecturer',
            'guard_name' => 'web',
        ]);

        $lecturer = User::factory()->create();
        $lecturer->assignRole($lecturerRole);

        $this->actingAs($lecturer)
            ->get('/admin')
            ->assertRedirect(route('filament.admin.auth.login', absolute: false));
    }

    public function test_admin_guard_session_cannot_access_web_dashboard_without_web_login(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Root Admin',
            'email' => 'root@ems.local',
            'password' => 'password',
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/dashboard')
            ->assertRedirect(route('landing', absolute: false));
    }

    public function test_inactive_admin_is_logged_out_of_admin_panel(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@ems.local',
            'password' => 'password',
            'is_super_admin' => false,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin');

        $response->assertRedirect(route('login', absolute: false));
        $this->assertGuest('admin');
    }
}
