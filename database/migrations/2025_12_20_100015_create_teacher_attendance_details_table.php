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
        Schema::create('teacher_attendance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->unique()->constrained('teacher_attendances')->onDelete('cascade');
            $table->foreignId('period_id')->nullable()->constrained('class_periods')->cascadeOnDelete();
            $table->enum('reason', ['sakit', 'izin', 'dinas', 'alpa', 'terlambat']);
            $table->text('description')->nullable();
            $table->string('evidence_url', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance_details');
    }
};
