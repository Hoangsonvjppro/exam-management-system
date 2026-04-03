<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\Subject;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================
 * QuestionSeeder — Tạo 150 câu hỏi (30 câu/môn × 5 môn)
 * ============================================================
 */
class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Lấy các question types ──────────────────────────
        $singleType = QuestionType::where('code', 'single_choice')->first();

        if (!$singleType) {
            $this->command->error('⚠ Thiếu question types. Hãy chạy QuestionTypeSeeder trước.');
            return;
        }

        // ─── Lấy tags ────────────────────────────────────────
        $allTags = Tag::all();
        $tagsByName = $allTags->keyBy('name');

        // ─── Định nghĩa tag mapping cho mỗi môn ─────────────
        $subjectTagMap = [
            'IT001' => ['Lập trình', 'C++'],
            'IT002' => ['Cấu trúc dữ liệu', 'Thuật toán'],
            'IT003' => ['Cơ sở dữ liệu', 'SQL'],
            'IT004' => ['Mạng máy tính', 'TCP/IP'],
            'IT005' => ['Lập trình Web', 'Laravel'],
        ];

        // ─── Câu hỏi mẫu thực tế cho từng môn ──────────────
        $questionBank = $this->getQuestionBank();

        $subjects = Subject::query()
            ->with([
                'chapters',
                'lecturers' => fn($query) => $query
                    ->whereHas('roles', fn($roleQuery) => $roleQuery->where('name', 'lecturer'))
                    ->orderBy('users.id'),
            ])
            ->get();

        $difficulties = ['remember', 'understand', 'apply', 'analyze'];
        $totalCreated = 0;

        foreach ($subjects as $subjectIndex => $subject) {
            $chapters = $subject->chapters;
            if ($chapters->isEmpty()) {
                $this->command->warn("   ⚠️ Môn {$subject->code} chưa có chương, bỏ qua.");
                continue;
            }

            $assignedLecturers = $subject->lecturers->values();
            if ($assignedLecturers->isEmpty()) {
                $this->command->warn("   ⚠️ Môn {$subject->code} chưa có giảng viên được phân công, bỏ qua.");
                continue;
            }

            // Chọn giảng viên trong tập đã phân công môn.
            $lecturer = $assignedLecturers[$subjectIndex % $assignedLecturers->count()];

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

                // Tạo câu hỏi
                if ($i <= 22) {
                    $question = $this->createMCQ($subject, $chapter, $singleType, $lecturer, $difficulty, $i, $bankQuestions);
                } else {
                    $question = $this->createTrueFalse($subject, $chapter, $singleType, $lecturer, $difficulty, $i, $bankQuestions);
                }

                // ─── Gán tags ────────────
                if ($question && $subjectTags->isNotEmpty()) {
                    $tagsToAttach = $subjectTags->random(min(1, $subjectTags->count()));

                    // Thêm tag mục đích (Thi giữa kỳ / cuối kỳ)
                    $purposeName = ($i <= 15) ? 'Thi giữa kỳ' : 'Thi cuối kỳ';
                    if (isset($tagsByName[$purposeName])) {
                        $tagsToAttach->push($tagsByName[$purposeName]->id);
                    }

                    foreach ($tagsToAttach->unique() as $tagId) {
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

        $this->command->info("✅ Đã tạo thành công {$totalCreated} câu hỏi.");
    }

    private function createMCQ($subject, $chapter, $questionType, $lecturer, $difficulty, $index, $bank): ?Question
    {
        $content = $bank['mcq'][$index - 1] ?? "Cần nắm vững nội dung chương \"{$chapter->name}\". Đâu là khái niệm chính xác nhất về chủ đề này?";

        $question = Question::create([
            'subject_id'       => $subject->id,
            'chapter_id'       => $chapter->id,
            'question_type_id' => $questionType->id,
            'created_by'       => $lecturer->id,
            'difficulty'       => $difficulty,
            'content'          => $content,
            'version'          => 1,
        ]);

        $correctIdx = rand(0, 3);
        $labels = ['A', 'B', 'C', 'D'];

        foreach ($labels as $idx => $label) {
            $isCorrect = ($idx === $correctIdx);
            $optContent = $isCorrect ? "Đáp án chính xác cho câu số {$index}" : "Phương án nhiễu {$label} cho câu {$index}";

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

    private function createTrueFalse($subject, $chapter, $questionType, $lecturer, $difficulty, $index, $bank): ?Question
    {
        $tfIndex = $index - 23;
        $content = $bank['tf'][$tfIndex] ?? "Trong môn {$subject->name}, nội dung thuộc chương \"{$chapter->name}\" là bắt buộc phải khảo sát đúng không?";

        $isTrue = (bool) rand(0, 1);

        $question = Question::create([
            'subject_id'       => $subject->id,
            'chapter_id'       => $chapter->id,
            'question_type_id' => $questionType->id,
            'created_by'       => $lecturer->id,
            'difficulty'       => $difficulty,
            'content'          => $content,
            'version'          => 1,
        ]);

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

    private function getQuestionBank(): array
    {
        return [
            'IT001' => [
                'mcq' => [
                    'Sự khác biệt chính giữa C và C++ là gì?',
                    'Từ khóa nào được dùng để khai báo một lớp (class) trong C++?',
                    'Phạm vi truy cập mặc định của các thành phần trong class là gì?',
                    'Đâu là cú pháp đúng để khai báo một hàm ảo (virtual function)?',
                    'Template trong C++ dùng để làm gì?',
                    'Hàm tạo (Constructor) được gọi khi nào?',
                    'Đa kế thừa là gì?',
                    'Toán tử scope resolution là toán tử nào?',
                    'Từ khóa "this" trong C++ là gì?',
                    'File header nào thường dùng cho nhập xuất cơ bản?',
                    'Làm thế nào để giải phóng bộ nhớ của một mảng động?',
                    'Namespace dùng để làm gì?',
                    'Cách khai báo một phương thức tĩnh (static method)?',
                    'Friend function có quyền truy cập vào thành phần nào của lớp?',
                    'Hàm hủy (Destructor) bắt đầu bằng ký tự nào?',
                    'Inline function có tác dụng gì?',
                    'Khai báo con trỏ hằng như thế nào?',
                    'STL là viết tắt của gì?',
                    'Kiểu dữ liệu bool trong C++ chiếm bao nhiêu byte?',
                    'Hằng số (const) khác gì với define?',
                    'Ngoại lệ (Exception) được xử lý bằng khối lệnh nào?',
                    'Toán tử NEW trả về kết quả gì?',
                ],
                'tf' => [
                    'C++ hỗ trợ lập trình hướng đối tượng tốt hơn C.',
                    'Số lượng constructor trong một lớp là không giới hạn.',
                    'Hàm ảo không thể là hàm tĩnh.',
                    'Copy constructor được gọi khi gán 2 đối tượng có sẵn.',
                    'Một lớp có thể có nhiều hàm hủy.',
                    'Biến tĩnh được chia sẻ giữa tất cả các đối tượng của lớp.',
                    'Toán tử gán mặc định thực hiện shallow copy.',
                    'Con trỏ null trong C++ dùng từ khóa nullptr.',
                ]
            ],
            'IT002' => [
                'mcq' => [
                    'Độ phức tạp thời gian của thuật toán Quicksort trung bình là?',
                    'Cấu trúc dữ liệu nào hoạt động theo nguyên tắc LIFO?',
                    'Hàng chờ (Queue) hoạt động theo nguyên tắc nào?',
                    'Đâu là đặc điểm của Danh sách liên kết đơn?',
                    'Độ cao của cây nhị phân đầy đủ có N nút là?',
                    'Thuật toán tìm kiếm nhị phân yêu cầu mảng phải như thế nào?',
                    'Đồ thị có hướng khác đồ thị vô hướng ở điểm nào?',
                    'Bảng băm (Hash table) giải quyết xung đột bằng cách nào?',
                    'Duyệt cây theo thứ tự trước (Pre-order) là?',
                    'Giải thuật tham lam (Greedy) luôn cho kết quả tối ưu toàn cục đúng hay sai?',
                ],
                'tf' => [
                    'Mảng có tốc độ truy cập ngẫu nhiên nhanh nhất.',
                    'Stack có thể dùng để khử đệ quy.',
                    'Duyệt cây theo chiều rộng (BFS) dùng Stack.',
                    'Thuật toán Dijkstra dùng để tìm đường đi ngắn nhất.',
                ]
            ],
            'IT003' => [
                'mcq' => [
                    'SQL là viết tắt của cụm từ nào?',
                    'Khóa ngoại (Foreign Key) dùng để làm gì?',
                    'Câu lệnh nào dùng để cập nhật dữ liệu?',
                    'Mệnh đề nào dùng để lọc kết quả theo nhóm?',
                    'Chuẩn hóa cơ sở dữ liệu giúp giảm thiểu điều gì?',
                    'Hệ quản trị CSDL quan hệ phổ biến nhất là?',
                    'Ràng buộc UNIQUE khác gì với PRIMARY KEY?',
                    'Transaction đảm bảo tính chất nào?',
                    'Index giúp tăng tốc độ cho thao tác nào?',
                    'Câu lệnh DELETE khác TRUNCATE ở điểm nào?',
                ],
                'tf' => [
                    'Một bảng chỉ có duy nhất một khóa chính.',
                    'Khóa ngoại có thể nhận giá trị NULL.',
                    'SQL không phân biệt hoa thường trong dữ liệu chuỗi.',
                    'Toán tử LIKE dùng để tìm kiếm gần đúng.',
                ]
            ],
            'IT004' => [
                'mcq' => [
                    'Tầng nào trong mô hình OSI chịu trách nhiệm định tuyến?',
                    'Giao thức TCP thuộc tầng nào?',
                    'Địa chỉ IP v4 có bao nhiêu bit?',
                    'HTTP mặc định chạy trên cổng nào?',
                    'DNS dùng để làm gì?',
                    'Thiết bị Switch hoạt động ở tầng nào?',
                    'Mặt nạ mạng (Subnet Mask) dùng để làm gì?',
                    'ICMP được dùng bởi lệnh nào?',
                    'Sự khác biệt giữa TCP và UDP là gì?',
                    'Tầng vật lý xử lý dữ liệu dưới dạng nào?',
                ],
                'tf' => [
                    'IPv6 có không gian địa chỉ lớn hơn IPv4.',
                    'Router là thiết bị tầng 2.',
                    'Hub gửi dữ liệu đến tất cả các cổng (Broadcast).',
                    'Giao thức FTP dùng 2 cổng để truyền dữ liệu.',
                ]
            ],
            'IT005' => [
                'mcq' => [
                    'Laravel là framework của ngôn ngữ nào?',
                    'Tập tin cấu hình chính của Laravel là?',
                    'Eloquent ORM dùng để làm gì?',
                    'Middleware trong Laravel có tác dụng gì?',
                    'Blade là gì trong Laravel?',
                    'Lệnh artisan nào tạo một Controller mới?',
                    'Migration được dùng để quản lý cái gì?',
                    'Route nào dùng để xử lý phương thức POST?',
                    'Composer là công cụ gì?',
                    'Environment variable được lưu ở file nào?',
                ],
                'tf' => [
                    'Laravel dùng mô hình MVC.',
                    'View trong Laravel nằm ở thư mục resources/views.',
                    'Cột created_at và updated_at được Laravel tự động quản lý.',
                    'Laravel hỗ trợ nhiều hệ quản trị CSDL cùng lúc.',
                ]
            ],
        ];
    }
}
