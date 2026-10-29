<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$user = App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);
view()->share('errors', new \Illuminate\Support\ViewErrorBag());

$controller = new App\Http\Controllers\BookingController();
$html = $controller->edit(1)->render();

$requiredIds = [
    'bookingForm',
    'packageSelect',
    'clientSelect',
    'no_of_pax',
    'pax_adults',
    'pax_children',
    'pax_infants',
    'flightPersonsList',
    'flight_currency',
    'flight_cost_roe',
    'flight_sale_roe',
    'flight_cost',
    'flight_charges',
    'default_visa_supplier_id',
    'default_visa_currency',
    'default_visa_cost_roe',
    'default_visa_sale_roe',
    'default_visa_cost_rate',
    'default_visa_sell_rate',
    'visasList',
    'visa_charges',
    'hotel_charges',
    'transport_charges',
    'total_amount',
];

echo "Checking required IDs in rendered edit view:\n";
foreach ($requiredIds as $id) {
    $found = strpos($html, 'id="' . $id . '"') !== false;
    echo ($found ? "[OK]" : "[MISSING]") . " id=\"{$id}\"\n";
}
