<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$missing = [];
$students = \App\Models\Student::with('schoolClass')->whereHas('schoolClass')->get();
foreach($students as $s) {
    $c = $s->schoolClass;
    if($c->class < 12) {
        $next = \App\Models\ClassModel::where('class', $c->class + 1)->where('major', $c->major)->first();
        if(!$next) {
            $missing['Kelas ' . ($c->class + 1) . ' ' . $c->major] = true;
        }
    }
}
$missingClasses = array_keys($missing);
sort($missingClasses);
foreach($missingClasses as $m) {
    echo "- " . $m . PHP_EOL;
}
