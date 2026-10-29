<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\ProfitLossReportController;

Auth::login(User::first());

$controller = new ProfitLossReportController();
$req = new Request(['package_type' => 'all']);
$response = $controller->index($req);

$html = $response->render();
// Extract the statement table
if (preg_match('/<table class="table table-bordered table-sm align-middle fs-13 mb-0 statement-table"[^>]*>(.*?)<\/table>/s', $html, $matches)) {
    // Strip tags and print clean lines
    $lines = explode("\n", strip_tags($matches[0]));
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (!empty($trimmed)) {
            echo $trimmed . "\n";
        }
    }
}
