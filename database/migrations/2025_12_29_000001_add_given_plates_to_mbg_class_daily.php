<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

        Schema::table('mbg_class_daily', function (Blueprint $table) {
            if (! Schema::hasColumn('mbg_class_daily', 'given_plates')) {
                $table->integer('given_plates')->default(0)->after('attended_students');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('mbg_class_daily')) {
            return;
        }

        Schema::table('mbg_class_daily', function (Blueprint $table) {
            if (Schema::hasColumn('mbg_class_daily', 'given_plates')) {
                $table->dropColumn('given_plates');
            }
        });
    }
};
