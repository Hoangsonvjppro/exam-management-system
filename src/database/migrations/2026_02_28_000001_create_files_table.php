<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng files quản lý tập trung mọi upload.
     * Các bảng khác tham chiếu file_id thay vì lưu path rời rạc.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uploaded_by')->nullable()->comment('Người upload (NULL=hệ thống)');
            $table->string('disk', 20)->default('local')->comment('Storage disk: local, s3, minio...');
            $table->string('path', 500)->comment('Đường dẫn trên disk');
            $table->string('original_name')->comment('Tên file gốc người dùng upload');
            $table->string('mime_type', 100)->comment('MIME type: application/pdf, image/png...');
            $table->string('extension', 20)->comment('Phần mở rộng: pdf, docx, png...');
            $table->unsignedBigInteger('size')->default(0)->comment('Dung lượng (bytes)');
            $table->string('checksum', 64)->nullable()->comment('SHA-256 hash để phát hiện file trùng');
            $table->boolean('is_public')->default(false)->comment('true=Public URL, false=Cần signed URL');
            $table->string('used_by_type', 100)->nullable()->comment('Polymorphic type: App\\Models\\Document...');
            $table->unsignedBigInteger('used_by_id')->nullable()->comment('ID bản ghi sử dụng file này');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete - chờ dọn rác');

            $table->index('uploaded_by');
            $table->index('disk');
            $table->index('mime_type');
            $table->index('checksum');
            $table->index(['used_by_type', 'used_by_id'], 'idx_files_used_by');
            $table->index(['used_by_type', 'deleted_at'], 'idx_files_orphan');

            $table->foreign('uploaded_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // Thêm FK users.avatar_file_id → files (circular dependency resolved)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('avatar_file_id')
                ->references('id')
                ->on('files')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xoá FK trước khi drop bảng
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['avatar_file_id']);
        });

        Schema::dropIfExists('files');
    }
};
