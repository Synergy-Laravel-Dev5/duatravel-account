<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = \App\Models\Booking::with(['hotels', 'persons', 'transports', 'visas'])->find(7);
if (!$booking) {
    echo "Booking 7 NOT FOUND\n";
    $latest = \App\Models\Booking::latest()->first();
    echo "Latest booking: " . ($latest ? $latest->id : 'none') . "\n";
} else {
    echo "Booking 7 found:\n";
    print_r($booking->toArray());
}
