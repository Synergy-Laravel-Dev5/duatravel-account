<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$txs = App\Models\ExpenseTransaction::where('client_id', 1)->get();
foreach ($txs as $t) {
    echo "ID: " . $t->id . " Type: " . $t->transaction_type . " Amount: " . $t->amount . " Curr: " . $t->currency . " Rate: " . $t->exchange_rate . " Desc: " . $t->description . " Payee: " . $t->payee_type . " Supplier: " . $t->supplier_id . PHP_EOL;
}
