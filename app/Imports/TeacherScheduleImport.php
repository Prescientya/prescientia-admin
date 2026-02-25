<?php

namespace App\Imports;

use App\Models\ClassModel;
use App\Models\ClassPeriod;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSchedule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class TeacherScheduleImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    private int   $imported   = 0;
    private array $failedRows = [];

    /**
     * Kolom yang diharapkan (heading row):
     * nip | mapel | kelas | hari | jam_ke
     *
     * - nip     : NIP guru (harus sudah ada di tabel teachers)
     * - mapel   : nama mata pelajaran (harus sudah ada di tabel subjects)
     * - kelas   : format "TINGKAT JURUSAN" misal "10 RPL" / "11 AKL 1"
     * - hari    : senin / selasa / rabu / kamis / jumat
     * - jam_ke  : nomor urut jam pelajaran (misal 1, 2, 3, ...)
     */
    public function model(array $row): ?TeacherSchedule
    {
        // Skip empty rows
        $nip   = trim((string) ($row['nip']   ?? ''));
        $mapel = trim((string) ($row['mapel'] ?? ''));
        $kelas = trim((string) ($row['kelas'] ?? ''));
        $hari  = strtolower(trim((string) ($row['hari']   ?? '')));
        $jamKe = (int) ($row['jam_ke'] ?? 0);

        if ($nip === '' || $mapel === '' || $kelas === '' || $hari === '' || $jamKe === 0) {
            return null;
        }

        // 1. Lookup teacher by NIP
        $teacher = Teacher::where('nip', $nip)->first();
        if (!$teacher) {
            $this->failedRows[] = [
                'row'    => "NIP: {$nip}",
                'reason' => "Guru dengan NIP {$nip} tidak ditemukan di sistem.",
            ];
            return null;
        }

        // 2. Lookup subject by name (case-insensitive)
        $subject = Subject::whereRaw('LOWER(name) = LOWER(?)', [$mapel])
                          ->where('is_active', true)
                          ->first();
        if (!$subject) {
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$mapel}",
                'reason' => "Mata pelajaran \"{$mapel}\" tidak ditemukan. Pastikan nama mapel sesuai data di sistem.",
            ];
            return null;
        }

        // 3. Lookup class by "TINGKAT JURUSAN" (e.g. "10 RPL" → class=10, major="RPL")
        $kelasNorm = preg_replace('/\s+/', ' ', strtoupper($kelas));
        $spacePos  = strpos($kelasNorm, ' ');
        if ($spacePos === false) {
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$kelas}",
                'reason' => "Format kelas tidak valid: \"{$kelas}\". Gunakan format \"TINGKAT JURUSAN\" misal \"10 RPL\".",
            ];
            return null;
        }
        $tingkat = (int) substr($kelasNorm, 0, $spacePos);
        $jurusan = trim(substr($kelasNorm, $spacePos + 1));

        $class = ClassModel::where('class', $tingkat)
                           ->whereRaw('UPPER(major) = ?', [$jurusan])
                           ->first();
        if (!$class) {
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$kelas}",
                'reason' => "Kelas \"{$kelas}\" tidak ditemukan. Pastikan data kelas sudah tersedia di menu Data Kelas.",
            ];
            return null;
        }

        // 4. Lookup class period by day + sequence + activity_type='lesson'
        if (!in_array($hari, ClassPeriod::DAYS, true)) {
            $valid = implode(', ', ClassPeriod::DAYS);
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — hari: {$hari}",
                'reason' => "Hari \"{$hari}\" tidak valid. Gunakan salah satu dari: {$valid}.",
            ];
            return null;
        }

        $period = ClassPeriod::where('day', $hari)
                             ->where('sequence', $jamKe)
                             ->where('activity_type', 'lesson')
                             ->first();
        if (!$period) {
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$hari} jam ke-{$jamKe}",
                'reason' => "Jam ke-{$jamKe} pada hari " . ucfirst($hari) . " tidak ditemukan. Pastikan jam pelajaran sudah dikonfigurasi.",
            ];
            return null;
        }

        // 5. Skip duplicate entry (same 4 fields)
        $exists = TeacherSchedule::where('teacher_id',    $teacher->id)
                                 ->where('subject_id',    $subject->id)
                                 ->where('class_id',      $class->id)
                                 ->where('class_period_id', $period->id)
                                 ->exists();
        if ($exists) {
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$mapel} — {$kelas} — " . ucfirst($hari) . " jam ke-{$jamKe}",
                'reason' => 'Jadwal ini sudah terdaftar di sistem (duplikat).',
            ];
            return null;
        }

        // 6. Conflict: teacher already assigned to ANOTHER class at the same period
        $teacherConflict = TeacherSchedule::where('teacher_id',      $teacher->id)
                                          ->where('class_period_id', $period->id)
                                          ->where('class_id',        '!=', $class->id)
                                          ->first();
        if ($teacherConflict) {
            $conflictClass   = $teacherConflict->schoolClass;
            $conflictLabel   = $conflictClass ? ($conflictClass->class . ' ' . $conflictClass->major) : 'kelas lain';
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$kelas} — " . ucfirst($hari) . " jam ke-{$jamKe}",
                'reason' => "Bentrok! {$teacher->name} sudah mengajar di kelas {$conflictLabel} pada " . ucfirst($hari) . " jam ke-{$jamKe}.",
            ];
            return null;
        }

        // 7. Conflict: class already has ANOTHER teacher at the same period
        $classConflict = TeacherSchedule::where('class_id',         $class->id)
                                        ->where('class_period_id',  $period->id)
                                        ->where('teacher_id',       '!=', $teacher->id)
                                        ->first();
        if ($classConflict) {
            $conflictTeacher = $classConflict->teacher;
            $conflictName    = $conflictTeacher ? $conflictTeacher->name : 'guru lain';
            $this->failedRows[] = [
                'row'    => "{$kelas} — " . ucfirst($hari) . " jam ke-{$jamKe}",
                'reason' => "Bentrok! Kelas {$kelas} sudah memiliki jadwal dengan {$conflictName} pada " . ucfirst($hari) . " jam ke-{$jamKe}.",
            ];
            return null;
        }

        $this->imported++;

        return new TeacherSchedule([
            'teacher_id'      => $teacher->id,
            'subject_id'      => $subject->id,
            'class_id'        => $class->id,
            'class_period_id' => $period->id,
        ]);
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getFailedRows(): array
    {
        return $this->failedRows;
    }
}
