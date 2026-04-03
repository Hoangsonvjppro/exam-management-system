<?php

namespace App\Services;

use App\Models\CourseSection;
use App\Models\ExamSchedule;
use App\Models\Semester;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class SemesterGovernanceService
{
    public function assertSectionAllowsExamScheduling(CourseSection $section): void
    {
        $semester = $section->semester;

        if (! $semester instanceof Semester || ! $semester->allowsCourseSectionCreation()) {
            throw ValidationException::withMessages([
                'semester' => 'Học kỳ đã kết thúc hoặc lưu trữ, không thể tạo/sửa lịch thi.',
            ]);
        }
    }

    public function assertSectionAllowsGradeEditing(CourseSection $section): void
    {
        $semester = $section->semester;

        if (! $semester instanceof Semester || ! $semester->isCurrentPeriod()) {
            throw ValidationException::withMessages([
                'semester' => 'Chỉ được cập nhật điểm trong học kỳ đang diễn ra.',
            ]);
        }
    }

    public function assertScheduleWindowInsideSemester(
        CourseSection $section,
        CarbonInterface $startAt,
        CarbonInterface $endAt
    ): void {
        $semester = $section->semester;

        if (! $semester instanceof Semester) {
            throw ValidationException::withMessages([
                'semester' => 'Không tìm thấy học kỳ của lớp học phần.',
            ]);
        }

        $semesterStart = Carbon::parse((string) $semester->start_date)->startOfDay();
        $semesterEnd = Carbon::parse((string) $semester->end_date)->endOfDay();

        if ($startAt->lt($semesterStart) || $endAt->gt($semesterEnd)) {
            throw ValidationException::withMessages([
                'exam_date' => 'Lịch thi phải nằm trong thời gian của học kỳ.',
            ]);
        }
    }

    public function assertScheduleCanMutate(ExamSchedule $schedule): void
    {
        $section = $schedule->courseSection;

        if (! $section instanceof CourseSection) {
            throw ValidationException::withMessages([
                'schedule' => 'Không tìm thấy lớp học phần của ca thi.',
            ]);
        }

        $this->assertSectionAllowsExamScheduling($section);
    }
}
