<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "booking_hotels columns:\n";
foreach(Illuminate\Support\Facades\DB::select('DESCRIBE booking_hotels') as $c) {
    echo "{$c->Field} ({$c->Type}) Null:{$c->Null} Default:{$c->Default}\n";
}
