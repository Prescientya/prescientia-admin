@extends('layouts.app')

@section('title', 'Petugas MBG - SekolahKu Admin')

@section('page-title', 'Data Petugas MBG')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mbg-officers.css') }}">
<link rel="stylesheet" href="{{ asset('css/action-dropdown.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Petugas MBG</h5>
        <a href="{{ route('admin.mbg-officers.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Petugas
        </a>
    </div>
    <div class="card-body">
        @if ($officers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-compact">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Username</th>
                            <th style="width: 80px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($officers as $key => $officer)
                            <tr>
                                <td>{{ $officers->firstItem() + $key }}</td>
                                <td>{{ $officer->username }}</td>
                                <td>
                                    <div class="action-menu-container" style="position: relative;">
                                        <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                            <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                                        </button>
                                        <ul class="dropdown-menu" style="display: none;">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.mbg-officers.show', $officer->id) }}">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    Lihat Detail
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.mbg-officers.edit', $officer->id) }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.mbg-officers.destroy', $officer->id) }}" class="dropdown-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus petugas ini?')">
                                                        <i class="bi bi-trash"></i> Hapus
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
                {{ $officers->links() }}
            </div>
        @else
            <div class="alert alert-info text-center">
                <p class="mb-0">Belum ada data petugas MBG. <a href="{{ route('admin.mbg-officers.create') }}">Tambah sekarang</a></p>
            </div>
        @endif
    </div>
</div>

<script src="{{ asset('js/action-dropdown.js') }}"></script>

@endsection
