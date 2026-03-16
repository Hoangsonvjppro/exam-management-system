<?php

namespace Tests\Feature\Auth;

use App\Models\CourseSection;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentRoleAutoSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_join_first_class_auto_assigns_student_role_and_leave_last_class_removes_it(): void
    {
        $studentRole = Role::query()->create([
            'name' => 'student',
            'guard_name' => 'web',
        ]);

        $lecturer = User::factory()->create([
            'email' => 'lecturer-owner@ems.local',
        ]);

        $semester = Semester::query()->create([
            'name' => 'HK1 2026-2027',
            'year' => 2026,
            'term' => 1,
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-15',
            'is_current' => true,
        ]);

        $subject = Subject::query()->create([
            'code' => 'CS101',
            'name' => 'Nhap mon CSDL',
            'credits' => 3,
        ]);

        $section = CourseSection::query()->create([
            'code' => 'CS101-01-HK1-2627',
            'invite_code' => 'JOINME01',
            'subject_id' => $subject->id,
            'semester_id' => $semester->id,
            'lecturer_id' => $lecturer->id,
            'max_students' => 60,
            'status' => 'active',
        ]);

        $studentUser = User::factory()->create([
            'student_code' => 'SV001',
            'class_name' => 'DHKTPM17A',
        ]);

        $this->actingAs($studentUser)
            ->post(route('student.join-class'), ['invite_code' => 'JOINME01'])
            ->assertRedirect();

        $studentUser->refresh();

        $this->assertTrue($studentUser->hasRole($studentRole));
        $this->assertDatabaseHas('course_section_students', [
            'course_section_id' => $section->id,
            'student_id' => $studentUser->id,
            'status' => 'enrolled',
        ]);

        $this->actingAs($studentUser)
            ->delete(route('student.leave-class', $section))
            ->assertRedirect();

        $studentUser->refresh();

        $this->assertFalse($studentUser->hasRole($studentRole));
    }
}
