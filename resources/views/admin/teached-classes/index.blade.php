@extends('layouts.app')

@section('title', 'Manajemen Guru Mengajar - SekolahKu Admin')

@section('page-title', 'Manajemen Guru Mengajar')

@section('css')
<style>
    .class-card {
        transition: all 0.3s ease;
    }
    .class-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .teachers-list {
        max-height: 300px;
        overflow-y: auto;
    }
    .teacher-badge {
        display: inline-block;
        margin-bottom: 5px;
    }
    .empty-state {
        text-align: center;
        padding: 30px;
        color: #999;
    }
    .card-header .badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.6rem;
    }
    
    /* Pagination Styles (same as classes page) */
    .pagination-section {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }
    
    /* Ensure 2 column grid layout */
    .classes-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .classes-grid {
            grid-template-columns: 1fr;
        }
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

    /* Autocomplete styles */
    .autocomplete-container {
        position: relative;
        width: 100%;
    }

    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }

    .autocomplete-dropdown.show {
        display: block;
    }

    .autocomplete-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s ease;
    }

    .autocomplete-item:hover,
    .autocomplete-item.highlighted {
        background-color: #f53003;
        color: white;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .filter-section {
        background-color: #f8f9fa;
        padding: 1.5rem;
        border-radius: 0.375rem;
        margin-bottom: 2rem;
    }

    .filter-input-group {
        position: relative;
    }

    .filter-input-group input {
        padding-right: 2.5rem;
    }

    .filter-clear-btn {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        padding: 0.25rem;
        font-size: 1.2rem;
        line-height: 1;
    }

    .filter-clear-btn:hover {
        color: #333;
    }
    /* Scoped simple-modal styles (same approach as Students/Teachers pages) */
    #importAssignmentModal.simple-modal { display: none; position: fixed; inset: 0; z-index: 1050; }

    /* Tooltip for unassigned subjects icon */
    .unassigned-icon {
        display: inline-block;
        position: relative;
        margin-left: 6px;
        vertical-align: middle;
        cursor: help;
    }
    .unassigned-icon svg { display: block; }

    /* New tooltip rendered into <body> with fixed positioning so it never gets clipped by overflow parents */
    .unassigned-icon-tooltip {
        position: fixed;
        left: var(--tooltip-left, 50px);
        top: var(--tooltip-top, 50px);
        transform: translate(-50%, -100%);
        white-space: nowrap;
        background: rgba(0,0,0,0.85);
        color: #fff;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 0.85rem;
        z-index: 99999;
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.12s ease, transform 0.12s ease;
    }
    .unassigned-icon-tooltip.visible { opacity: 1; pointer-events: auto; }
    .unassigned-icon-tooltip .arrow {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid rgba(0,0,0,0.85);
        bottom: -6px;
    }
    #importAssignmentModal.simple-modal.show { display: block; }
    #importAssignmentModal .simple-modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.5); }
    #importAssignmentModal .simple-modal-dialog { position: relative; max-width: 600px; margin: 6% auto; background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    #importAssignmentModal .simple-modal-header { display:flex; justify-content:space-between; align-items:center; padding:16px; border-bottom:1px solid #eee; }
    #importAssignmentModal .simple-modal-body { padding:16px; }
    #importAssignmentModal .simple-modal-footer { padding:12px 16px; text-align:right; border-top:1px solid #eee; }
    #importAssignmentModal .simple-modal-close { background:none; border:0; font-size:20px; line-height:1; cursor:pointer; }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Daftar Kelas & Guru Pengajar</h5>
                    <small class="text-muted">
                        <i class="bi bi-calendar-check"></i> Semester {{ $currentSemester }} ({{ $semesterName }}) - Tahun Ajaran {{ $academicYear }}
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importAssignmentModal">
                        <i class="bi bi-file-earmark-arrow-up"></i> Import Excel Guru Mengajar
                    </button>
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Data Kelas
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="filterClass" class="form-label"><strong>Filter Kelas</strong></label>
                            <div class="autocomplete-container">
                                <input 
                                    type="text" 
                                    id="filterClass" 
                                    class="form-control filter-input-group" 
                                    placeholder="Ketik kelas... (contoh: 10, 11, 12)"
                                    autocomplete="off"
                                >
                                <button type="button" class="filter-clear-btn" id="clearClass" title="Hapus filter">×</button>
                                <div class="autocomplete-dropdown" id="classDropdown"></div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="filterMajor" class="form-label"><strong>Filter Jurusan</strong></label>
                            <div class="autocomplete-container">
                                <input 
                                    type="text" 
                                    id="filterMajor" 
                                    class="form-control filter-input-group" 
                                    placeholder="Ketik jurusan... (contoh: RPL, TKJ)"
                                    autocomplete="off"
                                >
                                <button type="button" class="filter-clear-btn" id="clearMajor" title="Hapus filter">×</button>
                                <div class="autocomplete-dropdown" id="majorDropdown"></div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($classes->count() > 0)
                    <div class="classes-grid">
                        @foreach($classes as $class)
                            <div class="card class-card h-100 position-relative">
                                <div class="card-header bg-primary text-black d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 d-flex align-items-center gap-2">
                                        Kelas {{ $class->class }} <span class="fw-normal">({{ $class->major ?? 'Umum' }})</span>
                                        @if($class->unassignedSubjects && $class->unassignedSubjects->count() > 0)
                                            @php
                                                $names = $class->unassignedSubjects->pluck('name');
                                                $first = $names->take(3)->implode(', ');
                                                $more = $names->count() - 3;
                                                $tooltip = $names->count() . ' Mapel belum dikelola: ' . $first;
                                                if ($more > 0) { $tooltip .= ', dan ' . $more . ' mapel lagi'; }
                                            @endphp
                                            <span class="unassigned-icon" data-tooltip="{{ $tooltip }}">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="color:#dc3545;" aria-hidden="true" focusable="false">
                                                    <path d="M7.001 .8a1 1 0 0 1 .998 0l6 3.5A1 1 0 0 1 15 5.1v5.8a1 1 0 0 1-.001.1l-6 3.5a1 1 0 0 1-.998 0l-6-3.5A1 1 0 0 1 1 10.9V5.1a1 1 0 0 1 .002-.8L7.001.8zM8 5.5a.75.75 0 0 0-.75.75v2.5c0 .414.336.75.75.75s.75-.336.75-.75v-2.5A.75.75 0 0 0 8 5.5zm0 6.25a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75z"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1"><strong>Wali Kelas:</strong></small>
                                        <small>{{ $class->homeroomTeacher ? $class->homeroomTeacher->name : 'Belum ada wali kelas' }}</small>
                                    </div>

                                    <div class="mb-0">
                                        <small class="text-muted d-block mb-1"><strong>📚 Guru Pengajar:</strong></small>
                                        <h4 class="mb-0">{{ $class->totalTeachers }} Guru</h4>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('admin.teached-classes.edit', $class->id) }}" class="btn btn-sm btn-primary w-100">
                                        <i class="bi bi-pencil"></i> Kelola Guru Pengajar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        <p class="mb-0">Belum ada data kelas. <a href="{{ route('admin.classes.index') }}">Kelola kelas</a></p>
                    </div>
                @endif

                <!-- Pagination -->
                @php
                    $currentPage = $classes->currentPage();
                    $lastPage = $classes->lastPage();
                    $total = $classes->total();
                    $perPage = $classes->perPage();
                    $startIndex = $classes->firstItem();
                    $endIndex = $classes->lastItem();
                @endphp

                @if($classes->hasPages())
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
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterClassInput = document.getElementById('filterClass');
    const filterMajorInput = document.getElementById('filterMajor');
    const classDropdown = document.getElementById('classDropdown');
    const majorDropdown = document.getElementById('majorDropdown');
    const clearClassBtn = document.getElementById('clearClass');
    const clearMajorBtn = document.getElementById('clearMajor');
    const classesGrid = document.querySelector('.classes-grid');
    
    let allClasses = [];
    let highlightedIndexClass = -1;
    let highlightedIndexMajor = -1;

    // Fetch all classes data on load
    async function loadAllClasses() {
        try {
            const response = await fetch('{{ route("admin.teached-classes.suggestions") }}');
            const data = await response.json();
            allClasses = data.classes || [];
        } catch (error) {
            console.error('Error loading classes:', error);
        }
    }

    // Filter classes based on input
    function filterClasses(input) {
        const filtered = allClasses.filter(cls => 
            cls.number.toString().includes(input) || 
            (cls.major && cls.major.toLowerCase().includes(input.toLowerCase()))
        );
        return filtered;
    }

    // Filter majors based on input
    function filterMajors(input) {
        const majors = [...new Set(allClasses.map(cls => cls.major).filter(m => m))];
        return majors.filter(major => 
            major.toLowerCase().includes(input.toLowerCase())
        );
    }

    // Display autocomplete suggestions for class
    function showClassSuggestions(suggestions) {
        classDropdown.innerHTML = '';
        highlightedIndexClass = -1;
        
        if (suggestions.length === 0) {
            classDropdown.classList.remove('show');
            return;
        }

        suggestions.forEach((cls, idx) => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.textContent = `Kelas ${cls.number}${cls.major ? ' - ' + cls.major : ''}`;
            item.dataset.index = idx;
            item.dataset.classId = cls.id;
            item.dataset.classNum = cls.number;
            item.dataset.major = cls.major || '';
            
            item.addEventListener('click', () => selectClass(cls));
            item.addEventListener('mouseenter', () => {
                document.querySelectorAll('#classDropdown .autocomplete-item').forEach(el => 
                    el.classList.remove('highlighted')
                );
                item.classList.add('highlighted');
                highlightedIndexClass = idx;
            });
            
            classDropdown.appendChild(item);
        });

        classDropdown.classList.add('show');
    }

    // Display autocomplete suggestions for major
    function showMajorSuggestions(suggestions) {
        majorDropdown.innerHTML = '';
        highlightedIndexMajor = -1;
        
        if (suggestions.length === 0) {
            majorDropdown.classList.remove('show');
            return;
        }

        suggestions.forEach((major, idx) => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.textContent = major;
            item.dataset.index = idx;
            item.dataset.major = major;
            
            item.addEventListener('click', () => selectMajor(major));
            item.addEventListener('mouseenter', () => {
                document.querySelectorAll('#majorDropdown .autocomplete-item').forEach(el => 
                    el.classList.remove('highlighted')
                );
                item.classList.add('highlighted');
                highlightedIndexMajor = idx;
            });
            
            majorDropdown.appendChild(item);
        });

        majorDropdown.classList.add('show');
    }

    // Select class from dropdown
    function selectClass(cls) {
        filterClassInput.value = `Kelas ${cls.number}${cls.major ? ' - ' + cls.major : ''}`;
        filterClassInput.dataset.classId = cls.id;
        classDropdown.classList.remove('show');
        applyFilters();
    }

    // Select major from dropdown
    function selectMajor(major) {
        filterMajorInput.value = major;
        majorDropdown.classList.remove('show');
        applyFilters();
    }

    // Apply filters to cards
    function applyFilters() {
        const classFilterValue = filterClassInput.value.toLowerCase();
        const majorFilterValue = filterMajorInput.value.toLowerCase();
        
        const cards = document.querySelectorAll('.classes-grid .class-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const classNumber = card.querySelector('.card-header h6').textContent.match(/\d+/)[0];
            const major = card.querySelector('.card-header small').textContent.toLowerCase();
            
            const matchClass = classFilterValue === '' || classNumber.includes(classFilterValue.replace('kelas', '').trim());
            const matchMajor = majorFilterValue === '' || major.includes(majorFilterValue);
            
            if (matchClass && matchMajor) {
                card.parentElement.style.display = '';
                visibleCount++;
            } else {
                card.parentElement.style.display = 'none';
            }
        });

        // Show "no results" message if needed
        if (visibleCount === 0 && (classFilterValue !== '' || majorFilterValue !== '')) {
            if (!document.getElementById('noResultsMsg')) {
                const msg = document.createElement('div');
                msg.id = 'noResultsMsg';
                msg.className = 'alert alert-warning text-center';
                msg.textContent = 'Tidak ada data yang sesuai dengan filter.';
                classesGrid.parentElement.insertBefore(msg, classesGrid);
            }
        } else {
            const noResultsMsg = document.getElementById('noResultsMsg');
            if (noResultsMsg) noResultsMsg.remove();
        }
    }

    // Class input listener
    filterClassInput.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value.length === 0) {
            classDropdown.classList.remove('show');
            clearClassBtn.style.display = 'none';
        } else {
            clearClassBtn.style.display = 'block';
            const suggestions = filterClasses(value.replace('kelas', '').trim());
            showClassSuggestions(suggestions);
        }

        applyFilters();
    });

    // Major input listener
    filterMajorInput.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value.length === 0) {
            majorDropdown.classList.remove('show');
            clearMajorBtn.style.display = 'none';
        } else {
            clearMajorBtn.style.display = 'block';
            const suggestions = filterMajors(value);
            showMajorSuggestions(suggestions);
        }

        applyFilters();
    });

    // Clear buttons
    clearClassBtn.addEventListener('click', () => {
        filterClassInput.value = '';
        classDropdown.classList.remove('show');
        clearClassBtn.style.display = 'none';
        applyFilters();
    });

    clearMajorBtn.addEventListener('click', () => {
        filterMajorInput.value = '';
        majorDropdown.classList.remove('show');
        clearMajorBtn.style.display = 'none';
        applyFilters();
    });

    // Keyboard navigation for class dropdown
    filterClassInput.addEventListener('keydown', function(e) {
        const items = document.querySelectorAll('#classDropdown .autocomplete-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedIndexClass = Math.min(highlightedIndexClass + 1, items.length - 1);
            if (items[highlightedIndexClass]) {
                items[highlightedIndexClass].classList.add('highlighted');
                if (highlightedIndexClass > 0) items[highlightedIndexClass - 1].classList.remove('highlighted');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedIndexClass = Math.max(highlightedIndexClass - 1, -1);
            if (highlightedIndexClass >= 0) {
                items[highlightedIndexClass].classList.add('highlighted');
                if (highlightedIndexClass < items.length - 1) items[highlightedIndexClass + 1].classList.remove('highlighted');
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedIndexClass >= 0 && items[highlightedIndexClass]) {
                items[highlightedIndexClass].click();
            }
        } else if (e.key === 'Escape') {
            classDropdown.classList.remove('show');
        }
    });

    // Keyboard navigation for major dropdown
    filterMajorInput.addEventListener('keydown', function(e) {
        const items = document.querySelectorAll('#majorDropdown .autocomplete-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedIndexMajor = Math.min(highlightedIndexMajor + 1, items.length - 1);
            if (items[highlightedIndexMajor]) {
                items[highlightedIndexMajor].classList.add('highlighted');
                if (highlightedIndexMajor > 0) items[highlightedIndexMajor - 1].classList.remove('highlighted');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedIndexMajor = Math.max(highlightedIndexMajor - 1, -1);
            if (highlightedIndexMajor >= 0) {
                items[highlightedIndexMajor].classList.add('highlighted');
                if (highlightedIndexMajor < items.length - 1) items[highlightedIndexMajor + 1].classList.remove('highlighted');
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedIndexMajor >= 0 && items[highlightedIndexMajor]) {
                items[highlightedIndexMajor].click();
            }
        } else if (e.key === 'Escape') {
            majorDropdown.classList.remove('show');
        }
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#filterClass') && !e.target.closest('#classDropdown')) {
            classDropdown.classList.remove('show');
        }
        if (!e.target.closest('#filterMajor') && !e.target.closest('#majorDropdown')) {
            majorDropdown.classList.remove('show');
        }
    });

    // Load all classes on page load
    loadAllClasses();
});
</script>

<!-- Import Assignment Modal (simple-modal like Students/Teachers) -->
<div id="importAssignmentModal" class="simple-modal" aria-hidden="true">
    <div class="simple-modal-backdrop" data-modal-close></div>
    <div class="simple-modal-dialog">
        <div class="simple-modal-header">
            <h5>Import Guru Mengajar dari Excel</h5>
            <button type="button" class="simple-modal-close" data-modal-close>&times;</button>
        </div>
        <form action="{{ route('admin.teached-classes.import-assignments') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="simple-modal-body">
                <div class="mb-3">
                    <input class="form-control" type="file" id="assignment_file" name="file" accept=".xlsx,.xls" required>
                </div>

                <div class="mb-2">
                    <a href="{{ route('admin.teached-classes.download-assignment-template') }}" class="btn btn-sm btn-outline-secondary" download>
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

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const importBtn = document.querySelector('[data-bs-toggle="modal"][data-bs-target="#importAssignmentModal"]') || document.querySelector('.btn[data-bs-target="#importAssignmentModal"]');
    const modal = document.getElementById('importAssignmentModal');
    if (!modal) return;

    function openModal() { modal.classList.add('show'); modal.setAttribute('aria-hidden','false'); document.body.style.overflow = 'hidden'; }
    function closeModal() { modal.classList.remove('show'); modal.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; }

    if (importBtn) importBtn.addEventListener('click', function (e) { e.preventDefault(); openModal(); });

    // Auto-open when requested via query param (optional)
    @if(request()->get('show_import'))
        openModal();
    @endif

    // close triggers
    modal.querySelectorAll('[data-modal-close]').forEach(function (el) { el.addEventListener('click', closeModal); });

    // close on ESC
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

    // Initialize unassigned-subjects tooltips: render into body with viewport clamping
    function initUnassignedTooltips() {
        const margin = 8;
        document.querySelectorAll('.unassigned-icon').forEach(function (el) {
            let tooltipEl = null;

            function createTooltip() {
                tooltipEl = document.createElement('div');
                tooltipEl.className = 'unassigned-icon-tooltip';
                const text = document.createElement('div');
                text.className = 'text';
                text.textContent = el.getAttribute('data-tooltip') || '';
                const arrow = document.createElement('div');
                arrow.className = 'arrow';
                tooltipEl.appendChild(text);
                tooltipEl.appendChild(arrow);
                document.body.appendChild(tooltipEl);
            }

            function show() {
                if (!tooltipEl) createTooltip();
                // ensure measured size
                tooltipEl.style.left = '0px';
                tooltipEl.style.top = '0px';
                tooltipEl.style.visibility = 'hidden';
                tooltipEl.classList.add('visible');

                const rect = el.getBoundingClientRect();
                const tw = tooltipEl.offsetWidth;
                const th = tooltipEl.offsetHeight;

                // center horizontally on icon, then clamp to viewport
                let left = rect.left + rect.width / 2;
                left = Math.max(margin + tw / 2, Math.min(left, window.innerWidth - margin - tw / 2));

                // prefer above, otherwise place below
                let placeAbove = rect.top >= (th + margin + 10);
                if (placeAbove) {
                    tooltipEl.style.transform = 'translate(-50%, -100%)';
                    tooltipEl.style.left = left + 'px';
                    tooltipEl.style.top = (rect.top - 8) + 'px';
                } else {
                    tooltipEl.style.transform = 'translate(-50%, 0%)';
                    tooltipEl.style.left = left + 'px';
                    tooltipEl.style.top = (rect.bottom + 8) + 'px';
                }

                tooltipEl.style.visibility = '';
                tooltipEl.classList.add('visible');
            }

            function hide() {
                if (tooltipEl) tooltipEl.classList.remove('visible');
            }

            el.addEventListener('mouseenter', show);
            el.addEventListener('mouseleave', hide);
            el.addEventListener('focus', show);
            el.addEventListener('blur', hide);

            el.addEventListener('click', function (e) {
                if (!tooltipEl || !tooltipEl.classList.contains('visible')) {
                    e.preventDefault();
                    show();
                    setTimeout(hide, 3000);
                }
            });

            // hide on scroll/resize to avoid stale positions
            window.addEventListener('scroll', hide, { passive: true });
            window.addEventListener('resize', hide);
        });
    }

    // small delay to ensure DOM fully ready
    setTimeout(initUnassignedTooltips, 80);
});
</script>
@endsection
