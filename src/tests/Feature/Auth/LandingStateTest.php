<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LandingStateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_guest_can_view_public_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('EMS', false);
    }

    public function test_authenticated_user_without_role_stays_on_landing_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('EMS', false);
    }

    public function test_student_is_redirected_to_student_dashboard_when_visiting_landing(): void
    {
        $studentRole = Role::query()->create([
            'name' => 'student',
            'guard_name' => 'web',
        ]);

        $student = User::factory()->create();
        $student->assignRole($studentRole);

        $this->actingAs($student)
            ->get('/')
            ->assertRedirect(route('student.dashboard', absolute: false));
    }

    public function test_lecturer_is_redirected_to_lecturer_dashboard_when_visiting_landing(): void
    {
        $lecturerRole = Role::query()->create([
            'name' => 'lecturer',
            'guard_name' => 'web',
        ]);

        $lecturer = User::factory()->create();
        $lecturer->assignRole($lecturerRole);

        $this->actingAs($lecturer)
            ->get('/')
            ->assertRedirect(route('lecturer.dashboard', absolute: false));
    }
}
