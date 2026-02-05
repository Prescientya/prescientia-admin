@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="dashboard-container">
    <!-- Dashboard Overview Container -->
    <div class="dashboard-overview-container" id="overviewContainer">
        <div class="overview-header">
            <h2 class="overview-title" id="overviewTitle">Dashboard Overview</h2>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-cards" id="statsCardsContainer">
            <!-- Card Kelas 10 -->
            <div class="stat-card clickable-card" data-type="class" data-grade="10">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3 class="stat-title">Kelas 10</h3>
                        <p class="stat-number">{{ number_format($siswaKelas10) }}</p>
                        <p class="stat-description">Total Siswa</p>
                        <p class="stat-extra">{{ number_format($totalKelas10) }} Kelas</p>
                    </div>
                </div>
            </div>
            
            <!-- Card Kelas 11 -->
            <div class="stat-card clickable-card" data-type="class" data-grade="11">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3 class="stat-title">Kelas 11</h3>
                        <p class="stat-number">{{ number_format($siswaKelas11) }}</p>
                        <p class="stat-description">Total Siswa</p>
                        <p class="stat-extra">{{ number_format($totalKelas11) }} Kelas</p>
                    </div>
                </div>
            </div>
            
            <!-- Card Kelas 12 -->
            <div class="stat-card clickable-card" data-type="class" data-grade="12">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3 class="stat-title">Kelas 12</h3>
                        <p class="stat-number">{{ number_format($siswaKelas12) }}</p>
                        <p class="stat-description">Total Siswa</p>
                        <p class="stat-extra">{{ number_format($totalKelas12) }} Kelas</p>
                    </div>
                </div>
            </div>
            
            <!-- Card Guru -->
            <div class="stat-card clickable-card" data-type="guru">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3 class="stat-title">Guru</h3>
                        <p class="stat-number">{{ number_format($totalGuru) }}</p>
                        <p class="stat-description">Total Guru</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detail View Container (Hidden by default) -->
        <div id="detailViewContainer" style="display: none;">
            <button id="backBtn" class="btn-back">← Kembali ke Overview</button>
            <h3 id="detailTitle" class="detail-title"></h3>
            <div id="detailContent" class="detail-content"></div>
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

.stat-card.clickable-card {
    cursor: pointer;
}

.stat-card.clickable-card:hover {
    transform: translateY(-8px) scale(1.02);
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

.stat-extra {
    font-size: 0.85rem;
    color: white;
    margin: 0.25rem 0 0 0;
    opacity: 0.7;
    font-weight: 500;
}

/* Detail View Styles */
.btn-back {
    background: #667eea;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 2rem;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: #5568d3;
    transform: translateX(-5px);
}

.detail-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 2rem 0;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f3f4f6;
}

.detail-content {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.detail-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    color: white;
    transition: all 0.3s ease;
    cursor: pointer;
}

.detail-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

.detail-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 1rem 0;
    opacity: 0.9;
}

.detail-card-stat {
    font-size: 2rem;
    font-weight: 800;
    margin: 0 0 0.5rem 0;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.detail-card-label {
    font-size: 0.9rem;
    opacity: 0.8;
    margin: 0;
}

.detail-card.guru-hadir {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.detail-card.guru-tidak-hadir {
    background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
}

.detail-card.kelas-hadir {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.detail-card.kelas-belum-hadir {
    background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
}

/* Class Item Specific Styles */
.detail-card.class-item {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.5rem;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.class-info-header {
    margin-bottom: 1rem;
}

.class-name {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: white;
}

.class-major {
    font-size: 1rem;
    font-weight: 500;
    margin: 0;
    color: white;
    opacity: 0.8;
}

.class-stats {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    flex: 1;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
    display: block;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.8rem;
    color: white;
    opacity: 0.8;
    display: block;
}

.stat-item.stat-hadir .stat-value {
    color: #4ade80;
}

.stat-item.stat-belum-hadir .stat-value {
    color: #f87171;
}

/* Students List Styles */
.students-list-container {
    max-height: 500px;
    overflow-y: auto;
}

.student-item {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.student-item:hover {
    background: rgba(255, 255, 255, 0.2);
}

.student-info {
    flex: 1;
}

.student-name {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.25rem 0;
    color: white;
}

.student-nisn {
    font-size: 0.85rem;
    opacity: 0.7;
    margin: 0;
    color: white;
}

.student-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    text-align: right;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.status-badge.hadir {
    background: rgba(74, 222, 128, 0.2);
    color: #4ade80;
    border: 1px solid #4ade80;
}

.status-badge.belum-hadir {
    background: rgba(248, 113, 113, 0.2);
    color: #f87171;
    border: 1px solid #f87171;
}

.check-in-time {
    font-size: 0.75rem;
    opacity: 0.7;
    color: white;
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.error-message {
    background: #fee;
    color: #c33;
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
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
    
    .detail-content {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clickableCards = document.querySelectorAll('.clickable-card');
    const overviewContainer = document.getElementById('overviewContainer');
    const statsCardsContainer = document.getElementById('statsCardsContainer');
    const detailViewContainer = document.getElementById('detailViewContainer');
    const detailContent = document.getElementById('detailContent');
    const detailTitle = document.getElementById('detailTitle');
    const backBtn = document.getElementById('backBtn');
    
    clickableCards.forEach(card => {
        card.addEventListener('click', function() {
            const type = this.dataset.type;
            const grade = this.dataset.grade;
            
            if (type === 'class') {
                loadClassDetail(grade);
            } else if (type === 'guru') {
                loadTeacherDetail();
            }
        });
    });
    
    backBtn.addEventListener('click', function() {
        detailViewContainer.style.display = 'none';
        statsCardsContainer.style.display = 'grid';
    });
    
    function loadClassDetail(grade) {
        statsCardsContainer.style.display = 'none';
        detailViewContainer.style.display = 'block';
        detailTitle.textContent = `Detail Kelas ${grade}`;
        detailContent.innerHTML = '<p><span class="loading-spinner"></span> Loading...</p>';
        
        const apiUrl = `/admin/dashboard/classes/${grade}`;
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    detailContent.innerHTML = data.data.map(cls => `
                        <div class="detail-card class-item" onclick="loadStudentsList(${cls.id}, '${cls.displayName}')" data-class-id="${cls.id}">
                            <div class="class-info-header">
                                <h4 class="class-name">${cls.displayName}</h4>
                                <p class="class-major">Jurusan ${cls.major}</p>
                            </div>
                            <div class="class-stats">
                                <div class="stat-item">
                                    <span class="stat-value">${cls.totalSiswa}</span>
                                    <span class="stat-label">Total Siswa</span>
                                </div>
                                <div class="stat-item stat-hadir">
                                    <span class="stat-value">${cls.siswaHadir}</span>
                                    <span class="stat-label">Hadir</span>
                                </div>
                                <div class="stat-item stat-belum-hadir">
                                    <span class="stat-value">${cls.siswaBelumHadir}</span>
                                    <span class="stat-label">Belum Hadir</span>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    detailContent.innerHTML = '<p>Tidak ada data kelas.</p>';
                }
            })
            .catch(error => {
                detailContent.innerHTML = `<div class="error-message">Error loading data: ${error.message}</div>`;
            });
    }
    
    function loadStudentsList(classId, className) {
        detailTitle.textContent = `Daftar Siswa - ${className}`;
        detailContent.innerHTML = '<p><span class="loading-spinner"></span> Loading siswa...</p>';
        
        const apiUrl = `/admin/dashboard/class/${classId}/students`;
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const classInfo = data.data.class;
                    const students = data.data.students;
                    
                    let studentsHTML = '';
                    if (students.length > 0) {
                        studentsHTML = students.map(student => `
                            <div class="student-item">
                                <div class="student-info">
                                    <h5 class="student-name">${student.name}</h5>
                                    <p class="student-nisn">NISN: ${student.nisn}</p>
                                </div>
                                <div class="student-status">
                                    <span class="status-badge ${student.isPresent ? 'hadir' : 'belum-hadir'}">
                                        ${student.status}
                                    </span>
                                    ${student.checkInTime ? `<span class="check-in-time">${student.checkInTime}</span>` : ''}
                                </div>
                            </div>
                        `).join('');
                    } else {
                        studentsHTML = '<p style="text-align: center; color: white; opacity: 0.8;">Belum ada siswa di kelas ini.</p>';
                    }
                    
                    detailContent.innerHTML = `
                        <div style="grid-column: 1 / -1; margin-bottom: 2rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;">
                                <div>
                                    <h4 style="margin: 0; color: white; font-size: 1.2rem;">${classInfo.name}</h4>
                                    <p style="margin: 0.25rem 0 0 0; color: white; opacity: 0.8;">Total: ${classInfo.totalStudents} siswa</p>
                                </div>
                                <div style="display: flex; gap: 2rem; text-align: center;">
                                    <div>
                                        <span style="display: block; font-size: 1.5rem; font-weight: 700; color: #4ade80;">${classInfo.presentCount}</span>
                                        <span style="font-size: 0.8rem; color: white; opacity: 0.8;">Hadir</span>
                                    </div>
                                    <div>
                                        <span style="display: block; font-size: 1.5rem; font-weight: 700; color: #f87171;">${classInfo.absentCount}</span>
                                        <span style="font-size: 0.8rem; color: white; opacity: 0.8;">Belum Hadir</span>
                                    </div>
                                </div>
                            </div>
                            <div class="students-list-container">
                                ${studentsHTML}
                            </div>
                        </div>
                    `;
                } else {
                    detailContent.innerHTML = '<p>Error loading students data.</p>';
                }
            })
            .catch(error => {
                detailContent.innerHTML = `<div class="error-message">Error loading students: ${error.message}</div>`;
            });
    }
    
    function loadTeacherDetail() {
        statsCardsContainer.style.display = 'none';
        detailViewContainer.style.display = 'block';
        detailTitle.textContent = 'Statistik Guru';
        detailContent.innerHTML = '<p><span class="loading-spinner"></span> Loading...</p>';
        
        const apiUrl = '/admin/dashboard/teachers';
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.data;
                    detailContent.innerHTML = `
                        <div class="detail-card">
                            <h4 class="detail-card-title">Total Guru</h4>
                            <p class="detail-card-stat">${stats.totalGuru}</p>
                            <p class="detail-card-label">Jumlah Total Guru</p>
                        </div>
                        <div class="detail-card guru-hadir">
                            <h4 class="detail-card-title">Hadir</h4>
                            <p class="detail-card-stat">${stats.guruHadir}</p>
                            <p class="detail-card-label">Guru Hadir Hari Ini</p>
                        </div>
                        <div class="detail-card guru-tidak-hadir">
                            <h4 class="detail-card-title">Tidak Hadir</h4>
                            <p class="detail-card-stat">${stats.guruTidakHadir}</p>
                            <p class="detail-card-label">Guru Tidak Hadir</p>
                        </div>
                    `;
                } else {
                    detailContent.innerHTML = '<p>Error loading data.</p>';
                }
            })
            .catch(error => {
                detailContent.innerHTML = `<div class="error-message">Error loading data: ${error.message}</div>`;
            });
    }
});
</script>
@endsection
