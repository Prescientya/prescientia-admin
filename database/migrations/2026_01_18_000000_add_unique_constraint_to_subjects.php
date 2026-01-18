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
        // Hapus data duplikat terlebih dahulu (PostgreSQL syntax)
        DB::statement('
            DELETE FROM subjects
            WHERE id NOT IN (
                SELECT DISTINCT ON (name, major, kelas) id
                FROM subjects
                ORDER BY name, major, kelas, id ASC
            )
        ');

        // Tambah unique constraint
        Schema::table('subjects', function (Blueprint $table) {
            $table->unique(['name', 'major', 'kelas'], 'subjects_name_major_kelas_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_name_major_kelas_unique');
        });
    }
};
