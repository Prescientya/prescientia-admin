<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DRIVER CONFIG ===" . PHP_EOL;
echo "Cache:   " . config('cache.default') . PHP_EOL;
echo "Session: " . config('session.driver') . PHP_EOL;
echo "Queue:   " . config('queue.default') . PHP_EOL;
echo "Broadcast: " . config('broadcasting.default') . PHP_EOL;
echo "Octane Server: " . config('octane.server', 'not set') . PHP_EOL;
echo PHP_EOL;

echo "=== REDIS CONNECTION ===" . PHP_EOL;
try {
    $pong = Illuminate\Support\Facades\Redis::ping();
    echo "Redis::ping() => " . ($pong ? 'PONG (Connected)' : 'no response') . PHP_EOL;
} catch (Exception $e) {
    echo "Redis ERROR: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;
echo "=== REDIS CACHE READ/WRITE ===" . PHP_EOL;
try {
    Illuminate\Support\Facades\Cache::put('status_check', 'working', 30);
    $val = Illuminate\Support\Facades\Cache::get('status_check');
    echo "Cache::put + get => " . ($val === 'working' ? 'OK (working)' : 'FAIL (got: ' . $val . ')') . PHP_EOL;
} catch (Exception $e) {
    echo "Cache ERROR: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;
echo "=== OCTANE STATUS ===" . PHP_EOL;
$octaneInstalled = class_exists(\Laravel\Octane\Octane::class);
echo "Laravel\Octane installed: " . ($octaneInstalled ? 'YES' : 'NO') . PHP_EOL;
if ($octaneInstalled) {
    echo "Octane server configured: " . config('octane.server', 'not set') . PHP_EOL;
    $workerFile = file_exists(__DIR__ . '/../public/frankenphp-worker.php');
    echo "FrankenPHP worker file: " . ($workerFile ? 'EXISTS' : 'NOT FOUND') . PHP_EOL;
}

echo PHP_EOL;
echo "=== REDIS KEYS (DB0 default) ===" . PHP_EOL;
try {
    $keys = Illuminate\Support\Facades\Redis::keys('*');
    echo "Keys in default DB: " . count($keys) . PHP_EOL;
    foreach (array_slice($keys, 0, 5) as $k) {
        echo "  - " . $k . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Keys ERROR: " . $e->getMessage() . PHP_EOL;
}
