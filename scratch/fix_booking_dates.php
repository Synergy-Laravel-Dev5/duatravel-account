<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$affected = DB::table('bookings')
    ->whereNull('booking_date')
    ->update(['booking_date' => DB::raw('DATE(created_at)')]);

echo "Updated {$affected} bookings with their created_at date.\n";

$bookings = DB::table('bookings')->select('id', 'voucher_number', 'booking_date', 'created_at')->get();
foreach ($bookings as $b) {
    echo "ID: {$b->id} | BookingDate: {$b->booking_date} | CreatedAt: {$b->created_at}\n";
}
