<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->json('question_snapshot')->nullable()->after('order_index')
                ->comment('Bản sao JSON của question + options tại thời điểm tạo đề');
        });
    }

    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn('question_snapshot');
        });
    }
};
