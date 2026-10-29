<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$b = \App\Models\Booking::with(['hotels', 'visas', 'transports', 'persons'])->find(2) ?? \App\Models\Booking::with(['hotels', 'visas', 'transports', 'persons'])->first();

if (!$b) {
    echo "No booking found!\n";
    exit;
}

echo "Booking ID: {$b->id}\n";
echo "Voucher: {$b->voucher_number}\n";
echo "Currency: {$b->currency}\n";
echo "Total Amount: {$b->total_amount}\n";
echo "Total Received: {$b->total_received}\n";
echo "flight_roe: {$b->flight_roe}, flight_sale_roe: {$b->flight_sale_roe}, flight_charges: {$b->flight_charges}, flight_selling_rate: {$b->flight_selling_rate}, flight_currency: {$b->flight_currency}\n";

echo "\nHotels count: " . $b->hotels->count() . "\n";
foreach ($b->hotels as $h) {
    echo "  Hotel #{$h->id}: curr={$h->currency}, nights={$h->no_of_nights}, rooms={$h->no_of_rooms}, selling_per_night={$h->selling_per_night}, selling_amount={$h->selling_amount}, roe={$h->roe}, sale_roe={$h->sale_roe}\n";
}

echo "\nVisas count: " . $b->visas->count() . "\n";
foreach ($b->visas as $v) {
    echo "  Visa #{$v->id}: curr={$v->currency}, rate={$v->selling_rate}, amount={$v->selling_amount}, roe={$v->roe}, sale_roe={$v->sale_roe}\n";
}

echo "\nTransports count: " . $b->transports->count() . "\n";
foreach ($b->transports as $tr) {
    echo "  Transport #{$tr->id}: curr={$tr->currency}, rate={$tr->selling_rate}, amount={$tr->selling_amount}, roe={$tr->roe}, sale_roe={$tr->sale_roe}\n";
}
