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
        DB::table('student_attendances')
            ->whereRaw('LOWER(status) = ?', ['alpha'])
            ->update(['status' => 'alpa']);

        DB::table('student_attendance_details')
            ->whereRaw('LOWER(status) = ?', ['alpha'])
            ->update(['status' => 'alpa']);

        DB::table('attendance_status_changes')
            ->whereRaw('LOWER(old_status) = ?', ['alpha'])
            ->update(['old_status' => 'alpa']);

        DB::table('attendance_status_changes')
            ->whereRaw('LOWER(new_status) = ?', ['alpha'])
            ->update(['new_status' => 'alpa']);

        DB::table('student_attendance_details')
            ->where('approval_status', 'confirmed')
            ->update(['approval_status' => 'approved']);
    }

    /**
     * Reverse the migrations.
     *
     * This migration is intentionally irreversible because current enums use
     * the standardized values.
     */
    public function down(): void
    {
    }
};
