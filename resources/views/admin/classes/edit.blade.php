@extends('layouts.app')

@section('title', 'Edit Kelas - SekolahKu Admin')

@section('page-title', 'Edit Kelas')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form-pages.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header card-header-form">
        <h4>Form Edit Data Kelas</h4>
    </div>
    <div class="card-body card-body-form">
        <form action="{{ route('admin.classes.update', $class->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <x-forms.section title="Informasi Kelas">
                <x-forms.row-3>
                    <x-forms.field-input 
                        label="Kelas" 
                        name="class" 
                        type="number"
                        min="1"
                        max="99"
                        placeholder="Contoh: 10, 11, 12"
                        required 
                        :value="old('class', $class->class)"
                        :error="$errors->first('class')"
                    />
                    <x-forms.field-input 
                        label="Jurusan/Program Keahlian" 
                        name="major" 
                        type="text"
                        placeholder="Contoh: RPL, Kuliner 1, IPA"
                        help="Bidang keahlian atau program studi (opsional)"
                        :value="old('major', $class->major)"
                        :error="$errors->first('major')"
                    />
                </x-forms.row-3>

                <x-forms.row-full>
                    <x-forms.field-select 
                        label="Wali Kelas" 
                        name="homeroom_teacher_id" 
                        :value="old('homeroom_teacher_id', $class->homeroom_teacher_id)"
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
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
