<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom subject_id ke teached_classes untuk mendukung
     * assignment 1 guru = 1 subject per kelas.
     * 
     * Struktur baru: 1 row = 1 guru mengajar 1 mata pelajaran di 1 kelas
     */
    public function up(): void
    {
        Schema::table('teached_classes', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->after('class_id')->constrained('subjects')->onDelete('cascade');
            $table->index(['teacher_id', 'class_id', 'subject_id', 'semester'], 'idx_teacher_class_subject_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teached_classes', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropIndex('idx_teacher_class_subject_semester');
            $table->dropColumn('subject_id');
        });
    }
};
