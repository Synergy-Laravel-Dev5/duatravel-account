<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = App\Models\Supplier::where('name', 'like', '%Polani%')->first();
if ($s) {
    echo "Supplier: " . $s->name . " ID: " . $s->id . PHP_EOL;
    echo "Opening PKR: " . $s->opening_balance_pkr . " SAR: " . $s->opening_balance_sar . PHP_EOL;
    $txs = App\Models\ExpenseTransaction::where('supplier_id', $s->id)->get();
    foreach ($txs as $t) {
        echo "ExpenseTx ID: " . $t->id . " Type: " . $t->transaction_type . " Amount: " . $t->amount . " " . $t->currency . " Rate: " . $t->exchange_rate . " Desc: " . $t->description . " Date: " . $t->date . PHP_EOL;
    }

    $bH = App\Models\BookingHotel::where('supplier_id', $s->id)->get();
    echo "Booking Hotels count: " . $bH->count() . PHP_EOL;
    foreach ($bH as $h) {
        echo "Hotel ID: " . $h->id . " Booking ID: " . $h->booking_id . " Cost Amount: " . $h->cost_amount . " Rate: " . $h->cost_per_night . " N: " . $h->no_of_nights . " R: " . $h->no_of_rooms . " ROE: " . $h->cost_roe . " Curr: " . $h->currency . PHP_EOL;
    }

    $bF = App\Models\Booking::where('flight_supplier_id', $s->id)->orWhere('return_flight_supplier_id', $s->id)->get();
    echo "Booking Flights count: " . $bF->count() . PHP_EOL;
    foreach ($bF as $f) {
        echo "Flight Booking ID: " . $f->id . " Flight Cost: " . $f->flight_cost . " Curr: " . $f->flight_currency . " ROE: " . $f->flight_cost_roe . PHP_EOL;
    }

    $bV = App\Models\BookingVisa::where('supplier_id', $s->id)->get();
    echo "Booking Visa count: " . $bV->count() . PHP_EOL;
    foreach ($bV as $v) {
        echo "Visa ID: " . $v->id . " Booking ID: " . $v->booking_id . " Cost: " . $v->cost_amount . " Curr: " . $v->currency . " ROE: " . $v->cost_roe . PHP_EOL;
    }

    $bT = App\Models\BookingTransport::where('supplier_id', $s->id)->get();
    echo "Booking Transport count: " . $bT->count() . PHP_EOL;
    foreach ($bT as $t) {
        echo "Transport ID: " . $t->id . " Booking ID: " . $t->booking_id . " Cost: " . $t->cost_amount . " Curr: " . $t->currency . " ROE: " . $t->cost_roe . PHP_EOL;
    }
}
