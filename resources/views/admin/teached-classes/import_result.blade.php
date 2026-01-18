@extends('layouts.app')

@section('title', 'Hasil Import Guru Mengajar')

@section('page-title', 'Hasil Import Guru Mengajar')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header {{ empty($failures) ? 'bg-success text-white' : 'bg-warning text-dark' }}">
                    <h5 class="mb-0">
                        <i class="bi {{ empty($failures) ? 'bi-check-circle' : 'bi-exclamation-triangle' }}"></i>
                        {{ empty($failures) ? 'Import Berhasil!' : 'Import Selesai dengan Beberapa Error' }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Success Summary -->
                    @if($successCount > 0)
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="bi bi-check-circle-fill"></i> Berhasil Diimport
                            </h6>
                            <p class="mb-0">
                                <strong>{{ $successCount }}</strong> penugasan guru berhasil ditambahkan ke sistem.
                            </p>
                        </div>
                    @endif

                    <!-- Failures -->
                    @if(!empty($failures))
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">
                                <i class="bi bi-x-circle-fill"></i> Gagal Diimport
                            </h6>
                            <p class="mb-2">
                                <strong>{{ count($failures) }}</strong> baris gagal diimport karena error validasi:
                            </p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th width="80">Baris</th>
                                        <th>Nama Guru</th>
                                        <th>Kelas</th>
                                        <th>Jurusan</th>
                                        <th>Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($failures as $failure)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-danger">{{ $failure['row'] }}</span>
                                            </td>
                                            <td>{{ $failure['data']['teacher_name'] ?? '-' }}</td>
                                            <td>{{ $failure['data']['class_raw'] ?? '-' }}</td>
                                            <td>{{ $failure['data']['major_raw'] ?? '-' }}</td>
                                            <td>
                                                @if(!empty($failure['errors']))
                                                    <ul class="mb-0 small text-danger">
                                                        @foreach($failure['errors'] as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <h6 class="alert-heading"><i class="bi bi-lightbulb"></i> Tips Memperbaiki Error</h6>
                            <ul class="mb-0 small">
                                <li>Pastikan nama guru <strong>sudah terdaftar</strong> di sistem (cek di menu Data Guru)</li>
                                <li>Pastikan <strong>jumlah kelas = jumlah jurusan</strong> (contoh: "10,11" harus dengan "RPL,RPL")</li>
                                <li>Pastikan kombinasi <strong>Kelas+Jurusan sudah dibuat</strong> (cek di menu Data Kelas)</li>
                                <li>Gunakan <strong>format yang benar</strong>: Kelas berupa angka, Jurusan sesuai dengan data di sistem</li>
                            </ul>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.teached-classes.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Manajemen Guru Mengajar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
