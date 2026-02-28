<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Họ và tên');
            $table->string('email')->unique()->comment('Email đăng nhập');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable()->comment('Số điện thoại');
            $table->unsignedBigInteger('avatar_file_id')->nullable()->comment('FK → files (thêm sau)');
            $table->string('student_code', 20)->nullable()->unique()->comment('Mã số sinh viên (MSSV)');
            $table->string('lecturer_code', 20)->nullable()->unique()->comment('Mã giảng viên');
            $table->string('class_name', 100)->nullable()->comment('Lớp sinh hoạt (dành cho SV)');
            $table->string('department')->nullable()->comment('Khoa / Bộ môn');
            $table->boolean('is_active')->default(true)->comment('1=Hoạt động, 0=Bị khoá');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('department');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
