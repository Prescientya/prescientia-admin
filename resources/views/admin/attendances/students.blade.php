@extends('layouts.app')

@section('title', 'Absensi Siswa - SekolahKu Admin')

@section('page-title', 'Absensi Siswa')

@section('css')
<link rel="stylesheet" href="{{ asset('css/action-dropdown.css') }}">
<style>
.status-badge {
    padding: 0.25rem 0.625rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 500;
}

.status-hadir { background-color: #d4edda; color: #155724; }
.status-sakit { background-color: #fff3cd; color: #856404; }
.status-izin { background-color: #d1ecf1; color: #0c5460; }
.status-alpa { background-color: #f8d7da; color: #721c24; }
.status-dinas { background-color: #e7e7ff; color: #3a3a8f; }
.status-terlambat { background-color: #ffe5cc; color: #cc5500; }

/* Filter Grid Layout - Force 3 columns per row */
#filter-form {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

#filter-form .col-md-12 {
    grid-column: 1 / -1;
}

/* Pagination Styles */
.pagination-section {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.pagination-info {
    font-size: 0.95rem;
    color: #666;
    margin-bottom: 1.5rem;
    text-align: center;
}

.pagination-info strong {
    color: #333;
    font-weight: 600;
}

.pagination-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.pagination-btn {
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #ddd;
    background-color: #fff;
    color: #333;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.pagination-btn:hover:not(:disabled) {
    border-color: #f53003;
    color: #f53003;
    background-color: #fff5f0;
}

.pagination-btn.active {
    /* Strong, high-contrast orange for active state */
    background-color: #ff4d00 !important; /* saturated orange */
    color: #ffffff !important;
    border-color: #ff4d00 !important;
    font-weight: 800 !important;
    box-shadow: 0 6px 16px rgba(255, 77, 0, 0.18);
}

.pagination-btn.active:hover {
    background-color: #ff3b00 !important;
    border-color: #ff3b00 !important;
}

.pagination-btn:focus {
    outline: none;
    box-shadow: 0 0 0 5px rgba(255, 77, 0, 0.14);
}

.pagination-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    color: #999;
}

.pagination-separator {
    color: #ccc;
    margin: 0 0.25rem;
}
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <form id="filter-form" class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Jurusan</label>
                    <select name="major" id="major-filter" class="form-select">
                        <option value="">Semua Jurusan</option>
                        @foreach ($majors as $majorItem)
                            <option value="{{ $majorItem->major }}" {{ $filters['major'] == $majorItem->major ? 'selected' : '' }}>
                                {{ $majorItem->major }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kelas</label>
                    <select name="class_number" id="class-filter" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach ($classNumbers as $classNumber)
                            <option value="{{ $classNumber }}" {{ $filters['class_number'] == $classNumber ? 'selected' : '' }}>
                                {{ $classNumber }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ $filters['status'] == 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="sakit" {{ $filters['status'] == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin" {{ $filters['status'] == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="alpa" {{ $filters['status'] == 'alpa' ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Dari</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-4 d-flex">
                    <button type="button" id="clear-filters" class="btn btn-outline-secondary w-100 h-10 py-2 mt-8">Reset</button>
                </div>
                <div class="col-md-12 mb-10">
                    <label class="form-label">Cari (Nama / NIS)</label>
                    <input type="text" name="keyword" class="form-control" placeholder="Ketik nama atau NIS" value="{{ $filters['keyword'] ?? '' }}">
                </div>
            </form>
        </div>

        @if($records->count() > 0)
            <!-- Title and Buttons in One Row -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Recap Absen Siswa</h5>
                <button type="button" class="btn btn-primary" onclick="openInputAbsenModal()">
                    <i class="bi bi-plus-circle"></i> Input Absen Siswa
                </button>
                <a href="{{ route('admin.attendances.students.export', array_filter([
                    'date_from' => $filters['date_from'],
                    'date_to' => $filters['date_to'],
                    'status' => $filters['status'],
                    'class_number' => $filters['class_number'],
                    'major' => $filters['major'],
                    'keyword' => $filters['keyword'],
                ])) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-download"></i> Download Excel
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Sumber</th>
                            <th style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $k => $record)
                            <tr>
                                <td>{{ $records->firstItem() + $k }}</td>
                                <td>{{ $record->date ? \Carbon\Carbon::parse($record->date)->format('d M Y') : '-' }}</td>
                                <td>{{ $record->name }}</td>
                                <td>{{ $record->class ?? '-' }}</td>
                                <td><span class="status-badge status-{{ $record->status }}">{{ ucfirst($record->status) }}</span></td>
                                <td><small>{{ $record->source ? str_replace('_', ' ', ucfirst($record->source)) : '-' }}</small></td>
                                <td>
                                    <div class="action-menu-container">
                                        <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                            <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                                        </button>
                                        <ul class="dropdown-menu" style="display: none;">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.attendances.show', ['role' => $record->role, 'id' => $record->id]) }}">Lihat Detail</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.attendances.show', ['role' => $record->role, 'id' => $record->id]) }}">Edit</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Clean Pagination Section -->
            @php
                $currentPage = $records->currentPage();
                $lastPage = $records->lastPage();
                $total = $records->total();
                $perPage = $records->perPage();
                $startIndex = $records->firstItem();
                $endIndex = $records->lastItem();
            @endphp

            <div class="pagination-section">
                <!-- Information Display -->
                <div class="pagination-info">
                    Menampilkan data <strong>{{ $startIndex ?? 0 }}</strong> – 
                    <strong>{{ $endIndex ?? 0 }}</strong> dari <strong>{{ $total }}</strong>
                </div>

                <!-- Pagination Controls -->
                <div class="pagination-controls">
                    <!-- First Page Button -->
                    <a href="{{ $records->url(1) }}&{{ http_build_query(request()->except('page')) }}"
                       class="pagination-btn"
                       {{ $currentPage == 1 ? 'disabled onclick="return false;"' : '' }}>
                        &laquo;
                    </a>

                    <!-- Previous Page Button -->
                    <a href="{{ $records->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                       class="pagination-btn"
                       {{ $currentPage == 1 ? 'disabled onclick="return false;"' : '' }}>
                        &lt;
                    </a>

                    <span class="pagination-separator">|</span>

                    <!-- Page Numbers -->
                    @php
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $records->url(1) }}&{{ http_build_query(request()->except('page')) }}"
                           class="pagination-btn">
                            1
                        </a>
                        @if($start > 2)
                            <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                        @endif
                    @endif

                    @for($page = $start; $page <= $end; $page++)
                        @if($page == $currentPage)
                            <button class="pagination-btn active" aria-current="page" aria-disabled="true" tabindex="-1">{{ $page }}</button>
                        @else
                            <a href="{{ $records->url($page) }}&{{ http_build_query(request()->except('page')) }}"
                               class="pagination-btn">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                        @endif
                        <a href="{{ $records->url($lastPage) }}&{{ http_build_query(request()->except('page')) }}"
                           class="pagination-btn">
                            {{ $lastPage }}
                        </a>
                    @endif

                    <span class="pagination-separator">|</span>

                    <!-- Next Page Button -->
                    <a href="{{ $records->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                       class="pagination-btn"
                       {{ $currentPage == $lastPage ? 'disabled onclick="return false;"' : '' }}>
                        &gt;
                    </a>

                    <!-- Last Page Button -->
                    <a href="{{ $records->url($lastPage) }}&{{ http_build_query(request()->except('page')) }}"
                       class="pagination-btn"
                       {{ $currentPage == $lastPage ? 'disabled onclick="return false;"' : '' }}>
                        &raquo;
                    </a>
                </div>
            </div>
        @else
            <div class="alert alert-info text-center">Belum ada data absensi siswa untuk filter ini</div>
        @endif
    </div>
</div>

<!-- Modal Input Absen Siswa -->
<div id="inputAbsenModal" class="simple-modal" aria-hidden="true">
    <div class="simple-modal-backdrop" data-modal-close></div>
    <div class="simple-modal-dialog">
        <div class="simple-modal-header">
            <h5>Input Absen Siswa</h5>
            <button type="button" class="simple-modal-close" data-modal-close>&times;</button>
        </div>
        <div class="simple-modal-body">
            <form id="inputAbsenForm">
                @csrf
                <div class="mb-3">
                    <label for="studentSearch" class="form-label">Nama Siswa</label>
                    <input type="text" class="form-control" id="studentSearch" placeholder="Ketik nama siswa..." autocomplete="off" required>
                    <input type="hidden" id="studentId" name="student_id">
                    <div id="studentSuggestions" class="list-group mt-1" style="position: absolute; z-index: 1060; max-height: 200px; overflow-y: auto; display: none;"></div>
                    <small class="text-muted">Ketik untuk melihat siswa yang belum absen hari ini</small>
                </div>
                <div class="mb-3">
                    <label for="attendanceDate" class="form-label">Tanggal</label>
                    <input type="date" class="form-control" id="attendanceDate" name="date" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label for="attendanceStatus" class="form-label">Status</label>
                    <select class="form-select" id="attendanceStatus" name="status" required>
                        <option value="">Pilih Status</option>
                        <option value="hadir">Hadir</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpa">Alpha</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="simple-modal-footer">
            <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
            <button type="button" class="btn btn-primary" id="submitAbsen">Simpan Absensi</button>
        </div>
    </div>
</div>

<style>
.simple-modal { display: none; position: fixed; inset: 0; z-index: 1050; }
.simple-modal.show { display: block; }
.simple-modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.5); }
.simple-modal-dialog { position: relative; max-width: 600px; margin: 6% auto; background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
.simple-modal-header { display:flex; justify-content:space-between; align-items:center; padding:16px; border-bottom:1px solid #eee; }
.simple-modal-body { padding:16px; }
.simple-modal-footer { padding:12px 16px; text-align:right; border-top:1px solid #eee; }
.simple-modal-close { background:none; border:0; font-size:20px; line-height:1; cursor:pointer; }

/* Autocomplete suggestion styling */
#studentSuggestions {
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    z-index: 2000;
    max-height: 280px;
    overflow-y: auto;
    padding: 0;
    margin-top: 4px;
}
#studentSuggestions .suggestion-item {
    display: block;
    padding: 12px 14px;
    border-bottom: 1px solid #f0f0f0;
    background: #ffffff;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
}
#studentSuggestions .suggestion-item:last-child {
    border-bottom: none;
}
#studentSuggestions .suggestion-item:hover {
    background: #f5f9ff;
    padding-left: 16px;
}
#studentSuggestions .suggestion-item .student-name {
    display: block;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 4px;
    font-size: 14px;
}
#studentSuggestions .suggestion-item .student-info {
    display: block;
    font-size: 12px;
    color: #666;
    line-height: 1.4;
}
.danger-area-card {
    border: 2px solid #dc3545;
    border-radius: 8px;
    margin-top: 2rem;
}
.danger-area-header {
    background: #dc3545;
    color: #fff;
    padding: 12px 16px;
    font-weight: 700;
    font-size: 1rem;
    border-radius: 6px 6px 0 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.danger-area-body {
    padding: 20px;
    background: #fff8f8;
}
</style>

@endsection

<!-- Danger Area: Hapus Absensi Siswa Berdasarkan Rentang Tanggal -->
<div class="danger-area-card">
    <div class="danger-area-header">
        ⚠️ Danger Area!
    </div>
    <div class="danger-area-body">
        <p class="mb-3 text-danger fw-semibold">Hapus Rekap Absensi Siswa Berdasarkan Rentang Tanggal</p>
        <p class="text-muted mb-3" style="font-size:0.9rem;">
            Tindakan ini akan menghapus <strong>semua data absensi siswa</strong> pada rentang tanggal yang dipilih secara permanen dan tidak dapat dibatalkan.
        </p>
        <form id="deleteStudentAttendanceRangeForm" method="POST" action="{{ route('admin.attendances.students.delete-range') }}"
              onsubmit="return confirmDeleteStudentRange(event)">
            @csrf
            @method('DELETE')
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control border-danger" required id="studentRangeFrom">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control border-danger" required id="studentRangeTo">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-danger w-100">
                        🗑️ Hapus Absensi Siswa
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDeleteStudentRange(e) {
    const from = document.getElementById('studentRangeFrom').value;
    const to   = document.getElementById('studentRangeTo').value;
    if (!from || !to) return false;
    return confirm(
        'PERHATIAN!\n\nAnda akan menghapus SEMUA data absensi siswa dari ' + from + ' sampai ' + to + '.\n\nTindakan ini TIDAK DAPAT DIBATALKAN.\n\nApakah Anda yakin ingin melanjutkan?'
    );
}
</script>

@section('scripts')
<script>
// Modal functions
function openInputAbsenModal() {
    const modal = document.getElementById('inputAbsenModal');
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeInputAbsenModal() {
    const modal = document.getElementById('inputAbsenModal');
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

// Setup modal close handlers
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('inputAbsenModal');
    if (modal) {
        modal.querySelectorAll('[data-modal-close]').forEach(function(el) {
            el.addEventListener('click', closeInputAbsenModal);
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) closeInputAbsenModal();
        });
    }
});

// Filter form
(() => {
    const form = document.getElementById('filter-form');

    // Form submission on any field change
    form.addEventListener('change', () => {
        form.submit();
    });

    document.getElementById('clear-filters').addEventListener('click', () => {
        // Explicitly clear all form controls (selects, inputs, dates, text)
        form.querySelectorAll('select').forEach(s => { s.value = ''; });
        form.querySelectorAll('input').forEach(i => {
            if (i.type === 'checkbox' || i.type === 'radio') {
                i.checked = false;
            } else {
                i.value = '';
            }
        });
        // Remove any page param from the URL when submitting to reset pagination
        const pageInput = document.querySelector('input[name="page"]');
        if (pageInput) pageInput.remove();

        form.submit();
    });
})();

// Student Autocomplete and Submission
(() => {
    const studentSearch = document.getElementById('studentSearch');
    const studentId = document.getElementById('studentId');
    const suggestions = document.getElementById('studentSuggestions');
    const submitBtn = document.getElementById('submitAbsen');
    let debounceTimer;

    // Autocomplete functionality
    studentSearch.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestions.style.display = 'none';
            studentId.value = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('admin.attendances.students.search-unattended') }}?q=${encodeURIComponent(query)}&date=${document.getElementById('attendanceDate').value}`)
                .then(response => response.json())
                .then(data => {
                    suggestions.innerHTML = '';
                    
                    if (data.length === 0) {
                        suggestions.innerHTML = '<div class="list-group-item text-muted">Tidak ada siswa yang ditemukan</div>';
                    } else {
                        data.forEach(student => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'suggestion-item';
                            item.innerHTML = `
                                <span class="student-name">${student.name}</span>
                                <span class="student-info">NIS: ${student.nis} | Kelas: ${student.class || '-'}</span>
                            `;
                            item.addEventListener('click', (e) => {
                                e.preventDefault();
                                studentSearch.value = student.name;
                                studentId.value = student.id;
                                suggestions.style.display = 'none';
                            });
                            suggestions.appendChild(item);
                        });
                    }
                    
                    suggestions.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching students:', error);
                    suggestions.innerHTML = '<div class="list-group-item text-danger">Error memuat data</div>';
                    suggestions.style.display = 'block';
                });
        }, 300);
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!studentSearch.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.style.display = 'none';
        }
    });

    // Submit attendance
    submitBtn.addEventListener('click', function() {
        const form = document.getElementById('inputAbsenForm');
        const formData = new FormData(form);
        
        if (!studentId.value) {
            alert('Silakan pilih siswa dari daftar');
            return;
        }

        if (!formData.get('status')) {
            alert('Silakan pilih status kehadiran');
            return;
        }

        formData.append('student_id', studentId.value);
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

        fetch('{{ route("admin.attendances.students.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Absensi berhasil disimpan!');
                location.reload();
            } else {
                alert(data.message || 'Terjadi kesalahan saat menyimpan');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Simpan Absensi';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan absensi');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Simpan Absensi';
        });
    });

    // Reset form when modal is closed - tidak perlu lagi karena sudah di handle di closeInputAbsenModal
})();
</script>
<script src="{{ asset('js/action-dropdown.js') }}"></script>
@endsection
