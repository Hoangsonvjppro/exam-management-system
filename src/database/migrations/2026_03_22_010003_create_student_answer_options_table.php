<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_answer_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_answer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_option_id')->constrained()->cascadeOnDelete();
            $table->unique(['student_answer_id', 'question_option_id'], 'uk_answer_option');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answer_options');
    }
};
