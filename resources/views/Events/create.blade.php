@extends('layouts.app')

@section('title', 'Tambah Event')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Informasi</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <a href="{{ route('events.index') }}" style="color:var(--text-muted);text-decoration:none;">Event / Acara</a>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Tambah Event</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Events/style.css')) !!}</style>
@endpush

@section('content')
<div class="ev-page">

    @if(session('success'))
    <div class="alert alert--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('events.store') }}" method="POST" data-loading>
        @csrf
        <div class="edit-card">
            <div class="edit-card-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <h2>Tambah Event Baru</h2>
            </div>

            <div class="edit-card-body">

                {{-- Informasi Event --}}
                <div>
                    <div class="form-section-title">Informasi Event</div>
                    <div class="form-grid-2">
                        <div class="form-group form-col-full">
                            <label class="form-label">Judul Event <span class="req">*</span></label>
                            <input type="text" name="title" class="form-control"
                                   placeholder="Masukkan judul event..."
                                   value="{{ old('title') }}" maxlength="100" required>
                            @error('title')
                                <span class="form-hint" style="color:#ef4444;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group form-col-full">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="4"
                                      placeholder="Tuliskan deskripsi event..." maxlength="1000">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="form-hint" style="color:#ef4444;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group form-col-full">
                            <label class="form-label">Link</label>
                            <input type="url" name="link" class="form-control"
                                   placeholder="https://contoh.com/event"
                                   value="{{ old('link') }}" maxlength="500">
                            @error('link')
                                <span class="form-hint" style="color:#ef4444;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Rilis <span class="req">*</span></label>
                            <input type="date" name="release_date" class="form-control"
                                   value="{{ old('release_date') }}" required>
                            @error('release_date')
                                <span class="form-hint" style="color:#ef4444;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Selesai <span class="req">*</span></label>
                            <input type="date" name="end_date" class="form-control"
                                   value="{{ old('end_date') }}" required>
                            @error('end_date')
                                <span class="form-hint" style="color:#ef4444;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Target Audiens --}}
                <div>
                    <div class="form-section-title">Target Audiens</div>
                    <div class="form-group">
                        <label class="form-label">Untuk Siapa? <span class="req">*</span></label>
                        <select name="target_audience" id="targetAudience" class="form-control" required>
                            <option value="semua"  {{ old('target_audience', 'semua') === 'semua'  ? 'selected' : '' }}>Semua (Guru & Siswa)</option>
                            <option value="guru"   {{ old('target_audience') === 'guru'   ? 'selected' : '' }}>Guru Saja</option>
                            <option value="siswa"  {{ old('target_audience') === 'siswa'  ? 'selected' : '' }}>Siswa Saja</option>
                            <option value="kelas"  {{ old('target_audience') === 'kelas'  ? 'selected' : '' }}>Kelas / Jurusan Tertentu</option>
                        </select>
                    </div>

                    {{-- Conditional: Kelas Target --}}
                    <div id="targetKelasSection" class="ev-target-section" style="display:none;">
                        <div class="ev-target-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            <span>Pilih target spesifik. Bisa memilih berdasarkan kelas, tingkat, atau jurusan. Bisa kombinasi.</span>
                        </div>

                        {{-- By Specific Class --}}
                        <div class="form-group" style="margin-top:14px;">
                            <label class="form-label">Pilih Kelas Spesifik</label>
                            <div class="ev-checkbox-grid">
                                @foreach($classes as $cls)
                                <label class="ev-checkbox-item">
                                    <input type="checkbox" name="target_classes[]" value="{{ $cls->id }}"
                                           {{ in_array($cls->id, old('target_classes', [])) ? 'checked' : '' }}>
                                    <span>Kelas {{ $cls->class }}{{ $cls->major ? ' - ' . $cls->major : '' }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- By Grade --}}
                        <div class="form-group" style="margin-top:14px;">
                            <label class="form-label">Atau Berdasarkan Tingkat</label>
                            <div class="ev-checkbox-grid">
                                @foreach($grades as $grade)
                                <label class="ev-checkbox-item">
                                    <input type="checkbox" name="target_grades[]" value="{{ $grade }}"
                                           {{ in_array($grade, old('target_grades', [])) ? 'checked' : '' }}>
                                    <span>Semua Kelas {{ $grade }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- By Major --}}
                        <div class="form-group" style="margin-top:14px;">
                            <label class="form-label">Atau Berdasarkan Jurusan</label>
                            <div class="ev-checkbox-grid">
                                @foreach($majors as $major)
                                <label class="ev-checkbox-item">
                                    <input type="checkbox" name="target_majors[]" value="{{ $major }}"
                                           {{ in_array($major, old('target_majors', [])) ? 'checked' : '' }}>
                                    <span>{{ $major }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- end edit-card-body --}}

            <div class="edit-card-footer">
                <a href="{{ route('events.index') }}" class="btn btn--ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Kembali
                </a>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <span class="btn-text">Simpan Event</span>
                </button>
            </div>
        </div>{{-- end edit-card --}}
    </form>

</div>
@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Events/main.js')) !!}</script>
@endpush
