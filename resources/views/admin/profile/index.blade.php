@extends('layouts.app')

@section('title', 'Profil - SekolahKu Admin')

@section('page-title', 'Profil')

@section('css')
<link rel="stylesheet" href="{{ asset('css/under-development.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-body under-dev-card-body">
        <h2>Selamat datang di halaman Profil</h2>
        <p class="under-dev-text">Halaman ini sedang dalam pengembangan</p>
        <div class="mt-4">
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:inline-block">
                @csrf
                <button type="button" id="logout-button" class="btn btn-outline-danger">Logout</button>
            </form>
        </div>
    </div>
</div>
@section('scripts')
<script>
document.getElementById('logout-button').addEventListener('click', function() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        document.getElementById('logout-form').submit();
    }
});
</script>
@endsection
@endsection
