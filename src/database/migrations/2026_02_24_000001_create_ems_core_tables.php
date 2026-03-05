<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * EMS Core Tables Migration
 * ============================================================
 * Tạo các bảng nền tảng cho hệ thống quản lý thi trắc nghiệm.
 * Bao gồm: roles, user_roles, semesters, subjects, chapters,
 * course_sections, class_schedules, course_section_students,
 * question_types, questions, question_options, question_tags,
 * files, settings.
 *
 * Thứ tự tạo bảng tuân theo FK dependency.
 * ============================================================
 */
return new class extends Migration
{
    public function up(): void
    {
        // ────────────────────────────────────────────────────────
        // 1. ROLES — Vai trò (thay thế ENUM cứng)
        // ────────────────────────────────────────────────────────
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()
                  ->comment('Mã vai trò: admin, lecturer, student...');
            $table->string('name', 100)
                  ->comment('Tên hiển thị: Quản trị viên, Giảng viên...');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ────────────────────────────────────────────────────────
        // 2. USER_ROLES — Gán vai trò N-N
        // ────────────────────────────────────────────────────────
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['user_id', 'role_id'], 'uk_user_roles');
            $table->index('role_id', 'idx_ur_role');
        });

        // ────────────────────────────────────────────────────────
        // 3. Cập nhật bảng USERS — Thêm các cột EMS
        // ────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('password')
                  ->comment('Số điện thoại');
            $table->unsignedBigInteger('avatar_file_id')->nullable()->after('phone')
                  ->comment('FK → files. Ảnh đại diện');
            $table->string('student_code', 20)->nullable()->unique()->after('avatar_file_id')
                  ->comment('Mã số sinh viên (MSSV)');
            $table->string('lecturer_code', 20)->nullable()->unique()->after('student_code')
                  ->comment('Mã giảng viên');
            $table->string('class_name', 100)->nullable()->after('lecturer_code')
                  ->comment('Lớp sinh hoạt (dành cho SV)');
            $table->string('department')->nullable()->after('class_name')
                  ->comment('Khoa / Bộ môn');
            $table->boolean('is_active')->default(true)->after('department')
                  ->comment('1=Hoạt động, 0=Bị khoá');
            $table->softDeletes();

            $table->index('department', 'idx_users_department');
        });

        // ────────────────────────────────────────────────────────
        // 4. SEMESTERS — Học kỳ
        // ────────────────────────────────────────────────────────
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('VD: HK1 2025-2026');
            $table->smallInteger('year')->unsigned()->comment('Năm học bắt đầu');
            $table->tinyInteger('term')->unsigned()
                  ->comment('1=HK1, 2=HK2, 3=HK Hè');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false)
                  ->comment('Đánh dấu học kỳ hiện tại');
            $table->timestamps();

            $table->unique(['year', 'term'], 'uk_semesters_year_term');
            $table->index('is_current', 'idx_semesters_is_current');
        });

        // ────────────────────────────────────────────────────────
        // 5. SUBJECTS — Môn học
        // ────────────────────────────────────────────────────────
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Mã môn: CS101');
            $table->string('name')->comment('Tên môn học');
            $table->tinyInteger('credits')->unsigned()->default(3)
                  ->comment('Số tín chỉ');
            $table->string('department')->nullable()->comment('Khoa quản lý');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('department', 'idx_subjects_department');
        });

        // ────────────────────────────────────────────────────────
        // 6. CHAPTERS — Chương (thuộc môn học)
        // ────────────────────────────────────────────────────────
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('name')->comment('Tên chương');
            $table->unsignedInteger('order')->default(0)->comment('Thứ tự sắp xếp');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('subject_id', 'idx_chapters_subject');
            $table->index(['subject_id', 'order'], 'idx_chapters_order');
        });

        // ────────────────────────────────────────────────────────
        // 7. COURSE_SECTIONS — Lớp học phần
        // ────────────────────────────────────────────────────────
        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()
                  ->comment('Mã lớp học phần: CS101-01-HK1-2526');
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignId('lecturer_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('max_students')->default(50)
                  ->comment('Sĩ số tối đa');
            $table->enum('status', ['active', 'archived', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index('subject_id', 'idx_cs_subject');
            $table->index('semester_id', 'idx_cs_semester');
            $table->index('lecturer_id', 'idx_cs_lecturer');
            $table->index('status', 'idx_cs_status');
        });

        // ────────────────────────────────────────────────────────
        // 8. CLASS_SCHEDULES — Thời khóa biểu chi tiết
        // ────────────────────────────────────────────────────────
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')
                  ->constrained('course_sections')->cascadeOnDelete();
            $table->tinyInteger('day_of_week')->unsigned()
                  ->comment('2=Thứ Hai ... 8=Chủ Nhật');
            $table->tinyInteger('start_period')->unsigned()
                  ->comment('Tiết bắt đầu (1-16)');
            $table->tinyInteger('end_period')->unsigned()
                  ->comment('Tiết kết thúc (1-16)');
            $table->string('room', 50)->nullable()->comment('Phòng học');
            $table->timestamps();

            $table->index('course_section_id', 'idx_clsch_section');
            $table->index(['room', 'day_of_week', 'start_period', 'end_period'], 'idx_clsch_room_time');
            $table->index(['day_of_week', 'start_period'], 'idx_clsch_day');
        });

        // ────────────────────────────────────────────────────────
        // 9. COURSE_SECTION_STUDENTS — SV đăng ký lớp HP
        // ────────────────────────────────────────────────────────
        Schema::create('course_section_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')
                  ->constrained('course_sections')->cascadeOnDelete();
            $table->foreignId('student_id')
                  ->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['enrolled', 'dropped', 'completed'])
                  ->default('enrolled')
                  ->comment('enrolled=đang học, dropped=đã rút, completed=hoàn thành');
            $table->timestamp('enrolled_at')->useCurrent()
                  ->comment('Ngày đăng ký');
            $table->timestamps();

            $table->unique(['course_section_id', 'student_id'], 'uk_css_section_student');
            $table->index('student_id', 'idx_css_student');
            $table->index('status', 'idx_css_status');
        });

        // ────────────────────────────────────────────────────────
        // 10. QUESTION_TYPES — Loại câu hỏi
        // ────────────────────────────────────────────────────────
        Schema::create('question_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()
                  ->comment('Mã loại: multiple_choice, true_false...');
            $table->string('name', 100)
                  ->comment('Tên hiển thị');
            $table->text('description')->nullable();
            $table->json('answer_schema')->nullable()
                  ->comment('JSON Schema mô tả cấu trúc đáp án');
            $table->boolean('is_auto_grade')->default(true)
                  ->comment('1=Chấm tự động');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index('is_active', 'idx_qt_active');
        });

        // ────────────────────────────────────────────────────────
        // 11. QUESTIONS — Ngân hàng câu hỏi
        // ────────────────────────────────────────────────────────
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

        // ────────────────────────────────────────────────────────
        // 12. QUESTION_OPTIONS — Lựa chọn đáp án (A/B/C/D)
        // ────────────────────────────────────────────────────────
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')
                  ->constrained('questions')->cascadeOnDelete();
            $table->char('label', 1)->comment('Nhãn: A, B, C, D');
            $table->text('content')->comment('Nội dung đáp án');
            $table->unsignedBigInteger('image_file_id')->nullable()
                  ->comment('FK → files. Hình ảnh đáp án');
            $table->boolean('is_correct')->default(false)
                  ->comment('1=Đáp án đúng');
            $table->tinyInteger('order')->unsigned()->default(0)
                  ->comment('Thứ tự hiển thị gốc');
            $table->timestamps();

            $table->index('question_id', 'idx_qo_question');
            $table->index(['question_id', 'is_correct'], 'idx_qo_correct');
        });

        // ────────────────────────────────────────────────────────
        // 13. QUESTION_TAGS — Nhãn cho câu hỏi
        // ────────────────────────────────────────────────────────
        Schema::create('question_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')
                  ->constrained('questions')->cascadeOnDelete();
            $table->string('tag', 100);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['question_id', 'tag'], 'uk_qtags_question_tag');
            $table->index('tag', 'idx_qtags_tag');
        });

        // ────────────────────────────────────────────────────────
        // 14. FILES — Quản lý file tập trung
        // ────────────────────────────────────────────────────────
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()
                  ->constrained('users')->nullOnDelete()
                  ->comment('Người upload (NULL=hệ thống)');
            $table->string('disk', 20)->default('local')
                  ->comment('Storage disk: local, s3, minio');
            $table->string('path', 500)->comment('Đường dẫn trên disk');
            $table->string('original_name')->comment('Tên file gốc');
            $table->string('mime_type', 100)->comment('MIME type');
            $table->string('extension', 20)->comment('Phần mở rộng');
            $table->unsignedBigInteger('size')->default(0)
                  ->comment('Dung lượng (bytes)');
            $table->string('checksum', 64)->nullable()
                  ->comment('SHA-256 hash để phát hiện trùng');
            $table->boolean('is_public')->default(false)
                  ->comment('1=Public URL, 0=Cần signed URL');
            $table->string('used_by_type', 100)->nullable()
                  ->comment('Polymorphic model class');
            $table->unsignedBigInteger('used_by_id')->nullable()
                  ->comment('ID bản ghi sử dụng file');
            $table->timestamps();
            $table->softDeletes();

            $table->index('uploaded_by', 'idx_files_uploaded_by');
            $table->index('disk', 'idx_files_disk');
            $table->index('mime_type', 'idx_files_mime');
            $table->index('checksum', 'idx_files_checksum');
            $table->index(['used_by_type', 'used_by_id'], 'idx_files_used_by');
            $table->index(['used_by_type', 'deleted_at'], 'idx_files_orphan');
        });

        // ────────────────────────────────────────────────────────
        // 15. Deferred FK: users.avatar_file_id → files
        // ────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('avatar_file_id')
                  ->references('id')->on('files')
                  ->nullOnDelete()->cascadeOnUpdate();
        });

        // Deferred FK: questions.image_file_id → files
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('image_file_id')
                  ->references('id')->on('files')
                  ->nullOnDelete()->cascadeOnUpdate();
        });

        // Deferred FK: question_options.image_file_id → files
        Schema::table('question_options', function (Blueprint $table) {
            $table->foreign('image_file_id')
                  ->references('id')->on('files')
                  ->nullOnDelete()->cascadeOnUpdate();
        });

        // ────────────────────────────────────────────────────────
        // 16. SETTINGS — Cấu hình hệ thống
        // ────────────────────────────────────────────────────────
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key_name', 100)->unique()
                  ->comment('Khoá cấu hình');
            $table->text('value')->nullable()->comment('Giá trị');
            $table->string('description')->nullable()
                  ->comment('Mô tả cấu hình');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Drop deferred FKs trước
        Schema::table('question_options', function (Blueprint $table) {
            $table->dropForeign(['image_file_id']);
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['image_file_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['avatar_file_id']);
        });

        // Drop tables theo thứ tự ngược FK
        Schema::dropIfExists('settings');
        Schema::dropIfExists('files');
        Schema::dropIfExists('question_tags');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_types');
        Schema::dropIfExists('course_section_students');
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('course_sections');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('semesters');

        // Remove EMS columns from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex('idx_users_department');
            $table->dropColumn([
                'phone', 'avatar_file_id', 'student_code',
                'lecturer_code', 'class_name', 'department', 'is_active',
            ]);
        });

        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
};
