<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DailyReportController;
use Illuminate\Http\Request;
use App\Models\User;

$user = User::first();
if ($user) {
    \Auth::login($user);
}

$drc = new DailyReportController();
$req = new Request();
$res = $drc->index($req);

echo "Daily Report rendered: " . strlen($res->render()) . " bytes OK\n";
