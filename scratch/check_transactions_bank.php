<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = Illuminate\Support\Facades\Schema::getColumnListing('transactions');
echo "Columns in transactions:\n";
print_r($cols);

$bankTx = App\Models\Transaction::where('payment_type', 'bank')->orWhereNotNull('bank_id')->get();
echo "Bank transactions count: " . $bankTx->count() . "\n";
foreach($bankTx->take(5) as $bt) {
    echo "ID: {$bt->id} | BankID: {$bt->bank_id} | Type: {$bt->payment_type} | Amt: {$bt->amount} | Date: {$bt->date}\n";
}
