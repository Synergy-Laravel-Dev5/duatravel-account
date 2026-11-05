<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::first();
if ($user) Auth::login($user);

$booking = App\Models\Booking::with(['package', 'persons', 'hotels.supplier', 'transports', 'visas.supplier', 'flightSupplier', 'client', 'company'])->first();
if (!$booking) {
    echo "No booking found\n";
    exit;
}
$clients = App\Models\Client::all();
$companies = App\Models\Company::all();
$packages = App\Models\Package::all();
$hotels = App\Models\Hotel::all();
$suppliers = App\Models\Supplier::all();
$airlines = App\Models\Airline::all();
$routes = App\Models\Route::all();
$vehicles = App\Models\Vehicle::all();
$mealPlans = App\Models\MealPlan::all();
$years = [date('Y'), date('Y')+1];
$transactionsPaid = 0;
$defaultVouchers = [
    'booking' => 'BK-2026-00001',
    'flight' => 'FLT-2026-00001',
    'hotel' => 'HTL-2026-00001',
    'transport' => 'TRN-2026-00001',
    'visa' => 'VIS-2026-00001',
];

$view = view('booking.edit', compact(
    'booking', 'clients', 'companies', 'packages', 'hotels', 'suppliers',
    'years', 'transactionsPaid', 'airlines', 'routes', 'vehicles', 'mealPlans', 'defaultVouchers'
))->with('errors', new \Illuminate\Support\ViewErrorBag())->render();

$view = str_replace('http://localhost/assets/', 'file:///d:/xampp/htdocs/project/Duatravel-account/public/assets/', $view);
file_put_contents(__DIR__ . '/edit_rendered.html', $view);
echo "Rendered successfully: " . strlen($view) . " bytes\n";
