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
        Schema::table('exams', function (Blueprint $table) {
            // Lý do mở lại đề thi (bắt buộc khi reopen)
            $table->text('reopen_reason')->nullable()->after('status');
            // Soft-delete: đề có attempt chỉ xoá mềm
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('reopen_reason');
            $table->dropSoftDeletes();
        });
    }
};
