<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Bank;

Auth::login(User::first());

$bank = Bank::first();

echo "Testing view: bank.index... ";
$html = View::make('bank.index', ['banks' => Bank::all()])->render();
echo "OK (" . strlen($html) . " bytes)\n";

echo "Testing view: bank.create... ";
$html = View::make('bank.create')->render();
echo "OK (" . strlen($html) . " bytes)\n";

echo "Testing view: bank.edit... ";
$html = View::make('bank.edit', ['bank' => $bank])->render();
echo "OK (" . strlen($html) . " bytes)\n";

echo "=== ALL BANK VIEWS PASSED 100% ===\n";
