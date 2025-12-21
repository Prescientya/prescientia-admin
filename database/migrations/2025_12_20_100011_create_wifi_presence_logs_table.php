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
        Schema::create('wifi_presence_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('wifi_id')->constrained('wifi_networks')->onDelete('cascade');
            $table->dateTime('detected_at');
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'detected_at']);
            $table->index('wifi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wifi_presence_logs');
    }
};
