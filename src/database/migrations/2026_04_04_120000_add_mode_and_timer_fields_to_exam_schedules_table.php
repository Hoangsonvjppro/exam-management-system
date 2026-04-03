<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->string('schedule_mode', 20)
                ->default('within_day')
                ->after('end_date');

            $table->boolean('disable_attempt_timer')
                ->default(false)
                ->after('schedule_mode');
        });
    }

    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropColumn(['schedule_mode', 'disable_attempt_timer']);
        });
    }
};
