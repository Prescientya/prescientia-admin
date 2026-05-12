Prescientia Admin
Panel administrasi berbasis Laravel (PHP) untuk mengelola data akademik dan operasional sekolah secara terpusat. Mencakup manajemen user & role, kelas, mata pelajaran, jadwal, presensi, kalender sekolah, serta ekspor/impor data. Menggunakan Vite untuk frontend asset dan Laravel Excel untuk kebutuhan ekspor.

Tujuan & Fungsi Utama

Menyederhanakan proses administrasi sekolah
Menyediakan sumber data tunggal untuk jadwal, kelas, presensi, dan data pengguna
Mendukung kebutuhan pelaporan dan ekspor data
Menjaga konsistensi data antar modul (jadwal, kelas, dan presensi)


Alur Kerja
Login admin
  └─► Kelola data master (guru, siswa, kelas, mata pelajaran)
  └─► Atur jadwal & periode kelas
  └─► Pantau presensi siswa/guru & lakukan koreksi bila diperlukan
  └─► Ekspor laporan atau data yang dibutuhkan

Modul & Fitur
ModulDeskripsiUser & Role ManagementManajemen akun admin, guru, dan siswaKelas & Mata PelajaranPengelolaan kelas dan mata pelajaranJadwal & Periode KelasPengaturan jadwal mengajar dan periode belajarPresensi Siswa/GuruPemantauan dan koreksi data presensiKalender SekolahManajemen event dan hari libur sekolahEkspor & Impor DataTemplate dan file ekspor/impor dataManajemen Perangkat/Wi-FiPengelolaan perangkat dan jaringan (jika digunakan)

Tech Stack
KategoriTeknologiBackendLaravel (PHP)Frontend AssetViteDatabaseMySQL / MariaDBExport / ImportLaravel Excel (maatwebsite/excel)Containerization (opsional)Docker / Docker ComposeTestingPHPUnit

Setup (Local Development)
1. Clone Repository
bashgit clone <repo_url>
cd prescientia-admin
2. Konfigurasi Environment
bashcp .env.example .env
Sesuaikan koneksi database dan konfigurasi lain di file .env.
3. Install Dependencies
bashcomposer install
npm install
4. Generate App Key
bashphp artisan key:generate
5. Migrasi Database & Seed
bashphp artisan migrate --seed
6. Build Asset
bashnpm run dev
7. Jalankan Server
bashphp artisan serve

Struktur Proyek
app/          → Logic aplikasi (Models, Controllers, Imports/Exports)
database/     → Migrations, seeders, factories
resources/    → Asset frontend (CSS/JS)
routes/       → Web dan console routes
public/       → File publik & hasil build
tests/        → Unit/Feature test

Catatan

Pastikan .env sudah sesuai dengan environment yang digunakan
Untuk produksi, jalankan optimasi dengan php artisan optimize
Jika menggunakan Docker, jalankan dengan konfigurasi Compose sesuai tim
