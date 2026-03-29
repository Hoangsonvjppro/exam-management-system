<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy giảng viên Sang làm người tạo câu hỏi
        $lecturer = User::where('email', 'Sang@gmail.com')->first();
        if (!$lecturer) {
            $this->command->error('⚠ Không tìm thấy GV Sang@gmail.com. Hãy chạy UserSeeder trước.');
            return;
        }

        // Lấy type Trắc nghiệm (Multiple Choice)
        $mcqType = QuestionType::where('code', 'multiple_choice')->first();
        if (!$mcqType) {
            $this->command->error('⚠ Không tìm thấy Question Type. Hãy chạy QuestionTypeSeeder trước.');
            return;
        }

        $subjects = Subject::with('chapters')->get();
        $difficulties = ['remember', 'understand', 'apply', 'analyze'];
        $totalCreated = 0;

        foreach ($subjects as $subject) {
            $chapters = $subject->chapters;
            if ($chapters->isEmpty()) continue;

            // Mỗi môn học tạo đúng 500 câu hỏi
            for ($i = 1; $i <= 500; $i++) {
                // Chọn random 1 chương trong môn học này để gán câu hỏi
                $randomChapter = $chapters->random();
                $difficulty = $difficulties[array_rand($difficulties)];

                // Nội dung câu hỏi linh hoạt theo tên môn và chương để UI nhìn rất thật
                $questionContent = "Câu hỏi #{$i}: Trong ngữ cảnh môn {$subject->name}, phần {$randomChapter->name}, hãy chọn phương án đúng nhất?";

                $question = Question::create([
                    'subject_id' => $subject->id,
                    'chapter_id' => $randomChapter->id,
                    'question_type_id' => $mcqType->id,
                    'created_by' => $lecturer->id,
                    'difficulty' => $difficulty,
                    'content' => $questionContent,
                    'explanation' => "Giải thích chi tiết cho câu hỏi số {$i} của môn {$subject->code}.",
                    'status' => 'approved',
                    'version' => 1,
                ]);

                // Tạo 4 đáp án (A, B, C, D) và chọn ngẫu nhiên 1 đáp án đúng
                $correctOptionIndex = rand(0, 3);
                $labels = ['A', 'B', 'C', 'D'];

                foreach ($labels as $index => $label) {
                    $isCorrect = ($index === $correctOptionIndex);
                    $optionContent = $isCorrect
                        ? "Đây là phương án ĐÚNG của câu hỏi {$i}."
                        : "Đây là phương án SAI (gây nhiễu) số " . ($index + 1);

                    QuestionOption::create([
                        'question_id' => $question->id,
                        'label' => $label,
                        'content' => $optionContent,
                        'is_correct' => $isCorrect,
                        'order' => $index,
                    ]);
                }

                $totalCreated++;
            }
        }

        $this->command->info("✅ Đã tạo thành công {$totalCreated} câu hỏi trắc nghiệm (chia đều 500 câu/môn).");
    }
}