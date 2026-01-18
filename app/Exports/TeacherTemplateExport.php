<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Template Excel untuk Import Akun Guru
 * 
 * Purpose: Memandu admin untuk membuat akun guru dengan benar
 * Contains: 10 dummy rows dengan contoh data
 * Subjects: Limited to 5 general subjects (Matematika, Bahasa Indonesia, Bahasa Inggris, Pendidikan Agama, PJOK)
 * 
 * IMPORTANT: Template ini HANYA untuk pembuatan akun guru.
 * Penugasan kelas (class assignment) dilakukan terpisah di halaman Manajemen Guru Mengajar.
 */
class TeacherTemplateExport
{
    public function generate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Akun Guru');

        // Set header row (ACCOUNT ONLY - NO CLASS ASSIGNMENT)
        $headers = [
            'Email',
            'NIP',
            'Nama',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Nomor Telepon',
            'Alamat',
            'Mata Pelajaran',
        ];
        $sheet->fromArray([$headers], null, 'A1');

        // Style header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563eb'], // Blue color
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];

        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(30); // Email
        $sheet->getColumnDimension('B')->setWidth(18); // NIP
        $sheet->getColumnDimension('C')->setWidth(25); // Nama
        $sheet->getColumnDimension('D')->setWidth(15); // Gender
        $sheet->getColumnDimension('E')->setWidth(18); // Birth Date
        $sheet->getColumnDimension('F')->setWidth(18); // Phone
        $sheet->getColumnDimension('G')->setWidth(35); // Address
        $sheet->getColumnDimension('H')->setWidth(40); // Subjects (comma-separated)

        // Add 10 dummy teacher rows with realistic data
        $dummyData = [
            ['ahmad.fauzi@sekolah.id', '198501011', 'Ahmad Fauzi', 'L', '1985-01-15', '081234567801', 'Jl. Pendidikan No. 1, Jakarta', 'Matematika'],
            ['siti.nurhaliza@sekolah.id', '198602012', 'Siti Nurhaliza', 'P', '1986-02-20', '081234567802', 'Jl. Guru No. 2, Bandung', 'Bahasa Indonesia'],
            ['budi.santoso@sekolah.id', '198703013', 'Budi Santoso', 'L', '1987-03-10', '081234567803', 'Jl. Cendekia No. 3, Surabaya', 'Bahasa Inggris'],
            ['dewi.kartika@sekolah.id', '198804014', 'Dewi Kartika', 'P', '1988-04-25', '081234567804', 'Jl. Ilmu No. 4, Yogyakarta', 'Pendidikan Agama'],
            ['rizki.ramadhan@sekolah.id', '198905015', 'Rizki Ramadhan', 'L', '1989-05-12', '081234567805', 'Jl. Belajar No. 5, Semarang', 'PJOK'],
            ['linda.wijaya@sekolah.id', '199006016', 'Linda Wijaya', 'P', '1990-06-18', '081234567806', 'Jl. Pahlawan No. 6, Malang', 'Matematika, Bahasa Indonesia'],
            ['eko.prasetyo@sekolah.id', '199107017', 'Eko Prasetyo', 'L', '1991-07-22', '081234567807', 'Jl. Merdeka No. 7, Solo', 'Bahasa Inggris, PJOK'],
            ['rina.sulastri@sekolah.id', '199208018', 'Rina Sulastri', 'P', '1992-08-30', '081234567808', 'Jl. Kemerdekaan No. 8, Medan', 'Pendidikan Agama, Bahasa Indonesia'],
            ['hendra.gunawan@sekolah.id', '199309019', 'Hendra Gunawan', 'L', '1993-09-14', '081234567809', 'Jl. Proklamasi No. 9, Palembang', 'Matematika, Bahasa Inggris'],
            ['fitri.handayani@sekolah.id', '199410020', 'Fitri Handayani', 'P', '1994-10-05', '081234567810', 'Jl. Nusantara No. 10, Makassar', 'Bahasa Indonesia, Pendidikan Agama'],
        ];

        $sheet->fromArray($dummyData, null, 'A2');

        // Style dummy data rows (light gray to indicate it's sample data)
        $sampleStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'color' => ['rgb' => '6b7280'], // Gray color
                'size' => 10,
            ],
        ];
        $sheet->getStyle('A2:H11')->applyFromArray($sampleStyle);

        // Freeze header row
        $sheet->freezePane('A2');

        return $spreadsheet;
    }
}
