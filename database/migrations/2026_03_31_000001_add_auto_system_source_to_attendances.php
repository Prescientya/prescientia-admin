<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'auto_system' to the source enum for both student and teacher attendances.
     * This source indicates the record was created by the scheduled auto-alpa command.
     */
    public function up(): void
    {
        // For PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Student attendances - recreate check constraint
            DB::statement("ALTER TABLE student_attendances DROP CONSTRAINT IF EXISTS student_attendances_source_check");
            DB::statement("ALTER TABLE student_attendances ADD CONSTRAINT student_attendances_source_check CHECK (source::text = ANY (ARRAY['digital_wifi'::character varying, 'guru_pengajar'::character varying, 'wali_kelas'::character varying, 'manual'::character varying, 'self_report'::character varying, 'auto_system'::character varying]::text[]))");

            // Teacher attendances - recreate check constraint  
            DB::statement("ALTER TABLE teacher_attendances DROP CONSTRAINT IF EXISTS teacher_attendances_source_check");
            DB::statement("ALTER TABLE teacher_attendances ADD CONSTRAINT teacher_attendances_source_check CHECK (source::text = ANY (ARRAY['digital_wifi'::character varying, 'manual'::character varying, 'self_report'::character varying, 'auto_system'::character varying]::text[]))");
        }

        // For MySQL - modify enum
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE student_attendances MODIFY COLUMN source ENUM('digital_wifi', 'guru_pengajar', 'wali_kelas', 'manual', 'self_report', 'auto_system') NULL");
            DB::statement("ALTER TABLE teacher_attendances MODIFY COLUMN source ENUM('digital_wifi', 'manual', 'self_report', 'auto_system') NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update any auto_system records to manual before removing the enum value
        DB::table('student_attendances')->where('source', 'auto_system')->update(['source' => 'manual']);
        DB::table('teacher_attendances')->where('source', 'auto_system')->update(['source' => 'manual']);

        // For PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE student_attendances DROP CONSTRAINT IF EXISTS student_attendances_source_check");
            DB::statement("ALTER TABLE student_attendances ADD CONSTRAINT student_attendances_source_check CHECK (source::text = ANY (ARRAY['digital_wifi'::character varying, 'guru_pengajar'::character varying, 'wali_kelas'::character varying, 'manual'::character varying, 'self_report'::character varying]::text[]))");

            DB::statement("ALTER TABLE teacher_attendances DROP CONSTRAINT IF EXISTS teacher_attendances_source_check");
            DB::statement("ALTER TABLE teacher_attendances ADD CONSTRAINT teacher_attendances_source_check CHECK (source::text = ANY (ARRAY['digital_wifi'::character varying, 'manual'::character varying, 'self_report'::character varying]::text[]))");
        }

        // For MySQL
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE student_attendances MODIFY COLUMN source ENUM('digital_wifi', 'guru_pengajar', 'wali_kelas', 'manual', 'self_report') NULL");
            DB::statement("ALTER TABLE teacher_attendances MODIFY COLUMN source ENUM('digital_wifi', 'manual', 'self_report') NULL");
        }
    }
};
