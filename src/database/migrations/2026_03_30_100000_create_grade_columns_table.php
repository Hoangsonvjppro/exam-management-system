<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('grade_columns')) {
            Schema::create('grade_columns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_section_id')->constrained('course_sections')->cascadeOnDelete();
                $table->string('name');
                $table->decimal('weight', 5, 2)->default(0); // Trọng số %
                $table->boolean('is_exam_linked')->default(false);
                $table->foreignId('exam_schedule_id')->nullable()->constrained('exam_schedules')->nullOnDelete();
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_columns');
    }
};
