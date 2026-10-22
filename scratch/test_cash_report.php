<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

session(['dashboard_package' => 'hajj']);
$ctrl = new \App\Http\Controllers\CashReportController();
$reqHajj = new \Illuminate\Http\Request();
$resHajj = (new \ReflectionClass($ctrl))->getMethod('gatherCashData');
$resHajj->setAccessible(true);
$dataHajj = $resHajj->invoke($ctrl, $reqHajj);

echo "HAJJ CASH REPORT COUNT: " . count($dataHajj['transactions']) . "\n";
foreach ($dataHajj['transactions'] as $t) {
    echo "  - {$t['date']} | {$t['voucher_no']} | {$t['party_name']} | {$t['cash_in_pkr']} / {$t['cash_out_pkr']}\n";
}

session(['dashboard_package' => 'umrah']);
$reqUmrah = new \Illuminate\Http\Request();
$dataUmrah = $resHajj->invoke($ctrl, $reqUmrah);
echo "\nUMRAH CASH REPORT COUNT: " . count($dataUmrah['transactions']) . "\n";
foreach ($dataUmrah['transactions'] as $t) {
    echo "  - {$t['date']} | {$t['voucher_no']} | {$t['party_name']} | {$t['cash_in_pkr']} / {$t['cash_out_pkr']}\n";
}
