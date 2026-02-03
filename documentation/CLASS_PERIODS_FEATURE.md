# Dokumentasi: Tabel Jam Pelajaran (Class Periods)

**Update: 30 Januari 2026**

## 📋 Ringkasan

Fitur ini menambahkan tabel `class_periods` untuk menyimpan jadwal jam pelajaran per hari dengan detail waktu mulai, waktu selesai, dan jenis aktivitas. Data jadwal diambil dari dokumen jadwal sekolah semester 2 dan bersifat **statis global** (berlaku untuk semua kelas).

## 🎯 Use Case

- **Rekap Absen Per Jam**: Sistem dapat membuat laporan absensi siswa/guru berdasarkan jam pelajaran spesifik
- **Referensi Jam Pelajaran**: Controller/View dapat mengakses jam pelajaran untuk navigasi dan validasi
- **Fleksibilitas**: Jika peraturan jam berubah di masa depan, cukup update seeder, tidak perlu hardcode

## 📊 Skema Tabel

```sql
CREATE TABLE class_periods (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  day ENUM('senin','selasa','rabu','kamis','jumat') NOT NULL,
  sequence INT NOT NULL COMMENT 'Urutan jam dalam hari (0=pertama)',
  start_time TIME NOT NULL COMMENT 'Waktu mulai',
  end_time TIME NOT NULL COMMENT 'Waktu selesai',
  duration_minutes INT NOT NULL COMMENT 'Durasi dalam menit',
  activity_type ENUM('lesson','break','ceremony','prayer','cleaning','other') DEFAULT 'lesson',
  note VARCHAR(255) NULL COMMENT 'Catatan khusus (mis: Upacara)',
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  UNIQUE KEY unique_day_sequence (day, sequence),
  UNIQUE KEY unique_day_start_time (day, start_time),
  INDEX idx_day (day),
  INDEX idx_activity_type (activity_type)
);
```

### Penjelasan Kolom

| Kolom | Tipe | Contoh | Keterangan |
|-------|------|--------|-----------|
| `id` | bigint | 1 | Primary key auto-increment |
| `day` | enum | 'senin' | Hari kerja (senin s.d. jumat) |
| `sequence` | int | 0,1,2,... | Urutan jam dalam hari (untuk sorting) |
| `start_time` | time | 06:30:00 | Jam mulai periode |
| `end_time` | time | 07:15:00 | Jam selesai periode |
| `duration_minutes` | int | 45, 40 | Durasi dalam menit |
| `activity_type` | enum | 'lesson' | Jenis aktivitas (pelajaran, istirahat, upacara, dll) |
| `note` | varchar | 'Upacara' | Catatan untuk jam khusus |

### Jenis Activity Type

- **`lesson`** — Jam pelajaran reguler (guru mengajar)
- **`break`** — Istirahat (MBG atau siang)
- **`ceremony`** — Upacara bendera (Senin pagi)
- **`prayer`** — Ibadah/Shalat (Jumat)
- **`cleaning`** — Kebersihan/Tadarus (Selasa-Kamis pagi)
- **`other`** — Aktivitas lainnya

---

## 📅 Data Jadwal (Dari Dokumen Sekolah)

### SENIN (13 periode)
```
Seq | Jam Mulai | Jam Selesai | Durasi | Jenis        | Catatan
----|-----------|-------------|--------|--------------|------------------
0   | 06:30     | 07:15       | 45 min | ceremony     | Upacara
1   | 07:15     | 07:55       | 40 min | lesson       | -
2   | 07:55     | 08:35       | 40 min | lesson       | -
3   | 08:35     | 09:15       | 40 min | lesson       | -
4   | 09:15     | 09:55       | 40 min | lesson       | -
5   | 09:55     | 10:25       | 30 min | break        | Istirahat/MBG
6   | 10:25     | 11:05       | 40 min | lesson       | -
7   | 11:05     | 11:45       | 40 min | lesson       | -
8   | 11:45     | 12:20       | 35 min | break        | Istirahat
9   | 12:20     | 13:00       | 40 min | lesson       | -
10  | 13:00     | 13:40       | 40 min | lesson       | -
11  | 13:40     | 14:20       | 40 min | lesson       | -
12  | 14:20     | 15:00       | 40 min | lesson       | -
```
**Total jam pelajaran Senin:** 9 jam (9 × 40 menit)

### SELASA, RABU, KAMIS (14 periode - identik)
```
Seq | Jam Mulai | Jam Selesai | Durasi | Jenis        | Catatan
----|-----------|-------------|--------|--------------|------------------
0   | 06:10     | 06:30       | 20 min | cleaning     | Tadarus & Kebersiahan
1   | 06:30     | 07:10       | 40 min | lesson       | -
2   | 07:10     | 07:50       | 40 min | lesson       | -
3   | 07:50     | 08:30       | 40 min | lesson       | -
4   | 08:30     | 09:10       | 40 min | lesson       | -
5   | 09:10     | 09:50       | 40 min | lesson       | -
6   | 09:50     | 10:20       | 30 min | break        | Istirahat/MBG
7   | 10:20     | 11:00       | 40 min | lesson       | -
8   | 11:00     | 11:40       | 40 min | lesson       | -
9   | 11:40     | 12:30       | 50 min | break        | Istirahat
10  | 12:30     | 13:10       | 40 min | lesson       | -
11  | 13:10     | 13:50       | 40 min | lesson       | -
12  | 13:50     | 14:30       | 40 min | lesson       | -
13  | 14:30     | 15:10       | 40 min | lesson       | -
```
**Total jam pelajaran Sel/Rab/Kam:** 10 jam (10 × 40 menit)

### JUMAT (10 periode)
```
Seq | Jam Mulai | Jam Selesai | Durasi | Jenis        | Catatan
----|-----------|-------------|--------|--------------|------------------
0   | 06:30     | 07:30       | 60 min | other        | Kerohanian/Olahraga/Kebersihan
1   | 07:30     | 08:05       | 35 min | lesson       | -
2   | 08:05     | 08:40       | 35 min | lesson       | -
3   | 08:40     | 09:15       | 35 min | lesson       | -
4   | 09:15     | 09:50       | 35 min | lesson       | -
5   | 09:50     | 10:25       | 35 min | break        | Istirahat/MBG
6   | 10:25     | 10:55       | 30 min | lesson       | -
7   | 10:55     | 11:30       | 35 min | lesson       | -
8   | 11:30     | 12:30       | 60 min | prayer       | Shalat Jum'at / Keputian
9   | 12:30     | 13:10       | 40 min | break        | Istirahat
```
**Total jam pelajaran Jumat:** 5 jam 50 menit

---

## 🚀 Cara Implementasi

### 1. Jalankan Migration

```bash
php artisan migrate
```

Ini akan membuat tabel `class_periods` dengan struktur kolom di atas.

### 2. Jalankan Seeder

```bash
php artisan db:seed --class=ClassPeriodSeeder
```

Atau langsung dengan migrate + seed:
```bash
php artisan migrate --seed
```

Seeder akan mengisi 57 baris data jadwal untuk Senin s.d. Jumat.

### 3. Verifikasi Data

```bash
# Lihat semua jadwal
SELECT * FROM class_periods ORDER BY day, sequence;

# Lihat jadwal Senin
SELECT * FROM class_periods WHERE day = 'senin' ORDER BY sequence;

# Hitung total jam pelajaran per hari
SELECT day, COUNT(*) as total_periods, 
       SUM(duration_minutes) as total_minutes 
FROM class_periods 
WHERE activity_type = 'lesson' 
GROUP BY day;
```

---

## 💻 Cara Menggunakan di Code

### Import Model

```php
use App\Models\ClassPeriod;
```

### Query Contoh

#### 1. Get semua jadwal Senin
```php
$senin = ClassPeriod::byDay('senin')->get();
```

#### 2. Get hanya jam pelajaran (exclude istirahat/upacara)
```php
$lessons = ClassPeriod::byDay('senin')->lessonOnly()->get();
```

#### 3. Get periode yang sedang berjalan hari ini
```php
$current = ClassPeriod::currentPeriod('senin'); // Jika hari ini Senin
if ($current) {
    echo "Periode saat ini: {$current->time_range}";
}
```

#### 4. Get periode berikutnya
```php
$period = ClassPeriod::find(1);
$next = $period->nextPeriod();
```

#### 5. Check apakah sekarang jam sekolah
```php
if (ClassPeriod::isSchoolHours()) {
    echo "Sedang jam sekolah";
}
```

#### 6. Total durasi jam pelajaran per hari
```php
$total = ClassPeriod::totalLessonHoursByDay('senin'); // return 540 (menit)
```

### Attribute Helpers

```php
$period = ClassPeriod::first();

// Format jam dengan helper attribute
echo $period->time_range;        // Output: "06:30 - 07:15"

// Label aktivitas
echo $period->activity_label;    // Output: "Upacara"
```

---

## 🔄 Rollback (Jika Perlu)

Jika ingin menghapus tabel dan data:

```bash
php artisan migrate:rollback --step=1
```

Atau rollback ke migration tertentu:
```bash
php artisan migrate:rollback --target=2026_01_30_create_class_periods_table
```

---

## 📝 Integrasi dengan Rekap Absen

Contoh query untuk **rekap absen per jam pelajaran**:

```php
// Di Controller
use App\Models\ClassPeriod;
use App\Models\StudentAttendance;

$day = 'senin';
$periods = ClassPeriod::byDay($day)->lessonOnly()->get();

$recap = [];
foreach ($periods as $period) {
    $recap[] = [
        'jam_ke' => $period->sequence,
        'waktu' => $period->time_range,
        'hadir' => StudentAttendance::whereBetween('created_at', [
            now()->startOfDay()->setTimeFromTimeString($period->start_time),
            now()->startOfDay()->setTimeFromTimeString($period->end_time),
        ])->where('status', 'hadir')->count(),
        'sakit' => StudentAttendance::whereBetween('created_at', [
            now()->startOfDay()->setTimeFromTimeString($period->start_time),
            now()->startOfDay()->setTimeFromTimeString($period->end_time),
        ])->where('status', 'sakit')->count(),
    ];
}
```

---

## 📋 File yang Dibuat/Diubah

| File | Status | Keterangan |
|------|--------|-----------|
| `database/migrations/2026_01_30_create_class_periods_table.php` | ✅ Baru | Migration tabel |
| `database/seeders/ClassPeriodSeeder.php` | ✅ Baru | Seeder data jadwal |
| `app/Models/ClassPeriod.php` | ✅ Baru | Model dengan utility methods |

---

## ✅ Checklist

- [x] Tabel `class_periods` dirancang
- [x] Migration dibuat dengan constraints & indexes
- [x] Seeder mengisi 57 baris data (Senin-Jumat)
- [x] Model `ClassPeriod` dengan scopes & methods utility
- [x] Dokumentasi lengkap
- [ ] Jalankan: `php artisan migrate --seed`
- [ ] Verifikasi data di database
- [ ] Integrasikan dengan fitur rekap absen

---

**Catatan:** Data jadwal bersifat **statis** dan mengikuti dokumen jadwal sekolah semester 2. Jika ada perubahan jadwal di semester/tahun berikutnya, update seeder dan jalankan ulang migration.
