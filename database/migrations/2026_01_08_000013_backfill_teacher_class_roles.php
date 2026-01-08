<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert a default 'pengajar' role for teachers that have no role records
        $teacherIds = DB::table('teachers')
            ->leftJoin('teacher_class_roles', 'teachers.id', '=', 'teacher_class_roles.teacher_id')
            ->whereNull('teacher_class_roles.id')
            ->pluck('teachers.id')
            ->toArray();

        $now = now();

        $inserts = [];
        foreach ($teacherIds as $tid) {
            $inserts[] = [
                'teacher_id' => $tid,
                'class_id' => null,
                'role' => 'pengajar',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($inserts)) {
            DB::table('teacher_class_roles')->insert($inserts);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove roles that were created without class assignment and have role 'pengajar'
        DB::table('teacher_class_roles')
            ->whereNull('class_id')
            ->where('role', 'pengajar')
            ->delete();
    }
};
