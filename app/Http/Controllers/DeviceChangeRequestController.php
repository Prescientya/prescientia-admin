<?php

namespace App\Http\Controllers;

use App\Models\DeviceChangeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceChangeRequestController extends Controller
{
    /* ── Index ─────────────────────────────────────────── */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $type   = $request->input('type',   'all'); // all | siswa | guru
        $search = trim($request->input('search', ''));

        $query = DB::table('device_change_requests as dcr')
            ->join('users', 'users.id', '=', 'dcr.user_id')
            ->leftJoin('students', 'students.user_id', '=', 'users.id')
            ->leftJoin('teachers', 'teachers.user_id', '=', 'users.id')
            ->select(
                'dcr.id',
                'dcr.user_id',
                'dcr.device_id_old',
                'dcr.device_id_new',
                'dcr.status',
                'dcr.submitted_by',
                'dcr.created_at',
                'dcr.updated_at',
                'users.email',
                DB::raw("COALESCE(students.name, teachers.name) AS requester_name"),
                DB::raw("COALESCE(students.nis, teachers.nip)   AS requester_no"),
                DB::raw("CASE WHEN students.id IS NOT NULL THEN 'siswa' ELSE 'guru' END AS requester_type")
            );

        // Filter by status
        if ($status !== 'all') {
            $query->where('dcr.status', $status);
        }

        // Filter by type
        if ($type === 'siswa') {
            $query->whereNotNull('students.id');
        } elseif ($type === 'guru') {
            $query->whereNotNull('teachers.id');
        }

        // Search by name / NIS / NIP / device id
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('students.name',     'ilike', "%{$search}%")
                  ->orWhere('teachers.name',   'ilike', "%{$search}%")
                  ->orWhere('students.nis',    'ilike', "%{$search}%")
                  ->orWhere('teachers.nip',    'ilike', "%{$search}%")
                  ->orWhere('dcr.device_id_old', 'ilike', "%{$search}%")
                  ->orWhere('dcr.device_id_new', 'ilike', "%{$search}%");
            });
        }

        $requests = $query->orderByRaw("CASE dcr.status WHEN 'pending' THEN 0 ELSE 1 END")
                          ->orderBy('dcr.created_at', 'desc')
                          ->paginate(20)
                          ->withQueryString();

        // Summary counts
        $counts = DB::table('device_change_requests')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('Device_Requests.index', compact('requests', 'counts', 'status', 'type', 'search'));
    }

    /* ── Approve ────────────────────────────────────────── */
    public function approve(Request $request, int $id)
    {
        $dcr = DeviceChangeRequest::findOrFail($id);

        if ($dcr->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($dcr) {
            // Update the device_id on the user record
            User::where('id', $dcr->user_id)
                ->update(['device_id' => $dcr->device_id_new]);

            $dcr->update(['status' => 'approved']);
        });

        return back()->with('success', 'Permintaan pergantian device berhasil disetujui.');
    }

    /* ── Reject ─────────────────────────────────────────── */
    public function reject(Request $request, int $id)
    {
        $dcr = DeviceChangeRequest::findOrFail($id);

        if ($dcr->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $dcr->update(['status' => 'rejected']);

        return back()->with('success', 'Permintaan pergantian device berhasil ditolak.');
    }
}
