<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

view()->share('errors', new Illuminate\Support\ViewErrorBag());
$user = App\Models\User::first();
if ($user) {
    Illuminate\Support\Facades\Auth::login($user);
}

$bc = new App\Http\Controllers\BookingController();
$req = new Illuminate\Http\Request();

$booking = App\Models\Booking::first();
if ($booking) {
    $v = $bc->edit($booking->id);
    echo "booking.edit (#{$booking->id}): " . strlen($v->render()) . " bytes OK\n";
} else {
    echo "No booking found in DB!\n";
}
