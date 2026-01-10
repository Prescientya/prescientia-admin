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
            // Nama mata pelajaran (contoh: Matematika, Fisika, dll)
            $table->string('name', 100);
            // Kode mata pelajaran (contoh: MAT, FIS, BIO)
            $table->string('code', 10);
            // Jurusan/major yang memiliki mata pelajaran ini (IPA, IPS, RPL, TKJ, dll)
            // Bisa null jika mata pelajaran umum untuk semua jurusan
            $table->string('major', 50)->nullable()->comment('Jurusan yang memiliki mata pelajaran ini (IPA, IPS, RPL, TKJ, Bahasa, Umum)');
            // Deskripsi mata pelajaran (opsional)
            $table->text('description')->nullable();
            // Status aktif/tidak aktif
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint: kombinasi code + major harus unique
            // Karena mata pelajaran yang sama (code) bisa ada di banyak jurusan
            $table->unique(['code', 'major']);
            
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
