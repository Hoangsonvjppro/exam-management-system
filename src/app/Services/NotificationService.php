<?php

namespace App\Services;

use App\Models\CourseSection;
use App\Models\Notification;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Gửi thông báo đến tất cả sinh viên trong lớp học phần.
     *
     * @param CourseSection $section
     * @param array $data Validated data chứa 'title' và 'message'
     *
     * @throws \DomainException Khi lớp chưa có sinh viên
     */
    public function sendToSection(CourseSection $section, array $data): void
    {
        $students = $section->students;

        if ($students->isEmpty()) {
            throw new \DomainException('Lớp học phần này chưa có sinh viên nào để gửi thông báo.');
        }

        $now = now();
        $notificationsToInsert = [];

        foreach ($students as $student) {
            $notificationsToInsert[] = [
                'id'         => (string) Str::uuid(),
                'user_id'    => $student->id,
                'type'       => 'course_announcement',
                'title'      => $data['title'],
                'message'    => $data['message'],
                'data'       => json_encode([
                    'course_section_id'   => $section->id,
                    'course_section_name' => $section->name ?? $section->code,
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert for performance
        Notification::insert($notificationsToInsert);
    }
}
