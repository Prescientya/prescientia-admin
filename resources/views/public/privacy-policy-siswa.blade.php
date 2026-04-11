<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy Aplikasi Siswa - Prescientia</title>
    <style>
        :root {
            --bg: #f4f1e8;
            --paper: #fffdf7;
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #ddd6c8;
            --accent: #0f766e;
            --accent-soft: #d9f3ef;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 10%, #f5e6cf 0%, transparent 30%),
                radial-gradient(circle at 90% 20%, #e3f1ef 0%, transparent 35%),
                var(--bg);
            line-height: 1.6;
        }

        .wrap {
            max-width: 980px;
            margin: 24px auto;
            padding: 0 16px 40px;
        }

        .card {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        h1, h2, h3 { margin-top: 0; }
        h1 { font-size: 28px; margin-bottom: 8px; }
        h2 {
            font-size: 20px;
            margin-top: 26px;
            border-left: 5px solid var(--accent);
            padding-left: 10px;
        }

        .meta {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 16px;
        }

        .badge {
            display: inline-block;
            background: var(--accent-soft);
            color: #0b4f49;
            border: 1px solid #9ad5ce;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            margin-right: 6px;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 14px;
            table-layout: fixed;
            min-width: 980px;
        }

        th, td {
            border: 1px solid var(--line);
            padding: 10px;
            vertical-align: top;
            text-align: left;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        th {
            background: #f8f4ea;
            font-weight: 700;
        }

        ul { margin-top: 8px; }

        .note {
            font-size: 13px;
            color: var(--muted);
            background: #f7f8fa;
            border-left: 4px solid #9ca3af;
            padding: 10px 12px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .warn {
            border-left-color: #f59e0b;
            background: #fff7e7;
            color: #8a5a00;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            -webkit-overflow-scrolling: touch;
        }

        .table-wrap table {
            margin-top: 0;
            border: 0;
        }

        .table-wrap th,
        .table-wrap td {
            border: 1px solid var(--line);
        }

        a { color: var(--accent); }

        @media (max-width: 768px) {
            .card { padding: 16px; }
            h1 { font-size: 22px; }
            h2 { font-size: 18px; }
            table { font-size: 13px; }
            .table-wrap {
                margin-right: -2px;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Kebijakan Privasi Aplikasi Siswa Prescientia</h1>
        <div class="meta">
            Berlaku untuk aplikasi siswa Android.<br>
            Terakhir diperbarui: 10 April 2026.
        </div>

        <span class="badge">Rilis Play Store</span>
        <span class="badge">Khusus Aplikasi Siswa</span>
        <span class="badge">Sumber: FE + BE aktif</span>

        <h2>1. Informasi Umum Layanan</h2>
        <ul>
            <li>Nama aplikasi: Prescientia (Aplikasi Siswa)</li>
            <li>Package name Android: id.hadirteknologi.prescientia</li>
            <li>Nama perusahaan/pengelola: Tim Prescientia / Hadir Teknologi</li>
            <li>Email kontak privasi/support: fahmanzaidan1@gmail.com</li>
            <li>URL website resmi: https://presentapi.smkn1ciamis.id (API)</li>
        </ul>

        <h2>2. Data Yang Dikirim Dari Aplikasi Siswa Ke Backend</h2>
        <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Jenis Data</th>
                <th>Data yang Dikirim</th>
                <th>Tujuan Pengumpulan</th>
                <th>Fitur</th>
                <th>Wajib/Opsional</th>
                <th>Pihak Ketiga</th>
                <th>Retensi & Penghapusan</th>
                <th>Dasar Pemrosesan</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Data login</td>
                <td>NISN, password, device_id saat login siswa</td>
                <td>Autentikasi akun dan pembatasan perangkat</td>
                <td>Login siswa</td>
                <td>Wajib</td>
                <td>Tidak dibagikan ke pihak ketiga</td>
                <td>Disimpan pada sistem akun; password disimpan dalam bentuk hash di server. Penghapusan mengikuti kebijakan admin sekolah.</td>
                <td>Kontrak layanan (penyediaan fitur aplikasi) dan kepentingan sah keamanan akun</td>
            </tr>
            <tr>
                <td>Token sesi</td>
                <td>JWT Bearer token pada header Authorization</td>
                <td>Menjaga sesi login dan otorisasi akses endpoint</td>
                <td>Semua endpoint terproteksi</td>
                <td>Wajib setelah login</td>
                <td>Tidak dibagikan</td>
                <td>Masa berlaku token mengikuti konfigurasi server (umumnya 30 hari). Dihapus saat logout/clear data aplikasi.</td>
                <td>Kontrak layanan dan keamanan sistem</td>
            </tr>
            <tr>
                <td>Identitas akun/profil</td>
                <td>Data profil siswa didapat dari server (nama, email, NIS, kelas, dll). Aplikasi tidak mengirim ulang seluruh profil pada tiap request.</td>
                <td>Menampilkan profil dan personalisasi layanan</td>
                <td>Profile, dashboard</td>
                <td>Wajib untuk operasional akun</td>
                <td>Tidak dibagikan</td>
                <td>Disimpan sesuai data akademik sekolah; dihapus saat akun dinonaktifkan/dihapus oleh admin.</td>
                <td>Kontrak layanan dan kewajiban administrasi pendidikan</td>
            </tr>
            <tr>
                <td>Data perangkat</td>
                <td>device_id (UUID) untuk login, validasi device, dan permintaan ganti perangkat</td>
                <td>Mencegah penyalahgunaan akun lintas perangkat tanpa persetujuan</td>
                <td>Login, device change request</td>
                <td>Wajib</td>
                <td>Tidak dibagikan</td>
                <td>Disimpan selama akun aktif atau sampai admin memperbarui data perangkat.</td>
                <td>Kepentingan sah keamanan dan anti-fraud</td>
            </tr>
            <tr>
                <td>Data absensi</td>
                <td>student_id, class_id, calendar_id, check_in_time/check_out_time, status, source, wifi_mac(BSSID), device_id</td>
                <td>Pencatatan kehadiran dan validasi lokasi berbasis WiFi sekolah</td>
                <td>Absensi masuk/keluar</td>
                <td>Wajib untuk fitur absensi</td>
                <td>Tidak dibagikan</td>
                <td>Disimpan sebagai riwayat absensi akademik. Penghapusan mengikuti kebijakan administrasi sekolah.</td>
                <td>Kontrak layanan dan kepentingan sah operasional akademik</td>
            </tr>
            <tr>
                <td>Data WiFi/Jaringan</td>
                <td>BSSID (wifi_mac) yang terhubung saat absensi</td>
                <td>Memverifikasi kehadiran di lokasi jaringan sekolah</td>
                <td>Validasi absensi</td>
                <td>Wajib untuk absensi berbasis WiFi</td>
                <td>Tidak dibagikan</td>
                <td>Tercatat bersama data absensi/login history sesuai retensi sekolah.</td>
                <td>Kepentingan sah (integritas proses absensi)</td>
            </tr>
            <tr>
                <td>Data lokasi GPS</td>
                <td>Pada jalur absensi aktif saat ini tidak mengirim koordinat GPS sebagai payload wajib.</td>
                <td>Tidak digunakan pada jalur produksi utama</td>
                <td>-</td>
                <td>Opsional/tidak aktif pada jalur utama</td>
                <td>Tidak</td>
                <td>Tidak berlaku pada jalur aktif saat ini</td>
                <td>Jika diaktifkan di masa depan: persetujuan pengguna + kepentingan sah</td>
            </tr>
            <tr>
                <td>Data notifikasi</td>
                <td>Request fetch notifikasi siswa dan pengiriman alasan ketidakhadiran (attendance_id, reason, description/evidence bila ada)</td>
                <td>Menyampaikan pengingat/aksi lanjutan absensi</td>
                <td>Notifikasi dalam aplikasi</td>
                <td>Wajib untuk fitur terkait</td>
                <td>Tidak dibagikan</td>
                <td>Disimpan sebagai bagian riwayat absensi/detail absensi.</td>
                <td>Kontrak layanan dan kepentingan sah operasional sekolah</td>
            </tr>
            <tr>
                <td>Data penggunaan/log aktivitas</td>
                <td>history_login: user_id, device_id, wifi_mac, ip_address, login_at, logout_at, duration, status, location (jika dikirim)</td>
                <td>Audit, monitoring, troubleshooting, anti-fraud</td>
                <td>Monitoring operasional</td>
                <td>Wajib untuk keamanan sistem</td>
                <td>Tidak dibagikan</td>
                <td>Disimpan di basis data operasional; belum ada auto-delete berbasis waktu di kode aktif.</td>
                <td>Kepentingan sah keamanan dan akuntabilitas</td>
            </tr>
            </tbody>
        </table>
        </div>

        <div class="note">
            Catatan: aplikasi siswa menggunakan notifikasi lokal perangkat. Integrasi push token FCM tidak ditemukan pada jalur aplikasi siswa saat ini.
        </div>

        <h2>3. Proses Yang Dilakukan Backend</h2>
        <ul>
            <li>Autentikasi dan otorisasi pengguna menggunakan JWT dan middleware role-based (student/teacher/admin).</li>
            <li>Penyimpanan profil dan data akademik siswa untuk kebutuhan operasional aplikasi.</li>
            <li>Pemrosesan data WiFi (BSSID) untuk validasi absensi lokasi sekolah.</li>
            <li>Pengelolaan notifikasi dalam aplikasi terkait status ketidakhadiran dan tindak lanjut alasan.</li>
            <li>Pencatatan log aktivitas login/absensi untuk audit, monitoring, dan pencegahan penyalahgunaan akun.</li>
            <li>Analitik internal: digunakan untuk ringkasan kehadiran/rekap operasional (bukan profiling komersial eksternal).</li>
            <li>Integrasi pihak ketiga pada backend yang terdeteksi: Redis (cache/session teknis). Tidak ada integrasi crash analytics komersial pada jalur backend ini.</li>
        </ul>

        <h2>4. Keamanan Data</h2>
        <ul>
            <li>Enkripsi saat transit: komunikasi aplikasi ke API ditujukan melalui HTTPS/TLS pada endpoint produksi.</li>
            <li>Enkripsi saat disimpan: tidak ada enkripsi field-level eksplisit di kode backend; perlindungan data mengandalkan kontrol akses aplikasi, hashing password, dan keamanan infrastruktur.</li>
            <li>Kontrol akses: role-based access melalui middleware JWT (student, teacher, admin).</li>
            <li>Backup dan disaster recovery: mengikuti kebijakan infrastruktur operasional institusi (silakan lengkapi SOP internal).</li>
            <li>Insiden kebocoran data: notifikasi insiden dan mitigasi mengikuti prosedur keamanan internal institusi/pengelola.</li>
        </ul>

        <h2>5. Hak Pengguna</h2>
        <ul>
            <li>Permintaan akses data: melalui admin sekolah/helpdesk resmi.</li>
            <li>Perbaikan data: diajukan ke admin sekolah yang mengelola data induk siswa.</li>
            <li>Penghapusan akun/data: diajukan ke admin sekolah sesuai kebijakan retensi akademik dan peraturan yang berlaku.</li>
            <li>Penarikan persetujuan: pengguna dapat menghentikan izin tertentu di perangkat, namun fitur terkait mungkin tidak berfungsi.</li>
        </ul>

        <h2>6. Data Anak Di Bawah Umur</h2>
        <ul>
            <li>Aplikasi ditujukan untuk siswa (dapat mencakup pengguna di bawah umur).</li>
            <li>Pemrosesan data dilakukan untuk kepentingan layanan pendidikan sekolah.</li>
            <li>Persetujuan orang tua/wali mengikuti kebijakan sekolah dan regulasi lokal yang berlaku.</li>
        </ul>

        <h2>7. Lokasi Server Dan Transfer Data</h2>
        <ul>
            <li>Lokasi server utama: mengikuti infrastruktur deployment API Prescientia (domain produksi: presentapi.smkn1ciamis.id).</li>
            <li>Transfer lintas negara: tidak ditujukan sebagai transfer lintas negara pada arsitektur inti yang terdeteksi di repositori ini.</li>
            <li>Jika terjadi transfer lintas negara di masa depan, perlindungan dilakukan melalui TLS, kontrol akses, dan perjanjian pemrosesan data yang sesuai regulasi.</li>
        </ul>

        <div class="note warn">
            Jika diperlukan untuk dokumen legal final Play Store, mohon validasi akhir pada 3 poin berikut: email kontak privasi resmi, nama badan hukum pengelola yang final, serta kebijakan retensi data resmi institusi.
        </div>
    </div>
</div>
</body>
</html>
