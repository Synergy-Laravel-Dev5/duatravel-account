<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;

$b = Booking::with(['hotels', 'visas', 'transports'])->find(1);

// Method 1: Component by component
$sarTotal = 0;

// Hotels
foreach ($b->hotels as $h) {
    $cur = strtoupper($h->currency ?: 'SAR');
    $roe = (float) ($h->sale_roe > 0 ? $h->sale_roe : ($h->roe > 0 ? $h->roe : 75));
    if ($cur === 'SAR') {
        // selling_per_night is in SAR
        $nights = (int) ($h->no_of_nights ?: 1);
        $rooms = (int) ($h->no_of_rooms ?: 1);
        $hotelSar = (float) $h->selling_per_night * $nights * $rooms;
        if ($hotelSar <= 0 && (float) $h->selling_amount > 0 && $roe > 1) {
            $hotelSar = round((float) $h->selling_amount / $roe, 2);
        }
        $sarTotal += $hotelSar;
        echo "Hotel SAR: {$hotelSar} (rate={$h->selling_per_night}, nights={$nights}, rooms={$rooms})\n";
    } elseif ($roe > 1) {
        $sarTotal += round((float) $h->selling_amount / $roe, 2);
    }
}

// Flights
$flightRoe = (float) ($b->flight_sale_roe > 0 ? $b->flight_sale_roe : ($b->flight_roe > 0 ? $b->flight_roe : 75));
if (strtoupper($b->flight_currency ?: 'SAR') === 'SAR') {
    $pax = (int) ($b->no_of_pax ?: 1);
    $flightSar = (float) $b->flight_selling_rate * $pax;
    if ($flightSar <= 0 && (float) $b->flight_charges > 0 && $flightRoe > 1) {
        $flightSar = round((float) $b->flight_charges / $flightRoe, 2);
    }
    $sarTotal += $flightSar;
    echo "Flight SAR: {$flightSar} (rate={$b->flight_selling_rate}, pax={$pax})\n";
} elseif ($flightRoe > 1) {
    $sarTotal += round((float) $b->flight_charges / $flightRoe, 2);
}

// Visas
foreach ($b->visas as $v) {
    $vRoe = (float) ($v->sale_roe > 0 ? $v->sale_roe : ($v->roe > 0 ? $v->roe : 75));
    if (strtoupper($v->currency ?: 'SAR') === 'SAR') {
        $visaSar = (float) $v->selling_rate;
        if ($visaSar <= 0 && (float) $v->selling_amount > 0 && $vRoe > 1) {
            $visaSar = round((float) $v->selling_amount / $vRoe, 2);
        }
        $sarTotal += $visaSar;
        echo "Visa SAR: {$visaSar}\n";
    } elseif ($vRoe > 1) {
        $sarTotal += round((float) $v->selling_amount / $vRoe, 2);
    }
}

// Transports
foreach ($b->transports as $t) {
    $tRoe = (float) ($t->sale_roe > 0 ? $t->sale_roe : ($t->roe > 0 ? $t->roe : 75));
    if (strtoupper($t->currency ?: 'SAR') === 'SAR') {
        $transSar = (float) $t->selling_rate ?: ((float) $t->selling_amount / ($tRoe > 1 ? $tRoe : 1));
        $sarTotal += $transSar;
        echo "Transport SAR: {$transSar}\n";
    } elseif ($tRoe > 1) {
        $sarTotal += round((float) $t->selling_amount / $tRoe, 2);
    }
}

echo "Calculated SAR Total: {$sarTotal}\n";

// Method 2: Booking overall ROE
$overallRoe = (float) ($b->flight_sale_roe > 1 ? $b->flight_sale_roe : ($b->flight_roe > 1 ? $b->flight_roe : 75));
$convertedFromPkr = round((float) $b->total_amount / $overallRoe, 2);
echo "Converted from total_amount (59660 / {$overallRoe}): {$convertedFromPkr}\n";
