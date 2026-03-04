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
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('calendar_id')->constrained('school_calendar')->onDelete('cascade');
            $table->timestamp('check_in_time')->nullable();  // PostgreSQL: $table->timestampTz('check_in_time')
            $table->timestamp('check_out_time')->nullable(); // PostgreSQL: $table->timestampTz('check_out_time')
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa', 'terlambat']);
            $table->enum('source', ['digital_wifi', 'guru_pengajar', 'wali_kelas', 'manual', 'self_report'])->nullable();
            $table->timestamps();
            
            // Unique: satu siswa satu absensi per hari
            $table->unique(['student_id', 'calendar_id']);
            // Indexes
            $table->index(['class_id', 'calendar_id']);
            $table->index('status');
            $table->index('check_in_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
