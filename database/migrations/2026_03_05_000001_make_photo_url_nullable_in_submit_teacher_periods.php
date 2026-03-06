<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * photo_url menjadi nullable karena guru cukup klik tombol
     * "Submit Kehadiran Kelas" tanpa perlu mengambil foto.
     */
    public function up(): void
    {
        Schema::table('submit_teacher_periods', function (Blueprint $table) {
            $table->string('photo_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('submit_teacher_periods', function (Blueprint $table) {
            $table->string('photo_url')->nullable(false)->change();
        });
    }
};
