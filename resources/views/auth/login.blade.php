@extends('layouts.guest')

@section('title', 'Login - SekolahKu Admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login-container">
    <div class="login-box">
        <div class="login-header">
            <div class="login-logo">
                <img src="{{ asset('assets/logo-smkn1-ciamis.svg') }}" alt="SMKN 1 CIAMIS" width="80" height="80">
            </div>
            <h1 class="login-title">SekolahKu</h1>
            <p class="login-subtitle">Silakan login untuk melanjutkan</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    value="{{ old('email') }}" 
                    placeholder="Masukkan email Anda"
                    required 
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Masukkan password Anda"
                    required
                >
            </div>

            <div class="form-check">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-primary">
                Masuk
            </button>
        </form>
    </div>
</div>
@endsection
