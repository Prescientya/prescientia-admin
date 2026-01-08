@extends('layouts.app')

@section('title', 'Tambah Petugas MBG - SekolahKu Admin')

@section('page-title', 'Tambah Petugas MBG')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form-pages.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header card-header-form">
        <h4>Form Tambah Petugas MBG</h4>
    </div>
    <div class="card-body card-body-form">
        <form method="POST" action="{{ route('admin.mbg-officers.store') }}">
            @csrf

            <x-forms.section title="Kredensial Akun">
                <x-forms.row-2>
                    <x-forms.field-input 
                        label="Username" 
                        name="username" 
                        required 
                        :error="$errors->first('username')"
                    />
                    <x-forms.field-input 
                        label="Password" 
                        name="password" 
                        type="password"
                        required 
                        help="Minimal 6 karakter"
                        :error="$errors->first('password')"
                    />
                </x-forms.row-2>

                <x-forms.row-2>
                    <x-forms.field-input 
                        label="Konfirmasi Password" 
                        name="password_confirmation" 
                        type="password"
                        required 
                        :error="$errors->first('password_confirmation')"
                    />
                </x-forms.row-2>
            </x-forms.section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('admin.mbg-officers.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
