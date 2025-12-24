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
        // Add new integer column `class` (nullable for safe migration)
        Schema::table('classes', function (Blueprint $table) {
            $table->integer('class')->nullable()->after('class_name');
        });

        // Migrate existing values: extract leading number from class_name
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

        // Drop the old text column
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'class_name')) {
                $table->dropColumn('class_name');
            }
        });

        // Also remove `class_code` from mbg_class_daily if present
        Schema::table('mbg_class_daily', function (Blueprint $table) {
            if (Schema::hasColumn('mbg_class_daily', 'class_code')) {
                $table->dropColumn('class_code');
            }
        });

        // Make attendance `source` columns nullable (safe change)
        // Using raw statements for compatibility across DB engines
        try {
            DB::statement('ALTER TABLE student_attendances ALTER COLUMN source DROP NOT NULL');
        } catch (\Throwable $e) {
            // ignore if column doesn't exist yet or DB engine differs
        }

        try {
            DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN source DROP NOT NULL');
        } catch (\Throwable $e) {
            // ignore if column doesn't exist yet or DB engine differs
        }

        // Ensure any existing teacher attendance rows with status 'alpa' have NULL source
        try {
            DB::statement("UPDATE teacher_attendances SET source = NULL WHERE status = 'alpa'");
        } catch (\Throwable $e) {
            // ignore if table/column doesn't exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate string `class_name` column
        Schema::table('classes', function (Blueprint $table) {
            $table->string('class_name')->nullable()->after('id');
        });

        // Populate class_name from numeric `class` column
        $rows = DB::table('classes')->select('id', 'class')->get();
        foreach ($rows as $row) {
            $name = null;
            if (!is_null($row->class)) {
                // Map 10->10/X, 11->11/XI, 12->12/XII; otherwise keep numeric as string
                if ($row->class == 10) $name = '10/X';
                elseif ($row->class == 11) $name = '11/XI';
                elseif ($row->class == 12) $name = '12/XII';
                else $name = (string) $row->class;
            }

            DB::table('classes')->where('id', $row->id)->update([
                'class_name' => $name,
            ]);
        }

        // Drop the integer `class` column
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'class')) {
                $table->dropColumn('class');
            }
        });

        // Recreate `class_code` in mbg_class_daily (nullable)
        Schema::table('mbg_class_daily', function (Blueprint $table) {
            if (! Schema::hasColumn('mbg_class_daily', 'class_code')) {
                $table->string('class_code')->nullable();
            }
        });

        // Revert attendance `source` nullability: set NULLs to safe defaults before adding NOT NULL back
        try {
            DB::statement("UPDATE student_attendances SET source = 'wali_kelas' WHERE source IS NULL");
            DB::statement('ALTER TABLE student_attendances ALTER COLUMN source SET NOT NULL');
        } catch (\Throwable $e) {
            // ignore if column/constraint doesn't exist
        }

        try {
            DB::statement("UPDATE teacher_attendances SET source = 'manual' WHERE source IS NULL");
            DB::statement('ALTER TABLE teacher_attendances ALTER COLUMN source SET NOT NULL');
        } catch (\Throwable $e) {
            // ignore if column/constraint doesn't exist
        }
    }
};
