<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bank;
use App\Models\ExpenseTransaction;
use App\Models\BankTransfer;
use Carbon\Carbon;

$bankId = 1; // Meezan Bank
$bank = Bank::find($bankId);
echo "Testing Bank: {$bank->name}\n";

// 1. Fetch ExpenseTransactions for this bank
$expTxns = ExpenseTransaction::with(['bank', 'client', 'supplier', 'staff', 'expense'])
    ->where('bank_id', $bankId)
    ->whereNull('deleted_at')
    ->get();

echo "Found " . $expTxns->count() . " ExpenseTransactions for Meezan Bank:\n";
foreach ($expTxns as $et) {
    $rawAmt = (float) $et->amount;
    $rate = (float) ($et->exchange_rate > 0 ? $et->exchange_rate : 1);
    $cur = strtoupper($et->currency ?: 'PKR');
    $pkrAmt = ($cur === 'PKR') ? $rawAmt : round($rawAmt * $rate, 2);
    $isCredit = ($et->transaction_type === 'credit');
    
    echo "ID: {$et->id} | Date: {$et->date} | Type: " . ($isCredit ? 'CREDIT (Inflow)' : 'DEBIT (Outflow)') . " | Amount: {$cur} {$rawAmt} = PKR {$pkrAmt} | Payee: {$et->payee_type} | Mode: {$et->payment_type}\n";
}

// 2. Fetch Bank Transfers
$transfers = BankTransfer::where('from_bank_id', $bankId)->orWhere('to_bank_id', $bankId)->get();
echo "Found " . $transfers->count() . " BankTransfers for Meezan Bank.\n";
