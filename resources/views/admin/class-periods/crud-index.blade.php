@extends('layouts.app')

@section('title', 'Kelola Jam Pelajaran - Admin')

@section('page-title', 'Kelola Jam Pelajaran')

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('styles')
<style>
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }
    
    .modal-container {
      background: white;
      padding: 24px;
      border-radius: 12px;
      width: 600px;
      max-width: 90%;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    .file-input-wrapper {
      position: relative;
      display: inline-block;
      cursor: pointer;
    }

    .file-input-wrapper input[type="file"] {
      position: absolute;
      opacity: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }

    .file-input-label {
      display: inline-block;
      padding: 12px 24px;
      background: #f8f9fa;
      border: 2px dashed #dee2e6;
      border-radius: 8px;
      color: #6c757d;
      font-weight: 500;
      transition: all 0.3s ease;
      min-width: 200px;
      text-align: center;
    }

    .file-input-wrapper:hover .file-input-label {
      background: #e9ecef;
      border-color: #adb5bd;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Daftar Jam Pelajaran</h4>
                <div class="btn-group">
                    <button class="btn btn-success me-2" onclick="openImportModal()">
                        <i class="fas fa-file-import"></i> Import Excel
                    </button>
                    <a href="{{ route('admin.manage.class-periods.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Manual
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @foreach($days as $day)
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ ucfirst($day) }}</h5>
        </div>
        <div class="card-body p-0">
            @if(isset($groupedPeriods[$day]) && $groupedPeriods[$day]->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Urutan</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Selesai</th>
                            <th>Durasi (menit)</th>
                            <th>Jenis</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groupedPeriods[$day] as $period)
                        <tr>
                            <td>{{ $period->sequence }}</td>
                            <td>{{ $period->start_time }}</td>
                            <td>{{ $period->end_time }}</td>
                            <td>{{ $period->duration_minutes ?? '-' }}</td>
                            <td>
                                @if($period->activity_type === 'belajar')
                                    <span class="badge bg-success">Belajar</span>
                                @elseif($period->activity_type === 'istirahat')
                                    <span class="badge bg-warning">Istirahat</span>
                                @elseif($period->activity_type === 'shalat')
                                    <span class="badge bg-info">Shalat</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.manage.class-periods.edit', $period->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.manage.class-periods.destroy', $period->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus jam pelajaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-4 text-center text-muted">
                Belum ada jam pelajaran untuk hari {{ ucfirst($day) }}
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

<!-- Import Modal -->
<div class="modal-overlay" id="importModal">
  <div class="modal-container">
    <div class="import-section" id="importSection">
      <h3 class="text-center mb-4">📥 Import Jam Pelajaran</h3>
      
      <div class="mb-4 text-center">
          <p class="text-muted mb-3">Belum punya template? Download template Excel terlebih dahulu:</p>
          <a href="{{ route('admin.class-periods.download-template') }}" 
             class="btn btn-outline-primary">
              <i class="fas fa-download"></i> Download Template Excel
          </a>
      </div>

      <form id="importForm" onsubmit="handleImport(event)" method="POST" action="{{ route('admin.class-periods.import') }}" enctype="multipart/form-data">
          @csrf
          <div class="d-flex justify-content-center mb-3">
              <label class="file-input-wrapper">
                  <input type="file" id="importFile" name="file" accept=".xlsx,.csv,.xls" required>
                  <span class="file-input-label">
                      <i class="fas fa-file-excel"></i> Pilih File Excel/CSV
                  </span>
              </label>
          </div>
          
          <div class="text-center mb-4">
              <small class="text-muted">Format yang didukung: Excel (.xlsx, .xls), CSV | Maksimal 5MB</small>
          </div>

          <div class="d-flex justify-content-center gap-2">
              <button type="submit" class="btn btn-primary" id="uploadBtn">
                  <i class="fas fa-check"></i> Upload
              </button>
              <button type="button" class="btn btn-secondary" onclick="closeImportModal()">
                  <i class="fas fa-times"></i> Batal
              </button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    function openImportModal() {
        document.getElementById('importModal').style.display = 'flex';
    }

    function closeImportModal() {
        document.getElementById('importModal').style.display = 'none';
        document.getElementById('importForm').reset();
    }

    function handleImport(event) {
        event.preventDefault();
        
        const fileInput = document.getElementById('importFile');
        const uploadBtn = document.getElementById('uploadBtn');
        
        if (!fileInput.files[0]) {
            alert('Mohon pilih file terlebih dahulu');
            return;
        }
        
        // Validasi ukuran file (maksimal 5MB)
        const maxSize = 5 * 1024 * 1024;
        if (fileInput.files[0].size > maxSize) {
            alert('Ukuran file terlalu besar. Maksimal 5MB');
            return;
        }
        
        // Validasi tipe file
        const allowedTypes = ['.xlsx', '.xls', '.csv'];
        const fileName = fileInput.files[0].name.toLowerCase();
        const isValidType = allowedTypes.some(type => fileName.endsWith(type));
        
        if (!isValidType) {
            alert('Tipe file tidak didukung. Gunakan file Excel (.xlsx, .xls) atau CSV');
            return;
        }
        
        // Show loading state
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';
        
        // Submit form
        const formData = new FormData(event.target);
        
        fetch(event.target.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Import berhasil! ' + data.message);
                closeImportModal();
                window.location.reload();
            } else {
                let errorMessage = 'Import gagal: ' + (data.message || 'Terjadi kesalahan');
                
                if (data.errors && Array.isArray(data.errors)) {
                    errorMessage += '\n\nDetail Error:\n' + data.errors.join('\n');
                }
                
                alert(errorMessage);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Terjadi kesalahan saat mengupload file. Coba lagi.');
        })
        .finally(() => {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-check"></i> Upload';
        });
    }
</script>
@endpush
