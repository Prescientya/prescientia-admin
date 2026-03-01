<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('link', 500)->nullable();
            $table->date('release_date');
            $table->date('end_date');
            $table->enum('target_audience', ['semua', 'guru', 'siswa', 'kelas'])->default('semua');
            $table->timestamps();

            $table->index('release_date');
            $table->index('end_date');
            $table->index('target_audience');
        });

        Schema::create('event_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
            $table->integer('grade')->nullable();
            $table->string('major', 100)->nullable();
            $table->timestamps();

            $table->index('event_id');
            $table->index('class_id');
            $table->index('grade');
            $table->index('major');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_targets');
        Schema::dropIfExists('events');
    }
};
