<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->smallInteger('year')->unsigned();
            $table->tinyInteger('term')->unsigned();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false)->index();
            $table->timestamps();
            $table->unique(['year', 'term']);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->unsignedTinyInteger('credits')->default(3);
            $table->string('department')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255)->nullable();
            $table->string('invite_code', 20)->nullable()->unique();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->restrictOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->restrictOnDelete();
            $table->foreignId('lecturer_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('max_students')->default(100);
            $table->enum('status', ['active', 'archived', 'cancelled'])->default('active')->index();
            $table->timestamps();
        });

        Schema::create('course_section_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['enrolled', 'dropped', 'completed'])->default('enrolled');
            $table->timestamp('enrolled_at')->nullable()->useCurrent();
            $table->timestamps();
            $table->unique(['course_section_id', 'student_id']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('type', 30)->default('info');
            $table->boolean('is_published')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('course_section_students');
        Schema::dropIfExists('course_sections');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('semesters');
    }
};
