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
                'code' => 'multiple_choice',
                'name' => 'Trắc nghiệm nhiều lựa chọn',
                'description' => 'Câu hỏi có 2-6 lựa chọn, chỉ 1 đáp án đúng. Đáp án lưu trong question_options.',
                'is_auto_grade' => true,
                'display_order' => 1,
                'answer_schema' => json_encode([
                    'type' => 'single_select',
                    'source' => 'question_options',
                ]),
            ],
            [
                'code' => 'multiple_answer',
                'name' => 'Trắc nghiệm nhiều đáp án đúng',
                'description' => 'Câu hỏi có nhiều lựa chọn, có thể có nhiều đáp án đúng.',
                'is_auto_grade' => true,
                'display_order' => 2,
                'answer_schema' => json_encode([
                    'type' => 'multi_select',
                    'source' => 'question_options',
                ]),
            ],
            [
                'code' => 'true_false',
                'name' => 'Đúng / Sai',
                'description' => 'Câu hỏi chỉ có 2 lựa chọn: Đúng hoặc Sai.',
                'is_auto_grade' => true,
                'display_order' => 3,
                'answer_schema' => json_encode([
                    'type' => 'single_select',
                    'source' => 'question_options',
                    'fixed_options' => ['Đúng', 'Sai'],
                ]),
            ],
            [
                'code' => 'fill_blank',
                'name' => 'Điền vào chỗ trống',
                'description' => 'Sinh viên nhập câu trả lời ngắn. Đáp án đúng lưu trong questions.answer_data.',
                'is_auto_grade' => true,
                'display_order' => 4,
                'answer_schema' => json_encode([
                    'type' => 'text_input',
                    'source' => 'answer_data',
                    'schema' => [
                        'accepted_answers' => ['string'],
                        'case_sensitive' => false,
                    ],
                ]),
            ],
            [
                'code' => 'matching',
                'name' => 'Ghép cặp',
                'description' => 'Ghép các mục bên trái với bên phải.',
                'is_auto_grade' => true,
                'display_order' => 5,
                'answer_schema' => json_encode([
                    'type' => 'matching_pairs',
                    'source' => 'answer_data',
                    'schema' => [
                        'pairs' => [['left' => 'string', 'right' => 'string']],
                    ],
                ]),
            ],
            [
                'code' => 'essay',
                'name' => 'Tự luận',
                'description' => 'Sinh viên viết câu trả lời dạng văn bản dài. Cần giảng viên chấm tay.',
                'is_auto_grade' => false,
                'display_order' => 6,
                'answer_schema' => json_encode([
                    'type' => 'long_text',
                    'source' => 'answer_text',
                    'schema' => [
                        'max_words' => null,
                        'rubric' => 'answer_data',
                    ],
                ]),
            ],
        ];

        foreach ($types as $type) {
            QuestionType::updateOrCreate(['code' => $type['code']], $type);
        }

        $this->command->info('✓ Đã tạo 6 loại câu hỏi.');
    }
}
