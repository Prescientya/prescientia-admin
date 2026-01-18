<?php

namespace App\Exports;

use App\Models\TeacherAttendance;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TeacherAttendanceExport
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function export()
    {
        // Build query with filters
        $query = TeacherAttendance::with(['teacher', 'calendar']);

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
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

        if ($this->filters['subject_id']) {
            $query->whereHas('teacher', function ($q) {
                $q->whereHas('subjects', function ($s) {
                    $s->where('subjects.id', $this->filters['subject_id']);
                });
            });
        }

        if ($this->filters['keyword']) {
            $query->whereHas('teacher', function ($q) {
                $q->where('name', 'like', "%{$this->filters['keyword']}%")
                  ->orWhere('nip', 'like', "%{$this->filters['keyword']}%");
            });
        }

        $attendances = $query->latest()->get();

        // Get subject info for header
        $subjectInfo = '-';
        if ($this->filters['subject_id']) {
            $subject = \App\Models\Subject::find($this->filters['subject_id']);
            $subjectInfo = $subject ? $subject->name : '-';
        }

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Recap Absensi');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(15);

        // Header
        $sheet->setCellValue('A1', 'Recap Absensi Guru');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Mata Pelajaran info
        $sheet->setCellValue('A2', 'Mata Pelajaran: ' . $subjectInfo);
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        // Date range
        $dateFrom = $this->filters['date_from'] ? Carbon::parse($this->filters['date_from'])->format('d M Y') : '-';
        $dateTo = $this->filters['date_to'] ? Carbon::parse($this->filters['date_to'])->format('d M Y') : '-';
        $sheet->setCellValue('A3', "Periode: $dateFrom - $dateTo");
        $sheet->mergeCells('A3:F3');
        $sheet->getStyle('A3')->getFont()->setSize(10);

        // Empty row
        $sheet->setCellValue('A4', '');

        // Column headers
        $headers = ['No', 'Tanggal', 'Nama Guru', 'NIP', 'Status', 'Sumber'];
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
            $sheet->setCellValue('C' . $row, $attendance->teacher->name ?? '-');
            $sheet->setCellValue('D' . $row, $attendance->teacher->nip ?? '-');
            $sheet->setCellValue('E' . $row, ucfirst($attendance->status ?? '-'));
            $sheet->setCellValue('F' . $row, $attendance->source ? str_replace('_', ' ', ucfirst($attendance->source)) : '-');

            // Center align for certain columns
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal('center');

            // Color code status
            if ($attendance->status === 'hadir') {
                $sheet->getStyle('E' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFC6EFCE');
            } elseif ($attendance->status === 'sakit') {
                $sheet->getStyle('E' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFEB9C');
            } elseif ($attendance->status === 'izin') {
                $sheet->getStyle('E' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFC5D9F1');
            } elseif ($attendance->status === 'dinas') {
                $sheet->getStyle('E' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE2EFDA');
            } elseif ($attendance->status === 'alpa') {
                $sheet->getStyle('E' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF4CCCC');
            }

            $row++;
        }

        // Generate filename
        $fileName = 'Recap_Absensi_Guru_' . str_replace(' ', '_', $subjectInfo) . '_' . date('Y-m-d') . '.xlsx';

        // Return file
        $writer = new Xlsx($spreadsheet);
        $temp = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp);

        return response()->download($temp, $fileName)->deleteFileAfterSend(true);
    }
}
