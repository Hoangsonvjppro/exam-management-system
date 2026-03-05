<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * ============================================================
 * SubjectSeeder — Tạo 6 môn học IT + chương cho mỗi môn
 * ============================================================
 * Dữ liệu thực tế theo chương trình đào tạo CNTT đại học VN.
 * ============================================================
 */
class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'code' => 'CS101',
                'name' => 'Nhập môn Lập trình',
                'credits' => 3,
                'department' => 'Khoa CNTT',
                'description' => 'Giới thiệu các khái niệm cơ bản về lập trình, thuật toán và cấu trúc dữ liệu đơn giản.',
                'chapters' => [
                    'Tổng quan về máy tính và lập trình',
                    'Biến, kiểu dữ liệu và toán tử',
                    'Cấu trúc điều khiển (if/else, switch)',
                    'Vòng lặp (for, while, do-while)',
                    'Mảng và chuỗi',
                    'Hàm và phạm vi biến',
                    'Con trỏ và quản lý bộ nhớ',
                ],
            ],
            [
                'code' => 'CS201',
                'name' => 'Cấu trúc dữ liệu và Giải thuật',
                'credits' => 4,
                'department' => 'Khoa CNTT',
                'description' => 'Nghiên cứu các cấu trúc dữ liệu nâng cao và phân tích độ phức tạp thuật toán.',
                'chapters' => [
                    'Phân tích độ phức tạp thuật toán',
                    'Danh sách liên kết (Linked List)',
                    'Ngăn xếp và Hàng đợi (Stack & Queue)',
                    'Cây nhị phân và Cây tìm kiếm (BST)',
                    'Bảng băm (Hash Table)',
                    'Đồ thị và thuật toán duyệt đồ thị',
                    'Thuật toán sắp xếp',
                    'Quy hoạch động',
                ],
            ],
            [
                'code' => 'CS301',
                'name' => 'Cơ sở dữ liệu',
                'credits' => 3,
                'department' => 'Khoa CNTT',
                'description' => 'Thiết kế, triển khai và tối ưu cơ sở dữ liệu quan hệ.',
                'chapters' => [
                    'Tổng quan về cơ sở dữ liệu',
                    'Mô hình thực thể - liên kết (ER)',
                    'Mô hình quan hệ và chuẩn hóa',
                    'Ngôn ngữ SQL cơ bản',
                    'SQL nâng cao (JOIN, Subquery, View)',
                    'Giao dịch và kiểm soát đồng thời',
                    'Tối ưu truy vấn và Indexing',
                ],
            ],
            [
                'code' => 'CS302',
                'name' => 'Mạng máy tính',
                'credits' => 3,
                'department' => 'Khoa CNTT',
                'description' => 'Kiến thức nền tảng về mạng máy tính, giao thức và bảo mật mạng.',
                'chapters' => [
                    'Tổng quan về mạng máy tính',
                    'Mô hình OSI và TCP/IP',
                    'Tầng vật lý và Tầng liên kết dữ liệu',
                    'Tầng mạng và định tuyến',
                    'Tầng giao vận (TCP, UDP)',
                    'Tầng ứng dụng (HTTP, DNS, SMTP)',
                    'Bảo mật mạng',
                ],
            ],
            [
                'code' => 'CS401',
                'name' => 'Lập trình Web và Ứng dụng',
                'credits' => 4,
                'department' => 'Khoa CNTT',
                'description' => 'Phát triển ứng dụng web fullstack sử dụng HTML, CSS, JavaScript và PHP/Laravel.',
                'chapters' => [
                    'Tổng quan Web và HTTP',
                    'HTML5 và CSS3',
                    'JavaScript cơ bản và DOM',
                    'Lập trình phía server với PHP',
                    'Framework Laravel — Routing, Controller, View',
                    'Eloquent ORM và Migration',
                    'Authentication và Authorization',
                    'RESTful API và AJAX',
                ],
            ],
            [
                'code' => 'MATH201',
                'name' => 'Toán rời rạc',
                'credits' => 3,
                'department' => 'Khoa Toán-Tin',
                'description' => 'Logic mệnh đề, lý thuyết tập hợp, toán tổ hợp và lý thuyết đồ thị.',
                'chapters' => [
                    'Logic mệnh đề và vị từ',
                    'Phương pháp chứng minh',
                    'Lý thuyết tập hợp',
                    'Quan hệ và hàm',
                    'Toán tổ hợp (Đếm, hoán vị, tổ hợp)',
                    'Lý thuyết đồ thị',
                ],
            ],
        ];

        foreach ($subjects as $subjectData) {
            $chapters = $subjectData['chapters'];
            unset($subjectData['chapters']);

            $subject = Subject::updateOrCreate(
                ['code' => $subjectData['code']],
                $subjectData
            );

            // Tạo chương cho môn học
            foreach ($chapters as $order => $chapterName) {
                Chapter::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'name' => $chapterName,
                    ],
                    [
                        'order' => $order + 1,
                        'description' => "Chương " . ($order + 1) . " của môn {$subject->name}",
                    ]
                );
            }
        }

        $totalChapters = Chapter::count();
        $this->command->info("✓ Đã tạo 6 môn học + {$totalChapters} chương.");
    }
}
