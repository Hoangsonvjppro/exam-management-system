<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Bảng Cache & Jobs mặc định của Laravel
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration')->index();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 2. Các bảng Hệ thống của dự án (Files, Settings, Logs)
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk', 20)->default('local');
            $table->string('path', 500);
            $table->string('original_name');
            $table->string('mime_type', 100)->index();
            $table->string('extension', 20);
            $table->unsignedBigInteger('size')->default(0);
            $table->string('checksum', 64)->nullable()->index();
            $table->boolean('is_public')->default(false);
            $table->string('used_by_type', 100)->nullable();
            $table->unsignedBigInteger('used_by_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['used_by_type', 'used_by_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('avatar_file_id')->references('id')->on('files')->nullOnDelete();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key_name', 100)->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100)->index();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->index(['model_type', 'model_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('action', 120);
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 100)->index();
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
        });

        // 3. Bảng Telescope
        $telescopeConnection = config('telescope.storage.database.connection');
        $schema = Schema::connection($telescopeConnection);

        $schema->create('telescope_entries', function (Blueprint $table) {
            $table->bigIncrements('sequence');
            $table->uuid('uuid')->unique();
            $table->uuid('batch_id')->index();
            $table->string('family_hash')->nullable()->index();
            $table->boolean('should_display_on_index')->default(true);
            $table->string('type', 20);
            $table->longText('content');
            $table->dateTime('created_at')->nullable()->index();
            $table->index(['type', 'should_display_on_index']);
        });

        $schema->create('telescope_entries_tags', function (Blueprint $table) {
            $table->uuid('entry_uuid');
            $table->string('tag')->index();
            $table->primary(['entry_uuid', 'tag']);
            $table->foreign('entry_uuid')->references('uuid')->on('telescope_entries')->cascadeOnDelete();
        });

        $schema->create('telescope_monitoring', function (Blueprint $table) {
            $table->string('tag')->primary();
        });
    }

    public function down(): void
    {
        $schema = Schema::connection(config('telescope.storage.database.connection'));
        $schema->dropIfExists('telescope_entries_tags');
        $schema->dropIfExists('telescope_entries');
        $schema->dropIfExists('telescope_monitoring');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['avatar_file_id']);
        });
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('files');

        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};
