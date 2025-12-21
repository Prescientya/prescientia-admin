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
        Schema::create('history_login', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('device_id', 255)->nullable();
            $table->string('wifi_mac', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('login_at')->nullable();
            $table->dateTime('logout_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('location', 255)->nullable();
            $table->enum('status', ['success', 'failed']);
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('login_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_login');
    }
};
