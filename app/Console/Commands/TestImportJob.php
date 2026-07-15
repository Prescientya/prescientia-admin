<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessStudentImport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class TestImportJob extends Command
{
    protected $signature = 'test:import';
    protected $description = 'Test ProcessStudentImport job locally';

    public function handle()
    {
        $importId = (string) Str::uuid();
        
        // create a dummy excel file? No, just a dummy text file to see if it reaches IOFactory
        Storage::disk('local')->put('imports/test.xlsx', 'dummy content');
        
        Cache::put("student_import:{$importId}", [
            'status'     => 'queued',
            'admin_id'   => 1,
            'created_at' => now()->toIso8601String(),
        ], now()->addHour());

        try {
            $job = new ProcessStudentImport($importId, 'imports/test.xlsx', false, [], 1);
            $job->handle();
            $this->info("Job finished.");
        } catch (\Throwable $e) {
            $this->error("Job failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
