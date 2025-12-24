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
        Schema::create('mbg_class_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piring_mbg_id')->constrained('piring_mbg')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->integer('total_students')->default(0); // Total siswa di kelas
            $table->integer('attended_students')->default(0); // Siswa yang hadir
            $table->integer('returned_plates')->default(0); // Piring yang dikembalikan
            $table->string('class_code')->nullable(); // Kode kelas-jurusan (contoh: "Kuliner-1")
            $table->string('student_representative')->nullable(); // Nama siswa perwakilan kelas
            $table->timestamps();

            // Unique constraint: satu kelas hanya bisa punya satu record per piring_mbg_id
            $table->unique(['piring_mbg_id', 'class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mbg_class_daily');
    }
};
