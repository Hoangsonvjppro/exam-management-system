<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardRoleRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_dashboard_redirects_lecturer_to_lecturer_dashboard(): void
    {
        $lecturerRole = Role::query()->create([
            'name' => 'lecturer',
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create();
        $user->assignRole($lecturerRole);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('lecturer.dashboard', absolute: false));
    }

    public function test_dashboard_redirects_student_to_student_dashboard(): void
    {
        $studentRole = Role::query()->create([
            'name' => 'student',
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create();
        $user->assignRole($studentRole);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('student.dashboard', absolute: false));
    }

    public function test_authenticated_user_without_role_is_redirected_to_landing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('landing', absolute: false));
    }
}
