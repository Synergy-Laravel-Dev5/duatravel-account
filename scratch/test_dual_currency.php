<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ExpenseTransaction;

echo "=== CHECKING SAR & PKR EXPENSE TRANSACTIONS ===\n";
foreach (ExpenseTransaction::all() as $et) {
    $cur = strtoupper($et->currency ?: 'PKR');
    $rate = (float) ($et->exchange_rate > 0 ? $et->exchange_rate : 1);
    $rawAmt = (float) $et->amount;
    $pkrAmt = ($cur === 'PKR') ? $rawAmt : round($rawAmt * $rate, 2);
    $sarAmt = ($cur === 'SAR') ? $rawAmt : 0.00;

    $isCredit = ($et->transaction_type === 'credit');

    $crPkr = $isCredit ? $pkrAmt : 0;
    $drPkr = !$isCredit ? $pkrAmt : 0;
    $crSar = $isCredit ? $sarAmt : 0;
    $drSar = !$isCredit ? $sarAmt : 0;

    echo "ID: {$et->id} | Bank: {$et->bank_id} | Cur: {$cur} | Cr PKR: {$crPkr} | Dr PKR: {$drPkr} | Cr SAR: {$crSar} | Dr SAR: {$drSar}\n";
}
