@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-body">
            <h3>Nama Kelas Tidak Ditemukan</h3>
            <p>Beberapa nama kelas yang ada di file Excel tidak ditemukan di sistem. Pilih nama kelas yang ingin dibuat baru, lalu lanjutkan import.</p>

            <form method="POST" action="{{ route('admin.students.import.process') }}">
                @csrf
                <input type="hidden" name="path" value="{{ $path }}">

                <div class="mb-3">
                    @foreach($missing as $m)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="create_classes[]" value="{{ $m }}" id="c_{{ md5($m) }}" checked>
                            <label class="form-check-label" for="c_{{ md5($m) }}">Buat kelas: <strong>{{ $m }}</strong></label>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Buat dan Lanjutkan Import</button>
                    <a href="{{ route('admin.students.import.form') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
