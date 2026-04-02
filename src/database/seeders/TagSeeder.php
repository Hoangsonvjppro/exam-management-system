<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * ============================================================
 * TagSeeder — Tạo 15 tag chuẩn cho ngân hàng câu hỏi
 * ============================================================
 */
class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Tags theo lĩnh vực
            'Lập trình',
            'Cấu trúc dữ liệu',
            'Cơ sở dữ liệu',
            'Mạng máy tính',
            'Lập trình Web',
            'Thuật toán',
            'Hệ điều hành',
            // Tags theo mức độ / mục đích
            'Cơ bản',
            'Nâng cao',
            'Ôn tập',
            'Thi giữa kỳ',
            'Thi cuối kỳ',
            // Tags theo chủ đề phổ biến
            'SQL',
            'OOP',
            'TCP/IP',
        ];

        $count = 0;
        foreach ($tags as $tagName) {
            Tag::updateOrCreate(
                ['name' => $tagName],
                ['slug' => Str::slug($tagName) ?: Str::slug('tag-' . $tagName)]
            );
            $count++;
        }

        $this->command->info("✅ Đã tạo {$count} tags cho ngân hàng câu hỏi.");
    }
}
