@extends('layouts.app')

@section('title', 'Device Akun - Prescientia')

@section('page-title', 'Device Akun')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Permintaan Perubahan Device ID</h5>
                <p class="text-muted mb-0 mt-1">Kelola permintaan perubahan device dari siswa dan guru</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 25%">Username</th>
                                <th style="width: 30%">Device Lama</th>
                                <th style="width: 30%">Device Baru</th>
                                <th style="width: 15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                                <tr class="{{ $request->status === 'pending' ? '' : 'table-secondary' }}">
                                    <td>
                                        <strong>{{ $request->user->display_name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            @php
                                                $email = $request->user->email;
                                                $originalEmail = $email;
                                                $isTruncated = false;
                                                if (strlen($email) > 20) {
                                                    // Split email into local part and domain
                                                    $parts = explode('@', $email);
                                                    if (count($parts) == 2) {
                                                        $localPart = $parts[0];
                                                        $domain = $parts[1];
                                                        // Take first 8 characters of local part and add ...
                                                        $shortLocal = substr($localPart, 0, 8);
                                                        $email = $shortLocal . '...@' . $domain;
                                                        $isTruncated = true;
                                                    }
                                                }
                                            @endphp
                                            <span @if($isTruncated) title="{{ $originalEmail }}" style="cursor: help;" @endif>{{ $email }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <code class="text-break">{{ $request->device_id_old }}</code>
                                    </td>
                                    <td>
                                        <code class="text-break">{{ $request->device_id_new }}</code>
                                    </td>
                                    <td class="text-center">
                                        @if($request->status === 'pending')
                                            <div class="d-flex justify-content-center gap-2">
                                                <form action="{{ route('admin.device-accounts.approve', $request->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-success px-3" title="Setujui" onclick="return confirm('Setujui perubahan device ID untuk user ini?')">
                                                        ✓
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.device-accounts.deny', $request->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-danger px-3" title="Tolak" onclick="return confirm('Tolak perubahan device ID untuk user ini?')">
                                                        ✗
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            @if($request->status === 'confirm')
                                                <span class="status-badge status-approved">Disetujui</span>
                                            @else
                                                <span class="status-badge status-denied">Ditolak</span>
                                            @endif
                                            @if($request->submitted_by)
                                                <br><small class="text-muted">oleh {{ $request->submitted_by }}</small>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <p class="text-muted mb-0">Tidak ada permintaan perubahan device</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($requests->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.table code {
    font-size: 0.85rem;
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    color: #495057;
}
.gap-2 {
    gap: 0.5rem !important;
}
.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}
.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}
.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}
.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}
.table-secondary {
    opacity: 0.7;
}

/* Status Badge Styles */
.status-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    background-color: white;
    color: #1f2937;
    min-width: 100px;
    text-align: center;
    border: 2px solid;
    transition: all 0.2s ease;
}

.status-approved {
    border-color: #28a745;
}

.status-denied {
    border-color: #dc3545;
}
</style>
@endsection
