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
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->decimal('points',5,2)->default(1.00); //điểm của câu hỏi trong bài kiểm tra
            $table->integer('order_index')->default(0); //thứ tự câu hỏi trong bài kiểm tra
            $table->timestamps();

            // Đảm bảo mỗi câu hỏi chỉ được thêm vào một bài kiểm tra một lần
            $table->unique(['exam_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
