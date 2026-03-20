<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm cột exam_type vào exams
        if (!Schema::hasColumn('exams', 'exam_type')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->enum('exam_type', ['official', 'practice'])
                      ->default('official')
                      ->after('status')
                      ->comment('Loại đề thi: official (chính thức - 1 lần), practice (luyện tập - nhiều lần)');
            });
        }

        // 2. Sửa unique constraint ở exam_attempts
        $indexExists = collect(DB::select("SHOW INDEXES FROM exam_attempts"))
            ->pluck('Key_name')
            ->contains('exam_attempts_exam_id_user_id_unique');

        Schema::table('exam_attempts', function (Blueprint $table) use ($indexExists) {
            if ($indexExists) {
                $table->dropUnique('exam_attempts_exam_id_user_id_unique');
            }
            
            if (!Schema::hasColumn('exam_attempts', 'attempt_number')) {
                $table->unsignedInteger('attempt_number')->default(1)->after('user_id');
            }

            // Kiểm tra index mới đã tồn tại chưa
            $newIndexExists = collect(DB::select("SHOW INDEXES FROM exam_attempts"))
                ->pluck('Key_name')
                ->contains('exam_user_attempt_unique');

            if (!$newIndexExists) {
                $table->unique(['exam_id', 'user_id', 'attempt_number'], 'exam_user_attempt_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $newIndexExists = collect(DB::select("SHOW INDEXES FROM exam_attempts"))
                ->pluck('Key_name')
                ->contains('exam_user_attempt_unique');

            if ($newIndexExists) {
                $table->dropUnique('exam_user_attempt_unique');
            }

            if (Schema::hasColumn('exam_attempts', 'attempt_number')) {
                $table->dropColumn('attempt_number');
            }

            // Tạo lại index cũ
            $oldIndexExists = collect(DB::select("SHOW INDEXES FROM exam_attempts"))
                ->pluck('Key_name')
                ->contains('exam_attempts_exam_id_user_id_unique');

            if (!$oldIndexExists) {
                $table->unique(['exam_id', 'user_id'], 'exam_attempts_exam_id_user_id_unique');
            }
        });

        if (Schema::hasColumn('exams', 'exam_type')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn('exam_type');
            });
        }
    }
};
