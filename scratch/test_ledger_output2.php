<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = new \Illuminate\Http\Request([
    'client_id' => 1,
]);

$controller = new \App\Http\Controllers\TransactionController();
$view = $controller->ledgerView($request);
$data = $view->getData();

echo "Client Name: " . $data['client']->name . "\n";
echo "Total Debits PKR: " . $data['totalDebitsPkr'] . "\n";
echo "Total Debits SAR: " . $data['totalDebitsSar'] . "\n";
echo "Closing Balance PKR: " . $data['closingBalancePkr'] . "\n";
echo "Closing Balance SAR: " . $data['closingBalanceSar'] . "\n";

echo "\nStatement Lines:\n";
foreach ($data['statementLines'] as $line) {
    echo "  Date: {$line['date']} | Voucher: {$line['voucher_no']} | Debit PKR: {$line['debit_pkr']} | Debit SAR: {$line['debit_sar']} | ROE: {$line['exchange_rate']}\n";
}
