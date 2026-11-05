<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$places = App\Models\Hotel::select('place')->distinct()->pluck('place');
echo "Distinct places: " . json_encode($places) . PHP_EOL;
