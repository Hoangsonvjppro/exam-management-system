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
        Schema::create('question_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')
                  ->constrained('questions')->cascadeOnDelete();
            $table->string('tag', 100);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['question_id', 'tag'], 'uk_qtags_question_tag');
            $table->index('tag', 'idx_qtags_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_tags');
    }
};
