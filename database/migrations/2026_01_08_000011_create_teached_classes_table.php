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
        Schema::create('teached_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->integer('semester');
            $table->json('departments')->comment('Array of departments/subjects taught in this class');
            $table->timestamps();
            
            // Indexes
            $table->unique(['teacher_id', 'class_id', 'semester']);
            $table->index('semester');
            $table->index(['teacher_id', 'semester']);
            $table->index(['class_id', 'semester']);
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
