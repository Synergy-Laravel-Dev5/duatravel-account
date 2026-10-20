<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach ([3, 4, 5] as $id) {
    $et = App\Models\ExpenseTransaction::with('bank')->find($id);
    echo "ID: {$et->id} | Bank: {$et->bank?->name} | Amount: {$et->amount} {$et->currency} (Rate: {$et->exchange_rate}) | Type: {$et->transaction_type} | Payee: {$et->payee_type} | Method: {$et->payment_type}\n";
}
