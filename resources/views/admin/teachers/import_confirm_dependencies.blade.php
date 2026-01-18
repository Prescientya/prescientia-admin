@extends('layouts.app')

@section('title', 'Konfirmasi Import Guru - SekolahKu Admin')

@section('page-title', 'Konfirmasi Import Guru')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Data Belum Tersedia</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-warning mb-4">
            <strong>⚠️ Perhatian:</strong> File Excel Anda mengandung mata pelajaran atau kelas yang belum terdaftar dalam sistem.
            <br>Silakan pilih opsi di bawah untuk melanjutkan.
        </div>

        <div class="row">
            <div class="col-md-6">
                @if(!empty($missingSubjects))
                <div class="card border-warning mb-4">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h6 class="mb-0">📚 Mata Pelajaran yang Hilang</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            <strong>{{ count($missingSubjects) }}</strong> mata pelajaran baru ditemukan:
                        </p>
                        <ul class="list-group list-group-flush">
                            @foreach($missingSubjects as $subject)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $subject['name'] }}</strong>
                                    <br>
                                    <small class="text-muted">Ditemukan {{ $subject['count'] }}× di file</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $subject['count'] }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-6">
                @if(!empty($missingClasses))
                <div class="card border-info mb-4">
                    <div class="card-header bg-info bg-opacity-10">
                        <h6 class="mb-0">🏫 Kelas yang Hilang</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            <strong>{{ count($missingClasses) }}</strong> kombinasi kelas-jurusan baru ditemukan:
                        </p>
                        <ul class="list-group list-group-flush">
                            @foreach($missingClasses as $class)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Kelas {{ $class['class'] }}</strong>
                                    @if($class['major'])
                                    <span class="badge bg-secondary ms-2">{{ $class['major'] }}</span>
                                    @endif
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $class['count'] }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="alert alert-info mt-4">
            <strong>ℹ️ Catatan:</strong> 
            <ul class="mb-0 mt-2">
                <li>Pilih <strong>"Buat Data Baru"</strong> untuk membuat semua data yang hilang secara otomatis</li>
                <li>Pilih <strong>"Lewati"</strong> untuk melewati data yang hilang dan hanya import baris dengan data lengkap</li>
                <li>Anda dapat menambahkan data yang hilang manual nanti di panel admin</li>
            </ul>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-4">
            <form action="{{ route('admin.teachers.import.confirm-dependencies') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="filePath" value="{{ $filePath }}">
                <input type="hidden" name="action" value="skip">
                
                @foreach($missingSubjects as $subject)
                <input type="hidden" name="selectedSubjects[]" value="{{ $subject['name'] }}">
                @endforeach
                
                @foreach($missingClasses as $class)
                <input type="hidden" name="selectedClasses[{{ $loop->index }}][class]" value="{{ $class['class'] }}">
                <input type="hidden" name="selectedClasses[{{ $loop->index }}][major]" value="{{ $class['major'] ?? '' }}">
                @endforeach

                <button type="submit" class="btn btn-secondary">
                    <i class="bi bi-skip-forward"></i> Lewati
                </button>
            </form>

            <form action="{{ route('admin.teachers.import.confirm-dependencies') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="filePath" value="{{ $filePath }}">
                <input type="hidden" name="action" value="create">
                
                @foreach($missingSubjects as $subject)
                <input type="hidden" name="selectedSubjects[]" value="{{ $subject['name'] }}">
                @endforeach
                
                @foreach($missingClasses as $class)
                <input type="hidden" name="selectedClasses[{{ $loop->index }}][class]" value="{{ $class['class'] }}">
                <input type="hidden" name="selectedClasses[{{ $loop->index }}][major]" value="{{ $class['major'] ?? '' }}">
                @endforeach

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Buat Data Baru
                </button>
            </form>
        </div>

        <hr class="my-4">

        <div class="text-center text-muted">
            <small>
                <strong>File Summary:</strong> {{ $rowCount }} baris data guru ditemukan dalam file
                @if(!empty($missingSubjects))
                | {{ count($missingSubjects) }} mata pelajaran baru
                @endif
                @if(!empty($missingClasses))
                | {{ count($missingClasses) }} kelas baru
                @endif
            </small>
        </div>
    </div>
</div>

<style>
.card-header {
    padding: 1rem;
    font-weight: 600;
}

.list-group-item {
    padding: 0.75rem 1rem;
}
</style>
@endsection
