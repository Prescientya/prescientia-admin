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
        if (! Schema::hasTable('student_class_roles')) {
            return;
        }

        // Make class_id nullable
        try {
            if (Schema::hasColumn('student_class_roles', 'class_id')) {
                DB::statement('ALTER TABLE student_class_roles ALTER COLUMN class_id DROP NOT NULL');
            }
        } catch (\Throwable $e) {
            // ignore if cannot change
        }

        // Replace enum role with varchar and default 'pelajar'
        try {
            if (Schema::hasColumn('student_class_roles', 'role')) {
                // add new temporary column
                if (! Schema::hasColumn('student_class_roles', 'role_new')) {
                    Schema::table('student_class_roles', function (Blueprint $table) {
                        $table->string('role_new', 50)->default('pelajar');
                    });
                }

                // migrate existing values
                DB::statement("UPDATE student_class_roles SET role_new = CASE WHEN role IN ('KM','WKM','Sekretaris') THEN role ELSE 'pelajar' END");

                // drop old enum column and rename new
                Schema::table('student_class_roles', function (Blueprint $table) {
                    $table->dropColumn('role');
                });
                DB::statement("ALTER TABLE student_class_roles RENAME COLUMN role_new TO role");
            }
        } catch (\Throwable $e) {
            // ignore on failure
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('student_class_roles')) {
            return;
        }

        // Try to revert role back to enum-like values (best-effort)
        try {
            if (Schema::hasColumn('student_class_roles', 'role')) {
                // create temp enum column
                Schema::table('student_class_roles', function (Blueprint $table) {
                    $table->string('role_old', 50)->nullable();
                });

                DB::statement("UPDATE student_class_roles SET role_old = role");
                Schema::table('student_class_roles', function (Blueprint $table) {
                    $table->dropColumn('role');
                });
                DB::statement("ALTER TABLE student_class_roles RENAME COLUMN role_old TO role");
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Make class_id NOT NULL again (best-effort)
        try {
            if (Schema::hasColumn('student_class_roles', 'class_id')) {
                DB::statement('ALTER TABLE student_class_roles ALTER COLUMN class_id SET NOT NULL');
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
