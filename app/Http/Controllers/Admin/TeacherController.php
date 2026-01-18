<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Models\TeacherClassRole;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\TeacherTemplateExport;
use App\Imports\TeachersImport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teachers = Teacher::with('user')->paginate(10);
        $totalTeachers = Teacher::count();
        return view('admin.teachers.index', compact('teachers', 'totalTeachers'));
    }

    /**
     * Show form for bulk import via Excel (redirects to index modal).
     */
    public function importForm()
    {
        return redirect()->route('admin.teachers.index', ['show_import' => 1]);
    }

    /**
     * Handle Excel upload and import teachers (ACCOUNT ONLY).
     * 
     * SIMPLIFIED: Only creates teacher accounts with subjects.
     * Does NOT assign classes - that's handled by separate import.
     * 
     * Excel columns (STRICT):
     * A: email, B: nip, C: name, D: gender, E: birth_date, 
     * F: phone, G: address, H: subjects (comma-separated)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Use new simplified import class (account only)
            $importer = new TeachersImport($worksheet);
            [$success, $failures] = $importer->process();
            
            // If there are failures, show error details
            if (!empty($failures)) {
                return view('admin.teachers.import_error_details', [
                    'validationErrors' => $failures,
                    'filePath' => null,
                    'totalRows' => $success + count($failures),
                    'errorCount' => count($failures),
                    'successCount' => $success,
                ]);
            }
            
            // Success - redirect with message
            return redirect()->route('admin.teachers.index')
                ->with('success', "✅ Import berhasil: {$success} guru ditambahkan");
                
        } catch (\Throwable $e) {
            Log::error('Import teachers failed', ['exception' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Handle confirmation of missing subject and class creation.
     * Admin chooses to create missing data or cancel import.
     * 
     * @deprecated This method is deprecated and will be removed.
     * The new import process (TeachersImport) handles account creation only.
     * Class assignments should use separate import on teached-classes page.
     */
    public function confirmAndCreateDependencies(Request $request)
    {
        return redirect()->route('admin.teachers.index')
            ->with('error', 'Fitur ini sudah tidak digunakan. Silakan gunakan import akun guru yang baru.');
    }

    /**
     * Comprehensive prescan to detect both validation errors and missing dependencies.
     * Returns array with validation errors and missing data for detailed error reporting.
     */
    private function comprehensivePrescan($worksheet): array
    {
        $rows = $worksheet->toArray(null, true, true, true);
        $validationErrors = [];
        $missingSubjects = [];
        $missingClasses = [];
        $processedRows = 0;
        $headerRow = true;

        foreach ($rows as $index => $row) {
            if ($headerRow) {
                $headerRow = false;
                continue;
            }

            $rowNumber = $index;
            $processedRows++;
            $rowErrors = [];

            // Extract data
            $data = [
                'email' => isset($row['A']) ? trim($row['A']) : null,
                'nip' => isset($row['B']) ? trim($row['B']) : null,
                'name' => isset($row['C']) ? trim($row['C']) : null,
                'gender' => isset($row['D']) ? trim($row['D']) : null,
                'date_of_birth_raw' => isset($row['E']) ? $row['E'] : null,
                'phone_number' => isset($row['F']) ? trim($row['F']) : null,
                'address' => isset($row['G']) ? trim($row['G']) : null,
                'subjects_raw' => isset($row['H']) ? trim($row['H']) : null,
                'classes_raw' => isset($row['I']) ? trim($row['I']) : null,
                'majors_raw' => isset($row['K']) ? trim($row['K']) : null,
                'departments_raw' => isset($row['L']) ? trim($row['L']) : null,
            ];

            // Skip completely empty rows
            if (empty($data['email']) && empty($data['nip']) && empty($data['name'])) {
                continue;
            }

            // Validation: Required fields
            if (empty($data['email'])) {
                $rowErrors[] = 'Email tidak boleh kosong';
            }
            if (empty($data['nip'])) {
                $rowErrors[] = 'NIP tidak boleh kosong';
            }
            if (empty($data['name'])) {
                $rowErrors[] = 'Nama tidak boleh kosong';
            }

            // Validation: Email format
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'Format email tidak valid: ' . $data['email'];
            }

            // Validation: Gender
            if (!empty($data['gender']) && !in_array(strtoupper($data['gender']), ['L', 'P'])) {
                $rowErrors[] = 'Jenis kelamin harus "L" atau "P", ditemukan: ' . $data['gender'];
            }

            // Validation: Date of birth format
            if (!empty($data['date_of_birth_raw'])) {
                $dateOfBirth = $this->convertExcelDate($data['date_of_birth_raw']);
                if (!$dateOfBirth) {
                    $rowErrors[] = 'Format tanggal lahir tidak valid. Gunakan format: yyyy-mm-dd. Ditemukan: ' . $data['date_of_birth_raw'];
                }
            } else {
                $rowErrors[] = 'Tanggal lahir tidak boleh kosong';
            }

            // If validation errors exist for this row, add to list
            if (!empty($rowErrors)) {
                $validationErrors[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => $rowErrors,
                ];
                continue;  // Skip dependency check for invalid rows
            }

            // Check for missing subjects
            if (!empty($data['subjects_raw'])) {
                $subjectNames = array_filter(array_map('trim', explode(',', $data['subjects_raw'])));
                foreach ($subjectNames as $subjectName) {
                    if (empty($subjectName)) continue;
                    
                    $exists = Subject::whereRaw('LOWER(name) = ?', [mb_strtolower($subjectName)])
                        ->orWhere('name', 'ilike', '%' . $subjectName . '%')
                        ->exists();
                    
                    if (!$exists) {
                        $key = mb_strtolower($subjectName);
                        if (!isset($missingSubjects[$key])) {
                            $missingSubjects[$key] = [
                                'name' => $subjectName,
                                'count' => 0,
                            ];
                        }
                        $missingSubjects[$key]['count']++;
                    }
                }
            }

            // Check for missing classes - match class numbers with majors by position
            // Kolom I = Kelas (classes_raw), Kolom K = Jurusan (majors_raw)
            $classes = array_filter(array_map('trim', explode(',', $data['classes_raw'] ?? '')));
            $majors = array_filter(array_map('trim', explode(',', $data['majors_raw'] ?? '')));
            
            if (!empty($classes)) {
                foreach ($classes as $idx => $classNum) {
                    if (empty($classNum) || !is_numeric(trim($classNum))) continue;
                    
                    $classInt = (int) trim($classNum);
                    $majorName = isset($majors[$idx]) ? trim($majors[$idx]) : null;
                    
                    // Check if class+major combination exists
                    $query = ClassModel::where('class', $classInt);
                    if (!empty($majorName)) {
                        $query->where('major', 'ilike', $majorName);
                    }
                    $classExists = $query->exists();
                    
                    if (!$classExists) {
                        $key = $classInt . '_' . mb_strtolower($majorName ?? 'null');
                        if (!isset($missingClasses[$key])) {
                            $missingClasses[$key] = [
                                'class' => $classInt,
                                'major' => $majorName,
                                'count' => 0,
                            ];
                        }
                        $missingClasses[$key]['count']++;
                    }
                }
            }
        }

        return [
            'validationErrors' => $validationErrors,
            'missingSubjects' => array_values($missingSubjects),
            'missingClasses' => array_values($missingClasses),
            'totalRows' => $processedRows,
        ];
    }

    /**
     * Prescan worksheet to detect missing subjects and classes.
     * Returns array of missing data without importing anything.
     * Supports multi-major subjects and multiple classes.
     */
    private function prescanMissingData($worksheet): array
    {
        $rows = $worksheet->toArray(null, true, true, true);
        $missingSubjects = [];
        $missingClasses = [];
        $processedRows = 0;
        $headerRow = true;

        foreach ($rows as $index => $row) {
            if ($headerRow) {
                $headerRow = false;
                continue;
            }

            $processedRows++;

            // Extract data
            $subjectsRaw = isset($row['H']) ? trim($row['H']) : null;
            $classesRaw = isset($row['I']) ? trim($row['I']) : null;

            // Prescan subjects
            if (!empty($subjectsRaw)) {
                $subjectNames = array_filter(array_map('trim', explode(',', $subjectsRaw)));
                foreach ($subjectNames as $subjectName) {
                    if (empty($subjectName)) continue;
                    
                    // Check if subject exists (case-insensitive)
                    $exists = Subject::whereRaw('LOWER(name) = ?', [mb_strtolower($subjectName)])
                        ->orWhere('name', 'ilike', '%' . $subjectName . '%')
                        ->exists();
                    
                    if (!$exists) {
                        $key = mb_strtolower($subjectName);
                        if (!isset($missingSubjects[$key])) {
                            $missingSubjects[$key] = [
                                'name' => $subjectName,
                                'count' => 0,
                            ];
                        }
                        $missingSubjects[$key]['count']++;
                    }
                }
            }

            // Prescan classes
            $classRows = isset($row['I']) ? trim($row['I']) : null;
            $majorRows = isset($row['J']) ? trim($row['J']) : null;
            
            $classes = array_filter(array_map('trim', explode(',', $classRows ?? '')));
            $majors = array_filter(array_map('trim', explode(',', $majorRows ?? '')));
            
            if (!empty($classes)) {
                foreach ($classes as $idx => $classNum) {
                    if (empty($classNum) || !is_numeric(trim($classNum))) continue;
                    
                    $classInt = (int) trim($classNum);
                    $majorName = isset($majors[$idx]) ? trim($majors[$idx]) : null;
                    
                    // Check if class+major combination exists
                    $query = ClassModel::where('class', $classInt);
                    if (!empty($majorName)) {
                        $query->where('major', 'ilike', $majorName);
                    }
                    $classExists = $query->exists();
                    
                    if (!$classExists) {
                        $key = $classInt . '_' . mb_strtolower($majorName ?? 'null');
                        if (!isset($missingClasses[$key])) {
                            $missingClasses[$key] = [
                                'class' => $classInt,
                                'major' => $majorName,
                                'count' => 0,
                            ];
                        }
                        $missingClasses[$key]['count']++;
                    }
                }
            }
        }

        return [
            'subjects' => array_values($missingSubjects),
            'classes' => array_values($missingClasses),
            'totalRows' => $processedRows,
        ];
    }

    /**
     * Create missing subjects and classes with transaction safety.
     * Supports multi-major subjects as per domain requirements.
     * Prevents duplicates with proper validation.
     */
    private function createMissingDependencies(array $subjectNames, array $classNames): void
    {
        DB::transaction(function () use ($subjectNames, $classNames) {
            // Create missing subjects
            foreach ($subjectNames as $subjectName) {
                if (empty($subjectName)) continue;

                // Check again in case of race condition
                $exists = Subject::whereRaw('LOWER(name) = ?', [mb_strtolower($subjectName)])->first();
                
                if (!$exists) {
                    // Extract code from subject name (first 3-4 chars)
                    $code = strtoupper(substr(str_replace(' ', '', $subjectName), 0, 4));
                    
                    // Ensure code is unique
                    $baseCode = $code;
                    $counter = 1;
                    while (Subject::where('code', $code)->exists()) {
                        $code = $baseCode . $counter;
                        $counter++;
                    }

                    // Create subject WITHOUT major (will be multi-major capable)
                    // Note: admin can later assign majors in admin panel
                    try {
                        Subject::create([
                            'name' => $subjectName,
                            'code' => $code,
                            'major' => null, // Allow multi-major by using null
                            'is_active' => true,
                        ]);
                        Log::info('Created missing subject', ['name' => $subjectName, 'code' => $code]);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to create subject', ['name' => $subjectName, 'error' => $e->getMessage()]);
                    }
                }
            }

            // Create missing classes - format: [['class' => 10, 'major' => 'RPL'], ...]
            foreach ($classNames as $item) {
                if (empty($item['class'])) continue;

                // Check if class already exists
                $query = ClassModel::where('class', $item['class']);
                if (!empty($item['major'])) {
                    $query->where('major', 'ilike', $item['major']);
                }
                $classExists = $query->exists();

                if (!$classExists) {
                    try {
                        ClassModel::create([
                            'class' => $item['class'],
                            'major' => $item['major'] ?? null,
                        ]);
                        Log::info('Created missing class', [
                            'class' => $item['class'],
                            'major' => $item['major'] ?? null,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to create class', ['class' => $item['class'], 'major' => $item['major'], 'error' => $e->getMessage()]);
                    }
                }
            }
        });
    }

    /**
     * Process worksheet and import teachers. Returns [successCount, failures]
     * Enhanced to handle confirmation flow and skip rows with missing data if not auto-created.
     */
    private function processImportSpreadsheet($worksheet, $confirmationPassed = false)
    {
        $rows = $worksheet->toArray(null, true, true, true); // Use readable format with header row aware
        $success = 0;
        $failures = [];
        $headerRow = true;

        foreach ($rows as $index => $row) {
            // Skip header row
            if ($headerRow) {
                $headerRow = false;
                continue;
            }

            $rowNumber = $index;
            
            // Extract data from row - using column letters for reliability
            $data = [
                'email' => isset($row['A']) ? trim($row['A']) : null,
                'nip' => isset($row['B']) ? trim($row['B']) : null,
                'name' => isset($row['C']) ? trim($row['C']) : null,
                'gender' => isset($row['D']) ? trim($row['D']) : null,
                'date_of_birth_raw' => isset($row['E']) ? $row['E'] : null, // Keep as-is, don't trim yet
                'phone_number' => isset($row['F']) ? trim($row['F']) : null,
                'address' => isset($row['G']) ? trim($row['G']) : null,
                'subjects_raw' => isset($row['H']) ? trim($row['H']) : null,
                'classes_raw' => isset($row['I']) ? trim($row['I']) : null,
                'semester_raw' => isset($row['J']) ? trim($row['J']) : null,
                'majors_raw' => isset($row['K']) ? trim($row['K']) : null,
                'departments_raw' => isset($row['L']) ? trim($row['L']) : null,
            ];

            // Skip empty rows
            if (empty($data['email']) && empty($data['nip']) && empty($data['name'])) {
                continue;
            }

            // Convert date - use Carbon to parse the date directly
            $dateOfBirth = $this->convertExcelDate($data['date_of_birth_raw']);
            if (!$dateOfBirth) {
                $failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Format tanggal lahir tidak valid. Gunakan format: yyyy-mm-dd'],
                ];
                continue;
            }

            // Now validate with converted date
            $validator = \Illuminate\Support\Facades\Validator::make([
                'email' => $data['email'],
                'nip' => $data['nip'],
                'name' => $data['name'],
                'gender' => $data['gender'],
                'date_of_birth' => $dateOfBirth,
                'phone_number' => $data['phone_number'],
                'address' => $data['address'],
            ], [
                'email' => 'required|email|unique:users,email',
                'nip' => 'required|unique:teachers,nip',
                'name' => 'required|string',
                'gender' => 'required|string',
                'date_of_birth' => 'required|date_format:Y-m-d',
                'phone_number' => 'nullable|string',
                'address' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            // Parse subjects (comma-separated) and resolve to IDs
            // If subject doesn't exist and confirmation didn't pass, skip this row
            $subjectIds = [];
            if (!empty($data['subjects_raw'])) {
                $parts = array_filter(array_map('trim', explode(',', $data['subjects_raw'])));
                $missingSubjects = [];
                foreach ($parts as $subName) {
                    if ($subName === '') continue;
                    $subject = Subject::whereRaw('LOWER(name) = ?', [mb_strtolower($subName)])->first();
                    if (!$subject) {
                        // try partial match
                        $subject = Subject::where('name', 'ilike', '%' . $subName . '%')->first();
                    }
                    if ($subject) {
                        $subjectIds[] = $subject->id;
                    } else {
                        $missingSubjects[] = $subName;
                    }
                }
                if (!empty($missingSubjects)) {
                    // After confirmation, missing subjects should have been created
                    // If they still don't exist, this is a data integrity error
                    $failures[] = [
                        'row' => $rowNumber,
                        'data' => $data,
                        'errors' => array_map(function($s){ 
                            return "Mata pelajaran '{$s}' tidak ditemukan. Data mungkin gagal dibuat atau sudah dihapus."; 
                        }, $missingSubjects),
                    ];
                    continue;
                }
            }

            // Parse classes taught (comma-separated) and resolve to IDs
            // Parse classes and majors (comma-separated, paired by position)
            // Format: Kelas (Kolom I): "10, 11, 12" Jurusan (Kolom K): "RPL, RPL, RPL"
            // Artinya: Guru mengajar Kelas 10 RPL, 11 RPL, dan 12 RPL
            $classesTaughtIds = [];
            $classesAndMajors = [];
            
            if (!empty($data['classes_raw'])) {
                $classes = array_filter(array_map('trim', explode(',', $data['classes_raw'])));
                $majors = array_filter(array_map('trim', explode(',', $data['majors_raw'] ?? '')));
                
                $missingClasses = [];
                foreach ($classes as $idx => $classNum) {
                    if (empty($classNum) || !is_numeric($classNum)) continue;
                    
                    $classInt = (int) $classNum;
                    $majorName = isset($majors[$idx]) ? trim($majors[$idx]) : null;
                    
                    // Find the class model by matching class number and major
                    $classModel = null;
                    
                    // Try exact match with class and major (if major provided)
                    if (!empty($majorName)) {
                        $classModel = ClassModel::where('class', $classInt)
                            ->where('major', 'ilike', $majorName)
                            ->first();
                    }
                    
                    // Fallback: try class without major filter if not found
                    if (!$classModel) {
                        $classModel = ClassModel::where('class', $classInt)
                            ->when(!empty($majorName), function($q) use ($majorName) {
                                return $q->where('major', 'ilike', $majorName);
                            })
                            ->first();
                    }
                    
                    if ($classModel) {
                        $classesTaughtIds[] = $classModel->id;
                        $classesAndMajors[] = [
                            'class_id' => $classModel->id,
                            'class' => $classInt,
                            'major' => $majorName,
                        ];
                    } else {
                        $missingClasses[] = "Kelas {$classInt}" . ($majorName ? " {$majorName}" : "");
                    }
                }
                
                if (!empty($missingClasses)) {
                    // After confirmation, missing classes should have been created
                    // If they still don't exist, this is a data integrity error
                    $failures[] = [
                        'row' => $rowNumber,
                        'data' => $data,
                        'errors' => array_map(function($c){ 
                            return "Kelas '{$c}' tidak ditemukan. Data mungkin gagal dibuat atau sudah dihapus."; 
                        }, $missingClasses),
                    ];
                    continue;
                }
            }

            // Save to database
            try {
                DB::transaction(function () use ($data, $dateOfBirth, $subjectIds, $classesAndMajors) {
                    // Create user account
                    $user = User::create([
                        'email' => $data['email'],
                        'password' => Hash::make($data['nip']), // Password default = NIP
                    ]);
                    
                    // Create teacher record
                    $teacher = Teacher::create([
                        'user_id' => $user->id,
                        'nip' => $data['nip'],
                        'name' => $data['name'],
                        'gender' => $data['gender'],
                        'date_of_birth' => $dateOfBirth,
                        'phone_number' => $data['phone_number'] ?? null,
                        'address' => $data['address'] ?? null,
                    ]);
                    
                    // Sync subjects that teacher teaches
                    if (!empty($subjectIds)) {
                        $teacher->subjects()->sync($subjectIds);
                    }
                    
                    // Parse semester from input (required for teached_classes)
                    $semester = !empty($data['semester_raw']) ? (int) $data['semester_raw'] : 1; // Default semester 1
                    
                    // Parse departments (comma-separated) - mata pelajaran yang diajarkan
                    $departments = [];
                    if (!empty($data['departments_raw'])) {
                        $departments = array_filter(array_map('trim', explode(',', $data['departments_raw'])));
                    }
                    
                    // Create TeachedClass entries for each class this teacher teaches
                    // This is the key part: For each Kelas+Jurusan combination, create a teached_classes entry
                    if (!empty($classesAndMajors)) {
                        foreach ($classesAndMajors as $classInfo) {
                            // Create or update teached class entry
                            $teachedClass = \App\Models\TeachedClass::updateOrCreate(
                                [
                                    'teacher_id' => $teacher->id,
                                    'class_id' => $classInfo['class_id'],
                                    'semester' => $semester,
                                ],
                                [
                                    'departments' => $departments ?: [], // Always provide array, even if empty
                                ]
                            );
                            
                            // Sync subjects with this teached class
                            if (!empty($subjectIds)) {
                                $teachedClass->subjects()->sync($subjectIds);
                            }
                        }
                    }
                });
                $success++;
            } catch (\Throwable $e) {
                Log::error('Import teacher row failed', ['row' => $rowNumber, 'data' => $data, 'exception' => $e]);
                
                // Show raw error message from exception for debugging
                $errorMessage = $e->getMessage();
                
                $failures[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => [$errorMessage],
                ];
            }
        }

        return [$success, $failures];
    }

    /**
     * Parse a human-friendly class name like "X IPA 1" or "10 IPA 1"
     * into an array with keys: ['class' => int, 'major' => string|null]
     */
    private function parseClassName(?string $text)
    {
        if (empty($text)) {
            return null;
        }

        $text = trim($text);
        // normalize whitespace
        $parts = preg_split('/\s+/', $text);
        if (empty($parts)) {
            return null;
        }

        $classNum = null;
        $majorStartIndex = 0;

        $first = strtoupper($parts[0]);
        
        // Skip "Kelas" prefix if exists
        if ($first === 'KELAS' && count($parts) > 1) {
            $first = strtoupper($parts[1]);
            $majorStartIndex = 2;
        } else {
            $majorStartIndex = 1;
        }

        $romanMap = [
            'X' => 10,
            'XI' => 11,
            'XII' => 12,
            'IX' => 9,
        ];

        if (isset($romanMap[$first])) {
            $classNum = $romanMap[$first];
        } elseif (is_numeric($first)) {
            $classNum = (int) $first;
        }

        $major = null;
        if (count($parts) > $majorStartIndex) {
            $major = implode(' ', array_slice($parts, $majorStartIndex));
        }

        return ['class' => $classNum, 'major' => $major];
    }

    /**
     * Convert Excel date value (can be DateTime object, number, or string) to Y-m-d format.
     * Similar to how Maatwebsite/Excel handles it.
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil semua mata pelajaran yang aktif, dikelompokkan per jurusan
        $subjects = \App\Models\Subject::where('is_active', true)
            ->orderBy('major')
            ->orderBy('name')
            ->get()
            ->groupBy('major');
        
        return view('admin.teachers.create', compact('subjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {

        try {
            DB::transaction(function () use ($request) {
                $photoPath = null;
                if ($request->hasFile('photo_profile')) {
                    $photoPath = $request->file('photo_profile')->store('teachers', 'public');
                }

                // Create user with NIP as password
                $user = User::create([
                    'email' => $request->email,
                    'password' => Hash::make($request->nip),
                ]);

                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'nip' => $request->nip,
                    'name' => $request->name,
                    'gender' => $request->gender,
                    'date_of_birth' => $request->date_of_birth,
                    'phone_number' => $request->phone_number,
                    'department' => $request->departments ? array_filter($request->departments) : null,
                    'address' => $request->address,
                    'photo_profile' => $photoPath,
                ]);

                // Sync mata pelajaran yang dipilih
                if ($request->has('subject_ids') && is_array($request->subject_ids)) {
                    $teacher->subjects()->sync($request->subject_ids);
                }

                // Map UI role to DB enum value and create teacher class role entry
                $dbRole = $request->role === 'Pengajar' ? 'pengajar' : 'wali_kelas';
                TeacherClassRole::create([
                    'teacher_id' => $teacher->id,
                    'class_id' => null, // Will be set when assigning to classes
                    'role' => $dbRole,
                ]);
            });
        } catch (\Throwable $e) {
            // Build a concise, user-friendly error message for common DB errors
            $friendly = 'Terjadi kesalahan saat menyimpan data.';

            if ($e instanceof \Illuminate\Database\QueryException) {
                $sqlState = $e->errorInfo[0] ?? null;
                $sqlMessage = $e->getMessage();

                // String data right truncated (value too long)
                if ($sqlState === '22001' || str_contains($sqlMessage, 'value too long')) {
                    $over = [];
                    if (isset($request->nip) && mb_strlen($request->nip) > 20) {
                        $over[] = 'NIP (maks 20 karakter)';
                    }
                    if (isset($request->phone_number) && mb_strlen($request->phone_number) > 20) {
                        $over[] = 'No. Telepon (maks 20 karakter)';
                    }
                    if (isset($request->name) && mb_strlen($request->name) > 100) {
                        $over[] = 'Nama (maks 100 karakter)';
                    }
                    if (isset($request->departments)) {
                        $depts = array_filter($request->departments);
                        if (count($depts) > 0) {
                            $over[] = 'Bidang Studi (jumlah atau karakter melebihi batas)';
                        }
                    }

                    if (!empty($over)) {
                        $friendly = 'Panjang data melebihi batas: ' . implode(', ', $over) . '.';
                    } else {
                        $friendly = 'Salah satu nilai terlalu panjang untuk kolom database.';
                    }
                }

                // Unique constraint violation
                elseif ($sqlState === '23505' || str_contains($sqlMessage, 'duplicate key')) {
                    if (str_contains($sqlMessage, 'teachers_nip') || str_contains($sqlMessage, 'nip')) {
                        $friendly = 'NIP sudah terdaftar.';
                    } elseif (str_contains($sqlMessage, 'users_email') || str_contains($sqlMessage, 'email')) {
                        $friendly = 'Email sudah digunakan.';
                    } else {
                        $friendly = 'Terjadi pelanggaran constraint unik di database.';
                    }
                } else {
                    $friendly = 'Kesalahan database: silakan periksa input.';
                }
            } else {
                // Non-database exception
                $friendly = 'Kesalahan server: silakan hubungi administrator.';
            }

            return redirect()->back()->withInput()->with('error', 'Gagal! ' . $friendly);
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil ditambahkan. Password: ' . $request->nip);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        return view('admin.teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $teacher = Teacher::with(['user', 'subjects'])->findOrFail($id);
        
        // Ambil semua mata pelajaran yang aktif, dikelompokkan per jurusan
        $subjects = \App\Models\Subject::where('is_active', true)
            ->orderBy('major')
            ->orderBy('name')
            ->get()
            ->groupBy('major');
        
        return view('admin.teachers.edit', compact('teacher', 'subjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, string $id)
    {
        $teacher = Teacher::findOrFail($id);

        DB::transaction(function () use ($request, $teacher) {
            $userData = [
                'email' => $request->email,
            ];

            $teacher->user->update($userData);

            // Update password jika NIP berubah
            if ($request->nip !== $teacher->nip) {
                $teacher->user->update([
                    'password' => Hash::make($request->nip),
                ]);
            }

            $teacherData = [
                'nip' => $request->nip,
                'name' => $request->name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_number' => $request->phone_number,
                'department' => $request->departments ? array_filter($request->departments) : null,
                'address' => $request->address,
            ];

            if ($request->hasFile('photo_profile')) {
                if ($teacher->photo_profile) {
                    Storage::disk('public')->delete($teacher->photo_profile);
                }
                $teacherData['photo_profile'] = $request->file('photo_profile')->store('teachers', 'public');
            }

            $teacher->update($teacherData);
            
            // Sync mata pelajaran yang dipilih
            if ($request->has('subject_ids')) {
                $teacher->subjects()->sync($request->subject_ids ?? []);
            }
            
            // Update or recreate teacher class role (map UI role to DB enum)
            $teacher->classRoles()->delete();
            $dbRole = $request->role === 'Pengajar' ? 'pengajar' : 'wali_kelas';
            TeacherClassRole::create([
                'teacher_id' => $teacher->id,
                'class_id' => null, // Akan diisi saat assign ke kelas
                'role' => $dbRole,
            ]);

            // Jika guru tidak lagi berperan sebagai wali_kelas, hapus penugasan wali kelas pada tabel classes
            if ($dbRole !== 'wali_kelas') {
                ClassModel::where('homeroom_teacher_id', $teacher->id)
                    ->update(['homeroom_teacher_id' => null]);
            }
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $teacher = Teacher::findOrFail($id);
        
        DB::transaction(function () use ($teacher) {
            // Force delete both teacher and user completely (not soft delete)
            $user = $teacher->user;
            $teacher->forceDelete();
            if ($user) {
                $user->forceDelete();
            }
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil dihapus');
    }

    /**
     * Download sample Excel template for teachers import using PhpSpreadsheet.
     */
    public function downloadTemplate()
    {
        $export = new TeacherTemplateExport();
        $spreadsheet = $export->generate();
        
        $filename = 'Template_Import_Guru_' . date('Y-m-d_His') . '.xlsx';
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        
        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });
        
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        return $response;
    }
}

