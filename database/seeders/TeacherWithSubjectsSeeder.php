<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use App\Models\ClassPeriod;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherWithSubjectsSeeder extends Seeder
{
    /**
     * Seeder untuk membuat 1 guru dengan 2 mata pelajaran (Matematika & Informatika)
     * dan jadwal mengajar ke kelas-kelas tertentu.
     * 
     * Run dengan: php artisan db:seed --class=TeacherWithSubjectsSeeder
     */
    public function run(): void
    {
        $this->command->info('Membuat akun guru dengan mata pelajaran...');

        // 1. Buat User untuk guru
        $user = User::create([
            'email' => 'guru.matematika@prescientia.sch.id',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->command->info("✓ User dibuat: {$user->email}");

        // 2. Buat data Teacher
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'nip' => '198501012010011001',
            'name' => 'Budi Santoso, S.Pd.',
            'gender' => 'L',
            'date_of_birth' => '1985-01-01',
            'phone_number' => '08123456789',
            'address' => 'Jl. Pendidikan No. 123, Jakarta',
            'department' => ['MIPA'],
        ]);

        $this->command->info("✓ Guru dibuat: {$teacher->name} (NIP: {$teacher->nip})");

        // 3. Buat atau ambil mata pelajaran Matematika dan Informatika
        $matematika = Subject::firstOrCreate(
            ['name' => 'Matematika'],
            [
                'major' => 'Semua',
                'kelas' => null,
                'description' => 'Mata pelajaran Matematika',
                'is_active' => true,
            ]
        );

        $informatika = Subject::firstOrCreate(
            ['name' => 'Informatika'],
            [
                'major' => 'Semua',
                'kelas' => null,
                'description' => 'Mata pelajaran Informatika',
                'is_active' => true,
            ]
        );

        $this->command->info("✓ Mata pelajaran: Matematika & Informatika");

        // 4. Hubungkan guru dengan mata pelajaran
        $teacher->subjects()->syncWithoutDetaching([$matematika->id, $informatika->id]);
        $this->command->info("✓ Guru dihubungkan dengan mata pelajaran Matematika & Informatika");

        // 5. Ambil kelas-kelas yang tersedia (minimal 2 kelas)
        $classes = ClassModel::limit(2)->get();
        
        if ($classes->count() < 2) {
            $this->command->warn('Tidak cukup kelas tersedia. Minimal 2 kelas diperlukan.');
            $this->command->info('Buat kelas terlebih dahulu dengan: php artisan db:seed --class=ClassSeeder');
            return;
        }

        $class1 = $classes[0]; // Kelas pertama
        $class2 = $classes[1]; // Kelas kedua

        // 6. Ambil jam pelajaran (ClassPeriod)
        $periods = ClassPeriod::where('activity_type', 'lesson')->get();

        if ($periods->isEmpty()) {
            $this->command->warn('Tidak ada jam pelajaran (class_periods) tersedia.');
            $this->command->info('Buat class periods terlebih dahulu dengan: php artisan db:seed --class=ClassPeriodSeeder');
            return;
        }

        // 7. Buat jadwal mengajar
        $schedules = [];
        $semester = 1; // Semester 1

        // Jadwal Matematika - Kelas 1 - Senin jam 1-2
        $seninPeriods = ClassPeriod::where('day', 'senin')
            ->where('activity_type', 'lesson')
            ->orderBy('sequence')
            ->skip(0) // Mulai dari jam pertama
            ->take(2) // Ambil 2 jam
            ->get();

        foreach ($seninPeriods as $period) {
            $schedules[] = [
                'teacher_id' => $teacher->id,
                'class_id' => $class1->id,
                'subject_id' => $matematika->id,
                'period_id' => $period->id,
                'day' => 'senin',
                'semester' => $semester,
            ];
        }

        // Jadwal Matematika - Kelas 2 - Selasa jam 1-2
        $selasaPeriods = ClassPeriod::where('day', 'selasa')
            ->where('activity_type', 'lesson')
            ->orderBy('sequence')
            ->skip(0)
            ->take(2)
            ->get();

        foreach ($selasaPeriods as $period) {
            $schedules[] = [
                'teacher_id' => $teacher->id,
                'class_id' => $class2->id,
                'subject_id' => $matematika->id,
                'period_id' => $period->id,
                'day' => 'selasa',
                'semester' => $semester,
            ];
        }

        // Jadwal Informatika - Kelas 1 - Rabu jam 3-4
        $rabuPeriods = ClassPeriod::where('day', 'rabu')
            ->where('activity_type', 'lesson')
            ->orderBy('sequence')
            ->skip(2) // Mulai dari jam ketiga
            ->take(2)
            ->get();

        foreach ($rabuPeriods as $period) {
            $schedules[] = [
                'teacher_id' => $teacher->id,
                'class_id' => $class1->id,
                'subject_id' => $informatika->id,
                'period_id' => $period->id,
                'day' => 'rabu',
                'semester' => $semester,
            ];
        }

        // Jadwal Informatika - Kelas 2 - Kamis jam 1-2
        $kamisPeriods = ClassPeriod::where('day', 'kamis')
            ->where('activity_type', 'lesson')
            ->orderBy('sequence')
            ->skip(0)
            ->take(2)
            ->get();

        foreach ($kamisPeriods as $period) {
            $schedules[] = [
                'teacher_id' => $teacher->id,
                'class_id' => $class2->id,
                'subject_id' => $informatika->id,
                'period_id' => $period->id,
                'day' => 'kamis',
                'semester' => $semester,
            ];
        }

        // Insert jadwal
        $created = 0;
        $skipped = 0;

        foreach ($schedules as $schedule) {
            try {
                TeacherClassSchedule::create($schedule);
                $created++;
            } catch (\Exception $e) {
                $skipped++;
                // Skip jika sudah ada (duplicate)
            }
        }

        $this->command->info("✓ Jadwal dibuat: {$created} jadwal");
        if ($skipped > 0) {
            $this->command->warn("⚠ Jadwal dilewati (sudah ada): {$skipped} jadwal");
        }

        // Tampilkan ringkasan
        $this->command->newLine();
        $this->command->info('=== RINGKASAN ===');
        $this->command->info("Guru: {$teacher->name}");
        $this->command->info("Email: {$user->email}");
        $this->command->info("Password: password123");
        $this->command->info("Mata Pelajaran: Matematika, Informatika");
        $this->command->info("Jadwal Mengajar:");
        $this->command->info("  - Matematika di {$class1->class} {$class1->major}: Senin jam 1-2");
        $this->command->info("  - Matematika di {$class2->class} {$class2->major}: Selasa jam 1-2");
        $this->command->info("  - Informatika di {$class1->class} {$class1->major}: Rabu jam 3-4");
        $this->command->info("  - Informatika di {$class2->class} {$class2->major}: Kamis jam 1-2");
        $this->command->newLine();
        $this->command->info('✓ Seeder selesai dijalankan!');
    }
}
