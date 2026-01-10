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
     * @return void
     */
    public function run(): void
    {
        // Hapus data lama jika ada (opsional, hati-hati di production)
        // Subject::truncate();
        
        // Daftar mata pelajaran berdasarkan jurusan
        $subjects = [
            // Mata Pelajaran untuk jurusan IPA
            ['name' => 'Matematika', 'code' => 'MAT', 'major' => 'IPA', 'description' => 'Mata pelajaran matematika untuk jurusan IPA'],
            ['name' => 'Fisika', 'code' => 'FIS', 'major' => 'IPA', 'description' => 'Mata pelajaran fisika'],
            ['name' => 'Kimia', 'code' => 'KIM', 'major' => 'IPA', 'description' => 'Mata pelajaran kimia'],
            ['name' => 'Biologi', 'code' => 'BIO', 'major' => 'IPA', 'description' => 'Mata pelajaran biologi'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIND', 'major' => 'IPA', 'description' => 'Mata pelajaran bahasa Indonesia'],
            ['name' => 'Bahasa Inggris', 'code' => 'BING', 'major' => 'IPA', 'description' => 'Mata pelajaran bahasa Inggris'],
            ['name' => 'Pendidikan Agama', 'code' => 'PAI', 'major' => 'IPA', 'description' => 'Pendidikan agama dan budi pekerti'],
            ['name' => 'PJOK', 'code' => 'PJOK', 'major' => 'IPA', 'description' => 'Pendidikan jasmani, olahraga, dan kesehatan'],
            ['name' => 'Seni Budaya', 'code' => 'SBD', 'major' => 'IPA', 'description' => 'Mata pelajaran seni budaya'],
            ['name' => 'Sejarah Indonesia', 'code' => 'SEJ', 'major' => 'IPA', 'description' => 'Mata pelajaran sejarah Indonesia'],
            
            // Mata Pelajaran untuk jurusan IPS
            ['name' => 'Matematika', 'code' => 'MAT', 'major' => 'IPS', 'description' => 'Mata pelajaran matematika untuk jurusan IPS'],
            ['name' => 'Ekonomi', 'code' => 'EKO', 'major' => 'IPS', 'description' => 'Mata pelajaran ekonomi'],
            ['name' => 'Sosiologi', 'code' => 'SOS', 'major' => 'IPS', 'description' => 'Mata pelajaran sosiologi'],
            ['name' => 'Geografi', 'code' => 'GEO', 'major' => 'IPS', 'description' => 'Mata pelajaran geografi'],
            ['name' => 'Sejarah', 'code' => 'SEJ', 'major' => 'IPS', 'description' => 'Mata pelajaran sejarah'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIND', 'major' => 'IPS', 'description' => 'Mata pelajaran bahasa Indonesia'],
            ['name' => 'Bahasa Inggris', 'code' => 'BING', 'major' => 'IPS', 'description' => 'Mata pelajaran bahasa Inggris'],
            ['name' => 'Pendidikan Agama', 'code' => 'PAI', 'major' => 'IPS', 'description' => 'Pendidikan agama dan budi pekerti'],
            ['name' => 'PJOK', 'code' => 'PJOK', 'major' => 'IPS', 'description' => 'Pendidikan jasmani, olahraga, dan kesehatan'],
            ['name' => 'Seni Budaya', 'code' => 'SBD', 'major' => 'IPS', 'description' => 'Mata pelajaran seni budaya'],
            
            // Mata Pelajaran untuk jurusan Bahasa
            ['name' => 'Matematika', 'code' => 'MAT', 'major' => 'Bahasa', 'description' => 'Mata pelajaran matematika untuk jurusan Bahasa'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIND', 'major' => 'Bahasa', 'description' => 'Mata pelajaran bahasa Indonesia'],
            ['name' => 'Bahasa Inggris', 'code' => 'BING', 'major' => 'Bahasa', 'description' => 'Mata pelajaran bahasa Inggris'],
            ['name' => 'Bahasa Asing (Jepang/Mandarin/Arab)', 'code' => 'BASING', 'major' => 'Bahasa', 'description' => 'Mata pelajaran bahasa asing pilihan'],
            ['name' => 'Sastra Indonesia', 'code' => 'SASTIND', 'major' => 'Bahasa', 'description' => 'Mata pelajaran sastra Indonesia'],
            ['name' => 'Antropologi', 'code' => 'ANTRO', 'major' => 'Bahasa', 'description' => 'Mata pelajaran antropologi'],
            ['name' => 'Bahasa Jawa', 'code' => 'BJAWA', 'major' => 'Bahasa', 'description' => 'Mata pelajaran bahasa Jawa'],
            ['name' => 'Pendidikan Agama', 'code' => 'PAI', 'major' => 'Bahasa', 'description' => 'Pendidikan agama dan budi pekerti'],
            ['name' => 'PJOK', 'code' => 'PJOK', 'major' => 'Bahasa', 'description' => 'Pendidikan jasmani, olahraga, dan kesehatan'],
            ['name' => 'Seni Budaya', 'code' => 'SBD', 'major' => 'Bahasa', 'description' => 'Mata pelajaran seni budaya'],
            
            // Mata Pelajaran untuk jurusan TKJ (Teknik Komputer dan Jaringan)
            ['name' => 'Matematika', 'code' => 'MAT', 'major' => 'TKJ', 'description' => 'Mata pelajaran matematika untuk TKJ'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIND', 'major' => 'TKJ', 'description' => 'Mata pelajaran bahasa Indonesia'],
            ['name' => 'Bahasa Inggris', 'code' => 'BING', 'major' => 'TKJ', 'description' => 'Mata pelajaran bahasa Inggris'],
            ['name' => 'Pemrograman Dasar', 'code' => 'PROGDAS', 'major' => 'TKJ', 'description' => 'Mata pelajaran pemrograman dasar'],
            ['name' => 'Sistem Komputer', 'code' => 'SISKOMP', 'major' => 'TKJ', 'description' => 'Mata pelajaran sistem komputer'],
            ['name' => 'Komputer dan Jaringan Dasar', 'code' => 'KOMJAR', 'major' => 'TKJ', 'description' => 'Mata pelajaran komputer dan jaringan dasar'],
            ['name' => 'Simulasi dan Komunikasi Digital', 'code' => 'SIMDIG', 'major' => 'TKJ', 'description' => 'Mata pelajaran simulasi dan komunikasi digital'],
            ['name' => 'Administrasi Infrastruktur Jaringan', 'code' => 'AIJ', 'major' => 'TKJ', 'description' => 'Mata pelajaran administrasi infrastruktur jaringan'],
            ['name' => 'Teknologi Layanan Jaringan', 'code' => 'TLJ', 'major' => 'TKJ', 'description' => 'Mata pelajaran teknologi layanan jaringan'],
            ['name' => 'Pendidikan Agama', 'code' => 'PAI', 'major' => 'TKJ', 'description' => 'Pendidikan agama dan budi pekerti'],
            ['name' => 'PJOK', 'code' => 'PJOK', 'major' => 'TKJ', 'description' => 'Pendidikan jasmani, olahraga, dan kesehatan'],
            
            // Mata Pelajaran untuk jurusan RPL (Rekayasa Perangkat Lunak)
            ['name' => 'Matematika', 'code' => 'MAT', 'major' => 'RPL', 'description' => 'Mata pelajaran matematika untuk RPL'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIND', 'major' => 'RPL', 'description' => 'Mata pelajaran bahasa Indonesia'],
            ['name' => 'Bahasa Inggris', 'code' => 'BING', 'major' => 'RPL', 'description' => 'Mata pelajaran bahasa Inggris'],
            ['name' => 'Pemrograman Dasar', 'code' => 'PROGDAS', 'major' => 'RPL', 'description' => 'Mata pelajaran pemrograman dasar'],
            ['name' => 'Basis Data', 'code' => 'BD', 'major' => 'RPL', 'description' => 'Mata pelajaran basis data'],
            ['name' => 'Pemrograman Berorientasi Objek', 'code' => 'PBO', 'major' => 'RPL', 'description' => 'Mata pelajaran pemrograman berorientasi objek'],
            ['name' => 'Pemrograman Web dan Perangkat Bergerak', 'code' => 'PWPB', 'major' => 'RPL', 'description' => 'Mata pelajaran pemrograman web dan mobile'],
            ['name' => 'Produk Kreatif dan Kewirausahaan', 'code' => 'PKK', 'major' => 'RPL', 'description' => 'Mata pelajaran produk kreatif dan kewirausahaan'],
            ['name' => 'Pendidikan Agama', 'code' => 'PAI', 'major' => 'RPL', 'description' => 'Pendidikan agama dan budi pekerti'],
            ['name' => 'PJOK', 'code' => 'PJOK', 'major' => 'RPL', 'description' => 'Pendidikan jasmani, olahraga, dan kesehatan'],
            
            // Mata Pelajaran Umum (untuk kelas tanpa jurusan)
            ['name' => 'Matematika', 'code' => 'MAT', 'major' => 'Umum', 'description' => 'Mata pelajaran matematika umum'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIND', 'major' => 'Umum', 'description' => 'Mata pelajaran bahasa Indonesia'],
            ['name' => 'Bahasa Inggris', 'code' => 'BING', 'major' => 'Umum', 'description' => 'Mata pelajaran bahasa Inggris'],
            ['name' => 'IPA', 'code' => 'IPA', 'major' => 'Umum', 'description' => 'Ilmu pengetahuan alam'],
            ['name' => 'IPS', 'code' => 'IPS', 'major' => 'Umum', 'description' => 'Ilmu pengetahuan sosial'],
            ['name' => 'Pendidikan Agama', 'code' => 'PAI', 'major' => 'Umum', 'description' => 'Pendidikan agama dan budi pekerti'],
            ['name' => 'PJOK', 'code' => 'PJOK', 'major' => 'Umum', 'description' => 'Pendidikan jasmani, olahraga, dan kesehatan'],
            ['name' => 'Seni Budaya', 'code' => 'SBD', 'major' => 'Umum', 'description' => 'Mata pelajaran seni budaya'],
            ['name' => 'Prakarya', 'code' => 'PRAKRY', 'major' => 'Umum', 'description' => 'Mata pelajaran prakarya'],
            ['name' => 'Pendidikan Pancasila', 'code' => 'PPKN', 'major' => 'Umum', 'description' => 'Pendidikan pancasila dan kewarganegaraan'],
        ];
        
        // Insert data ke database menggunakan transaction untuk keamanan
        DB::transaction(function () use ($subjects) {
            foreach ($subjects as $subject) {
                Subject::updateOrCreate(
                    [
                        'code' => $subject['code'],
                        'major' => $subject['major'],
                    ],
                    [
                        'name' => $subject['name'],
                        'description' => $subject['description'],
                        'is_active' => true,
                    ]
                );
            }
        });
        
        $this->command->info('✅ Data mata pelajaran berhasil ditambahkan!');
        $this->command->info('📚 Total: ' . count($subjects) . ' mata pelajaran');
    }
}
