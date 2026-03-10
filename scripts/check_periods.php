<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$count = DB::table('class_periods')->count();
echo "class_periods rows: $count" . PHP_EOL;
if ($count > 0) {
    DB::table('class_periods')->orderBy('day')->orderBy('sequence')->get()->each(function($p) {
        echo "  {$p->day} | jam {$p->sequence} | {$p->start_time} - {$p->end_time} | {$p->activity_type}" . PHP_EOL;
    });
}
