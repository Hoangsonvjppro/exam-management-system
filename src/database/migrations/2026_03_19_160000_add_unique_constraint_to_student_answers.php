<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm unique constraint cho student_answers
     * để ngăn duplicate answer cùng question trong cùng attempt (High #5).
     */
    public function up(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->unique(['exam_attempt_id', 'question_id'], 'uk_answer_attempt_question');
        });
    }

    public function down(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropUnique('uk_answer_attempt_question');
        });
    }
};
