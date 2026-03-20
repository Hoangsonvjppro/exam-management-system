<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // GV cấu hình: cho SV xem điểm tổng sau khi nộp bài?
            $table->boolean('show_score_after_submit')->default(true)->after('pass_points');
            // GV cấu hình: cho SV xem chi tiết đáp án đúng/sai từng câu?
            $table->boolean('show_answers_after_submit')->default(false)->after('show_score_after_submit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['show_score_after_submit', 'show_answers_after_submit']);
        });
    }
};
