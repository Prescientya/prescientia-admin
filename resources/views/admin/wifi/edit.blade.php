@extends('layouts.app')

@section('title', 'Edit WiFi - SekolahKu Admin')

@section('page-title', 'Edit WiFi')

@section('css')
<link rel="stylesheet" href="{{ asset('css/under-development.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-body under-dev-card-body">
        <h2>Selamat datang di halaman Edit WiFi</h2>
        <p class="under-dev-text">Halaman ini sedang dalam pengembangan</p>
        <a href="{{ route('admin.wifi.index') }}" class="btn btn-secondary under-dev-back-btn">Kembali</a>
    </div>
</div>
@endsection
