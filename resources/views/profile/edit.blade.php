@extends('layouts.app')

@section('title', 'Profil - SekolahKu Admin')

@section('page-title', 'Profil')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Update Informasi Profil</h5>
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Update Password</h5>
            </div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Logout</h5>
            </div>
            <div class="card-body">
                @include('profile.partials.logout-form')
            </div>
        </div>

        <div class="card mt-4 border-danger">
            <div class="card-header bg-light">
                <h5 class="mb-0">Hapus Akun</h5>
            </div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
