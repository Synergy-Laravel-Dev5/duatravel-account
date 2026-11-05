<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$asOfDate = Carbon\Carbon::now()->toDateString();
$packageType = 'all';
$year = 'all';
$activeRoe = 75.0;

$client = App\Models\Client::where('name', 'like', '%Arsal%')->first();
$bQuery = App\Models\Booking::where('client_id', $client->id)
    ->whereDate(Illuminate\Support\Facades\DB::raw('COALESCE(booking_date, flight_booking_date, DATE(created_at))'), '<=', $asOfDate);

$bkgSell = (float) $bQuery->selectRaw('SUM(CASE WHEN currency = "SAR" THEN total_amount * COALESCE(flight_roe, 75) ELSE total_amount END) as total')->value('total');
$bkgRec  = (float) $bQuery->selectRaw('SUM(CASE WHEN currency = "SAR" THEN total_received * COALESCE(flight_roe, 75) ELSE total_received END) as total')->value('total');

$txPaid = (float) App\Models\Transaction::where('client_id', $client->id)
    ->where('status', 'confirmed')
    ->whereDate('payment_date', '<=', $asOfDate)
    ->sum('amount');

$etCredits = (float) App\Models\ExpenseTransaction::where('client_id', $client->id)
    ->where('transaction_type', 'credit')
    ->whereDate('date', '<=', $asOfDate)
    ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
    ->value('total');

$etDebits = (float) App\Models\ExpenseTransaction::where('client_id', $client->id)
    ->where('transaction_type', 'debit')
    ->whereDate('date', '<=', $asOfDate)
    ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
    ->value('total');

echo "Client: " . $client->name . " Status: " . $client->status . PHP_EOL;
echo "bkgSell: $bkgSell, bkgRec: $bkgRec, txPaid: $txPaid, etCredits: $etCredits, etDebits: $etDebits" . PHP_EOL;
$netClientDue = $bkgSell - $bkgRec - $txPaid - $etCredits + $etDebits;
echo "netClientDue: $netClientDue" . PHP_EOL;
