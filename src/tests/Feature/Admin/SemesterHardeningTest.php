<?php

namespace Tests\Feature\Admin;

use App\Models\CourseSection;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Services\SemesterGovernanceService;
use App\Services\SemesterLifecycleService;
use App\Services\SemesterValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SemesterHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_delete_semester_with_course_sections(): void
    {
        $semester = Semester::create([
            'name' => 'HK1 2026-2027',
            'year' => 2026,
            'term' => 1,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(90)->toDateString(),
            'status' => Semester::STATUS_CURRENT,
            'is_current' => true,
        ]);

        $subject = Subject::create([
            'code' => 'TEST101',
            'name' => 'Test Subject',
        ]);

        $lecturer = User::factory()->create([
            'lecturer_code' => 'GV_TEST_001',
        ]);

        CourseSection::create([
            'code' => 'TEST101-01-HK1-2627',
            'subject_id' => $subject->id,
            'semester_id' => $semester->id,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Không thể xóa học kỳ đã phát sinh lớp học phần.');

        $semester->delete();
    }

    public function test_rejects_semester_that_already_ended_in_the_past(): void
    {
        $payload = [
            'name' => 'HK Cu 2024-2025',
            'year' => 2024,
            'term' => 1,
            'start_date' => now()->subYear()->subDays(60)->toDateString(),
            'end_date' => now()->subYear()->toDateString(),
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Không thể cấu hình học kỳ đã kết thúc trong quá khứ.');

        app(SemesterValidationService::class)->validateForUpsert($payload);
    }

    public function test_rejects_start_year_in_the_past(): void
    {
        $currentYear = now()->year;

        $payload = [
            'name' => 'HK1 ' . ($currentYear - 1) . '-' . $currentYear,
            'year' => $currentYear - 1,
            'term' => 1,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Năm học bắt đầu chỉ được chọn từ năm hiện tại trở đi.');

        app(SemesterValidationService::class)->validateForUpsert($payload);
    }

    public function test_cannot_archive_current_semester(): void
    {
        $semester = Semester::create([
            'name' => 'HK1 2026-2027',
            'year' => now()->year,
            'term' => 1,
            'start_date' => now()->subDays(15)->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => Semester::STATUS_CURRENT,
            'is_current' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Chỉ có thể lưu trữ học kỳ đã kết thúc.');

        app(SemesterGovernanceService::class)->archiveSemester($semester);
    }

    public function test_can_archive_ended_semester(): void
    {
        $semester = Semester::create([
            'name' => 'HK2 2024-2025',
            'year' => now()->year,
            'term' => 2,
            'start_date' => now()->subMonths(6)->toDateString(),
            'end_date' => now()->subMonth()->toDateString(),
            'status' => Semester::STATUS_ENDED,
            'is_current' => false,
        ]);

        app(SemesterGovernanceService::class)->archiveSemester($semester);

        $semester->refresh();

        $this->assertSame(Semester::STATUS_ARCHIVED, $semester->status);
        $this->assertFalse($semester->is_current);
    }

    public function test_allows_overlapping_current_semesters(): void
    {
        Semester::create([
            'name' => 'HK1 2026-2027',
            'year' => now()->year,
            'term' => 1,
            'start_date' => now()->subDays(20)->toDateString(),
            'end_date' => now()->addDays(70)->toDateString(),
            'status' => Semester::STATUS_UPCOMING,
            'is_current' => false,
        ]);

        Semester::create([
            'name' => 'HK He 2026',
            'year' => now()->year,
            'term' => 3,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'status' => Semester::STATUS_UPCOMING,
            'is_current' => false,
        ]);

        app(SemesterLifecycleService::class)->syncAll();

        $currentCount = Semester::query()->where('status', Semester::STATUS_CURRENT)->count();

        $this->assertSame(2, $currentCount);
    }
}
