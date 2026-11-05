<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\Client;
use App\Models\Package;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

$user = User::first();
if ($user) Auth::login($user);

View::share('errors', new ViewErrorBag());

$booking = Booking::with(['client', 'persons', 'hotels', 'visas', 'transports'])->first();
if (!$booking) {
    echo "No booking found!\n";
    exit(1);
}

$clients = Client::all();
$packages = Package::all();
$suppliers = Supplier::all();
$hotels = \App\Models\Hotel::all();
$companies = [];
$mealPlans = [];
$airlines = [];
$transportTypes = [];
$routes = [];
$defaultVouchers = [
    'flight' => 'FLT-2026-00001',
    'hotel' => 'HTL-2026-00001',
    'visa' => 'VIS-2026-00001',
    'transport' => 'TRN-2026-00001'
];
$transactionsPaid = 0;

$html = view('booking.edit', compact(
    'booking', 'clients', 'packages', 'hotels', 'suppliers', 'companies', 'mealPlans', 'airlines', 'transportTypes', 'routes', 'defaultVouchers', 'transactionsPaid'
))->render();

file_put_contents('scratch/rendered_edit_fresh.html', $html);
echo "Rendered successfully! File size: " . strlen($html) . " bytes\n";
