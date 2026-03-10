<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subject_classes', function (Blueprint $table) {
            // Remove jam_ke / time_start / time_end — not needed for class assignment
            // Drop the composite unique first (actual constraint name in DB)
            $table->dropUnique('uq_subject_class_jam');
        });

        Schema::table('subject_classes', function (Blueprint $table) {
            $table->dropColumn(['jam_ke', 'time_start', 'time_end']);
        });

        Schema::table('subject_classes', function (Blueprint $table) {
            // Simple unique: one mapel per class
            $table->unique(['subject_id', 'class_id'], 'uq_subject_class');
        });
    }

    public function down(): void
    {
        Schema::table('subject_classes', function (Blueprint $table) {
            $table->dropUnique('uq_subject_class');
            $table->unsignedTinyInteger('jam_ke')->default(1);
            $table->time('time_start');
            $table->time('time_end');
            $table->unique(['subject_id', 'class_id', 'jam_ke'], 'uq_subject_class_jam');
        });
    }
};
