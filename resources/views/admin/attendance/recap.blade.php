@extends('layouts.app')

@section('title', 'Rekap Absensi Guru Per Hari - Admin')

@section('page-title', 'Rekap Absensi Guru Per Hari')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.attendance.recap') }}" method="GET" class="row align-items-end">
                        <div class="col-md-4">
                            <label for="date" class="form-label">Pilih Tanggal</label>
                            <input type="date" name="date" id="date" class="form-control" value="{{ $selectedDate }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Tampilkan
                            </button>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5 class="mb-0">Hari: <span class="badge bg-info">{{ ucfirst($dayName) }}</span></h5>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Rekap Absensi Guru - {{ date('d F Y', strtotime($selectedDate)) }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Nama Guru</th>
                            <th>Hari</th>
                            <th>Status Jam Pertama</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recapData as $index => $data)
                        <tr class="clickable-row" data-teacher-id="{{ $data['teacher']->id }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $data['teacher']->name }}</td>
                            <td>{{ ucfirst($dayName) }}</td>
                            <td>
                                @if($data['first_period_status'] === 'Hadir')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($data['first_period_status'] === 'Alpha')
                                    <span class="badge bg-danger">Alpha</span>
                                @else
                                    <span class="badge bg-secondary">Belum Absen</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info toggle-detail" data-target="detail-{{ $data['teacher']->id }}">
                                    <i class="fas fa-chevron-down"></i> Detail
                                </button>
                            </td>
                        </tr>
                        <tr class="detail-row" id="detail-{{ $data['teacher']->id }}" style="display:none;">
                            <td colspan="5">
                                <div class="p-3 bg-light">
                                    <h6 class="mb-3">Detail Per Periode - {{ $data['teacher']->name }}</h6>
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Periode</th>
                                                <th>Waktu</th>
                                                <th>Kelas</th>
                                                <th>Mata Pelajaran</th>
                                                <th>Status</th>
                                                <th>Foto</th>
                                                <th>Waktu Submit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data['details'] as $detail)
                                            <tr>
                                                <td>{{ $detail['period_sequence'] }}</td>
                                                <td>{{ $detail['period_time'] }}</td>
                                                <td>{{ $detail['class_name'] }}</td>
                                                <td>{{ $detail['subject_name'] }}</td>
                                                <td>
                                                    @if($detail['status'] === 'Hadir')
                                                        <span class="badge bg-success">Hadir</span>
                                                    @elseif($detail['status'] === 'Alpha')
                                                        <span class="badge bg-danger">Alpha</span>
                                                    @else
                                                        <span class="badge bg-secondary">Belum Absen</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($detail['photo_url'])
                                                        <a href="{{ $detail['photo_url'] }}" target="_blank" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-image"></i> Lihat
                                                        </a>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $detail['submitted_at'] ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Tidak ada guru dengan jadwal pada hari {{ ucfirst($dayName) }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.toggle-detail').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const detailRow = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        if (detailRow.style.display === 'none') {
            detailRow.style.display = 'table-row';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
            this.innerHTML = '<i class="fas fa-chevron-up"></i> Tutup';
        } else {
            detailRow.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
            this.innerHTML = '<i class="fas fa-chevron-down"></i> Detail';
        }
    });
});
</script>
@endsection

@section('css')
<style>
.clickable-row {
    cursor: default;
}
.detail-row {
    background-color: #f8f9fa;
}
.toggle-detail {
    transition: all 0.3s ease;
}
</style>
@endsection
