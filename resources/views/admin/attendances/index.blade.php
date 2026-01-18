@extends('layouts.app')

@section('title', 'Data Absensi - Redirect')

@section('page-title', 'Data Absensi')

@section('content')
    <div class="card">
        <div class="card-body text-center">
            <p>Halaman "Data Absensi" telah digantikan oleh dua menu baru: <strong>Absensi Siswa</strong> dan <strong>Absensi Guru</strong>.</p>
            <p>Anda akan diarahkan ke <a href="{{ route('admin.attendances.students') }}">Absensi Siswa</a> dalam beberapa detik.</p>
        </div>
    </div>
    <meta http-equiv="refresh" content="0;url={{ route('admin.attendances.students') }}" />
@endsection

