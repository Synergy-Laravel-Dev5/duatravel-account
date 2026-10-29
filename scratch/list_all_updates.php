<?php
$lines = file('storage/logs/laravel.log');
$updates = [];
foreach ($lines as $line) {
    if (strpos($line, 'Update Request ID 1:  {') !== false) {
        $jsonStr = substr($line, strpos($line, '{'));
        $updates[] = [
            'time' => substr($line, 1, 19),
            'data' => json_decode(trim($jsonStr), true)
        ];
    }
}

foreach ($updates as $idx => $u) {
    $h = $u['data']['hotels'][0] ?? [];
    $v = $u['data']['visas'][0] ?? [];
    $f = $u['data']['flight_cost_rate'] ?? null;
    $p = $u['data']['no_of_pax'] ?? null;
    echo "#{$idx} at {$u['time']} | Pax: {$p} | FlCostRate: {$f} | HotelSell: " . ($h['selling_per_night'] ?? 'N/A') . " | PersonSell: " . json_encode(array_column($h['person_rates'] ?? [], 'selling_per_night')) . " | TotalAmt: " . ($u['data']['total_amount'] ?? 'N/A') . "\n";
}
