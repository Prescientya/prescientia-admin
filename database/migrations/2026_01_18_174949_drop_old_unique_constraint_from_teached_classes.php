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
        Schema::table('teached_classes', function (Blueprint $table) {
            // Drop old unique constraint (teacher_id, class_id, semester)
            $table->dropUnique('teached_classes_teacher_id_class_id_semester_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teached_classes', function (Blueprint $table) {
            // Restore old unique constraint
            $table->unique(['teacher_id', 'class_id', 'semester'], 'teached_classes_teacher_id_class_id_semester_unique');
        });
    }
};
