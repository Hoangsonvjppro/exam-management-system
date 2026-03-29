<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('╔════════════════════════════════════════════╗');
        $this->command->info('║  EMS — System Database Seeding             ║');
        $this->command->info('╚════════════════════════════════════════════╝');

        $this->call([
            RoleAndPermissionSeeder::class, // Phân quyền (Bắt buộc chạy đầu tiên)
            AdminRootSeeder::class,         // Tạo Admin Root
            DepartmentSeeder::class,        // Khoa
            MajorSeeder::class,             // Ngành
            StudentClassSeeder::class,      // Lớp
            UserSeeder::class,              // Tạo GV Nguyễn Thanh Sang
            DifficultySeeder::class,        // Tạo mức độ khó (Dễ, Trung bình...)
            SemesterSeeder::class,          // Tạo học kỳ
            QuestionTypeSeeder::class,      // Tạo loại câu hỏi (MCQ, T/F...)
            SubjectSeeder::class,           // Tạo 5 môn học IT + Chương
            SettingSeeder::class,           // Cấu hình hệ thống
        ]);

        // Tạo 100 câu hỏi (Chỉ nên chạy ở Local/Dev để test UI)
        if (!App::environment('production')) {
            $this->command->warn('⚠ Đang ở môi trường Local/Dev. Bắt đầu seed 100 Câu hỏi...');
            $this->call([
                QuestionSeeder::class,
            ]);
        }

        $this->command->info('════════════════════════════════════════════');
        $this->command->info('  ✅ Seed hoàn tất!');
        $this->command->info('════════════════════════════════════════════');
    }
}
