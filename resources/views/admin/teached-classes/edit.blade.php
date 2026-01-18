@extends('layouts.app')

@section('title', 'Kelola Guru Pengajar - SekolahKu Admin')

@section('page-title', 'Kelola Guru Pengajar')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-3">
        <a href="{{ route('admin.teached-classes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Kelas {{ $class->class }} {{ $class->major }}</h4>
            <small class="text-muted">Semester: {{ $semesterName }} | Tahun Ajaran: {{ $academicYear }}</small>
        </div>
        <div class="card-body">
            <!-- Daftar Guru Pengajar -->
            <h5 class="mb-3">Daftar Guru Pengajar</h5>
            
            @if($existingAssignments->count() > 0)
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($existingAssignments as $index => $assignment)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $assignment->teacher->name }}</strong><br>
                                    <small class="text-muted">NIP: {{ $assignment->teacher->nip }}</small>
                                </td>
                                <td>
                                    {{ $assignment->subject->name }}
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editAssignment({{ $assignment->id }}, {{ $assignment->subject->id }}, '{{ $assignment->subject->name }}')">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.teached-classes.remove-subject', $assignment->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Belum ada guru yang ditugaskan.</p>
            @endif

            <hr class="my-4">

            <!-- Tambah Penugasan -->
            <h5 class="mb-3">Tambah Penugasan Guru</h5>
            <p class="text-muted">Klik mata pelajaran untuk menugaskan guru</p>

            <div class="mb-3">
                @foreach($availableSubjects as $subject)
                    @php
                        $isAssigned = $existingAssignments->where('subject_id', $subject->id)->isNotEmpty();
                    @endphp
                    @if($isAssigned)
                        <button class="btn btn-sm btn-success m-1" disabled>
                            <i class="fas fa-check"></i> {{ $subject->name }}
                        </button>
                    @else
                        <button class="btn btn-sm btn-outline-primary m-1" onclick="showAssignForm({{ $subject->id }}, '{{ $subject->name }}')">
                            {{ $subject->name }}
                        </button>
                    @endif
                @endforeach
            </div>

            <!-- Form Assign (Hidden by default) -->
            <div id="assignFormContainer" style="display:none;" class="card mt-3">
                <div class="card-body">
                    <h6>Tugaskan Guru untuk: <strong id="assignSubjectName"></strong></h6>
                    <form id="assignForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label>Pilih Guru:</label>
                            <select name="teacher_id" id="teacherSelect" class="form-select" required>
                                <option value="">-- Pilih Guru --</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" onclick="hideAssignForm()">Batal</button>
                    </form>
                </div>
            </div>

            <!-- Form Edit (Hidden by default) -->
            <div id="editFormContainer" style="display:none;" class="card mt-3">
                <div class="card-body">
                    <h6>Edit Guru untuk: <strong id="editSubjectName"></strong></h6>
                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label>Pilih Guru Baru:</label>
                            <select name="teacher_id" id="editTeacherSelect" class="form-select" required>
                                <option value="">-- Pilih Guru --</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning">Update</button>
                        <button type="button" class="btn btn-secondary" onclick="hideEditForm()">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    const teachersBySubject = @json($teachersBySubject);
    const classId = {{ $class->id }};

    function showAssignForm(subjectId, subjectName) {
        document.getElementById('assignFormContainer').style.display = 'block';
        document.getElementById('editFormContainer').style.display = 'none';
        document.getElementById('assignSubjectName').textContent = subjectName;
        document.getElementById('assignForm').action = `/admin/teached-classes/${classId}/subjects/${subjectId}/assign`;
        
        const select = document.getElementById('teacherSelect');
        select.innerHTML = '<option value="">-- Pilih Guru --</option>';
        
        if (teachersBySubject[subjectId]) {
            teachersBySubject[subjectId].forEach(teacher => {
                const option = document.createElement('option');
                option.value = teacher.id;
                option.textContent = `${teacher.name} (${teacher.nip})`;
                select.appendChild(option);
            });
        }
    }

    function hideAssignForm() {
        document.getElementById('assignFormContainer').style.display = 'none';
    }

    function editAssignment(assignmentId, subjectId, subjectName) {
        document.getElementById('editFormContainer').style.display = 'block';
        document.getElementById('assignFormContainer').style.display = 'none';
        document.getElementById('editSubjectName').textContent = subjectName;
        document.getElementById('editForm').action = `/admin/teached-classes/${assignmentId}/update-teacher`;
        
        const select = document.getElementById('editTeacherSelect');
        select.innerHTML = '<option value="">-- Pilih Guru --</option>';
        
        if (teachersBySubject[subjectId]) {
            teachersBySubject[subjectId].forEach(teacher => {
                const option = document.createElement('option');
                option.value = teacher.id;
                option.textContent = `${teacher.name} (${teacher.nip})`;
                select.appendChild(option);
            });
        }
    }

    function hideEditForm() {
        document.getElementById('editFormContainer').style.display = 'none';
    }
</script>
@endsection
@endsection
