<?php

namespace App\Console\Commands;

use App\Models\TeachedClass;
use Illuminate\Console\Command;

class MigrateTeachedClassesToSubjectId extends Command
{
    protected $signature = 'teached-classes:migrate-to-subject-id';
    protected $description = 'Migrate old teached_classes records to new subject_id structure';

    public function handle()
    {
        $this->info('Starting migration of teached_classes to new subject_id structure...');
        
        // Get all records without subject_id
        $oldRecords = TeachedClass::whereNull('subject_id')
            ->with(['subjects', 'teacher', 'class'])
            ->get();
        
        if ($oldRecords->isEmpty()) {
            $this->info('No records to migrate.');
            return 0;
        }
        
        $this->info("Found {$oldRecords->count()} old records to migrate.");
        
        $created = 0;
        $skipped = 0;
        
        foreach ($oldRecords as $oldRecord) {
            // Get all subjects from pivot table
            $subjects = $oldRecord->subjects;
            
            if ($subjects->isEmpty()) {
                $this->warn("Record ID {$oldRecord->id} has no subjects. Skipping.");
                $skipped++;
                continue;
            }
            
            // Create one new record for each subject
            foreach ($subjects as $subject) {
                // Check if already exists
                $exists = TeachedClass::where('teacher_id', $oldRecord->teacher_id)
                    ->where('class_id', $oldRecord->class_id)
                    ->where('subject_id', $subject->id)
                    ->where('semester', $oldRecord->semester)
                    ->exists();
                
                if (!$exists) {
                    TeachedClass::create([
                        'teacher_id' => $oldRecord->teacher_id,
                        'class_id' => $oldRecord->class_id,
                        'subject_id' => $subject->id,
                        'semester' => $oldRecord->semester,
                        'departments' => [], // Deprecated
                    ]);
                    
                    $this->info("Created: {$oldRecord->teacher->name} -> {$subject->name} in {$oldRecord->class->class} {$oldRecord->class->major}");
                    $created++;
                } else {
                    $this->warn("Duplicate: {$oldRecord->teacher->name} -> {$subject->name} already exists. Skipping.");
                    $skipped++;
                }
            }
            
            // Delete old record after migration
            $oldRecord->subjects()->detach(); // Remove pivot entries
            $oldRecord->delete();
            $this->info("Deleted old record ID {$oldRecord->id}");
        }
        
        $this->info("\nMigration completed!");
        $this->info("Created: {$created} new records");
        $this->info("Skipped: {$skipped} records");
        
        return 0;
    }
}
