<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = App\Models\Client::where('name', 'like', '%Arsal%')->first();
if ($client) {
    echo "Client: " . $client->name . " ID: " . $client->id . PHP_EOL;
    $bookings = App\Models\Booking::where('client_id', $client->id)->get();
    foreach ($bookings as $b) {
        echo "Booking ID: " . $b->id . " Total: " . $b->total_amount . " Received: " . $b->total_received . " Balance: " . $b->balance . PHP_EOL;
    }
    $txs = App\Models\Transaction::where('client_id', $client->id)->get();
    echo "Transactions count: " . $txs->count() . PHP_EOL;
    foreach ($txs as $t) {
        echo "Tx ID: " . $t->id . " Amount: " . $t->amount . " Status: " . $t->status . " Booking ID: " . $t->booking_id . PHP_EOL;
    }
    $etxs = App\Models\ExpenseTransaction::where('client_id', $client->id)->get();
    echo "Expense Transactions count: " . $etxs->count() . PHP_EOL;
    foreach ($etxs as $e) {
        echo "ETx ID: " . $e->id . " Amount: " . $e->amount . " Type: " . $e->transaction_type . PHP_EOL;
    }
}
