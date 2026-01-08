<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Steps:
     * 1. Backfill `class_id` from `students` where NULL.
     * 2. Set `class_id` NOT NULL.
     * 3. Add FK constraint to `classes(id)`.
     */
    public function up()
    {
        DB::transaction(function () {
            // 1) Backfill missing class_id using students table
            DB::statement(<<<'SQL'
                UPDATE student_class_roles
                SET class_id = students.class_id
                FROM students
                WHERE student_class_roles.student_id = students.id
                  AND student_class_roles.class_id IS NULL;
            SQL
            );

            // 2) Ensure no NULLs remain (if any remain, abort to avoid invalid schema change)
            $nulls = DB::selectOne('SELECT count(*) AS c FROM student_class_roles WHERE class_id IS NULL');
            if ($nulls->c > 0) {
                throw new \RuntimeException('There are still student_class_roles rows with NULL class_id; manual review required.');
            }

            // 3) Make column NOT NULL (Postgres)
            DB::statement('ALTER TABLE student_class_roles ALTER COLUMN class_id SET NOT NULL');

            // 4) Add foreign key constraint if not exists
            DB::statement(<<<'SQL'
                DO $$
                BEGIN
                    IF NOT EXISTS (
                        SELECT 1 FROM pg_constraint WHERE conname = 'fk_student_class_roles_class_id'
                    ) THEN
                        ALTER TABLE student_class_roles
                        ADD CONSTRAINT fk_student_class_roles_class_id
                            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE;
                    END IF;
                END
                $$;
            SQL
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::transaction(function () {
            // Drop FK if exists
            DB::statement(<<<'SQL'
                DO $$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM pg_constraint WHERE conname = 'fk_student_class_roles_class_id'
                    ) THEN
                        ALTER TABLE student_class_roles DROP CONSTRAINT fk_student_class_roles_class_id;
                    END IF;
                END
                $$;
            SQL
            );

            // Allow NULL again
            DB::statement('ALTER TABLE student_class_roles ALTER COLUMN class_id DROP NOT NULL');
        });
    }
};
