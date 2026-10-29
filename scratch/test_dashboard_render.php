<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use App\Models\User;

$user = User::first();
if ($user) {
    \Auth::login($user);
}

$dc = new DashboardController();
$req = new Request();
$res = $dc->index($req);

echo "Dashboard rendered: " . strlen($res->render()) . " bytes OK\n";
