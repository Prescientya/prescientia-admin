<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\StudentClassRole;
use App\Enums\StudentRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignDefaultStudentRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Assign default role 'Pelajar' to all students who don't have any class role yet.
     * This is useful for migrating existing students who were created before the role system was implemented.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Get all students that don't have any role yet
            $studentsWithoutRole = Student::whereDoesntHave('classRoles')
                ->get();

            $count = 0;
            $skipped = 0;

            foreach ($studentsWithoutRole as $student) {
                try {
                    // Only create role if student has a class assigned
                    // Otherwise just skip (student will get role when assigned to a class)
                    if (!empty($student->class_id)) {
                        StudentClassRole::create([
                            'student_id' => $student->id,
                            'class_id' => $student->class_id,
                            'role' => StudentRole::PELAJAR,
                        ]);
                        $count++;
                    } else {
                        $skipped++;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to assign role to student', [
                        'student_id' => $student->id,
                        'nis' => $student->nis,
                        'name' => $student->name,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($count > 0) {
                $this->command->info("✅ Berhasil mengassign role 'Pelajar' ke {$count} siswa!");
            }
            if ($skipped > 0) {
                $this->command->info("⏭️  {$skipped} siswa tidak memiliki kelas (role akan diberikan saat assign ke kelas)");
            }
            if ($count === 0 && $skipped === 0) {
                $this->command->info("ℹ️  Semua siswa sudah memiliki role, atau tidak ada siswa tanpa role");
            }
        });
    }
}
