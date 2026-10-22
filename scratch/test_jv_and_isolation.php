<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ExpenseTransaction;
use App\Models\Client;
use App\Models\Supplier;

echo "=== TESTING HAJJ VS UMRAH CLIENT ISOLATION ===\n";
session(['dashboard_package' => 'hajj']);
$ctrlClient = new \App\Http\Controllers\ClientController();
$viewHajjClients = $ctrlClient->index();
$hajjClients = $viewHajjClients->getData()['clients'];
echo "Hajj Client Count: " . $hajjClients->count() . "\n";
foreach ($hajjClients as $c) {
    echo "  - Client #{$c->id}: {$c->name} (Package: {$c->package_type})\n";
}

session(['dashboard_package' => 'umrah']);
$viewUmrahClients = $ctrlClient->index();
$umrahClients = $viewUmrahClients->getData()['clients'];
echo "Umrah Client Count: " . $umrahClients->count() . "\n";
foreach ($umrahClients as $c) {
    echo "  - Client #{$c->id}: {$c->name} (Package: {$c->package_type})\n";
}

echo "\n=== TESTING JV VOUCHER CREATION ===\n";
// Create a JV transaction
$jvTx = ExpenseTransaction::create([
    'bank_id' => null,
    'supplier_id' => null,
    'client_id' => 12, // Fahad (Umrah)
    'hajj_umrah' => 'umrah',
    'year' => 2026,
    'amount' => 5000,
    'payment_type' => 'cash',
    'transaction_type' => 'debit',
    'payee_type' => 'jv',
    'currency' => 'PKR',
    'exchange_rate' => 1,
    'reference_no' => 'DT-JV-000001',
    'date' => '2026-10-22',
    'description' => 'Test Journal Voucher Adjustment Entry',
]);
echo "Created JV Transaction ID: {$jvTx->id} | PayeeType: {$jvTx->payee_type} | Ref: {$jvTx->reference_no}\n";

// Verify in TransactionReport
$ctrlTransReport = new \App\Http\Controllers\TransactionReportController();
$req = new \Illuminate\Http\Request(['package_type' => 'umrah']);
$method = (new \ReflectionClass($ctrlTransReport))->getMethod('gatherTransactionsData');
$method->setAccessible(true);
$repData = $method->invoke($ctrlTransReport, $req);
$foundJV = false;
foreach ($repData['transactions'] as $t) {
    if ($t['voucher_no'] === 'ETXN-' . $jvTx->id) {
        $foundJV = true;
        echo "Found JV in All Transactions Report: Category={$t['category_label']} | Party={$t['party_name']} | Outflow={$t['outflow_pkr']}\n";
        break;
    }
}
echo "JV found in report: " . ($foundJV ? "YES" : "NO") . "\n";

// Clean up test transaction
$jvTx->forceDelete();
echo "Cleaned up test JV transaction.\n";
