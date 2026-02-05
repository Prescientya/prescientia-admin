@extends('layouts.app')

@section('title', 'Data WiFi - SekolahKu Admin')

@section('page-title', 'Data WiFi')

@section('css')
<link rel="stylesheet" href="{{ asset('css/action-dropdown.css') }}">
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
                                        <div class="action-menu-container">
                                            <button class="action-menu-btn" type="button" onclick="toggleDropdown(event, this)" title="Pengaturan aksi">
                                                <img src="{{ asset('assets/icons/setting.png') }}" alt="Setting" width="20" height="20">
                                            </button>
                                            <ul class="dropdown-menu" style="display: none;">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.wifi.edit', $wifi->id) }}">
                                                        ✏️ Edit
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('admin.wifi.destroy', $wifi->id) }}" class="dropdown-delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus WiFi ini?')">
                                                            🗑 Hapus
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
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
                    Belum ada WiFi yang terdaftar di database. Silakan tambah WiFi secara manual menggunakan tombol di bawah.
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
    function toggleDropdown(event, button) {
        event.stopPropagation();
        const dropdown = button.nextElementSibling;
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(el => {
            if (el !== dropdown) {
                el.style.display = 'none';
            }
        });
        
        // Toggle current dropdown
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    }

    // Close action menus if click is outside an open menu or settings button
    window.addEventListener('click', function(event) {
        const manualWifiModal = document.getElementById('manualWifiModal');

        if (manualWifiModal && event.target == manualWifiModal) {
            closeManualWifiModal();
        }

        // Close dropdown menus if click is outside
        if (!event.target.closest('.action-menu-container')) {
            document.querySelectorAll('.dropdown-menu').forEach(el => {
                el.style.display = 'none';
            });
        }
    });
</script>
@endsection
