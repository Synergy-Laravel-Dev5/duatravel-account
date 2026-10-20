<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\BankTransferController;
use Illuminate\Http\Request;

$ctrl = new BankTransferController();

// Test 1: Bank ID = 1 (Meezan Bank)
$req = Request::create('/bank-transfer/report', 'GET', [
    'bank_id' => '1',
]);

$response = $ctrl->report($req);
$data = $response->getData();

echo "Meezan Bank Total Records: " . $data['totalCount'] . "\n";
echo "Total Deposits: PKR " . $data['totalDeposits'] . "\n";
echo "Total Withdrawals: PKR " . $data['totalWithdrawals'] . "\n";
echo "Net Movement: PKR " . $data['netMovement'] . "\n";

foreach ($data['transactions'] as $tx) {
    echo "Date: {$tx['date']} | Voucher: {$tx['voucher_no']} | Flow: {$tx['flow']} | In: {$tx['deposit']} | Out: {$tx['withdrawal']} | Bal: {$tx['running_balance']} | Party: {$tx['party_name']}\n";
}
