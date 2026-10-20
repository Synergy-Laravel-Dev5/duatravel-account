<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

$emptyCount = Booking::whereNull('booking_date')->orWhere('booking_date', '')->count();
echo "Empty booking_date count: {$emptyCount}\n";

$bookings = Booking::whereNull('booking_date')->orWhere('booking_date', '')->get();
foreach ($bookings as $b) {
    echo "ID: {$b->id} | Created: {$b->created_at}\n";
}
