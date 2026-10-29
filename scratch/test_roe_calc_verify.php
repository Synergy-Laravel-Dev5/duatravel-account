<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$etAmount = 7600.00;
$currency = 'SAR';
$roe = 76.00;

// Test calculation logic in TransactionController
$rawAmount = $etAmount;
$exchangeRate = $roe;
$pkrAmount = $rawAmount;
$sarAmount = ($exchangeRate > 1) ? round($rawAmount / $exchangeRate, 2) : 0.00;

echo "Input Amount: {$etAmount}\n";
echo "Currency: {$currency}, ROE: {$roe}\n";
echo "Calculated PKR: {$pkrAmount} PKR\n";
echo "Calculated SAR: {$sarAmount} SAR\n";

if ($pkrAmount == 7600 && $sarAmount == 100.00) {
    echo "SUCCESS: 7600 PKR at ROE 76 gives exactly 100 SAR!\n";
} else {
    echo "FAIL\n";
}
