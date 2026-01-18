<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

/**
 * Seeder untuk mengisi data mata pelajaran ke database.
 * Data mata pelajaran dibagi berdasarkan jurusan/major.
 */
class SubjectSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk mengisi tabel subjects.
     * 
     * Format:
     * - name: Nama mata pelajaran
     * - major: Jurusan (RPL, TKJ, Kuliner 1, dll)
     * - kelas: Kelas yang diajarkan (10, 11, 12) - bisa null untuk semua kelas
     * - description: Deskripsi mata pelajaran
     * 
     * @return void
     */
    public function run(): void
    {
        // Daftar mata pelajaran (daftar lengkap), nanti akan difilter
        $subjects = [
            // RPL 10
            ['name' => 'Matematika', 'major' => 'RPL', 'kelas' => 10, 'description' => 'Matematika kelas 10 RPL'],
            ['name' => 'Bahasa Indonesia', 'major' => 'RPL', 'kelas' => 10, 'description' => 'Bahasa Indonesia kelas 10 RPL'],
            ['name' => 'Bahasa Inggris', 'major' => 'RPL', 'kelas' => 10, 'description' => 'Bahasa Inggris kelas 10 RPL'],
            ['name' => 'Pemrograman Dasar', 'major' => 'RPL', 'kelas' => 10, 'description' => 'Pemrograman dasar kelas 10 RPL'],
            ['name' => 'Sistem Komputer', 'major' => 'RPL', 'kelas' => 10, 'description' => 'Sistem komputer kelas 10 RPL'],
            ['name' => 'Simulasi dan Komunikasi Digital', 'major' => 'RPL', 'kelas' => 10, 'description' => 'Simulasi digital kelas 10 RPL'],
            ['name' => 'Pendidikan Agama', 'major' => 'RPL', 'kelas' => 10, 'description' => 'Pendidikan agama kelas 10 RPL'],
            ['name' => 'PJOK', 'major' => 'RPL', 'kelas' => 10, 'description' => 'PJOK kelas 10 RPL'],

            // RPL 11
            ['name' => 'Matematika', 'major' => 'RPL', 'kelas' => 11, 'description' => 'Matematika kelas 11 RPL'],
            ['name' => 'Bahasa Indonesia', 'major' => 'RPL', 'kelas' => 11, 'description' => 'Bahasa Indonesia kelas 11 RPL'],
            ['name' => 'Bahasa Inggris', 'major' => 'RPL', 'kelas' => 11, 'description' => 'Bahasa Inggris kelas 11 RPL'],
            ['name' => 'Basis Data', 'major' => 'RPL', 'kelas' => 11, 'description' => 'Basis data kelas 11 RPL'],
            ['name' => 'Pemrograman Berorientasi Objek', 'major' => 'RPL', 'kelas' => 11, 'description' => 'PBO kelas 11 RPL'],
            ['name' => 'Pendidikan Agama', 'major' => 'RPL', 'kelas' => 11, 'description' => 'Pendidikan agama kelas 11 RPL'],
            ['name' => 'PJOK', 'major' => 'RPL', 'kelas' => 11, 'description' => 'PJOK kelas 11 RPL'],

            // RPL 12
            ['name' => 'Matematika', 'major' => 'RPL', 'kelas' => 12, 'description' => 'Matematika kelas 12 RPL'],
            ['name' => 'Bahasa Indonesia', 'major' => 'RPL', 'kelas' => 12, 'description' => 'Bahasa Indonesia kelas 12 RPL'],
            ['name' => 'Bahasa Inggris', 'major' => 'RPL', 'kelas' => 12, 'description' => 'Bahasa Inggris kelas 12 RPL'],
            ['name' => 'Pemrograman Web dan Perangkat Bergerak', 'major' => 'RPL', 'kelas' => 12, 'description' => 'Web dan mobile kelas 12 RPL'],
            ['name' => 'Produk Kreatif dan Kewirausahaan', 'major' => 'RPL', 'kelas' => 12, 'description' => 'PKK kelas 12 RPL'],
            ['name' => 'Pendidikan Agama', 'major' => 'RPL', 'kelas' => 12, 'description' => 'Pendidikan agama kelas 12 RPL'],
            ['name' => 'PJOK', 'major' => 'RPL', 'kelas' => 12, 'description' => 'PJOK kelas 12 RPL'],
        ];

        // Ambil pasangan class-major yang tersedia di ClassModel
        $existingPairs = \App\Models\ClassModel::all()->map(function($c){
            return $c->class . '||' . trim($c->major);
        })->toArray();

        // Filter subjects agar hanya disimpan jika class+major ada di ClassModel
        $toSeed = array_values(array_filter($subjects, function($s) use ($existingPairs) {
            return in_array($s['kelas'] . '||' . trim($s['major']), $existingPairs, true);
        }));

        // Insert data ke database menggunakan transaction untuk keamanan
        DB::transaction(function () use ($toSeed) {
            foreach ($toSeed as $subject) {
                Subject::firstOrCreate(
                    [
                        'name' => $subject['name'],
                        'major' => $subject['major'],
                        'kelas' => $subject['kelas'],
                    ],
                    [
                        'description' => $subject['description'],
                        'is_active' => true,
                    ]
                );
            }
        });
        
        $this->command->info('✅ Data mata pelajaran berhasil ditambahkan!');
        $this->command->info('📚 Total: ' . count($subjects) . ' mata pelajaran');
        $this->command->info('💡 Tip: Edit array di seeder ini untuk menambah/mengubah mata pelajaran sesuai kebutuhan sekolah');
    }
}
