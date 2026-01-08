@extends('layouts.app')

@section('title', 'Tambah Kelas - SekolahKu Admin')

@section('page-title', 'Tambah Kelas')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form-pages.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header card-header-form">
        <h4>Form Tambah Data Kelas</h4>
    </div>
    <div class="card-body card-body-form">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.classes.store') }}" method="POST">
            @csrf
            
            <x-forms.section title="Informasi Kelas">
                <x-forms.row-3>
                    <x-forms.field-select 
                        label="Kelas" 
                        name="class" 
                        required 
                        :options="['10' => '10', '11' => '11', '12' => '12']"
                        :error="$errors->first('class')"
                    />
                    <x-forms.field-select 
                        label="Jurusan/Program Keahlian" 
                        name="major" 
                        :options="[
                            '' => 'Pilih Jurusan (opsional)',
                            'Kuliner 1' => 'Kuliner 1',
                            'Kuliner 2' => 'Kuliner 2',
                            'Kuliner 3' => 'Kuliner 3',
                            'Kuliner 4' => 'Kuliner 4',
                            'Kuliner 5' => 'Kuliner 5'
                        ]"
                        help="Bidang keahlian atau program studi (opsional)"
                        :error="$errors->first('major')"
                    />
                </x-forms.row-3>

                <x-forms.row-full>
                    <x-forms.field-select 
                        label="Wali Kelas" 
                        name="homeroom_teacher_id" 
                        :options="$teachers->mapWithKeys(function($teacher) {
                            $homeroomCount = $teacher->homeroomClasses->count();
                            $hasClass = $homeroomCount > 0;
                            $className = $hasClass ? $teacher->homeroomClasses->first()->class : '';
                            $label = $hasClass 
                                ? $teacher->name . ' ' . $teacher->nip . ' (sudah memiliki Kelas ' . $className . ')' 
                                : $teacher->name . ' ' . $teacher->nip . ' (Belum Memiliki Kelas)';
                            return [$teacher->id => $label];
                        })->prepend('Belum ditentukan', '')->toArray()"
                        help="Guru yang bertugas sebagai wali kelas"
                        :error="$errors->first('homeroom_teacher_id')"
                    />
                </x-forms.row-full>
            </x-forms.section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
