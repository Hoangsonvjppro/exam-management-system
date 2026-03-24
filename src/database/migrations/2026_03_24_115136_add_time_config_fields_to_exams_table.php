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
            $table->boolean('allow_late_entrance')->default(true)->after('duration_minutes');
            $table->integer('late_entrance_limit_minutes')->nullable()->after('allow_late_entrance');
            $table->enum('late_entrance_behavior', ['fixed_end', 'flexible_duration'])->default('fixed_end')->after('late_entrance_limit_minutes');
            $table->integer('min_duration_before_submit')->default(0)->after('late_entrance_behavior');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'allow_late_entrance',
                'late_entrance_limit_minutes',
                'late_entrance_behavior',
                'min_duration_before_submit',
            ]);
        });
    }
};
