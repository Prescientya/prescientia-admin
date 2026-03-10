<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

echo "subjects rows:      " . DB::table('subjects')->count() . PHP_EOL;
echo "teacher_subject:    " . DB::table('teacher_subject')->count() . PHP_EOL;
echo "teached_classes:    " . DB::table('teached_classes')->count() . PHP_EOL;
echo "subject_teached_cl: " . DB::table('subject_teached_class')->count() . PHP_EOL;
echo PHP_EOL;
echo "=== Classes ===" . PHP_EOL;
DB::table('classes')->orderBy('class')->orderBy('major')->get()
    ->each(fn($c) => print("  [{$c->id}] Kelas {$c->class} – {$c->major}" . PHP_EOL));
echo PHP_EOL;
echo "=== Subjects (sample) ===" . PHP_EOL;
DB::table('subjects')->limit(10)->get()
    ->each(fn($s) => print("  [{$s->id}] {$s->name} | major={$s->major} | kelas={$s->kelas}" . PHP_EOL));
