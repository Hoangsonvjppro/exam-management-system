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
       Schema::create('question_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // Ví dụ: 'single_choice', 'multiple_choice'
            $table->string('name', 100);          // Ví dụ: 'Trắc nghiệm 1 đáp án'
            $table->text('description')->nullable();
            $table->json('answer_schema')->nullable(); // Cấu trúc JSON mẫu cho loại câu hỏi này
            $table->boolean('is_auto_grade')->default(true); // Tự động chấm hay cần giáo viên
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_types');
    }
};
