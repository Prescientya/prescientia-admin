@extends('layouts.app')

@section('title', 'Data Siswa - SekolahKu Admin')

@section('page-title', 'Data Siswa')

@section('css')
<link rel="stylesheet" href="{{ asset('css/action-dropdown.css') }}">
<style>
/* Pagination Styles (same as other lists) */
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
    background-color: #ff4d00 !important;
    color: #ffffff !important;
    border-color: #ff4d00 !important;
    font-weight: 800 !important;
    box-shadow: 0 6px 16px rgba(255, 77, 0, 0.18);
}

.pagination-btn.active:hover { background-color: #ff3b00 !important; border-color: #ff3b00 !important; }
.pagination-btn:focus { outline: none; box-shadow: 0 0 0 5px rgba(255,77,0,0.14); }
.pagination-btn:disabled { opacity: 0.4; cursor: not-allowed; color: #999; }
.pagination-separator { color: #ccc; margin: 0 0.25rem; }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Data Siswa</h5>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-secondary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                Import Excel
            </button>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-sm">
                Tambah Siswa
            </a>
        </div>
    </div>
    <div class="card-body">
        @if ($students->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-compact">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th style="text-align: center">Email</th>
                            <th style="text-align: center">Kelas</th>
                            <th style="text-align: center">Jenis Kelamin</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $key => $student)
                            <tr>
                                <td>{{ $students->firstItem() + $key }}</td>
                                <td><strong>{{ $student->nis }}</strong></td>
                                <td>{{ $student->name }}</td>
                                <td>
                                    @if($student->user?->email)
                                        {{ preg_replace('/^(.{5}).+(@.+)$/', '$1...$2', $student->user->email) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $student->class ? $student->class->class . ' ' . $student->class->major : '-' }}</td>
                                <td class="text-center">{{ $student->gender === 'L' ? 'L' : 'P' }}</td>
                                <td>
                                    <div class="action-menu-container">
                                        <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                            <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                                        </button>
                                        <ul class="dropdown-menu" style="display: none;">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.students.show', $student->id) }}">
                                                    Lihat Detail
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.students.edit', $student->id) }}">
                                                    Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" class="dropdown-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus siswa ini?')">
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
                $currentPage = $students->currentPage();
                $lastPage = $students->lastPage();
                $total = $students->total();
                $perPage = $students->perPage();
                $startIndex = $students->firstItem();
                $endIndex = $students->lastItem();
            @endphp

            <div class="pagination-section">
                <div class="pagination-info">
                    Menampilkan data <strong>{{ $startIndex ?? 0 }}</strong> – 
                    <strong>{{ $endIndex ?? 0 }}</strong> dari <strong>{{ $total }}</strong>
                </div>

                <div class="pagination-controls">
                    <a href="{{ $students->url(1) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == 1 ? 'disabled onclick="return false;"' : '' }}>&laquo;</a>

                    <a href="{{ $students->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == 1 ? 'disabled onclick="return false;"' : '' }}>&lt;</a>

                    <span class="pagination-separator">|</span>

                    @php
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $students->url(1) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">1</a>
                        @if($start > 2)
                            <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                        @endif
                    @endif

                    @for($page = $start; $page <= $end; $page++)
                        @if($page == $currentPage)
                            <button class="pagination-btn active" aria-current="page" aria-disabled="true" tabindex="-1">{{ $page }}</button>
                        @else
                            <a href="{{ $students->url($page) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                        @endif
                        <a href="{{ $students->url($lastPage) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">{{ $lastPage }}</a>
                    @endif

                    <span class="pagination-separator">|</span>

                    <a href="{{ $students->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == $lastPage ? 'disabled onclick="return false;"' : '' }}>&gt;</a>

                    <a href="{{ $students->url($lastPage) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == $lastPage ? 'disabled onclick="return false;"' : '' }}>&raquo;</a>
                </div>
            </div>
        @else
            <div class="alert alert-info text-center">
                <p class="mb-0">Belum ada data siswa. <a href="{{ route('admin.students.create') }}">Tambah sekarang</a></p>
            </div>
        @endif
    </div>
</div>

<script src="{{ asset('js/action-dropdown.js') }}"></script>

<!-- Simple modal (no Bootstrap JS dependency) -->
<div id="importModal" class="simple-modal" aria-hidden="true">
        <div class="simple-modal-backdrop" data-modal-close></div>
        <div class="simple-modal-dialog">
                <div class="simple-modal-header">
                        <h5>Import Siswa dari Excel</h5>
                        <button type="button" class="simple-modal-close" data-modal-close>&times;</button>
                </div>
                <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="simple-modal-body">
                            <div class="mb-3">
                                <input class="form-control" type="file" id="import_file" name="file" accept=".xlsx,.xls" required>
                            </div>
                            <div class="mb-2">
                                <a href="{{ route('admin.students.download-template') }}" class="btn btn-sm btn-outline-secondary" download>
                                    <i class="bi bi-download"></i> Download Template Excel
                                </a>
                            </div>
                        </div>
                        <div class="simple-modal-footer">
                                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                                <button type="submit" class="btn btn-primary">Upload & Import</button>
                        </div>
                </form>
        </div>
</div>

<style>
/* Simple modal styles */
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
// Simple modal open/close logic
document.addEventListener('DOMContentLoaded', function () {
        const importBtn = document.querySelector('[data-bs-toggle="modal"][data-bs-target="#importModal"]') || document.querySelector('.btn[data-bs-target="#importModal"]');
        const modal = document.getElementById('importModal');
        if (!modal) return;

        function openModal() { modal.classList.add('show'); modal.setAttribute('aria-hidden','false'); document.body.style.overflow = 'hidden'; }
        function closeModal() { modal.classList.remove('show'); modal.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; }

        // open trigger
        if (importBtn) importBtn.addEventListener('click', function (e) { e.preventDefault(); openModal(); });

        // Auto-open when requested via query param
        @if(request()->get('show_import'))
            openModal();
        @endif

        // close triggers
        modal.querySelectorAll('[data-modal-close]').forEach(function (el) { el.addEventListener('click', closeModal); });

        // close on ESC
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
});
</script>
@endsection
