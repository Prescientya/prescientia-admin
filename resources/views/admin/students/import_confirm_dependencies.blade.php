@extends('layouts.app')

@section('title', 'Data Excel yang Gagal di Import - SekolahKu Admin')

@section('page-title', 'Data Excel yang Gagal di Import')

@section('content')
<style>
    /* Prevent ALL layout-level alerts from appearing */
    .admin-content > .alert { 
        display: none !important; 
    }
    
    /* Completely hide notification badge */
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
        <h5 class="mb-0">⚠️ Terdapat Kelas yang Hilang</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-warning mb-4">
            <strong>Perhatian:</strong> File Excel Anda mengandung siswa dengan kelas yang belum terdaftar dalam sistem.
            <br>Berikut adalah detail baris yang gagal beserta alasan:
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-hover table-sm">
                <thead class="table-warning">
                    <tr>
                        <th style="width: 80px;">Baris</th>
                        <th style="width: 120px;">NIS</th>
                        <th style="width: 150px;">Nama Siswa</th>
                        <th style="width: 60px;">Kelas</th>
                        <th style="width: 120px;">Jurusan</th>
                        <th>Alasan Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($missingClasses as $error)
                    <tr>
                        <td>
                            <strong style="color: #856404;">{{ $error['row'] }}</strong>
                        </td>
                        <td>
                            <small>{{ $error['data']['nis'] ?? '-' }}</small>
                        </td>
                        <td>
                            <small>{{ $error['data']['name'] ?? '-' }}</small>
                        </td>
                        <td>
                            <small><strong>{{ $error['data']['class_number'] ?? '-' }}</strong></small>
                        </td>
                        <td>
                            <span class="badge bg-warning">{{ $error['data']['class_major'] ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="error-messages">
                                @foreach($error['errors'] as $errorMsg)
                                <div class="error-item">
                                    <i class="bi bi-exclamation-circle-fill text-warning"></i>
                                    <span class="ms-2">{{ $errorMsg }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada kelas yang hilang</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="alert alert-info">
            <strong>ℹ️ Opsi Anda:</strong>
            <ul class="mb-0 mt-2">
                <li><strong>"Buat Data Baru"</strong> - Sistem akan membuat kelas-kelas yang hilang secara otomatis, kemudian mengimport semua siswa</li>
                <li><strong>"Kembali"</strong> - Kembali ke halaman import untuk memperbaiki file Excel</li>
            </ul>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-4">
            <a href="{{ route('admin.students.index', ['show_import' => 1]) }}" class="btn btn-secondary">
                <i class="bi bi-chevron-left"></i> Kembali
            </a>

            <form action="{{ route('admin.students.import.create-missing-and-import') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="filePath" value="{{ $filePath }}">
                
                @foreach($missingClasses as $item)
                <input type="hidden" name="missingClasses[{{ $loop->index }}][class]" value="{{ $item['data']['class_number'] }}">
                <input type="hidden" name="missingClasses[{{ $loop->index }}][major]" value="{{ $item['data']['class_major'] }}">
                @endforeach

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Buat Data Kelas Baru
                </button>
            </form>
        </div>

        <hr class="my-4">

        <div class="text-center text-muted">
            <small>
                <strong>File Summary:</strong> 
                Total baris: {{ $rowCount }} 
                | Kelas yang hilang: {{ count($missingClasses) }}
            </small>
        </div>
    </div>
</div>

<style>
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
    color: #856404;
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
