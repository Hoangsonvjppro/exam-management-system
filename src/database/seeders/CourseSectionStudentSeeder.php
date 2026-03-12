<?php

namespace Database\Seeders;

use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSectionStudentSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả lớp active
        $sections = CourseSection::where('status', 'active')->get();
        
        // Lấy tất cả user có role student
        $students = User::role('student')->get();

        if ($sections->isEmpty()) {
            $this->command->warn('⚠ Không có lớp active. Hãy chạy CourseSectionSeeder trước.');
            return;
        }

        if ($students->isEmpty()) {
            $this->command->warn('⚠ Không có sinh viên. Hãy chạy AdminUserSeeder trước.');
            return;
        }

        $inserted = 0;

        foreach ($sections as $section) {
            // Mỗi lớp lấy ngẫu nhiên 5–15 sinh viên (không vượt max_students)
            $count       = min(rand(5, 15), $section->max_students, $students->count());
            $picked      = $students->random($count);
            $enrollments = [];

            foreach ($picked as $student) {
                // Phân bổ status ngẫu nhiên có trọng số:
                // 80% enrolled, 10% completed, 10% dropped
                $rand   = rand(1, 10);
                $status = match(true) {
                    $rand <= 8 => 'enrolled',
                    $rand === 9 => 'completed',
                    default     => 'dropped',
                };

                $enrollments[] = [
                    'course_section_id' => $section->id,
                    'student_id'        => $student->id,
                    'status'            => $status,
                    'enrolled_at'       => now()->subDays(rand(1, 60)),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            // insertOrIgnore tránh lỗi nếu seeder chạy lại
            DB::table('course_section_students')->insertOrIgnore($enrollments);
            $inserted += count($enrollments);
        }

        $this->command->info("✓ CourseSectionStudentSeeder: {$inserted} bản ghi đã được tạo.");
    }
}