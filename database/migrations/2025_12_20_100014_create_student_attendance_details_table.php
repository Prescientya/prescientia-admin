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
        Schema::create('student_attendance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->unique()->constrained('student_attendances')->onDelete('cascade');
            $table->enum('reason', ['sakit', 'izin', 'alpa', 'terlambat']);
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
        Schema::dropIfExists('student_attendance_details');
    }
};
