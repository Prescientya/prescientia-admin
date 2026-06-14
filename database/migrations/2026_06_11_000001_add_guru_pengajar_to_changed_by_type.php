<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menambahkan nilai 'guru_pengajar' ke ENUM attendance_status_changes.changed_by_type.
 *
 * Konteks: backend (Prescientia-BE) mencatat perubahan status kehadiran oleh guru
 * pengajar dengan nilai 'guru_pengajar' (selaras dengan kolom student_attendances.source
 * dan ALLOWED_SOURCES). Namun ENUM kolom ini hanya memuat ['wali_kelas','pengajar','siswa'],
 * sehingga INSERT gagal: "Data truncated for column 'changed_by_type'".
 *
 * Migrasi ini bersifat ADDITIVE & backward-compatible: member lama tetap valid (baris
 * existing dengan 'pengajar' tidak berubah), hanya menambah 'guru_pengajar'.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE attendance_status_changes "
            . "MODIFY COLUMN changed_by_type "
            . "ENUM('wali_kelas', 'pengajar', 'siswa', 'guru_pengajar') NOT NULL "
            . "COMMENT 'Jenis pengubah: walikelas, pengajar mapel, atau petugas kelas (KM/WKM/Sekretaris)'"
        );
    }

    public function down(): void
    {
        // Normalisasi baris yang sudah memakai 'guru_pengajar' agar tidak ter-truncate
        // saat ENUM dipersempit kembali.
        DB::table('attendance_status_changes')
            ->where('changed_by_type', 'guru_pengajar')
            ->update(['changed_by_type' => 'pengajar']);

        DB::statement(
            "ALTER TABLE attendance_status_changes "
            . "MODIFY COLUMN changed_by_type "
            . "ENUM('wali_kelas', 'pengajar', 'siswa') NOT NULL "
            . "COMMENT 'Jenis pengubah: walikelas, pengajar mapel, atau petugas kelas (KM/WKM/Sekretaris)'"
        );
    }
};
