<table class="jp-table">
    <thead>
        <tr>
            <th width="70">Jam Ke</th>
            <th>Waktu (s.d.)</th>
            <th width="80">Durasi</th>
            <th>Keterangan / Aktivitas</th>
            <th>Catatan</th>
            <th width="90">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($periods as $p)
        @php
            $lessonNo = $p->activity_type === 'lesson'
                ? $periods->where('activity_type', 'lesson')->search(fn($r) => $r->id === $p->id) + 1
                : null;
        @endphp
        <tr class="jp-row jp-row--{{ $p->activity_type }}" data-id="{{ $p->id }}">
            <td class="jp-jam-ke">
                @if($p->activity_type === 'lesson')
                    <span class="jp-jam-badge">{{ $p->sequence }}</span>
                @else
                    <span class="jp-jam-badge jp-jam-badge--off">&mdash;</span>
                @endif
            </td>
            <td class="jp-waktu">
                <strong>{{ substr($p->start_time,0,5) }}</strong>
                <span style="color:var(--text-muted);"> s.d. </span>
                <strong>{{ substr($p->end_time,0,5) }}</strong>
            </td>
            <td class="jp-durasi">{{ $p->duration_minutes }} mnt</td>
            <td>
                <span class="jp-badge jp-badge--{{ $p->activity_type }}">{{ $p->activity_label }}</span>
            </td>
            <td class="jp-note">{{ $p->note ?? '—' }}</td>
            <td>
                <div class="jp-actions">
                    <button class="jp-btn-edit" data-id="{{ $p->id }}"
                        data-day="{{ $p->day }}"
                        data-sequence="{{ $p->sequence }}"
                        data-start="{{ substr($p->start_time,0,5) }}"
                        data-end="{{ substr($p->end_time,0,5) }}"
                        data-type="{{ $p->activity_type }}"
                        data-note="{{ $p->note ?? '' }}"
                        title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </button>
                    <button class="jp-btn-delete" data-id="{{ $p->id }}"
                        data-desc="{{ $p->day_label }} | Seq {{ $p->sequence }} | {{ substr($p->start_time,0,5) }}–{{ substr($p->end_time,0,5) }} ({{ $p->activity_label }})"
                        title="Hapus">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6"/><path d="M14 11v6"/>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">
            Belum ada jadwal untuk hari ini.
        </td></tr>
        @endforelse
    </tbody>
</table>
