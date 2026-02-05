# Teacher Schedule Import Template

## Format Excel yang Dibutuhkan

File Excel harus memiliki kolom-kolom berikut dengan urutan alphabet (A-G):

| A: Email        | B: Teacher Name | C: Class  | D: Subject | E: Semester | F: Day  | G: Period Sequence |
|-----------------|-----------------|-----------|------------|-------------|---------|-------------------|
| guru1@mail.com  | Budi            | X RPL 1   | Math       | 1           | senin   | 1                 |
| guru1@mail.com  | Budi            | X RPL 1   | Math       | 1           | senin   | 2                 |
| guru1@mail.com  | Budi            | X RPL 1   | Math       | 1           | rabu    | 3                 |
| guru2@mail.com  | Ani             | XI IPA 1  | Bio        | 1           | jumat   | 1                 |

**PENTING:** Header row harus ada di baris pertama. Data mulai dari baris ke-2.

## Penjelasan Kolom (Urutan Alphabet Excel)

1. **Column A - Email**: Email guru (harus sudah terdaftar di database)
2. **Column B - Teacher Name**: Nama guru (untuk referensi saja, tidak wajib match persis)
3. **Column C - Class**: Nama kelas (format: "X RPL 1", "XI IPA 1", "XII MIPA 2")
4. **Column D - Subject**: Nama mata pelajaran (harus sudah terdaftar di database)
5. **Column E - Semester**: 1 atau 2
6. **Column F - Day**: Hari (senin, selasa, rabu, kamis, jumat) - **huruf kecil**
7. **Column G - Period Sequence**: Urutan jam pelajaran (1, 2, 3, dst)

## Contoh Data Valid

```
Email              | Teacher Name | Class     | Subject    | Semester | Day    | Period Sequence
-------------------|--------------|-----------|------------|----------|--------|----------------
pak.budi@smk.ac.id | Pak Budi     | X RPL 1   | Matematika | 1        | senin  | 1
pak.budi@smk.ac.id | Pak Budi     | X RPL 1   | Matematika | 1        | senin  | 2
pak.budi@smk.ac.id | Pak Budi     | X RPL 1   | Matematika | 1        | selasa | 1
bu.ani@smk.ac.id   | Bu Ani       | XI IPA 1  | Biologi    | 1        | rabu   | 3
```

## Catatan Penting

✅ **Pastikan data master sudah ada:**
- Guru dengan email tersebut harus sudah terdaftar
- Kelas dengan nama tersebut harus sudah ada
- Mata pelajaran harus sudah terdaftar
- Jam pelajaran (class_period) untuk hari + urutan tersebut harus sudah ada

✅ **Format hari:**
- Harus huruf kecil: `senin`, `selasa`, `rabu`, `kamis`, `jumat`
- Bukan: `Senin`, `SENIN`, atau `Monday`

✅ **Duplikat:**
- Jika kombinasi (teacher + class + subject + period + day + semester) sudah ada, akan otomatis di-skip
- Tidak akan error, hanya masuk ke `skipped count`

✅ **Hasil Import:**
Sistem akan menampilkan summary:
- **Inserted**: berapa baris berhasil ditambahkan
- **Skipped**: berapa baris duplikat (sudah ada)
- **Failed**: berapa baris gagal (data master tidak ditemukan)

## Cara Membuat File Excel

### Option 1: Manual di Excel/Google Sheets
1. Buka Excel atau Google Sheets
2. Buat header di baris 1 sesuai urutan A-G di atas
3. Isi data mulai baris 2
4. Save as `.xlsx` atau `.xls`

### Option 2: Copy Template
Buat file Excel dengan struktur:

**Baris 1 (Header):**
```
Email | Teacher Name | Class | Subject | Semester | Day | Period Sequence
```

**Baris 2 dst (Data):**
Isi dengan data guru sesuai format di atas.

## Tips

- Gunakan Excel formula untuk generate data berulang jika guru mengajar di jam berturut-turut
- Simpan file dengan nama yang jelas, misal: `jadwal_guru_semester1_2026.xlsx`
- Test import dengan beberapa baris dulu sebelum import full data
- Jika ada error, cek pesan error untuk tahu baris mana yang bermasalah

## Troubleshooting

**Error: "Guru dengan email xxx tidak ditemukan"**
→ Pastikan guru dengan email tersebut sudah terdaftar di database

**Error: "Kelas xxx tidak ditemukan"**
→ Pastikan nama kelas match persis dengan database (termasuk spasi)

**Error: "Jam pelajaran X pada hari xxx tidak ditemukan"**
→ Pastikan class_period untuk hari dan urutan tersebut sudah ada (cek di menu Class Periods)

**Banyak yang skipped**
→ Normal jika re-import file yang sama. Artinya data sudah ada sebelumnya.
