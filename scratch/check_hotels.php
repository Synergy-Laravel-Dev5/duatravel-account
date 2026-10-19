<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Existing booking_hotels rows:\n";
$hotels = Illuminate\Support\Facades\DB::table('booking_hotels')->get();
foreach ($hotels as $h) {
    print_r($h);
}
