<?php

namespace App\Imports;

use App\Models\ClassModel;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
    private array $headerErrors = [];
    private array $headerInfo = [];
{
    private int   $imported       = 0;
    private array $failedRows     = [];
    private array $createdClasses = [];

    public function __construct(private bool $autoCreateClasses = false) {}

    public function collection(Collection $rows): void
    {
        $now = now();

        // Kolom template yang diharapkan
        $expectedColumns = [
            'nis', 'nama', 'email', 'tingkat', 'jurusan', 'gender', 'tanggal_lahir', 'no_hp', 'alamat'
        ];

        // Ambil header dari file
        $fileColumns = $rows->first() ? array_keys($rows->first()->toArray()) : [];
        $this->headerInfo = [
            'expected' => $expectedColumns,
            'found' => $fileColumns,
        ];

        // Cek kolom yang kurang, lebih, dan tidak sesuai
        $missing = array_diff($expectedColumns, $fileColumns);
        $extra   = array_diff($fileColumns, $expectedColumns);
        $wrong   = [];
        foreach ($fileColumns as $col) {
            if (!in_array($col, $expectedColumns)) {
                $wrong[] = $col;
            }
        }
        if ($missing || $extra) {
            $this->headerErrors = [
                'missing' => $missing,
                'extra' => $extra,
                'wrong' => $wrong,
            ];
            $this->failedRows[] = [
                'rowNumber' => 0,
                'messages' => [
                    'Kolom pada file tidak sesuai template.',
                    'Kolom yang diharapkan: ' . implode(', ', $expectedColumns),
                    'Kolom yang ditemukan: ' . implode(', ', $fileColumns),
                    $missing ? ('Kolom kurang: ' . implode(', ', $missing)) : null,
                    $extra ? ('Kolom berlebih/tidak dikenal: ' . implode(', ', $extra)) : null,
                ],
            ];
            // Tidak lanjut proses jika header salah
            return;
        }

        $existingNis    = Student::pluck('nis')->flip()->all();
        $existingEmails = User::pluck('email')->flip()->all();

        $classMap = ClassModel::all()->mapWithKeys(
            fn ($c) => [($c->class . '|' . strtoupper($c->major ?? '')) => $c->id]
        )->all();

        $usersToInsert    = [];
        $studentsToInsert = [];

        foreach ($rows as $row) {
            $row = $row->toArray();

            if (empty($row['nis']) || empty($row['nama'])) {
                continue;
            }

            $nis   = trim((string) $row['nis']);
            $nama  = trim($row['nama']);
            $email = trim($row['email'] ?? '');
            if (empty($email)) {
                $email = $nis . '@siswa.prescientia.id';
            }

            if (isset($existingNis[$nis])) {
                $this->failedRows[] = ['nis' => $nis, 'nama' => $nama, 'reason' => "NIS {$nis} sudah terdaftar di sistem."];
                continue;
            }

            if (isset($existingEmails[$email])) {
                $this->failedRows[] = ['nis' => $nis, 'nama' => $nama, 'reason' => "Email {$email} sudah digunakan akun lain."];
                continue;
            }

            $classId = null;
            if (!empty($row['tingkat'])) {
                $tingkat = (int) $row['tingkat'];
                $jurusan = strtoupper(trim($row['jurusan'] ?? ''));
                $key     = $tingkat . '|' . $jurusan;

                if (isset($classMap[$key])) {
                    $classId = $classMap[$key];
                } elseif ($this->autoCreateClasses) {
                    $cls = ClassModel::create(['class' => $tingkat, 'major' => $jurusan ?: null]);
                    $classMap[$key] = $cls->id;
                    $classId        = $cls->id;
                    $label          = "{$tingkat}" . ($jurusan ? " - {$jurusan}" : '');
                    $this->createdClasses[$label] = $cls->id;
                } else {
                    $label = "{$tingkat}" . ($jurusan ? " - {$jurusan}" : '');
                    $this->failedRows[] = ['nis' => $nis, 'nama' => $nama, 'reason' => "Kelas {$label} tidak ditemukan. Pastikan data kelas sudah tersedia di menu Data Kelas."];
                    continue;
                }
            }

            $dob = $now->format('Y-m-d');
            if (!empty($row['tanggal_lahir'])) {
                try { $dob = Carbon::parse($row['tanggal_lahir'])->format('Y-m-d'); } catch (\Exception) {}
            }

            $nowStr = $now->toDateTimeString();

            $usersToInsert[] = [
                'email'      => $email,
                'password'   => Hash::make($nis, ['rounds' => 10]),
                'role'       => 'student',
                'is_active'  => true,
                'created_at' => $nowStr,
                'updated_at' => $nowStr,
            ];

            $studentsToInsert[] = [
                '_email'        => $email,
                'nis'           => $nis,
                'name'          => $nama,
                'gender'        => strtoupper(trim($row['gender'] ?? 'L')) === 'P' ? 'P' : 'L',
                'date_of_birth' => $dob,
                'phone_number'  => $row['no_hp']  ?? null,
                'address'       => $row['alamat'] ?? null,
                'class_id'      => $classId,
                'created_at'    => $nowStr,
                'updated_at'    => $nowStr,
            ];

            $existingNis[$nis]      = true;
            $existingEmails[$email] = true;
        }

        if (empty($usersToInsert)) {
            return;
        }

        DB::transaction(function () use ($usersToInsert, $studentsToInsert) {
            foreach (array_chunk($usersToInsert, 200) as $chunk) {
                DB::table('users')->insert($chunk);
            }

            $emails        = array_column($usersToInsert, 'email');
            $userIdByEmail = DB::table('users')->whereIn('email', $emails)->pluck('id', 'email')->all();

            $studentsClean = array_map(function ($s) use ($userIdByEmail) {
                $s['user_id'] = $userIdByEmail[$s['_email']] ?? null;
                unset($s['_email']);
                return $s;
            }, $studentsToInsert);

            $studentsClean = array_values(array_filter($studentsClean, fn ($s) => $s['user_id'] !== null));

            foreach (array_chunk($studentsClean, 200) as $chunk) {
                DB::table('students')->insert($chunk);
            }

            $this->imported = count($studentsClean);
        });
    }

    public function getImportedCount(): int    { return $this->imported; }
    public function getFailedRows(): array     { return $this->failedRows; }
    public function getCreatedClasses(): array { return $this->createdClasses; }
    public function getHeaderErrors(): array   { return $this->headerErrors; }
    public function getHeaderInfo(): array     { return $this->headerInfo; }
}