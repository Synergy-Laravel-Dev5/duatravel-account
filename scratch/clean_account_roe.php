<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$accounts = \App\Models\Account::where('opening_balance_roe', '<=', 1)->get();
foreach ($accounts as $a) {
    echo "Updating Account #{$a->id} ({$a->account_name}): SAR was {$a->opening_balance_sar} -> set to 0.00\n";
    $a->opening_balance_sar = 0.00;
    $a->save();
}

echo "Done.\n";
