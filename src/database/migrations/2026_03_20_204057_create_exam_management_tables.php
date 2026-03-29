<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->default(60);
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


    }

    public function down(): void
    {

        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
    }
};
