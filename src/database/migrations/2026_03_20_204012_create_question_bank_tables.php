<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->json('answer_schema')->nullable();
            $table->boolean('is_auto_grade')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->nullOnDelete();
            $table->foreignId('question_type_id')->constrained('question_types')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->text('content');
            $table->enum('difficulty', ['remember', 'understand', 'apply', 'analyze'])->default('remember');
            $table->foreignId('image_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->text('explanation')->nullable();
            $table->json('answer_data')->nullable();
            $table->enum('status', ['draft', 'approved', 'hidden'])->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->unsignedInteger('usage_count')->default(0);
            $table->decimal('correct_rate', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['subject_id', 'chapter_id', 'difficulty', 'status']);
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->char('label', 1);
            $table->text('content');
            $table->foreignId('image_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->tinyInteger('order')->unsigned()->default(0);
            $table->timestamps();
        });

        Schema::create('question_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('tag', 100);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['question_id', 'tag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_tags');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_types');
    }
};
