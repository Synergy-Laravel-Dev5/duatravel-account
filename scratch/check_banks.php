<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (App\Models\Bank::all() as $b) {
    echo "ID: {$b->id} | Name: {$b->name} | PKR: {$b->opening_balance_pkr} | SAR: {$b->opening_balance_sar}\n";
}
