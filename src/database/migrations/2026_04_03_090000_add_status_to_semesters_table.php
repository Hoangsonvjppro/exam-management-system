<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table): void {
            $table->string('status', 20)->default('upcoming')->after('end_date')->index();
        });

        $today = Carbon::today();

        DB::table('semesters')
            ->orderBy('start_date')
            ->get(['id', 'start_date', 'end_date', 'is_current'])
            ->each(function ($semester) use ($today): void {
                $startDate = Carbon::parse($semester->start_date)->startOfDay();
                $endDate = Carbon::parse($semester->end_date)->endOfDay();

                if ($semester->is_current || ($startDate->lte($today) && $endDate->gte($today))) {
                    $status = 'current';
                } elseif ($startDate->gt($today)) {
                    $status = 'upcoming';
                } else {
                    $status = 'ended';
                }

                DB::table('semesters')
                    ->where('id', $semester->id)
                    ->update([
                        'status' => $status,
                        'is_current' => $status === 'current',
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
