<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add a display name to course sections and make FKs nullable
        // so lecturers can create classes without requiring subject/semester initially
        Schema::table('course_sections', function (Blueprint $table) {
            $table->string('name', 255)->nullable()->after('code');

            // Drop constraints then re-add as nullable
            $table->foreignId('subject_id')->nullable()->change();
            $table->foreignId('semester_id')->nullable()->change();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('type', 30)->default('info'); // info, warning, urgent, event
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index('is_published');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');

        Schema::table('course_sections', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->foreignId('subject_id')->nullable(false)->change();
            $table->foreignId('semester_id')->nullable(false)->change();
        });
    }
};
