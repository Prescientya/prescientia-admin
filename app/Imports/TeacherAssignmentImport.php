<?php

namespace App\Imports;

use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\TeachedClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Import Guru Mengajar (Teacher Class Assignment)
 * 
 * Purpose: Assign existing teachers to specific classes
 * Does NOT create teacher accounts
 * 
 * Excel Format (STRICT):
 * - Column A: teacher_name (must exist in database)
 * - Column B: class (comma-separated: 10,11,12)
 * - Column C: major (comma-separated: RPL,RPL,RPL)
 * 
 * VALIDATION RULES:
 * - Count of class values MUST equal count of major values
 * - Teacher must exist in database
 * - Class+Major combination must exist
 * - Invalid rows are NOT imported (transaction safety)
 */
class TeacherAssignmentImport
{
    private $worksheet;
    private $successCount = 0;
    private $failures = [];

    public function __construct(Worksheet $worksheet)
    {
        $this->worksheet = $worksheet;
    }

    /**
     * Process the import and return results
     * @return array [successCount, failures]
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
            
            // Extract data
            $data = [
                'teacher_name' => isset($row['A']) ? trim($row['A']) : null,
                'class_raw' => isset($row['B']) ? trim($row['B']) : null,
                'major_raw' => isset($row['C']) ? trim($row['C']) : null,
            ];

            // Skip completely empty rows
            if ($this->isEmptyRow($data)) {
                continue;
            }

            // Validate and process
            $validationResult = $this->validateRow($data, $rowNumber);
            
            if (!$validationResult['valid']) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => $validationResult['errors'],
                ];
                continue;
            }

            // Import the assignment
            try {
                $this->importAssignment($validationResult['teacher'], $validationResult['assignments']);
                $this->successCount++;
                
            } catch (\Throwable $e) {
                Log::error('Failed to import teacher assignment', [
                    'row' => $rowNumber,
                    'data' => $data,
                    'error' => $e->getMessage(),
                ]);
                
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Gagal menyimpan data: ' . $e->getMessage()],
                ];
            }
        }

        return [$this->successCount, $this->failures];
    }

    /**
     * Validate row data
     * Returns array with 'valid' flag and either 'errors' or 'teacher' + 'assignments'
     */
    private function validateRow(array $data, int $rowNumber): array
    {
        $errors = [];

        // Required fields
        if (empty($data['teacher_name'])) {
            $errors[] = 'Nama guru wajib diisi';
        }
        if (empty($data['class_raw'])) {
            $errors[] = 'Kelas wajib diisi';
        }
        if (empty($data['major_raw'])) {
            $errors[] = 'Jurusan wajib diisi';
        }

        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors];
        }

        // Find teacher (case-insensitive)
        $teacher = Teacher::whereRaw('LOWER(name) = ?', [mb_strtolower($data['teacher_name'])])
            ->first();
        
        if (!$teacher) {
            // Try partial match
            $teacher = Teacher::where('name', 'ilike', '%' . $data['teacher_name'] . '%')->first();
        }

        if (!$teacher) {
            return [
                'valid' => false,
                'errors' => ["Guru '{$data['teacher_name']}' tidak ditemukan di sistem. Pastikan guru sudah terdaftar terlebih dahulu."],
            ];
        }

        // Parse classes and majors
        $classes = array_filter(array_map('trim', explode(',', $data['class_raw'])));
        $majors = array_filter(array_map('trim', explode(',', $data['major_raw'])));

        // VALIDATION: Count must match
        if (count($classes) !== count($majors)) {
            return [
                'valid' => false,
                'errors' => [
                    "❌ VALIDASI GAGAL: Jumlah kelas (" . count($classes) . ") tidak sama dengan jumlah jurusan (" . count($majors) . ").",
                    "Contoh BENAR: Kelas '10,11,12' dengan Jurusan 'RPL,RPL,RPL' (3 = 3).",
                    "Data Anda: Kelas '{$data['class_raw']}' dengan Jurusan '{$data['major_raw']}'.",
                ],
            ];
        }

        // Validate each class+major combination
        $assignments = [];
        foreach ($classes as $idx => $classNum) {
            $classNum = trim($classNum);
            $majorName = trim($majors[$idx]);

            // Validate class number is numeric
            if (!is_numeric($classNum)) {
                return [
                    'valid' => false,
                    'errors' => ["Kelas '{$classNum}' harus berupa angka (contoh: 10, 11, 12)."],
                ];
            }

            $classInt = (int) $classNum;

            // Find class model
            $classModel = ClassModel::where('class', $classInt)
                ->where('major', 'ilike', $majorName)
                ->first();

            if (!$classModel) {
                // Try without major filter (for general classes)
                $classModel = ClassModel::where('class', $classInt)
                    ->whereNull('major')
                    ->first();
            }

            if (!$classModel) {
                return [
                    'valid' => false,
                    'errors' => ["Kelas {$classInt} {$majorName} tidak ditemukan di sistem. Pastikan kelas sudah dibuat terlebih dahulu."],
                ];
            }

            $assignments[] = [
                'class_id' => $classModel->id,
                'class_num' => $classInt,
                'major' => $majorName,
            ];
        }

        return [
            'valid' => true,
            'teacher' => $teacher,
            'assignments' => $assignments,
        ];
    }

    /**
     * Import teacher assignment to classes
     */
    private function importAssignment(Teacher $teacher, array $assignments): void
    {
        DB::transaction(function () use ($teacher, $assignments) {
            foreach ($assignments as $assignment) {
                // Get or create TeachedClass
                // Use current semester (you can make this configurable)
                $semester = config('prescientia.current_semester', 1);
                
                $teachedClass = TeachedClass::updateOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'class_id' => $assignment['class_id'],
                        'semester' => $semester,
                    ],
                    [
                        'departments' => [], // Empty by default, can be updated later
                    ]
                );

                // Sync teacher's subjects with this teached class
                $subjectIds = $teacher->subjects()->pluck('subjects.id')->toArray();
                if (!empty($subjectIds)) {
                    $teachedClass->subjects()->sync($subjectIds);
                }

                Log::info('Teacher assigned to class', [
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->name,
                    'class_id' => $assignment['class_id'],
                    'class_num' => $assignment['class_num'],
                    'major' => $assignment['major'],
                ]);
            }
        });
    }

    /**
     * Check if row is completely empty
     */
    private function isEmptyRow(array $data): bool
    {
        return empty($data['teacher_name']) 
            && empty($data['class_raw']) 
            && empty($data['major_raw']);
    }
}
