<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sups = App\Models\Supplier::all();
echo "Total suppliers: " . $sups->count() . PHP_EOL;
foreach($sups as $s) {
    echo "ID: {$s->id}, Name: {$s->name}, Type: " . json_encode($s->service_type ?? $s->type ?? null) . PHP_EOL;
}
