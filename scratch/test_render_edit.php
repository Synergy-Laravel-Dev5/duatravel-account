<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

$controller = new App\Http\Controllers\BookingController();
try {
    view()->share('errors', new \Illuminate\Support\ViewErrorBag());
    $res = $controller->edit(1);
    $html = $res->render();
    echo "SUCCESS_RENDER: Length " . strlen($html) . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
