<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the unused deleted_at (soft-delete) columns.
     * The app uses hard-delete everywhere; these columns are dead weight.
     *
     * Affected tables: admins, teachers, petugas_mbg
     */
    public function up(): void
    {
        foreach (['admins', 'teachers', 'petugas_mbg'] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['admins', 'teachers', 'petugas_mbg'] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }
};
