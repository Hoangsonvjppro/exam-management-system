<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

/**
 * ============================================================
 * DatabaseSeeder — Điều phối toàn bộ quá trình seed
 * ============================================================
 * Thứ tự seed theo dependency:
 *   1. Roles/Permissions (nền tảng phân quyền)
 *   2. Admin Root
 *   3. Difficulty, Settings, Semesters, QuestionTypes (dữ liệu hệ thống)
 *   4. Users (GV + SV)
 *   5. Subjects + Chapters
 *   6. CourseSections (phụ thuộc Subject, Semester, User)
 *   7. Tags
 *   8. Questions (phụ thuộc Subject, Chapter, QuestionType, User)
 *   9. Exams (phụ thuộc Subject, User, Question, CourseSection)
 * ============================================================
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('╔════════════════════════════════════════════╗');
        $this->command->info('║  EMS — System Database Seeding             ║');
        $this->command->info('╚════════════════════════════════════════════╝');

        // ─── Bước 1: Dữ liệu nền tảng (chạy mọi môi trường) ─
        $this->call([
            RoleAndPermissionSeeder::class, // Phân quyền (bắt buộc đầu tiên)
            AdminRootSeeder::class,         // Tạo Admin Root
            DifficultySeeder::class,        // 4 mức độ khó (Bloom's Taxonomy)
            SettingSeeder::class,           // Cấu hình hệ thống
            SemesterSeeder::class,          // 3 học kỳ (HK1, HK2, Hè)
            QuestionTypeSeeder::class,      // 6 loại câu hỏi
        ]);

        // ─── Bước 2: Users + Subjects ────────────────────────
        $this->call([
            UserSeeder::class,              // 3 GV + 20 SV (đầy đủ thông tin)
            SubjectSeeder::class,           // 5 môn IT + Chương
        ]);

        // ─── Bước 3: Lớp học phần (phụ thuộc Subject, Semester, User)
        $this->call([
            CourseSectionSeeder::class,     // 5 lớp HP + lịch học + gán SV
        ]);

        // ─── Bước 4: Dữ liệu test (chỉ Local/Dev) ──────────
        if (!App::environment('production')) {
            $this->command->warn('');
            $this->command->warn('⚠ Đang ở môi trường Local/Dev. Bắt đầu seed dữ liệu test...');

            $this->call([
                TagSeeder::class,           // 15 tags cho ngân hàng câu hỏi
                QuestionSeeder::class,      // 150 câu hỏi (30 câu/môn)
                ExamSeeder::class,          // 3 đề thi + lịch thi + SV
            ]);
        }

        $this->command->info('');
        $this->command->info('════════════════════════════════════════════');
        $this->command->info('  ✅ Seed hoàn tất!');
        $this->command->info('════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('  📧 Admin:    admin@root.com / password');
        $this->command->info('  📧 GV Sang:  Sang@gmail.com / password');
        $this->command->info('  📧 SV 1:     sv1@gmail.com  / password');
        $this->command->info('');
    }
}
