<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('mbg_class_daily')) {
            return;
        }

        if (Schema::hasColumn('mbg_class_daily', 'given_plates')) {
            try {
                DB::statement('ALTER TABLE mbg_class_daily ALTER COLUMN given_plates DROP NOT NULL');
            } catch (\Throwable $e) {
                // ignore if DB doesn't support or already nullable
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('mbg_class_daily')) {
            return;
        }

        if (Schema::hasColumn('mbg_class_daily', 'given_plates')) {
            try {
                DB::statement('UPDATE mbg_class_daily SET given_plates = 0 WHERE given_plates IS NULL');
                DB::statement('ALTER TABLE mbg_class_daily ALTER COLUMN given_plates SET NOT NULL');
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
};
