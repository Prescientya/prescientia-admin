<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel pivot antara guru dan mata pelajaran.
     * Tabel ini mengelola hubungan many-to-many: 1 guru bisa mengajar banyak mata pelajaran,
     * dan 1 mata pelajaran bisa diajar oleh banyak guru.
     */
    public function up(): void
    {
        Schema::create('teacher_subject', function (Blueprint $table) {
            $table->id();
            // ID guru yang mengajar mata pelajaran ini
            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->onDelete('cascade')
                ->comment('ID guru yang mengajar mata pelajaran');
            
            // ID mata pelajaran yang diajar oleh guru
            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->onDelete('cascade')
                ->comment('ID mata pelajaran yang diajar');
            
            $table->timestamps();
            
            // Pastikan 1 guru tidak bisa mengajar mata pelajaran yang sama 2 kali
            $table->unique(['teacher_id', 'subject_id']);
            
            // Indexes untuk query yang lebih cepat
            $table->index('teacher_id');
            $table->index('subject_id');
        });
    }

    /**
     * Batalkan migrasi dan hapus tabel pivot guru-mata pelajaran.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_subject');
    }
};
