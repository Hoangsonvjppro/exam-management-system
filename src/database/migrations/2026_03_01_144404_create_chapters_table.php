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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->string('name')->comment('Tên chương');
            $table->unsignedInteger('order')->default(0)->comment('Thứ tự sắp xếp');
            $table->text('description')->nullable;
            $table->timestamps();

            $table->index('subject_id', 'idx_chapters_subject');
            $table->index(['subject_id', 'order'], 'idx_chapters_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
