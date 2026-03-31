<?php

namespace Database\Seeders;

use App\Models\ClassSchedule;
use App\Models\CourseSection;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * ============================================================
 * CourseSectionSeeder — Tạo 5 lớp HP + lịch học + gán SV
 * ============================================================
 * Dùng đúng mã môn IT001-IT005 từ SubjectSeeder.
 * Chia đều 3 giảng viên, mỗi lớp 8-12 SV từ pool 20 SV.
 * Mỗi lớp có invite_code, lịch học 2 buổi/tuần.
 * ============================================================
 */
class CourseSectionSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Lấy học kỳ hiện tại ─────────────────────────────
        $semester = Semester::where('is_current', true)->first();
        if (!$semester) {
            $this->command->warn('⚠ Không tìm thấy học kỳ hiện tại. Bỏ qua CourseSectionSeeder.');
            return;
        }

        // ─── Lấy danh sách giảng viên (3 GV) ─────────────────
        $lecturers = User::whereHas('roles', fn($q) => $q->where('name', 'lecturer'))
            ->orderBy('id')
            ->get();

        if ($lecturers->isEmpty()) {
            $this->command->warn('⚠ Không tìm thấy giảng viên. Bỏ qua CourseSectionSeeder.');
            return;
        }

        // ─── Lấy danh sách sinh viên (20 SV) ─────────────────
        $students = User::whereHas('roles', fn($q) => $q->where('name', 'student'))
            ->orderBy('id')
            ->get();

        if ($students->count() < 8) {
            $this->command->warn('⚠ Cần ít nhất 8 sinh viên. Bỏ qua CourseSectionSeeder.');
            return;
        }

        // ─── Lấy tất cả môn học (IT001-IT005) ────────────────
        $subjects = Subject::all()->keyBy('code');

        // ─── Định nghĩa 5 lớp học phần ───────────────────────
        $sections = [
            [
                'subject_code'  => 'IT001',
                'lecturer_idx'  => 0,       // GV Sang
                'group'         => '01',
                'max_students'  => 40,
                'student_range' => [0, 9],  // SV 1-10
                'schedule' => [
                    ['day' => 2, 'start' => 1, 'end' => 3, 'room' => 'A101'], // Thứ 2
                    ['day' => 4, 'start' => 1, 'end' => 3, 'room' => 'A101'], // Thứ 4
                ],
            ],
            [
                'subject_code'  => 'IT002',
                'lecturer_idx'  => 1,       // GV Hai
                'group'         => '01',
                'max_students'  => 45,
                'student_range' => [2, 13], // SV 3-14 (overlap)
                'schedule' => [
                    ['day' => 3, 'start' => 1, 'end' => 3, 'room' => 'B201'], // Thứ 3
                    ['day' => 5, 'start' => 1, 'end' => 3, 'room' => 'B201'], // Thứ 5
                ],
            ],
            [
                'subject_code'  => 'IT003',
                'lecturer_idx'  => 2,       // GV Ba
                'group'         => '01',
                'max_students'  => 40,
                'student_range' => [0, 11], // SV 1-12
                'schedule' => [
                    ['day' => 2, 'start' => 4, 'end' => 6, 'room' => 'C301'], // Thứ 2
                    ['day' => 6, 'start' => 1, 'end' => 3, 'room' => 'C301'], // Thứ 6
                ],
            ],
            [
                'subject_code'  => 'IT004',
                'lecturer_idx'  => 0,       // GV Sang
                'group'         => '01',
                'max_students'  => 40,
                'student_range' => [5, 16], // SV 6-17
                'schedule' => [
                    ['day' => 3, 'start' => 4, 'end' => 6, 'room' => 'D102'], // Thứ 3
                    ['day' => 5, 'start' => 4, 'end' => 6, 'room' => 'D102'], // Thứ 5
                ],
            ],
            [
                'subject_code'  => 'IT005',
                'lecturer_idx'  => 1,       // GV Hai
                'group'         => '01',
                'max_students'  => 35,
                'student_range' => [8, 19], // SV 9-20
                'schedule' => [
                    ['day' => 4, 'start' => 4, 'end' => 6, 'room' => 'LAB01'], // Thứ 4
                    ['day' => 6, 'start' => 4, 'end' => 6, 'room' => 'LAB01'], // Thứ 6
                ],
            ],
        ];

        $createdCount = 0;

        foreach ($sections as $sectionData) {
            $subject = $subjects[$sectionData['subject_code']] ?? null;
            if (!$subject) {
                $this->command->warn("  ⚠ Không tìm thấy môn {$sectionData['subject_code']}. Bỏ qua.");
                continue;
            }

            // Lấy lecturer trong phạm vi an toàn
            $lecturerIdx = min($sectionData['lecturer_idx'], $lecturers->count() - 1);
            $lecturer = $lecturers[$lecturerIdx];

            // Tạo mã lớp: IT001-01-HK2-2526
            $termCode = 'HK' . $semester->term;
            $yearCode = sprintf('%02d%02d', $semester->year % 100, ($semester->year + 1) % 100);
            $code = "{$subject->code}-{$sectionData['group']}-{$termCode}-{$yearCode}";

            // Tạo invite_code duy nhất 6 ký tự
            $inviteCode = strtoupper(Str::random(6));

            // Tạo lớp học phần
            $section = CourseSection::updateOrCreate(
                ['code' => $code],
                [
                    'name'         => "{$subject->name} - Nhóm {$sectionData['group']}",
                    'invite_code'  => $inviteCode,
                    'subject_id'   => $subject->id,
                    'semester_id'  => $semester->id,
                    'lecturer_id'  => $lecturer->id,
                    'max_students' => $sectionData['max_students'],
                    'status'       => 'active',
                ]
            );

            // ─── Gán sinh viên (trong phạm vi an toàn) ────────
            [$startIdx, $endIdx] = $sectionData['student_range'];
            $endIdx = min($endIdx, $students->count() - 1);

            for ($i = $startIdx; $i <= $endIdx; $i++) {
                $student = $students[$i] ?? null;
                if ($student && !$section->students()->where('student_id', $student->id)->exists()) {
                    $section->students()->attach($student->id, [
                        'status'      => 'enrolled',
                        'enrolled_at' => now()->subDays(rand(1, 14)),
                    ]);
                }
            }

            // ─── Tạo lịch học (2 buổi/tuần) ──────────────────
            foreach ($sectionData['schedule'] as $scheduleData) {
                ClassSchedule::updateOrCreate(
                    [
                        'course_section_id' => $section->id,
                        'day_of_week'       => $scheduleData['day'],
                    ],
                    [
                        'start_period' => $scheduleData['start'],
                        'end_period'   => $scheduleData['end'],
                        'room'         => $scheduleData['room'],
                    ]
                );
            }

            $enrolledCount = $section->students()->wherePivot('status', 'enrolled')->count();
            $this->command->line("   📚 {$code} | GV: {$lecturer->name} | SV: {$enrolledCount}");
            $createdCount++;
        }

        $this->command->info("✅ Đã tạo {$createdCount} lớp học phần với lịch học và sinh viên đăng ký.");
    }
}
