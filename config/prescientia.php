<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Prescientia Attendance Settings
    |--------------------------------------------------------------------------
    */

    'check_in_time' => [
        'start' => '06:00:00',  // Jam mulai check-in
        'end' => '07:30:00',    // Jam akhir check-in
        'late_after' => '07:00:00', // Setelah jam ini dianggap terlambat
    ],

    'check_out_time' => [
        'start' => '14:00:00',  // Jam mulai check-out
        'end' => '17:00:00',    // Jam akhir check-out
    ],

    'auto_checkout' => [
        'enabled' => true,
        'time' => '17:00:00',   // Otomatis checkout jika belum checkout
    ],

    'wifi_detection' => [
        'enabled' => true,
        'auto_checkin' => true, // Auto check-in saat terdeteksi WiFi sekolah
        'detection_interval' => 300, // Deteksi setiap 5 menit (dalam detik)
    ],

    'attendance_source_priority' => [
        'digital_wifi',     // Prioritas tertinggi
        'guru_pengajar',
        'wali_kelas',
        'self_report',      // Prioritas terendah
    ],

    'notification' => [
        'absent_notification' => true, // Notifikasi orang tua jika anak tidak hadir
        'late_notification' => true,   // Notifikasi jika terlambat
        'notification_time' => '08:00:00', // Jam kirim notifikasi
    ],

    'report' => [
        'attendance_threshold' => 75, // Minimal kehadiran (%)
        'warning_threshold' => 85,    // Warning jika di bawah ini
        'absent_limit' => 10,         // Maksimal alpha dalam semester
    ],

    'academic_year' => [
        'current' => '2025/2026',
        'start_month' => 7,  // Juli
        'end_month' => 6,    // Juni
    ],

    /*
    |--------------------------------------------------------------------------
    | Titik 3: Fitur dan Aksi Menu Admin
    |--------------------------------------------------------------------------
    | Daftar lengkap semua fitur dan aksi CRUD yang tersedia di aplikasi
    */

    'features' => [
        'mbg_officers' => [
            'name' => 'Petugas MBG',
            'icon' => 'assets/icons/list.png',
            'description' => 'Kelola data petugas pembagian MBG (Makan Bersama Gratis)',
            'actions' => [
                'index' => [
                    'name' => 'Lihat Daftar',
                    'route' => 'admin.mbg-officers.index',
                    'method' => 'GET',
                    'icon' => 'bi bi-list',
                    'description' => 'Menampilkan daftar semua petugas MBG'
                ],
                'create' => [
                    'name' => 'Tambah Petugas',
                    'route' => 'admin.mbg-officers.create',
                    'method' => 'GET',
                    'icon' => 'bi bi-plus-circle',
                    'description' => 'Menambahkan petugas MBG baru'
                ],
                'store' => [
                    'name' => 'Simpan Petugas',
                    'route' => 'admin.mbg-officers.store',
                    'method' => 'POST',
                    'icon' => 'bi bi-save',
                    'description' => 'Menyimpan data petugas MBG baru ke database'
                ],
                'show' => [
                    'name' => 'Lihat Detail',
                    'route' => 'admin.mbg-officers.show',
                    'method' => 'GET',
                    'icon' => 'bi bi-eye',
                    'description' => 'Menampilkan detail lengkap petugas MBG'
                ],
                'edit' => [
                    'name' => 'Edit Data',
                    'route' => 'admin.mbg-officers.edit',
                    'method' => 'GET',
                    'icon' => 'bi bi-pencil',
                    'description' => 'Membuka form edit untuk mengubah data petugas'
                ],
                'update' => [
                    'name' => 'Perbarui Data',
                    'route' => 'admin.mbg-officers.update',
                    'method' => 'PUT',
                    'icon' => 'bi bi-check-circle',
                    'description' => 'Memperbarui data petugas MBG di database'
                ],
                'destroy' => [
                    'name' => 'Hapus Data',
                    'route' => 'admin.mbg-officers.destroy',
                    'method' => 'DELETE',
                    'icon' => 'bi bi-trash',
                    'description' => 'Menghapus data petugas MBG dari database'
                ]
            ]
        ]
    ],

];
