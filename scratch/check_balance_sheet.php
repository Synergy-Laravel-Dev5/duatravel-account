<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ctl = new App\Http\Controllers\BalanceSheetReportController();
$req = new Illuminate\Http\Request();
$ref = new ReflectionMethod($ctl, 'gatherBalanceSheetData');
$ref->setAccessible(true);
$data = $ref->invoke($ctl, $req);

echo "Total Assets: " . number_format($data['totalAssets'], 2) . PHP_EOL;
echo "Total Liabilities: " . number_format($data['totalLiabilities'], 2) . PHP_EOL;
echo "Capital Stock: " . number_format($data['capitalStock'], 2) . PHP_EOL;
echo "Capital Details: " . json_encode($data['capitalDetails']) . PHP_EOL;
echo "Retained Earnings: " . number_format($data['retainedEarnings'], 2) . PHP_EOL;
echo "Balancing Adjustment: " . number_format($data['balancingAdjustment'], 2) . PHP_EOL;
echo "Total Equity: " . number_format($data['totalEquity'], 2) . PHP_EOL;
echo "Total Prepaids: " . number_format($data['totalPrepaidsPkr'], 2) . PHP_EOL;
echo "Top Supplier Advances: " . json_encode($data['topSupplierAdvances']) . PHP_EOL;
echo "Top Client Receivables: " . json_encode($data['topClientReceivables']) . PHP_EOL;
