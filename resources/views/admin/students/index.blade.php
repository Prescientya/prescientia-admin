@extends('layouts.app')

@section('title', 'Data Siswa - SekolahKu Admin')

@section('page-title', 'Data Siswa')

@section('css')
<link rel="stylesheet" href="{{ asset('css/action-dropdown.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Data Siswa</h5>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-sm">
            Tambah Siswa
        </a>
    </div>
    <div class="card-body">
        @if ($students->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-compact">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th style="text-align: center">Email</th>
                            <th style="text-align: center">Kelas</th>
                            <th style="text-align: center">Jenis Kelamin</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $key => $student)
                            <tr>
                                <td>{{ $students->firstItem() + $key }}</td>
                                <td><strong>{{ $student->nis }}</strong></td>
                                <td>{{ $student->name }}</td>
                                <td>
                                    @if($student->user?->email)
                                        {{ preg_replace('/^(.{5}).+(@.+)$/', '$1...$2', $student->user->email) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $student->class ? $student->class->class . ' ' . $student->class->major : '-' }}</td>
                                <td class="text-center">{{ $student->gender === 'L' ? 'L' : 'P' }}</td>
                                <td>
                                    <div class="action-menu-container">
                                        <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                            <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                                        </button>
                                        <ul class="dropdown-menu" style="display: none;">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.students.show', $student->id) }}">
                                                    Lihat Detail
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.students.edit', $student->id) }}">
                                                    Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" class="dropdown-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus siswa ini?')">
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

            <div class="d-flex justify-content-center mt-3">
                {{ $students->links() }}
            </div>

            <div class="text-center mt-3">
                <p class="text-muted mb-0">Total Siswa: <strong>{{ $totalStudents }}</strong></p>
            </div>
        @else
            <div class="alert alert-info text-center">
                <p class="mb-0">Belum ada data siswa. <a href="{{ route('admin.students.create') }}">Tambah sekarang</a></p>
            </div>
        @endif
    </div>
</div>

<script src="{{ asset('js/action-dropdown.js') }}"></script>
@endsection
