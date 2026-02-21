<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel penugasan guru mengajar mata pelajaran di kelas tertentu.
     * 1 row = 1 guru mengajar 1 mata pelajaran di 1 kelas pada 1 semester.
     * Diletakkan setelah subjects agar bisa FK ke subjects.
     */
    public function up(): void
    {
        Schema::create('teached_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade')
                  ->comment('Mata pelajaran yang diajar guru di kelas ini');
            $table->integer('semester');
            $table->json('departments')->comment('Array of departments/subjects taught in this class');
            $table->timestamps();

            // Indexes
            $table->index('semester');
            $table->index(['teacher_id', 'semester']);
            $table->index(['class_id', 'semester']);
            $table->index(['teacher_id', 'class_id', 'subject_id', 'semester'], 'idx_teacher_class_subject_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teached_classes');
    }
};
