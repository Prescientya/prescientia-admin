<?php

namespace App\Exports;

use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentAttendanceExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected string $dateFrom;
    protected string $dateTo;
    protected ?int   $classId;
    protected ?string $status;

    /* Rows & tracked row positions for styling */
    private array $rows          = [];
    private array $classTitleRows = [];   // [rowIndex => 'Kelas X Y']
    private array $summaryRows    = [];   // rowIndex => true
    private array $grandTotalRow  = [];
    private int   $headerEndRow   = 6;    // rows used for the title block
    private array $altRows        = [];   // alternate row shading

    public function __construct(
        string $dateFrom,
        string $dateTo,
        ?int   $classId = null,
        ?string $status = null
    ) {
        $this->dateFrom = $dateFrom;
        $this->dateTo   = $dateTo;
        $this->classId  = $classId;
        $this->status   = $status;
    }

    public function title(): string { return 'Rekap Kehadiran Siswa'; }

    /* ── Build the array ──────────────────────────────────── */
    public function array(): array
    {
        $from = Carbon::parse($this->dateFrom);
        $to   = Carbon::parse($this->dateTo);

        // How many school days exist in the range?
        $totalDays = DB::table('school_calendar')
            ->where('status', 'aktif')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->count();

        /* ── 1. Title block ────────────────────────────────── */
        $this->rows[] = ['REKAP KEHADIRAN SISWA', '', '', '', '', '', '', '', '', ''];
        $this->rows[] = ['Periode', ':', $from->locale('id')->isoFormat('D MMMM YYYY') . '  —  ' . $to->locale('id')->isoFormat('D MMMM YYYY')];
        $this->rows[] = ['Diekspor', ':', Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm')];
        $this->rows[] = ['Hari Efektif (dalam range)', ':', $totalDays . ' hari'];

        /* ── 2. Grand summary header ───────────────────────── */
        $this->rows[] = [
            'No', 'NIS', 'Nama Siswa', 'Kelas',
            'Hadir', 'Sakit', 'Izin', 'Alpa', 'Terlambat',
            'Total Hadir (%)',
        ];
        $this->headerEndRow = count($this->rows); // row 6 = column header

        /* ── 3. Query attendance in range ──────────────────── */
        $classes = ClassModel::orderBy('class')->orderBy('major')
            ->when($this->classId, fn($q) => $q->where('id', $this->classId))
            ->get();

        $grandTotals = array_fill_keys(['hadir','sakit','izin','alpa','terlambat','students'], 0);
        $no = 0;

        foreach ($classes as $kelas) {
            $students = Student::where('class_id', $kelas->id)
                ->orderBy('name')
                ->get();

            if ($students->isEmpty()) continue;

            $classTotals = array_fill_keys(['hadir','sakit','izin','alpa','terlambat'], 0);

            /* Class title row */
            $classLabel = 'Kelas ' . $kelas->class . ($kelas->major ? ' ' . $kelas->major : '');
            $row = count($this->rows) + 1;
            $this->classTitleRows[$row] = $classLabel;
            $this->rows[] = [$classLabel, '', '', '', '', '', '', '', '', ''];

            $isAlt = false;
            foreach ($students as $student) {
                // Get all attendance for this student in the date range
                $atts = StudentAttendance::query()
                    ->join('school_calendar', 'school_calendar.id', '=', 'student_attendances.calendar_id')
                    ->where('student_attendances.student_id', $student->id)
                    ->where('school_calendar.status', 'aktif')
                    ->whereBetween('school_calendar.date', [$this->dateFrom, $this->dateTo])
                    ->when($this->status, fn($q) => $q->where('student_attendances.status', $this->status))
                    ->select('student_attendances.status')
                    ->get();

                $counts = $atts->countBy('status');
                $h = $counts->get('hadir', 0) + $counts->get('terlambat', 0); // terlambat = still attended
                $s = $counts->get('sakit', 0);
                $i = $counts->get('izin', 0);
                $a = $counts->get('alpa', 0);
                $t = $counts->get('terlambat', 0);
                $total      = $atts->count();
                $pct        = $totalDays > 0 ? round(($h / $totalDays) * 100, 1) : 0;

                foreach (['hadir','sakit','izin','alpa','terlambat'] as $k) {
                    $classTotals[$k]  += $counts->get($k, 0);
                    $grandTotals[$k]  += $counts->get($k, 0);
                }

                $rowIdx = count($this->rows) + 1;
                if ($isAlt) $this->altRows[] = $rowIdx;
                $isAlt = !$isAlt;

                $this->rows[] = [
                    ++$no,
                    $student->nis ?? '',
                    $student->name,
                    $classLabel,
                    $counts->get('hadir', 0),
                    $s,
                    $i,
                    $a,
                    $t,
                    $pct . '%',
                ];
            }

            /* Class subtotal */
            $sumRow = count($this->rows) + 1;
            $this->summaryRows[$sumRow] = true;
            $totalStudents = $students->count();
            $grandTotals['students'] += $totalStudents;

            $this->rows[] = [
                '', '', "Subtotal {$classLabel} ({$totalStudents} siswa)", '',
                $classTotals['hadir'],
                $classTotals['sakit'],
                $classTotals['izin'],
                $classTotals['alpa'],
                $classTotals['terlambat'],
                '',
            ];

            $this->rows[] = []; // spacer between classes
        }

        /* ── 4. Grand total row ───────────────────────────── */
        $gtRow = count($this->rows) + 1;
        $this->grandTotalRow = [$gtRow];
        $this->rows[] = [
            '', '', 'GRAND TOTAL (' . $grandTotals['students'] . ' siswa)', '',
            $grandTotals['hadir'],
            $grandTotals['sakit'],
            $grandTotals['izin'],
            $grandTotals['alpa'],
            $grandTotals['terlambat'],
            '',
        ];

        return $this->rows;
    }

    /* ── Apply styles ─────────────────────────────────────── */
    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->rows);
        $styles  = [];

        // Title row 1
        $styles[1] = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1e3a5f']],
        ];
        // Rows 2-4: info labels
        foreach ([2, 3, 4] as $r) {
            $styles[$r] = ['font' => ['size' => 10, 'color' => ['rgb' => '475569']]];
        }

        // Column header row
        $colHeaderRow = $this->headerEndRow;
        $styles[$colHeaderRow] = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        // Class title rows (teal background)
        foreach (array_keys($this->classTitleRows) as $r) {
            $styles[$r] = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'ffffff'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0f766e']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ];
        }

        // Alternating data rows
        foreach ($this->altRows as $r) {
            $styles[$r] = [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'eff6ff']],
            ];
        }

        // Subtotal rows
        foreach (array_keys($this->summaryRows) as $r) {
            $styles[$r] = [
                'font' => ['bold' => true, 'italic' => true, 'color' => ['rgb' => '0f766e']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0fdfa']],
                'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0f766e']]],
            ];
        }

        // Grand total row
        foreach ($this->grandTotalRow as $r) {
            $styles[$r] = [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'ffffff']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
                'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1e3a5f']]],
            ];
        }

        // Merge title and class title rows across all columns
        $sheet->mergeCells("A1:J1");
        foreach (array_keys($this->classTitleRows) as $r) {
            $sheet->mergeCells("A{$r}:J{$r}");
        }
        foreach ($this->grandTotalRow as $r) {
            $sheet->mergeCells("C{$r}:D{$r}");
        }
        foreach (array_keys($this->summaryRows) as $r) {
            $sheet->mergeCells("C{$r}:D{$r}");
        }

        // Borders around data area (from col-header row down to last row)
        $dataRange = "A{$this->headerEndRow}:J{$lastRow}";
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('cbd5e1');

        // Freeze the column header row
        $sheet->freezePane('A' . ($this->headerEndRow + 1));

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 14,  // NIS
            'C' => 32,  // Nama
            'D' => 18,  // Kelas
            'E' => 10,  // Hadir
            'F' => 10,  // Sakit
            'G' => 10,  // Izin
            'H' => 10,  // Alpa
            'I' => 12,  // Terlambat
            'J' => 16,  // %Kehadiran
        ];
    }
}
