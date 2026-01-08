@if($attendances->count() > 0)
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th style="width:50px;">No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Status</th>
                <th>Sumber</th>
                <th style="width:80px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $k => $r)
                <tr>
                    <td>{{ $attendances->firstItem() + $k }}</td>
                    <td>{{ $r->date ? \Carbon\Carbon::parse($r->date)->format('Y-m-d') : '-' }}</td>
                    <td>
                        @if($r->role == 'teachers')
                            <span style="background-color: #e6f7ff; color: #0d6efd; font-weight:600; border-radius:6px; padding:2px 4px; display:inline-block; width:120px">{{ $r->name }}</span>
                        @else
                            <span>{{ $r->name }}</span>
                        @endif
                    </td>
                    <td><span class="status-badge status-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                    <td><small>{{ $r->source ? str_replace('_', ' ', ucfirst($r->source)) : '-' }}</small></td>
                    <td>
                        <div class="action-menu-container">
                            <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                            </button>
                            <ul class="dropdown-menu" style="display: none;">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.attendances.show', ['role' => $r->role, 'id' => $r->id]) }}">Lihat Detail</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.attendances.show', ['role' => $r->role, 'id' => $r->id]) }}">Edit</a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-3">
    <div class="me-3 align-self-center">Page {{ $attendances->currentPage() }} of {{ $attendances->lastPage() }}</div>
    {{ $attendances->appends(request()->except('page'))->links() }}
    @if($attendances->lastPage() > 1)
        <div class="ms-3 align-self-center">
            <a href="{{ $attendances->url($attendances->lastPage()) }}" class="btn btn-sm btn-outline-secondary" data-last-page>Last</a>
        </div>
    @endif
</div>
@else
    <div class="alert alert-info text-center">Belum ada data absensi untuk filter ini</div>
@endif
