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
        if (!Schema::hasColumn('users', 'first_login')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('first_login')->default(true);
            });
        }

        DB::table('users')
            ->where('first_login', true)
            ->orWhereNull('first_login')
            ->update(['first_login' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'first_login')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('first_login');
            });
        }
    }
};
