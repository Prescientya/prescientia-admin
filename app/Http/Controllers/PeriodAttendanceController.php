<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\ClassPeriod;
use App\Models\SchoolCalendar;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentAttendancePeriod;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeacherAttendancePeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodAttendanceController extends Controller
{
    /* ═══════════════════════════════════════════════════════════
       STUDENT — Per Jam Pelajaran
       ═══════════════════════════════════════════════════════════ */

    /**
     * GET /attendance/siswa/period
     * Tampilkan grid kehadiran siswa per jam pelajaran.
     */
    public function studentPeriodIndex(Request $request)
    {
        $date    = $request->get('date', today()->toDateString());
        $classId = $request->get('class_id');

        $classes = ClassModel::orderBy('class')->orderBy('major')->get();

        // Tentukan nama hari (bahasa Indonesia → enum class_periods.day)
        $dayMap = [
            'Monday'    => 'senin',
            'Tuesday'   => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday'  => 'kamis',
            'Friday'    => 'jumat',
            'Saturday'  => 'sabtu',
            'Sunday'    => 'minggu',
        ];
        $dayEn  = Carbon::parse($date)->format('l');
        $dayId  = $dayMap[$dayEn] ?? null;

        // Ambil jam pelajaran (hanya lesson) untuk hari tersebut
        $periods = collect();
        if ($dayId && !in_array($dayId, ['sabtu', 'minggu'])) {
            $periods = ClassPeriod::where('day', $dayId)
                ->where('activity_type', 'lesson')
                ->orderBy('sequence')
                ->get();
        }

        // Data siswa + periode
        $students       = collect();
        $periodData     = [];
        $dailyStatusMap = [];

        if ($classId && $periods->isNotEmpty()) {
            // Ambil semua siswa di kelas ini
            $students = Student::where('class_id', $classId)
                ->orderBy('name')
                ->get();

            // Ambil calendar record
            $calendar = SchoolCalendar::where('date', $date)->first();

            if ($calendar) {
                // Ambil semua kehadiran harian siswa di kelas ini pada tanggal ini
                $attendances = StudentAttendance::where('class_id', $classId)
                    ->where('calendar_id', $calendar->id)
                    ->get()
                    ->keyBy('student_id');

                // Ambil semua period records yang sudah ada
                $attIds = $attendances->pluck('id')->toArray();
                $existingPeriods = StudentAttendancePeriod::whereIn('attendance_id', $attIds)
                    ->get()
                    ->groupBy('attendance_id');

                foreach ($students as $student) {
                    $att = $attendances->get($student->id);
                    $dailyStatusMap[$student->id] = $att ? $att->status : null;

                    if ($att) {
                        $attPeriods = $existingPeriods->get($att->id, collect())->keyBy('class_period_id');
                        foreach ($periods as $period) {
                            $periodRecord = $attPeriods->get($period->id);
                            $periodData[$student->id][$period->id] = $periodRecord
                                ? $periodRecord->status
                                : null; // null = belum diisi
                        }
                    }
                }
            }
        }

        return view('Kehadiran_Siswa.period', compact(
            'date', 'classId', 'classes', 'periods', 'students',
            'periodData', 'dailyStatusMap', 'dayId'
        ));
    }

    /**
     * POST /attendance/siswa/period/store (AJAX)
     * Simpan status kehadiran siswa per jam.
     */
    public function storeStudentPeriod(Request $request)
    {
        $request->validate([
            'student_id'      => 'required|exists:students,id',
            'class_period_id' => 'required|exists:class_periods,id',
            'date'            => 'required|date',
            'status'          => 'required|in:hadir,sakit,izin,alpa,terlambat,dispen',
        ]);

        $student  = Student::findOrFail($request->student_id);
        $calendar = SchoolCalendar::forDate($request->date);

        // Pastikan ada record kehadiran harian
        $attendance = StudentAttendance::firstOrCreate(
            ['student_id' => $student->id, 'calendar_id' => $calendar->id],
            [
                'class_id' => $student->class_id,
                'status'   => $request->status, // default ke status jam ini
                'source'   => 'manual',
            ]
        );

        // Upsert record periode
        StudentAttendancePeriod::updateOrCreate(
            [
                'attendance_id'   => $attendance->id,
                'class_period_id' => $request->class_period_id,
            ],
            [
                'status' => $request->status,
            ]
        );

        return response()->json([
            'ok'     => true,
            'status' => $request->status,
        ]);
    }

    /**
     * POST /attendance/siswa/period/auto-fill (AJAX)
     * Auto-fill kehadiran per jam dari absensi harian + attendance_status_changes.
     *
     * Mekanisme:
     * - Jika siswa hadir dan TIDAK ada perubahan status → semua jam = hadir
     * - Jika siswa punya status selain hadir dan TIDAK ada perubahan → semua jam = status itu
     * - Jika ada perubahan di attendance_status_changes → isi sesuai jam perubahan
     */
    public function autoFillStudentPeriods(Request $request)
    {
        $request->validate([
            'date'     => 'required|date',
            'class_id' => 'required|exists:classes,id',
        ]);

        $date    = $request->date;
        $classId = $request->class_id;

        // Tentukan hari
        $dayMap = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat',
        ];
        $dayEn = Carbon::parse($date)->format('l');
        $dayId = $dayMap[$dayEn] ?? null;

        if (!$dayId) {
            return response()->json(['ok' => false, 'message' => 'Hari ini bukan hari sekolah'], 400);
        }

        // Ambil jam pelajaran (lesson)
        $periods = ClassPeriod::where('day', $dayId)
            ->where('activity_type', 'lesson')
            ->orderBy('sequence')
            ->get();

        if ($periods->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'Tidak ada jam pelajaran di hari ini'], 400);
        }

        $calendar = SchoolCalendar::where('date', $date)->first();
        if (!$calendar) {
            return response()->json(['ok' => false, 'message' => 'Tanggal tidak ditemukan di kalender sekolah'], 400);
        }

        // Ambil semua kehadiran harian siswa di kelas ini
        $attendances = StudentAttendance::where('class_id', $classId)
            ->where('calendar_id', $calendar->id)
            ->get();

        if ($attendances->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'Tidak ada data kehadiran harian untuk kelas ini pada tanggal tersebut'], 400);
        }

        // Ambil semua status changes untuk kehadiran ini
        $attIds = $attendances->pluck('id')->toArray();
        $allChanges = DB::table('attendance_status_changes')
            ->whereIn('attendance_id', $attIds)
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('attendance_id');

        $filled = 0;

        foreach ($attendances as $att) {
            $changes = $allChanges->get($att->id, collect());

            if ($changes->isEmpty()) {
                // Tidak ada perubahan → semua jam = status harian
                $dailyStatus = $att->status;
                // Map status harian ke period status (terlambat di harian = hadir di per-jam setelah jam pertama)
                foreach ($periods as $period) {
                    StudentAttendancePeriod::updateOrCreate(
                        ['attendance_id' => $att->id, 'class_period_id' => $period->id],
                        ['status' => $dailyStatus]
                    );
                    $filled++;
                }
            } else {
                // Ada perubahan → walk through changes dan isi per period
                // Mulai dengan status harian awal (sebelum perubahan pertama)
                $firstChange = $changes->first();
                $initialStatus = $firstChange->old_status;

                // Buat timeline: period_id → status berdasarkan changes
                // Setiap change terjadi di class_period_id tertentu
                // Dari period itu ke depan, status berubah ke new_status
                $periodStatusMap = [];
                $currentStatus   = $initialStatus;

                // Build ordered changes by period sequence
                $changesByPeriod = [];
                foreach ($changes as $change) {
                    if ($change->class_period_id) {
                        $changesByPeriod[$change->class_period_id][] = $change;
                    }
                }

                foreach ($periods as $period) {
                    // Cek apakah ada perubahan di period ini
                    if (isset($changesByPeriod[$period->id])) {
                        // Ambil perubahan terakhir di period ini
                        $lastChange = collect($changesByPeriod[$period->id])->last();
                        $currentStatus = $lastChange->new_status;
                    }

                    // Map status ke enum yang valid untuk period table
                    $mappedStatus = in_array($currentStatus, StudentAttendancePeriod::STATUSES)
                        ? $currentStatus
                        : 'hadir';

                    $periodStatusMap[$period->id] = $mappedStatus;
                }

                // Simpan ke DB
                foreach ($periodStatusMap as $periodId => $status) {
                    StudentAttendancePeriod::updateOrCreate(
                        ['attendance_id' => $att->id, 'class_period_id' => $periodId],
                        ['status' => $status]
                    );
                    $filled++;
                }
            }
        }

        return response()->json([
            'ok'      => true,
            'message' => "Berhasil mengisi {$filled} data kehadiran per jam",
            'filled'  => $filled,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       TEACHER — Per Jam Pelajaran
       ═══════════════════════════════════════════════════════════ */

    /**
     * GET /attendance/guru/period
     * Tampilkan grid kehadiran guru per jam pelajaran.
     */
    public function teacherPeriodIndex(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        $dayMap = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat',
            'Saturday' => 'sabtu', 'Sunday' => 'minggu',
        ];
        $dayEn = Carbon::parse($date)->format('l');
        $dayId = $dayMap[$dayEn] ?? null;

        // Ambil jam pelajaran (lesson) untuk hari tersebut
        $periods = collect();
        if ($dayId && !in_array($dayId, ['sabtu', 'minggu'])) {
            $periods = ClassPeriod::where('day', $dayId)
                ->where('activity_type', 'lesson')
                ->orderBy('sequence')
                ->get();
        }

        // Data guru + periode
        $teachers       = collect();
        $periodData     = [];
        $dailyStatusMap = [];

        if ($periods->isNotEmpty()) {
            // Ambil semua guru yang aktif
            $teachers = Teacher::orderBy('name')->get();

            $calendar = SchoolCalendar::where('date', $date)->first();

            if ($calendar) {
                $attendances = TeacherAttendance::where('calendar_id', $calendar->id)
                    ->get()
                    ->keyBy('teacher_id');

                $attIds = $attendances->pluck('id')->toArray();
                $existingPeriods = TeacherAttendancePeriod::whereIn('teacher_attendance_id', $attIds)
                    ->get()
                    ->groupBy('teacher_attendance_id');

                foreach ($teachers as $teacher) {
                    $att = $attendances->get($teacher->id);
                    $dailyStatusMap[$teacher->id] = $att ? $att->status : null;

                    if ($att) {
                        $attPeriods = $existingPeriods->get($att->id, collect())->keyBy('class_period_id');
                        foreach ($periods as $period) {
                            $periodRecord = $attPeriods->get($period->id);
                            $periodData[$teacher->id][$period->id] = $periodRecord
                                ? $periodRecord->status
                                : null;
                        }
                    }
                }
            }
        }

        return view('Kehadiran_Guru.period', compact(
            'date', 'periods', 'teachers', 'periodData', 'dailyStatusMap', 'dayId'
        ));
    }

    /**
     * POST /attendance/guru/period/store (AJAX)
     */
    public function storeTeacherPeriod(Request $request)
    {
        $request->validate([
            'teacher_id'      => 'required|exists:teachers,id',
            'class_period_id' => 'required|exists:class_periods,id',
            'date'            => 'required|date',
            'status'          => 'required|in:hadir,sakit,izin,dinas,alpa,terlambat',
        ]);

        $teacher  = Teacher::findOrFail($request->teacher_id);
        $calendar = SchoolCalendar::forDate($request->date);

        $attendance = TeacherAttendance::firstOrCreate(
            ['teacher_id' => $teacher->id, 'calendar_id' => $calendar->id],
            [
                'status' => $request->status,
                'source' => 'manual',
            ]
        );

        TeacherAttendancePeriod::updateOrCreate(
            [
                'teacher_attendance_id' => $attendance->id,
                'class_period_id'       => $request->class_period_id,
            ],
            [
                'status' => $request->status,
            ]
        );

        return response()->json([
            'ok'     => true,
            'status' => $request->status,
        ]);
    }

    /**
     * POST /attendance/guru/period/auto-fill (AJAX)
     * Auto-fill kehadiran guru per jam dari absensi harian.
     */
    public function autoFillTeacherPeriods(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;

        $dayMap = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat',
        ];
        $dayEn = Carbon::parse($date)->format('l');
        $dayId = $dayMap[$dayEn] ?? null;

        if (!$dayId) {
            return response()->json(['ok' => false, 'message' => 'Hari ini bukan hari sekolah'], 400);
        }

        $periods = ClassPeriod::where('day', $dayId)
            ->where('activity_type', 'lesson')
            ->orderBy('sequence')
            ->get();

        if ($periods->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'Tidak ada jam pelajaran di hari ini'], 400);
        }

        $calendar = SchoolCalendar::where('date', $date)->first();
        if (!$calendar) {
            return response()->json(['ok' => false, 'message' => 'Tanggal tidak ditemukan di kalender sekolah'], 400);
        }

        $attendances = TeacherAttendance::where('calendar_id', $calendar->id)->get();

        if ($attendances->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'Tidak ada data kehadiran harian guru pada tanggal tersebut'], 400);
        }

        $filled = 0;
        foreach ($attendances as $att) {
            foreach ($periods as $period) {
                TeacherAttendancePeriod::updateOrCreate(
                    ['teacher_attendance_id' => $att->id, 'class_period_id' => $period->id],
                    ['status' => $att->status]
                );
                $filled++;
            }
        }

        return response()->json([
            'ok'      => true,
            'message' => "Berhasil mengisi {$filled} data kehadiran guru per jam",
            'filled'  => $filled,
        ]);
    }
}
