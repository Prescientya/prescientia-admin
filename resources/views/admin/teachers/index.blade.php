@extends('layouts.app')

@section('title', 'Data Guru - SekolahKu Admin')

@section('page-title', 'Data Guru')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teachers.css') }}">
<link rel="stylesheet" href="{{ asset('css/action-dropdown.css') }}">
<style>
/* Pagination Styles (copied from students/classes) */
.pagination-section {
    margin-top: 3rem;
    margin-bottom: 20px;
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
    background-color: #ff4d00 !important;
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Data Guru</h5>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-secondary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#importTeacherModal">
                Import Excel
            </button>
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Guru
            </a>
        </div>
    </div>
    <div class="card-body">
            <div style="display:flex; gap:0.75rem; align-items:center; margin-bottom:1rem;">
                <input id="nameSearch" type="search" class="form-control" placeholder="Nama" style="max-width: 260px;">
                <input id="nipSearch" type="search" class="form-control" placeholder="NIP" style="max-width: 180px;">
                <input id="emailSearch" type="search" class="form-control" placeholder="Email" style="max-width: 260px;">
                <select id="subjectSearch" class="form-control" style="max-width: 220px;">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

        @if ($teachers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-compact">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th style="width: 150px;">NIP</th>
                            <th style="width: 160px;">Nama Guru</th>
                            <th style="text-align: center">Email</th>
                            <th style="width: 120px; text-align: center;">Jenis Kelamin</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="teachersTableBody">
                        @foreach ($teachers as $key => $teacher)
                            <tr>
                                <td>{{ $teachers->firstItem() + $key }}</td>
                                <td><strong>{{ $teacher->nip }}</strong></td>
                                <td>{{ $teacher->name }}</td>
                                <td>
                                    @if($teacher->user?->email)
                                        {{ preg_replace('/^(.{5}).+(@.+)$/', '$1...$2', $teacher->user->email) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">{{ $teacher->gender === 'L' ? 'L' : 'P' }}</td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <div class="action-menu-container" style="position: relative; display: inline-block;">
                                        <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                            <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                                        </button>
                                        <ul class="dropdown-menu" style="display: none;">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.teachers.show', $teacher->id) }}">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    Lihat Detail
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.teachers.edit', $teacher->id) }}">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                    Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.teachers.destroy', $teacher->id) }}" class="dropdown-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus guru ini?')">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @php
                $currentPage = $teachers->currentPage();
                $lastPage = $teachers->lastPage();
                $total = $teachers->total();
                $perPage = $teachers->perPage();
                $startIndex = $teachers->firstItem();
                $endIndex = $teachers->lastItem();
            @endphp

            <div class="pagination-section">
                <div class="pagination-info">
                    Menampilkan data <strong>{{ $startIndex ?? 0 }}</strong> – 
                    <strong>{{ $endIndex ?? 0 }}</strong> dari <strong>{{ $total }}</strong>
                </div>

                <div class="pagination-controls">
                    <a href="{{ $teachers->url(1) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == 1 ? 'disabled onclick="return false;"' : '' }}>&laquo;</a>

                    <a href="{{ $teachers->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == 1 ? 'disabled onclick="return false;"' : '' }}>&lt;</a>

                    <span class="pagination-separator">|</span>

                    @php
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $teachers->url(1) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">1</a>
                        @if($start > 2)
                            <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                        @endif
                    @endif

                    @for($page = $start; $page <= $end; $page++)
                        @if($page == $currentPage)
                            <button class="pagination-btn active" aria-current="page" aria-disabled="true" tabindex="-1">{{ $page }}</button>
                        @else
                            <a href="{{ $teachers->url($page) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                        @endif
                        <a href="{{ $teachers->url($lastPage) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">{{ $lastPage }}</a>
                    @endif

                    <span class="pagination-separator">|</span>

                    <a href="{{ $teachers->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == $lastPage ? 'disabled onclick="return false;"' : '' }}>&gt;</a>

                    <a href="{{ $teachers->url($lastPage) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == $lastPage ? 'disabled onclick="return false;"' : '' }}>&raquo;</a>
                </div>
            </div>

        @else
            <div class="alert alert-info text-center">
                <p class="mb-0">Belum ada data guru. <a href="{{ route('admin.teachers.create') }}">Tambah sekarang</a></p>
            </div>
        @endif
    </div>
</div>

<script src="{{ asset('js/action-dropdown.js') }}"></script>

<script>
// Real-time teacher search (AJAX) with debounce
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('nameSearch');
    const nipInput = document.getElementById('nipSearch');
    const emailInput = document.getElementById('emailSearch');
    const subjectSelect = document.getElementById('subjectSearch');
    const tbody = document.getElementById('teachersTableBody');
    const paginationSection = document.querySelector('.pagination-section');
    let timeout = null;

    function renderRows(items) {
        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data guru.</td></tr>';
            return;
        }
        tbody.innerHTML = items.map((t, idx) => `
            <tr>
                <td>${idx + 1}</td>
                <td><strong>${t.nip ?? '-'}</strong></td>
                <td>${t.name ?? '-'}</td>
                <td style="text-align:center">${t.email ? (t.email.length > 20 ? t.email.substring(0,5) + '...' + t.email.substring(t.email.indexOf('@')) : t.email) : '-'}</td>
                <td class="text-center">${t.gender === 'L' ? 'L' : 'P'}</td>
                <td style="text-align:center; vertical-align:middle;">
                    <div class="action-menu-container" style="position: relative; display: inline-block;">
                        <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)">
                            <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                        </button>
                        <ul class="dropdown-menu" style="display: none;">
                            <li><a class="dropdown-item" href="/admin/teachers/${t.id}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                Lihat Detail
                            </a></li>
                            <li><a class="dropdown-item" href="/admin/teachers/${t.id}/edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Edit
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="/admin/teachers/${t.id}" class="dropdown-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus guru ini?')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async function fetchTeachers(params) {
        const url = new URL('{{ route("admin.teachers.index") }}');
        if (params.name) url.searchParams.set('name', params.name);
        if (params.nip) url.searchParams.set('nip', params.nip);
        if (params.email) url.searchParams.set('email', params.email);
        if (params.subject_id) url.searchParams.set('subject_id', params.subject_id);
        
        // Hide pagination when actively filtering
        const isFiltering = params.name || params.nip || params.email || params.subject_id;
        if (paginationSection) {
            paginationSection.style.display = isFiltering ? 'none' : 'block';
        }
        
        // Show loading state
        tbody.innerHTML = '<tr><td colspan="6" class="text-center"><span class="spinner-border spinner-border-sm me-2"></span>Memuat data...</td></tr>';
        
        try {
            const res = await fetch(url.toString(), {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const json = await res.json();
            if (json.success) {
                renderRows(json.data);
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>';
            }
        } catch (e) {
            console.error('Search failed', e);
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Terjadi kesalahan saat memuat data</td></tr>';
        }
    }

    function scheduleFetch() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const name = nameInput.value.trim();
            const nip = nipInput.value.trim();
            const email = emailInput.value.trim();
            const subject_id = subjectSelect.value;

            // Always fetch - if filters are empty, backend will return all teachers
            fetchTeachers({ name, nip, email, subject_id });
        }, 250);
    }

    [nameInput, nipInput, emailInput, subjectSelect].forEach(el => {
        el.addEventListener('input', scheduleFetch);
        el.addEventListener('change', scheduleFetch);
    });
});
</script>

<!-- Teacher import modal -->
<div id="importTeacherModal" class="simple-modal" aria-hidden="true">
    <div class="simple-modal-backdrop" data-modal-close></div>
    <div class="simple-modal-dialog">
        <div class="simple-modal-header">
            <h5>Import Akun Guru dari Excel</h5>
            <button type="button" class="simple-modal-close" data-modal-close>&times;</button>
        </div>
        <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="simple-modal-body">
                <div class="mb-3">
                    <label for="import_file_teachers" class="form-label">Pilih File Excel</label>
                    <input class="form-control" type="file" id="import_file_teachers" name="file" accept=".xlsx,.xls" required>
                    <div class="form-text">Format: .xlsx atau .xls (maksimal 10 MB)</div>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.teachers.download-template') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download"></i> Download Template Excel (10 Contoh Data)
                    </a>
                </div>
            </div>
            <div class="simple-modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload"></i> Upload & Import
                </button>
            </div>
        </form>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const importBtn = document.querySelector('[data-bs-toggle="modal"][data-bs-target="#importTeacherModal"]') || null;
    const modal = document.getElementById('importTeacherModal');
    if (!modal) return;
    function openModal() { modal.classList.add('show'); modal.setAttribute('aria-hidden','false'); document.body.style.overflow = 'hidden'; }
    function closeModal() { modal.classList.remove('show'); modal.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; }
    if (importBtn) importBtn.addEventListener('click', function (e) { e.preventDefault(); openModal(); });
    modal.querySelectorAll('[data-modal-close]').forEach(function (el) { el.addEventListener('click', closeModal); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
    @if(request()->get('show_import'))
        openModal();
    @endif
});
</script>
@endsection
