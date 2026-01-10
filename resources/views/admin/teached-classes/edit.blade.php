@extends('layouts.app')

@section('title', 'Kelola Guru Pengajar - SekolahKu Admin')

@section('page-title', 'Kelola Guru Pengajar')

@section('css')
<style>
    .teacher-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        border-bottom: 1px solid #eee;
        margin-bottom: 8px;
    }
    .teacher-row:last-child {
        border-bottom: none;
    }
    .teacher-info {
        flex: 1;
    }
    .teacher-actions {
        display: flex;
        gap: 5px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kelola Guru Pengajar - Kelas {{ $class->class }}</h5>
                <a href="{{ route('admin.teached-classes.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
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

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-4">
                    <h6><strong>Informasi Kelas</strong></h6>
                    <p class="mb-1">
                        <strong>Nama Kelas:</strong> Kelas {{ $class->class }}<br>
                        <strong>Jurusan/Major:</strong> {{ $class->major ?? 'Umum' }}<br>
                        <strong>Wali Kelas:</strong>
                        <span class="ms-2">{{ $class->homeroomTeacher ? $class->homeroomTeacher->name : 'Belum ada wali kelas' }}</span>
                    </p>
                </div>

                <hr>

                <!-- Daftar Guru Pengajar Saat Ini -->
                <h6 class="mb-3"><strong>Guru Pengajar Saat Ini</strong></h6>
                @if($class->teachedClasses->count() > 0)
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Guru</th>
                                    <th>Semester</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($class->teachedClasses as $tc)
                                    <tr>
                                        <td>
                                            <strong>{{ $tc->teacher->name }}</strong><br>
                                            <small class="text-muted">NIP: {{ $tc->teacher->nip }}</small>
                                        </td>
                                        <td>{{ $tc->semester }}</td>
                                        <td>
                                            <small>
                                                {{-- Tampilkan mata pelajaran dari relasi subjects --}}
                                                @if($tc->subjects->count() > 0)
                                                    @foreach($tc->subjects as $subject)
                                                        <span class="badge bg-primary me-1 mb-1">{{ $subject->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">Belum ada mata pelajaran</span>
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $tc->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.teached-classes.destroy', $tc->id) }}" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $tc->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit: {{ $tc->teacher->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.teached-classes.update', $tc->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="form-group mb-3">
                                                            <label for="semester{{ $tc->id }}" class="form-label">Semester</label>
                                                            <select name="semester" id="semester{{ $tc->id }}" class="form-control" required>
                                                                <option value="1" {{ $tc->semester == 1 ? 'selected' : '' }}>Semester 1</option>
                                                                <option value="2" {{ $tc->semester == 2 ? 'selected' : '' }}>Semester 2</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="form-label">Mata Pelajaran</label>
                                                            @php
                                                                // Ambil ID mata pelajaran yang sudah dipilih
                                                                $selectedSubjectIds = $tc->subjects->pluck('id')->toArray();
                                                                // Ambil ID mata pelajaran yang sudah digunakan guru lain di kelas ini
                                                                $assignedByOthers = $class->teachedClasses
                                                                    ->where('id', '!=', $tc->id)
                                                                    ->pluck('subjects')
                                                                    ->flatten()
                                                                    ->pluck('id')
                                                                    ->toArray();
                                                            @endphp
                                                            <select name="subject_ids[]" class="form-control" multiple style="min-height:120px;" required>
                                                                @foreach($availableSubjects as $subject)
                                                                    <option value="{{ $subject->id }}" 
                                                                        {{ in_array($subject->id, $selectedSubjectIds) ? 'selected' : '' }}
                                                                        {{ in_array($subject->id, $assignedByOthers) && !in_array($subject->id, $selectedSubjectIds) ? 'disabled' : '' }}>
                                                                        {{ $subject->name }} {{ in_array($subject->id, $assignedByOthers) && !in_array($subject->id, $selectedSubjectIds) ? '(sudah terdaftar)' : '' }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <small class="text-muted d-block mt-1">
                                                                <i class="bi bi-info-circle"></i> Tekan Ctrl/Cmd untuk pilih lebih dari satu
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mb-4">
                        Belum ada guru pengajar untuk kelas ini
                    </div>
                @endif

                <hr>

                <!-- Daftar Mata Pelajaran Kelas -->
                <div class="alert alert-info mb-4">
                    <h6 class="mb-2"><strong><i class="bi bi-book"></i> Mata Pelajaran untuk Kelas {{ $class->class }} ({{ $class->major ?? 'Umum' }})</strong></h6>
                    <div class="row">
                        @foreach($availableSubjects as $subject)
                            @php
                                // Cek apakah mata pelajaran sudah ada yang mengajar
                                $isAssigned = $class->teachedClasses
                                    ->pluck('subjects')
                                    ->flatten()
                                    ->contains('id', $subject->id);
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <small class="d-block mb-1">
                                    @if($isAssigned)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                    {{ $subject->name }}
                                    @if($isAssigned)
                                        <span class="badge bg-success">Sudah Ada</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Ada</span>
                                    @endif
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr>

                <!-- Form Tambah Guru -->
                <h6 class="mb-3"><strong>Tambah Guru Pengajar</strong></h6>
                <form method="POST" action="{{ route('admin.teached-classes.store', $class->id) }}">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="teacher_id" class="form-label">Pilih Guru <span class="text-danger">*</span></label>
                        <select name="teacher_id" id="teacher_id" class="form-control @error('teacher_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Guru --</option>
                            @foreach($teachers as $teacher)
                                @php
                                    // Ambil nama mata pelajaran yang cocok dengan kelas
                                    $matchedSubjectNames = $teacher->matched_subjects->pluck('name')->toArray();
                                    $subjectsText = count($matchedSubjectNames) > 0 ? implode(', ', $matchedSubjectNames) : 'Semua mata pelajaran';
                                @endphp
                                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }} ({{ $teacher->nip }}) - {{ $subjectsText }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-info-circle"></i> 
                            @if($teachers->count() > 0)
                                Menampilkan {{ $teachers->count() }} guru yang tersedia
                            @else
                                Belum ada guru yang terdaftar
                            @endif
                        </small>
                        @error('teacher_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                        <select name="semester" id="semester" class="form-control @error('semester') is-invalid @enderror" required>
                            <option value="">-- Pilih Semester --</option>
                            <option value="1" {{ old('semester') == 1 ? 'selected' : '' }}>Semester 1</option>
                            <option value="2" {{ old('semester') == 2 ? 'selected' : '' }}>Semester 2</option>
                        </select>
                        @error('semester')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Mata Pelajaran <span class="text-danger">*</span> <span class="text-muted">(Bisa lebih dari satu)</span></label>
                        <div id="departments-container">
                            @php
                                $oldSubjectIds = old('subject_ids', []);
                                // Ambil ID mata pelajaran yang sudah digunakan guru lain
                                $assignedSubjectIds = $class->teachedClasses
                                    ->pluck('subjects')
                                    ->flatten()
                                    ->pluck('id')
                                    ->toArray();
                            @endphp
                            <select name="subject_ids[]" id="subject_ids_select" class="form-control @error('subject_ids') is-invalid @enderror" multiple style="min-height:150px;" required>
                                @foreach($availableSubjects as $subject)
                                    <option value="{{ $subject->id }}" 
                                        {{ in_array($subject->id, $oldSubjectIds) ? 'selected' : '' }}
                                        {{ in_array($subject->id, $assignedSubjectIds) ? 'disabled' : '' }}>
                                        {{ $subject->name }} ({{ $subject->code }}) {{ in_array($subject->id, $assignedSubjectIds) ? '(sudah terdaftar)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle"></i> Tekan Ctrl/Cmd untuk pilih lebih dari satu. Mata pelajaran disesuaikan dengan jurusan kelas: <strong>{{ $class->major ?? 'Umum' }}</strong>
                            </small>
                        </div>
                        @error('subject_ids')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('subject_ids.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Tambah Guru Pengajar
                        </button>
                        <a href="{{ route('admin.teached-classes.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Ringkasan</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Total Guru Pengajar:</strong><br>
                    <h4>{{ $class->teachedClasses->count() }}</h4>
                </p>
                <hr>
                <p class="mb-0">
                    <strong>Guru Pengajar:</strong><br>
                    @if($class->teachedClasses->count() > 0)
                        @foreach($class->teachedClasses as $tc)
                            <small class="d-block mb-1">
                                • {{ $tc->teacher->name }} (Semester {{ $tc->semester }})
                            </small>
                        @endforeach
                    @else
                        <small class="text-muted">Belum ada</small>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // No longer need to populate departments from teacher selection
    // Departments are now pre-populated based on class major
    
    // Keep this for backward compatibility if needed
    function initDepartmentHandlers(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        function updateRemoveButtons() {
            const groups = container.querySelectorAll('.department-input-group');
            groups.forEach(group => {
                const removeBtn = group.querySelector('.remove-dept-btn');
                if (removeBtn) {
                    removeBtn.style.display = groups.length > 1 ? 'block' : 'none';
                }
            });
        }

        // Add department button
        const addBtn = container.nextElementSibling;
        if (addBtn && addBtn.id === 'add-department') {
            addBtn.addEventListener('click', function() {
                const newGroup = document.createElement('div');
                newGroup.className = 'department-input-group mb-2';
                newGroup.innerHTML = `
                    <div class="input-group">
                        <input type="text" name="departments[]" class="form-control" placeholder="Contoh: Bahasa Indonesia">
                        <button type="button" class="btn btn-outline-danger remove-dept-btn">Hapus</button>
                    </div>
                `;
                container.appendChild(newGroup);
                updateRemoveButtons();

                newGroup.querySelector('.remove-dept-btn').addEventListener('click', function() {
                    newGroup.remove();
                    updateRemoveButtons();
                });
            });
        }

        // Remove department buttons
        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-dept-btn')) {
                e.preventDefault();
                e.target.closest('.department-input-group').remove();
                updateRemoveButtons();
            }
        });

        updateRemoveButtons();
    }

    // Initialize for main form (if needed)
    initDepartmentHandlers('departments-container');
});
</script>
@endsection
