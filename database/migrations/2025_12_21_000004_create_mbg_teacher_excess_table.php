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
        Schema::create('mbg_teacher_excess', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piring_mbg_id')->constrained('piring_mbg')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->integer('quantity')->default(0); // Jumlah piring yang diberikan
            $table->string('location')->nullable(); // Ruangan/tempat penyerahan
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mbg_teacher_excess');
    }
};
