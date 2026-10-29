<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$b = \App\Models\Booking::with(['hotels', 'visas', 'transports', 'persons'])->find(2);

$cur = strtoupper($b->currency ?: 'PKR');
$roe = (float) ($b->flight_sale_roe > 1 ? $b->flight_sale_roe : ($b->flight_roe > 1 ? $b->flight_roe : 75));
$totAmt = (float) $b->total_amount;
$recAmt = (float) $b->total_received;

$compSar = 0;

// Hotels
foreach ($b->hotels as $h) {
    $hCur = strtoupper($h->currency ?: 'SAR');
    $hRoe = (float) ($h->sale_roe > 0 ? $h->sale_roe : ($h->roe > 0 ? $h->roe : $roe));
    $nights = max(1, (int) ($h->no_of_nights ?: 1));
    $rooms = max(1, (int) ($h->no_of_rooms ?: 1));
    $hSpn = (float) $h->selling_per_night;
    if ($hSpn > 0) {
        $compSar += ($hSpn * $nights * $rooms);
    } elseif ((float) $h->selling_amount > 0 && $hRoe > 0) {
        $compSar += round((float) $h->selling_amount / $hRoe, 2);
    }
}

// Flights
$fRoe = (float) ($b->flight_sale_roe > 0 ? $b->flight_sale_roe : ($b->flight_roe > 0 ? $b->flight_roe : $roe));
$fCharges = (float) $b->flight_charges;
$fSellRate = (float) $b->flight_selling_rate;
if ($fCharges > 0 && $fRoe > 0) {
    $compSar += round($fCharges / $fRoe, 2);
} elseif ($fSellRate > 0) {
    $compSar += $fSellRate;
}

// Visas
foreach ($b->visas as $v) {
    $vRoe = (float) ($v->sale_roe > 0 ? $v->sale_roe : ($v->roe > 0 ? $v->roe : $roe));
    if ((float) $v->selling_rate > 0) {
        $compSar += (float) $v->selling_rate;
    } elseif ((float) $v->selling_amount > 0 && $vRoe > 0) {
        $compSar += round((float) $v->selling_amount / $vRoe, 2);
    }
}

// Transports
foreach ($b->transports as $tr) {
    $trRoe = (float) ($tr->sale_roe > 0 ? $tr->sale_roe : ($tr->roe > 0 ? $tr->roe : $roe));
    if ((float) $tr->selling_rate > 0) {
        $compSar += (float) $tr->selling_rate;
    } elseif ((float) $tr->selling_amount > 0 && $trRoe > 0) {
        $compSar += round((float) $tr->selling_amount / $trRoe, 2);
    }
}

echo "Total compSar: {$compSar}\n";
echo "Total Amount (PKR): {$totAmt}\n";
echo "Calculated ROE: " . round($totAmt / $compSar, 2) . "\n";
