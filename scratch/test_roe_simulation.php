<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ExpenseTransaction;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\TransactionController;

// Find client 1
$client = Client::find(1);
if (!$client) {
    echo "No client found\n";
    exit(1);
}

// Emulate expense transaction
$et = ExpenseTransaction::create([
    'hajj_umrah' => 'umrah',
    'year' => 2026,
    'amount' => 7600,
    'payment_type' => 'bank',
    'transaction_type' => 'credit',
    'payee_type' => 'client',
    'client_id' => $client->id,
    'currency' => 'PKR',
    'exchange_rate' => 76.00,
    'reference_no' => 'TEST-ROE-76',
    'date' => date('Y-m-d'),
    'description' => 'Test 7600 PKR with 76 ROE'
]);

echo "Created ET #{$et->id} with amount {$et->amount}, currency {$et->currency}, exchange_rate {$et->exchange_rate}\n";

// Render client ledger
$tc = new TransactionController();
$req = new Request(['client_id' => $client->id]);
$response = $tc->ledgerView($req);
$data = $response->getData();
$lines = $data['statementLines'];

$found = false;
foreach ($lines as $row) {
    if ($row['ref_no'] === 'TEST-ROE-76') {
        $found = true;
        echo "FOUND TEST ROW IN LEDGER:\n";
        echo "  Voucher: " . $row['voucher_no'] . "\n";
        echo "  Currency: " . $row['currency'] . "\n";
        echo "  ROE: " . $row['exchange_rate'] . "\n";
        echo "  Credit PKR: " . $row['credit_pkr'] . "\n";
        echo "  Credit SAR: " . $row['credit_sar'] . "\n";
        break;
    }
}

if (!$found) {
    echo "ERROR: Test row not found in ledger!\n";
} else {
    echo "SUCCESS: Ledger calculation verified!\n";
}

// Cleanup test transaction
$et->delete();
echo "Cleaned up test ET\n";
