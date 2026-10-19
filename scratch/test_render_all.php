<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
view()->share('errors', new \Illuminate\Support\ViewErrorBag());
auth()->login(App\Models\User::first());

$suppliers = App\Models\Supplier::all();
$routes = App\Models\TravelRoute::all();
$airlines = App\Models\Airline::all();
$clients = App\Models\Client::all();
$companies = App\Models\Company::all();
$packages = App\Models\Package::all();
$hotels = App\Models\Hotel::all();
$banks = App\Models\Bank::all();

echo "Testing create view... ";
$viewCreate = view('booking.create', compact('suppliers', 'routes', 'airlines', 'clients', 'companies', 'packages', 'banks', 'hotels'))->render();
file_put_contents(__DIR__ . '/rendered_create.html', $viewCreate);
echo "OK (" . strlen($viewCreate) . " bytes)\n";

$booking = App\Models\Booking::with(['transports.supplier', 'hotels.hotel', 'persons'])->first();
if ($booking) {
    echo "Testing edit view... ";
    $viewEdit = view('booking.edit', compact('booking', 'suppliers', 'routes', 'airlines', 'clients', 'companies', 'packages', 'banks', 'hotels'))->render();
    file_put_contents(__DIR__ . '/rendered_edit.html', $viewEdit);
    echo "OK (" . strlen($viewEdit) . " bytes)\n";

    echo "Testing show view... ";
    $viewShow = view('booking.show', compact('booking'))->render();
    echo "OK (" . strlen($viewShow) . " bytes)\n";

    echo "Testing voucher view... ";
    $viewVoucher = view('booking.voucher', compact('booking'))->render();
    echo "OK (" . strlen($viewVoucher) . " bytes)\n";
}
echo "ALL TESTS PASSED!\n";
