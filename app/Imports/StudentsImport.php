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

    private int $imported = 0;

    /**
     * Column mapping (heading row):
     * nis | nama | email | password | gender | tanggal_lahir | no_hp | alamat | tingkat | jurusan
     *
     * - gender  : L / P
     * - tingkat : 10 / 11 / 12
     * - jurusan : RPL / TKJ / MM / etc.  (boleh kosong)
     * - password: jika kosong, default = nis
     */
    public function model(array $row): ?Student
    {
        // Skip empty rows
        if (empty($row['nis']) || empty($row['nama'])) {
            return null;
        }

        $nis   = trim((string) $row['nis']);
        $email = trim($row['email'] ?? '');

        // Default email from NIS if not provided
        if (empty($email)) {
            $email = $nis . '@siswa.prescientia.id';
        }

        // Skip duplicates
        if (Student::where('nis', $nis)->exists()) return null;
        if (User::where('email', $email)->exists())  return null;

        // Find / create class
        $classId = null;
        if (!empty($row['tingkat'])) {
            $tingkat = (int) $row['tingkat'];
            $jurusan = strtoupper(trim($row['jurusan'] ?? ''));
            $class   = ClassModel::firstOrCreate(
                ['class' => $tingkat, 'major' => $jurusan ?: null],
            );
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
                'password'  => Hash::make($row['password'] ?? $nis),
                'is_active' => true,
            ]);

            $student = Student::create([
                'user_id'       => $user->id,
                'nis'           => $nis,
                'name'          => trim($row['nama']),
                'gender'        => strtoupper(trim($row['gender'] ?? 'L')) === 'P' ? 'P' : 'L',
                'date_of_birth' => $dob,
                'phone_number'  => $row['no_hp'] ?? null,
                'address'       => $row['alamat'] ?? null,
                'class_id'      => $classId,
            ]);

            DB::commit();
            $this->imported++;
            return null; // return null karena sudah create manual
        } catch (\Exception $e) {
            DB::rollback();
            return null;
        }
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function onError(Throwable $e): void
    {
        // Log or skip errors silently
    }
}
