<?php

namespace App\Exports;

use App\Models\ClassModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Template Excel untuk Import Guru Mengajar (Class Assignment)
 * 
 * Purpose: Menugaskan guru yang sudah ada ke kelas-kelas tertentu
 * 
 * Excel Format (STRICT):
 * - Column A: teacher_name (nama guru yang sudah terdaftar)
 * - Column B: class (angka kelas, bisa multiple dipisah koma: 10,11,12)
 * - Column C: major (jurusan, bisa multiple dipisah koma: RPL,RPL,RPL)
 * 
 * VALIDATION RULES:
 * - Jumlah class HARUS sama dengan jumlah major
 * - Teacher harus sudah terdaftar di sistem
 * - Class+Major combination harus valid
 */
class TeacherAssignmentTemplateExport
{
    public function generate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Guru Mengajar');

        // Set header row
        $headers = [
            'Nama Guru',
            'Kelas',
            'Jurusan',
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
                'startColor' => ['rgb' => 'dc2626'], // Red color for assignment
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];

        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(30); // Teacher Name
        $sheet->getColumnDimension('B')->setWidth(25); // Class (comma-separated)
        $sheet->getColumnDimension('C')->setWidth(25); // Major (comma-separated)

        // Add 10 dummy assignment rows (menggunakan kelas-jurusan yang valid dari aplikasi)
        $dummyData = [
            ['Ahmad Fauzi', '10', 'RPL'],
            ['Siti Nurhaliza', '10,11', 'RPL,RPL'],
            ['Budi Santoso', '10,11,12', 'RPL,RPL,RPL'],
            ['Dewi Kartika', '11', 'AKL 1'],
            ['Rizki Ramadhan', '11,12', 'AKL 2,AKL 2'],
            ['Linda Wijaya', '10,11', 'HTL 1,HTL 1'],
            ['Eko Prasetyo', '12', 'HTL 2'],
            ['Rina Sulastri', '10', 'MPLB 1'],
            ['Hendra Gunawan', '11,12', 'MPLB 2,MPLB 2'],
            ['Fitri Handayani', '10,11,12', 'PM 1,PM 1,PM 1'],
        ];

        $sheet->fromArray($dummyData, null, 'A2');

        // Style dummy data rows
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
        $sheet->getStyle('A2:C11')->applyFromArray($sampleStyle);

        // Freeze header row
        $sheet->freezePane('A2');

        // Second sheet: daftar kelas yang valid (class + major)
        $listSheet = $spreadsheet->createSheet();
        $listSheet->setTitle('Daftar Kelas');
        $listSheet->fromArray([['Kelas', 'Jurusan']], null, 'A1');

        // Fetch classes from DB (ClassModel)
        $classes = ClassModel::orderBy('class')->orderBy('major')->get();
        $rows = [];
        foreach ($classes as $c) {
            $rows[] = [$c->class, $c->major];
        }

        if (!empty($rows)) {
            $listSheet->fromArray($rows, null, 'A2');
            $listSheet->getColumnDimension('A')->setWidth(10);
            $listSheet->getColumnDimension('B')->setWidth(30);
        } else {
            // If no classes found, add example rows
            $listSheet->fromArray([
                [10, 'RPL'],
                [11, 'RPL'],
                [12, 'RPL']
            ], null, 'A2');
        }

        // Make the first sheet active again
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
