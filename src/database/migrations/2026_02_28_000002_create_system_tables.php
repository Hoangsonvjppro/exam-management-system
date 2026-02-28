<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tạo 3 bảng hệ thống: settings, activity_logs, notifications
     */
    public function up(): void
    {
        // ─── 1. SETTINGS ─────────────────────────────────────────
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key_name', 100)->unique()->comment('Khoá cấu hình');
            $table->text('value')->nullable()->comment('Giá trị');
            $table->string('description')->nullable()->comment('Mô tả cấu hình');
            $table->timestamps();
        });

        // ─── 2. ACTIVITY_LOGS (Audit Trail) ──────────────────────
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Người thực hiện (NULL=hệ thống)');
            $table->string('action', 100)->comment('VD: created, updated, deleted, login, logout');
            $table->string('model_type')->nullable()->comment('Tên model: App\\Models\\ExamPaper');
            $table->unsignedBigInteger('model_id')->nullable()->comment('ID của bản ghi bị tác động');
            $table->text('description')->nullable()->comment('Mô tả hành động');
            $table->json('old_values')->nullable()->comment('Giá trị cũ (trước khi thay đổi)');
            $table->json('new_values')->nullable()->comment('Giá trị mới (sau khi thay đổi)');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('user_id');
            $table->index('action');
            $table->index(['model_type', 'model_id'], 'idx_al_model');
            $table->index('created_at');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // ─── 3. NOTIFICATIONS ─────────────────────────────────────
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('UUID');
            $table->unsignedBigInteger('user_id')->comment('Người nhận thông báo');
            $table->string('type', 100)->comment('Loại: exam_created, score_published, document_uploaded...');
            $table->string('title')->comment('Tiêu đề thông báo');
            $table->text('message')->comment('Nội dung thông báo');
            $table->json('data')->nullable()->comment('Dữ liệu bổ sung (link, ID liên quan...)');
            $table->dateTime('read_at')->nullable()->comment('Thời điểm đã đọc (NULL=chưa đọc)');
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'read_at'], 'idx_notif_user_read');
            $table->index('type');
            $table->index('created_at');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
    }
};
