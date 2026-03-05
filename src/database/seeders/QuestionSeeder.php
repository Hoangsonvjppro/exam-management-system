<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * ============================================================
 * QuestionSeeder — Tạo 50+ câu hỏi mẫu IT thực tế
 * ============================================================
 * Phân bổ câu hỏi theo môn học và chương.
 * Gồm: Multiple Choice + True/False.
 * Nội dung câu hỏi liên quan đến kiến thức CNTT thực tế.
 * ============================================================
 */
class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy giảng viên đầu tiên làm creator
        $lecturer = User::whereHas('roles', fn($q) => $q->where('code', 'lecturer'))->first();
        if (!$lecturer) {
            $this->command->warn('⚠ Không tìm thấy giảng viên. Bỏ qua QuestionSeeder.');
            return;
        }

        $mcqType = QuestionType::where('code', 'multiple_choice')->first();
        $tfType = QuestionType::where('code', 'true_false')->first();

        if (!$mcqType || !$tfType) {
            $this->command->warn('⚠ Không tìm thấy loại câu hỏi. Bỏ qua QuestionSeeder.');
            return;
        }

        $questions = $this->getQuestionData();
        $createdCount = 0;

        foreach ($questions as $qData) {
            $subject = Subject::where('code', $qData['subject_code'])->first();
            if (!$subject)
                continue;

            $chapter = null;
            if (!empty($qData['chapter_order'])) {
                $chapter = Chapter::where('subject_id', $subject->id)
                    ->where('order', $qData['chapter_order'])
                    ->first();
            }

            $typeModel = $qData['type'] === 'mcq' ? $mcqType : $tfType;

            $question = Question::updateOrCreate(
                [
                    'subject_id' => $subject->id,
                    'content' => $qData['content'],
                ],
                [
                    'chapter_id' => $chapter?->id,
                    'question_type_id' => $typeModel->id,
                    'created_by' => $lecturer->id,
                    'difficulty' => $qData['difficulty'],
                    'explanation' => $qData['explanation'] ?? null,
                    'status' => 'approved',
                    'version' => 1,
                ]
            );

            // Tạo options
            if (!$question->options()->exists()) {
                foreach ($qData['options'] as $index => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'label' => $opt['label'],
                        'content' => $opt['content'],
                        'is_correct' => $opt['is_correct'],
                        'order' => $index,
                    ]);
                }
            }

            $createdCount++;
        }

        $this->command->info("✓ Đã tạo {$createdCount} câu hỏi mẫu (MCQ + True/False).");
    }

    /**
     * Dữ liệu câu hỏi IT thực tế.
     */
    private function getQuestionData(): array
    {
        return [
            // ═══════════════════════════════════════════════════
            // CS101 — Nhập môn Lập trình (10 câu)
            // ═══════════════════════════════════════════════════
            [
                'subject_code' => 'CS101',
                'chapter_order' => 2,
                'type' => 'mcq',
                'difficulty' => 'remember',
                'content' => 'Trong ngôn ngữ C, kiểu dữ liệu nào sau đây dùng để lưu số thực?',
                'explanation' => 'float và double dùng để lưu số thực. int chỉ lưu số nguyên, char lưu ký tự.',
                'options' => [
                    ['label' => 'A', 'content' => 'int', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'float', 'is_correct' => true],
                    ['label' => 'C', 'content' => 'char', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'bool', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS101',
                'chapter_order' => 3,
                'type' => 'mcq',
                'difficulty' => 'understand',
                'content' => 'Cho đoạn mã: <code>int x = 10; if (x > 5) x = x * 2; else x = x + 1;</code>. Giá trị của x sau khi thực thi là bao nhiêu?',
                'explanation' => 'x = 10 > 5 nên vào nhánh if: x = 10 * 2 = 20.',
                'options' => [
                    ['label' => 'A', 'content' => '10', 'is_correct' => false],
                    ['label' => 'B', 'content' => '11', 'is_correct' => false],
                    ['label' => 'C', 'content' => '20', 'is_correct' => true],
                    ['label' => 'D', 'content' => '21', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS101',
                'chapter_order' => 4,
                'type' => 'mcq',
                'difficulty' => 'apply',
                'content' => 'Vòng lặp <code>for(int i = 0; i < 5; i++)</code> sẽ lặp bao nhiêu lần?',
                'explanation' => 'i chạy từ 0 đến 4 (i < 5), tổng 5 lần lặp.',
                'options' => [
                    ['label' => 'A', 'content' => '4 lần', 'is_correct' => false],
                    ['label' => 'B', 'content' => '5 lần', 'is_correct' => true],
                    ['label' => 'C', 'content' => '6 lần', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'Vô hạn', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS101',
                'chapter_order' => 5,
                'type' => 'mcq',
                'difficulty' => 'remember',
                'content' => 'Hàm strlen() trong C dùng để làm gì?',
                'explanation' => 'strlen() trả về chiều dài chuỗi (không tính ký tự null terminator).',
                'options' => [
                    ['label' => 'A', 'content' => 'So sánh hai chuỗi', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'Nối hai chuỗi', 'is_correct' => false],
                    ['label' => 'C', 'content' => 'Tính chiều dài chuỗi', 'is_correct' => true],
                    ['label' => 'D', 'content' => 'Sao chép chuỗi', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS101',
                'chapter_order' => 6,
                'type' => 'tf',
                'difficulty' => 'remember',
                'content' => 'Trong C, biến cục bộ (local variable) được khai báo bên trong hàm có thể truy cập từ hàm khác.',
                'explanation' => 'Sai. Biến cục bộ chỉ có phạm vi trong hàm chứa nó.',
                'options' => [
                    ['label' => 'A', 'content' => 'Đúng', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'Sai', 'is_correct' => true],
                ],
            ],

            // ═══════════════════════════════════════════════════
            // CS201 — Cấu trúc dữ liệu (10 câu)
            // ═══════════════════════════════════════════════════
            [
                'subject_code' => 'CS201',
                'chapter_order' => 1,
                'type' => 'mcq',
                'difficulty' => 'remember',
                'content' => 'Độ phức tạp thời gian của thuật toán tìm kiếm nhị phân (Binary Search) trên mảng đã sắp xếp là?',
                'explanation' => 'Binary Search chia đôi mảng mỗi lần so sánh nên có O(log n).',
                'options' => [
                    ['label' => 'A', 'content' => 'O(1)', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'O(n)', 'is_correct' => false],
                    ['label' => 'C', 'content' => 'O(log n)', 'is_correct' => true],
                    ['label' => 'D', 'content' => 'O(n log n)', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS201',
                'chapter_order' => 3,
                'type' => 'mcq',
                'difficulty' => 'understand',
                'content' => 'Cấu trúc dữ liệu nào hoạt động theo nguyên tắc LIFO (Last In, First Out)?',
                'explanation' => 'Stack (Ngăn xếp) hoạt động LIFO: phần tử vào sau sẽ ra trước.',
                'options' => [
                    ['label' => 'A', 'content' => 'Queue', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'Stack', 'is_correct' => true],
                    ['label' => 'C', 'content' => 'Linked List', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'Array', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS201',
                'chapter_order' => 4,
                'type' => 'mcq',
                'difficulty' => 'understand',
                'content' => 'Trong cây nhị phân tìm kiếm (BST), nút con bên trái luôn có giá trị:',
                'explanation' => 'Trong BST, nút con bên trái < nút cha, nút con bên phải > nút cha.',
                'options' => [
                    ['label' => 'A', 'content' => 'Lớn hơn nút cha', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'Nhỏ hơn nút cha', 'is_correct' => true],
                    ['label' => 'C', 'content' => 'Bằng nút cha', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'Không có quy tắc cụ thể', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS201',
                'chapter_order' => 7,
                'type' => 'mcq',
                'difficulty' => 'analyze',
                'content' => 'Thuật toán sắp xếp nào sau đây có độ phức tạp trung bình O(n log n) và là thuật toán sắp xếp tại chỗ (in-place)?',
                'explanation' => 'Quick Sort có O(n log n) trung bình và sắp xếp tại chỗ. Merge Sort dùng O(n) bộ nhớ phụ.',
                'options' => [
                    ['label' => 'A', 'content' => 'Merge Sort', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'Quick Sort', 'is_correct' => true],
                    ['label' => 'C', 'content' => 'Counting Sort', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'Radix Sort', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS201',
                'chapter_order' => 5,
                'type' => 'tf',
                'difficulty' => 'understand',
                'content' => 'Hash Table có thể xảy ra đụng độ (collision) khi hai key khác nhau có cùng hash value.',
                'explanation' => 'Đúng. Collision là hiện tượng phổ biến và được xử lý bằng chaining hoặc open addressing.',
                'options' => [
                    ['label' => 'A', 'content' => 'Đúng', 'is_correct' => true],
                    ['label' => 'B', 'content' => 'Sai', 'is_correct' => false],
                ],
            ],

            // ═══════════════════════════════════════════════════
            // CS301 — Cơ sở dữ liệu (10 câu)
            // ═══════════════════════════════════════════════════
            [
                'subject_code' => 'CS301',
                'chapter_order' => 1,
                'type' => 'mcq',
                'difficulty' => 'remember',
                'content' => 'DBMS là viết tắt của:',
                'explanation' => 'DBMS = Database Management System (Hệ quản trị cơ sở dữ liệu).',
                'options' => [
                    ['label' => 'A', 'content' => 'Data Base Management System', 'is_correct' => true],
                    ['label' => 'B', 'content' => 'Data Backup Management System', 'is_correct' => false],
                    ['label' => 'C', 'content' => 'Digital Binary Management System', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'Data Block Management System', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS301',
                'chapter_order' => 3,
                'type' => 'mcq',
                'difficulty' => 'understand',
                'content' => 'Dạng chuẩn 3NF (Third Normal Form) yêu cầu bảng phải thỏa mãn:',
                'explanation' => '3NF = 2NF + không có phụ thuộc bắc cầu (transitive dependency).',
                'options' => [
                    ['label' => 'A', 'content' => 'Loại bỏ thuộc tính đa trị', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'Loại bỏ phụ thuộc hàm bộ phận', 'is_correct' => false],
                    ['label' => 'C', 'content' => 'Loại bỏ phụ thuộc bắc cầu', 'is_correct' => true],
                    ['label' => 'D', 'content' => 'Loại bỏ phụ thuộc đa trị', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS301',
                'chapter_order' => 4,
                'type' => 'mcq',
                'difficulty' => 'apply',
                'content' => 'Câu lệnh SQL nào dùng để xoá TOÀN BỘ dữ liệu trong bảng mà không xoá cấu trúc bảng?',
                'explanation' => 'TRUNCATE TABLE xoá tất cả dữ liệu nhưng giữ cấu trúc. DELETE FROM cũng được nhưng chậm hơn.',
                'options' => [
                    ['label' => 'A', 'content' => 'DROP TABLE students;', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'DELETE TABLE students;', 'is_correct' => false],
                    ['label' => 'C', 'content' => 'TRUNCATE TABLE students;', 'is_correct' => true],
                    ['label' => 'D', 'content' => 'REMOVE FROM students;', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS301',
                'chapter_order' => 5,
                'type' => 'mcq',
                'difficulty' => 'apply',
                'content' => 'Câu lệnh SQL nào để lấy tất cả sinh viên có điểm trung bình (avg_score) > 8 và sắp xếp giảm dần?',
                'explanation' => 'SELECT + WHERE để lọc, ORDER BY ... DESC để sắp xếp giảm dần.',
                'options' => [
                    ['label' => 'A', 'content' => 'SELECT * FROM students WHERE avg_score > 8 ORDER BY avg_score DESC;', 'is_correct' => true],
                    ['label' => 'B', 'content' => 'SELECT * FROM students HAVING avg_score > 8 SORT BY avg_score DESC;', 'is_correct' => false],
                    ['label' => 'C', 'content' => 'SELECT * FROM students WHERE avg_score > 8 SORT BY avg_score;', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'GET * FROM students WHERE avg_score > 8 ORDER BY avg_score DESC;', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS301',
                'chapter_order' => 6,
                'type' => 'tf',
                'difficulty' => 'understand',
                'content' => 'Trong SQL, câu lệnh COMMIT dùng để hoàn tác (rollback) tất cả thay đổi trong giao dịch hiện tại.',
                'explanation' => 'Sai. COMMIT xác nhận thay đổi. ROLLBACK mới hoàn tác thay đổi.',
                'options' => [
                    ['label' => 'A', 'content' => 'Đúng', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'Sai', 'is_correct' => true],
                ],
            ],

            // ═══════════════════════════════════════════════════
            // CS302 — Mạng máy tính (5 câu)
            // ═══════════════════════════════════════════════════
            [
                'subject_code' => 'CS302',
                'chapter_order' => 2,
                'type' => 'mcq',
                'difficulty' => 'remember',
                'content' => 'Mô hình OSI có bao nhiêu tầng (layer)?',
                'explanation' => 'Mô hình OSI có 7 tầng: Physical, Data Link, Network, Transport, Session, Presentation, Application.',
                'options' => [
                    ['label' => 'A', 'content' => '4 tầng', 'is_correct' => false],
                    ['label' => 'B', 'content' => '5 tầng', 'is_correct' => false],
                    ['label' => 'C', 'content' => '7 tầng', 'is_correct' => true],
                    ['label' => 'D', 'content' => '8 tầng', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS302',
                'chapter_order' => 4,
                'type' => 'mcq',
                'difficulty' => 'understand',
                'content' => 'Giao thức nào hoạt động ở tầng Network của mô hình OSI?',
                'explanation' => 'IP (Internet Protocol) hoạt động ở tầng Network. TCP/UDP ở tầng Transport.',
                'options' => [
                    ['label' => 'A', 'content' => 'TCP', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'HTTP', 'is_correct' => false],
                    ['label' => 'C', 'content' => 'IP', 'is_correct' => true],
                    ['label' => 'D', 'content' => 'FTP', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS302',
                'chapter_order' => 5,
                'type' => 'tf',
                'difficulty' => 'remember',
                'content' => 'TCP là giao thức truyền tin cậy (reliable), còn UDP là giao thức truyền không tin cậy (unreliable).',
                'explanation' => 'Đúng. TCP đảm bảo dữ liệu đến đúng thứ tự và không mất gói. UDP không đảm bảo.',
                'options' => [
                    ['label' => 'A', 'content' => 'Đúng', 'is_correct' => true],
                    ['label' => 'B', 'content' => 'Sai', 'is_correct' => false],
                ],
            ],

            // ═══════════════════════════════════════════════════
            // CS401 — Lập trình Web (10 câu)
            // ═══════════════════════════════════════════════════
            [
                'subject_code' => 'CS401',
                'chapter_order' => 1,
                'type' => 'mcq',
                'difficulty' => 'remember',
                'content' => 'HTTP status code 404 có nghĩa là gì?',
                'explanation' => '404 = Not Found. Server không tìm thấy tài nguyên được yêu cầu.',
                'options' => [
                    ['label' => 'A', 'content' => 'Server Error', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'Not Found', 'is_correct' => true],
                    ['label' => 'C', 'content' => 'Unauthorized', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'Bad Request', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS401',
                'chapter_order' => 3,
                'type' => 'mcq',
                'difficulty' => 'understand',
                'content' => 'Trong JavaScript, sự khác biệt giữa <code>==</code> và <code>===</code> là gì?',
                'explanation' => '== so sánh giá trị (có ép kiểu), === so sánh cả giá trị lẫn kiểu dữ liệu.',
                'options' => [
                    ['label' => 'A', 'content' => 'Không có sự khác biệt', 'is_correct' => false],
                    ['label' => 'B', 'content' => '=== so sánh cả giá trị và kiểu dữ liệu', 'is_correct' => true],
                    ['label' => 'C', 'content' => '== nhanh hơn ===', 'is_correct' => false],
                    ['label' => 'D', 'content' => '=== chỉ dùng cho chuỗi', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS401',
                'chapter_order' => 5,
                'type' => 'mcq',
                'difficulty' => 'apply',
                'content' => 'Trong Laravel, cách nào sau đây đúng để định nghĩa một route GET trả về view "home"?',
                'explanation' => 'Route::get() nhận path và closure/controller. return view("home") trả về Blade template.',
                'options' => [
                    ['label' => 'A', 'content' => 'Route::get("/", function() { return view("home"); });', 'is_correct' => true],
                    ['label' => 'B', 'content' => 'Route::view("/", "home");', 'is_correct' => false],
                    ['label' => 'C', 'content' => 'Route::render("/home");', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'Route::page("/", "home");', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS401',
                'chapter_order' => 6,
                'type' => 'mcq',
                'difficulty' => 'understand',
                'content' => 'Eloquent ORM trong Laravel sử dụng pattern nào?',
                'explanation' => 'Eloquent sử dụng Active Record pattern: mỗi model tương ứng 1 bảng, mỗi instance tương ứng 1 row.',
                'options' => [
                    ['label' => 'A', 'content' => 'Data Mapper', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'Active Record', 'is_correct' => true],
                    ['label' => 'C', 'content' => 'Repository', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'Table Gateway', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS401',
                'chapter_order' => 7,
                'type' => 'mcq',
                'difficulty' => 'apply',
                'content' => 'Trong Laravel, middleware nào mặc định được dùng để bảo vệ route khỏi CSRF attack?',
                'explanation' => 'VerifyCsrfToken middleware tự động kiểm tra CSRF token trong mỗi POST/PUT/DELETE request.',
                'options' => [
                    ['label' => 'A', 'content' => 'auth', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'VerifyCsrfToken', 'is_correct' => true],
                    ['label' => 'C', 'content' => 'throttle', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'cors', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'CS401',
                'chapter_order' => 8,
                'type' => 'tf',
                'difficulty' => 'understand',
                'content' => 'RESTful API sử dụng HTTP method PUT để tạo mới tài nguyên.',
                'explanation' => 'Sai. POST dùng để tạo mới. PUT dùng để cập nhật (replace) toàn bộ tài nguyên.',
                'options' => [
                    ['label' => 'A', 'content' => 'Đúng', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'Sai', 'is_correct' => true],
                ],
            ],

            // ═══════════════════════════════════════════════════
            // MATH201 — Toán rời rạc (5 câu)
            // ═══════════════════════════════════════════════════
            [
                'subject_code' => 'MATH201',
                'chapter_order' => 1,
                'type' => 'mcq',
                'difficulty' => 'remember',
                'content' => 'Phép toán logic nào sau đây trả về TRUE khi và chỉ khi cả hai mệnh đề đều TRUE?',
                'explanation' => 'AND (∧) chỉ TRUE khi cả hai vế TRUE. OR TRUE khi ít nhất một vế TRUE.',
                'options' => [
                    ['label' => 'A', 'content' => 'OR (∨)', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'AND (∧)', 'is_correct' => true],
                    ['label' => 'C', 'content' => 'XOR (⊕)', 'is_correct' => false],
                    ['label' => 'D', 'content' => 'NOT (¬)', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'MATH201',
                'chapter_order' => 5,
                'type' => 'mcq',
                'difficulty' => 'apply',
                'content' => 'Từ tập {1, 2, 3, 4, 5}, có bao nhiêu cách chọn 3 phần tử (tổ hợp)?',
                'explanation' => 'C(5,3) = 5! / (3! × 2!) = 120 / (6 × 2) = 10.',
                'options' => [
                    ['label' => 'A', 'content' => '10', 'is_correct' => true],
                    ['label' => 'B', 'content' => '20', 'is_correct' => false],
                    ['label' => 'C', 'content' => '60', 'is_correct' => false],
                    ['label' => 'D', 'content' => '120', 'is_correct' => false],
                ],
            ],
            [
                'subject_code' => 'MATH201',
                'chapter_order' => 6,
                'type' => 'tf',
                'difficulty' => 'understand',
                'content' => 'Trong lý thuyết đồ thị, đồ thị vô hướng hoàn chỉnh K₅ có 10 cạnh.',
                'explanation' => 'Đúng. K_n có n(n-1)/2 cạnh. K₅ = 5×4/2 = 10 cạnh.',
                'options' => [
                    ['label' => 'A', 'content' => 'Đúng', 'is_correct' => true],
                    ['label' => 'B', 'content' => 'Sai', 'is_correct' => false],
                ],
            ],
        ];
    }
}
