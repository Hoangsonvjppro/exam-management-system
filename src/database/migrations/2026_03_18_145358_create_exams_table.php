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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')->constrained()->cascadeOnDelete();
            $table->string('title'); //tên bài kiểm tra
            $table->text('description')->nullable(); //mô tả bài kiểm tra
            $table->integer('duration_minutes')->default(60); //thời gian làm bài (phút)
            $table->dateTime('start_time'); //thời gian bắt đầu bài kiểm tra
            $table->dateTime('end_time')->nullable(); //thời gian kết thúc bài kiểm tra
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');// loai trạng thái của bài kiểm tra
            $table->decimal('total_points',5,2)->default(10.00); //tổng điểm của bài kiểm tra
            $table->decimal('pass_points',5,2)->default(5.00); //điểm đạt của bài kiểm tra
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
