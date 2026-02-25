<?php

namespace App\Imports;

use App\Models\ClassModel;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Throwable;

class StudentsImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    private int   $imported   = 0;
    private array $failedRows = [];

    /**
     * Column mapping (heading row):
     * nis | nama | email | gender | tanggal_lahir | no_hp | alamat | tingkat | jurusan
     *
     * - gender  : L / P
     * - tingkat : 10 / 11 / 12
     * - jurusan : harus sesuai data kelas yang sudah ada di tabel classes
     * - password: otomatis = NIS
     */
    public function model(array $row): ?Student
    {
        // Skip empty rows
        if (empty($row['nis']) || empty($row['nama'])) {
            return null;
        }

        $nis   = trim((string) $row['nis']);
        $nama  = trim($row['nama']);
        $email = trim($row['email'] ?? '');

        // Default email from NIS if not provided
        if (empty($email)) {
            $email = $nis . '@siswa.prescientia.id';
        }

        // Reject duplicates — track as failures instead of silently skipping
        if (Student::where('nis', $nis)->exists()) {
            $this->failedRows[] = [
                'nis'    => $nis,
                'nama'   => $nama,
                'reason' => "NIS {$nis} sudah terdaftar di sistem.",
            ];
            return null;
        }
        if (User::where('email', $email)->exists()) {
            $this->failedRows[] = [
                'nis'    => $nis,
                'nama'   => $nama,
                'reason' => "Email {$email} sudah digunakan akun lain.",
            ];
            return null;
        }

        // Validate class — must already exist, NO auto-create
        $classId = null;
        if (!empty($row['tingkat'])) {
            $tingkat = (int) $row['tingkat'];
            $jurusan = strtoupper(trim($row['jurusan'] ?? ''));

            $class = ClassModel::where('class', $tingkat)
                               ->where('major', $jurusan ?: null)
                               ->first();

            if (!$class) {
                $this->failedRows[] = [
                    'nis'    => $nis,
                    'nama'   => $nama,
                    'reason' => "Kelas {$tingkat}" . ($jurusan ? " - {$jurusan}" : '') . " tidak ditemukan. Pastikan data kelas sudah tersedia di menu Data Kelas.",
                ];
                return null;
            }

            $classId = $class->id;
        }

        // Parse date
        $dob = now()->format('Y-m-d');
        if (!empty($row['tanggal_lahir'])) {
            try {
                $dob = Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
            } catch (\Exception) {}
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'email'     => $email,
                'password'  => Hash::make($nis), // password = NIS
                'role'      => 'student',
                'is_active' => true,
            ]);

            Student::create([
                'user_id'       => $user->id,
                'nis'           => $nis,
                'name'          => $nama,
                'gender'        => strtoupper(trim($row['gender'] ?? 'L')) === 'P' ? 'P' : 'L',
                'date_of_birth' => $dob,
                'phone_number'  => $row['no_hp'] ?? null,
                'address'       => $row['alamat'] ?? null,
                'class_id'      => $classId,
            ]);

            DB::commit();
            $this->imported++;
            return null;
        } catch (\Exception $e) {
            DB::rollback();
            $this->failedRows[] = [
                'nis'    => $nis,
                'nama'   => $nama,
                'reason' => 'Gagal disimpan: ' . $e->getMessage(),
            ];
            return null;
        }
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getFailedRows(): array
    {
        return $this->failedRows;
    }

    public function onError(Throwable $e): void
    {
        // handled above
    }
}
