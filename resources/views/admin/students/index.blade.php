@extends('layouts.app')

@section('content')
<div class="content-header">
    <h1>Data Siswa</h1>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Data Siswa
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3>Daftar Siswa</h3>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">Tambah Siswa</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>Kelas</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td>{{ $student->nis }}</td>
                        <td>{{ $student->name }}</td>
                        <td>
                            <span class="badge {{ $student->gender == 'L' ? 'badge-primary' : 'badge-danger' }}">
                                {{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                        </td>
                        <td>{{ $student->class->name ?? '-' }}</td>
                        <td>{{ $student->user->email }}</td>
                        <td>{{ $student->phone_number ?? '-' }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus siswa ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center;">Tidak ada data siswa</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="pagination-wrapper">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection
