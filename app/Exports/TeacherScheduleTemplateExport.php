<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherScheduleTemplateExport implements FromArray, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            // ── Header row ──────────────────────────────────────────────────────
            ['nip', 'nama_guru', 'mapel', 'kelas', 'hari', 'jam_ke'],

            // ── Contoh data (15 baris, tidak ada bentrok) ───────────────────────
            // Kolom "nama_guru" hanya referensi — tidak dipakai saat import.
            // "kelas"  : format "TINGKAT JURUSAN"  →  contoh: "10 RPL", "11 AKL 1"
            // "hari"   : senin / selasa / rabu / kamis / jumat
            // "jam_ke" : nomor urut jam pelajaran (sesuai data Jam Pelajaran)

            // Senin
            ['19800101001', 'Ahmad Fauzan',     'Matematika',          '10 RPL',   'senin',  1],
            ['19820305002', 'Siti Munawaroh',   'Bahasa Indonesia',    '10 RPL',   'senin',  2],
            ['19851112003', 'Budi Hartono',     'Fisika',              '10 AKL 1', 'senin',  1],
            ['19780620004', 'Dewi Kurniasih',   'Biologi',             '10 AKL 1', 'senin',  2],
            ['19900428005', 'Rizky Firmansyah', 'Bahasa Inggris',      '10 AKL 2', 'senin',  1],
            ['19880715006', 'Nurul Khasanah',   'Sejarah',             '10 AKL 2', 'senin',  2],

            // Selasa
            ['19800101001', 'Ahmad Fauzan',     'Matematika',          '11 AKL 1', 'selasa', 1],
            ['19820305002', 'Siti Munawaroh',   'Bahasa Indonesia',    '11 AKL 1', 'selasa', 2],
            ['19830209007', 'Fajar Nugroho',    'Pendidikan Jasmani',  '10 RPL',   'selasa', 1],
            ['19910930008', 'Rika Wulandari',   'Seni Budaya',         '10 RPL',   'selasa', 2],

            // Rabu
            ['19860517009', 'Hendra Prasetyo',  'Teknologi Informasi', '10 AKL 1', 'rabu',   1],
            ['19851112003', 'Budi Hartono',     'Kimia',               '10 RPL',   'rabu',   1],
            ['19880715006', 'Nurul Khasanah',   'IPS',                 '11 AKL 1', 'rabu',   2],
            ['19940223010', 'Indah Ratnasari',  'PKN',                 '10 AKL 2', 'rabu',   1],

            // Jumat
            ['19940223010', 'Indah Ratnasari',  'Agama',               '10 RPL',   'jumat',  1],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [
            // Bold header row — dark indigo background
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1D4ED8']],
            ],
        ];

        // Alternating light-blue rows 2–16
        for ($i = 2; $i <= 16; $i++) {
            $styles[$i] = [
                'fill' => [
                    'fillType'   => 'solid',
                    'startColor' => ['rgb' => ($i % 2 === 0) ? 'EFF6FF' : 'DBEAFE'],
                ],
            ];
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,  // nip
            'B' => 26,  // nama_guru (referensi)
            'C' => 26,  // mapel
            'D' => 14,  // kelas
            'E' => 12,  // hari
            'F' => 10,  // jam_ke
        ];
    }
}
