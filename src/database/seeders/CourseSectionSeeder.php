<?php

namespace Database\Seeders;

use App\Models\CourseSection;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * ============================================================
 * CourseSectionSeeder — Tạo 5 lớp HP + gán SV đã onboarding
 * ============================================================
 * Quy tắc domain:
 * - Mỗi lớp học phần chỉ được gán cho giảng viên đã có phân công môn.
 * - Chỉ gán sinh viên đã có MSSV (đã hoàn tất onboarding).
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

        // ─── Lấy danh sách sinh viên đã hoàn tất onboarding ──
        $students = User::whereHas('roles', fn($q) => $q->where('name', 'student'))
            ->whereNotNull('student_code')
            ->orderBy('id')
            ->get();

        if ($students->count() < 8) {
            $this->command->warn('⚠ Cần ít nhất 8 sinh viên đã có MSSV. Bỏ qua CourseSectionSeeder.');
            return;
        }

        // ─── Lấy môn học + giảng viên được phân công ─────────
        $subjects = Subject::query()
            ->with([
                'lecturers' => fn($q) => $q
                    ->whereHas('roles', fn($roleQuery) => $roleQuery->where('name', 'lecturer'))
                    ->orderBy('users.id')
            ])
            ->get()
            ->keyBy('code');

        // ─── Định nghĩa 5 lớp học phần ───────────────────────
        $sections = [
            [
                'subject_code'  => 'IT001',
                'lecturer_code' => 'GV_001',
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
                'lecturer_code' => 'GV_002',
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
                'lecturer_code' => 'GV_003',
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
                'lecturer_code' => 'GV_001',
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
                'lecturer_code' => 'GV_002',
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

            $lecturer = $subject->lecturers
                ->firstWhere('lecturer_code', $sectionData['lecturer_code']);

            if (! $lecturer) {
                $this->command->warn(
                    "  ⚠ {$sectionData['lecturer_code']} chưa được phân công dạy {$sectionData['subject_code']}. Bỏ qua lớp này."
                );
                continue;
            }

            // Tạo mã lớp: IT001-01-HK2-2526
            $termCode = 'HK' . $semester->term;
            $yearCode = sprintf('%02d%02d', $semester->year % 100, ($semester->year + 1) % 100);
            $code = "{$subject->code}-{$sectionData['group']}-{$termCode}-{$yearCode}";

            // Invite code cố định theo mã lớp để seed idempotent.
            $inviteCode = strtoupper(substr(md5($code), 0, 6));

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

            $enrolledCount = $section->students()->wherePivot('status', 'enrolled')->count();
            $this->command->line("   📚 {$code} | GV: {$lecturer->name} | SV: {$enrolledCount}");
            $createdCount++;
        }

        $this->command->info("✅ Đã tạo {$createdCount} lớp học phần và sinh viên đăng ký.");
    }
}
