<?php

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Database\Seeder;

/**
 * ============================================================
 * SemesterSeeder — Tạo 3 học kỳ mẫu
 * ============================================================
 */
class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $semesters = [
            [
                'name' => 'HK1 2025-2026',
                'year' => 2025,
                'term' => 1,
                'start_date' => '2025-09-01',
                'end_date' => '2026-01-15',
                'status' => 'ended',
                'is_current' => false,
            ],
            [
                'name' => 'HK2 2025-2026',
                'year' => 2025,
                'term' => 2,
                'start_date' => '2026-02-17',
                'end_date' => '2026-06-30',
                'status' => 'current',
                'is_current' => true,
            ],
            [
                'name' => 'HK Hè 2025-2026',
                'year' => 2025,
                'term' => 3,
                'start_date' => '2026-07-06',
                'end_date' => '2026-08-28',
                'status' => 'upcoming',
                'is_current' => false,
            ],
        ];

        foreach ($semesters as $semester) {
            Semester::updateOrCreate(
                ['year' => $semester['year'], 'term' => $semester['term']],
                $semester
            );
        }

        $this->command->info('✓ Đã tạo 3 học kỳ (HK1, HK2, HK Hè 2025-2026).');
    }
}
