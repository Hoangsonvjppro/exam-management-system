<?php

namespace Database\Seeders;

use App\Models\QuestionType;
use Illuminate\Database\Seeder;

/**
 * ============================================================
 * QuestionTypeSeeder — Tạo 6 loại câu hỏi
 * ============================================================
 * Dữ liệu khớp với schema SQL v3.1.
 * ============================================================
 */
class QuestionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'single_choice',
                'name' => 'Trắc nghiệm 1 lựa chọn',
                'description' => 'Câu hỏi có nhiều lựa chọn, nhưng chỉ 1 đáp án đúng.',
                'is_auto_grade' => true,
                'display_order' => 1,
                'answer_schema' => json_encode([
                    'type' => 'single_select',
                    'source' => 'question_options',
                ]),
            ],
            [
                'code' => 'multiple_choice',
                'name' => 'Trắc nghiệm nhiều đáp án đúng',
                'description' => 'Câu hỏi có nhiều lựa chọn, có thể có nhiều đáp án đúng.',
                'is_auto_grade' => true,
                'display_order' => 2,
                'answer_schema' => json_encode([
                    'type' => 'multi_select',
                    'source' => 'question_options',
                ]),
            ]
        ];

        foreach ($types as $type) {
            QuestionType::updateOrCreate(['code' => $type['code']], $type);
        }

        $this->command->info('✓ Đã khởi tạo cấu hình loại câu hỏi mới (Single/Multi-choice).');
    }
}
