<?php

namespace App\Imports;

use App\Models\ClassModel;
use App\Models\ClassPeriod;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSchedule;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Import Teacher Schedules from Excel
 * 
 * Excel Format:
 * - Column A: email (teacher email)
 * - Column B: teacher_name (for reference)
 * - Column C: class (e.g., "X RPL 1", "XI IPA 1")
 * - Column D: subject (subject name)
 * - Column E: semester (1 or 2)
 * - Column F: day (senin, selasa, rabu, kamis, jumat)
 * - Column G: period_sequence (1, 2, 3, etc.)
 */
class TeacherScheduleImport
{
    protected $worksheet;
    protected $inserted = 0;
    protected $skipped = 0;
    protected $failed = 0;
    protected $failures = [];

    public function __construct(Worksheet $worksheet)
    {
        $this->worksheet = $worksheet;
    }

    /**
     * Process the import and return results
     */
    public function process(): array
    {
        $rows = $this->worksheet->toArray(null, true, true, true);
        $headerRow = true;

        foreach ($rows as $index => $row) {
            // Skip header row
            if ($headerRow) {
                $headerRow = false;
                continue;
            }

            $rowNumber = $index;

            // Extract data from columns
            $data = [
                'email' => isset($row['A']) ? trim($row['A']) : null,
                'teacher_name' => isset($row['B']) ? trim($row['B']) : null,
                'class' => isset($row['C']) ? trim($row['C']) : null,
                'subject' => isset($row['D']) ? trim($row['D']) : null,
                'semester' => isset($row['E']) ? trim($row['E']) : null,
                'day' => isset($row['F']) ? strtolower(trim($row['F'])) : null,
                'period_sequence' => isset($row['G']) ? trim($row['G']) : null,
            ];

            // Skip empty rows
            if ($this->isEmptyRow($data)) {
                continue;
            }

            $this->processRow($data, $rowNumber);
        }

        return [
            'inserted' => $this->inserted,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
            'failures' => $this->failures,
        ];
    }

    /**
     * Process a single row
     */
    protected function processRow(array $data, int $rowNumber)
    {
        try {
            // Find teacher by email
            $teacher = Teacher::where('email', $data['email'])->first();
            if (!$teacher) {
                $this->failed++;
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Guru dengan email ' . $data['email'] . ' tidak ditemukan'],
                ];
                return;
            }

            // Find class by name (e.g., "X RPL 1" -> class=10, major="RPL 1")
            $classModel = $this->findClass($data['class']);
            if (!$classModel) {
                $this->failed++;
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Kelas ' . $data['class'] . ' tidak ditemukan'],
                ];
                return;
            }

            // Find subject by name
            $subject = Subject::where('name', $data['subject'])->first();
            if (!$subject) {
                $this->failed++;
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Mata pelajaran ' . $data['subject'] . ' tidak ditemukan'],
                ];
                return;
            }

            // Find class_period by day and sequence
            $period = ClassPeriod::where('day', $data['day'])
                ->where('sequence', $data['period_sequence'])
                ->first();
            if (!$period) {
                $this->failed++;
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Jam pelajaran ' . $data['period_sequence'] . ' pada hari ' . $data['day'] . ' tidak ditemukan'],
                ];
                return;
            }

            // Try to create
            try {
                TeacherClassSchedule::create([
                    'teacher_id' => $teacher->id,
                    'class_id' => $classModel->id,
                    'subject_id' => $subject->id,
                    'period_id' => $period->id,
                    'day' => $data['day'],
                    'semester' => $data['semester'],
                ]);
                
                $this->inserted++;
                
            } catch (\Exception $e) {
                // Likely duplicate (unique constraint)
                if (strpos($e->getMessage(), 'unique') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
                    $this->skipped++;
                } else {
                    $this->failed++;
                    $this->failures[] = [
                        'row' => $rowNumber,
                        'data' => $data,
                        'errors' => ['Database error: ' . $e->getMessage()],
                    ];
                }
            }

        } catch (\Exception $e) {
            $this->failed++;
            $this->failures[] = [
                'row' => $rowNumber,
                'data' => $data,
                'errors' => ['Error: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Check if row is empty
     */
    protected function isEmptyRow(array $data): bool
    {
        return empty($data['email']) && empty($data['class']) && empty($data['subject']);
    }

    /**
     * Find class by name like "X RPL 1" or "XI IPA 1".
     */
    protected function findClass($className)
    {
        $className = trim($className);
        
        // Extract class number (X, XI, XII -> 10, 11, 12)
        $classNumber = null;
        if (str_starts_with($className, 'XII')) {
            $classNumber = 12;
            $rest = trim(substr($className, 3));
        } elseif (str_starts_with($className, 'XI')) {
            $classNumber = 11;
            $rest = trim(substr($className, 2));
        } elseif (str_starts_with($className, 'X')) {
            $classNumber = 10;
            $rest = trim(substr($className, 1));
        }

        if (!$classNumber) {
            return null;
        }

        // The rest is major
        $major = $rest ?: null;

        return ClassModel::where('class', $classNumber)
            ->where(function ($query) use ($major) {
                if ($major) {
                    $query->where('major', $major);
                } else {
                    $query->whereNull('major');
                }
            })
            ->first();
    }

    /**
     * Get import summary.
     */
    public function getSummary()
    {
        return [
            'inserted' => $this->inserted,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
        ];
    }
}
