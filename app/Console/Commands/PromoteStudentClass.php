<?php

namespace App\Console\Commands;

use App\Models\ClassModel;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PromoteStudentClass extends Command
{
    /**
     * Tanggal kenaikan kelas (bulan-tanggal).
     * Ubah nilai di bawah untuk mengatur kapan siswa naik kelas.
     */
    const PROMOTION_MONTH = 7;  // Juli
    const PROMOTION_DAY   = 19; // Tanggal 19

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:promote-class
                            {--date= : (optional) simulate today date in YYYY-MM-DD format}
                            {--force : Bypass the flag check and force re-processing}
                            {--dry-run : Show what would happen without actually updating data}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Automatically promote all students to the next class level every July 19. Class 10 → 11, Class 11 → 12, Class 12 → graduated (class_id set to null).';

    /**
     * Get the path for the flag file for a given year.
     */
    private function getFlagPath(int $year): string
    {
        return storage_path("app/student_promotion_{$year}.flag");
    }

    /**
     * Write a flag file indicating promotion for the given year has been processed.
     */
    private function writeFlag(int $year): void
    {
        file_put_contents($this->getFlagPath($year), now()->toDateTimeString());
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : now();

        $isDryRun = $this->option('dry-run');

        // Only run on the configured promotion date
        if ($today->month !== self::PROMOTION_MONTH || $today->day !== self::PROMOTION_DAY) {
            $promotionDate = sprintf('%02d-%02d', self::PROMOTION_MONTH, self::PROMOTION_DAY);
            $message = "Today ({$today->toDateString()}) is not the promotion date ({$promotionDate}). No action taken.";
            $this->info($message);
            Log::info('[Student Promotion] ' . $message);
            return 0;
        }

        $year = $today->year;
        $flagPath = $this->getFlagPath($year);

        // Skip if already processed for this year (unless --force)
        if (!$this->option('force') && !$isDryRun && file_exists($flagPath)) {
            $processedAt = trim(file_get_contents($flagPath));
            $message = "Student promotion for year {$year} already processed at {$processedAt}. Skipping.";
            $this->info($message);
            Log::info('[Student Promotion] ' . $message);
            return 0;
        }

        if ($isDryRun) {
            $this->warn('[DRY RUN] No data will be changed.');
        }

        $this->info("Starting student class promotion for academic year {$year}...");

        $promoted    = 0;
        $graduated   = 0;
        $skipped     = 0;

        $students = Student::with('class')->whereHas('class')->get();

        foreach ($students as $student) {
            $currentClass = $student->class;

            if (!$currentClass) {
                $skipped++;
                continue;
            }

            $currentLevel = $currentClass->class; // 10, 11, or 12

            if ($currentLevel >= 12) {
                // Kelas 12 → lulus
                if (!$isDryRun) {
                    $student->class_id = null;
                    $student->save();
                }
                $graduated++;
                $this->line("  [LULUS]   {$student->name} (NIS: {$student->nis}) — Kelas 12 {$currentClass->major}");
            } else {
                // Cari kelas berikutnya dengan major yang sama
                $nextLevel = $currentLevel + 1;
                $nextClass = ClassModel::where('class', $nextLevel)
                    ->where('major', $currentClass->major)
                    ->first();

                if (!$nextClass) {
                    $this->warn("  [SKIP]    {$student->name} (NIS: {$student->nis}) — Kelas {$nextLevel} {$currentClass->major} tidak ditemukan di database.");
                    Log::warning("[Student Promotion] Next class not found for student {$student->name} (NIS: {$student->nis}): Kelas {$nextLevel} {$currentClass->major}");
                    $skipped++;
                    continue;
                }

                if (!$isDryRun) {
                    $student->class_id = $nextClass->id;
                    $student->save();
                }
                $promoted++;
                $this->line("  [NAIK]    {$student->name} (NIS: {$student->nis}) — Kelas {$currentLevel} → {$nextLevel} {$currentClass->major}");
            }
        }

        $summary = "Promotion done for year {$year}: {$promoted} students promoted, {$graduated} graduated, {$skipped} skipped.";
        $this->info($summary);
        Log::info('[Student Promotion] ' . $summary);

        if (!$isDryRun) {
            $this->writeFlag($year);
        }

        return 0;
    }
}
