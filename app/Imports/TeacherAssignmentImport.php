<?php

namespace App\Imports;

use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\TeachedClass;
use App\Helpers\AttendanceHelper;
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

        // VALIDATION: Teacher harus memiliki mata pelajaran
        $teacherSubjects = $teacher->subjects()->get();
        if ($teacherSubjects->isEmpty()) {
            return [
                'valid' => false,
                'errors' => [
                    "❌ Guru '{$teacher->name}' TIDAK MEMILIKI mata pelajaran yang terdaftar.",
                    "📝 Langkah perbaikan: ",
                    "1. Buka menu 'Data Guru'",
                    "2. Klik guru '{$teacher->name}'",
                    "3. Tambahkan mata pelajaran yang akan diajar",
                    "4. Coba import ulang"
                ],
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
        $errorDetails = [];
        
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
                // Get available classes for better error message
                $availableClasses = ClassModel::select('class', 'major')
                    ->orderBy('class')
                    ->orderBy('major')
                    ->get();
                
                $classList = $availableClasses->map(function($c) {
                    return "Kelas {$c->class} " . ($c->major ? "({$c->major})" : "(Umum)");
                })->unique()->join(", ");
                
                return [
                    'valid' => false,
                    'errors' => [
                        "❌ Kelas {$classInt} {$majorName} TIDAK DITEMUKAN di sistem.",
                        "Kelas+Jurusan yang tersedia: {$classList}",
                        "Mohon buat kelas tersebut terlebih dahulu di menu 'Data Kelas'."
                    ],
                ];
            }

            // VALIDATION: Check which subjects in this class don't have a teacher yet
            // Only assign guru untuk mapel yang belum ada guru di kelas ini
            $classRequiredSubjects = \App\Models\Subject::forMajor($classModel->major ?? 'Umum')
                ->active()
                ->get();
            
            if ($classRequiredSubjects->isEmpty()) {
                return [
                    'valid' => false,
                    'errors' => [
                        "❌ Kelas {$classInt} {$majorName} tidak memiliki mapel yang didefinisikan.",
                        "Pastikan kelas ini sudah dikonfigurasi dengan mapel yang tepat di menu 'Data Kelas'."
                    ],
                ];
            }

            // Get subjects that already have teachers assigned in this class
            $assignedSubjectIds = TeachedClass::where('class_id', $classModel->id)
                ->where('semester', AttendanceHelper::getCurrentSemester())
                ->whereNotNull('subject_id')
                ->pluck('subject_id')
                ->toArray();

            // Find VACANT subjects (not yet assigned) in this class
            $classSubjectIds = $classRequiredSubjects->pluck('id')->toArray();
            $vacantSubjectIds = array_diff($classSubjectIds, $assignedSubjectIds);

            // Get teacher's subjects that match vacant subjects
            $teacherSubjectIds = $teacherSubjects->pluck('id')->toArray();
            $assignableSubjectIds = array_intersect($teacherSubjectIds, $vacantSubjectIds);

            // If guru tidak punya subject apapun yang vacant di kelas ini, reject
            if (empty($assignableSubjectIds)) {
                $teacherSubjectNames = $teacherSubjects->pluck('name')->toArray();
                $vacantSubjectNames = $classRequiredSubjects->whereIn('id', $vacantSubjectIds)->pluck('name')->toArray();
                $assignedSubjectNames = $classRequiredSubjects->whereIn('id', $assignedSubjectIds)->pluck('name')->toArray();
                
                return [
                    'valid' => false,
                    'errors' => [
                        "⚠️ VALIDASI SUBJECT GAGAL:",
                        "Guru '{$teacher->name}' tidak memiliki mapel yang BELUM ADA guru di Kelas {$classInt} {$majorName}.",
                        "",
                        "📚 Mata pelajaran yang Guru ajarkan: " . implode(', ', $teacherSubjectNames),
                        "",
                        "📚 Mapel yang SUDAH ada guru di kelas: " . implode(', ', $assignedSubjectNames),
                        "",
                        "📚 Mapel yang BELUM ada guru di kelas: " . implode(', ', $vacantSubjectNames),
                        "",
                        "💡 Guru '{$teacher->name}' bisa mengajar mapel: " . implode(', ', $teacherSubjectNames),
                        "   Tapi semua mapel itu SUDAH ada guru lain di kelas ini.",
                        "",
                        "💡 Solusi: Gunakan guru lain yang bisa mengajar mapel yang masih vacant.",
                    ],
                ];
            }

            $assignments[] = [
                'class_id' => $classModel->id,
                'class_num' => $classInt,
                'major' => $majorName,
                'subject_ids' => $assignableSubjectIds, // Only assignable subjects (not yet assigned)
            ];
        }

        return [
            'valid' => true,
            'teacher' => $teacher,
            'assignments' => $assignments,
            'teacher_subjects' => $teacherSubjects,
        ];
    }

    /**
     * Import teacher assignment to classes with proper subject handling
     * Creates separate TeachedClass records for EACH subject the teacher teaches
     * ONLY for subjects that don't already have a teacher in the class
     */
    private function importAssignment(Teacher $teacher, array $assignments): void
    {
        DB::transaction(function () use ($teacher, $assignments) {
            // Get all subject names that this teacher teaches (for departments field)
            $teacherSubjectNames = $teacher->subjects()->pluck('name')->toArray();
            $departmentsJson = json_encode($teacherSubjectNames, JSON_UNESCAPED_UNICODE);
            
            foreach ($assignments as $assignment) {
                // Use current semester based on current date
                $semester = AttendanceHelper::getCurrentSemester();
                
                // Get ASSIGNABLE subject IDs (subjects that are vacant in this class)
                $subjectIds = $assignment['subject_ids'] ?? [];
                
                if (empty($subjectIds)) {
                    Log::warning('No assignable subjects for teacher, skipping assignment', [
                        'teacher_id' => $teacher->id,
                        'teacher_name' => $teacher->name,
                        'class_id' => $assignment['class_id'],
                    ]);
                    return;
                }

                // Create separate TeachedClass for EACH assignable subject
                foreach ($subjectIds as $subjectId) {
                    // Final check: Make sure subject is not already assigned
                    $existing = TeachedClass::where('teacher_id', $teacher->id)
                        ->where('class_id', $assignment['class_id'])
                        ->where('subject_id', $subjectId)
                        ->where('semester', $semester)
                        ->first();

                    if ($existing) {
                        Log::info('Teacher-Subject-Class already assigned, skipping', [
                            'teacher_id' => $teacher->id,
                            'subject_id' => $subjectId,
                            'class_id' => $assignment['class_id'],
                        ]);
                        continue;
                    }

                    // Create new TeachedClass with subject_id and departments
                    $teachedClass = TeachedClass::create([
                        'teacher_id' => $teacher->id,
                        'class_id' => $assignment['class_id'],
                        'subject_id' => $subjectId, // Only for vacant subjects
                        'semester' => $semester,
                        'departments' => $departmentsJson, // Store teacher's subject names for UI reference
                    ]);

                    Log::info('Teacher assigned to class for specific subject', [
                        'teacher_id' => $teacher->id,
                        'teacher_name' => $teacher->name,
                        'class_id' => $assignment['class_id'],
                        'class_num' => $assignment['class_num'],
                        'major' => $assignment['major'],
                        'subject_id' => $subjectId,
                        'teached_class_id' => $teachedClass->id,
                        'departments' => $teacherSubjectNames,
                    ]);
                }
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
