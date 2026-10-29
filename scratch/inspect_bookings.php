<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$bookings = App\Models\Booking::with(['persons', 'hotels', 'visas', 'transports'])->get();
foreach ($bookings as $b) {
    echo "Booking ID: {$b->id} | Ref: {$b->booking_reference} | Pax: {$b->no_of_pax} | Total: {$b->total_amount} | Hotel Chg: {$b->hotel_charges} | Flight Chg: {$b->flight_charges} | Visa Chg: {$b->visa_charges}\n";
    echo "  Persons: " . $b->persons->pluck('full_name')->implode(', ') . "\n";
    foreach ($b->hotels as $h) {
        echo "  Hotel: {$h->hotel_name} | Nights: {$h->no_of_nights} | Rooms: {$h->no_of_rooms} | Cost/nt: {$h->cost_per_night} | Sell/nt: {$h->selling_per_night} | Cost Amt: {$h->cost_amount} | Sell Amt: {$h->selling_amount} | PersonRates: " . json_encode($h->person_rates) . "\n";
    }
    foreach ($b->visas as $v) {
        echo "  Visa: {$v->given_name} | Pass: {$v->passport_number} | Sup: {$v->supplier_id} | Curr: {$v->currency} | CostROE: {$v->cost_roe} | SaleROE: {$v->sale_roe} | CostRate: {$v->cost_rate} | SellRate: {$v->selling_rate} | CostAmt: {$v->cost_amount} | SellAmt: {$v->selling_amount}\n";
    }
}
