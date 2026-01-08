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
        Schema::table('teachers', function (Blueprint $table) {
            // Add temporary JSON column
            if (!Schema::hasColumn('teachers', 'departments_json')) {
                $table->json('departments_json')->nullable()->after('department');
            }
        });

        // Migrate existing department data to JSON array format
        // Handle both MySQL and PostgreSQL
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql') {
            DB::statement("UPDATE teachers SET departments_json = JSON_ARRAY(department) WHERE department IS NOT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("UPDATE teachers SET departments_json = jsonb_build_array(department) WHERE department IS NOT NULL");
        }

        // Drop old department column and rename new one
        Schema::table('teachers', function (Blueprint $table) use ($driver) {
            // Drop index if it exists
            try {
                if ($driver === 'mysql') {
                    $table->dropIndex('department');
                } elseif ($driver === 'pgsql') {
                    // PostgreSQL drops index differently
                    DB::statement('DROP INDEX IF EXISTS teachers_department_index');
                }
            } catch (\Exception $e) {
                // Index might not exist, that's okay
            }
        });

        // Drop old column
        DB::statement('ALTER TABLE teachers DROP COLUMN department');
        
        // Rename temporary column
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE teachers CHANGE departments_json department JSON');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE teachers RENAME COLUMN departments_json TO department');
            // Also change type to jsonb for PostgreSQL
            DB::statement('ALTER TABLE teachers ALTER COLUMN department TYPE jsonb USING department::jsonb');
        }

        // Add index back
        $driver = DB::connection()->getDriverName();
        Schema::table('teachers', function (Blueprint $table) use ($driver) {
            if ($driver === 'mysql') {
                $table->index('department(100)');
            } elseif ($driver === 'pgsql') {
                // PostgreSQL uses GIN index for JSON queries
                $table->index('department', 'teachers_department_index', 'gin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        // Drop JSON index if exists
        try {
            if ($driver === 'mysql') {
                Schema::table('teachers', function (Blueprint $table) {
                    $table->dropIndex('teachers_department_index');
                });
            } elseif ($driver === 'pgsql') {
                DB::statement('DROP INDEX IF EXISTS teachers_department_index');
            }
        } catch (\Exception $e) {
            // Index might not exist
        }

        // Rename column back
        DB::statement('ALTER TABLE teachers ADD COLUMN department_backup VARCHAR(100) NULL');
        
        if ($driver === 'pgsql') {
            // For PostgreSQL, extract first element if it's an array
            DB::statement("UPDATE teachers SET department_backup = department::text->0 WHERE department IS NOT NULL");
        } else {
            // For MySQL, extract first element
            DB::statement("UPDATE teachers SET department_backup = JSON_EXTRACT(department, '$[0]') WHERE department IS NOT NULL");
        }

        DB::statement('ALTER TABLE teachers DROP COLUMN department');
        DB::statement('ALTER TABLE teachers RENAME COLUMN department_backup TO department');

        // Add old index back
        Schema::table('teachers', function (Blueprint $table) use ($driver) {
            if ($driver === 'mysql') {
                $table->index('department');
            } elseif ($driver === 'pgsql') {
                $table->index('department');
            }
        });
    }
};
