<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_matrices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('difficulty', ['remember', 'understand', 'apply', 'analyze']);
            $table->foreignId('question_type_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('question_count')->default(1);
            $table->decimal('points_each', 5, 2)->default(1.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_matrices');
    }
};
