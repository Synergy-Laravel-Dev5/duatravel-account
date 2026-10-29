<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;

$controller = new TransactionController();
$req = new Request(['client_id' => 1]);

$response = $controller->ledgerView($req);
$viewData = $response->getData();

echo "=== CLIENT STATEMENT OF ACCOUNT (LEDGER) ===\n";
echo "Client: " . $viewData['client']->name . "\n";
echo "Closing Balance PKR: " . $viewData['closingBalancePkr'] . "\n";
echo "Closing Balance SAR: " . $viewData['closingBalanceSar'] . "\n\n";

echo "LINES:\n";
foreach ($viewData['statementLines'] as $line) {
    echo "- Date: {$line['date']} | Voucher: {$line['voucher_no']} | Desc: {$line['description']}\n";
    echo "  PKR Debit: {$line['debit_pkr']} | PKR Credit: {$line['credit_pkr']} | PKR Bal: {$line['running_balance_pkr']}\n";
    echo "  SAR Debit: {$line['debit_sar']} | SAR Credit: {$line['credit_sar']} | SAR Bal: {$line['running_balance_sar']} | ROE: {$line['exchange_rate']}\n";
}
