<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Total BankTransfer: " . App\Models\BankTransfer::count() . "\n";
echo "Total ExpenseTransactions with bank_id: " . App\Models\ExpenseTransaction::whereNotNull('bank_id')->count() . "\n";
echo "Total ExpenseTransactions with payment_type cash: " . App\Models\ExpenseTransaction::where('payment_type', 'cash')->count() . "\n";
echo "Total Transactions (client payments) with payment_type cash: " . App\Models\Transaction::where('payment_type', 'cash')->count() . "\n";
