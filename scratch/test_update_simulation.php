<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

$b = App\Models\Booking::with(['persons', 'hotels', 'visas', 'transports'])->find(1);

$postData = [
    'booking_for' => 'client',
    'client_id' => $b->client_id,
    'no_of_pax' => $b->no_of_pax,
    'booking_date' => $b->booking_date,
    'currency' => $b->currency ?? 'PKR',
    'persons' => [
        [
            'pax_type' => 'adult',
            'surname' => 'Arsal',
            'given_name' => 'Ali',
            'full_name' => 'Arsal Ali',
            'passport_number' => 'AA545545545',
            'dob' => '2003-12-04',
            'gender' => 'male',
            'city' => 'karachi',
        ],
        [
            'pax_type' => 'adult',
            'surname' => 'D',
            'given_name' => 'arsal',
            'full_name' => 'D arsal',
            'passport_number' => '21412424',
            'dob' => null,
            'gender' => 'female',
            'city' => null,
        ]
    ],
    'flight_supplier_id' => '1',
    'flight_currency' => 'SAR',
    'flight_cost_roe' => '75.0000',
    'flight_sale_roe' => '76.0000',
    'flight_cost_rate' => '104000.00',
    'flight_cost' => '104000.00',
    'flight_selling_rate' => '120000.00',
    'flight_charges' => '120000.00',
    'departure_date' => '2026-10-01',
    'flight_persons' => [
        [
            'name' => 'Arsal Ali',
            'passport' => 'AA545545545',
            'pax_type' => 'adult',
            'flight_supplier_id' => '1',
            'ticket' => '867',
            'flight_pnr' => '87',
            'flight_date' => '2026-10-01',
            'flight_time' => '21:30',
            'flight_cost' => '52000.00',
            'flight_sell' => '60000.00',
        ],
        [
            'name' => 'D arsal',
            'passport' => '21412424',
            'pax_type' => 'adult',
            'flight_supplier_id' => '3',
            'ticket' => '200',
            'flight_pnr' => '867',
            'flight_date' => '2026-10-01',
            'flight_time' => '21:30',
            'flight_cost' => '52000.00',
            'flight_sell' => '60000.00',
        ]
    ],
    'hotels' => [
        [
            'location' => 'madinah',
            'check_in' => '2026-10-01',
            'check_out' => '2026-10-14',
            'no_of_nights' => '13',
            'booking_date' => '2026-01-01',
            'option_date' => '2026-10-27',
            'hotel_name' => 'Pullman Zamzam Makkah',
            'supplier_id' => '1',
            'currency' => 'SAR',
            'cost_roe' => '75.0000',
            'roe' => '75',
            'sale_roe' => '76.0000',
            'room_type' => 'double',
            'no_of_rooms' => '1',
            'cost_per_night' => '100.00',
            'selling_per_night' => '150.00',
            'cost_amount' => '97500.00',
            'selling_amount' => '148200.00',
            'person_rates' => [
                ['name' => 'Arsal Ali', 'cost_per_night' => '50.00', 'total_cost' => '48750.00', 'selling_per_night' => '75.00', 'total_selling' => '74100.00'],
                ['name' => 'D arsal', 'cost_per_night' => '50.00', 'total_cost' => '48750.00', 'selling_per_night' => '75.00', 'total_selling' => '74100.00'],
            ]
        ]
    ],
    'visas' => [
        [
            'passport_number' => 'AA545545545',
            'given_name' => 'Arsal Ali',
            'date_of_birth' => '2003-12-04',
            'supplier_id' => '1',
            'currency' => 'SAR',
            'cost_roe' => '75.0000',
            'sale_roe' => '76.0000',
            'cost_rate' => '250.00',
            'cost_amount' => '18750.00',
            'selling_rate' => '300.00',
            'selling_amount' => '22800.00',
            'booking_date' => '2026-10-27',
            'status' => 'approved',
        ],
        [
            'passport_number' => '21412424',
            'given_name' => 'D arsal',
            'date_of_birth' => null,
            'supplier_id' => '1',
            'currency' => 'SAR',
            'cost_roe' => '75.0000',
            'sale_roe' => '76.0000',
            'cost_rate' => '250.00',
            'cost_amount' => '18750.00',
            'selling_rate' => '300.00',
            'selling_amount' => '22800.00',
            'booking_date' => '2026-10-27',
            'status' => 'pending',
        ]
    ],
    'hotel_charges' => '148200.00',
    'visa_charges' => '45600.00',
    'flight_charges' => '120000.00',
    'transport_charges' => '0.00',
    'package_cost' => '0.00',
    'other_charges' => '0.00',
    'discount' => '0.00',
    'total_amount' => '313800.00',
    'total_received' => '0.00',
    'balance' => '313800.00',
];

$request = \Illuminate\Http\Request::create('/booking/update/1', 'PUT', $postData);
$request->setLaravelSession(app('session.store'));

$controller = new App\Http\Controllers\BookingController();
try {
    $response = $controller->update($request, 1);
    echo "STATUS: " . ($response->getStatusCode() ?? 200) . "\n";
    if ($response->isRedirect()) {
        echo "REDIRECT TARGET: " . $response->getTargetUrl() . "\n";
        echo "SESSION FLASH: " . json_encode(session()->all()) . "\n";
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
