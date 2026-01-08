@extends('layouts.app')

@section('title', 'Edit Petugas MBG - SekolahKu Admin')

@section('page-title', 'Edit Petugas MBG')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form-pages.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header card-header-form">
        <h4>Form Edit Petugas MBG</h4>
    </div>
    <div class="card-body card-body-form">
        <form method="POST" action="{{ route('admin.mbg-officers.update', $officer->id) }}">
            @csrf
            @method('PUT')

            <x-forms.section title="Kredensial Akun">
                <x-forms.row-2>
                    <x-forms.field-input 
                        label="Username" 
                        name="username" 
                        required 
                        :value="old('username', $officer->username)"
                        :error="$errors->first('username')"
                    />
                </x-forms.row-2>

                <x-forms.row-2>
                    <x-forms.field-input 
                        label="Password Baru" 
                        name="password" 
                        type="password"
                        placeholder="Kosongkan jika tidak ingin mengubah password"
                        help="Minimal 6 karakter. Kosongkan jika tidak ingin mengubah password"
                        :error="$errors->first('password')"
                    />
                    <x-forms.field-input 
                        label="Konfirmasi Password Baru" 
                        name="password_confirmation" 
                        type="password"
                        placeholder="Kosongkan jika tidak ingin mengubah password"
                        :error="$errors->first('password_confirmation')"
                    />
                </x-forms.row-2>
            </x-forms.section>

            <x-forms.section title="Informasi Akun">
                <div style="display: grid; gap: 12px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <strong style="font-size: 0.9rem;">ID:</strong>
                            <p style="font-size: 0.9rem; color: #495057; margin-top: 4px;">{{ $officer->id }}</p>
                        </div>
                        <div>
                            <strong style="font-size: 0.9rem;">Dibuat:</strong>
                            <p style="font-size: 0.9rem; color: #495057; margin-top: 4px;">{{ $officer->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div>
                        <strong style="font-size: 0.9rem;">Terakhir Diperbarui:</strong>
                        <p style="font-size: 0.9rem; color: #495057; margin-top: 4px;">{{ $officer->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </x-forms.section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.mbg-officers.show', $officer->id) }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
