<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_calendar', function (Blueprint $table) {
            // Holiday / event description: "Tahun Baru Masehi", "Idul Fitri", etc.
            $table->string('notes')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('school_calendar', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
