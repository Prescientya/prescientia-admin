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
        // For PostgreSQL: modify enum to add 'manual'
        // For MySQL: modify enum
        Schema::table('student_attendances', function (Blueprint $table) {
            // Drop old enum constraint and recreate with 'manual' added
            $table->dropColumn('source');
        });

        Schema::table('student_attendances', function (Blueprint $table) {
            $table->enum('source', ['digital_wifi', 'guru_pengajar', 'wali_kelas', 'manual', 'self_report'])->default('digital_wifi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        Schema::table('student_attendances', function (Blueprint $table) {
            $table->enum('source', ['digital_wifi', 'guru_pengajar', 'wali_kelas', 'self_report'])->default('digital_wifi');
        });
    }
};
