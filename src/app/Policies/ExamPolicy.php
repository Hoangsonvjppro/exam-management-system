<?php

namespace App\Policies;

use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    private const ENROLLMENT_STATUS_ENROLLED = 'enrolled';

    /**
     * Sinh viên có được xem đề thi không?
     * Điều kiện: sinh viên phải enrolled trong lớp học phần chứa đề thi.
     */
    public function viewAsStudent(User $user, Exam $exam): bool
    {
        return $exam->schedules()
            ->whereHas('courseSection.students', function ($query) use ($user) {
                $query->where('users.id', $user->id)
                    ->where('course_section_students.status', self::ENROLLMENT_STATUS_ENROLLED);
            })
            ->exists();
    }

    /**
     * Sinh viên có được bắt đầu / làm bài thi không?
     * Điều kiện: enrolled + exam published + trong khung giờ hợp lệ.
     */
    public function attemptExam(User $user, Exam $exam): bool
    {
        if (! $this->viewAsStudent($user, $exam)) {
            return false;
        }

        return $exam->status === \App\Enums\ExamStatus::Published;
    }

    /**
     * Giảng viên có quyền quản lý đề thi không?
     * Điều kiện: giảng viên phải là lecturer_id của course_section chứa đề thi.
     */
    public function manageLecturer(User $user, Exam $exam): bool
    {
        return $user->id === $exam->created_by;
    }

    /**
     * Giảng viên có quyền tạo đề thi cho lớp học phần không?
     * Dùng cho nested routes: course-sections/{courseSection}/exams/create
     */
    public function createForSection(User $user, CourseSection $courseSection): bool
    {
        return $courseSection->lecturer_id === $user->id;
    }
}
