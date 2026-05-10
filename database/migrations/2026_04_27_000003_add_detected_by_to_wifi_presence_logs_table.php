<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('wifi_presence_logs', 'detected_by')) {
            Schema::table('wifi_presence_logs', function (Blueprint $table) {
                $table->string('detected_by', 10)->default('BSSID');
            });
        }

        DB::table('wifi_presence_logs')
            ->whereNull('detected_by')
            ->update(['detected_by' => 'BSSID']);

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("COMMENT ON COLUMN wifi_presence_logs.detected_by IS 'Detection method: BSSID (primary) or SSID (fallback)'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('wifi_presence_logs', 'detected_by')) {
            Schema::table('wifi_presence_logs', function (Blueprint $table) {
                $table->dropColumn('detected_by');
            });
        }
    }
};
