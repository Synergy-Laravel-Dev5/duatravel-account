<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = \App\Models\Booking::with(['client', 'company', 'package', 'persons', 'hotels', 'transports', 'visas'])->find(1);

$effectiveRoe = (float)($booking->flight_sale_roe > 1 ? $booking->flight_sale_roe : ($booking->flight_roe > 1 ? $booking->flight_roe : ($booking->roe > 1 ? $booking->roe : 75.0)));
if ($effectiveRoe <= 1) $effectiveRoe = 75.0;

$hotelSarTotal = 0; $hotelPkrTotal = 0; $totalNights = 0;
foreach ($booking->hotels as $h) {
    $cur = strtoupper($h->currency ?: 'SAR');
    $nights = (int)($h->no_of_nights ?: 1);
    $totalNights += $nights;
    $rate = (float)($h->selling_per_night > 0 ? $h->selling_per_night : ($h->cost_per_night > 0 ? $h->cost_per_night : 0));
    $amt = (float)($h->selling_amount > 0 ? $h->selling_amount : ($h->cost_amount > 0 ? $h->cost_amount : 0));
    $roe = (float)($h->sale_roe > 1 ? $h->sale_roe : ($h->cost_roe > 1 ? $h->cost_roe : $effectiveRoe));

    if ($cur === 'PKR') {
        $pkr = $amt;
        $sar = ($roe > 1) ? round($amt / $roe, 2) : 0;
    } else {
        $sar = $amt;
        $pkr = round($amt * $roe, 2);
    }
    $hotelSarTotal += $sar;
    $hotelPkrTotal += $pkr;
    echo "Hotel: {$h->hotel_name} | Nights: $nights | Cur: $cur | Rate: $rate | SAR: $sar | PKR: $pkr\n";
}

$trSarTotal = 0; $trPkrTotal = 0;
foreach ($booking->transports as $tr) {
    $cur = strtoupper($tr->currency ?: 'SAR');
    $rate = (float)($tr->selling_rate > 0 ? $tr->selling_rate : ($tr->cost_rate > 0 ? $tr->cost_rate : 0));
    $amt = (float)($tr->selling_amount > 0 ? $tr->selling_amount : ($tr->cost_amount > 0 ? $tr->cost_amount : 0));
    $roe = (float)($tr->sale_roe > 1 ? $tr->sale_roe : ($tr->cost_roe > 1 ? $tr->cost_roe : $effectiveRoe));

    if ($cur === 'PKR') {
        $pkr = $amt;
        $sar = ($roe > 1) ? round($amt / $roe, 2) : 0;
    } else {
        $sar = $amt;
        $pkr = round($amt * $roe, 2);
    }
    $trSarTotal += $sar;
    $trPkrTotal += $pkr;
    echo "Transport: {$tr->transport_type} | Rate: $rate | SAR: $sar | PKR: $pkr\n";
}

echo "Total Hotel SAR: $hotelSarTotal, PKR: $hotelPkrTotal\n";
echo "Total Transport SAR: $trSarTotal, PKR: $trPkrTotal\n";
echo "Total Nights: $totalNights\n";
echo "Booking Total Amount: {$booking->total_amount}\n";
