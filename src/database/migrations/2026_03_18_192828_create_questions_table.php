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
    {   Schema::create('questions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('subject_id')->constrained();
        $table->foreignId('difficulty_id')->constrained('difficulties');
        $table->text('content');
        $table->unsignedBigInteger('correct_option_id')->nullable(); // Để nullable để update sau
        $table->text('explanation')->nullable();
        $table->decimal('correct_rate', 5, 2)->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
