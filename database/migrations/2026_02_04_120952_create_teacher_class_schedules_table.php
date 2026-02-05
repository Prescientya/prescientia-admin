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
        Schema::create('teacher_class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('class_periods')->cascadeOnDelete();
            $table->string('day'); // senin, selasa, rabu, kamis, jumat
            $table->integer('semester');
            $table->timestamps();

            // Unique constraint to prevent duplicate schedules
            $table->unique(['teacher_id', 'class_id', 'subject_id', 'period_id', 'day', 'semester'], 'unique_teacher_class_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_class_schedules');
    }
};
