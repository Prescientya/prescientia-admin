<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabel ini mencatat setiap perubahan status kehadiran siswa per jam pelajaran.
     * Setiap kali walikelas, pengajar, atau petugas kelas (KM/WKM/Sekretaris) mengubah
     * status kehadiran siswa, akan dicatat di sini beserta jam ke berapa, dari status apa
     * ke status apa, dan oleh siapa perubahannya.
     */
    public function up(): void
    {
        Schema::create('attendance_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('student_attendances')->cascadeOnDelete()
                  ->comment('FK ke student_attendances (kehadiran harian siswa)');
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete()
                  ->comment('FK ke students (siswa yang statusnya diubah)');
            $table->foreignId('class_period_id')->nullable()->constrained('class_periods')->nullOnDelete()
                  ->comment('FK ke class_periods (jam pelajaran saat perubahan terjadi)');
            $table->enum('old_status', ['hadir', 'sakit', 'izin', 'alpa', 'terlambat'])
                  ->comment('Status kehadiran sebelum diubah');
            $table->enum('new_status', ['hadir', 'sakit', 'izin', 'alpa', 'terlambat'])
                  ->comment('Status kehadiran setelah diubah');
            $table->enum('changed_by_type', ['wali_kelas', 'pengajar', 'siswa'])
                  ->comment('Jenis pengubah: walikelas, pengajar mapel, atau petugas kelas (KM/WKM/Sekretaris)');
            $table->unsignedBigInteger('changed_by_id')
                  ->comment('ID dari pengubah (teacher_id atau student_id)');
            $table->string('changed_by_name', 100)
                  ->comment('Nama pengubah (denormalisasi untuk kemudahan tampilan)');
            $table->text('note')->nullable()
                  ->comment('Alasan/catatan perubahan (opsional)');
            $table->timestamps();

            // Index untuk query: semua perubahan pada satu attendance record
            $table->index('attendance_id');
            // Index untuk query: semua perubahan oleh satu pengubah
            $table->index(['changed_by_type', 'changed_by_id']);
            // Index untuk query: perubahan per jam pelajaran
            $table->index('class_period_id');
            // Index untuk query: perubahan per siswa
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_status_changes');
    }
};
