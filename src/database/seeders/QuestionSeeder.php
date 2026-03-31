<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\Subject;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================
 * QuestionSeeder — Tạo 150 câu hỏi (30 câu/môn × 5 môn)
 * ============================================================
 * Đa dạng loại câu hỏi: MCQ, True/False, Fill Blank.
 * Gán tags qua bảng question_tag_map.
 * Nội dung câu hỏi thực tế theo từng môn học.
 * ============================================================
 */
class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Lấy giảng viên làm người tạo ────────────────────
        $lecturers = User::whereHas('roles', fn($q) => $q->where('name', 'lecturer'))
            ->orderBy('id')->get();

        if ($lecturers->isEmpty()) {
            $this->command->error('⚠ Không tìm thấy giảng viên. Hãy chạy UserSeeder trước.');
            return;
        }

        // ─── Lấy các question types ──────────────────────────
        $mcqType   = QuestionType::where('code', 'multiple_choice')->first();
        $tfType    = QuestionType::where('code', 'true_false')->first();
        $fillType  = QuestionType::where('code', 'fill_blank')->first();

        if (!$mcqType || !$tfType || !$fillType) {
            $this->command->error('⚠ Thiếu question types. Hãy chạy QuestionTypeSeeder trước.');
            return;
        }

        // ─── Lấy tags ────────────────────────────────────────
        $allTags = Tag::all();
        $tagsByName = $allTags->keyBy('name');

        // ─── Định nghĩa tag mapping cho mỗi môn ─────────────
        $subjectTagMap = [
            'IT001' => ['Lập trình', 'OOP', 'Cơ bản'],
            'IT002' => ['Cấu trúc dữ liệu', 'Thuật toán', 'Nâng cao'],
            'IT003' => ['Cơ sở dữ liệu', 'SQL', 'Cơ bản'],
            'IT004' => ['Mạng máy tính', 'TCP/IP', 'Cơ bản'],
            'IT005' => ['Lập trình Web', 'OOP', 'Nâng cao'],
        ];

        // ─── Câu hỏi mẫu thực tế cho từng môn ──────────────
        $questionBank = $this->getQuestionBank();

        $subjects = Subject::with('chapters')->get();
        $difficulties = ['remember', 'understand', 'apply', 'analyze'];
        $totalCreated = 0;

        foreach ($subjects as $subject) {
            $chapters = $subject->chapters;
            if ($chapters->isEmpty()) continue;

            // Lấy giảng viên tạo câu hỏi (xoay vòng)
            $lecturer = $lecturers[$totalCreated % $lecturers->count()];

            // Lấy câu hỏi mẫu cho môn này
            $bankQuestions = $questionBank[$subject->code] ?? [];

            // Lấy tag IDs cho môn này
            $subjectTags = collect($subjectTagMap[$subject->code] ?? [])
                ->map(fn($name) => $tagsByName[$name]->id ?? null)
                ->filter()
                ->values();

            for ($i = 1; $i <= 30; $i++) {
                $chapter = $chapters[($i - 1) % $chapters->count()];
                $difficulty = $difficulties[($i - 1) % count($difficulties)];

                // Chọn loại câu hỏi: 20 MCQ + 6 T/F + 4 Fill
                if ($i <= 20) {
                    $questionType = $mcqType;
                    $question = $this->createMCQ($subject, $chapter, $questionType, $lecturer, $difficulty, $i, $bankQuestions);
                } elseif ($i <= 26) {
                    $questionType = $tfType;
                    $question = $this->createTrueFalse($subject, $chapter, $questionType, $lecturer, $difficulty, $i, $bankQuestions);
                } else {
                    $questionType = $fillType;
                    $question = $this->createFillBlank($subject, $chapter, $questionType, $lecturer, $difficulty, $i, $bankQuestions);
                }

                // ─── Gán tags qua question_tag_map ────────────
                if ($question && $subjectTags->isNotEmpty()) {
                    // Gán 2 tag cho mỗi câu hỏi
                    $tagsToAttach = $subjectTags->random(min(2, $subjectTags->count()));
                    // Thêm tag theo mục đích sử dụng
                    $purposeTag = ($i <= 15)
                        ? ($tagsByName['Thi giữa kỳ'] ?? null)
                        : ($tagsByName['Thi cuối kỳ'] ?? null);
                    if ($purposeTag) {
                        $tagsToAttach = $tagsToAttach->push($purposeTag->id)->unique();
                    }

                    foreach ($tagsToAttach as $tagId) {
                        DB::table('question_tag_map')->insertOrIgnore([
                            'question_id' => $question->id,
                            'tag_id'      => $tagId,
                        ]);
                    }
                }

                $totalCreated++;
            }

            $this->command->line("   📝 {$subject->code} — {$subject->name}: 30 câu hỏi");
        }

        $this->command->info("✅ Đã tạo thành công {$totalCreated} câu hỏi (30 câu/môn × 5 môn).");
    }

    // ─── Tạo câu MCQ ─────────────────────────────────────────
    private function createMCQ($subject, $chapter, $questionType, $lecturer, $difficulty, $index, $bank): ?Question
    {
        $content = $bank['mcq'][$index - 1]
            ?? "Trong môn {$subject->name}, phần \"{$chapter->name}\", phương án nào sau đây là đúng?";

        $question = Question::create([
            'subject_id'       => $subject->id,
            'chapter_id'       => $chapter->id,
            'question_type_id' => $questionType->id,
            'created_by'       => $lecturer->id,
            'difficulty'       => $difficulty,
            'content'          => $content,
            'explanation'      => "Giải thích: Đây là câu hỏi số {$index} thuộc chương \"{$chapter->name}\" của môn {$subject->code}.",
            'status'           => 'approved',
            'version'          => 1,
        ]);

        // Tạo 4 options (A, B, C, D)
        $correctIdx = rand(0, 3);
        $labels = ['A', 'B', 'C', 'D'];
        $optionContents = $bank['mcq_options'][$index - 1]
            ?? null;

        foreach ($labels as $idx => $label) {
            $isCorrect = ($idx === $correctIdx);

            if ($optionContents) {
                $optContent = $isCorrect ? $optionContents['correct'] : ($optionContents['wrong'][$idx] ?? "Phương án {$label} (sai)");
            } else {
                $optContent = $isCorrect
                    ? "Đáp án đúng cho câu {$index}"
                    : "Phương án nhiễu {$label} cho câu {$index}";
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'label'       => $label,
                'content'     => $optContent,
                'is_correct'  => $isCorrect,
                'order'       => $idx,
            ]);
        }

        return $question;
    }

    // ─── Tạo câu True/False ──────────────────────────────────
    private function createTrueFalse($subject, $chapter, $questionType, $lecturer, $difficulty, $index, $bank): ?Question
    {
        $tfIndex = $index - 21; // 0-based cho T/F
        $content = $bank['tf'][$tfIndex]
            ?? "Nhận định: Trong phần \"{$chapter->name}\" của môn {$subject->name}, kiến thức cơ bản luôn là nền tảng quan trọng nhất.";

        $isTrue = (bool) rand(0, 1);

        $question = Question::create([
            'subject_id'       => $subject->id,
            'chapter_id'       => $chapter->id,
            'question_type_id' => $questionType->id,
            'created_by'       => $lecturer->id,
            'difficulty'       => $difficulty,
            'content'          => $content,
            'explanation'      => $isTrue ? 'Nhận định này là ĐÚNG.' : 'Nhận định này là SAI.',
            'status'           => 'approved',
            'version'          => 1,
        ]);

        // Tạo 2 options: Đúng / Sai
        QuestionOption::create([
            'question_id' => $question->id,
            'label'       => 'A',
            'content'     => 'Đúng',
            'is_correct'  => $isTrue,
            'order'       => 0,
        ]);
        QuestionOption::create([
            'question_id' => $question->id,
            'label'       => 'B',
            'content'     => 'Sai',
            'is_correct'  => !$isTrue,
            'order'       => 1,
        ]);

        return $question;
    }

    // ─── Tạo câu Fill Blank ──────────────────────────────────
    private function createFillBlank($subject, $chapter, $questionType, $lecturer, $difficulty, $index, $bank): ?Question
    {
        $fbIndex = $index - 27; // 0-based cho fill blank
        $content = $bank['fill'][$fbIndex]
            ?? "Trong môn {$subject->name}, phần \"{$chapter->name}\", từ khóa quan trọng nhất là _____.";

        $acceptedAnswers = $bank['fill_answers'][$fbIndex]
            ?? [$chapter->name, strtolower($chapter->name)];

        $question = Question::create([
            'subject_id'       => $subject->id,
            'chapter_id'       => $chapter->id,
            'question_type_id' => $questionType->id,
            'created_by'       => $lecturer->id,
            'difficulty'       => $difficulty,
            'content'          => $content,
            'explanation'      => "Đáp án chấp nhận: " . implode(', ', $acceptedAnswers),
            'answer_data'      => [
                'accepted_answers' => $acceptedAnswers,
                'case_sensitive'   => false,
            ],
            'status'           => 'approved',
            'version'          => 1,
        ]);

        return $question;
    }

    // ─── Ngân hàng câu hỏi mẫu thực tế ─────────────────────
    private function getQuestionBank(): array
    {
        return [
            'IT001' => [
                'mcq' => [
                    'Trong C++, kiểu dữ liệu nào dùng để lưu số nguyên?',
                    'Toán tử nào dùng để so sánh bằng trong C++?',
                    'Vòng lặp nào kiểm tra điều kiện trước khi thực thi?',
                    'Hàm main() trong C++ trả về kiểu gì?',
                    'Đâu là cách khai báo mảng đúng trong C++?',
                    'Toán tử ++ trong C++ có chức năng gì?',
                    'Cấu trúc switch-case dùng để làm gì?',
                    'Từ khóa nào dùng để khai báo hằng số trong C++?',
                    'Đâu là comment đúng trong C++?',
                    'Hàm trong C++ được định nghĩa bởi những thành phần nào?',
                    'Đâu là cú pháp đúng của câu lệnh if-else?',
                    'Toán tử && trong C++ là gì?',
                    'Kiểu float lưu trữ bao nhiêu byte?',
                    'Đâu là cú pháp khai báo con trỏ?',
                    'Vòng lặp do-while khác while ở điểm nào?',
                    'Đâu là toán tử gán trong C++?',
                    'Header file iostream dùng để làm gì?',
                    'Từ khóa break dùng trong ngữ cảnh nào?',
                    'Mảng 2 chiều được khai báo như thế nào?',
                    'Hàm void có đặc điểm gì?',
                ],
                'tf' => [
                    'Trong C++, biến phải được khai báo trước khi sử dụng.',
                    'C++ là ngôn ngữ lập trình hướng đối tượng.',
                    'Mảng trong C++ có thể thay đổi kích thước sau khi khai báo.',
                    'Vòng lặp for và while có thể thay thế cho nhau.',
                    'Hàm trong C++ chỉ có thể trả về một giá trị.',
                    'Toán tử = và == có cùng chức năng.',
                ],
                'fill' => [
                    'Để xuất dữ liệu ra màn hình trong C++, ta sử dụng đối tượng _____.',
                    'Vòng lặp _____ luôn thực hiện ít nhất 1 lần.',
                    'Từ khóa _____ dùng để thoát khỏi vòng lặp.',
                    'Trong C++, _____ là kiểu dữ liệu lưu ký tự.',
                ],
                'fill_answers' => [
                    ['cout', 'std::cout'],
                    ['do-while', 'do while'],
                    ['break'],
                    ['char'],
                ],
            ],
            'IT003' => [
                'mcq' => [
                    'Mô hình ER dùng để làm gì?',
                    'Khóa chính (Primary Key) có đặc điểm gì?',
                    'Câu lệnh SQL nào dùng để truy vấn dữ liệu?',
                    'JOIN trong SQL dùng để làm gì?',
                    'Chuẩn hóa 1NF yêu cầu gì?',
                    'Chuẩn hóa 2NF giải quyết vấn đề gì?',
                    'Câu lệnh nào tạo bảng mới trong SQL?',
                    'WHERE trong SQL có chức năng gì?',
                    'GROUP BY dùng kết hợp với gì?',
                    'INDEX trong database có tác dụng gì?',
                ],
                'tf' => [
                    'Mỗi bảng trong CSDL quan hệ phải có khóa chính.',
                    'SQL là ngôn ngữ lập trình hướng đối tượng.',
                    'VIEW trong SQL lưu dữ liệu vật lý.',
                    'NULL trong SQL nghĩa là giá trị 0.',
                ],
                'fill' => [
                    'Câu lệnh _____ dùng để chèn dữ liệu mới vào bảng.',
                    'Để xóa toàn bộ dữ liệu trong bảng mà không xóa cấu trúc, dùng lệnh _____.',
                ],
                'fill_answers' => [
                    ['INSERT', 'INSERT INTO'],
                    ['TRUNCATE', 'TRUNCATE TABLE', 'DELETE'],
                ],
            ],
        ];
    }
}