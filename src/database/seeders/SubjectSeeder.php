<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'code' => 'IT001',
                'name' => 'Nhập môn Lập trình C++',
                'credits' => 3,
                'chapters' => ['Tổng quan về C++', 'Cấu trúc điều khiển', 'Hàm và Mảng']
            ],
            [
                'code' => 'IT002',
                'name' => 'Cấu trúc Dữ liệu & Giải thuật',
                'credits' => 4,
                'chapters' => ['Độ phức tạp thuật toán', 'Danh sách liên kết', 'Cây và Đồ thị']
            ],
            [
                'code' => 'IT003',
                'name' => 'Cơ sở Dữ liệu',
                'credits' => 3,
                'chapters' => ['Mô hình ER', 'Ngôn ngữ SQL', 'Chuẩn hóa Dữ liệu']
            ],
            [
                'code' => 'IT004',
                'name' => 'Mạng Máy tính',
                'credits' => 3,
                'chapters' => ['Mô hình OSI', 'Giao thức TCP/IP', 'Định tuyến mạng']
            ],
            [
                'code' => 'IT005',
                'name' => 'Lập trình Web (Laravel)',
                'credits' => 4,
                'chapters' => ['HTML/CSS/JS Cơ bản', 'PHP & MVC', 'Laravel Framework']
            ],
        ];

        foreach ($subjects as $sub) {
            $subject = Subject::updateOrCreate(
                ['code' => $sub['code']],
                ['name' => $sub['name'], 'credits' => $sub['credits'], 'department' => 'Khoa CNTT']
            );

            foreach ($sub['chapters'] as $index => $chapterName) {
                Chapter::updateOrCreate(
                    ['subject_id' => $subject->id, 'order' => $index + 1],
                    ['name' => $chapterName, 'description' => "Nội dung chương: $chapterName"]
                );
            }
        }

        $this->command->info('✅ Đã tạo 5 Môn học IT và các Chương tương ứng.');
    }
}
