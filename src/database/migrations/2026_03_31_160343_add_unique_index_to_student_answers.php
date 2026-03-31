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
        Schema::table('student_answers', function (Blueprint $table) {
            // First, make sure we don't have duplicates or handle them if we do.
            // But usually this fails if duplicates exist, which is good.
            $table->unique(['exam_attempt_id', 'question_id'], 'student_answers_attempt_question_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropUnique('student_answers_attempt_question_unique');
        });
    }
};
