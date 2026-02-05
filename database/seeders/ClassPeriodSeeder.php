<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Data jadwal berdasarkan dokumen jadwal sekolah semester 2.
     * Durasi standar: 40 menit per jam pelajaran.
     */
    public function run(): void
    {

        // Jadwal SENIN
        $senin = [
            ['seq' => 0, 'start' => '06:30', 'end' => '07:15', 'type' => 'ceremony', 'note' => 'Upacara'],
            ['seq' => 1, 'start' => '07:15', 'end' => '07:55', 'type' => 'lesson', 'note' => null],
            ['seq' => 2, 'start' => '07:55', 'end' => '08:35', 'type' => 'lesson', 'note' => null],
            ['seq' => 3, 'start' => '08:35', 'end' => '09:15', 'type' => 'lesson', 'note' => null],
            ['seq' => 4, 'start' => '09:15', 'end' => '09:55', 'type' => 'lesson', 'note' => null],
            ['seq' => 5, 'start' => '09:55', 'end' => '10:25', 'type' => 'break', 'note' => 'Istirahat/MBG'],
            ['seq' => 6, 'start' => '10:25', 'end' => '11:05', 'type' => 'lesson', 'note' => null],
            ['seq' => 7, 'start' => '11:05', 'end' => '11:45', 'type' => 'lesson', 'note' => null],
            ['seq' => 8, 'start' => '11:45', 'end' => '12:20', 'type' => 'break', 'note' => 'Istirahat'],
            ['seq' => 9, 'start' => '12:20', 'end' => '13:00', 'type' => 'lesson', 'note' => null],
            ['seq' => 10, 'start' => '13:00', 'end' => '13:40', 'type' => 'lesson', 'note' => null],
            ['seq' => 11, 'start' => '13:40', 'end' => '14:20', 'type' => 'lesson', 'note' => null],
            ['seq' => 12, 'start' => '14:20', 'end' => '15:00', 'type' => 'lesson', 'note' => null],
        ];

        // Jadwal SELASA
        $selasa = [
            ['seq' => 0, 'start' => '06:10', 'end' => '06:30', 'type' => 'cleaning', 'note' => 'Tadarus & Kebersihan'],
            ['seq' => 1, 'start' => '06:30', 'end' => '07:10', 'type' => 'lesson', 'note' => null],
            ['seq' => 2, 'start' => '07:10', 'end' => '07:50', 'type' => 'lesson', 'note' => null],
            ['seq' => 3, 'start' => '07:50', 'end' => '08:30', 'type' => 'lesson', 'note' => null],
            ['seq' => 4, 'start' => '08:30', 'end' => '09:10', 'type' => 'lesson', 'note' => null],
            ['seq' => 5, 'start' => '09:10', 'end' => '09:50', 'type' => 'lesson', 'note' => null],
            ['seq' => 6, 'start' => '09:50', 'end' => '10:20', 'type' => 'break', 'note' => 'Istirahat/MBG'],
            ['seq' => 7, 'start' => '10:20', 'end' => '11:00', 'type' => 'lesson', 'note' => null],
            ['seq' => 8, 'start' => '11:00', 'end' => '11:40', 'type' => 'lesson', 'note' => null],
            ['seq' => 9, 'start' => '11:40', 'end' => '12:30', 'type' => 'break', 'note' => 'Istirahat'],
            ['seq' => 10, 'start' => '12:30', 'end' => '13:10', 'type' => 'lesson', 'note' => null],
            ['seq' => 11, 'start' => '13:10', 'end' => '13:50', 'type' => 'lesson', 'note' => null],
            ['seq' => 12, 'start' => '13:50', 'end' => '14:30', 'type' => 'lesson', 'note' => null],
            ['seq' => 13, 'start' => '14:30', 'end' => '15:10', 'type' => 'lesson', 'note' => null],
        ];

        // Jadwal RABU (sama dengan Selasa)
        $rabu = $selasa;

        // Jadwal KAMIS (sama dengan Selasa)
        $kamis = $selasa;

        // Jadwal JUMAT
        $jumat = [
            ['seq' => 0, 'start' => '06:30', 'end' => '07:30', 'type' => 'other', 'note' => 'Kerohanian/Olahraga/Kebersihan'],
            ['seq' => 1, 'start' => '07:30', 'end' => '08:05', 'type' => 'lesson', 'note' => null],
            ['seq' => 2, 'start' => '08:05', 'end' => '08:40', 'type' => 'lesson', 'note' => null],
            ['seq' => 3, 'start' => '08:40', 'end' => '09:15', 'type' => 'lesson', 'note' => null],
            ['seq' => 4, 'start' => '09:15', 'end' => '09:50', 'type' => 'lesson', 'note' => null],
            ['seq' => 5, 'start' => '09:50', 'end' => '10:20', 'type' => 'break', 'note' => 'Istirahat/MBG'],
            ['seq' => 6, 'start' => '10:20', 'end' => '10:55', 'type' => 'lesson', 'note' => null],
            ['seq' => 7, 'start' => '10:55', 'end' => '11:30', 'type' => 'lesson', 'note' => null],
            ['seq' => 8, 'start' => '11:30', 'end' => '12:30', 'type' => 'prayer', 'note' => 'Shalat Jum\'at / Keputian'],
            ['seq' => 9, 'start' => '12:30', 'end' => '13:10', 'type' => 'lesson', 'note' => null],
            ['seq' => 10, 'start' => '13:10', 'end' => '13:50', 'type' => 'lesson', 'note' => null],
        ];

        // Insert ke database
        $this->insertPeriods('senin', $senin);
        $this->insertPeriods('selasa', $selasa);
        $this->insertPeriods('rabu', $rabu);
        $this->insertPeriods('kamis', $kamis);
        $this->insertPeriods('jumat', $jumat);

        $this->command->info('Jadwal pembelajaran berhasil dibuat!');
    }

    /**
     * Helper function untuk insert data period ke database
     */
    private function insertPeriods(string $day, array $periods): void
    {
        foreach ($periods as $period) {
            // Hitung durasi dalam menit (pastikan non-negatif)
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $period['start']);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $period['end']);
            $duration = (int) abs($endTime->diffInMinutes($startTime));

            DB::table('class_periods')->insert([
                'day' => $day,
                'sequence' => $period['seq'],
                'start_time' => $period['start'] . ':00',
                'end_time' => $period['end'] . ':00',
                'duration_minutes' => $duration,
                'activity_type' => $period['type'],
                'note' => $period['note'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
