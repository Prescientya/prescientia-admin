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
            <!-- Export Button -->
            <div class="mb-3 d-flex justify-content-end">
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

                @php
                    $currentPage = $paginator->currentPage();
                    $lastPage = $paginator->lastPage();
                    $total = $paginator->total();
                    $perPage = $paginator->perPage();
                    $startIndex = $paginator->firstItem();
                    $endIndex = $paginator->lastItem();
                @endphp

                <div class="pagination-section">
                    <div class="pagination-info">
                        Menampilkan data <strong>{{ $startIndex ?? 0 }}</strong> – 
                        <strong>{{ $endIndex ?? 0 }}</strong> dari <strong>{{ $total }}</strong>
                    </div>

                    <div class="pagination-controls">
                        <!-- First -->
                        <a href="{{ $paginator->url(1) }}&{{ http_build_query(request()->except('page')) }}"
                           class="pagination-btn"
                           {{ $currentPage == 1 ? 'disabled onclick="return false;"' : '' }}>&laquo;</a>

                        <!-- Previous -->
                        <a href="{{ $paginator->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                           class="pagination-btn"
                           {{ $currentPage == 1 ? 'disabled onclick="return false;"' : '' }}>&lt;</a>

                        <span class="pagination-separator">|</span>

                        @php
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $paginator->url(1) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">1</a>
                            @if($start > 2)
                                <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            @if($page == $currentPage)
                                <button class="pagination-btn active" aria-current="page" aria-disabled="true" tabindex="-1">{{ $page }}</button>
                            @else
                                <a href="{{ $paginator->url($page) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">{{ $page }}</a>
                            @endif
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="pagination-btn" style="border: none; background: none; cursor: default;">...</span>
                            @endif
                            <a href="{{ $paginator->url($lastPage) }}&{{ http_build_query(request()->except('page')) }}" class="pagination-btn">{{ $lastPage }}</a>
                        @endif

                        <span class="pagination-separator">|</span>

                        <!-- Next -->
                        <a href="{{ $paginator->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                           class="pagination-btn"
                           {{ $currentPage == $lastPage ? 'disabled onclick="return false;"' : '' }}>&gt;</a>

                        <!-- Last -->
                        <a href="{{ $paginator->url($lastPage) }}&{{ http_build_query(request()->except('page')) }}"
                           class="pagination-btn"
                           {{ $currentPage == $lastPage ? 'disabled onclick="return false;"' : '' }}>&raquo;</a>
                    </div>
                </div>
        @else
            <div class="alert alert-info text-center">Belum ada data absensi guru untuk filter ini</div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
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
</script>
<script src="{{ asset('js/action-dropdown.js') }}"></script>
@endsection
