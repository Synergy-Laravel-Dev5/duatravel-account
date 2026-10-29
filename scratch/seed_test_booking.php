<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = App\Models\Client::first();
$b = App\Models\Booking::create([
    'client_id' => $client ? $client->id : 1,
    'booking_date' => date('Y-m-d'),
    'booking_category' => 'umrah',
    'package_type' => 'umrah',
    'package_year' => date('Y'),
    'currency' => 'PKR',
    'total_amount' => 150000,
    'balance' => 150000,
    'status' => 'pending',
    'voucher_number' => 'BK-' . date('Y') . '-00001',
    'flight_voucher_no' => 'FLT-' . date('Y') . '-00001',
    'hotel_voucher_no' => 'HTL-' . date('Y') . '-00001',
    'transport_voucher_no' => 'TRN-' . date('Y') . '-00001',
    'visa_voucher_no' => 'VIS-' . date('Y') . '-00001',
]);
echo "Created test booking #{$b->id}\n";
