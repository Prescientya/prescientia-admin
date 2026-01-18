<?php

namespace App\Exports;

use App\Models\StudentAttendance;
use App\Models\ClassModel;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentAttendanceExport
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function export()
    {
        // Build query with filters
        $query = StudentAttendance::with(['student', 'class', 'calendar']);

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        // Filter by class number and/or major
        if ($this->filters['class_number']) {
            $query->whereHas('class', function ($q) {
                $q->where('class', $this->filters['class_number']);
                if ($this->filters['major']) {
                    $q->where('major', $this->filters['major']);
                }
            });
        } elseif ($this->filters['major']) {
            $query->whereHas('class', function ($q) {
                $q->where('major', $this->filters['major']);
            });
        }

        if ($this->filters['date_from'] || $this->filters['date_to']) {
            $query->whereHas('calendar', function ($q) {
                if ($this->filters['date_from']) {
                    $q->whereDate('date', '>=', $this->filters['date_from']);
                }
                if ($this->filters['date_to']) {
                    $q->whereDate('date', '<=', $this->filters['date_to']);
                }
            });
        }

        if ($this->filters['keyword']) {
            $query->whereHas('student', function ($q) {
                $q->where('name', 'like', "%{$this->filters['keyword']}%")
                  ->orWhere('nis', 'like', "%{$this->filters['keyword']}%");
            });
        }

        $attendances = $query->latest()->get();

        // Get class info for header (class number + major)
        $classInfo = '-';
        if ($this->filters['class_number']) {
            $classInfo = $this->filters['class_number'];
            if ($this->filters['major']) {
                $classInfo .= ' ' . $this->filters['major'];
            }
        } elseif ($this->filters['major']) {
            $classInfo = $this->filters['major'];
        }

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Recap Absensi');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(15);

        // Header
        $sheet->setCellValue('A1', 'Recap Absensi Siswa');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Kelas info
        $sheet->setCellValue('A2', 'Kelas: ' . $classInfo);
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        // Date range
        $dateFrom = $this->filters['date_from'] ? Carbon::parse($this->filters['date_from'])->format('d M Y') : '-';
        $dateTo = $this->filters['date_to'] ? Carbon::parse($this->filters['date_to'])->format('d M Y') : '-';
        $sheet->mergeCells('A3:F3');
        $sheet->getStyle('A3')->getFont()->setSize(10);

        // Empty row
        $sheet->setCellValue('A4', '');

        // Column headers
        $headers = ['No', 'Tanggal', 'Nama Siswa', 'NIS', 'Kelas', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '5', $header);
            $sheet->getStyle($col . '5')->getFont()->setBold(true);
            $sheet->getStyle($col . '5')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE0E0E0');
            $sheet->getStyle($col . '5')->getAlignment()->setHorizontal('center');
            $col++;
        }

        // Data rows
        $row = 6;
        foreach ($attendances as $index => $attendance) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $attendance->calendar ? Carbon::parse($attendance->calendar->date)->format('d M Y') : '-');
            $sheet->setCellValue('C' . $row, $attendance->student->name ?? '-');
            $sheet->setCellValue('D' . $row, $attendance->student->nis ?? '-');
            $sheet->setCellValue('E' . $row, optional($attendance->class)->class ?? '-');
            $sheet->setCellValue('F' . $row, ucfirst($attendance->status ?? '-'));

            // Center align for certain columns
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal('center');

            // Color code status
            if ($attendance->status === 'hadir') {
                $sheet->getStyle('F' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFC6EFCE');
            } elseif ($attendance->status === 'sakit') {
                $sheet->getStyle('F' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFEB9C');
            } elseif ($attendance->status === 'izin') {
                $sheet->getStyle('F' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFC5D9F1');
            } elseif ($attendance->status === 'alpa') {
                $sheet->getStyle('F' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF4CCCC');
            }

            $row++;
        }

        // Generate filename
        $fileName = 'Recap_Absensi_' . str_replace(' ', '_', $classInfo) . '_' . date('Y-m-d') . '.xlsx';

        // Return file
        $writer = new Xlsx($spreadsheet);
        $temp = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp);

        return response()->download($temp, $fileName)->deleteFileAfterSend(true);
    }
}
