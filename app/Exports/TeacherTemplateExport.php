<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherTemplateExport implements FromArray, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            // Header row
            ['nip', 'nama', 'email', 'gender', 'tanggal_lahir', 'no_hp', 'alamat', 'mapel'],

            // Example data — mapel can be single or comma-separated (e.g. "Matematika,Fisika")
            ['19800101001', 'Ahmad Fauzan',       'ahmad.fauzan@guru.sch.id',      'L', '1980-01-01', '081234567801', 'Jl. Mawar No.1, Jakarta',        'Matematika'],
            ['19820305002', 'Siti Munawaroh',     'siti.munawaroh@guru.sch.id',    'P', '1982-03-05', '081234567802', 'Jl. Melati No.2, Bandung',        'Bahasa Indonesia'],
            ['19851112003', 'Budi Hartono',       'budi.hartono@guru.sch.id',      'L', '1985-11-12', '081234567803', 'Jl. Anggrek No.3, Surabaya',      'Fisika,Kimia'],
            ['19780620004', 'Dewi Kurniasih',     'dewi.kurniasih@guru.sch.id',    'P', '1978-06-20', '081234567804', 'Jl. Dahlia No.4, Yogyakarta',     'Biologi'],
            ['19900428005', 'Rizky Firmansyah',   'rizky.firmansyah@guru.sch.id',  'L', '1990-04-28', '081234567805', 'Jl. Kenanga No.5, Semarang',      'Bahasa Inggris'],
            ['19880715006', 'Nurul Khasanah',     'nurul.khasanah@guru.sch.id',    'P', '1988-07-15', '081234567806', 'Jl. Flamboyan No.6, Medan',       'Sejarah,IPS'],
            ['19830209007', 'Fajar Nugroho',      'fajar.nugroho@guru.sch.id',     'L', '1983-02-09', '081234567807', 'Jl. Cempaka No.7, Makassar',      'Pendidikan Jasmani'],
            ['19910930008', 'Rika Wulandari',     'rika.wulandari@guru.sch.id',    'P', '1991-09-30', '081234567808', 'Jl. Bougenville No.8, Palembang', 'Seni Budaya'],
            ['19860517009', 'Hendra Prasetyo',    'hendra.prasetyo@guru.sch.id',   'L', '1986-05-17', '081234567809', 'Jl. Lavender No.9, Denpasar',     'Teknologi Informasi'],
            ['19940223010', 'Indah Ratnasari',    'indah.ratnasari@guru.sch.id',   'P', '1994-02-23', '081234567810', 'Jl. Tulip No.10, Malang',         'PKN,Agama'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [
            // Bold header row with blue background
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1D4ED8']],
            ],
        ];

        // Light blue for all 10 example rows (rows 2–11)
        for ($i = 2; $i <= 11; $i++) {
            $styles[$i] = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EFF6FF']]];
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,  // nip
            'B' => 28,  // nama
            'C' => 32,  // email
            'D' => 10,  // gender
            'E' => 18,  // tanggal_lahir
            'F' => 18,  // no_hp
            'G' => 38,  // alamat
            'H' => 35,  // mapel (comma-separated)
        ];
    }
}
