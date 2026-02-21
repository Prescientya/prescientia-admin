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
        Schema::create('student_class_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('role', 50)->default('pelajar')->comment('Role siswa di kelas: KM, WKM, Sekretaris, atau pelajar');
            $table->timestamps();
            
            // Unique constraint: satu siswa hanya punya satu role per kelas
            $table->unique(['class_id', 'student_id']);
            // Indexes
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_class_roles');
    }
};
