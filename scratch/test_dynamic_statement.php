<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;

// Simulate dynamic income breakdown
$totalSellingPkr = 139460.0;
$totalHotelSellPkr = 79800.0;
$totalFlightSellPkr = 14060.0;
$totalTransSellPkr = 11400.0;
$totalVisaSellPkr = 34200.0;

$systemIncomeItems = [];
$sysCode = 1;

if ($totalHotelSellPkr > 0) {
    $systemIncomeItems[] = [
        'code' => '40-10-' . str_pad($sysCode++, 3, '0', STR_PAD_LEFT),
        'title' => 'Hotel Service Revenue',
        'amount' => (float)$totalHotelSellPkr,
    ];
}
if ($totalFlightSellPkr > 0) {
    $systemIncomeItems[] = [
        'code' => '40-10-' . str_pad($sysCode++, 3, '0', STR_PAD_LEFT),
        'title' => 'Flight Service Revenue',
        'amount' => (float)$totalFlightSellPkr,
    ];
}
if ($totalTransSellPkr > 0) {
    $systemIncomeItems[] = [
        'code' => '40-10-' . str_pad($sysCode++, 3, '0', STR_PAD_LEFT),
        'title' => 'Other Services / Transport Income',
        'amount' => (float)$totalTransSellPkr,
    ];
}
if ($totalVisaSellPkr > 0) {
    $systemIncomeItems[] = [
        'code' => '40-10-' . str_pad($sysCode++, 3, '0', STR_PAD_LEFT),
        'title' => 'Visa Income',
        'amount' => (float)$totalVisaSellPkr,
    ];
}

$servicesSellSum = $totalHotelSellPkr + $totalFlightSellPkr + $totalTransSellPkr + $totalVisaSellPkr;
$residualPkgSell = round($totalSellingPkr - $servicesSellSum, 2);
if ($residualPkgSell > 0 || empty($systemIncomeItems)) {
    $systemIncomeItems[] = [
        'code' => '40-10-' . str_pad($sysCode++, 3, '0', STR_PAD_LEFT),
        'title' => 'Package Sales Revenue',
        'amount' => (float)($residualPkgSell > 0 ? $residualPkgSell : $totalSellingPkr),
    ];
}

$totalSystemIncome = array_sum(array_column($systemIncomeItems, 'amount'));

echo "=== DYNAMIC SYSTEM INCOME ITEMS ===\n";
foreach ($systemIncomeItems as $item) {
    echo "{$item['code']} | {$item['title']} | PKR " . number_format($item['amount'], 2) . "\n";
}
echo "Total System Income: PKR " . number_format($totalSystemIncome, 2) . "\n";
echo "Matches Total Selling? " . ($totalSystemIncome == $totalSellingPkr ? "YES (100%)" : "NO") . "\n";
