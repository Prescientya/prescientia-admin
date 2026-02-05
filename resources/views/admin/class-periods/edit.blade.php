@extends('layouts.app')

@section('title', 'Edit Jam Pelajaran - Admin')

@section('page-title', 'Edit Jam Pelajaran')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Form Edit Jam Pelajaran</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.class-periods.update', $classPeriod->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="day" class="form-label">Hari <span class="text-danger">*</span></label>
                            <select name="day" id="day" class="form-select @error('day') is-invalid @enderror" required>
                                <option value="">-- Pilih Hari --</option>
                                @foreach($days as $day)
                                <option value="{{ $day }}" {{ old('day', $classPeriod->day) === $day ? 'selected' : '' }}>
                                    {{ ucfirst($day) }}
                                </option>
                                @endforeach
                            </select>
                            @error('day')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="sequence" class="form-label">Urutan <span class="text-danger">*</span></label>
                            <input type="number" name="sequence" id="sequence" class="form-control @error('sequence') is-invalid @enderror" value="{{ old('sequence', $classPeriod->sequence) }}" required min="1">
                            @error('sequence')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', $classPeriod->start_time) }}" required>
                                    @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', $classPeriod->end_time) }}" required>
                                    @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="duration_minutes" class="form-label">Durasi (menit)</label>
                            <input type="number" name="duration_minutes" id="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" value="{{ old('duration_minutes', $classPeriod->duration_minutes) }}" min="1">
                            @error('duration_minutes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="activity_type" class="form-label">Jenis Aktivitas <span class="text-danger">*</span></label>
                            <select name="activity_type" id="activity_type" class="form-select @error('activity_type') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis --</option>
                                @foreach($activityTypes as $type)
                                <option value="{{ $type }}" {{ old('activity_type', $classPeriod->activity_type) === $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                                @endforeach
                            </select>
                            @error('activity_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.class-periods.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
