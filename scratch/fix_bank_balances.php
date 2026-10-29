<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bank;

$banks = Bank::all();
foreach ($banks as $b) {
    $b->opening_balance_sar = 0.00;
    $b->opening_balance_roe = 1.0000;
    $b->save();
    echo "Updated Bank ID: {$b->id} | Name: {$b->name} | PKR: {$b->opening_balance_pkr} | SAR: {$b->opening_balance_sar}\n";
}
echo "All banks updated to PKR only opening balance.\n";
