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
                <div class="col-md-2">
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
                <div class="col-md-2">
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
                <div class="col-md-2">
                    <label class="form-label">Dari</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ $filters['status'] == 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="sakit" {{ $filters['status'] == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin" {{ $filters['status'] == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="alpa" {{ $filters['status'] == 'alpa' ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="clear-filters" class="btn btn-outline-secondary w-100">Reset</button>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Cari (Nama / NIS)</label>
                    <input type="text" name="keyword" class="form-control" placeholder="Ketik nama atau NIS" value="{{ $filters['keyword'] ?? '' }}">
                </div>
            </form>
        </div>

        @if($records->count() > 0)
            <!-- Export Button -->
            <div class="mb-3 d-flex justify-content-end">
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
@endsection

@section('scripts')
<script>
(() => {
    const form = document.getElementById('filter-form');

    // Form submission on any field change
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
