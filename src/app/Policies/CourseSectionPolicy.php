<?php

namespace App\Policies;

use App\Models\CourseSection;
use App\Models\User;

class CourseSectionPolicy
{
    /**
     * Giảng viên có quyền quản lý lớp học phần không?
     * Điều kiện: giảng viên phải là lecturer_id của lớp.
     */
    public function manage(User $user, CourseSection $courseSection): bool
    {
        return $courseSection->lecturer_id === $user->id;
    }

    /**
     * Giảng viên có quyền gửi thông báo đến lớp không?
     * Điều kiện: giảng viên phải sở hữu lớp.
     */
    public function sendNotification(User $user, CourseSection $courseSection): bool
    {
        return $this->manage($user, $courseSection);
    }
}
