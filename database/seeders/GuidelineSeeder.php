<?php

namespace Database\Seeders;

use App\Models\GuidelinePage;
use Illuminate\Database\Seeder;

class GuidelineSeeder extends Seeder
{
    /**
     * Buat 2 entri halaman panduan awal (siswa & guru).
     * Konten seksi dan langkah diisi oleh admin melalui panel.
     */
    public function run(): void
    {
        GuidelinePage::updateOrCreate(
            ['user_type' => 'siswa'],
            [
                'title'        => 'Panduan Penggunaan Aplikasi Siswa Prescientia',
                'subtitle'     => 'Pelajari cara melakukan absensi dan memanfaatkan seluruh fitur aplikasi Prescientia untuk siswa.',
                'is_published' => false,
            ]
        );

        GuidelinePage::updateOrCreate(
            ['user_type' => 'guru'],
            [
                'title'        => 'Panduan Penggunaan Aplikasi Guru Prescientia',
                'subtitle'     => 'Pelajari cara melakukan absensi dan mengelola kehadiran kelas menggunakan aplikasi Prescientia untuk guru.',
                'is_published' => false,
            ]
        );
    }
}
