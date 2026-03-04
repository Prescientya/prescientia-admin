<?php

namespace App\Exports;

use Database\Seeders\ClassPeriodSeeder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export template jam pelajaran.
 *
 * Baris 1        : header kolom (hari | jam_ke | waktu_mulai | waktu_selesai | jenis | keterangan)
 * Baris 2 – akhir: data default dari ClassPeriodSeeder (5 hari)
 * Baris kosong + 2 baris info tersedia di bagian bawah sebagai petunjuk.
 *
 * Nilai valid kolom "jenis":
 *   lesson   = Jam Pelajaran
 *   break    = Istirahat
 *   ceremony = Upacara
 *   prayer   = Ibadah / Sholat
 *   cleaning = Kebersihan
 *   other    = Lainnya
 */
class ClassPeriodTemplateExport implements FromArray, WithStyles, WithColumnWidths
{
    /** Track spreadsheet row → activity_type for coloring in styles(). */
    private array $rowTypes = [];

    /* ── DATA ────────────────────────────────────────── */

    public function array(): array
    {
        $rows = [];

        // Row 1: column headers
        $rows[] = ['hari', 'jam_ke', 'waktu_mulai', 'waktu_selesai', 'jenis', 'keterangan',
                   'INFO: jenis yang valid → lesson | break | ceremony | prayer | cleaning | other'];

        // Rows 2+: default schedule (all 5 days)
        $seeder = new ClassPeriodSeeder;
        foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat'] as $day) {
            foreach ($seeder->rowsForDay($day) as $dbRow) {
                $spreadsheetRow = count($rows) + 1; // 1-based
                $this->rowTypes[$spreadsheetRow] = $dbRow['activity_type'];

                $rows[] = [
                    $dbRow['day'],
                    $dbRow['sequence'],
                    substr($dbRow['start_time'], 0, 5),  // HH:MM
                    substr($dbRow['end_time'],   0, 5),  // HH:MM
                    $dbRow['activity_type'],
                    $dbRow['note'] ?? '',
                    '',  // info column — blank for data rows
                ];
            }
        }

        // Spacer + info rows at the bottom (will be skipped by importer)
        $rows[] = [];
        $rows[] = ['[INFO] Hari yang valid: senin, selasa, rabu, kamis, jumat'];
        $rows[] = ['[INFO] Kolom keterangan bersifat opsional. Kolom durasi dihitung otomatis dari waktu mulai dan selesai.'];

        return $rows;
    }

    /* ── STYLES ──────────────────────────────────────── */

    public function styles(Worksheet $sheet): array
    {
        // Color map: activity_type → hex rgb (null = no fill / white)
        $colorMap = [
            'lesson'   => null,      // white/transparent
            'break'    => '00B050',  // green  (Istirahat / MBG)
            'ceremony' => 'FFFF00',  // yellow (Upacara, Kerohanian, dsb)
            'cleaning' => 'FFFF00',  // yellow (Tadarus & Kebersihan)
            'prayer'   => 'FFFF00',  // yellow (Shalat Jum'at / Keputrian)
            'other'    => 'FFD700',  // gold   (Ekstra Kurikuler)
        ];

        $styles = [
            // Row 1: bold header – dark blue bg, white text
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1D4ED8']],
            ],
        ];

        foreach ($this->rowTypes as $row => $type) {
            $color = $colorMap[$type] ?? null;
            if ($color) {
                $styles[$row] = [
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $color]],
                ];
            }
        }

        // Auto-fit row heights + freeze the header row
        $sheet->freezePane('A2');

        return $styles;
    }

    /* ── COLUMN WIDTHS ───────────────────────────────── */

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // hari
            'B' => 8,   // jam_ke
            'C' => 14,  // waktu_mulai
            'D' => 14,  // waktu_selesai
            'E' => 16,  // jenis
            'F' => 44,  // keterangan
            'G' => 70,  // info column (header only)
        ];
    }
}
