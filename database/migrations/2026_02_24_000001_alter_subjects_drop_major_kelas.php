<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Drop old unique constraint before dropping columns
            $table->dropUnique('subjects_name_major_kelas_unique');
            $table->dropIndex('subjects_major_index');
            $table->dropIndex('subjects_major_is_active_index');

            $table->dropColumn(['major', 'kelas']);

            // Re-add simple unique on name alone
            $table->unique('name', 'subjects_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_name_unique');

            $table->string('major', 50)->nullable();
            $table->integer('kelas')->nullable();

            $table->index('major');
            $table->index(['major', 'is_active']);
            $table->unique(['name', 'major', 'kelas'], 'subjects_name_major_kelas_unique');
        });
    }
};
