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
        Schema::create('student_attendance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->unique()->constrained('student_attendances')->onDelete('cascade');
            $table->enum('status', ['sakit', 'izin', 'alpa', 'terlambat'])->comment('Status ketidakhadiran siswa');
            $table->string('approval_status', 50)->default('pending')->comment('Status persetujuan: pending, approved, rejected');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('ID guru yang menyetujui');
            $table->timestamp('approved_at')->nullable();
            $table->text('description')->nullable();
            $table->string('evidence_url', 255)->nullable();
            $table->timestamps();

            $table->index('approval_status', 'idx_student_attendance_details_approval_status');
            $table->foreign('approved_by', 'student_attendance_details_approved_by_foreign')
                ->references('id')->on('teachers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendance_details');
    }
};
