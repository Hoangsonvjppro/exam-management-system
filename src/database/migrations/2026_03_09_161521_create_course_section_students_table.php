<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_section_students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_section_id')
                  ->constrained()
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('student_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->enum('status', ['enrolled', 'dropped', 'completed'])
                  ->default('enrolled')
                  ->comment('enrolled=đang học, dropped=đã rút, completed=hoàn thành');

            $table->timestamp('enrolled_at')
                  ->useCurrent()
                  ->nullable()
                  ->comment('Ngày đăng ký');

            $table->timestamps();

            // Một sinh viên chỉ đăng ký một lớp một lần
            $table->unique(['course_section_id', 'student_id'], 'uk_css_section_student');

            $table->index('student_id',  'idx_css_student');
            $table->index('status',      'idx_css_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_section_students');
    }
};