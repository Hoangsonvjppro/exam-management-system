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
            $table->string('code', 50)->unique()
                  ->comment('Mã loại: multiple_choice, true_false...');
            $table->string('name', 100)
                  ->comment('Tên hiển thị');
            $table->text('description')->nullable();
            $table->json('answer_schema')->nullable()
                  ->comment('JSON Schema mô tả cấu trúc đáp án');
            $table->boolean('is_auto_grade')->default(true)
                  ->comment('1=Chấm tự động');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index('is_active', 'idx_qt_active');
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
