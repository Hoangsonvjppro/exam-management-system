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
        Schema::create('question_tag_map', function (Blueprint $table) {
            // Không cần cột ID riêng, dùng Primary Key phức hợp
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            
            // Thiết lập Primary Key để một câu hỏi không bị gắn trùng 1 tag 2 lần
            $table->primary(['question_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_tag_map');
    }
};
