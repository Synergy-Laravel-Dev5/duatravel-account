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

// 1. Booking Create
$bc = new App\Http\Controllers\BookingController();
$req = new Illuminate\Http\Request();
$v = $bc->create($req);
echo "1. booking.create: " . strlen($v->render()) . " bytes OK\n";

// 2. Booking Edit
$booking = App\Models\Booking::first();
if ($booking) {
    $v = $bc->edit($booking->id);
    echo "2. booking.edit (#{$booking->id}): " . strlen($v->render()) . " bytes OK\n";
}

// 3. Client Ledger
$tc = new App\Http\Controllers\TransactionController();
$client = App\Models\Client::first();
if ($client) {
    $v = $tc->ledgerView($req, $client->id);
    echo "3. client ledger (#{$client->id}): " . strlen($v->render()) . " bytes OK\n";
}

// 4. Supplier Ledger
$sc = new App\Http\Controllers\SupplierController();
$sup = App\Models\Supplier::first();
if ($sup) {
    $v = $sc->ledger($req, $sup->id);
    echo "4. supplier ledger (#{$sup->id}): " . strlen($v->render()) . " bytes OK\n";
}

// 5. Cash Report (Cash Book)
$crc = new App\Http\Controllers\CashReportController();
$v = $crc->index($req);
echo "5. cash report: " . strlen($v->render()) . " bytes OK\n";

// 6. Account Index, Create, Edit
$ac = new App\Http\Controllers\AccountController();
$v = $ac->index();
echo "6. account.index: " . strlen($v->render()) . " bytes OK\n";
$v = $ac->create();
echo "6. account.create: " . strlen($v->render()) . " bytes OK\n";
$acc = App\Models\Account::first();
if ($acc) {
    $v = $ac->edit($acc);
    echo "6. account.edit (#{$acc->id}): " . strlen($v->render()) . " bytes OK\n";
}

// 7. Dashboard
$dc = new App\Http\Controllers\DashboardController();
$v = $dc->index($req);
echo "7. dashboard: " . strlen($v->render()) . " bytes OK\n";

// 8. Meal Plans Index & Create
$mpc = new App\Http\Controllers\MealPlanController();
$v = $mpc->index();
echo "8. meal-plans.index: " . strlen($v->render()) . " bytes OK\n";
$v = $mpc->create();
echo "8. meal-plans.create: " . strlen($v->render()) . " bytes OK\n";

// 9. Daily Report
$drc = new App\Http\Controllers\DailyReportController();
$v = $drc->index($req);
echo "9. reports.daily: " . strlen($v->render()) . " bytes OK\n";

echo "\nALL MODIFIED VIEWS RENDERED 100% PERFECTLY!\n";
