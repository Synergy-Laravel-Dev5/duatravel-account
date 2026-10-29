<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$hotels = \App\Models\BookingHotel::where('booking_id', 1)->get();
foreach ($hotels as $h) {
    print_r($h->toArray());
}
