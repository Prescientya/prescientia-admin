<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Teacher;

$id = $argv[1] ?? 1;
$teacher = Teacher::with(['classRoles','user'])->find($id);
if (!$teacher) {
    echo "Teacher not found\n";
    exit(0);
}
print_r($teacher->toArray());
