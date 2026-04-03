<?php

namespace App\Policies;

use App\Enums\ExamStatus;
use App\Models\ExamSchedule;
use App\Models\User;
use Illuminate\Support\Carbon;

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

        $isAssigned = $schedule->scheduleStudents()
            ->where('student_id', $user->id)
            ->exists();

        if ($isAssigned) {
            return true;
        }

        // Cho phép truy cập nếu sinh viên vừa tham gia lớp sau khi lịch thi được tạo
        // (phục vụ tự động đồng bộ assignment ở bước bắt đầu thi).
        if (! $schedule->created_at) {
            return false;
        }

        $enrolledAt = $courseSection->students()
            ->where('users.id', $user->id)
            ->where('course_section_students.status', self::ENROLLMENT_STATUS_ENROLLED)
            ->value('course_section_students.enrolled_at');

        if (! $enrolledAt) {
            return false;
        }

        try {
            $enrolledAt = Carbon::parse($enrolledAt);

            if (! $enrolledAt->gt($schedule->created_at)) {
                return false;
            }

            // Chỉ chấp nhận fallback nếu mốc vào lớp vẫn hợp lý theo cửa sổ thời gian ca thi.
            if ($schedule->schedule_mode === ExamSchedule::MODE_IN_RANGE) {
                return $enrolledAt->lte($schedule->end_datetime);
            }

            return $enrolledAt->lte($schedule->start_datetime);
        } catch (\Throwable) {
            return false;
        }
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
