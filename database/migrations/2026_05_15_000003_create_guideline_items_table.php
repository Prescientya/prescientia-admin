<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guideline_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guideline_section_id')
                  ->constrained('guideline_sections')
                  ->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('content')->nullable();
            $table->string('image_path', 500)->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index(['guideline_section_id', 'order'], 'idx_guideline_items_section_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guideline_items');
    }
};
