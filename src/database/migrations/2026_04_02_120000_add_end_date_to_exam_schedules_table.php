<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->date('end_date')->nullable();
        });

        DB::table('exam_schedules')
            ->whereNull('end_date')
            ->update(['end_date' => DB::raw('exam_date')]);
    }

    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });
    }
};
