<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentTemplateExport implements FromArray, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            // Header row
            ['nis', 'nama', 'email', 'gender', 'tanggal_lahir', 'no_hp', 'alamat', 'tingkat', 'jurusan'],
            // 11 contoh data — tingkat & jurusan harus sesuai data di menu Data Kelas
            ['2024001', 'Ahmad Fauzi',        'ahmad.fauzi@email.com',      'L', '2007-03-12', '081234567801', 'Jl. Mawar No. 1, Jakarta',        '10', 'RPL'],
            ['2024002', 'Siti Rahayu',         'siti.rahayu@email.com',      'P', '2007-06-25', '081234567802', 'Jl. Melati No. 2, Bandung',        '10', 'AKL 1'],
            ['2024003', 'Budi Santoso',        'budi.santoso@email.com',     'L', '2006-11-08', '081234567803', 'Jl. Anggrek No. 3, Surabaya',      '11', 'TKJ'],
            ['2024004', 'Dewi Lestari',        'dewi.lestari@email.com',     'P', '2006-04-17', '081234567804', 'Jl. Dahlia No. 4, Yogyakarta',     '11', 'AKL 2'],
            ['2024005', 'Rizky Pratama',       'rizky.pratama@email.com',    'L', '2005-09-30', '081234567805', 'Jl. Kenanga No. 5, Semarang',      '12', 'DKV'],
            ['2024006', 'Nurul Hidayah',       'nurul.hidayah@email.com',    'P', '2007-01-14', '081234567806', 'Jl. Flamboyan No. 6, Medan',       '10', 'MM'],
            ['2024007', 'Fajar Setiawan',      'fajar.setiawan@email.com',   'L', '2006-07-22', '081234567807', 'Jl. Cempaka No. 7, Makassar',      '11', 'HTL 1'],
            ['2024008', 'Rika Amalia',         'rika.amalia@email.com',      'P', '2005-12-05', '081234567808', 'Jl. Bougenville No. 8, Palembang', '12', 'MPLB 1'],
            ['2024009', 'Hendra Wijaya',       'hendra.wijaya@email.com',    'L', '2007-02-18', '081234567809', 'Jl. Lavender No. 9, Denpasar',     '10', 'KLN 1'],
            ['2024010', 'Indah Permatasari',   'indah.permatasari@email.com','P', '2006-08-09', '081234567810', 'Jl. Tulip No. 10, Malang',         '11', 'PM 1'],
            ['2024011', 'Dimas Ardiansyah',    'dimas.ardiansyah@email.com', 'L', '2005-05-27', '081234567811', 'Jl. Orchid No. 11, Bogor',         '12', 'AKL 3'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [
            // Bold header row
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '166534']],
            ],
        ];

        // Light gray for all 11 example rows (rows 2–12)
        for ($i = 2; $i <= 12; $i++) {
            $styles[$i] = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F0FDF4']]];
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // nis
            'B' => 28,  // nama
            'C' => 30,  // email
            'D' => 10,  // gender
            'E' => 18,  // tanggal_lahir
            'F' => 18,  // no_hp
            'G' => 35,  // alamat
            'H' => 10,  // tingkat
            'I' => 15,  // jurusan
        ];
    }
}
