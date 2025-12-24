@extends('layouts.app')

@section('title', 'Detail Petugas MBG - SekolahKu Admin')

@section('page-title', 'Detail Petugas MBG')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Petugas MBG</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><strong>ID</strong></label>
                    <p class="form-control-plaintext">{{ $officer->id }}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Username</strong></label>
                    <p class="form-control-plaintext">{{ $officer->username }}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Tanggal Dibuat</strong></label>
                    <p class="form-control-plaintext">
                        @if($officer->created_at)
                            {{ $officer->created_at->format('d/m/Y H:i:s') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><strong>Terakhir Diperbarui</strong></label>
                    <p class="form-control-plaintext">
                        @if($officer->updated_at)
                            {{ $officer->updated_at->format('d/m/Y H:i:s') }}
                        @else
                            -
                        @endif
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Status</strong></label>
                    <p class="form-control-plaintext">
                        <span class="badge bg-success">Aktif</span>
                    </p>
                </div>
            </div>
        </div>

        <hr>

        <div class="action-buttons">
            <a href="{{ route('admin.mbg-officers.edit', $officer->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit Data
            </a>
            <a href="{{ route('admin.mbg-officers.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
</div>
@endsection
