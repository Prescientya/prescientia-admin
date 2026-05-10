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
        Schema::table('users', function (Blueprint $table) {
            // Add first_login column if it doesn't exist
            if (!Schema::hasColumn('users', 'first_login')) {
                $table->boolean('first_login')->default(false)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the first_login column if it exists
            if (Schema::hasColumn('users', 'first_login')) {
                $table->dropColumn('first_login');
            }
        });
    }
};
