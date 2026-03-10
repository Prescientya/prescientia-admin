<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Enums\StudentRole;
use App\Services\StudentRoleService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Exports\StudentTemplateExport;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request()->query('q');
        $name = request()->query('name');
        $nis = request()->query('nis');
        $email = request()->query('email');
        $classId = request()->query('class_id');

        // Base query with relations
        $query = Student::with(['user', 'class']);

        // Apply individual filters if provided
        if (!empty($name)) {
            $query->where('name', 'ilike', "%{$name}%");
        }

        if (!empty($nis)) {
            $query->where('nis', 'ilike', "%{$nis}%");
        }

        if (!empty($email)) {
            $query->whereHas('user', function($u) use ($email) {
                $u->where('email', 'ilike', "%{$email}%");
            });
        }

        if (!empty($classId)) {
            $query->where('class_id', $classId);
        }

        // Apply general search filter if provided (for backward compatibility)
        if (!empty($q)) {
            $query->where(function($sub) use ($q) {
                $sub->where('nis', 'ilike', "%{$q}%")
                    ->orWhere('name', 'ilike', "%{$q}%")
                    ->orWhereHas('user', function($u) use ($q) {
                        $u->where('email', 'ilike', "%{$q}%");
                    })
                    ->orWhereHas('class', function($c) use ($q) {
                        $c->where('major', 'ilike', "%{$q}%")
                          ->orWhere('class', '::text' , 'ilike');
                    });
            });
        }

        // If client expects JSON (AJAX live search), return a compact JSON payload
        if (request()->wantsJson() || request()->ajax()) {
            $students = $query->orderBy('name')->limit(300)->get();
            $data = $students->map(function($s) {
                return [
                    'id' => $s->id,
                    'nis' => $s->nis,
                    'name' => $s->name,
                    'email' => $s->user?->email,
                    'class' => $s->class ? trim(($s->class->class ?? '') . ' ' . ($s->class->major ?? '')) : null,
                    'gender' => $s->gender,
                ];
            });
            return response()->json(['success' => true, 'data' => $data]);
        }

        $students = $query->orderBy('name')->paginate(10)->withQueryString();
        $totalStudents = Student::count();

        // Load classes for class dropdown filter
        $classes = ClassModel::orderBy('class')->orderBy('major')->get();

        return view('admin.students.index', compact('students', 'totalStudents', 'classes'));
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

        $first = strtoupper($parts[0]);
        $romanMap = [
            'X' => 10,
            'XI' => 11,
            'XII' => 12,
            'IX' => 9,
        ];

        $classNum = null;
        if (isset($romanMap[$first])) {
            $classNum = $romanMap[$first];
        } elseif (is_numeric($first)) {
            $classNum = (int) $first;
        }

        $major = null;
        if (count($parts) > 1) {
            $major = implode(' ', array_slice($parts, 1));
        }

        return ['class' => $classNum, 'major' => $major];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = ClassModel::all();
        return view('admin.students.create', compact('classes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'nish' => 'required|unique:students,nis',
            'name' => 'required|string|max:100',
            'gender' => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'role' => ['nullable','string', Rule::in(StudentRole::all())],
        ]);

        DB::transaction(function () use ($request) {
            $photoPath = null;
            if ($request->hasFile('photo_profile')) {
                $photoPath = $request->file('photo_profile')->store('students', 'public');
            }

            // Create user with NISH as password
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->nish),
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'nis' => $request->nish,
                'name' => $request->name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'class_id' => $request->class_id,
                'photo_profile' => $photoPath,
            ]);

            // Assign default role 'Pelajar' to the newly created student
            try {
                $service = new StudentRoleService();
                $service->assignDefaultRole($student);
            } catch (\Throwable $e) {
                // Log warning but don't fail the transaction
                Log::warning('Failed to assign default role to new student', ['student_id' => $student->id, 'error' => $e->getMessage()]);
            }
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil ditambahkan. Password: ' . $request->nish);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $student = Student::with(['user', 'class', 'attendanceSummary', 'classRoles'])->findOrFail($id);

        $currentRole = $student->classRoles()
            ->where('class_id', $student->class_id)
            ->value('role') ?? StudentRole::PELAJAR;

        // If attendanceSummary is missing, compute fallback from attendances
        if (!$student->attendanceSummary) {
            $att = $student->attendances()->get();
            $attendanceSummary = [
                'present' => $att->whereIn('status', ['hadir', 'terlambat'])->count(),
                'permission' => $att->where('status', 'izin')->count(),
                'sick' => $att->where('status', 'sakit')->count(),
                'absent' => $att->where('status', 'alpa')->count(),
                'late' => $att->where('status', 'terlambat')->count(),
            ];
        } else {
            // Use values from attendanceSummary model but map to view keys
            $as = $student->attendanceSummary;
            $attendanceSummary = [
                'present' => $as->total_hadir ?? $as->total_present ?? 0,
                'permission' => $as->total_izin ?? $as->total_permission ?? 0,
                'sick' => $as->total_sakit ?? $as->total_sick ?? 0,
                'absent' => $as->total_alpha ?? $as->total_absent ?? 0,
                'late' => method_exists($as, 'getTotalLateAttribute') ? $as->getTotalLateAttribute() : ($as->total_late ?? 0),
            ];
        }

        return view('admin.students.show', compact('student', 'currentRole', 'attendanceSummary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $student = Student::with(['user', 'classRoles'])->findOrFail($id);
        $classes = ClassModel::all();

        $currentRole = $student->classRoles()
            ->where('class_id', $student->class_id)
            ->value('role') ?? StudentRole::PELAJAR;

        $roles = StudentRole::all();

        return view('admin.students.edit', compact('student', 'classes', 'currentRole', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($student->user_id),
            ],
            'nish' => [
                'required',
                Rule::unique('students', 'nis')->ignore($student->id),
            ],
            'name' => 'required|string|max:100',
            'gender' => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::transaction(function () use ($request, $student) {
            $userData = [
                'email' => $request->email,
            ];

            $student->user->update($userData);

            // Update password jika NISH berubah
            if ($request->nish !== $student->nis) {
                $student->user->update([
                    'password' => Hash::make($request->nish),
                ]);
            }

            $studentData = [
                'nis' => $request->nish,
                'name' => $request->name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'class_id' => $request->class_id,
            ];

            if ($request->hasFile('photo_profile')) {
                if ($student->photo_profile) {
                    Storage::disk('public')->delete($student->photo_profile);
                }
                $studentData['photo_profile'] = $request->file('photo_profile')->store('students', 'public');
            }

            $student->update($studentData);
            // handle role update if provided
            if ($request->has('role')) {
                $service = new StudentRoleService();
                // will throw ValidationException if invalid or quota exceeded
                $service->updateRole($student, $request->get('role'));
            }
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);
        
        DB::transaction(function () use ($student) {
            // Force delete both student and user completely (not soft delete)
            $user = $student->user;
            $student->forceDelete();
            if ($user) {
                $user->forceDelete();
            }
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil dihapus');
    }

    /**
     * Delete all graduated students (class_id = null).
     * Only allowed on July 19 each year, matching the PROMOTION_DAY constant.
     */
    public function destroyGraduates()
    {
        $today = now()->timezone('Asia/Jakarta');

        // Only allowed from July 19 onwards
        $isAfterPromotionDate = ($today->month > 7) || ($today->month === 7 && $today->day >= 19);
        if (!$isAfterPromotionDate) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Penghapusan siswa lulus hanya dapat dilakukan mulai tanggal 19 Juli.');
        }

        $graduates = Student::whereNull('class_id')->get();

        if ($graduates->isEmpty()) {
            return redirect()->route('admin.students.index')
                ->with('info', 'Tidak ada siswa lulus yang perlu dihapus.');
        }

        $count = $graduates->count();

        DB::transaction(function () use ($graduates) {
            foreach ($graduates as $student) {
                $user = $student->user;
                $student->forceDelete();
                if ($user) {
                    $user->forceDelete();
                }
            }
        });

        // Write flag so the button disappears until next year
        file_put_contents(
            storage_path('app/graduates_deleted_' . $today->year . '.flag'),
            $today->toDateTimeString()
        );

        Log::info("[Student Graduation] {$count} graduated students deleted by admin on " . $today->toDateString());

        return redirect()->route('admin.students.index')
            ->with('success', "{$count} data siswa lulus berhasil dihapus dari sistem.");
    }

    /**
     * Show form for bulk import via Excel.
     */
    public function importForm()
    {
        // Redirect to the students index and open the import modal there.
        return redirect()->route('admin.students.index', ['show_import' => 1]);
    }

    /**
     * Handle Excel upload and import students using PhpSpreadsheet.
     * This method performs a comprehensive prescan to detect:
     * 1. Validation errors (format, required fields)
     * 2. Missing classes
     * Then shows appropriate view or proceeds with import
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
            
            // Comprehensive prescan: validation errors + missing data
            $importer = new \App\Imports\StudentsImport();
            $scanResult = $importer->prescanWorksheet($worksheet);
            
            // Combine all errors: validation errors + missing classes
            $allErrors = array_merge(
                $scanResult['validationErrors'],
                $scanResult['missingClasses']
            );
            
            // If there are any errors (validation or missing classes), show error view
            if (!empty($allErrors)) {
                // Ensure folder exists before storing
                if (!Storage::exists('imports')) {
                    Storage::makeDirectory('imports');
                }
                
                $stored = $file->store('imports');
                Log::info('File stored for import', ['path' => $stored]);
                
                // Check if errors are only missing classes (no validation errors)
                if (empty($scanResult['validationErrors']) && !empty($scanResult['missingClasses'])) {
                    // Show confirmation view with option to create missing classes
                    return view('admin.students.import_confirm_dependencies', [
                        'missingClasses' => $scanResult['missingClasses'],
                        'filePath' => $stored,
                        'rowCount' => $scanResult['totalRows'],
                    ]);
                } else {
                    // Show error details view
                    return view('admin.students.import_error_details', [
                        'validationErrors' => $allErrors,
                        'filePath' => $stored,
                        'totalRows' => $scanResult['totalRows'],
                        'errorCount' => count($allErrors),
                    ]);
                }
            }

            // All validations pass, proceed with import
            $importer = new \App\Imports\StudentsImport();
            $importer->processWorksheet($worksheet);
            $success = $importer->getSuccessCount();
            $failures = $importer->getFailures();
            
            // If there are import failures, show error details
            if (!empty($failures)) {
                return view('admin.students.import_error_details', [
                    'validationErrors' => $failures,
                    'filePath' => null,
                    'totalRows' => $success + count($failures),
                    'errorCount' => count($failures),
                    'successCount' => $success,
                ]);
            }
            
            return redirect()->route('admin.students.index')
                ->with('success', "✅ Import berhasil: {$success} siswa ditambahkan");
        } catch (\Throwable $e) {
            Log::error('Import students failed', ['exception' => $e]);
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Create missing classes and import students
     */
    public function createMissingAndImport(Request $request)
    {
        $request->validate([
            'filePath' => 'required|string',
            'missingClasses' => 'nullable|array',
        ]);

        $filePath = $request->input('filePath');
        $missingClassesData = $request->input('missingClasses', []);
        $classesCreated = 0;
        $studentsCreated = 0;
        $studentsFailed = 0;
        $tempPath = null;

        try {
            // Ensure imports folder exists
            if (!Storage::exists('imports')) {
                Storage::makeDirectory('imports');
            }

            // Construct full path based on local disk configuration
            // Default 'local' disk stores in storage_path('app/private')
            $localDiskRoot = storage_path('app/private');
            $fullFilePath = $localDiskRoot . DIRECTORY_SEPARATOR . $filePath;
            
            Log::info('Attempting to load import file', [
                'filePath' => $filePath,
                'fullPath' => $fullFilePath,
                'exists' => file_exists($fullFilePath),
                'localDiskRoot' => $localDiskRoot,
            ]);

            // Check if file exists at the actual path
            if (!file_exists($fullFilePath)) {
                // Fallback: try alternate path without the local disk root prefix
                $alternatePath = storage_path('app') . DIRECTORY_SEPARATOR . $filePath;
                if (!file_exists($alternatePath)) {
                    Log::error('File not found in either location', [
                        'attempted_path' => $fullFilePath,
                        'alternate_path' => $alternatePath,
                        'imports_folder_contents' => array_map(
                            fn($f) => basename($f),
                            glob(storage_path('app/private/imports') . '/*') ?: []
                        )
                    ]);
                    throw new \Exception('File import tidak ditemukan. Path dicoba: ' . $filePath);
                }
                $fullFilePath = $alternatePath;
            }

            // Read file directly and write to temp
            $fileContent = file_get_contents($fullFilePath);
            if ($fileContent === false) {
                throw new \Exception('Gagal membaca file import: ' . $filePath);
            }
            
            // Write to system temp file for processing
            $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'student_import_' . uniqid() . '.xlsx';
            if (file_put_contents($tempPath, $fileContent) === false) {
                throw new \Exception('Gagal menyimpan file ke temp directory');
            }
            
            Log::info('File loaded and temp file created', ['tempPath' => $tempPath]);
            
            try {
                $spreadsheet = IOFactory::load($tempPath);
                $worksheet = $spreadsheet->getActiveSheet();

                // Create missing classes and count how many were created
                $classesCreated = $this->createMissingClasses($missingClassesData);

                // Proceed with import
                $importer = new \App\Imports\StudentsImport();
                $importer->processWorksheet($worksheet);
                $studentsCreated = $importer->getSuccessCount();
                $studentsFailed = count($importer->getFailures());
                
                Log::info('Import completed successfully', [
                    'classesCreated' => $classesCreated,
                    'studentsCreated' => $studentsCreated,
                    'studentsFailed' => $studentsFailed,
                ]);
            } finally {
                // Clean up temp file
                if ($tempPath && file_exists($tempPath)) {
                    unlink($tempPath);
                    Log::debug('Temp file cleaned up', ['tempPath' => $tempPath]);
                }
            }
            
            // Cleanup storage file
            try {
                if (file_exists($fullFilePath)) {
                    unlink($fullFilePath);
                    Log::debug('Original import file deleted', ['path' => $fullFilePath]);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to delete original import file', ['path' => $fullFilePath, 'error' => $e->getMessage()]);
            }

            // Build message
            $message = "✅ Kelas dibuat: {$classesCreated} | Siswa berhasil: {$studentsCreated}";
            if ($studentsFailed > 0) {
                $message = "⚠️ Kelas dibuat: {$classesCreated} | Siswa berhasil: {$studentsCreated} | Siswa gagal: {$studentsFailed}";
            }

            return redirect()->route('admin.students.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Create missing classes and import failed', ['exception' => $e]);
            return redirect()->route('admin.students.index')
                ->with('error', 'Gagal membuat kelas dan mengimport: ' . $e->getMessage());
        }
    }

    /**
     * Create missing classes from import and return count of classes created
     */
    private function createMissingClasses(array $classesData): int
    {
        $created = 0;
        
        DB::transaction(function () use ($classesData, &$created) {
            foreach ($classesData as $item) {
                $classNumber = isset($item['class']) ? (int)$item['class'] : null;
                $major = isset($item['major']) ? trim($item['major']) : null;

                if (!$classNumber || empty($major)) {
                    continue;
                }

                // Check again in case of race condition
                $exists = ClassModel::where('class', $classNumber)
                    ->where('major', $major)
                    ->exists();
                
                if (!$exists) {
                    try {
                        ClassModel::create([
                            'class' => $classNumber,
                            'major' => $major,
                        ]);
                        $created++;
                        Log::info('Created missing class', [
                            'class' => $classNumber,
                            'major' => $major,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Failed to create class', [
                            'class' => $classNumber,
                            'major' => $major,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        });
        
        return $created;
    }

    /**
     * Download sample Excel template for students import using PhpSpreadsheet.
     */
    public function downloadTemplate()
    {
        $export = new StudentTemplateExport();
        $spreadsheet = $export->generate();
        
        $filename = 'Template_Import_Siswa_' . date('Y-m-d_His') . '.xlsx';
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        
        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });
        
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        return $response;
    }

    /**
     * Process an uploaded worksheet and perform imports.
     * Returns [successCount, failuresArray]
     */
    private function processImportSpreadsheet($worksheet)
    {
        $rows = $worksheet->toArray();
        $success = 0;
        $failures = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;
            $rowNumber = $index + 1;

            $data = [
                'email' => isset($row[0]) ? trim($row[0]) : null,
                'nis' => isset($row[1]) ? trim($row[1]) : null,
                'name' => isset($row[2]) ? trim($row[2]) : null,
                'gender' => isset($row[3]) ? trim($row[3]) : null,
                'birth_date' => isset($row[4]) ? trim($row[4]) : null,
                'phone' => isset($row[5]) ? trim($row[5]) : null,
                'address' => isset($row[6]) ? trim($row[6]) : null,
                'class_name' => isset($row[7]) ? trim($row[7]) : null,
            ];

            if (empty($data['email']) && empty($data['nis']) && empty($data['name'])) {
                continue;
            }

            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'email' => 'required|email|unique:users,email',
                'nis' => 'required|unique:students,nis',
                'name' => 'required|string',
                'gender' => 'required|string',
                'birth_date' => 'required|date_format:Y-m-d',
                'class_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                $failures[] = ['row' => $rowNumber, 'data' => $data, 'errors' => $validator->errors()->all()];
                continue;
            }

            // resolve class (should exist now)
            $provided = $data['class_name'];
            $parsed = $this->parseClassName($provided);
            $class = null;
            if ($parsed && isset($parsed['class'])) {
                $query = ClassModel::where('class', $parsed['class']);
                if (!empty($parsed['major'])) {
                    $query->where('major', $parsed['major']);
                }
                $class = $query->first();
                if (!$class && !empty($parsed['major'])) {
                    $class = ClassModel::where('class', $parsed['class'])
                        ->where('major', 'ilike', '%' . $parsed['major'] . '%')
                        ->first();
                }
            } else {
                $class = ClassModel::where('major', $provided)->first();
            }

            if (!$class) {
                $failures[] = ['row' => $rowNumber, 'data' => $data, 'errors' => ["Kelas \"{$provided}\" tidak ditemukan saat pemrosesan akhir"]];
                continue;
            }

            try {
                DB::transaction(function () use ($data, $class) {
                    $user = User::create(['email' => $data['email'], 'password' => Hash::make($data['nis'])]);
                    $student = Student::create([
                        'user_id' => $user->id,
                        'nis' => $data['nis'],
                        'name' => $data['name'],
                        'gender' => $data['gender'],
                        'date_of_birth' => $data['birth_date'],
                        'phone_number' => $data['phone'],
                        'address' => $data['address'],
                        'class_id' => $class->id,
                    ]);

                    // Assign default role 'Pelajar' to the newly created student
                    try {
                        $service = new StudentRoleService();
                        $service->assignDefaultRole($student);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to assign default role to imported student', ['student_id' => $student->id, 'nis' => $data['nis'], 'error' => $e->getMessage()]);
                    }
                });
                $success++;
            } catch (\Throwable $e) {
                Log::error('Import student row failed', ['row' => $rowNumber, 'data' => $data, 'exception' => $e]);
                $failures[] = ['row' => $rowNumber, 'data' => $data, 'errors' => ['Terjadi kesalahan saat menyimpan data pada baris ini.']];
            }
        }

        return [$success, $failures];
    }

    /**
     * Process creation of selected missing classes and then import from stored file.
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'create_classes' => 'nullable|array',
        ]);

        $path = $request->input('path');
        $toCreate = $request->input('create_classes', []);

        // Create classes requested by admin
        foreach ($toCreate as $classLabel) {
            $parsed = $this->parseClassName($classLabel);
            $data = ['major' => $parsed['major'] ?? null, 'class' => $parsed['class'] ?? null];
            if (empty($data['class'])) continue;
            $exists = ClassModel::where('class', $data['class'])->where('major', $data['major'])->first();
            if (!$exists) {
                ClassModel::create($data);
            }
        }

        // Load stored file and process
        try {
            $full = storage_path('app/' . $path);
            $spreadsheet = IOFactory::load($full);
            $worksheet = $spreadsheet->getActiveSheet();
            [$success, $failures] = $this->processImportSpreadsheet($worksheet);
        } catch (\Throwable $e) {
            Log::error('Import process failed', ['exception' => $e]);
            return redirect()->back()->with('error', 'Gagal memproses file setelah membuat kelas.');
        }

        // cleanup
        try { Storage::delete($path); } catch (\Throwable $e) { /* ignore */ }

        return view('admin.students.import_result', ['successCount' => $success, 'failures' => $failures]);
    }
}

