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
        Schema::create('submit_teacher_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('class_periods')->cascadeOnDelete();
            $table->string('day'); // senin, selasa, rabu, kamis, jumat
            $table->string('photo_url'); // URL from Flutter app
            $table->boolean('is_present')->default(true);
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            // Unique constraint: one submission per teacher/class/subject/period/day
            $table->unique(['teacher_id', 'class_id', 'subject_id', 'period_id', 'day'], 'unique_teacher_period_submission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submit_teacher_periods');
    }
};
