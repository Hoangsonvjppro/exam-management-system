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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')
                  ->nullable()
                  ->after('class_name')
                  ->constrained('departments')
                  ->nullOnDelete();

            $table->foreignId('student_class_id')
                  ->nullable()
                  ->after('class_name')
                  ->constrained('student_classes')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['student_class_id']);
            $table->dropColumn(['department_id', 'student_class_id']);
        });
    }
};
