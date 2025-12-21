@extends('layouts.app')

@section('content')
<div class="content-header">
    <h1>Data Kelas</h1>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Data Kelas
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3>Daftar Kelas</h3>
        <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">Tambah Kelas</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Kelas</th>
                        <th>Wali Kelas</th>
                        <th>Jumlah Siswa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $class)
                    <tr>
                        <td><strong>{{ $class->name }}</strong></td>
                        <td>{{ $class->homeroomTeacher->name ?? '-' }}</td>
                        <td>
                            <span class="badge badge-info">{{ $class->students->count() }} siswa</span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.classes.show', $class->id) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.classes.destroy', $class->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus kelas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">Tidak ada data kelas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="pagination-wrapper">
            {{ $classes->links() }}
        </div>
    </div>
</div>
@endsection
