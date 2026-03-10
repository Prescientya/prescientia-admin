<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot: mapel tersedia di kelas tertentu pada jam ke berapa.
     * Satu row = 1 jam pelajaran mapel tersebut di satu kelas.
     *
     * Contoh:
     *   IPAS, Kelas 10 RPL, jam_ke=1 → 07:15–07:55
     *   IPAS, Kelas 10 RPL, jam_ke=2 → 07:55–08:35
     *   Relasi DB, Kelas 11 RPL, jam_ke=3 → 08:35–09:15
     */
    public function up(): void
    {
        Schema::create('subject_classes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->foreignId('class_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            // Jam ke (1–12 sesuai sequence di class_periods, bukan sequence 0/break)
            $table->tinyInteger('jam_ke')->unsigned()
                ->comment('Urutan jam pelajaran (sesuai sequence lesson di class_periods)');

            // Cache waktu dari class_periods agar tidak perlu join tiap query
            $table->time('time_start')->comment('Waktu mulai jam ini');
            $table->time('time_end')->comment('Waktu selesai jam ini');

            $table->timestamps();

            // 1 mapel di 1 kelas hanya boleh ada sekali per jam
            $table->unique(['subject_id', 'class_id', 'jam_ke'], 'uq_subject_class_jam');

            $table->index('subject_id');
            $table->index('class_id');
            $table->index(['subject_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_classes');
    }
};
