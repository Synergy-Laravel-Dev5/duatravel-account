<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag());

$user = \App\Models\User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
}

try {
    $controller = new \App\Http\Controllers\BookingController();
    $response = $controller->edit(7);
    $html = $response->render();
    echo "SUCCESS, rendered length: " . strlen($html) . " bytes\n";
    file_put_contents(__DIR__ . '/rendered_edit_7.html', $html);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
