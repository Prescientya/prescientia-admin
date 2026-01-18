<?php

namespace App\Imports;

use App\Models\ClassModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class StudentsImport
{
    protected $successCount = 0;
    protected $failures = [];

    /**
     * Process worksheet and import students.
     * Used by StudentController to handle Excel uploads.
     */
    public function processWorksheet(Worksheet $worksheet, $confirmationPassed = false)
    {
        $rows = $worksheet->toArray(null, true, true, true); // Use readable format with column letters
        $headerRow = true;

        foreach ($rows as $index => $row) {
            // Skip header row
            if ($headerRow) {
                $headerRow = false;
                continue;
            }

            $rowNumber = $index;

            // Extract data from row using column letters (A, B, C, etc)
            $data = [
                'email' => isset($row['A']) ? trim($row['A']) : null,
                'nis' => isset($row['B']) ? trim($row['B']) : null,
                'name' => isset($row['C']) ? trim($row['C']) : null,
                'gender' => isset($row['D']) ? trim($row['D']) : null,
                'birth_date_raw' => isset($row['E']) ? $row['E'] : null, // Keep raw for date parsing
                'phone' => isset($row['F']) ? trim($row['F']) : null,
                'address' => isset($row['G']) ? trim($row['G']) : null,
                'class_number' => isset($row['H']) ? trim($row['H']) : null,
                'class_major' => isset($row['I']) ? trim($row['I']) : null,
            ];

            // Skip empty rows
            if (empty($data['email']) && empty($data['nis']) && empty($data['name'])) {
                continue;
            }

            // Convert date - use helper to parse various date formats
            $birthDate = $this->convertExcelDate($data['birth_date_raw']);
            if (!$birthDate) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Format tanggal lahir tidak valid. Gunakan format: yyyy-mm-dd'],
                ];
                continue;
            }

            // Parse class number and major - both columns required
            if (empty($data['class_number'])) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Kolom Kelas harus diisi'],
                ];
                continue;
            }

            if (empty($data['class_major'])) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Kolom Jurusan harus diisi'],
                ];
                continue;
            }

            // Validate data
            $validator = Validator::make([
                'email' => $data['email'],
                'nis' => $data['nis'],
                'name' => $data['name'],
                'gender' => $data['gender'],
                'birth_date' => $birthDate,
                'phone' => $data['phone'],
                'address' => $data['address'],
            ], [
                'email' => 'required|email|unique:users,email',
                'nis' => 'required|unique:students,nis',
                'name' => 'required|string',
                'gender' => 'required|string',
                'birth_date' => 'required|date_format:Y-m-d',
            ]);

            if ($validator->fails()) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            // Find class by class number and major
            $classNumber = is_numeric($data['class_number']) ? (int)$data['class_number'] : null;
            if (!$classNumber) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Kolom Kelas harus berisi angka (contoh: 10, 11, 12)'],
                ];
                continue;
            }

            $class = ClassModel::where('class', $classNumber)
                ->where('major', $data['class_major'])
                ->first();
            if (!$class) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ["Kelas tidak ada: Kelas {$classNumber} {$data['class_major']}"],
                ];
                continue;
            }

            // Insert in transaction per row
            try {
                DB::transaction(function () use ($data, $birthDate, $class) {
                    // Create user
                    $user = User::create([
                        'email' => $data['email'],
                        'password' => Hash::make($data['nis']),
                    ]);

                    // Create student
                    Student::create([
                        'user_id' => $user->id,
                        'nis' => $data['nis'],
                        'name' => $data['name'],
                        'gender' => $data['gender'],
                        'date_of_birth' => $birthDate,
                        'phone_number' => $data['phone'] ?? null,
                        'address' => $data['address'] ?? null,
                        'class_id' => $class->id,
                    ]);
                });

                $this->successCount++;
            } catch (\Throwable $e) {
                Log::error('Import student row failed', ['row' => $rowNumber, 'data' => $data, 'exception' => $e]);
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Terjadi kesalahan saat menyimpan data pada baris ini.'],
                ];
            }
        }
    }

    /**
     * Convert Excel date value to Y-m-d format.
     * Handles: DateTime objects, numeric values, and string formats.
     */
    private function convertExcelDate($value)
    {
        if (!$value) {
            return null;
        }

        // If it's already a DateTime object
        if ($value instanceof \DateTime) {
            try {
                return $value->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // If it's a numeric value (Excel timestamp)
        if (is_numeric($value)) {
            try {
                $excelDate = intval($value);
                if ($excelDate > 0) {
                    $dateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDate);
                    return $dateTime->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // Fall through to string parsing
            }
        }

        // If it's a string, try parsing
        $value = (string) $value;
        $value = trim($value);

        if (empty($value)) {
            return null;
        }

        // Try common formats
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d', 'd/m/y', 'd-m-y'];

        foreach ($formats as $format) {
            try {
                return \Carbon\Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Exception $e) {
                // Try next format
            }
        }

        return null;
    }

    /**
     * Prescan worksheet to detect missing classes before import
     * Returns array with missingClasses and validation errors
     */
    public function prescanWorksheet(Worksheet $worksheet)
    {
        $rows = $worksheet->toArray(null, true, true, true);
        $headerRow = true;
        $missingClasses = []; // These will be treated as errors for display
        $validationErrors = [];
        $classMap = []; // Track found classes to avoid duplicates in report

        foreach ($rows as $index => $row) {
            if ($headerRow) {
                $headerRow = false;
                continue;
            }

            $rowNumber = $index;

            $data = [
                'email' => isset($row['A']) ? trim($row['A']) : null,
                'nis' => isset($row['B']) ? trim($row['B']) : null,
                'name' => isset($row['C']) ? trim($row['C']) : null,
                'gender' => isset($row['D']) ? trim($row['D']) : null,
                'birth_date_raw' => isset($row['E']) ? $row['E'] : null,
                'phone' => isset($row['F']) ? trim($row['F']) : null,
                'address' => isset($row['G']) ? trim($row['G']) : null,
                'class_number' => isset($row['H']) ? trim($row['H']) : null,
                'class_major' => isset($row['I']) ? trim($row['I']) : null,
            ];

            // Skip empty rows
            if (empty($data['email']) && empty($data['nis']) && empty($data['name'])) {
                continue;
            }

            // Validate date format
            $birthDate = $this->convertExcelDate($data['birth_date_raw']);
            if (!$birthDate) {
                $validationErrors[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Format tanggal lahir tidak valid. Gunakan format: yyyy-mm-dd'],
                ];
                continue;
            }

            // Check required class fields
            if (empty($data['class_number']) || empty($data['class_major'])) {
                $validationErrors[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Kolom Kelas dan Jurusan harus diisi'],
                ];
                continue;
            }

            // Validate class number is numeric
            $classNumber = is_numeric($data['class_number']) ? (int)$data['class_number'] : null;
            if (!$classNumber) {
                $validationErrors[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Kolom Kelas harus berisi angka (contoh: 10, 11, 12)'],
                ];
                continue;
            }

            // Validate email format
            $validator = Validator::make(['email' => $data['email']], ['email' => 'required|email']);
            if ($validator->fails()) {
                $validationErrors[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Format email tidak valid'],
                ];
                continue;
            }

            // Check if class exists
            $classExists = ClassModel::where('class', $classNumber)
                ->where('major', $data['class_major'])
                ->exists();

            if (!$classExists) {
                $classKey = "{$classNumber}|{$data['class_major']}";
                if (!isset($classMap[$classKey])) {
                    // Treat missing class as error for display
                    $missingClasses[] = [
                        'row' => $rowNumber,
                        'data' => $data,
                        'errors' => ["Kelas tidak ada: Kelas {$classNumber} {$data['class_major']}"],
                    ];
                    $classMap[$classKey] = true;
                }
            }
        }

        return [
            'missingClasses' => $missingClasses,
            'validationErrors' => $validationErrors,
            'totalRows' => count($rows) - 1, // Exclude header
        ];
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailures(): array
    {
        return $this->failures;
    }
}
