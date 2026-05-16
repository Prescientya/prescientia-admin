<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guideline_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guideline_page_id')
                  ->constrained('guideline_pages')
                  ->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index(['guideline_page_id', 'order'], 'idx_guideline_sections_page_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guideline_sections');
    }
};
