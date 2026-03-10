<?php
// Parse .env manually — no Laravel boot needed
$envFile = __DIR__ . '/../.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
}

$host   = $env['DB_HOST']     ?? '127.0.0.1';
$port   = $env['DB_PORT']     ?? 3306;
$dbname = $env['DB_DATABASE'] ?? 'prescientia';
$user   = $env['DB_USERNAME'] ?? 'root';
$pass   = $env['DB_PASSWORD'] ?? '';

$pdo  = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8", $user, $pass);
$rows = $pdo->query('SELECT id, major, `class` FROM classes ORDER BY `class`, major')->fetchAll(PDO::FETCH_OBJ);

$needed = ['AK-1','AK-2','AK-3','AK-4','PBR-1','PBR-2','PBR-3','MP-1','MP-2','MP-3','HTL-1','HTL-2','KLN-1','KLN-2','DKV','RPL'];

echo "=== Semua kelas di database (" . count($rows) . ") ===\n";
if (empty($rows)) {
    echo "  (tidak ada kelas sama sekali)\n";
} else {
    foreach ($rows as $r) {
        echo "  [{$r->id}] Grade {$r->class} - {$r->major}\n";
    }
}

$existingMajors12 = array_column(array_filter($rows, fn($r) => (int)$r->class === 12), 'major');
$missing = array_values(array_diff($needed, $existingMajors12));

echo "\n=== Status kelas Grade 12 untuk import siswa ===\n";
echo "Total dibutuhkan: " . count($needed) . "\n";
echo "Sudah ada       : " . count($existingMajors12) . "\n";
echo "BELUM ADA       : " . count($missing) . "\n";

if (!empty($missing)) {
    echo "\nKelas yang perlu ditambahkan ke database:\n";
    foreach ($missing as $i => $m) {
        echo "  " . ($i+1) . ". Grade 12 - {$m}\n";
    }
} else {
    echo "\nSemua kelas sudah ada! Import bisa langsung dilakukan.\n";
}
