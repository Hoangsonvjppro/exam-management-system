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
    public function assertCanArchiveSemester(Semester $semester): void
    {
        if ($semester->status === Semester::STATUS_ARCHIVED) {
            throw ValidationException::withMessages([
                'status' => 'Học kỳ này đã ở trạng thái lưu trữ.',
            ]);
        }

        if (! $semester->isEndedPeriod()) {
            throw ValidationException::withMessages([
                'status' => 'Chỉ có thể lưu trữ học kỳ đã kết thúc.',
            ]);
        }

        $hasOpenSchedules = ExamSchedule::query()
            ->whereHas('courseSection', function ($query) use ($semester): void {
                $query->where('semester_id', $semester->id);
            })
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->exists();

        if ($hasOpenSchedules) {
            throw ValidationException::withMessages([
                'status' => 'Không thể lưu trữ khi học kỳ vẫn còn ca thi chưa hoàn tất.',
            ]);
        }
    }

    public function archiveSemester(Semester $semester): Semester
    {
        $this->assertCanArchiveSemester($semester);

        $semester->forceFill([
            'status' => Semester::STATUS_ARCHIVED,
            'is_current' => false,
        ])->save();

        return $semester->refresh();
    }

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
