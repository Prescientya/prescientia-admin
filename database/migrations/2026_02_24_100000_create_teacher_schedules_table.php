<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('class_period_id')->constrained('class_periods')->cascadeOnDelete();
            $table->timestamps();

            // Satu slot kelas + periode hanya bisa 1 mapel/guru
            $table->unique(['class_id', 'class_period_id'], 'uniq_class_period');
            // Satu guru tidak bisa di dua tempat bersamaan
            $table->unique(['teacher_id', 'class_period_id'], 'uniq_teacher_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_schedules');
    }
};
