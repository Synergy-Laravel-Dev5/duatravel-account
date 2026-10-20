<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bookings = App\Models\Booking::with('hotels')->latest('id')->take(10)->get();
foreach($bookings as $b) {
    echo "ID: {$b->id} | Voucher: {$b->voucher_number} | booking_date: {$b->booking_date} | created_at: {$b->created_at} | package_year: {$b->package_year} | total_amount: {$b->total_amount}\n";
    foreach($b->hotels as $h) {
        echo "   -> Hotel ID: {$h->id} | Name: {$h->hotel_name} | check_in: {$h->check_in} | booking_date: {$h->booking_date} | created_at: {$h->created_at}\n";
    }
}
