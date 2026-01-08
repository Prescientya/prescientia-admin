<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration consolidates logic from the previous problematic migration:
     * - Ensures `classes` table has integer `class` column (not duplicate)
     * - Removes `class_code` from `mbg_class_daily`
     * - Makes attendance `source` columns nullable
     */
    public function up(): void
    {
        // Only proceed if classes table exists
        if (! Schema::hasTable('classes')) {
            return;
        }

        // ===== CLASSES TABLE =====
        // The base migration already created `class` as integer, so skip adding it.
        // If somehow `class_name` exists (from old schema), migrate and drop it.
        if (Schema::hasColumn('classes', 'class_name')) {
            // Migrate values: extract leading number from class_name
            $rows = DB::table('classes')->select('id', 'class_name')->get();
            foreach ($rows as $row) {
                $num = null;
                if (is_string($row->class_name) && preg_match('/(\d+)/', $row->class_name, $m)) {
                    $num = (int) $m[1];
                }

                DB::table('classes')->where('id', $row->id)->update([
                    'class' => $num,
                ]);
            }

            // Drop old column
            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('class_name');
            });
        }

        // ===== MBG_CLASS_DAILY TABLE =====
        // Remove `class_code` if it exists (no longer needed)
        if (Schema::hasTable('mbg_class_daily') && Schema::hasColumn('mbg_class_daily', 'class_code')) {
            Schema::table('mbg_class_daily', function (Blueprint $table) {
                $table->dropColumn('class_code');
            });
        }

        // ===== ATTENDANCE SOURCE COLUMNS =====
        // Make source columns nullable in both student and teacher attendances
        if (Schema::hasTable('student_attendances') && Schema::hasColumn('student_attendances', 'source')) {
            try {
                DB::statement('ALTER TABLE student_attendances ALTER COLUMN source DROP NOT NULL');
            } catch (\Throwable $e) {
                // Column already nullable or DB engine doesn't support this syntax
            }
        }

        if (Schema::hasTable('teacher_attendances') && Schema::hasColumn('teacher_attendances', 'source')) {
            try {
                DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN source DROP NOT NULL');
            } catch (\Throwable $e) {
                // Column already nullable or DB engine doesn't support this syntax
            }
        }

        // Set teacher attendance with status 'alpa' to have NULL source
        if (Schema::hasTable('teacher_attendances')) {
            try {
                DB::statement("UPDATE teacher_attendances SET source = NULL WHERE status = 'alpa'");
            } catch (\Throwable $e) {
                // Table doesn't exist yet or query fails
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('classes')) {
            return;
        }

        // Recreate `class_name` column from numeric `class` for old schema compatibility
        if (! Schema::hasColumn('classes', 'class_name')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->string('class_name')->nullable();
            });

            $rows = DB::table('classes')->select('id', 'class')->get();
            foreach ($rows as $row) {
                $name = null;
                if (!is_null($row->class)) {
                    // Map numbers to standard class names
                    if ($row->class == 10) $name = '10/X';
                    elseif ($row->class == 11) $name = '11/XI';
                    elseif ($row->class == 12) $name = '12/XII';
                    else $name = (string) $row->class;
                }

                DB::table('classes')->where('id', $row->id)->update([
                    'class_name' => $name,
                ]);
            }
        }

        // Recreate `class_code` in mbg_class_daily
        if (Schema::hasTable('mbg_class_daily') && ! Schema::hasColumn('mbg_class_daily', 'class_code')) {
            Schema::table('mbg_class_daily', function (Blueprint $table) {
                $table->string('class_code')->nullable();
            });
        }

        // Revert source nullability (make NOT NULL again with safe defaults)
        if (Schema::hasTable('student_attendances') && Schema::hasColumn('student_attendances', 'source')) {
            try {
                DB::statement("UPDATE student_attendances SET source = 'wali_kelas' WHERE source IS NULL");
                DB::statement('ALTER TABLE student_attendances ALTER COLUMN source SET NOT NULL');
            } catch (\Throwable $e) {
                // Revert failed; continue
            }
        }

        if (Schema::hasTable('teacher_attendances') && Schema::hasColumn('teacher_attendances', 'source')) {
            try {
                DB::statement("UPDATE teacher_attendances SET source = 'manual' WHERE source IS NULL");
                DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN source SET NOT NULL');
            } catch (\Throwable $e) {
                // Revert failed; continue
            }
        }
    }
};
