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
 *   4. Users (GV do admin cấp, SV theo Google-first)
 *   5. Subjects + Chapters
 *   6. Assignments (phân công giảng viên - môn học)
 *   7. CourseSections (phụ thuộc Subject, Semester, User, Assignment)
 *   8. Tags
 *   9. Questions (phụ thuộc Subject, Chapter, QuestionType, Assignment)
 *  10. Exams (phụ thuộc Subject, Question, CourseSection)
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
            DepartmentSeeder::class,        // Khoa
            MajorSeeder::class,             // Ngành
            StudentClassSeeder::class,      // Lớp
            DifficultySeeder::class,        // Tạo mức độ khó (Dễ, Trung bình...)
            SemesterSeeder::class,          // Tạo học kỳ
            QuestionTypeSeeder::class,      // Tạo loại câu hỏi (MCQ, T/F...)
            SettingSeeder::class,           // Cấu hình hệ thống
        ]);

        // ─── Bước 2: Users + Subjects ────────────────────────
        $this->call([
            UserSeeder::class,              // 3 GV + tập SV Google-first
            SubjectSeeder::class,           // 5 môn IT + Chương
        ]);

        // ─── Bước 3: Phân công môn cho giảng viên
        $this->call([
            AssignmentSeeder::class,
        ]);

        // ─── Bước 4: Lớp học phần (phụ thuộc Subject, Semester, Assignment)
        $this->call([
            CourseSectionSeeder::class,     // 5 lớp HP + lịch học + gán SV
        ]);

        // ─── Bước 5: Dữ liệu test (chỉ Local/Dev) ──────────
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
        $this->command->info('  👨‍🏫 GV demo:  GV_001 / password');
        $this->command->info('  🎓 SV demo:  đăng nhập Google + hoàn tất onboarding MSSV');
        $this->command->info('');
    }
}
