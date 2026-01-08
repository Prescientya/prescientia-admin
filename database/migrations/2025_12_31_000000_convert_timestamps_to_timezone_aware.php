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
        $driver = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            // PostgreSQL: convert datetime columns to TIMESTAMP WITH TIME ZONE
            DB::statement('ALTER TABLE student_attendances ALTER COLUMN check_in_time TYPE TIMESTAMP WITH TIME ZONE');
            DB::statement('ALTER TABLE student_attendances ALTER COLUMN check_out_time TYPE TIMESTAMP WITH TIME ZONE');
            DB::statement('ALTER TABLE student_attendances ALTER COLUMN created_at TYPE TIMESTAMP WITH TIME ZONE');
            DB::statement('ALTER TABLE student_attendances ALTER COLUMN updated_at TYPE TIMESTAMP WITH TIME ZONE');

            DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN check_in_time TYPE TIMESTAMP WITH TIME ZONE');
            DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN check_out_time TYPE TIMESTAMP WITH TIME ZONE');
            DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN created_at TYPE TIMESTAMP WITH TIME ZONE');
            DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN updated_at TYPE TIMESTAMP WITH TIME ZONE');
        } else {
            // MySQL: use DATETIME as fallback (MySQL DATETIME doesn't store timezone info, but we'll handle conversion in app)
            // For true timezone support in MySQL, consider using TIMESTAMP or storing as VARCHAR with offset
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->dateTime('check_in_time')->change();
                $table->dateTime('check_out_time')->change();
            });

            Schema::table('teacher_attendances', function (Blueprint $table) {
                $table->dateTime('check_in_time')->change();
                $table->dateTime('check_out_time')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            // Revert to TIMESTAMP WITHOUT TIME ZONE (original behavior)
            DB::statement('ALTER TABLE student_attendances ALTER COLUMN check_in_time TYPE TIMESTAMP WITHOUT TIME ZONE');
            DB::statement('ALTER TABLE student_attendances ALTER COLUMN check_out_time TYPE TIMESTAMP WITHOUT TIME ZONE');
            DB::statement('ALTER TABLE student_attendances ALTER COLUMN created_at TYPE TIMESTAMP WITHOUT TIME ZONE');
            DB::statement('ALTER TABLE student_attendances ALTER COLUMN updated_at TYPE TIMESTAMP WITHOUT TIME ZONE');

            DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN check_in_time TYPE TIMESTAMP WITHOUT TIME ZONE');
            DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN check_out_time TYPE TIMESTAMP WITHOUT TIME ZONE');
            DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN created_at TYPE TIMESTAMP WITHOUT TIME ZONE');
            DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN updated_at TYPE TIMESTAMP WITHOUT TIME ZONE');
        }
    }
};
