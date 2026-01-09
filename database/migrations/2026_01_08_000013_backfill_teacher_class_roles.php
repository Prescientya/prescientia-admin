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
        // Backfill disabled - not needed for current application flow
        // Teachers should have roles assigned explicitly through the application
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
