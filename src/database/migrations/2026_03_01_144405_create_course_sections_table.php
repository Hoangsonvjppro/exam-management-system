<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();    // VD: "CS101-01-HK1-2526"

            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->foreignId('semester_id')
                  ->constrained('semesters')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->foreignId('lecturer_id')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->unsignedInteger('max_students')->default(100);
            $table->enum('status', ['active', 'archived', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index('subject_id');
            $table->index('semester_id');
            $table->index('lecturer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sections');
    }
};