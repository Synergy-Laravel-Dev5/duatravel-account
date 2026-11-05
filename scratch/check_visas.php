<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$visas = App\Models\BookingVisa::all();
echo "Total booking visas: " . $visas->count() . PHP_EOL;
foreach($visas as $v) {
    echo "ID: {$v->id}, Booking ID: {$v->booking_id}, Name: {$v->given_name}, Supplier ID: " . json_encode($v->supplier_id) . PHP_EOL;
}
