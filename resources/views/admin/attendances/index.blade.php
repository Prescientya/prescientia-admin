@extends('layouts.app')

@section('title', 'Data Absensi - SekolahKu Admin')

@section('page-title', 'Data Absensi')

@section('css')
<link rel="stylesheet" href="{{ asset('css/action-dropdown.css') }}">
<style>
.attendance-tabs {
    display: flex;
    border-bottom: 2px solid var(--color-border);
    margin-bottom: 1.5rem;
    gap: 0.5rem;
}

.tab-button {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 0.9375rem;
    font-weight: 500;
    color: var(--color-text-light);
    transition: all 0.2s;
}

.tab-button:hover {
    color: var(--color-link);
    background-color: rgba(245, 48, 3, 0.05);
}

.tab-button.active {
    color: var(--color-link);
    border-bottom-color: var(--color-link);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    padding: 1.25rem;
    border-radius: var(--radius-lg);
    border-left: 4px solid var(--color-link);
    box-shadow: var(--shadow-sm);
}

.stat-card.success { border-left-color: var(--color-success); }
.stat-card.warning { border-left-color: var(--color-warning); }
.stat-card.danger { border-left-color: var(--color-danger); }
.stat-card.info { border-left-color: var(--color-info); }

.stat-card h4 {
    font-size: 2rem;
    margin: 0 0 0.25rem 0;
    color: var(--color-text-dark);
}

.stat-card p {
    margin: 0;
    color: var(--color-text-light);
    font-size: 0.875rem;
}

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

.date-info {
    background: linear-gradient(135deg, var(--color-link), #c73d00);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: var(--radius-lg);
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.date-info h3 {
    margin: 0;
    font-size: 1.25rem;
}

.date-info p {
    margin: 0.25rem 0 0 0;
    opacity: 0.9;
}

.calendar-status {
    padding: 0.375rem 0.875rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-full);
    font-weight: 500;
}
</style>
@endsection

@section('content')
<!-- Date & Calendar Status Info -->
<div class="date-info">
    <div>
        <h3>{{ \Carbon\Carbon::today()->isoFormat('dddd, D MMMM YYYY') }}</h3>
        <p>Data absensi hari ini</p>
    </div>
    @if($calendar)
        <span class="calendar-status">
            {{ $calendar->status === 'aktif' ? '📚 Hari Aktif' : '🏖️ Hari Libur' }}
        </span>
    @else
        <span class="calendar-status">⚠️ Kalender belum tersedia</span>
    @endif
</div>

<!-- Unified Attendance view: single table with filters -->
        <div class="card">
    <div class="card-body">
        <div class="mb-3">
            <form id="filter-form" class="row g-2">
                <div class="col-md-2">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">Semua</option>
                        <option value="students" {{ request('role')=='students' ? 'selected' : '' }}>Siswa</option>
                        <option value="teachers" {{ request('role')=='teachers' ? 'selected' : '' }}>Guru</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="hadir" {{ request('status')=='hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="sakit" {{ request('status')=='sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin" {{ request('status')=='izin' ? 'selected' : '' }}>Izin</option>
                        <option value="alpa" {{ request('status')=='alpa' ? 'selected' : '' }}>Alpa</option>
                        <option value="dinas" {{ request('status')=='dinas' ? 'selected' : '' }}>Dinas</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cari (nama / NIS / NIP)</label>
                    <input type="text" name="keyword" class="form-control" placeholder="Ketik nama atau NIS/NIP" value="{{ request('keyword') ?? '' }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="clear-filters" class="btn btn-outline-secondary w-100">Clear</button>
                </div>
            </form>
        </div>

        <div id="attendance-table-container">
            @include('admin.attendances._table', ['attendances' => $paginator])
        </div>
    </div>

@endsection

@section('scripts')
<script>
// Debounced AJAX filter: submit filters and update table in realtime
(() => {
    const form = document.getElementById('filter-form');
    const container = document.getElementById('attendance-table-container');
    let timeout = null;

    function fetchTable() {
        const params = new URLSearchParams(new FormData(form));
        params.set('ajax', '1');
        fetch(window.location.pathname + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => { container.innerHTML = html; })
        .catch(() => { /* ignore errors for now */ });
    }

    // Delegate pagination clicks inside the container to use AJAX
    container.addEventListener('click', function(e) {
        const a = e.target.closest('a');
        if (!a) return;
        // only intercept pagination / last-button links
        if (a.closest('.pagination') || a.hasAttribute('data-last-page')) {
            e.preventDefault();
            const url = new URL(a.href);
            url.searchParams.set('ajax', '1');
            fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => { container.innerHTML = html; window.scrollTo({ top: 0, behavior: 'smooth' }); })
                .catch(() => {});
        }
    });

    form.addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(fetchTable, 350);
    });

    document.getElementById('clear-filters').addEventListener('click', () => {
        form.reset();
        fetchTable();
    });
})();
</script>
<script src="{{ asset('js/action-dropdown.js') }}"></script>
@endsection

