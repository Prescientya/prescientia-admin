<?php

namespace App\Imports;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Import Akun Guru (Teacher Account Only)
 * 
 * Purpose: Create teacher accounts with subjects
 * Does NOT handle class assignments
 * 
 * Excel Format (STRICT):
 * - Column A: email (required, unique)
 * - Column B: nip (required, unique)
 * - Column C: name (required)
 * - Column D: gender (required: L or P)
 * - Column E: birth_date (required: yyyy-mm-dd)
 * - Column F: phone (optional)
 * - Column G: address (optional)
 * - Column H: subjects (optional, comma-separated for multiple)
 */
class TeachersImport
{
    private $worksheet;
    private $successCount = 0;
    private $failures = [];

    public function __construct($worksheet)
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
            
            // Extract data (ACCOUNT COLUMNS ONLY)
            $data = [
                'email' => isset($row['A']) ? trim($row['A']) : null,
                'nip' => isset($row['B']) ? trim($row['B']) : null,
                'name' => isset($row['C']) ? trim($row['C']) : null,
                'gender' => isset($row['D']) ? strtoupper(trim($row['D'])) : null,
                'birth_date_raw' => isset($row['E']) ? $row['E'] : null,
                'phone' => isset($row['F']) ? trim($row['F']) : null,
                'address' => isset($row['G']) ? trim($row['G']) : null,
                'subjects_raw' => isset($row['H']) ? trim($row['H']) : null,
            ];

            // Skip completely empty rows
            if ($this->isEmptyRow($data)) {
                continue;
            }

            // Convert Excel date
            $birthDate = $this->convertExcelDate($data['birth_date_raw']);
            if (!$birthDate) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Format tanggal lahir tidak valid. Gunakan format: yyyy-mm-dd'],
                ];
                continue;
            }

            // Validate data
            $validator = Validator::make([
                'email' => $data['email'],
                'nip' => $data['nip'],
                'name' => $data['name'],
                'gender' => $data['gender'],
                'birth_date' => $birthDate,
            ], [
                'email' => 'required|email|unique:users,email',
                'nip' => 'required|unique:teachers,nip',
                'name' => 'required|string|max:255',
                'gender' => 'required|in:L,P',
                'birth_date' => 'required|date',
            ], [
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah terdaftar',
                'nip.required' => 'NIP wajib diisi',
                'nip.unique' => 'NIP sudah terdaftar',
                'name.required' => 'Nama wajib diisi',
                'gender.required' => 'Jenis kelamin wajib diisi',
                'gender.in' => 'Jenis kelamin harus L atau P',
                'birth_date.required' => 'Tanggal lahir wajib diisi',
                'birth_date.date' => 'Format tanggal lahir tidak valid',
            ]);

            if ($validator->fails()) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            // Parse subjects (FIX: Create proper relationships)
            $subjectIds = $this->parseSubjects($data['subjects_raw']);
            $subjectNames = $this->getSubjectNames($subjectIds); // Get actual subject names

            // Import the teacher
            try {
                DB::transaction(function () use ($data, $birthDate, $subjectIds, $subjectNames) {
                    // Create user account
                    $user = User::create([
                        'email' => $data['email'],
                        'password' => Hash::make($data['nip']), // Default password = NIP
                    ]);
                    
                    // Create teacher record
                    $teacher = Teacher::create([
                        'user_id' => $user->id,
                        'nip' => $data['nip'],
                        'name' => $data['name'],
                        'gender' => $data['gender'],
                        'date_of_birth' => $birthDate,
                        'phone_number' => $data['phone'] ?? null,
                        'address' => $data['address'] ?? null,
                        'department' => !empty($subjectNames) ? $subjectNames : null, // Store subject names as array
                    ]);
                    
                    // FIX: Properly attach subjects to teacher (many-to-many)
                    if (!empty($subjectIds)) {
                        $teacher->subjects()->sync($subjectIds);
                        
                        Log::info('Teacher subjects attached', [
                            'teacher_id' => $teacher->id,
                            'teacher_name' => $teacher->name,
                            'subject_ids' => $subjectIds,
                            'subject_names' => $subjectNames,
                        ]);
                    }
                });

                $this->successCount++;
                
            } catch (\Throwable $e) {
                Log::error('Failed to import teacher', [
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
     * Get subject names from subject IDs
     */
    private function getSubjectNames(array $subjectIds): array
    {
        if (empty($subjectIds)) {
            return [];
        }

        return Subject::whereIn('id', $subjectIds)
            ->pluck('name')
            ->toArray();
    }

    /**
     * Parse comma-separated subjects and return subject IDs
     * Supports multiple subjects per teacher
     */
    private function parseSubjects(?string $subjectsRaw): array
    {
        if (empty($subjectsRaw)) {
            return [];
        }

        $subjectNames = array_filter(array_map('trim', explode(',', $subjectsRaw)));
        $subjectIds = [];

        foreach ($subjectNames as $subjectName) {
            if (empty($subjectName)) {
                continue;
            }

            // Find subject (case-insensitive)
            $subject = Subject::whereRaw('LOWER(name) = ?', [mb_strtolower($subjectName)])->first();
            
            // Try partial match if exact match fails
            if (!$subject) {
                $subject = Subject::where('name', 'ilike', '%' . $subjectName . '%')->first();
            }

            if ($subject) {
                $subjectIds[] = $subject->id;
            } else {
                Log::warning('Subject not found during import', ['subject_name' => $subjectName]);
            }
        }

        return array_unique($subjectIds);
    }

    /**
     * Convert Excel date to Y-m-d format
     */
    private function convertExcelDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // If already a string in Y-m-d format
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        // If numeric (Excel serial date)
        if (is_numeric($value)) {
            try {
                $date = Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Try parsing various formats
        try {
            $date = \Carbon\Carbon::parse($value);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if row is completely empty
     */
    private function isEmptyRow(array $data): bool
    {
        return empty($data['email']) 
            && empty($data['nip']) 
            && empty($data['name']);
    }
}
