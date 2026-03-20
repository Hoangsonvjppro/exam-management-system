<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->enum('exam_type', ['official', 'practice'])->default('official');
            $table->text('reopen_reason')->nullable();
            $table->decimal('total_points', 5, 2)->default(10.00);
            $table->decimal('pass_points', 5, 2)->default(5.00);
            $table->boolean('show_score_after_submit')->default(true);
            $table->boolean('show_answers_after_submit')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->decimal('points', 5, 2)->default(1.00);
            $table->integer('order_index')->default(0);
            $table->timestamps();
            $table->unique(['exam_id', 'question_id']);
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            $table->decimal('total_score', 5, 2)->nullable();
            $table->timestamps();
            // Đảm bảo mỗi lần làm bài (attempt) là duy nhất cho 1 user và 1 exam
            $table->unique(['exam_id', 'user_id', 'attempt_number'], 'exam_user_attempt_unique');
        });

        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_awarded', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['exam_attempt_id', 'question_id'], 'uk_answer_attempt_question');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
    }
};
