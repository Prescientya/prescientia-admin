<?php

namespace App\Http\Controllers;

use App\Models\ClassPeriod;
use App\Services\ClassPeriodImportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ClassPeriodController extends Controller
{
    protected ClassPeriodImportService $importService;

    public function __construct(ClassPeriodImportService $importService)
    {
        $this->importService = $importService;
        $this->middleware('auth');
        $this->middleware('admin'); // Hanya admin
    }

    /**
     * GET: List semua class periods
     */
    public function index(Request $request)
    {
        // Jika request JSON (AJAX), return JSON
        if ($request->wantsJson()) {
            $day = $request->query('day');

            $query = ClassPeriod::query();

            if ($day) {
                $query->where('day', $day);
            }

            $periods = $query->orderBy('day')->orderBy('sequence')->get();

            // Group by day untuk tampilan
            $grouped = $periods->groupBy('day');

            return response()->json([
                'success' => true,
                'data' => $grouped,
                'has_data' => ClassPeriod::exists(),
            ]);
        }

        // Jika request HTML, return view
        return view('admin.class-periods.index');
    }

    /**
     * GET: Detail satu periode
     */
    public function show(ClassPeriod $classPeriod)
    {
        return response()->json([
            'success' => true,
            'data' => $classPeriod,
        ]);
    }

    /**
     * POST: Import jadwal dari file Excel/CSV
     * 
     * Request:
     * {
     *   "file": File (xlsx, csv)
     * }
     */
    public function import(Request $request): JsonResponse
    {
        // Validasi file
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls|max:5120', // max 5MB
        ]);

        try {
            // Cek apakah sudah ada data
            if ($this->importService->hasExistingData()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data jadwal pelajaran sudah ada. Hapus data terlebih dahulu dengan tombol "Delete All" sebelum import yang baru.',
                    'action' => 'delete_first',
                ], 422);
            }

            // Parse file
            $filePath = $request->file('file')->store('temp');
            $data = $this->importService->parseFile(storage_path('app/' . $filePath));

            // Validasi data
            $errors = $this->importService->validate($data);
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $errors,
                ], 422);
            }

            // Import ke database
            $count = $this->importService->import($data);

            // Bersihkan temp file
            @unlink(storage_path('app/' . $filePath));

            return response()->json([
                'success' => true,
                'message' => "Import berhasil! {$count} periode jadwal telah ditambahkan.",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * PATCH: Update note untuk periode tertentu per hari
     * 
     * Request:
     * {
     *   "day": "senin",
     *   "sequence": 0,
     *   "note": "Upacara Bendera"
     * }
     */
    public function updateNote(Request $request): JsonResponse
    {
        $request->validate([
            'day' => 'required|in:senin,selasa,rabu,kamis,jumat',
            'sequence' => 'required|integer|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        $period = ClassPeriod::where('day', $request->day)
                             ->where('sequence', $request->sequence)
                             ->first();

        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'Periode tidak ditemukan',
            ], 404);
        }

        $period->update(['note' => $request->note]);

        return response()->json([
            'success' => true,
            'message' => 'Catatan periode berhasil diperbarui',
            'data' => $period,
        ]);
    }

    /**
     * DELETE: Hapus SEMUA jadwal pelajaran (bulk delete)
     * Memerlukan konfirmasi dan admin authorization
     */
    public function deleteAll(Request $request): JsonResponse
    {
        // Double check: cegah accident delete
        $request->validate([
            'confirm' => 'required|in:yes,true,1',
        ]);

        if ($request->confirm !== 'yes') {
            return response()->json([
                'success' => false,
                'message' => 'Konfirmasi tidak sesuai',
            ], 422);
        }

        $count = ClassPeriod::count();
        ClassPeriod::query()->delete();

        return response()->json([
            'success' => true,
            'message' => "Semua {$count} periode jadwal telah dihapus. Silakan import jadwal baru.",
            'count' => $count,
        ]);
    }

    /**
     * GET: Check status — apakah sudah ada data atau belum
     */
    public function checkStatus(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'has_data' => ClassPeriod::exists(),
            'total' => ClassPeriod::count(),
        ]);
    }

    /**
     * GET: Download template Excel untuk import jam pelajaran
     */
    public function downloadTemplate()
    {
        $templatePath = storage_path('app/templates/Template_Jam_Pelajaran.xlsx');
        
        // Always create fresh template
        $this->createTemplate($templatePath);
        
        return response()->download($templatePath, 'Template_Jam_Pelajaran.xlsx');
    }

    /**
     * Create Excel template file using PhpSpreadsheet
     */
    private function createTemplate($templatePath)
    {
        // Make sure directory exists
        $directory = dirname($templatePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setTitle('Template Jam Pelajaran');
        
        // Header row
        $headers = ['HARI', 'JAM_KE', 'WAKTU_MULAI', 'WAKTU_SELESAI', 'KETERANGAN'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Style header
        $headerRange = 'A1:E1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');
        
        // Template data based on the PDF format shown
        $templateData = [
            // SENIN (13 periods)
            ['senin', 0, '06:30', '07:15', 'Upacara'],
            ['senin', 1, '07:15', '07:55', ''],
            ['senin', 2, '07:55', '08:35', ''],
            ['senin', 3, '08:35', '09:15', ''],
            ['senin', 4, '09:15', '09:55', ''],
            ['senin', 5, '09:55', '10:25', 'Istirahat/MBG'],
            ['senin', 6, '10:25', '11:05', ''],
            ['senin', 7, '11:05', '11:45', ''],
            ['senin', 8, '11:45', '12:20', 'Istirahat'],
            ['senin', 9, '12:20', '13:00', ''],
            ['senin', 10, '13:00', '13:40', ''],
            ['senin', 11, '13:40', '14:20', ''],
            ['senin', 12, '14:20', '15:00', ''],
            
            // SELASA, RABU, KAMIS (14 periods each - identical schedule)
            ['selasa', 0, '06:10', '06:30', 'Tadarus & Kebersihan'],
            ['selasa', 1, '06:30', '07:10', ''],
            ['selasa', 2, '07:10', '07:50', ''],
            ['selasa', 3, '07:50', '08:30', ''],
            ['selasa', 4, '08:30', '09:10', ''],
            ['selasa', 5, '09:10', '09:50', ''],
            ['selasa', 6, '09:50', '10:20', 'Istirahat/MBG'],
            ['selasa', 7, '10:20', '11:00', ''],
            ['selasa', 8, '11:00', '11:40', ''],
            ['selasa', 9, '11:40', '12:30', 'Istirahat'],
            ['selasa', 10, '12:30', '13:10', ''],
            ['selasa', 11, '13:10', '13:50', ''],
            ['selasa', 12, '13:50', '14:30', ''],
            ['selasa', 13, '14:30', '15:10', ''],
            
            // RABU (same as Tuesday)
            ['rabu', 0, '06:10', '06:30', 'Tadarus & Kebersihan'],
            ['rabu', 1, '06:30', '07:10', ''],
            ['rabu', 2, '07:10', '07:50', ''],
            ['rabu', 3, '07:50', '08:30', ''],
            ['rabu', 4, '08:30', '09:10', ''],
            ['rabu', 5, '09:10', '09:50', ''],
            ['rabu', 6, '09:50', '10:20', 'Istirahat/MBG'],
            ['rabu', 7, '10:20', '11:00', ''],
            ['rabu', 8, '11:00', '11:40', ''],
            ['rabu', 9, '11:40', '12:30', 'Istirahat'],
            ['rabu', 10, '12:30', '13:10', ''],
            ['rabu', 11, '13:10', '13:50', ''],
            ['rabu', 12, '13:50', '14:30', ''],
            ['rabu', 13, '14:30', '15:10', ''],
            
            // KAMIS (same as Tuesday/Wednesday)
            ['kamis', 0, '06:10', '06:30', 'Tadarus & Kebersihan'],
            ['kamis', 1, '06:30', '07:10', ''],
            ['kamis', 2, '07:10', '07:50', ''],
            ['kamis', 3, '07:50', '08:30', ''],
            ['kamis', 4, '08:30', '09:10', ''],
            ['kamis', 5, '09:10', '09:50', ''],
            ['kamis', 6, '09:50', '10:20', 'Istirahat/MBG'],
            ['kamis', 7, '10:20', '11:00', ''],
            ['kamis', 8, '11:00', '11:40', ''],
            ['kamis', 9, '11:40', '12:30', 'Istirahat'],
            ['kamis', 10, '12:30', '13:10', ''],
            ['kamis', 11, '13:10', '13:50', ''],
            ['kamis', 12, '13:50', '14:30', ''],
            ['kamis', 13, '14:30', '15:10', ''],
            
            // JUMAT (10 periods)
            ['jumat', 0, '06:30', '07:30', 'Kerohanian/Olahraga/Kebersihan'],
            ['jumat', 1, '07:30', '08:05', ''],
            ['jumat', 2, '08:05', '08:40', ''],
            ['jumat', 3, '08:40', '09:15', ''],
            ['jumat', 4, '09:15', '09:50', ''],
            ['jumat', 5, '09:50', '10:25', 'Istirahat/MBG'],
            ['jumat', 6, '10:25', '10:55', ''],
            ['jumat', 7, '10:55', '11:30', ''],
            ['jumat', 8, '11:30', '12:30', 'Shalat Jum\'at / Keputian'],
            ['jumat', 9, '12:30', '13:10', 'Istirahat'],
        ];
        
        // Add data to sheet
        $sheet->fromArray($templateData, null, 'A2');
        
        // Auto-size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Add borders to all data
        $lastRow = count($templateData) + 1;
        $sheet->getStyle("A1:E{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Add instructions in a separate sheet
        $instructionSheet = $spreadsheet->createSheet();
        $instructionSheet->setTitle('Petunjuk');
        
        $instructions = [
            ['PETUNJUK PENGGUNAAN TEMPLATE JAM PELAJARAN'],
            [''],
            ['1. Isi data pada sheet "Template Jam Pelajaran"'],
            ['2. Format kolom:'],
            ['   - HARI: senin, selasa, rabu, kamis, jumat (huruf kecil)'],
            ['   - JAM_KE: angka urutan (0, 1, 2, dst)'],
            ['   - WAKTU_MULAI: format HH:MM (contoh: 06:30)'],
            ['   - WAKTU_SELESAI: format HH:MM (contoh: 07:15)'],
            ['   - KETERANGAN: teks bebas (boleh kosong)'],
            [''],
            ['3. Jangan ubah nama kolom header'],
            ['4. Simpan file dalam format Excel (.xlsx)'],
            ['5. Upload file di sistem admin'],
        ];
        
        $instructionSheet->fromArray($instructions, null, 'A1');
        $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $instructionSheet->getColumnDimension('A')->setAutoSize(true);
        
        // Set active sheet back to template
        $spreadsheet->setActiveSheetIndex(0);
        
        // Save file
        $writer = new Xlsx($spreadsheet);
        $writer->save($templatePath);
    }
}
