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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('nip', 20)->unique();
            $table->string('name', 100);
            $table->enum('gender', ['L', 'P']);
            $table->date('date_of_birth');
            $table->string('phone_number', 20)->nullable();
            $table->text('address')->nullable();
            $table->jsonb('department')->nullable()->comment('Array jurusan/bidang yang diajar guru, disimpan sebagai JSON array');
            $table->string('photo_profile', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
