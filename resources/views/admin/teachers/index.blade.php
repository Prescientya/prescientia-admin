@extends('layouts.app')

@section('content')
<div class="content-header">
    <h1>Data Guru</h1>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Data Guru
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3>Daftar Guru</h3>
        <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">Tambah Guru</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                    <tr>
                        <td>{{ $teacher->nip }}</td>
                        <td>{{ $teacher->name }}</td>
                        <td>
                            <span class="badge {{ $teacher->gender == 'L' ? 'badge-primary' : 'badge-danger' }}">
                                {{ $teacher->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                        </td>
                        <td>{{ $teacher->user->email }}</td>
                        <td>{{ $teacher->phone_number ?? '-' }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.teachers.show', $teacher->id) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.teachers.destroy', $teacher->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus guru ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">Tidak ada data guru</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="pagination-wrapper">
            {{ $teachers->links() }}
        </div>
    </div>
</div>
@endsection
