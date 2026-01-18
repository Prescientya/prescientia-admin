<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class StudentTemplateExport
{
    public function generate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Siswa');

        // Set header row
        $headers = ['Email', 'NIS', 'Nama', 'Jenis Kelamin', 'Tanggal Lahir', 'Nomor Telepon', 'Alamat', 'Kelas', 'Jurusan'];
        $sheet->fromArray([$headers], null, 'A1');

        // Style header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF6B00'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];

        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(20);

        // Add sample row (optional)
        $sampleData = [
            ['siswa@email.com', '12345', 'John Doe', 'L', '2006-01-15', '081234567890', 'Jl. Merdeka No. 123', '10', 'RPL'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Style sample row
        $sampleStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'color' => ['rgb' => 'CCCCCC'],
            ],
        ];
        $sheet->getStyle('A2:I2')->applyFromArray($sampleStyle);

        // Freeze header row
        $sheet->freezePane('A2');

        return $spreadsheet;
    }
}
