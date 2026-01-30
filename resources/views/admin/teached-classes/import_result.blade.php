@extends('layouts.app')

@section('title', 'Hasil Import Guru Mengajar')

@section('page-title', 'Hasil Import Guru Mengajar')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header {{ empty($failures) ? 'bg-success text-white' : 'bg-warning text-dark' }}">
                    <h5 class="mb-0">
                        <i class="bi {{ empty($failures) ? 'bi-check-circle' : 'bi-exclamation-triangle' }}"></i>
                        {{ empty($failures) ? '✅ Import Berhasil!' : '⚠️ Import Selesai dengan Beberapa Error' }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Success Summary -->
                    @if($successCount > 0)
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="bi bi-check-circle-fill"></i> Penugasan Guru Berhasil Ditambahkan
                            </h6>
                            <p class="mb-0">
                                <strong>{{ $successCount }}</strong> penugasan guru pengajar berhasil ditambahkan ke sistem.
                                Data guru sekarang sudah muncul di halaman "Kelola Guru Pengajar" masing-masing kelas.
                            </p>
                        </div>
                    @endif

                    <!-- Failures -->
                    @if(!empty($failures))
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">
                                <i class="bi bi-x-circle-fill"></i> Beberapa Baris Gagal Diimport
                            </h6>
                            <p class="mb-2">
                                <strong>{{ count($failures) }}</strong> baris gagal diimport karena error validasi:
                            </p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="80" class="text-center">Baris</th>
                                        <th width="250">Nama Guru</th>
                                        <th width="120">Kelas</th>
                                        <th width="120">Jurusan</th>
                                        <th>Detail Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($failures as $failure)
                                        <tr>
                                            <td class="text-center align-middle">
                                                <span class="badge bg-danger fs-6">{{ $failure['row'] }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <strong>{{ $failure['data']['teacher_name'] ?? '-' }}</strong>
                                            </td>
                                            <td class="align-middle">{{ $failure['data']['class_raw'] ?? '-' }}</td>
                                            <td class="align-middle">{{ $failure['data']['major_raw'] ?? '-' }}</td>
                                            <td>
                                                @if(!empty($failure['errors']))
                                                    <ul class="mb-0 small">
                                                        @foreach($failure['errors'] as $error)
                                                            <li class="{{ strpos($error, '❌') !== false ? 'text-danger fw-bold' : (strpos($error, '⚠️') !== false ? 'text-warning fw-bold' : (strpos($error, '📚') !== false ? 'text-info' : (strpos($error, '💡') !== false ? 'text-primary' : ''))) }}">
                                                                {{ $error }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-4">
                            <h6 class="alert-heading">
                                <i class="bi bi-lightbulb-fill"></i> <strong>Panduan Perbaikan Error Import</strong>
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <h7 class="text-info"><strong>❌ Error: "Guru tidak ditemukan"</strong></h7>
                                    <ol class="small mb-3">
                                        <li>Pastikan nama guru <strong>sudah terdaftar</strong> di sistem</li>
                                        <li>Buka menu <strong>"Data Guru"</strong> untuk verifikasi</li>
                                        <li>Perhatikan penulisan nama (case-sensitive untuk exact match)</li>
                                        <li>Coba import ulang dengan nama yang benar</li>
                                    </ol>
                                </div>
                                <div class="col-md-6">
                                    <h7 class="text-warning"><strong>⚠️ Error: "Guru tidak mengajar semua mata pelajaran"</strong></h7>
                                    <ol class="small mb-3">
                                        <li>Buka menu <strong>"Data Guru"</strong></li>
                                        <li>Klik guru yang dimaksud</li>
                                        <li>Tambahkan mata pelajaran yang kurang di tab "Mata Pelajaran"</li>
                                        <li>Simpan perubahan</li>
                                        <li>Coba import ulang</li>
                                    </ol>
                                </div>
                                <div class="col-md-6">
                                    <h7 class="text-danger"><strong>❌ Error: "Kelas tidak ditemukan"</strong></h7>
                                    <ol class="small mb-3">
                                        <li>Buka menu <strong>"Data Kelas"</strong></li>
                                        <li>Pastikan kelas dengan jurusan tersebut sudah dibuat</li>
                                        <li>Jika belum, buat kelas baru di tab "Tambah Kelas"</li>
                                        <li>Coba import ulang</li>
                                    </ol>
                                </div>
                                <div class="col-md-6">
                                    <h7 class="text-danger"><strong>❌ Error: "Jumlah kelas ≠ jumlah jurusan"</strong></h7>
                                    <ol class="small mb-3">
                                        <li>Periksa format Excel: setiap kelas harus punya jurusan</li>
                                        <li><strong>Contoh BENAR:</strong> "10,11,12" | "RPL,RPL,RPL" (3=3) ✓</li>
                                        <li><strong>Contoh SALAH:</strong> "10,11" | "RPL,RPL,AKL" (2≠3) ✗</li>
                                        <li>Perbaiki dan coba import ulang</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Summary Box -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">Total Baris</h6>
                                    <h4 class="mb-0"><strong>{{ $totalRows }}</strong></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="text-success">Berhasil</h6>
                                    <h4 class="mb-0 text-success"><strong>{{ $successCount }}</strong></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="text-danger">Gagal</h6>
                                    <h4 class="mb-0 text-danger"><strong>{{ count($failures) }}</strong></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="text-primary">Persentase Sukses</h6>
                                    <h4 class="mb-0 text-primary"><strong>{{ $totalRows > 0 ? round(($successCount / $totalRows) * 100) : 0 }}%</strong></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.teached-classes.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Manajemen Guru Mengajar
                        </a>
                        @if(!empty($failures))
                            <a href="javascript:history.back()" class="btn btn-warning">
                                <i class="bi bi-arrow-counterclockwise"></i> Perbaiki & Coba Lagi
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .alert-heading {
        font-size: 1rem;
    }
    .table-dark {
        background-color: #343a40 !important;
    }
    .table-dark th {
        color: white !important;
    }
</style>
@endsection
