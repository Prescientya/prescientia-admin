<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceAccountController extends Controller
{
    /**
     * Display a listing of device change requests.
     */
    public function index()
    {
        $requests = DeviceChangeRequest::with(['user.student', 'user.teacher', 'user.admin'])
            ->orderByRaw("CASE 
                WHEN status = 'pending' THEN 1 
                WHEN status = 'confirm' THEN 2 
                WHEN status = 'denied' THEN 3 
                ELSE 4 END")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.device_accounts.index', compact('requests'));
    }

    /**
     * Approve device change request.
     */
    public function approve($id)
    {
        try {
            DB::beginTransaction();

            $request = DeviceChangeRequest::findOrFail($id);

            if ($request->status !== 'pending') {
                return redirect()->back()->with('error', 'Request sudah diproses sebelumnya.');
            }

            // Update device_id di table users
            $user = $request->user;
            if ($user) {
                $user->device_id = $request->device_id_new;
                $user->save();
            }

            // Update status request
            $request->status = 'confirm';
            $request->submitted_by = 'Admin';
            $request->save();

            DB::commit();

            return redirect()->back()->with('success', 'Request berhasil disetujui. Device ID user telah diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Deny device change request.
     */
    public function deny($id)
    {
        try {
            $request = DeviceChangeRequest::findOrFail($id);

            if ($request->status !== 'pending') {
                return redirect()->back()->with('error', 'Request sudah diproses sebelumnya.');
            }

            // Update status request
            $request->status = 'denied';
            $request->submitted_by = 'Admin';
            $request->save();

            return redirect()->back()->with('success', 'Request berhasil ditolak.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
