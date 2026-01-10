<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel pivot antara mata pelajaran dan kelas yang diajar.
     * Tabel ini mengelola hubungan many-to-many: 1 mata pelajaran bisa diajar di banyak kelas,
     * dan 1 teached_class (guru mengajar di kelas) bisa mengajar banyak mata pelajaran.
     */
    public function up(): void
    {
        Schema::create('subject_teached_class', function (Blueprint $table) {
            $table->id();
            // ID mata pelajaran yang diajar
            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->onDelete('cascade')
                ->comment('ID mata pelajaran yang diajar');
            
            // ID teached_class (kombinasi guru, kelas, dan semester)
            $table->foreignId('teached_class_id')
                ->constrained('teached_classes')
                ->onDelete('cascade')
                ->comment('ID kelas yang diajar (teached_class)');
            
            $table->timestamps();
            
            // Pastikan 1 mata pelajaran tidak bisa terdaftar 2 kali dalam 1 teached_class
            $table->unique(['subject_id', 'teached_class_id']);
            
            // Indexes untuk query yang lebih cepat
            $table->index('subject_id');
            $table->index('teached_class_id');
        });
    }

    /**
     * Batalkan migrasi dan hapus tabel pivot mata pelajaran-teached class.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_teached_class');
    }
};
