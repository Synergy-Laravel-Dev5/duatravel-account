<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;

$b = Booking::with(['hotels', 'visas', 'transports', 'flightSupplier'])->first();
if ($b) {
    echo "Booking ID: {$b->id}\n";
    echo "Total Booking Selling (PKR): {$b->selling_price}\n";
    echo "Hotels count: " . $b->hotels->count() . "\n";
    foreach ($b->hotels as $h) {
        echo "  Hotel: selling_amount={$h->selling_amount}, selling_rate={$h->selling_rate}, sale_roe={$h->sale_roe}\n";
    }
    echo "Visas count: " . $b->visas->count() . "\n";
    foreach ($b->visas as $v) {
        echo "  Visa: selling_amount={$v->selling_amount}, selling_rate={$v->selling_rate}, sale_roe={$v->sale_roe}\n";
    }
    echo "Transports count: " . $b->transports->count() . "\n";
    foreach ($b->transports as $t) {
        echo "  Transport: selling_amount={$t->selling_amount}, selling_rate={$t->selling_rate}, sale_roe={$t->sale_roe}\n";
    }
    echo "Flight: flight_selling_amount={$b->flight_selling_amount}, flight_selling_rate={$b->flight_selling_rate}, flight_sale_roe={$b->flight_sale_roe}\n";
}
