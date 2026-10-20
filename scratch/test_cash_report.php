<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\ExpenseTransaction;
use App\Models\Transaction;

echo "=== CASH REPORT DATA TEST ===\n";

// 1. ExpenseTransactions with payment_type = 'cash'
$cashExp = ExpenseTransaction::where('payment_type', 'cash')->whereNull('deleted_at')->get();
echo "ExpenseTransactions with cash: " . $cashExp->count() . "\n";
foreach ($cashExp as $e) {
    echo "ID: {$e->id} | Date: {$e->date} | Type: {$e->transaction_type} | Payee: {$e->payee_type} | Amt: {$e->amount} {$e->currency} @ {$e->exchange_rate}\n";
}

// 2. Bookings with total_received > 0
$cashBookings = Booking::where('total_received', '>', 0)->get();
echo "\nBookings with total_received > 0: " . $cashBookings->count() . "\n";
foreach ($cashBookings as $b) {
    echo "Booking ID: {$b->id} | Date: {$b->booking_date} | Received: {$b->total_received} | Client: " . ($b->client?->name ?? 'None') . "\n";
}

// 3. Transactions with payment_type = 'cash'
$cashTxns = Transaction::where('payment_type', 'cash')->get();
echo "\nClient Transactions with cash: " . $cashTxns->count() . "\n";
