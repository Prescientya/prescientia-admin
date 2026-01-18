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
        Schema::table('subjects', function (Blueprint $table) {
            // Drop code column - not needed
            if (Schema::hasColumn('subjects', 'code')) {
                $table->dropColumn('code');
            }
            
            // Add kelas column after major - to track which class(es) this subject is for
            if (!Schema::hasColumn('subjects', 'kelas')) {
                $table->integer('kelas')->nullable()->after('major')->comment('Kelas untuk mata pelajaran ini (10, 11, 12, dll). Null berarti berlaku untuk semua kelas.');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Re-add code column if we rollback
            if (!Schema::hasColumn('subjects', 'code')) {
                $table->string('code')->nullable()->after('name')->comment('Kode mata pelajaran');
            }
            
            // Remove kelas column if we rollback
            if (Schema::hasColumn('subjects', 'kelas')) {
                $table->dropColumn('kelas');
            }
        });
    }
};
