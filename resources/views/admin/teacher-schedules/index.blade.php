@extends('layouts.app')

@section('title', 'Atur Jadwal Mengajar Guru - Admin')

@section('page-title', 'Atur Jadwal Mengajar Guru')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Form Section -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tambah Jadwal Mengajar</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.teacher-schedules.bulk') }}" method="POST" id="scheduleForm">
                        @csrf

                        <div class="mb-3">
                            <label for="teacher_id" class="form-label">Guru <span class="text-danger">*</span></label>
                            <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">
                                    {{ $class->class }} {{ $class->major }}
                                </option>
                                @endforeach
                            </select>
                            @error('class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="subject_id" class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="day" class="form-label">Hari <span class="text-danger">*</span></label>
                            <select name="day" id="day" class="form-select @error('day') is-invalid @enderror" required>
                                <option value="">-- Pilih Hari --</option>
                                @foreach($days as $day)
                                <option value="{{ $day }}">{{ ucfirst($day) }}</option>
                                @endforeach
                            </select>
                            @error('day')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                            <select name="semester" id="semester" class="form-select @error('semester') is-invalid @enderror" required>
                                <option value="">-- Pilih Semester --</option>
                                @foreach($semesters as $sem)
                                <option value="{{ $sem }}">Semester {{ $sem }}</option>
                                @endforeach
                            </select>
                            @error('semester')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jam Pelajaran <span class="text-danger">*</span></label>
                            <div id="periodCheckboxes" class="border rounded p-3">
                                <p class="text-muted">Pilih hari terlebih dahulu</p>
                            </div>
                            @error('period_ids')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Simpan Jadwal
                        </button>
                    </form>
                </div>
            </div>

            <!-- Import Section -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Import dari Excel</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.teacher-schedules.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">File Excel</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls" required>
                        </div>
                        <button type="submit" class="btn btn-info w-100">
                            <i class="fas fa-upload"></i> Import
                        </button>
                    </form>
                    <hr>
                    <a href="{{ route('admin.teacher-schedules.download-template') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>
            </div>
        </div>

        <!-- List Section -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Jadwal Mengajar</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Guru</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Hari</th>
                                    <th>Jam</th>
                                    <th>Semester</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->teacher->name }}</td>
                                    <td>{{ $schedule->class->class }} {{ $schedule->class->major }}</td>
                                    <td>{{ $schedule->subject->name }}</td>
                                    <td>{{ ucfirst($schedule->day) }}</td>
                                    <td>
                                        {{ $schedule->period->sequence }} 
                                        <small class="text-muted">({{ $schedule->period->start_time }}-{{ $schedule->period->end_time }})</small>
                                    </td>
                                    <td>{{ $schedule->semester }}</td>
                                    <td>
                                        <form action="{{ route('admin.teacher-schedules.destroy', $schedule->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada jadwal</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $schedules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('day').addEventListener('change', function() {
    const day = this.value;
    const periodCheckboxes = document.getElementById('periodCheckboxes');
    
    if (!day) {
        periodCheckboxes.innerHTML = '<p class="text-muted mb-0">Pilih hari terlebih dahulu</p>';
        return;
    }
    
    // Fetch periods for this day
    fetch(`/admin/teacher-schedules/periods?day=${day}`)
        .then(response => response.json())
        .then(data => {
            if (data.periods && data.periods.length > 0) {
                let html = '';
                data.periods.forEach(period => {
                    html += `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="period_ids[]" value="${period.id}" id="period_${period.id}">
                            <label class="form-check-label" for="period_${period.id}">
                                ${period.sequence} — ${period.start_time} – ${period.end_time}
                            </label>
                        </div>
                    `;
                });
                periodCheckboxes.innerHTML = html;
            } else {
                periodCheckboxes.innerHTML = '<p class="text-muted mb-0">Tidak ada jam pelajaran untuk hari ini</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            periodCheckboxes.innerHTML = '<p class="text-danger mb-0">Error loading periods</p>';
        });
});
</script>
@endsection
