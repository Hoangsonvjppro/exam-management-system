<?php

namespace App\Services;

use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Support\Str;

class CourseSectionService
{
    /**
     * Tạo lớp học phần mới.
     *
     * @param User $lecturer
     * @param array $data
     * @return CourseSection
     */
    public function createCourseSection(User $lecturer, array $data): CourseSection
    {
        $code = CourseSection::generateCode($data['subject_id'], $data['semester_id']);

        return CourseSection::create([
            'name'         => $data['name'],
            'code'         => $code,
            'subject_id'   => $data['subject_id'],
            'semester_id'  => $data['semester_id'],
            'invite_code'  => strtoupper(Str::random(6)),
            'lecturer_id'  => $lecturer->id,
            'max_students' => $data['max_students'] ?? 100,
            'status'       => 'active',
        ]);
    }

    /**
     * Tạo mới mã học phần.
     *
     * @param CourseSection $section
     * @return CourseSection
     */
    public function regenerateInviteCode(CourseSection $section): CourseSection
    {
        $section->update(['invite_code' => strtoupper(Str::random(6))]);
        return $section;
    }
}
