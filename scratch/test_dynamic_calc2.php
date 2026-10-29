<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = \App\Models\Booking::with(['client', 'company', 'package', 'persons', 'hotels', 'transports', 'visas'])->find(1);

$effectiveRoe = (float)($booking->flight_sale_roe > 1 ? $booking->flight_sale_roe : ($booking->flight_roe > 1 ? $booking->flight_roe : ($booking->roe > 1 ? $booking->roe : 75.0)));

$hotelSarTotal = 0; $hotelPkrTotal = 0; $totalNights = 0;
foreach ($booking->hotels as $h) {
    $cur = strtoupper($h->currency ?: 'SAR');
    $nights = (int)($h->no_of_nights ?: 1);
    $rooms = (int)($h->no_of_rooms ?: 1);
    $totalNights += $nights;
    $rate = (float)($h->selling_per_night > 0 ? $h->selling_per_night : ($h->cost_per_night > 0 ? $h->cost_per_night : 0));
    $roe = (float)($h->sale_roe > 1 ? $h->sale_roe : ($h->cost_roe > 1 ? $h->cost_roe : $effectiveRoe));

    // In DB, if currency is SAR, rate is SAR. Total SAR = rate * nights * rooms. Total PKR = total SAR * roe.
    if ($cur === 'SAR') {
        $sarRate = $rate;
        $sarTotal = $rate * $nights * $rooms;
        $pkrRate = $rate * $roe;
        $pkrTotal = (float)($h->selling_amount > 0 ? $h->selling_amount : round($sarTotal * $roe, 2));
    } else {
        $pkrRate = $rate;
        $pkrTotal = (float)($h->selling_amount > 0 ? $h->selling_amount : ($rate * $nights * $rooms));
        $sarRate = ($roe > 1) ? round($rate / $roe, 2) : 0;
        $sarTotal = ($roe > 1) ? round($pkrTotal / $roe, 2) : 0;
    }
    $hotelSarTotal += $sarTotal;
    $hotelPkrTotal += $pkrTotal;
    echo "Hotel: {$h->hotel_name} | Nights: $nights | Rooms: $rooms | SAR Rate: $sarRate | SAR Total: $sarTotal | PKR Total: $pkrTotal\n";
}

$trSarTotal = 0; $trPkrTotal = 0;
foreach ($booking->transports as $tr) {
    $cur = strtoupper($tr->currency ?: 'SAR');
    $qty = (int)($tr->quantity ?: 1);
    $rate = (float)($tr->selling_rate > 0 ? $tr->selling_rate : ($tr->cost_rate > 0 ? $tr->cost_rate : 0));
    $roe = (float)($tr->sale_roe > 1 ? $tr->sale_roe : ($tr->cost_roe > 1 ? $tr->cost_roe : $effectiveRoe));

    if ($cur === 'SAR') {
        $sarRate = $rate;
        $sarTotal = $rate * $qty;
        $pkrRate = $rate * $roe;
        $pkrTotal = (float)($tr->selling_amount > 0 ? $tr->selling_amount : round($sarTotal * $roe, 2));
    } else {
        $pkrRate = $rate;
        $pkrTotal = (float)($tr->selling_amount > 0 ? $tr->selling_amount : ($rate * $qty));
        $sarRate = ($roe > 1) ? round($rate / $roe, 2) : 0;
        $sarTotal = ($roe > 1) ? round($pkrTotal / $roe, 2) : 0;
    }
    $trSarTotal += $sarTotal;
    $trPkrTotal += $pkrTotal;
    echo "Transport: {$tr->transport_type} | Qty: $qty | SAR Rate: $sarRate | SAR Total: $sarTotal | PKR Total: $pkrTotal\n";
}

echo "Sum Hotels SAR: $hotelSarTotal, PKR: $hotelPkrTotal\n";
echo "Sum Transports SAR: $trSarTotal, PKR: $trPkrTotal\n";
echo "Package Without Ticket (SAR): " . ($hotelSarTotal + $trSarTotal) . "\n";
echo "Booking Net Total (PKR): {$booking->total_amount}\n";
