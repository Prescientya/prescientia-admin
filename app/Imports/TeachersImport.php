<?php

namespace App\Imports;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Bulk teacher importer  ToCollection strategy.
 *
 * Loads all existing NIPs, emails, and subjects in 3 queries upfront,
 * then validates every row in PHP, bulk-inserts users + teachers,
 * and inserts the teacher_subject pivot rows  all inside one transaction.
 *
 * Column mapping (heading row):
 *   nip | nama | email | gender | tanggal_lahir | no_hp | alamat | mapel
 *
 * - gender       : L / P
 * - tanggal_lahir: Y-m-d or any format recognised by Carbon
 * - mapel        : comma-separated subject names (e.g. "Matematika,Fisika Dasar")
 * - password     : auto-set to NIP (bcrypt rounds=10)
 */
class TeachersImport implements ToCollection, WithHeadingRow
{
    private int   $imported   = 0;
    private array $failedRows = [];

    /*  MAIN  */

    public function collection(Collection $rows): void
    {
        $this->importSheet($rows);
    }

    public function importSheet(Collection $rows, ?string $sheetName = null): void
    {
        $sheetLabel = $sheetName ? "[{$sheetName}] " : '';

        //  1. Preload lookup sets
        $existingNips   = DB::table('teachers')->pluck('nip')->flip()->toArray();
        $existingEmails = DB::table('users')->pluck('email')->flip()->toArray();

        // Build subject map:  lowercase(name) => id
        $subjectMap = Subject::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();

        //  2. Validate & bucket rows
        $toInsertUsers    = [];
        $teacherDataByNip = [];
        $subjectsByNip    = [];
        $seenNips         = [];
        $now              = now()->toDateTimeString();

        foreach ($rows as $row) {
            $row = $row->toArray();

            if (empty($row['nip']) || empty($row['nama'])) {
                continue;
            }

            $nip   = trim((string) $row['nip']);
            $nama  = trim($row['nama']);
            $email = trim($row['email'] ?? '');

            if (empty($email)) {
                $email = $nip . '@guru.prescientia.id';
            }

            // Duplicate NIP (DB or within this sheet)
            if (isset($existingNips[$nip]) || isset($seenNips[$nip])) {
                $this->failedRows[] = [
                    'sheet'  => $sheetName,
                    'nip'    => $nip,
                    'nama'   => $nama,
                    'reason' => $sheetLabel . "NIP {$nip} sudah terdaftar di sistem.",
                ];
                continue;
            }

            // Duplicate email
            if (isset($existingEmails[$email])) {
                $this->failedRows[] = [
                    'sheet'  => $sheetName,
                    'nip'    => $nip,
                    'nama'   => $nama,
                    'reason' => $sheetLabel . "Email {$email} sudah digunakan akun lain.",
                ];
                continue;
            }

            // Parse date of birth
            $dob = now()->format('Y-m-d');
            if (!empty($row['tanggal_lahir'])) {
                try {
                    $dob = Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
                } catch (\Exception) {
                }
            }

            // Resolve mapel → subject IDs
            $subjectIds    = [];
            $notFoundMapel = [];
            if (!empty($row['mapel'])) {
                $mapelNames = array_filter(array_map('trim', explode(',', (string) $row['mapel'])));
                foreach ($mapelNames as $mName) {
                    $key = strtolower($mName);
                    if (isset($subjectMap[$key])) {
                        $subjectIds[] = $subjectMap[$key];
                    } else {
                        $notFoundMapel[] = $mName;
                    }
                }
            }

            if (!empty($notFoundMapel)) {
                $list = collect($notFoundMapel)->map(fn($n) => '"' . $n . '"')->implode(', ');
                $this->failedRows[] = [
                    'sheet'  => $sheetName,
                    'nip'    => $nip,
                    'nama'   => $nama,
                    'reason' => $sheetLabel . "Mapel {$list} tidak ditemukan di sistem sekolah ini.",
                ];
                continue;
            }

            // Mark as seen
            $seenNips[$nip]         = true;
            $existingEmails[$email] = true;

            $toInsertUsers[] = [
                'email'      => $email,
                'password'   => Hash::make($nip), // rounds dari config (BCRYPT_ROUNDS), bukan hardcoded
                'role'       => 'teacher',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $teacherDataByNip[$nip] = [
                'nip'           => $nip,
                'name'          => $nama,
                'gender'        => strtoupper(trim($row['gender'] ?? 'L')) === 'P' ? 'P' : 'L',
                'date_of_birth' => $dob,
                'phone_number'  => $row['no_hp'] ?? null,
                'address'       => $row['alamat'] ?? null,
                'email'         => $email,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            $subjectsByNip[$nip] = array_unique($subjectIds);
        }

        if (empty($toInsertUsers)) {
            return;
        }

        //  3. Bulk insert inside a transaction
        DB::transaction(function () use ($toInsertUsers, $teacherDataByNip, $subjectsByNip) {

            foreach (array_chunk($toInsertUsers, 200) as $chunk) {
                DB::table('users')->insert($chunk);
            }

            $emails        = array_column($toInsertUsers, 'email');
            $userIdByEmail = DB::table('users')
                ->whereIn('email', $emails)
                ->pluck('id', 'email')
                ->toArray();

            $toInsertTeachers = [];
            foreach ($teacherDataByNip as $nip => $data) {
                $userId = $userIdByEmail[$data['email']] ?? null;
                if (!$userId) {
                    continue;
                }
                $toInsertTeachers[] = [
                    'user_id'       => $userId,
                    'nip'           => $nip,
                    'name'          => $data['name'],
                    'gender'        => $data['gender'],
                    'date_of_birth' => $data['date_of_birth'],
                    'phone_number'  => $data['phone_number'],
                    'address'       => $data['address'],
                    'created_at'    => $data['created_at'],
                    'updated_at'    => $data['updated_at'],
                ];
            }

            foreach (array_chunk($toInsertTeachers, 200) as $chunk) {
                DB::table('teachers')->insert($chunk);
            }

            $insertedNips   = array_column($toInsertTeachers, 'nip');
            $teacherIdByNip = DB::table('teachers')
                ->whereIn('nip', $insertedNips)
                ->pluck('id', 'nip')
                ->toArray();

            $pivotRows = [];
            foreach ($subjectsByNip as $nip => $subjectIds) {
                $teacherId = $teacherIdByNip[$nip] ?? null;
                if (!$teacherId || empty($subjectIds)) {
                    continue;
                }
                foreach ($subjectIds as $subjectId) {
                    $pivotRows[] = [
                        'teacher_id' => $teacherId,
                        'subject_id' => $subjectId,
                    ];
                }
            }

            if (!empty($pivotRows)) {
                foreach (array_chunk($pivotRows, 500) as $chunk) {
                    DB::table('teacher_subject')->insert($chunk);
                }
            }

            $this->imported += count($toInsertTeachers);
        });
    }

    /*  ACCESSORS  */

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getFailedRows(): array
    {
        return $this->failedRows;
    }
}