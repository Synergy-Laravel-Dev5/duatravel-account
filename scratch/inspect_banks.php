<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$banks = App\Models\Bank::all();
foreach ($banks as $b) {
    echo "Bank ID: {$b->id} | Name: {$b->name} | PKR: {$b->opening_balance_pkr} | SAR: {$b->opening_balance_sar} | ROE: {$b->opening_balance_roe}\n";
}
