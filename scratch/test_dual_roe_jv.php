<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;
use App\Models\Supplier;
use App\Models\ExpenseTransaction;

$client = Client::first();
$supplier = Supplier::first();

if (!$client || !$supplier) {
    echo "Client or Supplier missing for test.\n";
    exit;
}

echo "Testing JV with Client ID: {$client->id} and Supplier ID: {$supplier->id}\n";

$request = new \Illuminate\Http\Request([
    'payee_type'       => 'jv',
    'jv_mode'          => 'universal',
    'jv_from_account'  => "client:{$client->id}",
    'jv_to_account'    => "supplier:{$supplier->id}",
    'jv_from_roe'      => 76.00,
    'jv_to_roe'        => 75.00,
    'hajj_umrah'       => 'umrah',
    'year'             => 2026,
    'amount'           => 76000,
    'currency'         => 'PKR',
    'payment_type'     => 'jv',
    'date'             => '2026-10-29',
    'description'      => 'Testing Dual ROE JV',
]);

$controller = new \App\Http\Controllers\ExpenseTransactionController();
$response = $controller->store($request);

$lastTwo = ExpenseTransaction::orderBy('id', 'desc')->take(2)->get();
echo "Stored transactions:\n";
foreach ($lastTwo as $t) {
    echo "ID: {$t->id} | Ref: {$t->reference_no} | Amt: {$t->amount} | ROE: {$t->exchange_rate} | Type: {$t->transaction_type} | Client: {$t->client_id} | Supp: {$t->supplier_id} | Desc: {$t->description}\n";
}

// Clean up test transactions
foreach ($lastTwo as $t) {
    $t->forceDelete();
}
echo "Test completed and cleaned up.\n";
