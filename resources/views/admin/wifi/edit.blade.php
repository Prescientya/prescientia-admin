@extends('layouts.app')

@section('title', 'Edit WiFi - SekolahKu Admin')

@section('page-title', 'Edit WiFi')

@section('css')
<style>
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        font-size: 1rem;
        box-sizing: border-box;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .form-control.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .form-text {
        display: block;
        margin-top: 0.25rem;
        color: #6c757d;
        font-size: 0.875rem;
    }

    .btn-group {
        display: flex;
        gap: 1rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background-color: #0d6efd;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #bb2d3b;
    }

    .form-card {
        max-width: 600px;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 0.25rem;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }

    .alert-danger ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .alert-danger li {
        margin-bottom: 0.5rem;
    }

    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }

    .info-box {
        background-color: #e7f3ff;
        border-left: 4px solid #0d6efd;
        padding: 1rem;
        border-radius: 0.25rem;
        margin-bottom: 1.5rem;
    }

    .info-box strong {
        color: #004085;
    }

    code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        color: #d73a49;
        font-family: 'Courier New', monospace;
    }
</style>
@endsection

@section('content')
<div class="card form-card">
    <div class="card-body">
        <h2 style="margin-top: 0; margin-bottom: 1.5rem;">✏️ Edit WiFi</h2>

        <div class="info-box">
            <strong>📡 WiFi yang diedit:</strong> {{ $wifi->ssid }} ({{ $wifi->bssid }})
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi kesalahan saat validasi:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.wifi.update', $wifi->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-section-title">📡 Informasi WiFi</div>

            {{-- SSID Field --}}
            <div class="form-group">
                <label for="ssid" class="form-label">SSID (Nama WiFi) <span style="color: #dc3545;">*</span></label>
                <input 
                    type="text" 
                    id="ssid" 
                    name="ssid" 
                    class="form-control @error('ssid') is-invalid @enderror"
                    value="{{ old('ssid', $wifi->ssid) }}"
                    placeholder="Contoh: WiFi-Sekolah"
                    required
                >
                <small class="form-text">Nama jaringan WiFi</small>
                @error('ssid')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- BSSID Field --}}
            <div class="form-group">
                <label for="bssid" class="form-label">BSSID (MAC Address) <span style="color: #dc3545;">*</span></label>
                <input 
                    type="text" 
                    id="bssid" 
                    name="bssid" 
                    class="form-control @error('bssid') is-invalid @enderror"
                    value="{{ old('bssid', $wifi->bssid) }}"
                    placeholder="Contoh: aa:bb:cc:dd:ee:ff"
                    required
                >
                <small class="form-text">MAC Address unik dari Access Point WiFi (format: xx:xx:xx:xx:xx:xx)</small>
                @error('bssid')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- IP Address Field --}}
            <div class="form-group">
                <label for="ip_address" class="form-label">IP Address <span style="color: #6c757d;">(Opsional)</span></label>
                <input 
                    type="text" 
                    id="ip_address" 
                    name="ip_address" 
                    class="form-control @error('ip_address') is-invalid @enderror"
                    value="{{ old('ip_address', $wifi->ip_address) }}"
                    placeholder="Contoh: 192.168.1.1"
                >
                <small class="form-text">IP Address router/gateway WiFi (opsional)</small>
                @error('ip_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Form Actions --}}
            <div class="form-group" style="margin-top: 2rem;">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                    <a href="{{ route('admin.wifi.index') }}" class="btn btn-secondary">❌ Batal</a>
                </div>
            </div>
        </form>

        {{-- Delete Form (Terpisah dari form update) --}}
        <form action="{{ route('admin.wifi.destroy', $wifi->id) }}" method="POST" style="display: inline; margin-left: 0.5rem;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus WiFi ini? Data ini tidak dapat dipulihkan.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">🗑️ Hapus WiFi</button>
        </form>

        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #dee2e6;">
            <small style="color: #6c757d;">
                <strong>Dibuat:</strong> {{ $wifi->created_at->format('d/m/Y H:i') }}<br>
                <strong>Diperbarui:</strong> {{ $wifi->updated_at->format('d/m/Y H:i') }}
            </small>
        </div>
    </div>
</div>
@endsection
