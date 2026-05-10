<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('student_attendances', 'updated_by_role')) {
                $table->string('updated_by_role', 50)->nullable();
            }

            if (!Schema::hasColumn('student_attendances', 'updated_by_teacher_id')) {
                $table->unsignedBigInteger('updated_by_teacher_id')->nullable();
            }

            if (!Schema::hasColumn('student_attendances', 'change_reason')) {
                $table->text('change_reason')->nullable();
            }
        });

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement(
                "DO $$
                BEGIN
                  IF NOT EXISTS (
                    SELECT 1
                    FROM pg_constraint
                    WHERE conname = 'student_attendances_updated_by_teacher_id_fkey'
                  ) THEN
                    ALTER TABLE student_attendances
                      ADD CONSTRAINT student_attendances_updated_by_teacher_id_fkey
                      FOREIGN KEY (updated_by_teacher_id)
                      REFERENCES teachers(id)
                      ON DELETE SET NULL;
                  END IF;
                END $$;"
            );

            DB::statement(
                "CREATE OR REPLACE FUNCTION set_student_attendances_updated_at()
                RETURNS TRIGGER AS $$
                BEGIN
                  NEW.updated_at = NOW();
                  RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;"
            );

            DB::statement('DROP TRIGGER IF EXISTS trg_student_attendances_set_updated_at ON student_attendances');
            DB::statement(
                'CREATE TRIGGER trg_student_attendances_set_updated_at
                BEFORE UPDATE ON student_attendances
                FOR EACH ROW
                EXECUTE FUNCTION set_student_attendances_updated_at()'
            );
            return;
        }

        if ($driver === 'mysql') {
            try {
                DB::statement(
                    'ALTER TABLE student_attendances
                    ADD CONSTRAINT student_attendances_updated_by_teacher_id_fkey
                    FOREIGN KEY (updated_by_teacher_id)
                    REFERENCES teachers(id)
                    ON DELETE SET NULL'
                );
            } catch (\Throwable $e) {
                // Constraint may already exist.
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS trg_student_attendances_set_updated_at ON student_attendances');
            DB::statement('DROP FUNCTION IF EXISTS set_student_attendances_updated_at()');
            DB::statement('ALTER TABLE student_attendances DROP CONSTRAINT IF EXISTS student_attendances_updated_by_teacher_id_fkey');
        }

        if ($driver === 'mysql') {
            try {
                DB::statement('ALTER TABLE student_attendances DROP FOREIGN KEY student_attendances_updated_by_teacher_id_fkey');
            } catch (\Throwable $e) {
                // Constraint may not exist.
            }
        }

        Schema::table('student_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('student_attendances', 'change_reason')) {
                $table->dropColumn('change_reason');
            }

            if (Schema::hasColumn('student_attendances', 'updated_by_teacher_id')) {
                $table->dropColumn('updated_by_teacher_id');
            }

            if (Schema::hasColumn('student_attendances', 'updated_by_role')) {
                $table->dropColumn('updated_by_role');
            }
        });
    }
};
