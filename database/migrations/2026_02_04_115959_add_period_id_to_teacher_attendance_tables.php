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
        Schema::table('teacher_attendances', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable()->constrained('class_periods')->cascadeOnDelete();
        });

        Schema::table('teacher_attendance_details', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable()->constrained('class_periods')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_attendances', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropColumn('period_id');
        });

        Schema::table('teacher_attendance_details', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropColumn('period_id');
        });
    }
};
