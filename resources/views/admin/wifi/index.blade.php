@extends('layouts.app')

@section('title', 'Data WiFi - SekolahKu Admin')

@section('page-title', 'Data WiFi')

@section('css')
<style>
    .wifi-section {
        margin-bottom: 2rem;
    }

    .wifi-section-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }

    .wifi-table {
        width: 100%;
        border-collapse: collapse;
    }

    .wifi-table thead {
        background-color: #f8f9fa;
    }

    .wifi-table th,
    .wifi-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
    }

    .wifi-table th {
        font-weight: 600;
        color: #333;
    }

    .wifi-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .wifi-table a {
        color: #0d6efd;
        text-decoration: none;
    }

    .wifi-table a:hover {
        text-decoration: underline;
    }

    .btn-group {
        display: flex;
        gap: 0.5rem;
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
        border: none;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-primary {
        background-color: #0d6efd;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #bb2d3b;
    }

    .btn-success {
        background-color: #198754;
        color: white;
    }

    .btn-success:hover {
        background-color: #157347;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 0.25rem;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #664d03;
        border: 1px solid #ffecb5;
    }

    .alert-success {
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }

    .no-data {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
    }

    .signal-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        background-color: #e7f3ff;
        color: #004085;
    }

    .signal-strong {
        background-color: #d4edda;
        color: #155724;
    }

    .signal-medium {
        background-color: #fff3cd;
        color: #856404;
    }

    .signal-weak {
        background-color: #f8d7da;
        color: #721c24;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        align-items: center;
        justify-content: center;
        padding: 1rem;
        box-sizing: border-box;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background-color: #fefefe;
        padding: 1.5rem 2rem;
        border: 1px solid #888;
        border-radius: 0.5rem;
        width: 100%;
        max-width: 600px;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .modal-close {
        color: #aaa;
        float: right;
        font-size: 1.5rem;
        font-weight: bold;
        cursor: pointer;
    }

    .modal-close:hover {
        color: #000;
    }

    .modal-buttons {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 1.5rem;
    }

    .modal-buttons button {
        padding: 0.5rem 1.5rem;
        font-size: 1rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
    }

    .modal-buttons .btn-confirm {
        background-color: #198754;
        color: white;
    }

    .modal-buttons .btn-confirm:hover {
        background-color: #157347;
    }

    .modal-buttons .btn-cancel {
        background-color: #6c757d;
        color: white;
    }

    .modal-buttons .btn-cancel:hover {
        background-color: #5a6268;
    }

    .loader {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #0d6efd;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

        /* Action menu (settings) */
        .action-menu {
            position: relative;
            display: inline-block;
        }

        .action-menu .settings-btn {
            padding: 0.4rem 0.6rem;
            border-radius: 0.375rem;
            background-color: #343a40;
            color: white;
            border: none;
            cursor: pointer;
        }

        .action-menu-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 6px);
            min-width: 140px;
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 0.25rem;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            z-index: 2000;
        }

        .action-menu-dropdown a,
        .action-menu-dropdown button {
            display: block;
            padding: 0.45rem 0.75rem;
            text-decoration: none;
            color: #212529;
            background: transparent;
            border: none;
            text-align: left;
            width: 100%;
            cursor: pointer;
        }

        .action-menu-dropdown a:hover,
        .action-menu-dropdown button:hover {
            background-color: #f8f9fa;
        }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        {{-- Messages --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($error)
            <div class="alert alert-warning">
                <strong>⚠️ Peringatan:</strong><br>
                <code style="background: #fff8e1; padding: 0.5rem; display: block; margin-top: 0.5rem; word-break: break-word; font-size: 0.85rem;">{{ $error }}</code>
            </div>
        @endif

        {{-- Section 1: WiFi Networks in Database --}}
        <div class="wifi-section">
            <h2 class="wifi-section-title">📡 WiFi Terdaftar di Database</h2>

            @if ($wifiNetworks->count() > 0)
                <div style="overflow-x: auto;">
                    <table class="wifi-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>SSID</th>
                                <th>BSSID (MAC)</th>
                                <th>IP Address</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($wifiNetworks as $key => $wifi)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td><strong>{{ $wifi->ssid }}</strong></td>
                                    <td><code>{{ $wifi->bssid }}</code></td>
                                    <td>{{ $wifi->ip_address ?? '-' }}</td>
                                    <td>{{ $wifi->created_at->format('d/m/Y H:i') }}</td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <div class="action-menu">
                                            <button type="button" class="settings-btn" onclick="toggleActionMenu('{{ $wifi->id }}')">⚙</button>
                                            <div id="action-menu-{{ $wifi->id }}" class="action-menu-dropdown" aria-hidden="true">
                                                <a href="{{ route('admin.wifi.edit', $wifi->id) }}">✏️ Edit</a>
                                                <form action="{{ route('admin.wifi.destroy', $wifi->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus WiFi ini?');">🗑 Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="no-data">Belum ada WiFi yang terdaftar</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">
                    Belum ada WiFi yang terdaftar di database. Silakan cari dan tambahkan WiFi dari sekitar Anda.
                </div>
            @endif
        </div>

        {{-- Section 1.5: Input Manual WiFi --}}
        <div class="wifi-section">
            <h2 class="wifi-section-title">
                ✏️ Input Manual WiFi
                <span style="float: right; font-size: 1rem;">
                    <button type="button" onclick="openManualWifiModal()" class="btn btn-sm btn-primary" style="padding: 0.5rem 1rem;">
                        ➕ Tambah WiFi Manual
                    </button>
                </span>
            </h2>
            <p style="color: #6c757d;">Tambahkan WiFi secara manual dengan memasukkan SSID dan BSSID (MAC Address) yang ingin dijadikan patokan di sekolah.</p>
        </div>

        {{-- Section 2: Detected WiFi Networks --}}
        <div class="wifi-section">
            <h2 class="wifi-section-title">
                🔍 WiFi yang Terdeteksi
                <span style="float: right; font-size: 1rem;">
                    <button onclick="location.reload()" class="btn btn-sm btn-primary" style="padding: 0.5rem 1rem;">
                        🔄 Scan Ulang
                    </button>
                </span>
            </h2>

            @if (!empty($detectedNetworksFiltered) && count($detectedNetworksFiltered) > 0)
                @foreach ($detectedNetworksFiltered as $network)
                    <div style="margin-bottom: 2rem; padding: 1.5rem; background-color: #f8f9fa; border-radius: 0.5rem; border-left: 4px solid #0d6efd;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div>
                                <h3 style="margin: 0 0 0.5rem 0;">{{ $network['ssid'] }}</h3>
                                <p style="margin: 0; color: #6c757d; font-size: 0.9rem;">
                                    <strong>{{ count($network['bssids']) }}</strong> Access Point{{ count($network['bssids']) > 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>

                        <div style="overflow-x: auto;">
                            <table class="wifi-table" style="background-color: white; margin-bottom: 1rem;">
                                <thead>
                                    <tr>
                                        <th>BSSID</th>
                                        <th>Channel</th>
                                        <th>Signal</th>
                                        <th>Radio Type</th>
                                        <th>Security</th>
                                        <th>Encryption</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($network['bssids'] as $bssid)
                                        <tr>
                                            <td><code style="font-size: 0.85rem;">{{ $bssid['bssid'] }}</code></td>
                                            <td>{{ $bssid['channel'] ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $signal = $bssid['signal'];
                                                    $signalClass = 'signal-weak';
                                                    if ($signal >= 70) {
                                                        $signalClass = 'signal-strong';
                                                    } elseif ($signal >= 40) {
                                                        $signalClass = 'signal-medium';
                                                    }
                                                @endphp
                                                <span class="signal-badge {{ $signalClass }}">
                                                    {{ $signal ?? '-' }}%
                                                </span>
                                            </td>
                                            <td>{{ $bssid['radio'] ?? '-' }}</td>
                                            <td>{{ $bssid['security'] ?? '-' }}</td>
                                            <td>{{ $bssid['encryption'] ?? '-' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success" onclick="openConvertModal('{{ $network['ssid'] }}', '{{ $bssid['bssid'] }}')">
                                                    Convert
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-success">
                    ✅ Semua WiFi yang terdeteksi sudah terdaftar di database, atau sedang melakukan pemindaian...
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Convert Modal --}}
<div id="convertModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeConvertModal()">&times;</span>
        <h2 style="margin-top: 0;">Konfirmasi Tambah WiFi</h2>
        <p id="confirmMessage"></p>
        <div class="modal-buttons">
            <button type="button" class="btn-cancel" onclick="closeConvertModal()">Batal</button>
            <button type="button" class="btn-confirm" id="confirmBtn" onclick="convertWifi()">
                <span id="confirmBtnText">Ya, Tambahkan</span>
                <span id="confirmBtnLoader" class="loader" style="display: none; margin-left: 0.5rem;"></span>
            </button>
        </div>
    </div>
</div>

{{-- Manual WiFi Input Modal --}}
<div id="manualWifiModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <span class="modal-close" onclick="closeManualWifiModal()">&times;</span>
        <h2 style="margin-top: 0;">➕ Input Manual WiFi</h2>
        
        <form id="manualWifiForm" onsubmit="submitManualWifi(event)">
            @csrf
            
            <div style="margin-bottom: 1.5rem;">
                <label for="manual_ssid" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">SSID (Nama WiFi) <span style="color: #dc3545;">*</span></label>
                <input 
                    type="text" 
                    id="manual_ssid" 
                    name="ssid" 
                    style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 0.25rem; font-size: 1rem; box-sizing: border-box;"
                    placeholder="Contoh: WiFi-Sekolah"
                    required
                >
                <small style="display: block; margin-top: 0.25rem; color: #6c757d;">Nama jaringan WiFi yang akan ditambahkan</small>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="manual_bssid" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">BSSID (MAC Address) <span style="color: #dc3545;">*</span></label>
                <input 
                    type="text" 
                    id="manual_bssid" 
                    name="bssid" 
                    style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 0.25rem; font-size: 1rem; box-sizing: border-box;"
                    placeholder="Contoh: aa:bb:cc:dd:ee:ff"
                    required
                >
                <small style="display: block; margin-top: 0.25rem; color: #6c757d;">MAC Address unik dari Access Point WiFi (format: xx:xx:xx:xx:xx:xx)</small>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="manual_ip_address" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">IP Address <span style="color: #6c757d;">(Opsional)</span></label>
                <input 
                    type="text" 
                    id="manual_ip_address" 
                    name="ip_address" 
                    style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 0.25rem; font-size: 1rem; box-sizing: border-box;"
                    placeholder="Contoh: 192.168.1.1"
                >
                <small style="display: block; margin-top: 0.25rem; color: #6c757d;">IP Address router/gateway WiFi (opsional, bisa ditambahkan/diubah nanti)</small>
            </div>

            <div id="manualWifiErrors" class="alert alert-danger" style="display: none; margin-bottom: 1rem;">
                <strong>Terjadi kesalahan:</strong>
                <ul id="manualWifiErrorList" style="margin: 0.5rem 0 0 1.5rem; padding: 0;"></ul>
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeManualWifiModal()">Batal</button>
                <button type="submit" class="btn-confirm" id="manualWifiSubmitBtn">
                    <span id="manualWifiSubmitBtnText">💾 Simpan WiFi</span>
                    <span id="manualWifiSubmitBtnLoader" class="loader" style="display: none; margin-left: 0.5rem;"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentConvertData = {
        ssid: '',
        bssid: ''
    };

    function openConvertModal(ssid, bssid) {
        currentConvertData = { ssid, bssid };
        document.getElementById('confirmMessage').textContent = 
            `Apakah Anda yakin ingin menjadikan "${ssid}" (BSSID: ${bssid}) ini sebagai patokan WiFi sekolah?`;
        document.getElementById('convertModal').classList.add('show');
    }

    function closeConvertModal() {
        document.getElementById('convertModal').classList.remove('show');
    }

    function convertWifi() {
        const confirmBtn = document.getElementById('confirmBtn');
        const confirmBtnText = document.getElementById('confirmBtnText');
        const confirmBtnLoader = document.getElementById('confirmBtnLoader');

        confirmBtn.disabled = true;
        confirmBtnText.style.display = 'none';
        confirmBtnLoader.style.display = 'inline-block';

        fetch('{{ route("admin.wifi.convert") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ssid: currentConvertData.ssid,
                bssid: currentConvertData.bssid
            })
        })
        .then(response => response.json())
        .then(data => {
            confirmBtn.disabled = false;
            confirmBtnText.style.display = 'inline';
            confirmBtnLoader.style.display = 'none';

            if (data.success) {
                closeConvertModal();
                // Show success message and reload
                alert(data.message);
                location.reload();
            } else {
                alert('Gagal: ' + data.message);
            }
        })
        .catch(error => {
            confirmBtn.disabled = false;
            confirmBtnText.style.display = 'inline';
            confirmBtnLoader.style.display = 'none';
            console.error('Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
        });
    }

    // Manual WiFi Modal Functions
    function openManualWifiModal() {
        document.getElementById('manualWifiModal').classList.add('show');
        document.getElementById('manualWifiForm').reset();
        document.getElementById('manualWifiErrors').style.display = 'none';
    }

    function closeManualWifiModal() {
        document.getElementById('manualWifiModal').classList.remove('show');
        document.getElementById('manualWifiForm').reset();
        document.getElementById('manualWifiErrors').style.display = 'none';
    }

    function submitManualWifi(event) {
        event.preventDefault();

        const submitBtn = document.getElementById('manualWifiSubmitBtn');
        const submitBtnText = document.getElementById('manualWifiSubmitBtnText');
        const submitBtnLoader = document.getElementById('manualWifiSubmitBtnLoader');
        const errorContainer = document.getElementById('manualWifiErrors');
        const errorList = document.getElementById('manualWifiErrorList');

        submitBtn.disabled = true;
        submitBtnText.style.display = 'none';
        submitBtnLoader.style.display = 'inline-block';

        const formData = new FormData(document.getElementById('manualWifiForm'));

        fetch('{{ route("admin.wifi.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeManualWifiModal();
                alert(data.message);
                location.reload();
            } else {
                submitBtn.disabled = false;
                submitBtnText.style.display = 'inline';
                submitBtnLoader.style.display = 'none';
                
                if (data.errors) {
                    errorList.innerHTML = '';
                    for (let field in data.errors) {
                        const errors = data.errors[field];
                        errors.forEach(msg => {
                            const li = document.createElement('li');
                            li.textContent = msg;
                            errorList.appendChild(li);
                        });
                    }
                    errorContainer.style.display = 'block';
                }
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtnText.style.display = 'inline';
            submitBtnLoader.style.display = 'none';
            console.error('Error:', error);
            
            errorList.innerHTML = '<li>Terjadi kesalahan: ' + error.message + '</li>';
            errorContainer.style.display = 'block';
        });
    }

    // Action menu helpers
    function closeAllActionMenus() {
        document.querySelectorAll('.action-menu-dropdown').forEach(el => {
            el.style.display = 'none';
            el.setAttribute('aria-hidden', 'true');
        });
    }

    function toggleActionMenu(id) {
        const el = document.getElementById('action-menu-' + id);
        if (!el) return;
        const isOpen = el.style.display === 'block';
        closeAllActionMenus();
        if (!isOpen) {
            el.style.display = 'block';
            el.setAttribute('aria-hidden', 'false');
        }
    }

    // Close modal or action menu when clicking outside
    window.addEventListener('click', function(event) {
        const convertModal = document.getElementById('convertModal');
        const manualWifiModal = document.getElementById('manualWifiModal');

        if (convertModal && event.target == convertModal) {
            closeConvertModal();
        }
        if (manualWifiModal && event.target == manualWifiModal) {
            closeManualWifiModal();
        }

        // Close action menus if click is outside an open menu or settings button
        if (!event.target.closest || !event.target.closest('.action-menu')) {
            closeAllActionMenus();
        }
    });
</script>
@endsection
