<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$user = App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);
view()->share('errors', new \Illuminate\Support\ViewErrorBag());

$controller = new App\Http\Controllers\BookingController();
$html = $controller->edit(1)->render();

preg_match('/name="hotels\[0\]\[selling_per_night\]"[^>]*value="([^"]*)"/', $html, $m);
echo "selling_per_night input value in Blade: " . ($m[1] ?? 'NOT FOUND') . "\n";

preg_match('/data-person-rates=\'([^\']*)\'/', $html, $m2);
echo "data-person-rates in Blade: " . ($m2[1] ?? 'NOT FOUND') . "\n";
