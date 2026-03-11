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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('chapter_id')->nullable()
                  ->constrained('chapters')->nullOnDelete();
            $table->foreignId('question_type_id')
                  ->constrained('question_types')->restrictOnDelete();
            $table->foreignId('created_by')
                  ->constrained('users')->restrictOnDelete();
            $table->text('content')->comment('Nội dung câu hỏi (HTML/Markdown)');
            $table->enum('difficulty', ['remember', 'understand', 'apply', 'analyze'])
                  ->default('remember')
                  ->comment('Mức độ theo Bloom');
            $table->unsignedBigInteger('image_file_id')->nullable()
                  ->comment('FK → files. Hình ảnh minh hoạ');
            $table->text('explanation')->nullable()
                  ->comment('Giải thích đáp án đúng');
            $table->json('answer_data')->nullable()
                  ->comment('Dữ liệu đáp án linh hoạt cho fill_blank, matching...');
            $table->enum('status', ['draft', 'approved', 'hidden'])->default('draft');
            $table->unsignedInteger('version')->default(1)
                  ->comment('Số phiên bản');
            $table->unsignedInteger('usage_count')->default(0);
            $table->decimal('correct_rate', 5, 2)->nullable()
                  ->comment('Tỷ lệ % trả lời đúng');
            $table->timestamps();
            $table->softDeletes();

            $table->index('subject_id', 'idx_questions_subject');
            $table->index('chapter_id', 'idx_questions_chapter');
            $table->index('question_type_id', 'idx_questions_type');
            $table->index('created_by', 'idx_questions_created_by');
            $table->index('difficulty', 'idx_questions_difficulty');
            $table->index('status', 'idx_questions_status');
            $table->index(['subject_id', 'chapter_id', 'difficulty', 'status'], 'idx_questions_matrix');
        });
        
        Schema::table('questions', function (Blueprint $table) {
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
        Schema::dropIfExists('questions');
    }
};
