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
        Schema::create('piring_mbg', function (Blueprint $table) {
            $table->id();
            $table->integer('stok')->default(0); // Total stok dari semua kelas yang ada siswa hadir
            $table->date('tanggal_distribusi')->nullable(); // Tanggal distribusi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piring_mbg');
    }
};
