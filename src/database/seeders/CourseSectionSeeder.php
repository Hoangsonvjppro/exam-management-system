<?php

namespace Database\Seeders;

use App\Models\ClassSchedule;
use App\Models\CourseSection;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================
 * CourseSectionSeeder — Tạo lớp học phần + lịch học + gán SV
 * ============================================================
 * Tạo 8 lớp HP cho HK2 2025-2026:
 *   - Mỗi môn 1-2 lớp
 *   - Mỗi lớp có lịch học chi tiết
 *   - Gán 10-15 SV vào mỗi lớp
 * ============================================================
 */
class CourseSectionSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy HK2 đang current
        $semester = Semester::where('is_current', true)->first();
        if (!$semester) {
            $this->command->warn('⚠ Không tìm thấy học kỳ hiện tại. Bỏ qua CourseSectionSeeder.');
            return;
        }

        // Lấy danh sách giảng viên
        $lecturers = User::whereHas('roles', fn($q) => $q->where('name', 'lecturer'))
            ->get();

        if ($lecturers->isEmpty()) {
            $this->command->warn('⚠ Không tìm thấy giảng viên. Bỏ qua CourseSectionSeeder.');
            return;
        }

        // Lấy danh sách SV
        $students = User::whereHas('roles', fn($q) => $q->where('name', 'student'))
            ->get();

        // Lấy tất cả môn học
        $subjects = Subject::all()->keyBy('code');

        // ─── Định nghĩa lớp học phần ────────────────────────
        $sections = [
            // CS101 — 2 lớp
            [
                'subject' => 'CS101',
                'lecturer' => 0, // index trong $lecturers
                'group' => '01',
                'max' => 40,
                'students' => range(0, 14), // 15 SV đầu
            ],
            [
                'subject' => 'CS101',
                'lecturer' => 1,
                'group' => '02',
                'max' => 40,
                'students' => range(15, 29), // 15 SV còn lại
            ],

            // CS201 — 1 lớp
            [
                'subject' => 'CS201',
                'lecturer' => 2,
                'group' => '01',
                'max' => 45,
                'students' => range(0, 9),
            ],

            // CS301 — 1 lớp
            [
                'subject' => 'CS301',
                'lecturer' => 0,
                'group' => '01',
                'max' => 40,
                'students' => range(10, 24),
            ],

            // CS302 — 1 lớp
            [
                'subject' => 'CS302',
                'lecturer' => 3,
                'group' => '01',
                'max' => 40,
                'students' => range(0, 12),
            ],

            // CS401 — 2 lớp (Lập trình Web — chính là môn của đồ án!)
            [
                'subject' => 'CS401',
                'lecturer' => 1,
                'group' => '01',
                'max' => 35,
                'students' => range(0, 14),
            ],
            [
                'subject' => 'CS401',
                'lecturer' => 4,
                'group' => '02',
                'max' => 35,
                'students' => range(15, 29),
            ],

            // MATH201 — 1 lớp
            [
                'subject' => 'MATH201',
                'lecturer' => 3,
                'group' => '01',
                'max' => 50,
                'students' => range(0, 19),
            ],
        ];

        $createdCount = 0;

        foreach ($sections as $sectionData) {
            $subject = $subjects[$sectionData['subject']] ?? null;
            $lecturer = $lecturers[$sectionData['lecturer']] ?? $lecturers->first();

            if (!$subject) {
                continue;
            }

            // Mã lớp: CS101-01-HK2-2526
            $code = "{$subject->code}-{$sectionData['group']}-HK{$semester->term}-" .
                substr($semester->year, -2) . substr($semester->year + 1, -2);

            // Tạo lớp học phần
            $section = CourseSection::updateOrCreate(
                ['code' => $code],
                [
                    'subject_id' => $subject->id,
                    'semester_id' => $semester->id,
                    'lecturer_id' => $lecturer->id,
                    'max_students' => $sectionData['max'],
                    'status' => 'active',
                ]
            );

            // Gán sinh viên
            foreach ($sectionData['students'] as $studentIndex) {
                $student = $students[$studentIndex] ?? null;
                if ($student && !$section->students()->where('student_id', $student->id)->exists()) {
                    $section->students()->attach($student->id, [
                        'status' => 'enrolled',
                        'enrolled_at' => now()->subDays(rand(1, 14)),
                    ]);
                }
            }

            // Tạo thời khóa biểu mẫu (2 buổi/tuần)
            $dayOfWeek = rand(2, 4); // Thứ 2 - Thứ 4
            ClassSchedule::create([
                'course_section_id' => $section->id,
                'day_of_week'      => $dayOfWeek,
                'start_period'     => 1,
                'end_period'       => 3,
                'room'             => 'A' . rand(100, 300),
            ]);

            ClassSchedule::create([
                'course_section_id' => $section->id,
                'day_of_week'      => $dayOfWeek + 2, // Thứ 4 - Thứ 6
                'start_period'     => 4,
                'end_period'       => 6,
                'room'             => 'B' . rand(100, 300),
            ]);

            $createdCount++;
        }

        $this->command->info("✓ Đã tạo {$createdCount} lớp học phần với lịch học và sinh viên đăng ký.");
    }
}
