@extends('layouts.app')

@section('title', 'Detail Error Import Siswa - SekolahKu Admin')

@section('page-title', 'Detail Error Import Siswa')

@section('content')
<!-- Hide all alerts and notifications for this page to keep focus on error details -->
<style>
    /* Prevent ALL layout-level alerts from appearing */
    .admin-content > .alert { 
        display: none !important; 
    }
    
    /* Completely hide notification badge (the red circle at top-right) */
    .notification-badge {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        width: 0 !important;
        height: 0 !important;
        min-width: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        position: absolute !important;
        pointer-events: none !important;
    }
    
    /* Hide any floating/fixed alerts or toasts */
    .alert[class*="fixed"],
    .alert[class*="toast"],
    [role="alert"],
    .alert-floating {
        display: none !important;
    }
    
    /* Force hide all session flash alerts */
    .admin-content .alert-danger,
    .admin-content .alert-success,
    .admin-content .alert-warning,
    .admin-content .alert-info {
        display: none !important;
        visibility: hidden !important;
    }
</style>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">❌ Terdapat Error pada File Excel</h5>
    </div>
    <div class="card-body">
        @if($successCount !== null && $successCount > 0)
        <div class="alert alert-info mb-4">
            <strong>✅ Catatan:</strong> {{ $successCount }} data berhasil diimport, tetapi {{ $errorCount }} data gagal.
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-danger">
                    <tr>
                        <th style="width: 80px; background-color: #f8d7da;">Baris</th>
                        <th style="width: 150px; background-color: #f8d7da;">Email</th>
                        <th style="width: 120px; background-color: #f8d7da;">NIS</th>
                        <th style="width: 150px; background-color: #f8d7da;">Nama Siswa</th>
                        <th style="background-color: #f8d7da;">Alasan Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($validationErrors as $error)
                    <tr>
                        <td>
                            <strong style="font-size: 1rem; color: #721c24;">{{ $error['row'] }}</strong>
                        </td>
                        <td>
                            <small>{{ $error['data']['email'] ?? '-' }}</small>
                        </td>
                        <td>
                            <small>{{ $error['data']['nis'] ?? '-' }}</small>
                        </td>
                        <td>
                            <small>{{ $error['data']['name'] ?? '-' }}</small>
                        </td>
                        <td>
                            <div class="error-messages">
                                @foreach($error['errors'] as $errorMsg)
                                <div class="error-item">
                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                    <span class="ms-2">{{ $errorMsg }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Tidak ada error</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <hr class="my-4">

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                <i class="bi bi-chevron-left"></i> Kembali
            </a>
        </div>

        <hr class="my-4">

        <div class="text-center text-muted">
            <small>
                <strong>Summary:</strong> 
                Total baris: {{ $totalRows }} 
                | Error: {{ $errorCount }}
                @if($successCount !== null)
                | Berhasil: {{ $successCount }}
                @endif
            </small>
        </div>
    </div>
</div>

<style>
.badge {
    padding: 0.35rem 0.65rem;
    font-size: 0.75rem;
}

.table-sm td {
    padding: 0.5rem;
    vertical-align: top;
}

.error-messages {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.error-item {
    display: flex;
    align-items: flex-start;
    padding: 0.5rem 0;
    color: #721c24;
    font-size: 0.9rem;
}

.error-item i {
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.error-item span {
    flex: 1;
}
</style>
@endsection
