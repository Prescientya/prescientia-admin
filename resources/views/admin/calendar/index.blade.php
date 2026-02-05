@extends('layouts.app')

@section('title', 'Kalender Sekolah - SekolahKu Admin')

@section('page-title', 'Kalender Sekolah')

@section('css')
<style>
.calendar-header {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 2rem;
}

.month-navigation {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.month-navigation h3 {
    margin: 0;
    min-width: 200px;
    text-align: center;
    font-size: 1.5rem;
    color: var(--color-text-dark);
    cursor: pointer;
    position: relative;
    padding: 0.5rem;
    border-radius: var(--radius-md);
    transition: all 0.2s;
}

.month-navigation h3:hover {
    background: rgba(0, 0, 0, 0.05);
}

.month-picker {
    position: absolute;
    top: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    min-width: 300px;
    padding: 1rem;
    display: none;
}

.month-picker.show {
    display: block;
}

.month-picker-header {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--color-border);
}

.month-picker-header h4 {
    display: none;
}

.month-picker-nav {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.picker-btn {
    padding: 0.25rem 0.5rem;
    background: var(--color-link);
    color: white;
    border: none;
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.picker-btn:hover {
    background: #c73d00;
}

.month-picker-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.month-item {
    padding: 0.75rem;
    background: white;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    text-align: center;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.month-item:hover {
    background: rgba(0, 0, 0, 0.05);
    border-color: var(--color-link);
}

.month-item.active {
    background: var(--color-link);
    color: white;
    border-color: var(--color-link);
}

.year-picker-section {
    border-top: 1px solid var(--color-border);
    padding-top: 1rem;
}

.year-picker-section h5 {
    display: none;
}

.year-picker-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.5rem;
    max-height: 200px;
    overflow-y: auto;
    padding-right: 0.25rem;
}

.year-picker-grid::-webkit-scrollbar {
    width: 6px;
}

.year-picker-grid::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.year-picker-grid::-webkit-scrollbar-thumb {
    background: var(--color-link);
    border-radius: 3px;
}

.year-picker-grid::-webkit-scrollbar-thumb:hover {
    background: #c73d00;
}

.year-item {
    padding: 0.75rem;
    background: white;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    text-align: center;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.year-item:hover {
    background: rgba(0, 0, 0, 0.05);
    border-color: var(--color-link);
}

.year-item.active {
    background: var(--color-link);
    color: white;
    border-color: var(--color-link);
}

.nav-btn {
    padding: 0.5rem 1rem;
    background: var(--color-link);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
}

.nav-btn:hover {
    background: #c73d00;
    color: white;
}

.stats-mini {
    display: none;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: var(--color-border);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.calendar-day-header {
    background: linear-gradient(135deg, var(--color-link), #c73d00);
    color: white;
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.calendar-day {
    background: white;
    padding: 0.6rem 0.75rem;
    height: 120px;
    box-sizing: border-box;
    position: relative;
    cursor: pointer;
    transition: background 0.15s ease;
}

.calendar-day:hover {
    background: #f8f9fa;
}

.calendar-day.other-month {
    background: #f8f9fa;
    color: #adb5bd;
}

.calendar-day.libur {
    background-color: rgba(255, 0, 0, 0.2); /* 20% red */
    color: inherit;
}

.day-number {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.5rem;
    color: inherit;
}

.day-status { display: none; }

.day-description {
    font-size: 0.75rem;
    color: var(--color-text-light);
    margin-top: 0.25rem;
    line-height: 1.3;
}

.legend {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    margin-top: 1.5rem;
    padding: 1rem;
    background: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
}
.legend-item { display:flex; align-items:center; gap:0.5rem; font-size:0.875rem; }
.legend-color { width:20px; height:20px; border-radius:var(--radius-sm); }
.legend-color.libur { background:#f8d7da; border:1px solid var(--color-danger); }

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal.show {
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    padding: 2rem;
    border-radius: var(--radius-lg);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    width: 90%;
    max-width: 400px;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--color-border);
}

.modal-header h2 {
    margin: 0;
    color: var(--color-text-dark);
    font-size: 1.5rem;
}

.modal-close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--color-text-light);
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-sm);
    transition: all 0.2s;
}

.modal-close-btn:hover {
    background: #f0f0f0;
    color: var(--color-text-dark);
}

.modal-body {
    margin-bottom: 1.5rem;
}

.date-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: var(--radius-md);
    font-size: 0.95rem;
}

.date-info-item {
    display: flex;
    flex-direction: column;
}

.date-info-label {
    font-weight: 600;
    color: var(--color-text-light);
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
}

.date-info-value {
    color: var(--color-text-dark);
    font-size: 1rem;
}

.status-section {
    margin: 1.5rem 0;
    padding: 1rem;
    background: white;
    border: 2px solid var(--color-border);
    border-radius: var(--radius-md);
}

.status-section h4 {
    margin: 0 0 1rem 0;
    color: var(--color-text-dark);
    font-size: 1rem;
}

.status-toggle {
    display: flex;
    gap: 0.5rem;
    justify-content: space-between;
}

.status-btn {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid #ccc;
    background: white;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    position: relative;
}

.status-btn:hover {
    border-color: #999;
}

.status-checkbox {
    width: 20px;
    height: 20px;
    border: 2px solid #ccc;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    background: white;
}

.status-btn:hover .status-checkbox {
    border-color: #999;
}

.status-btn.active.aktif {
    background: #d4edda;
    border-color: var(--color-success);
    color: #155724;
}

.status-btn.active.aktif .status-checkbox {
    background: var(--color-success);
    border-color: var(--color-success);
    color: white;
    font-size: 1.1rem;
    font-weight: bold;
}

.status-btn.active.libur {
    background: #f8d7da;
    border-color: var(--color-danger);
    color: #721c24;
}

.status-btn.active.libur .status-checkbox {
    background: var(--color-danger);
    border-color: var(--color-danger);
    color: white;
    font-size: 1.1rem;
    font-weight: bold;
}

.modal-footer {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

.modal-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    font-size: 0.95rem;
}

.modal-btn-primary {
    background: var(--color-link);
    color: white;
}

.modal-btn-primary:hover {
    background: #c73d00;
}

.modal-btn-secondary {
    background: var(--color-border);
    color: var(--color-text-dark);
}

.modal-btn-secondary:hover {
    background: #e0e0e0;
}

.modal-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.add-event-btn {
    padding: 0.625rem 1.25rem;
    background: var(--color-success);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
}

.add-event-btn:hover {
    background: #1e7e34;
    color: white;
}

.calendar-actions {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
    gap: 1rem;
}

@media (max-width: 768px) {
    .calendar-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .calendar-day {
        min-height: 80px;
        padding: 0.5rem;
    }
    
    .stats-mini {
        flex-wrap: wrap;
    }
}
</style>
@endsection

@section('content')
<!-- Calendar Header -->
<div class="card">
    <div class="card-body">
        <div class="calendar-header">
            <div class="month-navigation">
                <a href="{{ route('admin.calendar.index', ['month' => $month == 1 ? 12 : $month - 1, 'year' => $month == 1 ? $year - 1 : $year]) }}" 
                   class="nav-btn">
                    ← Bulan Sebelumnya
                </a>
                <div style="position: relative;">
                    <h3 onclick="toggleMonthPicker()">
                        {{ \Carbon\Carbon::create($year, $month, 1)->isoFormat('MMMM YYYY') }}
                    </h3>
                    
                    <!-- Month/Year Picker -->
                    <div id="monthPicker" class="month-picker">
                        <div class="month-picker-header">
                            <h4>Pilih Bulan dan Tahun</h4>
                            <div class="month-picker-nav">
                                <button class="picker-btn" onclick="event.stopPropagation(); changePickerYear(-1)">← Tahun</button>
                                <button class="picker-btn" onclick="event.stopPropagation(); changePickerYear(1)">Tahun →</button>
                            </div>
                        </div>
                        
                        <!-- Bulan -->
                        <div class="month-picker-grid" id="monthGrid">
                            @php
                                $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            @endphp
                            @for ($m = 1; $m <= 12; $m++)
                                <div class="month-item {{ $m == $month ? 'active' : '' }}" onclick="event.stopPropagation(); selectMonth({{ $m }})">
                                    {{ substr($monthNames[$m-1], 0, 3) }}
                                </div>
                            @endfor
                        </div>
                        
                        <!-- Tahun -->
                        <div class="year-picker-section">
                            <h5>Pilih Tahun: <span id="pickerYearDisplay">{{ $year }}</span></h5>
                            <div class="year-picker-grid" id="yearGrid"></div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.calendar.index', ['month' => $month == 12 ? 1 : $month + 1, 'year' => $month == 12 ? $year + 1 : $year]) }}" 
                   class="nav-btn">
                    Bulan Berikutnya →
                </a>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="calendar-grid">
            <!-- Day Headers -->
            <div class="calendar-day-header">Minggu</div>
            <div class="calendar-day-header">Senin</div>
            <div class="calendar-day-header">Selasa</div>
            <div class="calendar-day-header">Rabu</div>
            <div class="calendar-day-header">Kamis</div>
            <div class="calendar-day-header">Jumat</div>
            <div class="calendar-day-header">Sabtu</div>

            @php
                $firstDay = \Carbon\Carbon::create($year, $month, 1);
                $lastDay = $firstDay->copy()->endOfMonth();
                $startDate = $firstDay->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                $endDate = $lastDay->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                $currentDate = $startDate->copy();
                $today = \Carbon\Carbon::today()->toDateString();
            @endphp

            @while ($currentDate <= $endDate)
                @php
                    $dateString = $currentDate->toDateString();
                    $isCurrentMonth = $currentDate->month == $month;
                    $calendarEvent = $calendars->get($dateString);

                    $classes = ['calendar-day'];
                    if (! $isCurrentMonth) $classes[] = 'other-month';
                    // only add status class (e.g., 'libur') when calendar event exists
                    if ($calendarEvent && !empty($calendarEvent->status)) {
                        $classes[] = trim($calendarEvent->status);
                    }
                @endphp

                <div class="{{ implode(' ', $classes) }}" 
                     onclick="editDate('{{ $dateString }}', '{{ $calendarEvent ? $calendarEvent->status : '' }}', '{{ $calendarEvent ? addslashes($calendarEvent->description) : '' }}')">
                    <div class="day-number">{{ $currentDate->day }}</div>
                    
                    @if ($calendarEvent)
                        <div class="day-status {{ $calendarEvent->status }}"></div>
                        @if ($calendarEvent->description)
                            <div class="day-description">{{ $calendarEvent->description }}</div>
                        @endif
                    @endif
                </div>
                @php
                    $currentDate->addDay();
                @endphp
            @endwhile
        </div>

        <!-- Calendar Actions -->
        <div class="calendar-actions">
            <button type="button" id="generateBtn" class="add-event-btn" onclick="handleGenerateCalendar()">
                Generate Kalender ({{ $year }})
            </button>
        </div>
    </div>
</div>

<!-- Modal for Edit Date Status -->
<div id="dateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Tanggal</h2>
            <button class="modal-close-btn" onclick="closeDateModal()">×</button>
        </div>
        
        <div class="modal-body">
            <!-- Date Information -->
            <div class="date-info">
                <div class="date-info-item">
                    <span class="date-info-label">Hari</span>
                    <span class="date-info-value" id="modalDayName">-</span>
                </div>
                <div class="date-info-item">
                    <span class="date-info-label">Tanggal</span>
                    <span class="date-info-value" id="modalDate">-</span>
                </div>
                <div class="date-info-item">
                    <span class="date-info-label">Bulan</span>
                    <span class="date-info-value" id="modalMonth">-</span>
                </div>
                <div class="date-info-item">
                    <span class="date-info-label">Tahun</span>
                    <span class="date-info-value" id="modalYear">-</span>
                </div>
            </div>
            
            <!-- Status Toggle -->
            <div class="status-section">
                <h4>Status Hari</h4>
                <div class="status-toggle">
                    <button class="status-btn aktif" id="statusAktifBtn" onclick="changeStatus('aktif')">
                        <span class="status-checkbox">✓</span>
                        Hari Efektif
                    </button>
                    <button class="status-btn libur" id="statusLiburBtn" onclick="changeStatus('libur')">
                        <span class="status-checkbox">✓</span>
                        Hari Libur
                    </button>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="modal-btn modal-btn-secondary" onclick="closeDateModal()">Batal</button>
            <button class="modal-btn modal-btn-primary" id="saveDateBtn" onclick="saveDateStatus()">Simpan</button>
        </div>
    </div>
</div>

<!-- Modal for Edit/Add Event (simple alert for now) -->
@endsection

@section('scripts')
<script>
// Current year and month from blade
const currentYear = {{ $year }};
const currentMonth = {{ $month }};
let pickerYear = {{ $year }};

// Month Picker Functions
function toggleMonthPicker() {
    const picker = document.getElementById('monthPicker');
    picker.classList.toggle('show');
    if (picker.classList.contains('show')) {
        regenerateYearGrid();
    }
}

function changePickerYear(direction) {
    pickerYear += direction;
    regenerateYearGrid();
}

function regenerateYearGrid() {
    const yearGrid = document.getElementById('yearGrid');
    yearGrid.innerHTML = '';
    
    // Generate tahun dari pickerYear - 10 sampai pickerYear + 10
    const startYear = pickerYear - 10;
    const endYear = pickerYear + 10;
    
    for (let y = startYear; y <= endYear; y++) {
        const yearItem = document.createElement('div');
        yearItem.className = 'year-item';
        if (y === pickerYear) {
            yearItem.classList.add('active');
        }
        yearItem.textContent = y;
        yearItem.onclick = function(event) { 
            event.stopPropagation();
            selectYear(y); 
        };
        yearGrid.appendChild(yearItem);
    }
}

function selectMonth(month) {
    // Navigate to selected month and year
    const url = `{{ route('admin.calendar.index') }}?month=${month}&year=${pickerYear}`;
    window.location.href = url;
}

function selectYear(year) {
    pickerYear = year;
    regenerateYearGrid();
}

// Close picker when clicking outside
document.addEventListener('click', function(event) {
    const picker = document.getElementById('monthPicker');
    const heading = event.target.closest('.month-navigation h3');
    
    if (!heading && !picker.contains(event.target)) {
        picker.classList.remove('show');
    }
});

function handleGenerateCalendar() {
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.style.opacity = '0.6';
    
    // Check if calendar already exists for this year
    fetch('{{ route("admin.calendar.check-exists") }}?year=' + currentYear, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.style.opacity = '1';
        
        if (data.exists) {
            // Calendar sudah ada
            alert('Data Kalender sudah di buatkan bedasarkan data libur nasional!');
        } else {
            // Calendar belum ada, tanya user
            if (confirm('Proses ini dapat memakan waktu 2–4 menit. Lanjutkan pembuatan Kalender?')) {
                // Submit form untuk generate calendar
                generateCalendarData();
            }
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.style.opacity = '1';
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memeriksa data kalender');
    });
}

function generateCalendarData() {
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.style.opacity = '0.6';
    
    // Submit form menggunakan POST ke seed endpoint
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.calendar.seed") }}';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    
    form.appendChild(csrfInput);
    document.body.appendChild(form);
    form.submit();
}

function editDate(date, status, description) {
    // Fetch date status from server
    fetch('{{ route("admin.calendar.get-date-status") }}?date=' + date, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Store the calendar ID for later use
            window.currentDateId = data.id;
            window.currentDateStatus = data.status;
            
            // Populate modal
            document.getElementById('modalDayName').textContent = data.day_name;
            document.getElementById('modalDate').textContent = data.day;
            document.getElementById('modalMonth').textContent = data.month_name;
            document.getElementById('modalYear').textContent = data.year;
            
            // Set status buttons
            updateStatusButtons(data.status);
            
            // Show modal
            document.getElementById('dateModal').classList.add('show');
        } else {
            alert('Tanggal tidak ditemukan dalam kalender');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengambil data tanggal');
    });
}

function closeDateModal() {
    document.getElementById('dateModal').classList.remove('show');
    window.currentDateId = null;
    window.currentDateStatus = null;
}

function updateStatusButtons(status) {
    const aktifBtn = document.getElementById('statusAktifBtn');
    const liburBtn = document.getElementById('statusLiburBtn');
    
    aktifBtn.classList.remove('active');
    liburBtn.classList.remove('active');
    
    if (status === 'aktif') {
        aktifBtn.classList.add('active');
    } else if (status === 'libur') {
        liburBtn.classList.add('active');
    }
}

function changeStatus(status) {
    window.currentDateStatus = status;
    updateStatusButtons(status);
}

function saveDateStatus() {
    if (!window.currentDateId) {
        alert('Data tidak lengkap');
        return;
    }

    const btn = document.getElementById('saveDateBtn');
    btn.disabled = true;
    btn.style.opacity = '0.6';

    fetch('{{ route("admin.calendar.toggle-status", ":id") }}'.replace(':id', window.currentDateId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.style.opacity = '1';

        if (data.success) {
            // Optionally show the new status to the user
            alert('Status berhasil diubah menjadi: ' + data.new_status);
            window.location.reload();
        } else {
            alert('Gagal mengubah status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.style.opacity = '1';
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    });
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('dateModal');
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeDateModal();
        }
    });
});
</script>
@endsection
