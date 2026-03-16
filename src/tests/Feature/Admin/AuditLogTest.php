<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AdminUserLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_toggle_and_reset_password_actions_are_audited(): void
    {
        $lecturerRole = Role::query()->create([
            'name' => 'lecturer',
            'guard_name' => 'web',
        ]);

        $admin = Admin::query()->create([
            'name' => 'Audit Admin',
            'email' => 'audit@ems.local',
            'password' => 'password',
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        $lecturer = User::factory()->create();
        $lecturer->assignRole($lecturerRole);

        $this->actingAs($admin, 'admin');

        app(AdminUserLifecycleService::class)->toggleActive($lecturer);
        app(AdminUserLifecycleService::class)->resetLecturerPassword($lecturer);

        $this->assertDatabaseHas('audit_logs', [
            'actor_admin_id' => $admin->id,
            'action' => 'users.toggle_active',
            'target_type' => User::class,
            'target_id' => $lecturer->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_admin_id' => $admin->id,
            'action' => 'users.reset_password',
            'target_type' => User::class,
            'target_id' => $lecturer->id,
        ]);

        $this->assertSame(2, AuditLog::query()->count());
    }
}
