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
        if (! Schema::hasTable('teacher_class_roles')) {
            return;
        }

        // Drop foreign key and column if it exists
        try {
            if (Schema::hasColumn('teacher_class_roles', 'class_id')) {
                // drop foreign key constraint if exists (Postgres naming may vary)
                // find constraint name
                $constraints = DB::select("SELECT conname FROM pg_constraint WHERE conrelid = 'teacher_class_roles'::regclass AND contype = 'f'");
                foreach ($constraints as $c) {
                    $name = $c->conname;
                    // attempt to drop constraint
                    try { DB::statement("ALTER TABLE teacher_class_roles DROP CONSTRAINT \"$name\""); } catch (\Throwable $e) {}
                }

                Schema::table('teacher_class_roles', function (Blueprint $table) {
                    $table->dropColumn('class_id');
                });
            }
        } catch (\Throwable $e) {
            // if any error, ignore to keep migration safe
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('teacher_class_roles')) {
            return;
        }

        if (! Schema::hasColumn('teacher_class_roles', 'class_id')) {
            Schema::table('teacher_class_roles', function (Blueprint $table) {
                $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
            });
        }
    }
};
