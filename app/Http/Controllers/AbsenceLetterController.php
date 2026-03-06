<?php

namespace App\Http\Controllers;

use App\Models\AbsenceLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AbsenceLetterController extends Controller
{
    /* ── Index ─────────────────────────────────────────── */
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $type   = $request->input('type', 'all');
        $search = trim($request->input('search', ''));

        $query = DB::table('absence_letters as al')
            ->leftJoin('students as s', 'al.student_id', '=', 's.id')
            ->leftJoin('teachers as t', 'al.teacher_id', '=', 't.id')
            ->leftJoin('classes as c', 'al.class_id', '=', 'c.id')
            ->select(
                'al.*',
                DB::raw("CASE WHEN al.user_type = 'student' THEN s.name ELSE t.name END AS requester_name"),
                DB::raw("CASE WHEN al.user_type = 'student' THEN s.nis ELSE t.nip END AS requester_no"),
                DB::raw("CONCAT(c.class, ' ', COALESCE(c.major, '')) as class_name")
            );

        // Type filter
        if ($type === 'siswa') {
            $query->where('al.user_type', 'student');
        } elseif ($type === 'guru') {
            $query->where('al.user_type', 'teacher');
        }

        // Status filter: "pending" shows items ready for admin/wali action
        if ($status === 'pending') {
            $query->where('al.status', 'pending');
        } elseif ($status === 'approved') {
            $query->where('al.status', 'approved');
        } elseif ($status === 'rejected') {
            $query->where('al.status', 'rejected');
        }
        // 'all' — no filter

        // Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('s.name', 'like', "%{$search}%")
                  ->orWhere('t.name', 'like', "%{$search}%")
                  ->orWhere('s.nis', 'like', "%{$search}%")
                  ->orWhere('t.nip', 'like', "%{$search}%")
                  ->orWhere('al.description', 'like', "%{$search}%");
            });
        }

        $letters = $query
            ->orderByRaw("CASE al.status
                WHEN 'pending' THEN 0
                WHEN 'approved' THEN 1
                WHEN 'rejected' THEN 2
                ELSE 3 END")
            ->orderBy('al.created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Summary counts
        $counts = [
            'pending' => DB::table('absence_letters')
                ->where('status', 'pending')
                ->count(),
            'approved' => DB::table('absence_letters')
                ->where('status', 'approved')
                ->count(),
            'rejected' => DB::table('absence_letters')
                ->where('status', 'rejected')
                ->count(),
            'all' => DB::table('absence_letters')->count(),
        ];

        return view('Absence_Letters.index', compact('letters', 'counts', 'status', 'type', 'search'));
    }

    /* ── Approve ────────────────────────────────────────── */
    public function approve(Request $request, int $id)
    {
        $letter = AbsenceLetter::findOrFail($id);

        // For students: can be pending (wali kelas or admin can approve).
        // For teachers: must be pending.
        if ($letter->status !== 'pending') {
            return back()->with('error', 'Surat sudah diproses sebelumnya.');
        }

        $adminId = Auth::id();

        DB::transaction(function () use ($letter, $adminId) {
            $letter->update([
                'status'            => 'approved',
                'approved_by_admin' => $adminId,
                'approved_admin_at' => now(),
            ]);

            // Auto-create/update attendance record
            if ($letter->user_type === 'student' && $letter->student_id) {
                $existing = DB::table('student_attendances')
                    ->where('student_id', $letter->student_id)
                    ->where('calendar_id', $letter->calendar_id)
                    ->first();

                if ($existing) {
                    DB::table('student_attendances')
                        ->where('id', $existing->id)
                        ->update([
                            'status'     => $letter->reason,
                            'updated_at' => now(),
                        ]);
                } elseif ($letter->calendar_id) {
                    DB::table('student_attendances')->insert([
                        'student_id'  => $letter->student_id,
                        'class_id'    => $letter->class_id,
                        'calendar_id' => $letter->calendar_id,
                        'status'      => $letter->reason,
                        'source'      => 'manual',
                        'created_at'  => now(),
                    ]);
                }

                // Update student_attendance_summary counters
                $summaryCol = $letter->reason === 'sakit' ? 'total_sakit' : 'total_izin';
                $existingSummary = DB::table('student_attendance_summary')
                    ->where('student_id', $letter->student_id)
                    ->first();

                if ($existingSummary) {
                    DB::table('student_attendance_summary')
                        ->where('student_id', $letter->student_id)
                        ->update([
                            $summaryCol  => DB::raw("{$summaryCol} + 1"),
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('student_attendance_summary')->insert([
                        'student_id'  => $letter->student_id,
                        'total_hadir' => 0,
                        'total_izin'  => $summaryCol === 'total_izin' ? 1 : 0,
                        'total_sakit' => $summaryCol === 'total_sakit' ? 1 : 0,
                        'total_alpha' => 0,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            } elseif ($letter->user_type === 'teacher' && $letter->teacher_id) {
                $existing = DB::table('teacher_attendances')
                    ->where('teacher_id', $letter->teacher_id)
                    ->where('calendar_id', $letter->calendar_id)
                    ->first();

                if ($existing) {
                    DB::table('teacher_attendances')
                        ->where('id', $existing->id)
                        ->update([
                            'status'     => $letter->reason,
                            'updated_at' => now(),
                        ]);
                } elseif ($letter->calendar_id) {
                    DB::table('teacher_attendances')->insert([
                        'teacher_id'  => $letter->teacher_id,
                        'calendar_id' => $letter->calendar_id,
                        'status'      => $letter->reason,
                        'source'      => 'manual',
                        'created_at'  => now(),
                    ]);
                }
            }
        });

        return back()->with('success', 'Surat izin berhasil disetujui dan kehadiran diperbarui.');
    }

    /* ── Reject ─────────────────────────────────────────── */
    public function reject(Request $request, int $id)
    {
        $letter = AbsenceLetter::findOrFail($id);

        if ($letter->status === 'approved' || $letter->status === 'rejected') {
            return back()->with('error', 'Surat sudah selesai diproses.');
        }

        $adminId = Auth::id();

        $letter->update([
            'status'           => 'rejected',
            'rejected_by'      => $adminId,
            'rejected_by_role' => 'admin',
            'rejected_at'      => now(),
        ]);

        return back()->with('success', 'Surat izin berhasil ditolak.');
    }
}
