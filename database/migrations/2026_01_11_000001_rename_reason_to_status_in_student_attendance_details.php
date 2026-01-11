<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Mengubah field 'reason' menjadi 'status' di table student_attendance_details
     * Field ini merepresentasikan alasan ketidakhadiran siswa (sakit, izin, alpa, terlambat)
     * dan direlasikan dengan status di table student_attendances
     */
    public function up(): void
    {
        Schema::table('student_attendance_details', function (Blueprint $table) {
            // Ganti nama kolom reason menjadi status
            $table->renameColumn('reason', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendance_details', function (Blueprint $table) {
            // Kembalikan nama kolom status menjadi reason
            $table->renameColumn('status', 'reason');
        });
    }
};
