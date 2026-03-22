<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * ============================================================
 * DatabaseSeeder — Điều phối toàn bộ seeders
 * ============================================================
 * Thứ tự chạy tuân theo FK dependency:
 *   1. Roles       → (không phụ thuộc gì)
 *   2. Users       → (phụ thuộc roles)
 *   3. Semesters   → (không phụ thuộc gì)
 *   4. Subjects    → (không phụ thuộc gì, tạo luôn chapters)
 *   5. QuestionTypes → (không phụ thuộc gì)
 *   6. CourseSections → (phụ thuộc subjects, semesters, users)
 *   7. Questions   → (phụ thuộc subjects, chapters, qt, users)
 *   8. Settings    → (không phụ thuộc gì)
 *
 * Chạy: php artisan db:seed
 * Chạy lại: php artisan migrate:fresh --seed
 * ============================================================
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Thứ tự quan trọng:
     * 1. Roles & Permissions trước (User cần role khi assignRole)
     * 2. Admin & demo users sau
     * 3. Settings cuối
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════════╗');
        $this->command->info('║  EMS — Seeding Database                   ║');
        $this->command->info('╚════════════════════════════════════════════╝');
        $this->command->info('');

        $this->call([
            RoleAndPermissionSeeder::class, // 1. Vai trò + Permissions (Spatie)
            AdminRootSeeder::class,         // 2. Root admin (bảng admins, guard admin)
            UserSeeder::class,         // 2. Người dùng (admin, GV, SV)
            SemesterSeeder::class,          // 3. Học kỳ
            SubjectSeeder::class,           // 4. Môn học + Chương
            QuestionTypeSeeder::class,      // 5. Loại câu hỏi
            CourseSectionSeeder::class,     // 6. Lớp HP + Lịch + Gán SV
            QuestionSeeder::class,          // 7. Câu hỏi mẫu + Options
            SettingSeeder::class,           // 8. Cấu hình hệ thống
        ]);

        $this->command->info('');
        $this->command->info('════════════════════════════════════════════');
        $this->command->info('  ✅ Seed hoàn tất!');
        $this->command->info('  📧 Root Admin: '.env('EMS_ROOT_ADMIN_EMAIL', 'root@ems.local').' / '.env('EMS_ROOT_ADMIN_PASSWORD', 'password'));
        $this->command->info('════════════════════════════════════════════');
        $this->command->info('');
    }
}
