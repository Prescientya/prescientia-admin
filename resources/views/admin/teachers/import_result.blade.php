@extends('layouts.app')

@section('page-title', 'Hasil Import Guru')
@section('content')
<div class="container">
    <div class="card">
        <div class="container py-4">
            
            <p><strong>Berhasil:</strong> {{ $successCount ?? 0 }}</p>
            <p><strong>Gagal:</strong> {{ count($failures ?? []) }}</p>

            @if(!empty($failures))
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Baris Excel</th>
                                <th>Email</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Alasan Gagal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($failures as $f)
                                <tr>
                                    <td>{{ $f['row'] }}</td>
                                    <td>{{ $f['data']['email'] ?? '-' }}</td>
                                    <td>{{ $f['data']['nip'] ?? '-' }}</td>
                                    <td>{{ $f['data']['name'] ?? '-' }}</td>
                                    <td>{{ $f['data']['gender'] ?? '-' }}</td>
                                    <td>
                                        <ul class="mb-0">
                                            @foreach($f['errors'] as $e)
                                                <li>{{ $e }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    Semua data berhasil diimport tanpa error.
                </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Guru
                </a>
                <a href="{{ route('admin.teachers.index', ['show_import' => 1]) }}" class="btn btn-secondary">
                    <i class="bi bi-upload"></i> Import Lagi
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
