<?php

namespace Database\Seeders;

use App\Models\Difficulty;
use Illuminate\Database\Seeder;

class DifficultySeeder extends Seeder
{
    public function run(): void
    {
        $difficulties = [
            [
                'code' => 'remember',
                'name' => 'Nhận biết',
                'score_weight' => 1.0,
            ],
            [
                'code' => 'understand',
                'name' => 'Thông hiểu',
                'score_weight' => 1.5,
            ],
            [
                'code' => 'apply',
                'name' => 'Vận dụng',
                'score_weight' => 2.5,
            ],
            [
                'code' => 'analyze',
                'name' => 'Vận dụng cao',
                'score_weight' => 4.0,
            ],
        ];

        foreach ($difficulties as $difficulty) {
            Difficulty::updateOrCreate(
                ['code' => $difficulty['code']],
                $difficulty
            );
        }

        $this->command->info('✓ Đã tạo 4 mức độ khó tiêu chuẩn.');
    }
}
