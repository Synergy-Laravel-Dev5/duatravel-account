<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ExpenseTransaction;
use App\Models\Bank;
use App\Models\Transaction;
use App\Models\BankTransfer;
use Illuminate\Support\Facades\Schema;

echo "ExpenseTransaction columns:\n";
print_r(Schema::getColumnListing('expense_transactions'));

echo "\nAll ExpenseTransactions:\n";
foreach (ExpenseTransaction::with(['bank', 'client', 'supplier', 'staff', 'expense'])->get() as $et) {
    echo "ID: {$et->id} | Date: {$et->date} | Type: {$et->type} | PayeeType: {$et->payee_type} | TxType: {$et->transaction_type} | BankID: {$et->bank_id} (" . ($et->bank?->name ?? 'None') . ") | PaymentType: {$et->payment_type} | Amt: {$et->amount} | Cur: {$et->currency} | Desc: {$et->description}\n";
}

echo "\nAll BankTransfers:\n";
foreach (BankTransfer::all() as $bt) {
    echo "ID: {$bt->id} | Date: {$bt->transfer_date} | From: {$bt->from_bank_id} | To: {$bt->to_bank_id} | Amt: {$bt->amount}\n";
}

echo "\nAll Client Transactions:\n";
foreach (Transaction::all() as $t) {
    echo "ID: {$t->id} | Date: {$t->payment_date} | PayType: {$t->payment_type} | Amt: {$t->amount} | Booking: {$t->booking_id}\n";
}
