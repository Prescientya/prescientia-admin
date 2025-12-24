@extends('layouts.app')

@section('title', 'Detail Absensi')

@section('page-title', 'Detail Absensi')

@section('content')
<div class="card">
    <div class="card-body">
        <h5>Detail Absensi</h5>
        <div class="mb-3">
            @if($role === 'teachers')
                <p><strong>Nama:</strong> {{ $attendance->teacher->name }}</p>
                <p><strong>Jabatan:</strong> {{ $attendance->teacher->position ?? '-' }}</p>
            @else
                <p><strong>Nama:</strong> {{ $attendance->student->name }}</p>
                <p><strong>Kelas:</strong> {{ $attendance->class->class ?? '-' }} {{ $attendance->class->major ?? '' }}</p>
            @endif

            <p><strong>Tanggal:</strong> {{ optional($attendance->calendar)->date ? \Carbon\Carbon::parse($attendance->calendar->date)->format('Y-m-d') : '-' }}</p>
            <p><strong>Check In:</strong> {{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s') : '-' }}</p>
            <p><strong>Check Out:</strong> {{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i:s') : '-' }}</p>
            <p><strong>Status:</strong> <span class="status-badge status-{{ $attendance->status }}">{{ ucfirst($attendance->status) }}</span></p>
            <p><strong>Sumber:</strong> {{ $attendance->source ?? '-' }}</p>
        </div>

        <hr>
        <h6>Edit Status</h6>
        <form method="POST" action="{{ route('admin.attendances.update', ['role' => $role, 'id' => $attendance->id]) }}">
            @csrf
            @method('PUT')
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @php
                            $options = ($role === 'teachers') ? ['hadir','sakit','izin','dinas','alpa'] : ['hadir','sakit','izin','alpa'];
                        @endphp
                        @foreach($options as $opt)
                            <option value="{{ $opt }}" {{ $attendance->status == $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 align-self-end">
                    <button class="btn btn-success" type="submit">Simpan</button>
                </div>
            </div>
        </form>

        <div class="mt-3">
            <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@endsection
