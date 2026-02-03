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
        Schema::create('class_periods', function (Blueprint $table) {
            $table->id();
            $table->enum('day', ['senin', 'selasa', 'rabu', 'kamis', 'jumat']);
            $table->integer('sequence')->comment('Urutan jam dalam hari (0=pertama, 1=kedua, dst)');
            $table->time('start_time')->comment('Waktu mulai jam pelajaran');
            $table->time('end_time')->comment('Waktu selesai jam pelajaran');
            $table->integer('duration_minutes')->comment('Durasi dalam menit');
            $table->enum('activity_type', ['lesson', 'break', 'ceremony', 'prayer', 'cleaning', 'other'])
                  ->default('lesson')
                  ->comment('Jenis aktivitas: lesson=pelajaran, break=istirahat, ceremony=upacara, prayer=ibadah, cleaning=kebersihan');
            $table->string('note')->nullable()->comment('Catatan khusus untuk jam ini');
            $table->timestamps();

            // Unique constraint: tidak boleh ada jam yang sama di hari yang sama
            $table->unique(['day', 'sequence']);
            $table->unique(['day', 'start_time']);

            // Index untuk query cepat per hari
            $table->index('day');
            $table->index('activity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_periods');
    }
};
