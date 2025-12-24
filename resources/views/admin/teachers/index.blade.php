@extends('layouts.app')

@section('title', 'Data Guru - SekolahKu Admin')

@section('page-title', 'Data Guru')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teachers.css') }}">
<link rel="stylesheet" href="{{ asset('css/action-dropdown.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Data Guru</h5>
        <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Guru
        </a>
    </div>
    <div class="card-body">
        @if ($teachers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-compact">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th style="width: 150px;">NIP</th>
                            <th style="width: 160px;">Nama Guru</th>
                            <th style="text-align: center">Email</th>
                            <th style="width: 120px; text-align: center;">Jenis Kelamin</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teachers as $key => $teacher)
                            <tr>
                                <td>{{ $teachers->firstItem() + $key }}</td>
                                <td><strong>{{ $teacher->nip }}</strong></td>
                                <td>{{ $teacher->name }}</td>
                                <td>
                                    @if($teacher->user?->email)
                                        {{ preg_replace('/^(.{5}).+(@.+)$/', '$1...$2', $teacher->user->email) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">{{ $teacher->gender === 'L' ? 'L' : 'P' }}</td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <div class="action-menu-container" style="position: relative; display: inline-block;">
                                        <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                            <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                                        </button>
                                        <ul class="dropdown-menu" style="display: none;">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.teachers.show', $teacher->id) }}">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    Lihat Detail
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.teachers.edit', $teacher->id) }}">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                    Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.teachers.destroy', $teacher->id) }}" class="dropdown-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus guru ini?')">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $teachers->links() }}
            </div>

            <!-- Total Count -->
            <div class="text-center mt-3">
                <p class="text-muted mb-0">Total Guru: <strong>{{ $totalTeachers }}</strong></p>
            </div>
        @else
            <div class="alert alert-info text-center">
                <p class="mb-0">Belum ada data guru. <a href="{{ route('admin.teachers.create') }}">Tambah sekarang</a></p>
            </div>
        @endif
    </div>
</div>

<script src="{{ asset('js/action-dropdown.js') }}"></script>
@endsection
