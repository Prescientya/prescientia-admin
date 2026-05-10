<?php

namespace App\Imports;

use App\Models\ClassModel;
use App\Models\ClassPeriod;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSchedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class TeacherScheduleImport implements ToModel, WithHeadingRow, WithChunkReading, SkipsOnError
{
    use SkipsErrors;

    public function chunkSize(): int
    {
        return 200;
    }

    private int   $imported   = 0;
    private array $failedRows = [];

    /* ── Pre-loaded lookup collections (populated once in constructor) ── */
    private Collection $teachersByNip;
    private Collection $subjectsByName;
    private Collection $classesByLabel;
    private Collection $periodsByDaySeq;

    /* ── In-memory conflict tracking (updated as rows are imported) ───── */
    private array $existingTSCP      = [];   // "t-s-c-p" → true
    private array $teacherPeriodMap  = [];   // "teacherId:periodId" → ['class_id','class_label','subject_name']
    private array $classPeriodMap    = [];   // "classId:periodId"   → ['teacher_id','teacher_name']
    private array $teacherSubjectSet = [];   // "teacherId:subjectId" → true
    private array $subjectClassSet   = [];   // "subjectId:classId"   → true

    public function __construct()
    {
        // 1. Teachers keyed by NIP (1 query)
        $this->teachersByNip = Teacher::all()->keyBy('nip');

        // 2. Active subjects keyed by lowercase name (1 query)
        $this->subjectsByName = Subject::where('is_active', true)->get()
            ->keyBy(fn ($s) => mb_strtolower($s->name));

        // 3. Classes keyed by "TINGKAT MAJOR" label (1 query)
        $this->classesByLabel = ClassModel::all()
            ->keyBy(fn ($c) => $c->class . ' ' . strtoupper(trim($c->major ?? '')));

        // 4. Lesson periods keyed by "day-sequence" (1 query)
        $this->periodsByDaySeq = ClassPeriod::where('activity_type', 'lesson')->get()
            ->keyBy(fn ($p) => $p->day . '-' . $p->sequence);

        // 5. Existing schedules → conflict maps (1 query)
        $existing = TeacherSchedule::with(['teacher:id,name', 'subject:id,name', 'schoolClass:id,class,major'])->get();
        foreach ($existing as $s) {
            $this->existingTSCP["{$s->teacher_id}-{$s->subject_id}-{$s->class_id}-{$s->class_period_id}"] = true;

            $classLabel  = $s->schoolClass ? ($s->schoolClass->class . ' ' . $s->schoolClass->major) : 'kelas lain';
            $this->teacherPeriodMap["{$s->teacher_id}:{$s->class_period_id}"] = [
                'class_id'     => $s->class_id,
                'class_label'  => $classLabel,
                'subject_name' => $s->subject->name ?? '?',
            ];
            $this->classPeriodMap["{$s->class_id}:{$s->class_period_id}"] = [
                'teacher_id'   => $s->teacher_id,
                'teacher_name' => $s->teacher->name ?? 'guru lain',
            ];
        }

        // 6. Teacher ↔ subject eligibility set (1 query)
        foreach (DB::table('teacher_subject')->select('teacher_id', 'subject_id')->get() as $row) {
            $this->teacherSubjectSet["{$row->teacher_id}:{$row->subject_id}"] = true;
        }

        // 7. Subject ↔ class assignment set (1 query)
        foreach (DB::table('subject_classes')->select('subject_id', 'class_id')->get() as $row) {
            $this->subjectClassSet["{$row->subject_id}:{$row->class_id}"] = true;
        }
    }

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

        // 1. Lookup teacher by NIP (in-memory)
        $teacher = $this->teachersByNip->get($nip);
        if (!$teacher) {
            $this->failedRows[] = [
                'row'    => "NIP: {$nip}",
                'reason' => "Guru dengan NIP {$nip} tidak ditemukan di sistem.",
            ];
            return null;
        }

        // 2. Lookup subject by name (in-memory, case-insensitive)
        $subject = $this->subjectsByName->get(mb_strtolower($mapel));
        if (!$subject) {
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$mapel}",
                'reason' => "Mata pelajaran \"{$mapel}\" tidak ditemukan. Pastikan nama mapel sesuai data di sistem.",
            ];
            return null;
        }

        // 3. Lookup class by "TINGKAT JURUSAN" (in-memory)
        $kelasNorm = preg_replace('/\s+/', ' ', strtoupper($kelas));
        $spacePos  = strpos($kelasNorm, ' ');
        if ($spacePos === false) {
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$kelas}",
                'reason' => "Format kelas tidak valid: \"{$kelas}\". Gunakan format \"TINGKAT JURUSAN\" misal \"10 RPL\".",
            ];
            return null;
        }
        $tingkat  = (int) substr($kelasNorm, 0, $spacePos);
        $jurusan  = trim(substr($kelasNorm, $spacePos + 1));
        $classKey = $tingkat . ' ' . $jurusan;

        $class = $this->classesByLabel->get($classKey);
        if (!$class) {
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$kelas}",
                'reason' => "Kelas \"{$kelas}\" tidak ditemukan. Pastikan data kelas sudah tersedia di menu Data Kelas.",
            ];
            return null;
        }

        // 3b. Teacher must be assigned to this subject
        if (!isset($this->teacherSubjectSet["{$teacher->id}:{$subject->id}"])) {
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$mapel}",
                'reason' => "{$teacher->name} belum ditugaskan untuk mapel {$subject->name}. Atur mapel guru dulu di menu Data Guru.",
            ];
            return null;
        }

        // 3c. Subject must be assigned to this class in Mapel > Penugasan Kelas
        if (!isset($this->subjectClassSet["{$subject->id}:{$class->id}"])) {
            $this->failedRows[] = [
                'row'    => "{$subject->name} — {$kelas}",
                'reason' => "Mapel {$subject->name} belum dipetakan ke kelas {$kelas}. Atur dulu di menu Mata Pelajaran > Penugasan Kelas.",
            ];
            return null;
        }

        // 4. Lookup class period by day + sequence (in-memory)
        if (!in_array($hari, ClassPeriod::DAYS, true)) {
            $valid = implode(', ', ClassPeriod::DAYS);
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — hari: {$hari}",
                'reason' => "Hari \"{$hari}\" tidak valid. Gunakan salah satu dari: {$valid}.",
            ];
            return null;
        }

        $periodKey = "{$hari}-{$jamKe}";
        $period    = $this->periodsByDaySeq->get($periodKey);
        if (!$period) {
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$hari} jam ke-{$jamKe}",
                'reason' => "Jam ke-{$jamKe} pada hari " . ucfirst($hari) . " tidak ditemukan. Pastikan jam pelajaran sudah dikonfigurasi.",
            ];
            return null;
        }

        // 5. Duplicate check (in-memory)
        $dupeKey = "{$teacher->id}-{$subject->id}-{$class->id}-{$period->id}";
        if (isset($this->existingTSCP[$dupeKey])) {
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$mapel} — {$kelas} — " . ucfirst($hari) . " jam ke-{$jamKe}",
                'reason' => 'Jadwal ini sudah terdaftar di sistem (duplikat).',
            ];
            return null;
        }

        // 6. Teacher conflict: teacher already has another class at same period (in-memory)
        $tpKey = "{$teacher->id}:{$period->id}";
        if (isset($this->teacherPeriodMap[$tpKey]) && $this->teacherPeriodMap[$tpKey]['class_id'] !== $class->id) {
            $conflict = $this->teacherPeriodMap[$tpKey];
            $this->failedRows[] = [
                'row'    => "{$teacher->name} — {$kelas} — " . ucfirst($hari) . " jam ke-{$jamKe}",
                'reason' => "Bentrok! {$teacher->name} sudah mengajar di kelas {$conflict['class_label']} pada " . ucfirst($hari) . " jam ke-{$jamKe}.",
            ];
            return null;
        }

        // 7. Class conflict: class already has another teacher at same period (in-memory)
        $cpKey = "{$class->id}:{$period->id}";
        if (isset($this->classPeriodMap[$cpKey]) && $this->classPeriodMap[$cpKey]['teacher_id'] !== $teacher->id) {
            $conflict = $this->classPeriodMap[$cpKey];
            $this->failedRows[] = [
                'row'    => "{$kelas} — " . ucfirst($hari) . " jam ke-{$jamKe}",
                'reason' => "Bentrok! Kelas {$kelas} sudah memiliki jadwal dengan {$conflict['teacher_name']} pada " . ucfirst($hari) . " jam ke-{$jamKe}.",
            ];
            return null;
        }

        // ── Track new schedule in memory for subsequent row checks ──
        $this->existingTSCP[$dupeKey] = true;
        $this->teacherPeriodMap[$tpKey] = [
            'class_id'     => $class->id,
            'class_label'  => $class->class . ' ' . $class->major,
            'subject_name' => $subject->name,
        ];
        $this->classPeriodMap[$cpKey] = [
            'teacher_id'   => $teacher->id,
            'teacher_name' => $teacher->name,
        ];

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
