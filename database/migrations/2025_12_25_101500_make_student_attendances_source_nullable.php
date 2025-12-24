<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make `source` nullable and remove default value.
        try {
            $driver = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (\Exception $e) {
            $driver = null;
        }

        if ($driver === 'pgsql') {
            // For PostgreSQL: drop default and drop NOT NULL
            DB::statement("ALTER TABLE student_attendances ALTER COLUMN source DROP DEFAULT");
            DB::statement("ALTER TABLE student_attendances ALTER COLUMN source DROP NOT NULL");
        } else {
            // For MySQL: modify enum to be nullable with no default
            DB::statement("ALTER TABLE student_attendances MODIFY COLUMN `source` ENUM('digital_wifi','guru_pengajar','wali_kelas','manual','self_report') NULL DEFAULT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to NOT NULL with default 'digital_wifi'
        try {
            $driver = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (\Exception $e) {
            $driver = null;
        }

        if ($driver === 'pgsql') {
            // Set null values to default first
            DB::statement("UPDATE student_attendances SET source = 'digital_wifi' WHERE source IS NULL");
            DB::statement("ALTER TABLE student_attendances ALTER COLUMN source SET DEFAULT 'digital_wifi'");
            DB::statement("ALTER TABLE student_attendances ALTER COLUMN source SET NOT NULL");
        } else {
            DB::statement("UPDATE student_attendances SET source = 'digital_wifi' WHERE source IS NULL");
            DB::statement("ALTER TABLE student_attendances MODIFY COLUMN `source` ENUM('digital_wifi','guru_pengajar','wali_kelas','manual','self_report') NOT NULL DEFAULT 'digital_wifi'");
        }
    }
};
