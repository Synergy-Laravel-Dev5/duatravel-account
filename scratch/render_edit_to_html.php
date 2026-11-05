<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

view()->share('errors', new \Illuminate\Support\ViewErrorBag());
$res = (new App\Http\Controllers\BookingController())->edit(1);
file_put_contents('scratch/edit_rendered.html', $res->render());
echo 'Rendered: ' . filesize('scratch/edit_rendered.html') . "\n";
