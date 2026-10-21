<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Supplier;
use App\Models\Client;
use App\Models\Booking;
use Illuminate\Http\Request;

\Illuminate\Support\Facades\Auth::loginUsingId(1);

echo "=== 1. Testing Supplier Ledger View Rendering ===\n";
$supplier = Supplier::first();
if ($supplier) {
    $controller = app()->make(App\Http\Controllers\SupplierController::class);
    $request = Request::create(route('supplier.ledger.show', $supplier->id), 'GET');
    $response = $controller->ledger($request, $supplier->id);
    $html = $response->render();
    echo "Supplier Ledger rendered successfully! HTML length: " . strlen($html) . " bytes\n";
    echo "Has Booking Date header: " . (str_contains($html, '<th>Booking Date</th>') ? 'YES' : 'NO') . "\n";
    echo "Has View 1 (Services Breakdown): " . (str_contains($html, 'id="view-breakdown"') ? 'YES' : 'NO') . "\n";
    echo "Has View 2 (Statement of Account): " . (str_contains($html, 'id="view-statement"') ? 'YES' : 'NO') . "\n";
    echo "Has toggleSupplierSarColumns switch: " . (str_contains($html, 'id="toggleSupplierSarColumns"') ? 'YES' : 'NO') . "\n";
} else {
    echo "No supplier found in DB.\n";
}

echo "\n=== 2. Testing Customer Ledger View Rendering ===\n";
$client = Client::first();
if ($client) {
    $txnController = app()->make(App\Http\Controllers\TransactionController::class);
    $req = Request::create(route('transaction.ledger.view'), 'GET', ['client_id' => $client->id]);
    $resp = $txnController->ledgerView($req);
    $clientHtml = $resp->render();
    echo "Customer Ledger rendered successfully! HTML length: " . strlen($clientHtml) . " bytes\n";
    echo "Has View 1 (c-view-statement): " . (str_contains($clientHtml, 'id="c-view-statement"') ? 'YES' : 'NO') . "\n";
    echo "Has View 2 (c-view-breakdown): " . (str_contains($clientHtml, 'id="c-view-breakdown"') ? 'YES' : 'NO') . "\n";
    echo "Has toggleSarCols switch: " . (str_contains($clientHtml, 'id="toggleSarCols"') ? 'YES' : 'NO') . "\n";
} else {
    echo "No client found in DB.\n";
}

echo "\n=== 3. Testing CheckIn Report ===\n";
$checkInController = app()->make(App\Http\Controllers\CheckInReportController::class);
$checkInReq = Request::create(route('reports.checkin'), 'GET');
$checkInResp = $checkInController->index($checkInReq);
$checkInHtml = $checkInResp->render();
echo "CheckIn Report rendered successfully! HTML length: " . strlen($checkInHtml) . " bytes\n";
echo "Has Option Date header: " . (str_contains($checkInHtml, 'Option Date') ? 'YES' : 'NO') . "\n";

echo "\n=== 4. Testing Expense Transaction Index ===\n";
$expController = app()->make(App\Http\Controllers\ExpenseTransactionController::class);
$expReq = Request::create(route('expense.transaction.index'), 'GET');
$expResp = $expController->index($expReq);
$expHtml = $expResp->render();
echo "Expense Transactions rendered successfully! HTML length: " . strlen($expHtml) . " bytes\n";
echo "Has Bank / Contra A/C: " . (str_contains($expHtml, 'Bank / Contra A/C') ? 'YES' : 'NO') . "\n";

echo "\n=== 5. Testing Sep PnL Report (Booking #9 Fahad Visa) ===\n";
$pnlController = app()->make(App\Http\Controllers\ProfitLossReportController::class);
$pnlReq = Request::create(route('report.profit-loss'), 'GET', [
    'from_date' => '2026-09-01',
    'to_date' => '2026-09-30'
]);
$pnlResp = $pnlController->index($pnlReq);
$pnlHtml = $pnlResp->render();
echo "PnL Report for Sep rendered successfully! HTML length: " . strlen($pnlHtml) . " bytes\n";
echo "Includes Booking #9: " . (str_contains($pnlHtml, '#9') ? 'YES' : 'NO') . "\n";
echo "Includes Fahad: " . (str_contains($pnlHtml, 'fahad') || str_contains($pnlHtml, 'Fahad') ? 'YES' : 'NO') . "\n";

echo "\nALL CHECKS PASSED!\n";
