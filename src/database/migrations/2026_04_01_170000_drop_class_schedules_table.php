<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('class_schedules');
    }

    public function down(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week')->comment('2=Monday, ..., 8=Sunday');
            $table->unsignedTinyInteger('start_period');
            $table->unsignedTinyInteger('end_period');
            $table->string('room', 100)->nullable();
            $table->timestamps();
        });
    }
};
