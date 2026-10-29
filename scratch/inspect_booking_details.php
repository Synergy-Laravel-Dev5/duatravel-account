<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BookingHotel;
use App\Models\BookingVisa;
use App\Models\BookingTransport;

echo "=== BOOKING HOTELS ===\n";
print_r(BookingHotel::where('booking_id', 1)->get()->toArray());

echo "=== BOOKING VISAS ===\n";
print_r(BookingVisa::where('booking_id', 1)->get()->toArray());

echo "=== BOOKING TRANSPORTS ===\n";
print_r(BookingTransport::where('booking_id', 1)->get()->toArray());
