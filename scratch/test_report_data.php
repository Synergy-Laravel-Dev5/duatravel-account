<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\BankTransferController;

$controller = new BankTransferController();

$reqAll = new Request();
$dataAll = $controller->report($reqAll)->getData();
echo "--- ALL BANKS ---\n";
echo "Total Deposits PKR: " . $dataAll['totalDepositsPkr'] . "\n";
echo "Net Movement PKR: " . $dataAll['netMovementPkr'] . "\n";
echo "Transactions count: " . count($dataAll['transactions']) . "\n";
if (!empty($dataAll['transactions'])) {
    foreach ($dataAll['transactions'] as $t) {
        echo "- " . $t['voucher_no'] . " | " . $t['category_label'] . " | Deposit: " . $t['deposit_pkr'] . " | Withdrawal: " . $t['withdrawal_pkr'] . " | Running: " . $t['running_balance_pkr'] . "\n";
    }
}

$reqSamba = new Request(['bank_id' => 1]);
$dataSamba = $controller->report($reqSamba)->getData();
echo "\n--- SAMBA BANK (ID 1) ---\n";
echo "Total Deposits PKR: " . $dataSamba['totalDepositsPkr'] . "\n";
echo "Net Movement PKR: " . $dataSamba['netMovementPkr'] . "\n";
echo "Transactions count: " . count($dataSamba['transactions']) . "\n";
if (!empty($dataSamba['transactions'])) {
    foreach ($dataSamba['transactions'] as $t) {
        echo "- " . $t['voucher_no'] . " | " . $t['category_label'] . " | Deposit: " . $t['deposit_pkr'] . " | Withdrawal: " . $t['withdrawal_pkr'] . " | Running: " . $t['running_balance_pkr'] . "\n";
    }
}
