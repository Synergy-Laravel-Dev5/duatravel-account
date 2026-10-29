<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$user = App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);
view()->share('errors', new \Illuminate\Support\ViewErrorBag());

$controller = new App\Http\Controllers\BookingController();
$html = $controller->edit(1)->render();

preg_match('/data-person-rates=\'([^\']*)\'/', $html, $m);
if (!empty($m[1])) {
    echo "data-person-rates content:\n" . $m[1] . "\n";
    $decoded = json_decode($m[1], true);
    print_r($decoded);
} else {
    echo "data-person-rates NOT matched!\n";
    preg_match('/data-person-rates[^\s>]*/', $html, $m2);
    print_r($m2);
}
