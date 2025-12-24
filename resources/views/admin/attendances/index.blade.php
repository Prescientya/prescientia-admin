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

<!-- Tabs Navigation -->
<div class="card">
    <div class="card-body">
        <div class="attendance-tabs">
            <button class="tab-button active" onclick="switchTab('students', event)">
                👨‍🎓 Absensi Siswa
            </button>
            <button class="tab-button" onclick="switchTab('teachers', event)">
                👨‍🏫 Absensi Guru
            </button>
            <button class="tab-button" onclick="switchTab('history', event)">
                📜 History / Filter
            </button>
        </div>

        <!-- Students Tab Content -->
        <div id="students-tab" class="tab-content active">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card success">
                    <h4>{{ $studentStats['hadir'] }}</h4>
                    <p>Hadir</p>
                </div>
                <div class="stat-card warning">
                    <h4>{{ $studentStats['sakit'] }}</h4>
                    <p>Sakit</p>
                </div>
                <div class="stat-card info">
                    <h4>{{ $studentStats['izin'] }}</h4>
                    <p>Izin</p>
                </div>
                <div class="stat-card danger">
                    <h4>{{ $studentStats['alpa'] }}</h4>
                    <p>Alpa</p>
                </div>
                <div class="stat-card">
                    <h4>{{ $totalStudents - ($studentStats['hadir'] + $studentStats['sakit'] + $studentStats['izin'] + $studentStats['alpa']) }}</h4>
                    <p>Belum Absen</p>
                </div>
            </div>

            <!-- Students Table -->
            @if($studentAttendances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Sumber</th>
                                <th style="width:80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentAttendances as $key => $attendance)
                                <tr>
                                    <td>{{ $studentAttendances->firstItem() + $key }}</td>
                                    <td>{{ $attendance->student->name }}</td>
                                    <td>{{ $attendance->class->class ?? '-' }} {{ $attendance->class->major ?? '' }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $attendance->status }}">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                    <td><small>{{ $attendance->source ? str_replace('_', ' ', ucfirst($attendance->source)) : '-' }}</small></td>
                                    <td>
                                        <div class="action-menu-container">
                                            <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                                <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                                            </button>
                                            <ul class="dropdown-menu" style="display: none;">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.attendances.show', ['role' => 'students', 'id' => $attendance->id]) }}">
                                                        Lihat Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.attendances.show', ['role' => 'students', 'id' => $attendance->id]) }}">
                                                        Edit
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $studentAttendances->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    <p class="mb-0">Belum ada data absensi siswa untuk hari ini</p>
                </div>
            @endif
        </div>

        <!-- Teachers Tab Content -->
        <div id="teachers-tab" class="tab-content">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card success">
                    <h4>{{ $teacherStats['hadir'] }}</h4>
                    <p>Hadir</p>
                </div>
                <div class="stat-card warning">
                    <h4>{{ $teacherStats['sakit'] }}</h4>
                    <p>Sakit</p>
                </div>
                <div class="stat-card info">
                    <h4>{{ $teacherStats['izin'] }}</h4>
                    <p>Izin</p>
                </div>
                <div class="stat-card" style="border-left-color: #6c757d;">
                    <h4>{{ $teacherStats['dinas'] }}</h4>
                    <p>Dinas</p>
                </div>
                <div class="stat-card danger">
                    <h4>{{ $totalTeachers - ($teacherStats['hadir'] + $teacherStats['sakit'] + $teacherStats['izin'] + $teacherStats['dinas']) }}</h4>
                    <p>Belum Absen</p>
                </div>
            </div>

            <!-- Teachers Table -->
            @if($teacherAttendances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Guru</th>
                                <th>Status</th>
                                <th>Sumber</th>
                                <th style="width:80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teacherAttendances as $key => $attendance)
                                <tr>
                                    <td>{{ $teacherAttendances->firstItem() + $key }}</td>
                                    <td>{{ $attendance->teacher->name }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $attendance->status }}">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                    <td><small>{{ $attendance->source ? str_replace('_', ' ', ucfirst($attendance->source)) : '-' }}</small></td>
                                    <td>
                                        <div class="action-menu-container">
                                            <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                                <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                                            </button>
                                            <ul class="dropdown-menu" style="display: none;">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.attendances.show', ['role' => 'teachers', 'id' => $attendance->id]) }}">
                                                        Lihat Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.attendances.show', ['role' => 'teachers', 'id' => $attendance->id]) }}">
                                                        Edit
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $teacherAttendances->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    <p class="mb-0">Belum ada data absensi guru untuk hari ini</p>
                </div>
            @endif
        </div>

        <!-- History Tab Content -->
        <div id="history-tab" class="tab-content">
            <div class="mb-3">
                <form method="GET" action="{{ route('admin.attendances.history') }}" class="row g-2">
                    <div class="col-md-2">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="students" {{ (isset($role) && $role == 'students') ? 'selected' : '' }}>Siswa</option>
                            <option value="teachers" {{ (isset($role) && $role == 'teachers') ? 'selected' : '' }}>Guru</option>
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
                            <option value="">Semua</option>
                            <option value="hadir" {{ (isset($status) && $status == 'hadir') ? 'selected' : '' }}>Hadir</option>
                            <option value="sakit" {{ (isset($status) && $status == 'sakit') ? 'selected' : '' }}>Sakit</option>
                            <option value="izin" {{ (isset($status) && $status == 'izin') ? 'selected' : '' }}>Izin</option>
                            <option value="alpa" {{ (isset($status) && $status == 'alpa') ? 'selected' : '' }}>Alpa</option>
                            <option value="dinas" {{ (isset($status) && $status == 'dinas') ? 'selected' : '' }}>Dinas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cari (nama / NIS / NIP)</label>
                        <input type="text" name="keyword" class="form-control" placeholder="Ketik nama atau NIS/NIP" value="{{ $keyword ?? '' }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>

            @if(isset($results) && $results->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                @if((isset($role) && $role=='teachers') || (!isset($role) && request('role')=='teachers'))
                                    <th>Nama Guru</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                @else
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                @endif
                                <th>Sumber</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $k => $r)
                                <tr>
                                    <td>{{ $results->firstItem() + $k }}</td>
                                    @if((isset($role) && $role=='teachers') || (!isset($role) && request('role')=='teachers'))
                                        <td>{{ $r->teacher->name }}</td>
                                        <td>{{ optional($r->calendar)->date ? \Carbon\Carbon::parse($r->calendar->date)->format('Y-m-d') : '-' }}</td>
                                        <td><span class="status-badge status-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                                    @else
                                        <td>{{ $r->student->name }}</td>
                                        <td>{{ $r->class->class ?? '-' }} {{ $r->class->major ?? '' }}</td>
                                        <td>{{ optional($r->calendar)->date ? \Carbon\Carbon::parse($r->calendar->date)->format('Y-m-d') : '-' }}</td>
                                        <td><span class="status-badge status-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                                    @endif
                                    <td><small>{{ $r->source ? str_replace('_', ' ', ucfirst($r->source)) : '-' }}</small></td>
                                    <td>
                                        <div class="action-menu-container">
                                            <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                                <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                                            </button>
                                            <ul class="dropdown-menu" style="display: none;">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.attendances.show', ['role' => $role ?? 'students', 'id' => $r->id]) }}">
                                                        Lihat Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.attendances.show', ['role' => $role ?? 'students', 'id' => $r->id]) }}">
                                                        Edit
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $results->appends(request()->except('history_page'))->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    <p class="mb-0">Belum ada data history absensi untuk filter ini</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchTab(tabName, evt) {
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Add active class to clicked tab
    if (evt && evt.currentTarget) {
        evt.currentTarget.classList.add('active');
    } else if (evt && evt.target) {
        evt.target.classList.add('active');
    }
    document.getElementById(tabName + '-tab').classList.add('active');
}
</script>
<script src="{{ asset('js/action-dropdown.js') }}"></script>
@endsection

