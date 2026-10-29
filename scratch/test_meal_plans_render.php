<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\MealPlanController;
use App\Models\User;

$user = User::first();
if ($user) {
    \Auth::login($user);
}

$mpc = new MealPlanController();
$res = $mpc->index();

echo "Meal Plans index rendered: " . strlen($res->render()) . " bytes OK\n";
