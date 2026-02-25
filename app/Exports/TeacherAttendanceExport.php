<?php

namespace App\Exports;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
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

class TeacherAttendanceExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected string  $dateFrom;
    protected string  $dateTo;
    protected ?string $status;

    private array $rows         = [];
    private array $summaryRows  = [];
    private int   $headerEndRow = 5;
    private array $altRows      = [];

    public function __construct(
        string  $dateFrom,
        string  $dateTo,
        ?string $status = null
    ) {
        $this->dateFrom = $dateFrom;
        $this->dateTo   = $dateTo;
        $this->status   = $status;
    }

    public function title(): string { return 'Rekap Kehadiran Guru'; }

    public function array(): array
    {
        $from = Carbon::parse($this->dateFrom);
        $to   = Carbon::parse($this->dateTo);

        $totalDays = DB::table('school_calendar')
            ->where('status', 'aktif')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->count();

        /* ── Title block ──────────────────────────────── */
        $this->rows[] = ['REKAP KEHADIRAN GURU', '', '', '', '', '', '', '', ''];
        $this->rows[] = ['Periode', ':', $from->locale('id')->isoFormat('D MMMM YYYY') . '  —  ' . $to->locale('id')->isoFormat('D MMMM YYYY')];
        $this->rows[] = ['Diekspor', ':', Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm')];
        $this->rows[] = ['Hari Efektif (dalam range)', ':', $totalDays . ' hari'];

        /* ── Column header ────────────────────────────── */
        $this->rows[] = [
            'No', 'NIP', 'Nama Guru',
            'Hadir', 'Sakit', 'Izin', 'Dinas', 'Alpa', 'Terlambat',
        ];
        $this->headerEndRow = count($this->rows);

        /* ── Query ────────────────────────────────────── */
        $calendarIds = DB::table('school_calendar')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->pluck('id');

        $teachers = Teacher::orderBy('name')->get();

        $grandTotals = array_fill_keys(['hadir', 'sakit', 'izin', 'dinas', 'alpa', 'terlambat', 'teachers'], 0);
        $no = 0;

        $isAlt = false;
        foreach ($teachers as $teacher) {
            $atts = TeacherAttendance::query()
                ->where('teacher_id', $teacher->id)
                ->whereIn('calendar_id', $calendarIds)
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->get();

            if ($atts->isEmpty() && $this->status) continue;

            $counts = array_fill_keys(['hadir', 'sakit', 'izin', 'dinas', 'alpa', 'terlambat'], 0);
            foreach ($atts as $a) {
                if (isset($counts[$a->status])) {
                    $counts[$a->status]++;
                }
            }

            $no++;
            $rowIdx = count($this->rows) + 1;
            if ($isAlt) $this->altRows[] = $rowIdx;
            $isAlt = !$isAlt;

            $this->rows[] = [
                $no,
                $teacher->nip ?? '–',
                $teacher->name,
                $counts['hadir'],
                $counts['sakit'],
                $counts['izin'],
                $counts['dinas'],
                $counts['alpa'],
                $counts['terlambat'],
            ];

            foreach (['hadir', 'sakit', 'izin', 'dinas', 'alpa', 'terlambat'] as $s) {
                $grandTotals[$s] += $counts[$s];
            }
            $grandTotals['teachers']++;
        }

        /* ── Grand total row ──────────────────────────── */
        $totalRowIdx = count($this->rows) + 1;
        $this->summaryRows[] = $totalRowIdx;
        $this->rows[] = [
            '', '', 'TOTAL (' . $grandTotals['teachers'] . ' Guru)',
            $grandTotals['hadir'],
            $grandTotals['sakit'],
            $grandTotals['izin'],
            $grandTotals['dinas'],
            $grandTotals['alpa'],
            $grandTotals['terlambat'],
        ];

        return $this->rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 18,
            'C' => 32,
            'D' => 10,
            'E' => 10,
            'F' => 10,
            'G' => 10,
            'H' => 10,
            'I' => 12,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow   = count($this->rows);
        $headerRow = $this->headerEndRow;

        /* ── Title row ──────────────────────────── */
        $sheet->mergeCells("A1:I1");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        /* ── Column header ──────────────────────── */
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        /* ── Data rows border ───────────────────── */
        $dataStart = $headerRow + 1;
        if ($dataStart <= $lastRow) {
            $sheet->getStyle("A{$dataStart}:I{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);
        }

        /* ── Alternate row shading ──────────────── */
        foreach ($this->altRows as $r) {
            $sheet->getStyle("A{$r}:I{$r}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
            ]);
        }

        /* ── Summary (grand total) rows ─────────── */
        foreach ($this->summaryRows as $r) {
            $sheet->getStyle("A{$r}:I{$r}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
            ]);
        }

        /* ── Center number columns ──────────────── */
        for ($r = $dataStart; $r <= $lastRow; $r++) {
            $sheet->getStyle("D{$r}:I{$r}")->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
