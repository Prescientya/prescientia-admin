<?php

namespace Database\Seeders;

use App\Models\GuidelineItem;
use App\Models\GuidelinePage;
use App\Models\GuidelineSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Mengisi konten halaman panduan untuk aplikasi Siswa & Guru.
 *
 * Catatan validasi (lihat App\Http\Controllers\GuidelineController):
 *   - page.title       : max 20 karakter
 *   - section.title    : max 20 karakter
 *   - item.title       : max 20 karakter
 *   - page.subtitle    : max 1000
 *   - section.desc     : max 2000
 *   - item.content     : max 5000
 *
 * Konten dibangun dari implementasi nyata aplikasi prescientia_fe (Siswa)
 * dan prescientia_guru_fe (Guru), termasuk pembedaan role:
 *
 *   Siswa : Pelajar (default) | KM | WKM (Wakil KM) | Sekretaris
 *           Hanya KM/WKM/Sekretaris yang mendapat menu "Management Kelas".
 *           Sumber: prescientia_fe/lib/widgets/app_scaffold.dart:83-90
 *
 *   Guru  : Pengajar (default) | Wali Kelas (homeroom)
 *           Hanya Wali Kelas yang mendapat menu Konfirmasi Kehadiran
 *           & Persetujuan Surat Izin Siswa.
 *           Sumber: prescientia_guru_fe/lib/widgets/app_scaffold.dart:89-102
 */
class GuidelineSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedSiswa();
            $this->seedGuru();
        });
    }

    /* ─────────────────────────────────────────────────────────
     |  PANDUAN SISWA
     |───────────────────────────────────────────────────────── */
    private function seedSiswa(): void
    {
        $page = GuidelinePage::updateOrCreate(
            ['user_type' => 'siswa'],
            [
                'title'        => 'Panduan Siswa',
                'subtitle'     => 'Panduan lengkap penggunaan aplikasi Prescientia untuk siswa: cara absensi berbasis WiFi sekolah, mengajukan surat izin, melihat rekap kehadiran, hingga pengelolaan kelas untuk pengurus kelas (KM, Wakil KM, dan Sekretaris).',
                'is_published' => true,
            ]
        );

        $page->sections()->delete();

        $sections = [
            [
                'title'       => 'Persiapan Awal',
                'description' => 'Sebelum mulai absensi, pastikan akun aktif dan perangkat Anda siap. Semua langkah di bawah ini wajib agar fitur deteksi WiFi sekolah dapat berjalan.',
                'items' => [
                    ['Login ke Aplikasi',  'Buka aplikasi Prescientia Siswa. Masukkan NIS/username dan password yang diberikan oleh sekolah, lalu tekan tombol Login. Sesi disimpan otomatis sehingga Anda tidak perlu login setiap hari sampai token kedaluwarsa atau melakukan logout.'],
                    ['Aktifkan WiFi',      'Hidupkan mode WiFi melalui pengaturan ponsel. Anda tidak harus terkoneksi ke jaringan tertentu — WiFi cukup dalam keadaan ON agar aplikasi dapat memindai jaringan di sekitar.'],
                    ['Aktifkan Lokasi',    'Hidupkan layanan Lokasi/GPS pada perangkat. Sistem Android mewajibkan lokasi aktif untuk membaca SSID/BSSID WiFi di sekitar Anda.'],
                    ['Beri Izin Akses',    'Saat pertama membuka aplikasi, izinkan permintaan akses Lokasi dan "Perangkat WiFi Terdekat". Tanpa kedua izin ini pemindaian WiFi sekolah tidak akan berjalan.'],
                    ['Pastikan Internet',  'Aplikasi membutuhkan koneksi internet (data seluler atau WiFi lain) untuk berkomunikasi dengan server. Indikator "Backend Ready" akan tampil hijau di Beranda bila koneksi sehat.'],
                ],
            ],
            [
                'title'       => 'Peran & Akses',
                'description' => 'Aplikasi Siswa mengenali 4 (empat) jenis peran kelas. Tiga di antaranya — KM, Wakil KM, dan Sekretaris — mendapat menu tambahan "Management Kelas" yang tidak tersedia untuk Pelajar biasa.',
                'items' => [
                    ['Pelajar (Default)',  'Peran standar untuk seluruh siswa. Hak aksesnya: melakukan absensi WiFi, mengajukan surat izin/sakit, melihat rekap kehadiran pribadi, melihat kalender event, menerima notifikasi, dan mengelola profil. Pelajar TIDAK memiliki akses Management Kelas.'],
                    ['Ketua Murid (KM)',   'Selain seluruh hak akses Pelajar, KM mendapat menu tambahan "Management Kelas" yang dapat digunakan untuk memantau dan mengubah status kehadiran teman sekelasnya secara cepat (lihat seksi "Management Kelas").'],
                    ['Wakil Ketua (WKM)',  'Memiliki hak akses identik dengan KM. Wakil Ketua dapat mengakses menu "Management Kelas" dan mengubah status kehadiran teman sekelas, biasanya saat KM berhalangan.'],
                    ['Sekretaris',         'Sama seperti KM dan WKM, Sekretaris mendapat menu "Management Kelas". Peran ini umumnya bertugas mencatat status kehadiran harian kelas sehingga akses ini sangat membantu rekap manual.'],
                ],
            ],
            [
                'title'       => 'Cara Absen Masuk',
                'description' => 'Absensi siswa dilakukan otomatis dengan validasi jaringan WiFi sekolah (sumber: digital_wifi). Anda tidak perlu menekan tombol khusus — cukup berada di lingkungan sekolah.',
                'items' => [
                    ['Buka Beranda',         'Setelah login, aplikasi langsung membuka halaman Beranda dan memulai pemeriksaan kondisi: WiFi aktif, Lokasi aktif, hari libur, dan keberadaan WiFi sekolah.'],
                    ['Tunggu Pemeriksaan',   'Aplikasi menampilkan pesan "Memeriksa status WiFi dan Lokasi...". Jika ada syarat yang belum terpenuhi, akan muncul daftar kondisi yang harus Anda penuhi terlebih dahulu.'],
                    ['Deteksi WiFi Sekolah', 'Aplikasi memindai seluruh WiFi di sekitar lalu mencocokkan SSID dan BSSID dengan daftar WiFi resmi sekolah yang terdaftar di server. Anda dianggap "hadir di sekolah" bila minimal satu jaringan sekolah terdeteksi (Anda tidak harus terkoneksi ke jaringan tersebut).'],
                    ['Absen Otomatis',       'Begitu jaringan sekolah terdeteksi, sistem otomatis mengirim data absensi dengan sumber digital_wifi. Status akan "hadir" jika dilakukan sebelum pukul 06:30, atau "terlambat" jika setelahnya.'],
                    ['Konfirmasi Sukses',    'Setelah berhasil, kartu sapaan menampilkan nama dan waktu check-in Anda. Status hari ini juga muncul di kartu ringkasan beranda dan tersembunyi otomatis setelah 10 detik.'],
                    ['Hari Libur',           'Jika hari ini ditandai libur pada Kalender Sekolah, aplikasi tidak menerima absensi dan menampilkan pesan "Hari ini libur, selamat beristirahat!".'],
                ],
            ],
            [
                'title'       => 'Status Kehadiran',
                'description' => 'Aplikasi mengenali lima status kehadiran. Setiap status memiliki warna dan ikon yang berbeda pada kartu rekap.',
                'items' => [
                    ['Hadir',     'Anda terdeteksi di jaringan WiFi sekolah dan melakukan absensi sebelum batas waktu pukul 06:30.'],
                    ['Terlambat', 'Anda terdeteksi di jaringan WiFi sekolah namun melakukan absensi setelah pukul 06:30. Tetap terhitung kehadiran, namun ditandai terlambat.'],
                    ['Sakit',     'Status manual yang diberikan setelah Anda mengajukan Surat Izin dengan alasan "sakit" dan disetujui oleh wali kelas.'],
                    ['Izin',      'Status manual yang diberikan setelah Anda mengajukan Surat Izin dengan alasan "izin" dan disetujui oleh wali kelas.'],
                    ['Alpa',      'Status default jika Anda tidak melakukan absensi pada hari aktif sekolah dan tidak mengajukan surat izin.'],
                ],
            ],
            [
                'title'       => 'Surat Izin/Sakit',
                'description' => 'Gunakan fitur Surat Izin saat Anda tidak dapat hadir ke sekolah. Surat akan diteruskan ke wali kelas untuk dikonfirmasi (approve/reject).',
                'items' => [
                    ['Buka Menu Surat',  'Pada Beranda, ketuk kartu/tombol "Ajukan Surat Izin", atau buka menu Surat Izin dari sidebar navigasi.'],
                    ['Pilih Alasan',     'Pilih salah satu dari dua jenis alasan: Sakit atau Izin. Jenis yang dipilih menentukan status akhir kehadiran Anda setelah disetujui wali kelas.'],
                    ['Isi Deskripsi',    'Tulis keterangan singkat alasan ketidakhadiran (mis. demam, keperluan keluarga). Deskripsi wajib diisi sebagai bukti pengajuan.'],
                    ['Kirim Pengajuan',  'Tekan tombol Kirim. Aplikasi akan menampilkan notifikasi keberhasilan dan mencegah pengajuan ganda di hari yang sama.'],
                    ['Pantau Status',    'Status surat Anda ditampilkan: Pending (menunggu), Disetujui, atau Ditolak. Status kehadiran harian baru berubah setelah surat disetujui oleh wali kelas.'],
                ],
            ],
            [
                'title'       => 'Rekap Kehadiran',
                'description' => 'Halaman Rekap Kehadiran menampilkan riwayat lengkap absensi Anda dengan filter dan paginasi.',
                'items' => [
                    ['Buka Rekap',        'Pilih menu "Rekap Kehadiran" dari sidebar. Data dimuat dari server dan disimpan sementara (cache 5 menit) sehingga pembukaan ulang terasa instan.'],
                    ['Gunakan Filter',    'Anda dapat memfilter berdasarkan Tanggal tertentu, Hari (Senin–Jumat), dan Status (hadir, sakit, izin, alpa). Tekan tombol Reset untuk menghapus seluruh filter.'],
                    ['Baca Tabel Rekap',  'Setiap baris menampilkan kolom Tanggal, Hari, Waktu check-in, dan Status (berwarna). Ketuk badge Status untuk melihat label lengkapnya.'],
                    ['Navigasi Halaman',  'Data dibatasi 10 baris per halaman. Gunakan tombol panah dan tombol "first/last" untuk berpindah halaman.'],
                    ['Refresh Manual',    'Tarik layar ke bawah (pull-to-refresh) untuk memuat ulang data terbaru langsung dari server.'],
                ],
            ],
            [
                'title'       => 'Management Kelas',
                'description' => 'PERHATIAN: Menu ini HANYA muncul untuk siswa dengan peran KM (Ketua Murid), Wakil KM, dan Sekretaris. Jika Anda berstatus Pelajar biasa, menu ini tidak akan tampil di sidebar — itu normal dan bukan bug. Hubungi wali kelas bila Anda merasa peran kelas perlu diperbarui.',
                'items' => [
                    ['Akses Menu',           'Untuk KM/WKM/Sekretaris, buka menu "Management Kelas" dari sidebar navigasi. Halaman akan memuat data kehadiran SELURUH siswa kelas Anda untuk hari ini.'],
                    ['Filter Status Siswa',  'Gunakan filter cepat di atas daftar untuk menampilkan siswa berdasarkan status: Semua, Hadir, Sakit, Izin, Alpa, atau Belum Ada Info (belum tercatat absensinya).'],
                    ['Pilih Siswa',          'Ketuk satu atau beberapa siswa untuk memilih (multi-select). Diri Anda sendiri tidak dapat dipilih — sistem mencegah perubahan status untuk diri sendiri.'],
                    ['Pilih Semua "No Info"','Tekan tombol "Pilih Semua Belum Absen" untuk dengan cepat menyeleksi seluruh siswa yang belum memiliki catatan kehadiran hari ini.'],
                    ['Tentukan Status',     'Setelah memilih, panel batch akan muncul. Pilih status target (hadir, sakit, izin, alpa) dan tambahkan catatan opsional bila perlu.'],
                    ['Kirim Pembaruan',      'Tekan tombol Submit. Sistem memproses dua jenis perubahan sekaligus: membuat catatan baru untuk siswa yang belum absen, atau memperbarui catatan untuk yang sudah ada.'],
                    ['Sakit/Izin Khusus',    'Status "sakit" dan "izin" yang Anda kirim TIDAK langsung final — sistem akan meneruskannya ke wali kelas untuk dikonfirmasi terlebih dahulu, persis seperti pengajuan surat izin.'],
                ],
            ],
            [
                'title'       => 'Fitur Pendukung',
                'description' => 'Beberapa fitur tambahan untuk mendukung aktivitas akademik Anda.',
                'items' => [
                    ['Event Sekolah',  'Halaman Events menampilkan agenda dan event resmi sekolah, termasuk hari libur. Status libur secara otomatis menonaktifkan absensi pada tanggal terkait.'],
                    ['Notifikasi',     'Halaman Notifikasi menampilkan pengingat absensi pagi, pengingat checkout pulang, info hari libur, dan pemberitahuan terkait surat izin Anda.'],
                    ['Profil Siswa',   'Menampilkan data diri (NIS, kelas, wali kelas, foto). Dari sini Anda juga dapat mengganti tema (terang/gelap) dan melakukan Logout.'],
                ],
            ],
            [
                'title'       => 'Troubleshooting',
                'description' => 'Solusi cepat untuk masalah yang sering terjadi saat menggunakan aplikasi.',
                'items' => [
                    ['WiFi Belum Aktif',    'Pesan "Mode WiFi belum diaktifkan" muncul bila WiFi mati. Aktifkan WiFi melalui pengaturan ponsel — Anda tidak perlu terkoneksi ke jaringan tertentu, cukup mode WiFi ON.'],
                    ['Lokasi Belum Aktif',  'Pesan "Layanan Lokasi belum aktif" muncul bila GPS mati. Hidupkan Lokasi melalui quick settings atau pengaturan ponsel.'],
                    ['Izin Belum Diberi',   'Pesan "Izin belum diberikan" muncul bila aplikasi belum mendapat izin Lokasi atau Nearby Devices. Buka Pengaturan > Aplikasi > Prescientia Siswa > Izin lalu aktifkan keduanya.'],
                    ['WiFi Sekolah Hilang', 'Pesan "WiFi sekolah tidak ditemukan" berarti tidak ada jaringan sekolah terdeteksi. Dekati access point atau pindah ke area yang lebih terbuka.'],
                    ['Backend Terputus',    'Pesan "Backend tidak terhubung" berarti ponsel tidak dapat menghubungi server. Pastikan koneksi internet (data seluler atau WiFi lain) aktif.'],
                    ['Sesi Kadaluwarsa',    'Pesan "Sesi kadaluwarsa" berarti token login Anda telah habis masa berlakunya. Lakukan login ulang dengan kredensial Anda.'],
                    ['Sudah Absen',         'Pesan "Sudah absen hari ini" berarti sistem telah mencatat absensi Anda. Sistem mencegah absensi ganda — tunggu hari berikutnya.'],
                ],
            ],
        ];

        $this->writeSections($page, $sections);
    }

    /* ─────────────────────────────────────────────────────────
     |  PANDUAN GURU
     |───────────────────────────────────────────────────────── */
    private function seedGuru(): void
    {
        $page = GuidelinePage::updateOrCreate(
            ['user_type' => 'guru'],
            [
                'title'        => 'Panduan Guru',
                'subtitle'     => 'Panduan lengkap penggunaan aplikasi Prescientia untuk guru: absensi pribadi berbasis WiFi, mengajar harian, hingga tugas tambahan khusus Wali Kelas seperti konfirmasi kehadiran siswa dan persetujuan surat izin.',
                'is_published' => true,
            ]
        );

        $page->sections()->delete();

        $sections = [
            [
                'title'       => 'Persiapan Awal',
                'description' => 'Langkah pertama sebelum guru menggunakan aplikasi sehari-hari. Semua kondisi di bawah ini wajib agar fitur absensi berjalan normal.',
                'items' => [
                    ['Login ke Aplikasi', 'Buka aplikasi Prescientia Guru. Masukkan NIP/username dan password Anda, lalu tekan Login. Sesi disimpan otomatis sampai Anda logout atau token kedaluwarsa.'],
                    ['Aktifkan WiFi',     'Sama seperti aplikasi siswa, WiFi perangkat wajib aktif agar aplikasi dapat memindai jaringan sekolah di sekitar (Anda tidak harus terkoneksi ke salah satu SSID).'],
                    ['Aktifkan Lokasi',   'Hidupkan layanan Lokasi/GPS perangkat. Wajib aktif agar Android mengizinkan akses data WiFi (SSID/BSSID) ke aplikasi.'],
                    ['Beri Izin Akses',   'Izinkan permintaan akses Lokasi dan "Perangkat WiFi Terdekat" saat diminta. Tanpa keduanya, fitur deteksi WiFi sekolah tidak berjalan.'],
                    ['Pastikan Internet', 'Internet (data seluler atau WiFi non-sekolah) wajib aktif. Semua aksi — absensi, konfirmasi siswa, jadwal — memerlukan sinkronisasi ke server.'],
                ],
            ],
            [
                'title'       => 'Peran Guru',
                'description' => 'Aplikasi Guru mengenali 2 (dua) peran. Peran "Wali Kelas" mendapat menu tambahan yang tidak tersedia untuk pengajar biasa.',
                'items' => [
                    ['Pengajar',     'Peran standar untuk seluruh guru. Hak akses: absensi pribadi via WiFi sekolah, checkout pulang, ajukan surat izin pribadi, lihat jadwal mengajar hari ini, tandai kehadiran pada periode mengajar, lihat daftar siswa di kelas yang diajar, lihat rekap kehadiran pribadi, lihat kalender event, dan kelola profil. Pengajar TIDAK memiliki akses Konfirmasi Kehadiran maupun Persetujuan Surat Izin Siswa.'],
                    ['Wali Kelas',   'Selain seluruh hak akses Pengajar, Wali Kelas (homeroom) mendapat dua menu tambahan di sidebar: "Konfirmasi Kehadiran" (menyetujui/menolak laporan absensi pending dari siswa kelasnya) dan "Surat Izin Siswa" (menyetujui/menolak surat izin yang diajukan siswa kelas perwaliannya). Lihat seksi "Konfirmasi Hadir" dan "Setujui Surat Izin".'],
                ],
            ],
            [
                'title'       => 'Cara Absen Guru',
                'description' => 'Mekanisme absensi guru identik dengan siswa: deteksi otomatis berbasis WiFi sekolah di halaman Dashboard.',
                'items' => [
                    ['Buka Dashboard',     'Setelah login, halaman Dashboard secara otomatis menjalankan pengecekan WiFi, Lokasi, dan status hari libur dari kalender sekolah.'],
                    ['Penuhi Syarat',      'Pastikan WiFi ON, Lokasi ON, dan izin sudah diberikan. Kartu syarat akan menampilkan ceklist dan menghilang otomatis setelah seluruhnya terpenuhi.'],
                    ['Deteksi Sekolah',    'Aplikasi memindai SSID/BSSID di sekitar dan mencocokkannya dengan daftar WiFi sekolah yang terdaftar di server. Bila cocok, absensi langsung tercatat.'],
                    ['Checkout/Pulang',    'Setelah jam pelajaran terakhir berakhir, aplikasi menampilkan kartu Checkout. Konfirmasi jam pulang Anda agar tercatat sebagai waktu keluar.'],
                    ['Hari Libur',         'Bila tanggal hari ini ditandai libur di kalender sekolah, aplikasi tidak menerima absensi dan menampilkan pemberitahuan "Hari Libur".'],
                ],
            ],
            [
                'title'       => 'Surat Izin Guru',
                'description' => 'Gunakan fitur ini bila Anda tidak dapat hadir ke sekolah karena sakit atau keperluan lain.',
                'items' => [
                    ['Buka Menu Surat',    'Akses menu Surat Izin dari sidebar navigasi.'],
                    ['Pilih Alasan',       'Pilih jenis: Sakit atau Izin. Tulis deskripsi penjelasan singkat. Sistem mencegah pengajuan ganda di hari yang sama.'],
                    ['Kirim Pengajuan',    'Tekan Kirim. Status pengajuan ditampilkan dan dapat dipantau melalui halaman yang sama.'],
                ],
            ],
            [
                'title'       => 'Jadwal Mengajar',
                'description' => 'Menu Jadwal Hari Ini menampilkan seluruh jam mengajar Anda dan memungkinkan pencatatan kehadiran tiap periode.',
                'items' => [
                    ['Lihat Periode',      'Halaman menampilkan daftar periode mengajar berdasarkan jadwal mingguan dan kalender sekolah. Periode yang sedang berlangsung ditandai aktif (tombol berwarna).'],
                    ['Mulai Periode',      'Tekan tombol periode untuk membuka kelas. Sistem akan mencatat kehadiran Anda pada periode tersebut dan membuka layar daftar siswa kelas yang bersangkutan.'],
                    ['Pengingat Periode',  'Aplikasi mengirim pengingat 10 menit sebelum jadwal dimulai untuk periode yang belum Anda submit kehadirannya. Pengingat dimatikan otomatis pada hari libur.'],
                ],
            ],
            [
                'title'       => 'Kelola Siswa',
                'description' => 'Halaman Kelas menampilkan daftar siswa beserta status absensi terkini untuk setiap kelas yang Anda ajar.',
                'items' => [
                    ['Pilih Kelas',        'Buka menu Kelas untuk melihat daftar kelas yang Anda ajar, lalu pilih salah satu kelas.'],
                    ['Lihat Status',       'Setiap siswa ditampilkan dengan status hari ini: hadir, terlambat, sakit, izin, alpa, atau belum_absen.'],
                    ['Filter Status',      'Gunakan filter di atas daftar untuk menampilkan hanya siswa dengan status tertentu (mis. "belum_absen") agar pemantauan lebih cepat.'],
                ],
            ],
            [
                'title'       => 'Konfirmasi Hadir',
                'description' => 'KHUSUS WALI KELAS: Menu "Konfirmasi Kehadiran" hanya muncul di sidebar untuk guru berperan Wali Kelas. Pengajar biasa tidak akan melihat menu ini. Fitur ini digunakan untuk mengkonfirmasi laporan absensi siswa yang berstatus pending.',
                'items' => [
                    ['Buka Menu',          'Pilih menu "Konfirmasi Kehadiran" di sidebar. Halaman menampilkan daftar siswa kelas perwalian Anda yang absensinya masih pending.'],
                    ['Periksa Detail',     'Tiap kartu menampilkan foto, nama, dan info absensi siswa. Kartu dengan status pending ditandai border oranye.'],
                    ['Setujui (Approve)',  'Tekan tombol Setujui untuk mengkonfirmasi absensi siswa. Status berubah menjadi approved dan tercatat sebagai kehadiran resmi.'],
                    ['Tolak (Reject)',     'Tekan tombol Tolak bila laporan tidak sesuai. Status berubah menjadi rejected dan tidak terhitung sebagai kehadiran.'],
                    ['Tidak Ada Kelas',    'Bila pesan "Tidak ada kelas wali untuk dimuat" muncul, akun Anda belum ditetapkan sebagai Wali Kelas. Hubungi admin sekolah untuk penyesuaian peran.'],
                ],
            ],
            [
                'title'       => 'Setujui Surat Izin',
                'description' => 'KHUSUS WALI KELAS: Menu "Surat Izin Siswa" hanya muncul untuk guru berperan Wali Kelas. Surat izin/sakit yang diajukan siswa kelas perwalian Anda harus dikonfirmasi terlebih dahulu agar berubah menjadi status kehadiran resmi.',
                'items' => [
                    ['Buka Menu',     'Pilih menu "Surat Izin Siswa" di sidebar. Daftar surat pending dari siswa kelas Anda akan ditampilkan.'],
                    ['Tinjau Surat',  'Periksa nama siswa, jenis alasan (sakit/izin), dan deskripsi yang ditulis siswa.'],
                    ['Setujui',       'Tekan Setujui setelah konfirmasi singkat. Status kehadiran siswa hari itu akan menjadi "sakit" atau "izin" sesuai jenis surat.'],
                    ['Tolak',         'Tekan Tolak bila alasan tidak dapat diterima. Status siswa akan tetap "alpa" jika tidak ada absensi lain.'],
                ],
            ],
            [
                'title'       => 'Rekap Kehadiran',
                'description' => 'Halaman Rekap Kehadiran menampilkan riwayat kehadiran pribadi Anda sebagai guru.',
                'items' => [
                    ['Buka Rekap',          'Pilih menu Rekap Kehadiran. Data dimuat dari server dan disimpan sementara (cache 5 menit) sehingga akses ulang lebih cepat.'],
                    ['Filter Periode',      'Gunakan filter cepat: Hari ini, Minggu ini, Bulan ini, atau Semua. Anda juga dapat memfilter berdasarkan status (hadir, alpa, sakit, izin, terlambat).'],
                    ['Paginasi/Refresh',    'Data ditampilkan 10 baris per halaman. Tarik layar ke bawah untuk refresh data terbaru.'],
                ],
            ],
            [
                'title'       => 'Fitur Pendukung',
                'description' => 'Fitur tambahan untuk mendukung aktivitas Anda di sekolah.',
                'items' => [
                    ['Event Sekolah',  'Halaman Events menampilkan event resmi sekolah dan hari libur. Hari libur otomatis menonaktifkan absensi dan pengingat periode.'],
                    ['Notifikasi',     'Pengingat absensi pagi, pengingat checkout pulang, pengingat periode mengajar, serta — khusus Wali Kelas — notifikasi pengajuan surat izin siswa.'],
                    ['Profil Guru',    'Lihat data diri (NIP, mata pelajaran, kelas yang diajar, status Wali Kelas), ubah tema aplikasi, dan lakukan Logout.'],
                ],
            ],
            [
                'title'       => 'Troubleshooting',
                'description' => 'Solusi cepat untuk kendala umum.',
                'items' => [
                    ['WiFi/Lokasi Mati',    'Aktifkan WiFi dan Lokasi/GPS di pengaturan perangkat. Keduanya wajib untuk pemindaian jaringan sekolah.'],
                    ['Izin Belum Diberi',   'Buka Pengaturan > Aplikasi > Prescientia Guru > Izin, lalu aktifkan izin Lokasi dan Perangkat Terdekat.'],
                    ['WiFi Sekolah Hilang', 'Pastikan Anda berada di area jangkauan WiFi sekolah. Jika tetap tidak terdeteksi, hubungi admin sekolah untuk memastikan SSID/BSSID terdaftar di sistem.'],
                    ['Bukan Wali Kelas',    'Bila menu "Konfirmasi Kehadiran" atau "Surat Izin Siswa" tidak muncul, akun Anda belum berperan Wali Kelas. Hubungi admin sekolah bila peran perlu diperbarui.'],
                    ['Sesi Kadaluwarsa',    'Token login telah habis masa berlakunya. Lakukan login ulang dengan kredensial Anda.'],
                    ['Backend Terputus',    'Pastikan perangkat memiliki koneksi internet aktif (data seluler atau WiFi non-sekolah).'],
                ],
            ],
        ];

        $this->writeSections($page, $sections);
    }

    /**
     * @param  GuidelinePage  $page
     * @param  array<int, array{title:string, description:string, items:array<int, array<int,string>>}>  $sections
     */
    private function writeSections(GuidelinePage $page, array $sections): void
    {
        foreach ($sections as $sectionIndex => $sectionData) {
            $section = GuidelineSection::create([
                'guideline_page_id' => $page->id,
                'title'             => $sectionData['title'],
                'description'       => $sectionData['description'],
                'order'             => $sectionIndex + 1,
            ]);

            foreach ($sectionData['items'] as $itemIndex => $item) {
                GuidelineItem::create([
                    'guideline_section_id' => $section->id,
                    'title'                => $item[0],
                    'content'              => $item[1],
                    'image_path'           => null,
                    'order'                => $itemIndex + 1,
                ]);
            }
        }
    }
}
