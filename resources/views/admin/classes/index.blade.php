@extends('layouts.app')

@section('title', 'Data Kelas - SekolahKu Admin')

@section('page-title', 'Data Kelas')

@section('css')
<link rel="stylesheet" href="{{ asset('css/classes.css') }}">
<link rel="stylesheet" href="{{ asset('css/action-dropdown.css') }}">
<style>
/* Pagination Styles (copied from students) */
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
        <h5 class="mb-0">Daftar Data Kelas</h5>
        <a href="{{ route('admin.classes.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Kelas
        </a>
    </div>
    <div class="card-body">
        @if ($classes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-compact">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Kelas</th>
                            <th>Wali Kelas</th>
                            <th style="text-align: center;">Jumlah Siswa</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classes as $key => $class)
                            <tr>
                                <td>{{ $classes->firstItem() + $key }}</td>
                                <td><strong>{{ trim($class->class . ' ' . ($class->major ?? '')) }}</strong></td>
                                <td>{{ $class->homeroomTeacher?->name ?? '-' }}</td>
                                <td class="text-center">{{ $class->students->count() }}</td>
                                <td>
                                    <div class="action-menu-container" style="position: relative;">
                                        <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                            <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                                        </button>
                                        <ul class="dropdown-menu" style="display: none;">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.classes.show', $class->id) }}">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    Lihat Detail
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.classes.edit', $class->id) }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.classes.destroy', $class->id) }}" class="dropdown-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')">
                                                        <i class="bi bi-trash"></i> Hapus
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

            <!-- Pagination -->
            @php
                $currentPage = $classes->currentPage();
                $lastPage = $classes->lastPage();
                $total = $classes->total();
                $perPage = $classes->perPage();
                $startIndex = $classes->firstItem();
                $endIndex = $classes->lastItem();
            @endphp

            <div class="pagination-section">
                <div class="pagination-info">
                    Menampilkan data <strong>{{ $startIndex ?? 0 }}</strong> – 
                    <strong>{{ $endIndex ?? 0 }}</strong> dari <strong>{{ $total }}</strong>
                </div>

                <div class="pagination-controls">
                    <a href="{{ $classes->url(1) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == 1 ? 'disabled onclick="return false;"' : '' }}>&laquo;</a>

                    <a href="{{ $classes->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == 1 ? 'disabled onclick="return false;"' : '' }}>&lt;</a>

                    <span class="pagination-separator">|</span>

                    @php
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $classes->url(1) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">1</a>
                        @if($start > 2)
                            <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                        @endif
                    @endif

                    @for($page = $start; $page <= $end; $page++)
                        @if($page == $currentPage)
                            <button class="pagination-btn active" aria-current="page" aria-disabled="true" tabindex="-1">{{ $page }}</button>
                        @else
                            <a href="{{ $classes->url($page) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                        @endif
                        <a href="{{ $classes->url($lastPage) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">{{ $lastPage }}</a>
                    @endif

                    <span class="pagination-separator">|</span>

                    <a href="{{ $classes->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == $lastPage ? 'disabled onclick="return false;"' : '' }}>&gt;</a>

                    <a href="{{ $classes->url($lastPage) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn" {{ $currentPage == $lastPage ? 'disabled onclick="return false;"' : '' }}>&raquo;</a>
                </div>
            </div>

            <!-- Total Count -->
        @else
            <div class="alert alert-info text-center">
                <p class="mb-0">Belum ada data kelas. <a href="{{ route('admin.classes.create') }}">Tambah sekarang</a></p>
            </div>
        @endif
    </div>
</div>

<script src="{{ asset('js/action-dropdown.js') }}"></script>
@endsection
