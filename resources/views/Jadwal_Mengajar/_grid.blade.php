{{-- _grid.blade.php
     Variables: $mode ('teacher'|'class'), $allPeriodsByDay, $byPeriod (keyed by class_period_id)
--}}
@php
    use App\Models\ClassPeriod;
    $days      = ClassPeriod::DAYS;
    $dayLabels = ClassPeriod::DAY_LABELS;
    $totalSlots = $byPeriod->count();

    $perDayCount = [];
    foreach ($days as $d) {
        $perDayCount[$d] = $byPeriod->filter(fn($s) => $s->classPeriod->day === $d)->count();
    }
@endphp

{{-- Summary cards --}}
<div class="jm-summary" style="margin-bottom:16px;">
    <div class="jm-summary-card">
        <span class="jm-summary-val">{{ $totalSlots }}</span>
        <span class="jm-summary-label">Total Jam Mengajar</span>
    </div>
    @if($mode === 'teacher')
    <div class="jm-summary-card">
        <span class="jm-summary-val">{{ $byPeriod->map(fn($s) => $s->class_id)->unique()->count() }}</span>
        <span class="jm-summary-label">Kelas Diajar</span>
    </div>
    @else
    <div class="jm-summary-card">
        <span class="jm-summary-val">{{ $byPeriod->map(fn($s) => $s->teacher_id)->unique()->count() }}</span>
        <span class="jm-summary-label">Guru Mengajar</span>
    </div>
    @endif
    @foreach($days as $day)
    <div class="jm-summary-card">
        <span class="jm-summary-val">{{ $perDayCount[$day] }}</span>
        <span class="jm-summary-label">{{ $dayLabels[$day] }}</span>
    </div>
    @endforeach
</div>

{{-- Day panels (all shown, stacked with spacing) --}}
@foreach($days as $day)
@php
    $dayPeriods   = $allPeriodsByDay[$day] ?? collect();
    $daySchedules = $byPeriod->filter(fn($s) => $s->classPeriod->day === $day);
@endphp
<div class="jm-day-panel" id="jm-panel-{{ $day }}">
    <div class="jm-day-label">{{ $dayLabels[$day] }}</div>
    <div class="jm-grid-wrap">
    <table class="jm-grid">
        <thead>
            <tr>
                <th width="70">Jam Ke</th>
                <th width="150">Waktu</th>
                <th>Mata Pelajaran</th>
                @if($mode === 'teacher')
                <th>Kelas</th>
                @else
                <th>Guru Pengajar</th>
                @endif
                <th width="110" style="text-align:center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($dayPeriods as $period)
        @php
            $sched    = $daySchedules->firstWhere('class_period_id', $period->id);
            $isLesson = $period->activity_type === 'lesson';
            $rowCls   = match($period->activity_type) {
                'break'    => 'jm-row-break',
                'ceremony' => 'jm-row-ceremony',
                'prayer'   => 'jm-row-prayer',
                'cleaning' => 'jm-row-cleaning',
                default    => '',
            };
        @endphp
        <tr class="{{ $rowCls }}">
            {{-- Jam ke --}}
            <td style="text-align:center;">
                @if($isLesson)
                    <span class="jp-badge jp-badge--lesson" style="width:32px;justify-content:center;">{{ $period->sequence }}</span>
                @else
                    <span style="color:var(--text-muted);">&mdash;</span>
                @endif
            </td>

            {{-- Waktu --}}
            <td class="jm-td-time">
                <strong>{{ substr($period->start_time,0,5) }}</strong>
                <span style="color:var(--text-muted);"> s.d. </span>
                <strong>{{ substr($period->end_time,0,5) }}</strong>
            </td>

            @if(!$isLesson)
            {{-- Non-lesson: span 3 columns --}}
            <td colspan="3" class="jm-td-activity">
                <span class="jp-badge jp-badge--{{ $period->activity_type }}">
                    {{ $period->note ?? $period->activity_label }}
                </span>
                <span style="font-size:0.75rem;color:var(--text-muted);margin-left:6px;">
                    {{ $period->duration_minutes }} menit
                </span>
            </td>
            @else
            {{-- Lesson: Mapel --}}
            <td style="padding:8px 12px;">
                @if($sched)
                    <span style="font-weight:700;font-size:0.85rem;">{{ $sched->subject->name }}</span>
                @else
                    <span style="color:var(--text-muted);font-size:0.82rem;">— Belum diisi —</span>
                @endif
            </td>

            {{-- Lesson: Kelas / Guru --}}
            <td style="padding:8px 12px;">
                @if($sched)
                    @if($mode === 'teacher')
                        <span class="jp-badge jp-badge--lesson">{{ $sched->schoolClass->short_name }}</span>
                    @else
                        <span style="font-size:0.84rem;">{{ $sched->teacher->name }}</span>
                    @endif
                @endif
            </td>

            {{-- Aksi --}}
            <td style="text-align:center;">
                @if($sched)
                <div class="jm-action">
                    <button class="jm-action__btn" title="Aksi">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>
                        </svg>
                    </button>
                    <div class="jm-dropdown">
                        <button class="jm-dropdown__item jm-dropdown-edit"
                                data-id="{{ $sched->id }}"
                                data-teacher="{{ $sched->teacher_id }}"
                                data-subject="{{ $sched->subject_id }}"
                                data-class="{{ $sched->class_id }}"
                                data-period="{{ $sched->class_period_id }}"
                                data-day="{{ $day }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit
                        </button>
                        <div class="jm-dropdown__separator"></div>
                        <button class="jm-dropdown__item jm-dropdown__item--danger jm-dropdown-del"
                                data-id="{{ $sched->id }}"
                                data-desc="{{ $sched->teacher->name }} · {{ $sched->subject->name }} · {{ $sched->schoolClass->short_name }} · {{ $dayLabels[$day] }} Jam {{ $period->sequence }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            Hapus
                        </button>
                    </div>
                </div>
                @else
                <button class="jm-add-slot-btn" title="Isi Jadwal"
                    data-period="{{ $period->id }}"
                    data-day="{{ $day }}"
                    data-seq="{{ $period->sequence }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                @endif
            </td>
            @endif
        </tr>
        @endforeach
        @if($dayPeriods->isEmpty())
        <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted);">
            Tidak ada data slot untuk hari ini.
        </td></tr>
        @endif
        </tbody>
    </table>
    </div>
</div>
@endforeach
