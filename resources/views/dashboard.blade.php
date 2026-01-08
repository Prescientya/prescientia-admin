@extends('layouts.app')

@section('title', 'Dashboard - SekolahKu Admin')

@section('page-title', 'Dashboard')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-content p {
            color: #6b7280;
            font-size: 14px;
            margin: 0 0 8px 0;
        }
        
        .stat-content h3 {
            color: #111827;
            font-size: 32px;
            font-weight: bold;
            margin: 8px 0;
        }
        
        .stat-content .change {
            color: #10b981;
            font-size: 12px;
            margin-top: 8px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin-left: 16px;
        }
        
        .stat-icon.blue { background-color: #3b82f6; }
        .stat-icon.green { background-color: #10b981; }
        .stat-icon.purple { background-color: #a855f7; }
        .stat-icon.orange { background-color: #f97316; }
        
        .chart-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .chart-card h3 {
            color: #111827;
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 8px 0;
        }
        
        .chart-card p {
            color: #6b7280;
            font-size: 14px;
            margin: 0 0 20px 0;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
        }
        
        .bottom-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .activity-item {
            display: flex;
            gap: 12px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 6px;
        }
        
        .activity-content p:first-child {
            color: #111827;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
        }
        
        .activity-content p:last-child {
            color: #6b7280;
            font-size: 12px;
            margin: 4px 0 0 0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #6b7280;
            font-size: 14px;
        }
        
        .info-value {
            color: #111827;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
        }
        
        .status-text {
            color: #10b981;
            font-weight: 600;
            font-size: 14px;
        }
    </style>
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <!-- Total Siswa -->
        <div class="stat-card">
            <div class="stat-content">
                <p>Total Siswa</p>
                <h3>{{ $totalSiswa ?? 0 }}</h3>
                <div class="change">↑ +5% dari bulan lalu</div>
            </div>
            <div class="stat-icon blue">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>

        <!-- Total Guru -->
        <div class="stat-card">
            <div class="stat-content">
                <p>Total Guru</p>
                <h3>{{ $totalGuru ?? 0 }}</h3>
                <div class="change">↑ +2 guru baru</div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-chalkboard-user"></i>
            </div>
        </div>

        <!-- Total Kelas -->
        <div class="stat-card">
            <div class="stat-content">
                <p>Total Kelas</p>
                <h3>{{ $totalKelas ?? 0 }}</h3>
                <div class="change">&nbsp;</div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-book"></i>
            </div>
        </div>

        <!-- Login Hari Ini -->
        <div class="stat-card">
            <div class="stat-content">
                <p>Login Hari Ini</p>
                <h3>{{ $loginHariIni ?? 0 }}</h3>
                <div class="change">↑ +12% dari kemarin</div>
            </div>
            <div class="stat-icon orange">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>

    <!-- Grafik Login Harian -->
    <div class="chart-card">
        <h3>Grafik Kehadiran Harian</h3>
        <p>7 hari terakhir - Data dari tabel attendance</p>
        <div class="chart-container">
            <canvas id="loginChart"></canvas>
        </div>
    </div>

    <!-- Row 2: Pie Chart + System Info -->
    <div class="bottom-grid">
        <!-- Distribusi Login Berdasarkan Peran -->
        <div class="chart-card">
            <h3>Distribusi Login Berdasarkan Peran</h3>
            <p>Total {{ $totalLoginDistribusi ?? 0 }} login</p>
            <div class="chart-container" style="height: 300px;">
                <canvas id="roleDistributionChart"></canvas>
            </div>
        </div>

        <!-- Ringkasan Sistem -->
        <div class="chart-card">
            <h3>Ringkasan Sistem</h3>
            <div style="margin-top: 24px;">
                <div class="info-row">
                    <span class="info-label">Tahun Ajaran</span>
                    <span class="info-value">{{ $tahunAjaran ?? '2024/2025' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Semester</span>
                    <span class="info-value">{{ $semester ?? 'Ganjil' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Pengguna</span>
                    <span class="info-value">{{ $totalPengguna ?? 0 }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status Sistem</span>
                    <span class="status-badge">
                        <span class="status-dot"></span>
                        <span class="status-text">Aktif</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Grafik Login Harian (Line Chart)
    const loginCtx = document.getElementById('loginChart').getContext('2d');
    const loginChart = new Chart(loginCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartDates ?? []) !!},
            datasets: [
                {
                    label: 'Siswa Hadir',
                    data: {!! json_encode($siswaloginData ?? []) !!},
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.05)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 5,
                    pointBackgroundColor: '#3B82F6',
                },
                {
                    label: 'Guru Hadir',
                    data: {!! json_encode($guruLoginData ?? []) !!},
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 5,
                    pointBackgroundColor: '#10B981',
                },
                {
                    label: 'Admin Login',
                    data: {!! json_encode($adminLoginData ?? []) !!},
                    borderColor: '#F97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.05)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 5,
                    pointBackgroundColor: '#F97316',
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12 }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 80,
                    ticks: {
                        stepSize: 20
                    }
                }
            }
        }
    });

    // Distribusi Login Berdasarkan Peran (Doughnut Chart)
    const roleCtx = document.getElementById('roleDistributionChart').getContext('2d');
    const roleChart = new Chart(roleCtx, {
        type: 'doughnut',
        data: {
            labels: ['Siswa', 'Guru', 'Admin'],
            datasets: [{
                data: {!! json_encode($roleDistributionData ?? [0, 0, 0]) !!},
                backgroundColor: [
                    '#3B82F6',
                    '#10B981',
                    '#F97316',
                ],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        padding: 20,
                        font: { size: 12 }
                    }
                }
            }
        }
    });
</script>
@endsection
