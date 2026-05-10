<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('absence_letters')) {
            Schema::create('absence_letters', function (Blueprint $table) {
                $table->id();
                $table->enum('user_type', ['student', 'teacher']);
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
                $table->foreignId('calendar_id')->nullable()->constrained('school_calendar')->nullOnDelete();
                $table->date('date');
                $table->enum('reason', ['sakit', 'izin']);
                $table->text('description');
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->unsignedBigInteger('approved_by_wali')->nullable();
                $table->timestamp('approved_wali_at')->nullable();
                $table->unsignedBigInteger('approved_by_admin')->nullable();
                $table->timestamp('approved_admin_at')->nullable();
                $table->timestamp('approved_by_wali_at')->nullable();
                $table->timestamp('approved_by_admin_at')->nullable();
                $table->unsignedBigInteger('rejected_by')->nullable();
                $table->string('rejected_by_type', 10)->nullable();
                $table->string('rejected_by_role', 20)->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->text('rejection_note')->nullable();
                $table->timestamps();

                $table->index(['user_type', 'status'], 'idx_absence_letters_type_status');
                $table->index(['student_id', 'date'], 'idx_absence_letters_student_date');
                $table->index(['teacher_id', 'date'], 'idx_absence_letters_teacher_date');
                $table->index('calendar_id', 'idx_absence_letters_calendar_id');
            });
        }

        Schema::table('absence_letters', function (Blueprint $table) {
            if (!Schema::hasColumn('absence_letters', 'approved_by_wali_at')) {
                $table->timestamp('approved_by_wali_at')->nullable();
            }

            if (!Schema::hasColumn('absence_letters', 'approved_by_admin_at')) {
                $table->timestamp('approved_by_admin_at')->nullable();
            }

            if (!Schema::hasColumn('absence_letters', 'rejected_by_role')) {
                $table->string('rejected_by_role', 20)->nullable();
            }

            if (!Schema::hasColumn('absence_letters', 'approved_wali_at')) {
                $table->timestamp('approved_wali_at')->nullable();
            }

            if (!Schema::hasColumn('absence_letters', 'approved_admin_at')) {
                $table->timestamp('approved_admin_at')->nullable();
            }

            if (!Schema::hasColumn('absence_letters', 'rejected_by_type')) {
                $table->string('rejected_by_type', 10)->nullable();
            }

            if (!Schema::hasColumn('absence_letters', 'rejection_note')) {
                $table->text('rejection_note')->nullable();
            }

            if (!Schema::hasColumn('absence_letters', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('absence_letters', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (Schema::hasColumn('absence_letters', 'approved_wali_at') && Schema::hasColumn('absence_letters', 'approved_by_wali_at')) {
            DB::table('absence_letters')
                ->whereNull('approved_by_wali_at')
                ->whereNotNull('approved_wali_at')
                ->update(['approved_by_wali_at' => DB::raw('approved_wali_at')]);
        }

        if (Schema::hasColumn('absence_letters', 'approved_admin_at') && Schema::hasColumn('absence_letters', 'approved_by_admin_at')) {
            DB::table('absence_letters')
                ->whereNull('approved_by_admin_at')
                ->whereNotNull('approved_admin_at')
                ->update(['approved_by_admin_at' => DB::raw('approved_admin_at')]);
        }

        if (Schema::hasColumn('absence_letters', 'rejected_by_type') && Schema::hasColumn('absence_letters', 'rejected_by_role')) {
            DB::table('absence_letters')
                ->whereNull('rejected_by_role')
                ->whereNotNull('rejected_by_type')
                ->update(['rejected_by_role' => DB::raw('rejected_by_type')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_letters');
    }
};
