<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabel kehadiran per jam pelajaran untuk siswa dan guru.
     * Admin bisa mengisi absen per jam; auto-fill dari absensi harian.
     */
    public function up(): void
    {
        // ── Kehadiran Siswa Per Jam ──────────────────────
        Schema::create('student_attendance_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')
                  ->constrained('student_attendances')->cascadeOnDelete()
                  ->comment('FK ke student_attendances (kehadiran harian)');
            $table->foreignId('class_period_id')
                  ->constrained('class_periods')->cascadeOnDelete()
                  ->comment('FK ke class_periods (jam pelajaran)');
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa', 'terlambat', 'dispen'])
                  ->default('hadir')
                  ->comment('Status kehadiran di jam ini');
            $table->timestamps();

            $table->unique(['attendance_id', 'class_period_id'], 'uniq_student_att_period');
            $table->index('class_period_id');
        });

        // ── Kehadiran Guru Per Jam ──────────────────────
        Schema::create('teacher_attendance_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_attendance_id')
                  ->constrained('teacher_attendances')->cascadeOnDelete()
                  ->comment('FK ke teacher_attendances (kehadiran harian guru)');
            $table->foreignId('class_period_id')
                  ->constrained('class_periods')->cascadeOnDelete()
                  ->comment('FK ke class_periods (jam pelajaran)');
            $table->enum('status', ['hadir', 'sakit', 'izin', 'dinas', 'alpa', 'terlambat'])
                  ->default('hadir')
                  ->comment('Status kehadiran guru di jam ini');
            $table->timestamps();

            $table->unique(['teacher_attendance_id', 'class_period_id'], 'uniq_teacher_att_period');
            $table->index('class_period_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance_periods');
        Schema::dropIfExists('student_attendance_periods');
    }
};
