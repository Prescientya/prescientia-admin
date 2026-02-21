<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel mata pelajaran.
     * Tabel ini menyimpan daftar semua mata pelajaran yang ada di sekolah.
     */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            // Nama mata pelajaran (contoh: Matematika, dll)
            $table->string('name', 100);
            // Jurusan/major yang memiliki mata pelajaran ini (IPA, IPS, RPL, TKJ, dll)
            // Bisa null jika mata pelajaran umum untuk semua jurusan
            $table->string('major', 50)->nullable()->comment('Jurusan yang memiliki mata pelajaran ini (IPA, IPS, RPL, TKJ, Bahasa, Umum)');
            // Kelas untuk mata pelajaran ini (10, 11, 12). Null berarti berlaku untuk semua kelas
            $table->integer('kelas')->nullable()->comment('Kelas untuk mata pelajaran ini (10, 11, 12, dll). Null berarti berlaku untuk semua kelas.');
            // Deskripsi mata pelajaran (opsional)
            $table->text('description')->nullable();
            // Status aktif/tidak aktif
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint: kombinasi name + major + kelas harus unique
            $table->unique(['name', 'major', 'kelas'], 'subjects_name_major_kelas_unique');
            
            // Indexes untuk pencarian lebih cepat
            $table->index('name');
            $table->index('major');
            $table->index(['major', 'is_active']);
        });
    }

    /**
     * Batalkan migrasi dan hapus tabel mata pelajaran.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
