<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guideline_pages', function (Blueprint $table) {
            $table->id();
            $table->enum('user_type', ['siswa', 'guru'])->unique();
            $table->string('title', 200);
            $table->text('subtitle')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guideline_pages');
    }
};
