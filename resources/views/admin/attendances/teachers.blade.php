@extends('layouts.app')

@section('title', 'Absensi Guru - SekolahKu Admin')

@section('page-title', 'Absensi Guru')

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

/* Pagination Styles (same as students) */
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
                <div class="col-md-2">
                    <label class="form-label">Mata Pelajaran</label>
                    <select name="subject_id" class="form-select">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="dinas" {{ request('status') == 'dinas' ? 'selected' : '' }}>Dinas</option>
                        <option value="alpa" {{ request('status') == 'alpa' ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cari (Nama / NIP)</label>
                    <input type="text" name="keyword" class="form-control" placeholder="Ketik nama atau NIP" value="{{ $keyword ?? '' }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" id="clear-filters" class="btn btn-outline-secondary w-100">Reset</button>
                </div>
            </form>
        </div>

        @if($paginator->count() > 0)
            <!-- Title and Buttons in One Row -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Recap Absen Guru</h5>
                <button type="button" class="btn btn-primary" onclick="openInputAbsenModal()">
                    <i class="bi bi-plus-circle"></i> Input Absen Guru
                </button>
                <a href="{{ route('admin.attendances.teachers.export', array_filter([
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'status' => $status,
                    'subject_id' => $subjectId,
                    'keyword' => $keyword,
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
                            <th>Nama Guru</th>
                            <th>Status</th>
                            <th>Sumber</th>
                            <th style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginator as $k => $record)
                            <tr>
                                <td>{{ $paginator->firstItem() + $k }}</td>
                                <td>{{ $record->date ? \Carbon\Carbon::parse($record->date)->format('d M Y') : '-' }}</td>
                                <td>{{ $record->name }}</td>
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

            <div class="pagination-section">
                    <div class="pagination-info">
                        Menampilkan data <strong>{{ $startIndex ?? 0 }}</strong> – 
                        <strong>{{ $endIndex ?? 0 }}</strong> dari <strong>{{ $total ?? 0 }}</strong>
                    </div>

                    <div class="pagination-controls">
                        <!-- First -->
                        <a href="{{ $paginator->url(1) }}&{{ http_build_query(request()->except('page')) }}"
                           class="pagination-btn"
                           {{ ($currentPage ?? 1) == 1 ? 'disabled onclick="return false;"' : '' }}>&laquo;</a>

                        <!-- Previous -->
                        <a href="{{ $paginator->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                           class="pagination-btn"
                           {{ ($currentPage ?? 1) == 1 ? 'disabled onclick="return false;"' : '' }}>&lt;</a>

                        <span class="pagination-separator">|</span>

                        @php
                            $start = max(1, ($currentPage ?? 1) - 2);
                            $end = min(($lastPage ?? 1), ($currentPage ?? 1) + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $paginator->url(1) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">1</a>
                            @if($start > 2)
                                <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            @if($page == ($currentPage ?? 1))
                                <button class="pagination-btn active" aria-current="page" aria-disabled="true" tabindex="-1">{{ $page }}</button>
                            @else
                                <a href="{{ $paginator->url($page) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">{{ $page }}</a>
                            @endif
                        @endfor

                        @if($end < ($lastPage ?? 1))
                            @if($end < (($lastPage ?? 1) - 1))
                                <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                            @endif
                            <a href="{{ $paginator->url($lastPage ?? 1) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">{{ $lastPage ?? 1 }}</a>
                        @endif

                        <span class="pagination-separator">|</span>

                        <!-- Next -->
                        <a href="{{ $paginator->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                           class="pagination-btn"
                           {{ ($currentPage ?? 1) == ($lastPage ?? 1) ? 'disabled onclick="return false;"' : '' }}>&gt;</a>

                        <!-- Last -->
                        <a href="{{ $paginator->url($lastPage ?? 1) }}&{{ http_build_query(request()->except('page')) }}"
                           class="pagination-btn"
                           {{ ($currentPage ?? 1) == ($lastPage ?? 1) ? 'disabled onclick="return false;"' : '' }}>&raquo;</a>
                    </div>
                </div>
        @else
            <div class="alert alert-info text-center">Belum ada data absensi guru untuk filter ini</div>
        @endif
    </div>
</div>

<!-- Modal Input Absen Guru -->
<div id="inputAbsenModal" class="simple-modal" aria-hidden="true">
    <div class="simple-modal-backdrop" data-modal-close></div>
    <div class="simple-modal-dialog">
        <div class="simple-modal-header">
            <h5>Input Absen Guru</h5>
            <button type="button" class="simple-modal-close" data-modal-close>&times;</button>
        </div>
        <div class="simple-modal-body">
            <form id="inputAbsenForm">
                @csrf
                <div class="mb-3">
                    <label for="teacherSearch" class="form-label">Nama Guru</label>
                    <input type="text" class="form-control" id="teacherSearch" placeholder="Ketik nama guru..." autocomplete="off" required>
                    <input type="hidden" id="teacherId" name="teacher_id">
                    <div id="teacherSuggestions" class="list-group mt-1" style="position: absolute; z-index: 1060; max-height: 200px; overflow-y: auto; display: none;"></div>
                    <small class="text-muted">Ketik untuk melihat guru yang belum absen hari ini</small>
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
                        <option value="dinas">Kedinasan</option>
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
#teacherSuggestions {
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
#teacherSuggestions .suggestion-item {
    display: block;
    padding: 12px 14px;
    border-bottom: 1px solid #f0f0f0;
    background: #ffffff;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
}
#teacherSuggestions .suggestion-item:last-child {
    border-bottom: none;
}
#teacherSuggestions .suggestion-item:hover {
    background: #f5f9ff;
    padding-left: 16px;
}
#teacherSuggestions .suggestion-item .teacher-name {
    display: block;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 4px;
    font-size: 14px;
}
#teacherSuggestions .suggestion-item .teacher-info {
    display: block;
    font-size: 12px;
    color: #666;
    line-height: 1.4;
}
</style>

@endsection

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

    form.addEventListener('change', () => {
        form.submit();
    });

    document.getElementById('clear-filters').addEventListener('click', () => {
        form.reset();
        form.submit();
    });
})();

// Teacher Autocomplete and Submission
(() => {
    const teacherSearch = document.getElementById('teacherSearch');
    const teacherId = document.getElementById('teacherId');
    const suggestions = document.getElementById('teacherSuggestions');
    const submitBtn = document.getElementById('submitAbsen');
    let debounceTimer;

    // Autocomplete functionality
    teacherSearch.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestions.style.display = 'none';
            teacherId.value = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('admin.attendances.teachers.search-unattended') }}?q=${encodeURIComponent(query)}&date=${document.getElementById('attendanceDate').value}`)
                .then(response => response.json())
                .then(data => {
                    suggestions.innerHTML = '';
                    
                    if (data.length === 0) {
                        suggestions.innerHTML = '<div class="list-group-item text-muted">Tidak ada guru yang ditemukan</div>';
                    } else {
                        data.forEach(teacher => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'suggestion-item';
                            item.innerHTML = `
                                <span class="teacher-name">${teacher.name}</span>
                                <span class="teacher-info">NIP: ${teacher.nip || '-'} | ${teacher.position || '-'}</span>
                            `;
                            item.addEventListener('click', (e) => {
                                e.preventDefault();
                                teacherSearch.value = teacher.name;
                                teacherId.value = teacher.id;
                                suggestions.style.display = 'none';
                            });
                            suggestions.appendChild(item);
                        });
                    }
                    
                    suggestions.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching teachers:', error);
                    suggestions.innerHTML = '<div class="list-group-item text-danger">Error memuat data</div>';
                    suggestions.style.display = 'block';
                });
        }, 300);
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!teacherSearch.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.style.display = 'none';
        }
    });

    // Submit attendance
    submitBtn.addEventListener('click', function() {
        const form = document.getElementById('inputAbsenForm');
        const formData = new FormData(form);
        
        if (!teacherId.value) {
            alert('Silakan pilih guru dari daftar');
            return;
        }

        if (!formData.get('status')) {
            alert('Silakan pilih status kehadiran');
            return;
        }

        formData.append('teacher_id', teacherId.value);
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

        fetch('{{ route("admin.attendances.teachers.store") }}', {
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
