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
        Schema::create('student_classes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();       // Mã lớp: DKP1235
            $table->string('name');                 // Tên lớp: Kỹ thuật phần mềm - K.23 - lớp 5
            $table->foreignId('major_id')
                ->constrained('majors');
            $table->year('academic_year');          // Khóa nhập học: 2023
            $table->unsignedTinyInteger('class_group')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['major_id', 'academic_year', 'class_group'], 'uq_class_major_year_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_classes');
    }
};
