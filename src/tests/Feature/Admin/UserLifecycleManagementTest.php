<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\AdminUserLifecycleService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserLifecycleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_reset_password_for_lecturer_sets_temporary_password_and_force_change_flag(): void
    {
        $lecturerRole = Role::query()->create([
            'name' => 'lecturer',
            'guard_name' => 'web',
        ]);

        $lecturer = User::factory()->create([
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);
        $lecturer->assignRole($lecturerRole);

        $temporaryPassword = app(AdminUserLifecycleService::class)->resetLecturerPassword($lecturer);

        $lecturer->refresh();

        $this->assertTrue(Hash::check($temporaryPassword, $lecturer->password));
        $this->assertTrue($lecturer->must_change_password);
        $this->assertNull($lecturer->password_changed_at);
    }

    public function test_reset_password_throws_for_non_lecturer_account(): void
    {
        $studentRole = Role::query()->create([
            'name' => 'student',
            'guard_name' => 'web',
        ]);

        $student = User::factory()->create();
        $student->assignRole($studentRole);

        $this->expectException(DomainException::class);

        app(AdminUserLifecycleService::class)->resetLecturerPassword($student);
    }

    public function test_toggle_active_flips_user_status_each_time(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $firstState = app(AdminUserLifecycleService::class)->toggleActive($user);
        $user->refresh();

        $this->assertFalse($firstState);
        $this->assertFalse($user->is_active);

        $secondState = app(AdminUserLifecycleService::class)->toggleActive($user);
        $user->refresh();

        $this->assertTrue($secondState);
        $this->assertTrue($user->is_active);
    }
}
