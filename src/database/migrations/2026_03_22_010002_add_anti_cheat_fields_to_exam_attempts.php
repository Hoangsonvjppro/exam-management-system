<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('total_score');
            $table->string('user_agent', 500)->nullable()->after('ip_address');
            $table->unsignedInteger('submitted_answers_count')->default(0)->after('user_agent');
            $table->unsignedInteger('tab_switch_count')->default(0)->after('submitted_answers_count');
            $table->json('focus_lost_at')->nullable()->after('tab_switch_count')
                ->comment('Mảng timestamps khi mất focus');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'ip_address',
                'user_agent',
                'submitted_answers_count',
                'tab_switch_count',
                'focus_lost_at',
            ]);
        });
    }
};
