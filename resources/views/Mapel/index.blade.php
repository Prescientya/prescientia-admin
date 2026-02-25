@extends('layouts.app')

@section('title', 'Mata Pelajaran')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Akademik</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <span>Mata Pelajaran</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Mapel/style.css')) !!}</style>
@endpush

@section('content')
<div class="mp-page">

    {{-- Header --}}
    <div class="mp-header">
        <div>
            <div class="mp-title">Mata Pelajaran</div>
            <div class="mp-subtitle">Kelola daftar mata pelajaran &amp; penugasan kelas</div>
        </div>
        <button class="mp-btn mp-btn-save" id="mp-add-btn">
            + Tambah Mata Pelajaran
        </button>
    </div>

    {{-- Stats --}}
    <div class="mp-stats">
        <div class="mp-stat-card">
            <div class="mp-stat-label">Total Mapel</div>
            <div class="mp-stat-value">{{ $subjects->count() }}</div>
        </div>
        <div class="mp-stat-card">
            <div class="mp-stat-label">Mapel Aktif</div>
            <div class="mp-stat-value">{{ $subjects->where('is_active', true)->count() }}</div>
        </div>
        <div class="mp-stat-card">
            <div class="mp-stat-label">Mapel Non-Aktif</div>
            <div class="mp-stat-value">{{ $subjects->where('is_active', false)->count() }}</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="mp-card">
        @if($subjects->isEmpty())
        <div class="mp-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p>Belum ada mata pelajaran. Klik <strong>Tambah Mata Pelajaran</strong> untuk memulai.</p>
        </div>
        @else
        <div class="mp-table-wrap">
            <table class="mp-table" id="mp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Mapel</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Total Kelas</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjects as $i => $s)
                    <tr data-id="{{ $s->id }}">
                        <td style="color:var(--text-muted);font-size:.78rem;">{{ $i + 1 }}</td>
                        <td>
                            <div class="mp-name">{{ $s->name }}</div>
                        </td>
                        <td>
                            <div class="mp-desc">{{ $s->description ?: '—' }}</div>
                        </td>
                        <td>
                            @if($s->is_active)
                                <span class="badge badge-green">Aktif</span>
                            @else
                                <span class="badge badge-gray">Non-Aktif</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-blue">{{ $s->jumlah_kelas }} kelas</span>
                        </td>
                        <td>
                            {{-- Will be populated via JS after classOptions call, or show nothing --}}
                            <div class="mp-classes-preview" id="mp-chips-{{ $s->id }}">
                                <span style="color:var(--text-muted);font-size:.75rem;">—</span>
                            </div>
                        </td>
                        <td>
                            <div class="mp-action-group">
                                <button class="mp-btn-icon mp-btn-edit"
                                        data-id="{{ $s->id }}"
                                        data-name="{{ $s->name }}"
                                        data-description="{{ $s->description }}"
                                        data-active="{{ $s->is_active ? '1' : '0' }}"
                                        title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <button class="mp-btn-icon mp-btn-del"
                                        data-id="{{ $s->id }}"
                                        data-name="{{ $s->name }}"
                                        title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6"/><path d="M14 11v6"/>
                                        <path d="M9 6V4h6v2"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     MODAL
═════════════════════════════════════════════════════════════ --}}
<div class="mp-overlay" id="mp-overlay">
    <div class="mp-modal" id="mp-modal">
        <div class="mp-modal-header">
            <div class="mp-modal-title" id="mp-modal-title">Tambah Mata Pelajaran</div>
            <button class="mp-modal-close" id="mp-modal-close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- Tab bar --}}
        <div class="mp-tab-bar">
            <button class="mp-tab-btn active" data-tab="info">Info Dasar</button>
            <button class="mp-tab-btn" data-tab="kelas">Penugasan Kelas</button>
        </div>

        <form id="mp-form" novalidate>
            @csrf
            <input type="hidden" id="mp-subject-id" name="_subject_id" value="">
            <input type="hidden" name="_method" id="mp-method" value="POST">

            {{-- Tab 1: Info Dasar --}}
            <div class="mp-tab-panel active" data-panel="info">
                <div class="mp-form-group">
                    <label class="mp-label" for="mp-name">Nama Mata Pelajaran <span style="color:#ef4444">*</span></label>
                    <input class="mp-input" type="text" id="mp-name" name="name"
                           placeholder="e.g. Matematika" maxlength="100" required>
                </div>
                <div class="mp-form-group">
                    <label class="mp-label" for="mp-desc">Deskripsi</label>
                    <textarea class="mp-textarea" id="mp-desc" name="description"
                              placeholder="Deskripsi singkat (opsional)" maxlength="500"></textarea>
                </div>
                <div class="mp-form-group">
                    <label class="mp-label">Status</label>
                    <label class="mp-toggle-row">
                        <span class="mp-toggle">
                            <input type="checkbox" id="mp-active" name="is_active" value="1" checked>
                            <span class="mp-toggle-slider"></span>
                        </span>
                        <span id="mp-active-label">Aktif</span>
                    </label>
                </div>
            </div>

            {{-- Tab 2: Penugasan Kelas --}}
            <div class="mp-tab-panel" data-panel="kelas">
                <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:12px;">
                    Pilih aturan penugasan kelas untuk mata pelajaran ini.
                </p>

                {{-- Rule-type pills --}}
                <div class="mp-rule-pills">
                    <button type="button" class="mp-rule-pill active" data-rule="semua">Semua Kelas</button>
                    <button type="button" class="mp-rule-pill" data-rule="tingkat">Per Tingkat</button>
                    <button type="button" class="mp-rule-pill" data-rule="jurusan">Per Jurusan</button>
                    <button type="button" class="mp-rule-pill" data-rule="tingkat_jurusan">Tingkat + Jurusan</button>
                    <button type="button" class="mp-rule-pill" data-rule="manual">Kelas Manual</button>
                </div>
                <input type="hidden" id="mp-rule-type" name="rule_type" value="semua">

                {{-- Panel: semua --}}
                <div class="mp-rule-panel active" data-rulepanel="semua">
                    <div style="color:var(--text-muted);font-size:.85rem;padding:10px 0;">
                        Mata pelajaran akan diajarkan di <strong>semua 48 kelas</strong>.
                    </div>
                </div>

                {{-- Panel: tingkat --}}
                <div class="mp-rule-panel" data-rulepanel="tingkat">
                    <div class="mp-cb-group">
                        <div class="mp-cb-group-title">Pilih Tingkat</div>
                        <div class="mp-cb-grid" id="mp-grades-cb">
                            @foreach([10, 11, 12] as $g)
                            <label class="mp-cb-item">
                                <input type="checkbox" name="rule_params[grades][]" value="{{ $g }}"
                                       class="mp-grade-cb"> Kelas {{ $g }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Panel: jurusan --}}
                <div class="mp-rule-panel" data-rulepanel="jurusan">
                    <div class="mp-cb-group">
                        <div class="mp-cb-group-title">Pilih Jurusan</div>
                        <div class="mp-cb-grid" id="mp-majors-cb">
                            @foreach($majors as $m)
                            <label class="mp-cb-item">
                                <input type="checkbox" name="rule_params[majors][]" value="{{ $m }}"
                                       class="mp-major-cb"> {{ $m }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Panel: tingkat_jurusan --}}
                <div class="mp-rule-panel" data-rulepanel="tingkat_jurusan">
                    <div class="mp-cb-group">
                        <div class="mp-cb-group-title">Tingkat</div>
                        <div class="mp-cb-grid" id="mp-tj-grades-cb">
                            @foreach([10, 11, 12] as $g)
                            <label class="mp-cb-item">
                                <input type="checkbox" name="rule_params[grades][]" value="{{ $g }}"
                                       class="mp-tj-grade-cb"> Kelas {{ $g }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mp-cb-group">
                        <div class="mp-cb-group-title">Jurusan</div>
                        <div class="mp-cb-grid" id="mp-tj-majors-cb">
                            @foreach($majors as $m)
                            <label class="mp-cb-item">
                                <input type="checkbox" name="rule_params[majors][]" value="{{ $m }}"
                                       class="mp-tj-major-cb"> {{ $m }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Panel: manual --}}
                <div class="mp-rule-panel" data-rulepanel="manual">
                    @foreach($grouped as $grade => $classes)
                    <div class="mp-cb-group">
                        <div class="mp-cb-group-title">Kelas {{ $grade }}</div>
                        <div class="mp-cb-grid">
                            @foreach($classes as $c)
                            <label class="mp-cb-item">
                                <input type="checkbox" name="class_ids[]" value="{{ $c->id }}"
                                       class="mp-manual-cb"> {{ $c->major }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Preview --}}
                <div class="mp-preview" id="mp-preview">
                    Akan diterapkan ke <strong>semua kelas</strong>.
                </div>
            </div>

            <div class="mp-modal-footer">
                <button type="button" class="mp-btn mp-btn-cancel" id="mp-cancel-btn">Batal</button>
                <button type="submit" class="mp-btn mp-btn-save" id="mp-save-btn">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Toast --}}
<div class="mp-toast" id="mp-toast"></div>

{{-- Data payload for JS --}}
<script>
window.MP_DATA = {
    storeUrl:  "{{ route('mapel.store') }}",
    updateUrl: "{{ url('/mapel') }}",
    destroyUrl: "{{ url('/mapel') }}",
    csrfToken: "{{ csrf_token() }}",
    classOptionsUrl: "{{ url('/mapel/class-options') }}",
    totalClasses: {{ $allClasses->count() }},
};
</script>
@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Mapel/main.js')) !!}</script>
@endpush
