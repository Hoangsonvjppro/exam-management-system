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
        $lecturers = User::whereHas('roles', fn($q) => $q->where('code', 'lecturer'))
            ->get();

        if ($lecturers->isEmpty()) {
            $this->command->warn('⚠ Không tìm thấy giảng viên. Bỏ qua CourseSectionSeeder.');
            return;
        }

        // Lấy danh sách SV
        $students = User::whereHas('roles', fn($q) => $q->where('code', 'student'))
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
                'schedules' => [
                    ['day' => 2, 'start' => 1, 'end' => 3, 'room' => 'A101'], // Thứ 2, tiết 1-3
                    ['day' => 5, 'start' => 1, 'end' => 3, 'room' => 'A101'], // Thứ 5, tiết 1-3
                ],
                'students' => range(0, 14), // 15 SV đầu
            ],
            [
                'subject' => 'CS101',
                'lecturer' => 1,
                'group' => '02',
                'max' => 40,
                'schedules' => [
                    ['day' => 3, 'start' => 4, 'end' => 6, 'room' => 'A102'],
                    ['day' => 6, 'start' => 4, 'end' => 6, 'room' => 'A102'],
                ],
                'students' => range(15, 29), // 15 SV còn lại
            ],

            // CS201 — 1 lớp
            [
                'subject' => 'CS201',
                'lecturer' => 2,
                'group' => '01',
                'max' => 45,
                'schedules' => [
                    ['day' => 2, 'start' => 7, 'end' => 9, 'room' => 'B201'],
                    ['day' => 4, 'start' => 7, 'end' => 9, 'room' => 'B201'],
                ],
                'students' => range(0, 9),
            ],

            // CS301 — 1 lớp
            [
                'subject' => 'CS301',
                'lecturer' => 0,
                'group' => '01',
                'max' => 40,
                'schedules' => [
                    ['day' => 3, 'start' => 1, 'end' => 3, 'room' => 'Lab01'],
                    ['day' => 5, 'start' => 7, 'end' => 9, 'room' => 'Lab01'],
                ],
                'students' => range(10, 24),
            ],

            // CS302 — 1 lớp
            [
                'subject' => 'CS302',
                'lecturer' => 3,
                'group' => '01',
                'max' => 40,
                'schedules' => [
                    ['day' => 4, 'start' => 1, 'end' => 3, 'room' => 'A201'],
                    ['day' => 6, 'start' => 1, 'end' => 3, 'room' => 'A201'],
                ],
                'students' => range(0, 12),
            ],

            // CS401 — 2 lớp (Lập trình Web — chính là môn của đồ án!)
            [
                'subject' => 'CS401',
                'lecturer' => 1,
                'group' => '01',
                'max' => 35,
                'schedules' => [
                    ['day' => 2, 'start' => 4, 'end' => 6, 'room' => 'Lab02'],
                    ['day' => 4, 'start' => 4, 'end' => 6, 'room' => 'Lab02'],
                ],
                'students' => range(0, 14),
            ],
            [
                'subject' => 'CS401',
                'lecturer' => 4,
                'group' => '02',
                'max' => 35,
                'schedules' => [
                    ['day' => 3, 'start' => 7, 'end' => 9, 'room' => 'Lab03'],
                    ['day' => 6, 'start' => 7, 'end' => 9, 'room' => 'Lab03'],
                ],
                'students' => range(15, 29),
            ],

            // MATH201 — 1 lớp
            [
                'subject' => 'MATH201',
                'lecturer' => 3,
                'group' => '01',
                'max' => 50,
                'schedules' => [
                    ['day' => 5, 'start' => 4, 'end' => 6, 'room' => 'C301'],
                ],
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

            // Tạo lịch học
            foreach ($sectionData['schedules'] as $schedule) {
                ClassSchedule::updateOrCreate(
                    [
                        'course_section_id' => $section->id,
                        'day_of_week' => $schedule['day'],
                        'start_period' => $schedule['start'],
                    ],
                    [
                        'end_period' => $schedule['end'],
                        'room' => $schedule['room'],
                    ]
                );
            }

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

            $createdCount++;
        }

        $this->command->info("✓ Đã tạo {$createdCount} lớp học phần với lịch học và sinh viên đăng ký.");
    }
}
