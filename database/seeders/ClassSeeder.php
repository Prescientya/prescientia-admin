<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seed data kelas dengan kombinasi kelas dan jurusan.
     * Edit array $classes di bawah sesuai kebutuhan sekolah Anda.
     */
    public function run(): void
    {
        // Data kelas dan jurusan sesuai sekolah
        // Jurusan: AKL 1-4, HTL 1-2, KLN 1-2, MPLB 1-3, PM 1-3, DKV, RPL
        $classes = [
            // AKL 1
            ['class' => 10, 'major' => 'AKL 1'],
            ['class' => 11, 'major' => 'AKL 1'],
            ['class' => 12, 'major' => 'AKL 1'],
            
            // AKL 2
            ['class' => 10, 'major' => 'AKL 2'],
            ['class' => 11, 'major' => 'AKL 2'],
            ['class' => 12, 'major' => 'AKL 2'],
            
            // AKL 3
            ['class' => 10, 'major' => 'AKL 3'],
            ['class' => 11, 'major' => 'AKL 3'],
            ['class' => 12, 'major' => 'AKL 3'],
            
            // AKL 4
            ['class' => 10, 'major' => 'AKL 4'],
            ['class' => 11, 'major' => 'AKL 4'],
            ['class' => 12, 'major' => 'AKL 4'],

            // AKL 5
            ['class' => 10, 'major' => 'AKL 5'],
            ['class' => 11, 'major' => 'AKL 5'],
            ['class' => 12, 'major' => 'AKL 5'],
            
            // HTL 1
            ['class' => 10, 'major' => 'HTL 1'],
            ['class' => 11, 'major' => 'HTL 1'],
            ['class' => 12, 'major' => 'HTL 1'],
            
            // HTL 2
            ['class' => 10, 'major' => 'HTL 2'],
            ['class' => 11, 'major' => 'HTL 2'],
            ['class' => 12, 'major' => 'HTL 2'],
            
            // KLN 1
            ['class' => 10, 'major' => 'KLN 1'],
            ['class' => 11, 'major' => 'KLN 1'],
            ['class' => 12, 'major' => 'KLN 1'],
            
            // KLN 2
            ['class' => 10, 'major' => 'KLN 2'],
            ['class' => 11, 'major' => 'KLN 2'],
            ['class' => 12, 'major' => 'KLN 2'],
            
            // MPLB 1
            ['class' => 10, 'major' => 'MPLB 1'],
            ['class' => 11, 'major' => 'MPLB 1'],
            ['class' => 12, 'major' => 'MPLB 1'],
            
            // MPLB 2
            ['class' => 10, 'major' => 'MPLB 2'],
            ['class' => 11, 'major' => 'MPLB 2'],
            ['class' => 12, 'major' => 'MPLB 2'],
            
            // MPLB 3
            ['class' => 10, 'major' => 'MPLB 3'],
            ['class' => 11, 'major' => 'MPLB 3'],
            ['class' => 12, 'major' => 'MPLB 3'],
            
            // PM 1
            ['class' => 10, 'major' => 'PM 1'],
            ['class' => 11, 'major' => 'PM 1'],
            ['class' => 12, 'major' => 'PM 1'],
            
            // PM 2
            ['class' => 10, 'major' => 'PM 2'],
            ['class' => 11, 'major' => 'PM 2'],
            ['class' => 12, 'major' => 'PM 2'],
            
            // PM 3
            ['class' => 10, 'major' => 'PM 3'],
            ['class' => 11, 'major' => 'PM 3'],
            ['class' => 12, 'major' => 'PM 3'],
            
            // DKV
            ['class' => 10, 'major' => 'DKV'],
            ['class' => 11, 'major' => 'DKV'],
            ['class' => 12, 'major' => 'DKV'],
            
            // RPL
            ['class' => 10, 'major' => 'RPL'],
            ['class' => 11, 'major' => 'RPL'],
            ['class' => 12, 'major' => 'RPL'],
        ];

        // Insert atau skip jika sudah ada
        foreach ($classes as $classData) {
            ClassModel::firstOrCreate(
                [
                    'class' => $classData['class'],
                    'major' => $classData['major'],
                ]
            );
        }

        $this->command->info('Class seeding completed successfully!');
    }
}

