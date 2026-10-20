<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bookings = App\Models\Booking::with('hotels')->get();
foreach($bookings as $b) {
    echo "ID: {$b->id} | Voucher: {$b->voucher_number} | booking_date: '{$b->booking_date}' | created_at: '{$b->created_at}'\n";
    foreach($b->hotels as $h) {
        echo "   -> Hotel: {$h->hotel_name} | h_booking_date: '{$h->booking_date}' | check_in: '{$h->check_in}' | created_at: '{$h->created_at}'\n";
    }
}
