<?php

namespace Tests\Feature;

use App\Enums\ExamStatus;
use App\Enums\ExamType;
use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\Notification;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BladeSynchronizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_classes_page_has_enrolled_sections_view_data(): void
    {
        $this->ensureRole('student');

        $student = User::factory()->create();
        $student->assignRole('student');

        $lecturer = User::factory()->create();

        $subject = Subject::create([
            'code' => 'MTH101',
            'name' => 'Toan Roi Rac',
            'credits' => 3,
        ]);

        $semester = Semester::create([
            'name' => 'HK1 2026',
            'year' => 2026,
            'term' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-05-01',
            'is_current' => true,
        ]);

        $section = CourseSection::create([
            'code' => 'MTH101-01-HK1-2627',
            'name' => 'Toan Roi Rac - Nhom 1',
            'invite_code' => 'ABC123',
            'subject_id' => $subject->id,
            'semester_id' => $semester->id,
            'lecturer_id' => $lecturer->id,
            'max_students' => 100,
            'status' => 'active',
        ]);

        $section->students()->attach($student->id, [
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('student.classes.index'));

        $response->assertOk();
        $response->assertViewHas('enrolledSections');
        $response->assertSee('Toan Roi Rac - Nhom 1');
    }

    public function test_lecturer_exam_show_renders_published_status_and_action_buttons(): void
    {
        $this->ensureRole('lecturer');

        $lecturer = User::factory()->create();
        $lecturer->assignRole('lecturer');

        $subject = Subject::create([
            'code' => 'PRG101',
            'name' => 'Nhap Mon Lap Trinh',
            'credits' => 3,
        ]);

        $semester = Semester::create([
            'name' => 'HK1 2026',
            'year' => 2026,
            'term' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-05-01',
            'is_current' => true,
        ]);

        $section = CourseSection::create([
            'code' => 'PRG101-01-HK1-2627',
            'name' => 'Nhap Mon Lap Trinh - Nhom 1',
            'invite_code' => 'XYZ789',
            'subject_id' => $subject->id,
            'semester_id' => $semester->id,
            'lecturer_id' => $lecturer->id,
            'max_students' => 80,
            'status' => 'active',
        ]);

        $exam = Exam::create([
            'course_section_id' => $section->id,
            'title' => 'Giua Ky',
            'duration_minutes' => 45,
            'status' => ExamStatus::Published,
            'exam_type' => ExamType::Official,
            'total_points' => 10,
            'pass_points' => 5,
        ]);

        $response = $this->actingAs($lecturer)->get(route('lecturer.exams.show', $exam));

        $response->assertOk();
        $response->assertSee('Đang mở');
        $response->assertSee('Đóng đề');
    }

    public function test_student_layout_shows_unread_notification_badge(): void
    {
        $this->ensureRole('student');

        $student = User::factory()->create();
        $student->assignRole('student');

        UserNotification::create([
            'user_id' => $student->id,
            'type' => 'class_notification',
            'title' => 'Thong bao moi',
            'message' => 'Noi dung thong bao',
        ]);

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee('Có thông báo mới');
    }

    private function ensureRole(string $name): void
    {
        Role::query()->firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]);
    }
}
