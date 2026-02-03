# CRUD & Import Jadwal Pelajaran

**Update: 30 Januari 2026**

## 📋 Ringkasan

Fitur CRUD untuk tabel `class_periods` dengan strategi **Import-Based** yang aman:

- **Class Periods**: Import via Excel/PDF saja (tidak hardcode), edit note, delete bulk, tidak boleh manual add/edit jam

---

## 🔒 Strategi CRUD Class Periods

Mengapa import-based? Karena mengubah jam pelajaran **sangat risikoĸo** (bisa shift semua jadwal), jadi:

### ✅ Boleh Dilakukan
| Operasi | Deskripsi | Validasi |
|---------|-----------|----------|
| **Import** | Upload file Excel/CSV dengan jadwal baru | File harus sesuai format, cegah import jika sudah ada data |
| **Edit Note** | Update keterangan per jam (mis: "Upacara", "Istirahat MBG") | Hanya field `note` saja, tidak boleh ubah jam |
| **Delete All** | Hapus SEMUA jadwal untuk reset, baru import yang baru | Butuh konfirmasi double-click, hanya admin |
| **View** | Lihat list jadwal per hari, grouped by day | Read-only |

### ❌ TIDAK Boleh Dilakukan
| Operasi | Alasan |
|---------|--------|
| **Add Manual** | Tidak ada form tambah baris — harus via import |
| **Edit Jam** | Jika edit jam mulai/selesai, semua jadwal berikutnya shift → berantakan |
| **Delete Per Baris** | Tidak ada delete button per baris — hanya delete ALL kemudian re-import |
| **Edit Duration** | Durasi otomatis dihitung dari start/end time |

---

## 📥 Import Class Periods

### Format File yang Diterima
File Excel/CSV dengan header dan kolom berikut:

```
| HARI    | JAM KE | WAKTU           | Keterangan      |
|---------|--------|-----------------|-----------------|
| senin   | 0      | 06:30 s.d. 07:15| Upacara         |
| senin   | 1      | 07:15 s.d. 07:55| -               |
| selasa  | 0      | 06:10 s.d. 06:30| Tadarus         |
```

**Kolom & Format:**
- **HARI**: `senin`, `selasa`, `rabu`, `kamis`, `jumat` (lowercase)
- **JAM KE**: Integer (0, 1, 2, 3, ...)
- **WAKTU**: Format `HH:MM s.d. HH:MM` atau `HH:MM - HH:MM`
- **Keterangan**: Bisa kosong atau custom text

**File Format:**
- `.xlsx` (Excel 2007+)
- `.csv` (Comma Separated Values)
- `.xls` (Excel 97-2003)
- Max file size: 5 MB

### Endpoint Import

```
POST /admin/class-periods/import
Content-Type: multipart/form-data

{
  "file": <FILE_OBJECT>
}
```

**Response Success (201)**
```json
{
  "success": true,
  "message": "Import berhasil! 57 periode jadwal telah ditambahkan.",
  "count": 57
}
```

**Response Error — Data Sudah Ada (422)**
```json
{
  "success": false,
  "message": "Data jadwal pelajaran sudah ada. Hapus data terlebih dahulu dengan tombol \"Delete All\" sebelum import yang baru.",
  "action": "delete_first"
}
```

**Response Error — Format Invalid (422)**
```json
{
  "success": false,
  "message": "Data tidak valid",
  "errors": [
    "Baris 5: Hari 'libur' tidak valid. Gunakan: senin, selasa, rabu, kamis, jumat",
    "Baris 8: Format waktu '06:30-07:15' tidak valid. Gunakan 'HH:MM s.d. HH:MM'"
  ]
}
```

### Logic Import Service

```php
// app/Services/ClassPeriodImportService.php

public function parseFile(string $filePath): array {
    // Parse Excel → array structured
}

public function validate(array $data): array {
    // Check: duplikat sequence, overlap waktu, format valid
}

public function import(array $data): int {
    // Delete semua data lama → Insert data baru
    // return count rows inserted
}
```

---

## 🔧 CRUD Class Periods

### GET: List Jadwal
```
GET /admin/class-periods

Query params (optional):
  - day: "senin" (filter by hari)

Response:
{
  "success": true,
  "data": {
    "senin": [
      {
        "id": 1,
        "day": "senin",
        "sequence": 0,
        "start_time": "06:30:00",
        "end_time": "07:15:00",
        "duration_minutes": 45,
        "activity_type": "ceremony",
        "note": "Upacara",
        "time_range": "06:30 - 07:15",  // Helper
        "activity_label": "Upacara"      // Helper
      }
    ],
    "selasa": [...]
  },
  "has_data": true
}
```

### GET: Detail Periode
```
GET /admin/class-periods/1

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "day": "senin",
    "sequence": 0,
    "time_range": "06:30 - 07:15",
    ...
  }
}
```

### PATCH: Edit Note Per Hari
```
PATCH /admin/class-periods/update-note
Content-Type: application/json

{
  "day": "senin",
  "sequence": 0,
  "note": "Upacara Bendera Hari Senin"
}

Response:
{
  "success": true,
  "message": "Catatan periode berhasil diperbarui",
  "data": { ... }
}
```

### DELETE: Hapus Semua Jadwal
```
DELETE /admin/class-periods/delete-all
Content-Type: application/json

{
  "confirm": "yes"  // Harus "yes" untuk double-check
}

Response:
{
  "success": true,
  "message": "Semua 57 periode jadwal telah dihapus. Silakan import jadwal baru.",
  "count": 57
}
```

### GET: Check Status
```
GET /admin/class-periods/check-status

Response:
{
  "success": true,
  "has_data": true,
  "total": 57
}
```

---

## 📅 CRUD Schedule Events (Acara Dadakan)

Untuk acara dadakan seperti kegiatan sosial, upacara dadakan, libur dadakan, dll.

### GET: List Events
```
GET /admin/schedule-events

Query params (optional):
  - start_date: "2026-02-01"
  - end_date: "2026-02-28"
  - day: "senin"

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "date": "2026-02-04",
      "day": "selasa",
      "start_time": "10:00:00",
      "end_time": "12:00:00",
      "name": "Kegiatan Sosial",
      "description": "Siswa mengunjungi panti asuhan",
      "created_by": 1,
      "time_range": "10:00 - 12:00",
      "creator": { "id": 1, "name": "Admin" }
    }
  ]
}
```

### GET: Detail Event + Affected Periods
```
GET /admin/schedule-events/1

Response:
{
  "success": true,
  "data": { ... },
  "affected_periods": [
    {
      "id": 7,
      "day": "selasa",
      "sequence": 6,
      "start_time": "10:20:00",
      "end_time": "11:00:00",
      "activity_type": "lesson",
      "time_range": "10:20 - 11:00"
    },
    {
      "id": 8,
      "day": "selasa",
      "sequence": 7,
      "start_time": "11:00:00",
      "end_time": "11:40:00",
      "activity_type": "lesson",
      "time_range": "11:00 - 11:40"
    }
  ]
}
```

### POST: Create Event
```
POST /admin/schedule-events
Content-Type: application/json

{
  "date": "2026-02-04",
  "day": "selasa",
  "start_time": "10:00",
  "end_time": "12:00",
  "name": "Kegiatan Sosial",
  "description": "Siswa mengunjungi panti asuhan"
}

Validasi:
  - date: required, valid date
  - day: required, in (senin, selasa, rabu, kamis, jumat)
  - start_time: required, format H:i
  - end_time: required, format H:i, harus > start_time
  - name: required, max 255 chars
  - description: optional, max 500 chars
  - Cek overlap dengan event lain di tanggal sama

Response (201):
{
  "success": true,
  "message": "Event berhasil dibuat",
  "data": { ... }
}
```

### PATCH: Update Event
```
PATCH /admin/schedule-events/1
Content-Type: application/json

{
  "start_time": "10:00",
  "end_time": "13:00",
  "name": "Kegiatan Sosial + Makan Bersama"
}

Response:
{
  "success": true,
  "message": "Event berhasil diperbarui",
  "data": { ... }
}
```

### DELETE: Hapus Event
```
DELETE /admin/schedule-events/1

Response:
{
  "success": true,
  "message": "Event 'Kegiatan Sosial' berhasil dihapus"
}
```

### GET: Check Events untuk Tanggal Tertentu
```
GET /admin/schedule-events/check-date?date=2026-02-04

Response:
{
  "success": true,
  "data": [ ... ],
  "has_events": true
}
```

---

## 🛠️ Fitur Utility & Helpers

### ClassPeriod Methods

```php
// Get jadwal Senin
$senin = ClassPeriod::byDay('senin')->get();

// Hanya jam pelajaran
$lessons = ClassPeriod::byDay('senin')->lessonOnly()->get();

// Total durasi jam pelajaran
$minutes = ClassPeriod::totalLessonHoursByDay('senin');  // 540 menit = 9 jam

// Periode saat ini (hari ini)
$now = ClassPeriod::currentPeriod('senin');
echo $now->time_range;  // "06:30 - 07:15"

// Period berikutnya
$next = $period->nextPeriod();

// Check jam sekolah sekarang
if (ClassPeriod::isSchoolHours()) {
    echo "Sedang jam sekolah";
}
```

---

## 🔐 Authorization & Security

Semua endpoints **require auth + admin role**:

```php
$this->middleware('auth');
$this->middleware('admin');
```

### Validasi Keamanan Import

✅ **File validation:**
- Hanya `.xlsx`, `.csv`, `.xls`
- Max 5 MB
- Harus valid format Excel

✅ **Data validation:**
- Hari harus valid (senin-jumat)
- Format waktu harus valid
- Tidak boleh overlap
- Tidak boleh duplikat sequence

✅ **Business logic:**
- Cegah import jika sudah ada data
- Delete All butuh konfirmasi `confirm=yes`
- Tidak ada delete per baris

---

## 🚀 Setup & Migration

```bash
# 1. Buat migration tables
php artisan migrate

# 2. (Optional) Seed data awal dengan seeder
php artisan db:seed --class=ClassPeriodSeeder

# 3. Routes sudah registered di routes/web.php
```

---

## 📊 Integrasi dengan Rekap Absen

Contoh query untuk **menggunakan class periods di rekap absen**:

```php
// Di Controller
use App\Models\ClassPeriod;
use App\Models\StudentAttendance;

$day = 'senin';
$date = now(); // Tanggal tertentu

// 1. Get jadwal pelajaran hari ini
$periods = ClassPeriod::byDay($day)->lessonOnly()->get();

// 2. Generate rekap per jam
$recap = [];
foreach ($periods as $period) {
    // Generate attendance recap for this period
    });

    if ($eventOnThisPeriod) {
        $recap[] = [
            'jam_ke' => $period->sequence,
            'waktu' => $period->time_range,
            'status' => 'acara',
            'acara' => $eventOnThisPeriod->name,
            'hadir' => 0,
            'sakit' => 0,
        ];
    } else {
        // Hitung absen untuk periode ini
        $hadir = StudentAttendance::whereBetween('created_at', [
            $date->copy()->setTimeFromTimeString($period->start_time),
            $date->copy()->setTimeFromTimeString($period->end_time),
        ])->where('status', 'hadir')->count();

        $recap[] = [
            'jam_ke' => $period->sequence,
            'waktu' => $period->time_range,
            'status' => 'normal',
            'hadir' => $hadir,
            'sakit' => $sakit,
        ];
    }
}

return response()->json($recap);
```

---

## 📁 File yang Dibuat

| File | Status | Keterangan |
|------|--------|-----------|
| `app/Services/ClassPeriodImportService.php` | ✅ | Service import Excel |
| `app/Http/Controllers/ClassPeriodController.php` | ✅ | Controller CRUD class periods |
| `routes/web.php` | ✅ | Routes (updated) |

---

## 🎯 Checklist Implementasi

- [x] Service `ClassPeriodImportService` untuk parse Excel
- [x] Controller `ClassPeriodController` (import, PATCH note, DELETE all)
- [x] Routes registered
- [ ] Jalankan: `php artisan migrate`
- [ ] Test import Excel file
- [ ] Integrasikan dengan fitur rekap absen

---

## 💡 Tips & Best Practices

1. **Export Template** — Sediakan template Excel untuk admin download, kemudian isi dan upload kembali

2. **Validation Messages** — Pastikan error import jelas dan actionable ("Duplikat jam ke-5 di hari Senin")

3. **Affected Periods** — Saat create/update event, tampilkan jam-jam pelajaran yang tergantung untuk notifikasi

4. **Audit Log** — Catat siapa yang import/delete jadwal untuk audit trail

5. **Timezone** — Pastikan semua waktu konsisten (gunakan timezone yang sama)

---

**Catatan:** Strategi import-based ini **aman dari accident delete/edit** yang akan berantakan jadwal. Admin hanya bisa re-import jadwal baru jika ada perubahan dari sekolah.
