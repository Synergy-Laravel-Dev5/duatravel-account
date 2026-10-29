<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;

$u = User::first();
if ($u) Auth::login($u);

$html = view('layout.header')->render();
echo "Sidebar & Header rendered successfully! HTML length: " . strlen($html) . "\n";
