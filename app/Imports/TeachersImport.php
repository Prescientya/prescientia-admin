<?php

namespace App\Imports;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Throwable;

class TeachersImport implements ToModel, WithHeadingRow, WithChunkReading, SkipsOnError
{
    use SkipsErrors;

    public function chunkSize(): int
    {
        return 200;
    }

    private int   $imported   = 0;
    private array $failedRows = [];

    /**
     * Column mapping (heading row):
     * nip | nama | email | gender | tanggal_lahir | no_hp | alamat | mapel
     *
     * - gender       : L / P
     * - tanggal_lahir: Y-m-d atau format yang dikenali Carbon
     * - mapel        : nama mata pelajaran, pisah koma (cth: "Matematika,Fisika Dasar")
     * - password     : otomatis = NIP
     */
    public function model(array $row): ?Teacher
    {
        // Skip empty rows
        if (empty($row['nip']) || empty($row['nama'])) {
            return null;
        }

        $nip   = trim((string) $row['nip']);
        $nama  = trim($row['nama']);
        $email = trim($row['email'] ?? '');

        // Default email from NIP if not provided
        if (empty($email)) {
            $email = $nip . '@guru.prescientia.id';
        }

        // Reject duplicates — track as failures instead of silently skipping
        if (Teacher::where('nip', $nip)->exists()) {
            $this->failedRows[] = [
                'nip'    => $nip,
                'nama'   => $nama,
                'reason' => "NIP {$nip} sudah terdaftar di sistem.",
            ];
            return null;
        }
        if (User::where('email', $email)->exists()) {
            $this->failedRows[] = [
                'nip'    => $nip,
                'nama'   => $nama,
                'reason' => "Email {$email} sudah digunakan akun lain.",
            ];
            return null;
        }

        // Parse date of birth
        $dob = now()->format('Y-m-d');
        if (!empty($row['tanggal_lahir'])) {
            try {
                $dob = Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
            } catch (\Exception) {}
        }

        // Resolve mata pelajaran → Subject IDs (only from existing subjects; do not auto-create)
        $subjectIds   = [];
        if (!empty($row['mapel'])) {
            $mapelNames   = array_map('trim', explode(',', (string) $row['mapel']));
            $notFoundMapel = [];
            foreach ($mapelNames as $name) {
                if (empty($name)) continue;
                $subject = Subject::whereRaw('LOWER(name) = LOWER(?)', [$name])->first();
                if ($subject) {
                    $subjectIds[] = $subject->id;
                } else {
                    $notFoundMapel[] = $name;
                }
            }
            if (!empty($notFoundMapel)) {
                $list = collect($notFoundMapel)->map(fn($n) => '"' . $n . '"')->implode(', ');
                $this->failedRows[] = [
                    'nip'    => $nip,
                    'nama'   => $nama,
                    'reason' => "Mapel {$list} tidak ditemukan di sistem sekolah ini.",
                ];
                return null;
            }
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'email'     => $email,
                'password'  => Hash::make($nip), // default password = NIP
                'role'      => 'teacher',
                'is_active' => true,
            ]);

            $teacher = Teacher::create([
                'user_id'       => $user->id,
                'nip'           => $nip,
                'name'          => $nama,
                'gender'        => strtoupper(trim($row['gender'] ?? 'L')) === 'P' ? 'P' : 'L',
                'date_of_birth' => $dob,
                'phone_number'  => $row['no_hp'] ?? null,
                'address'       => $row['alamat'] ?? null,
            ]);

            if (!empty($subjectIds)) {
                $teacher->subjects()->sync($subjectIds);
            }

            DB::commit();
            $this->imported++;
            return null;
        } catch (\Exception $e) {
            DB::rollback();
            $this->failedRows[] = [
                'nip'    => $nip,
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
