@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="dashboard-container">
    <!-- Dashboard Overview Container -->
    <div class="dashboard-overview-container">
        <div class="overview-header">
            <h2 class="overview-title">Dashboard Overview</h2>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-cards">
        <!-- Card Kelas 10 -->
        <div class="stat-card">
            <div class="stat-card-content">
                <div class="stat-info">
                    <h3 class="stat-title">Kelas 10</h3>
                    <p class="stat-number">{{ number_format($siswaKelas10) }}</p>
                    <p class="stat-description">Total Siswa</p>
                </div>
                <div class="stat-icon">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 4V6C15 7.1 14.1 8 13 8V22H11V16H9V22H7V8C5.9 8 5 7.1 5 6V4L3 7V9H1V7C1 5.9 1.9 5 3 5L12 1L21 5C22.1 5 23 5.9 23 7V9H21Z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Card Kelas 11 -->
        <div class="stat-card">
            <div class="stat-card-content">
                <div class="stat-info">
                    <h3 class="stat-title">Kelas 11</h3>
                    <p class="stat-number">{{ number_format($siswaKelas11) }}</p>
                    <p class="stat-description">Total Siswa</p>
                </div>
                <div class="stat-icon">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 4V6C15 7.1 14.1 8 13 8V22H11V16H9V22H7V8C5.9 8 5 7.1 5 6V4L3 7V9H1V7C1 5.9 1.9 5 3 5L12 1L21 5C22.1 5 23 5.9 23 7V9H21Z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Card Kelas 12 -->
        <div class="stat-card">
            <div class="stat-card-content">
                <div class="stat-info">
                    <h3 class="stat-title">Kelas 12</h3>
                    <p class="stat-number">{{ number_format($siswaKelas12) }}</p>
                    <p class="stat-description">Total Siswa</p>
                </div>
                <div class="stat-icon">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 4V6C15 7.1 14.1 8 13 8V22H11V16H9V22H7V8C5.9 8 5 7.1 5 6V4L3 7V9H1V7C1 5.9 1.9 5 3 5L12 1L21 5C22.1 5 23 5.9 23 7V9H21Z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Card Guru -->
        <div class="stat-card">
            <div class="stat-card-content">
                <div class="stat-info">
                    <h3 class="stat-title">Guru</h3>
                    <p class="stat-number">{{ number_format($totalGuru) }}</p>
                    <p class="stat-description">Total Guru</p>
                </div>
                <div class="stat-icon">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12,2A3,3 0 0,1 15,5A3,3 0 0,1 12,8A3,3 0 0,1 9,5A3,3 0 0,1 12,2M21,9V7L15,4V6C15,7.1 14.1,8 13,8V22H11V16H13V22H15V8C16.1,8 17,7.1 17,6V4L21,7V9H23V7C23,5.9 22.1,5 21,5L12,1L3,5C1.9,5 1,5.9 1,7V9H3V7L7,4V6C7,7.1 7.9,8 9,8V22H11V16H9V22H7V8C5.9,8 5,7.1 5,6V4L1,7V9Z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.dashboard-overview-container {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.overview-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f3f4f6;
}

.overview-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    text-align: center;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 0;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-card-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 2rem;
    position: relative;
    z-index: 1;
}

.stat-info {
    flex: 1;
}

.stat-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: white;
    margin: 0;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: white;
    margin: 0;
    margin-bottom: 0.5rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.stat-description {
    font-size: 0.9rem;
    color: white;
    margin: 0;
    opacity: 0.8;
}

.stat-icon {
    margin-left: 1rem;
    color: white;
    opacity: 0.7;
    transition: all 0.3s ease;
}

.stat-card:hover .stat-icon {
    opacity: 1;
    transform: scale(1.1);
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem;
    }
    
    .dashboard-overview-container {
        padding: 1.5rem;
        border-radius: 16px;
    }
    
    .stats-cards {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .overview-title {
        font-size: 1.5rem;
    }
    
    .stat-card-content {
        padding: 1.5rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
}
</style>
@endsection
