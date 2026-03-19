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
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
        // ID của đáp án sinh viên chọn (nullable vì có thể là tự luận hoặc bỏ trống)
            $table->foreignId('question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->text('answer_text')->nullable(); // Dành cho câu tự luận
            $table->boolean('is_correct')->nullable(); // True nếu đúng, False nếu sai
            $table->decimal('points_awarded', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
