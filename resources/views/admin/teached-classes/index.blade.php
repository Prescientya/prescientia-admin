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
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Kelas & Guru Pengajar</h5>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali ke Data Kelas
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($classes as $class)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card class-card h-100 position-relative">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Kelas {{ $class->class }}</h6>
                                    <small>{{ $class->major ?? 'Umum' }}</small>
                                </div>
                                <div class="card-body">
                                    @php
                                        $semesters = $class->teachedClasses->pluck('semester')->unique()->filter()->values()->all();
                                    @endphp
                                    <div class="mb-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted d-block mb-2"><strong>Wali Kelas:</strong></small>
                                            <small class="d-block">{{ $class->homeroomTeacher ? $class->homeroomTeacher->name : 'Belum ada wali kelas' }}</small>
                                        </div>
                                        <div>
                                            <small class="text-muted">Semester: {{ count($semesters) ? implode(', ', $semesters) : '-' }}</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-2"><strong>📚 Guru Pengajar:</strong></small>
                                        <div class="teachers-list">
                                            @if($class->teachedClasses->count() > 0)
                                                @foreach($class->teachedClasses as $tc)
                                                    <div class="teacher-item mb-2 p-2 bg-light rounded" >
                                                            <small class="d-block">
                                                                @php
                                                                    $tcDeps = is_array($tc->departments) ? array_filter($tc->departments) : [];
                                                                    $teacherDeps = is_array($tc->teacher->department) ? $tc->teacher->department : ($tc->teacher->department ? [$tc->teacher->department] : []);
                                                                    $displayDeps = count($tcDeps) ? $tcDeps : $teacherDeps;
                                                                @endphp
                                                                <strong>{{ $tc->teacher->name }}</strong> - {{ count($displayDeps) ? implode(', ', $displayDeps) : '-' }}
                                                            </small>
                                                        </div>
                                                @endforeach
                                            @else
                                                <small class="d-block text-muted">Belum ada guru pengajar</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('admin.teached-classes.edit', $class->id) }}" class="btn btn-sm btn-primary w-100">
                                        <i class="bi bi-pencil"></i> Kelola Guru Pengajar
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($classes->hasPages())
                    <div class="row mt-4">
                        <div class="col-md-12">
                            {{ $classes->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
