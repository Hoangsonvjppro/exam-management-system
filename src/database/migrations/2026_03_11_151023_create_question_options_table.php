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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')
                  ->constrained('questions')->cascadeOnDelete();
            $table->char('label', 1)->comment('Nhãn: A, B, C, D');
            $table->text('content')->comment('Nội dung đáp án');
            $table->unsignedBigInteger('image_file_id')->nullable()
                  ->comment('FK → files. Hình ảnh đáp án');
            $table->boolean('is_correct')->default(false)
                  ->comment('1=Đáp án đúng');
            $table->tinyInteger('order')->unsigned()->default(0)
                  ->comment('Thứ tự hiển thị gốc');
            $table->timestamps();

            $table->index('question_id', 'idx_qo_question');
            $table->index(['question_id', 'is_correct'], 'idx_qo_correct');
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->foreign('image_file_id')
                  ->references('id')->on('files')
                  ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
