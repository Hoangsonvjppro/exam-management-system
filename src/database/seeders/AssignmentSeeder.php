<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * ============================================================
 * AssignmentSeeder — Phân công môn học cho giảng viên
 * ============================================================
 * Quy tắc domain:
 * - Giảng viên chỉ được dạy các môn đã được phân công.
 * - Dữ liệu seed sử dụng sync để đảm bảo idempotent.
 * ============================================================
 */
class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $assignmentMap = [
            'GV_001'      => ['IT001', 'IT004'],
            'GV_002'      => ['IT002', 'IT005'],
            'GV_003'      => ['IT003'],
        ];

        $totalAssignments = 0;

        foreach ($assignmentMap as $lecturerCode => $subjectCodes) {
            $lecturer = User::query()->where('lecturer_code', $lecturerCode)->first();

            if (! $lecturer) {
                $this->command->warn("⚠ Không tìm thấy giảng viên {$lecturerCode}. Bỏ qua phân công.");
                continue;
            }

            $subjectIds = Subject::query()
                ->whereIn('code', $subjectCodes)
                ->pluck('id')
                ->values()
                ->all();

            if (count($subjectIds) !== count($subjectCodes)) {
                $this->command->warn("⚠ Một số môn chưa tồn tại khi phân công cho {$lecturerCode}.");
            }

            $lecturer->subjects()->sync($subjectIds);
            $totalAssignments += count($subjectIds);

            $this->command->line("   📘 {$lecturerCode}: " . count($subjectIds) . ' môn');
        }

        $this->command->info("✅ Đã seed {$totalAssignments} phân công giảng viên-môn học.");
    }
}
