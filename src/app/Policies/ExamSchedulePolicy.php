<?php

namespace App\Policies;

use App\Enums\ExamStatus;
use App\Models\ExamSchedule;
use App\Models\User;

class ExamSchedulePolicy
{
    private const ENROLLMENT_STATUS_ENROLLED = 'enrolled';

    /**
     * Sinh viên có được xem ca thi không?
     * Điều kiện:
     * - Thuộc lớp học phần của ca thi với trạng thái enrolled.
     * - Nếu ca thi đã có danh sách được phân, sinh viên phải nằm trong danh sách đó.
     */
    public function viewAsStudent(User $user, ExamSchedule $schedule): bool
    {
        $courseSection = $schedule->courseSection;
        if (! $courseSection) {
            return false;
        }

        $isEnrolled = $courseSection->students()
            ->where('users.id', $user->id)
            ->where('course_section_students.status', self::ENROLLMENT_STATUS_ENROLLED)
            ->exists();

        if (! $isEnrolled) {
            return false;
        }

        $hasAssignments = $schedule->scheduleStudents()->exists();

        if (! $hasAssignments) {
            return true;
        }

        return $schedule->scheduleStudents()
            ->where('student_id', $user->id)
            ->exists();
    }

    /**
     * Sinh viên có được bắt đầu / làm bài trong ca thi không?
     * Điều kiện: viewAsStudent + đề đã Published + ca chưa bị huỷ.
     */
    public function attemptExam(User $user, ExamSchedule $schedule): bool
    {
        if (! $this->viewAsStudent($user, $schedule)) {
            return false;
        }

        if ($schedule->status === 'cancelled') {
            return false;
        }

        return $schedule->exam?->status === ExamStatus::Published;
    }
}
