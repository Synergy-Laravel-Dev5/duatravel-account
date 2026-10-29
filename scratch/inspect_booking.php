<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;

$b = Booking::with(['hotels', 'visas', 'transports', 'client'])->find(1);
if (!$b) {
    $b = Booking::with(['hotels', 'visas', 'transports', 'client'])->first();
}

if ($b) {
    echo "BOOKING ID: " . $b->id . "\n";
    echo "currency: " . ($b->currency ?? 'NULL') . "\n";
    echo "total_amount: " . ($b->total_amount ?? '0') . "\n";
    echo "total_received: " . ($b->total_received ?? '0') . "\n";
    echo "flight_roe: " . ($b->flight_roe ?? 'NULL') . "\n";
    echo "flight_cost: " . ($b->flight_cost ?? 'NULL') . "\n";
    echo "flight_sell: " . ($b->flight_sell ?? 'NULL') . "\n";
    echo "flight_currency: " . ($b->flight_currency ?? 'NULL') . "\n";
    
    echo "\nHOTELS:\n";
    foreach ($b->hotels as $h) {
        echo "  - Hotel: {$h->hotel_name}, sell_currency: {$h->selling_currency}, buy_currency: {$h->buying_currency}, total_price: {$h->total_price}, buy_price: {$h->buy_price}, roe: {$h->roe}\n";
    }

    echo "\nTRANSPORTS:\n";
    foreach ($b->transports as $t) {
        echo "  - Transport: {$t->transport_type}, sell_currency: {$t->selling_currency}, buy_currency: {$t->buying_currency}, total_price: {$t->total_price}, buy_price: {$t->buy_price}, roe: {$t->roe}\n";
    }

    echo "\nVISAS:\n";
    foreach ($b->visas as $v) {
        echo "  - Visa: {$v->visa_type}, sell_currency: {$v->selling_currency}, buy_currency: {$v->buying_currency}, total_price: {$v->total_price}, buy_price: {$v->buy_price}, roe: {$v->roe}\n";
    }

    echo "\nALL BOOKING COLUMNS:\n";
    print_r($b->getAttributes());
} else {
    echo "No booking found.\n";
}
