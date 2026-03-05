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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            // Tên học kỳ (VARCHAR 100)
        $table->string('name', 100)->comment('VD: HK1 2025-2026');

        // Năm học (SMALLINT UNSIGNED)
        $table->smallInteger('year')->unsigned()->comment('Năm học bắt đầu, VD: 2025');

        // Học kỳ số mấy (TINYINT UNSIGNED)
        $table->tinyInteger('term')->unsigned()->comment('1=HK1, 2=HK2, 3=HK Hè');

        // Ngày bắt đầu và kết thúc (DATE)
        $table->date('start_date');
        $table->date('end_date');

        // Đánh dấu học kỳ hiện tại (TINYINT 1 / BOOLEAN)
        $table->boolean('is_current')->default(0)->comment('Đánh dấu học kỳ hiện tại');

        // Tự động tạo created_at và updated_at
        $table->timestamps();

        // Thiết lập UNIQUE KEY cho cặp (year, term)
        $table->unique(['year', 'term'], 'uk_semesters_year_term');

        // Thiết lập INDEX cho cột is_current để tìm kiếm nhanh
        $table->index('is_current', 'idx_semesters_is_current');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
