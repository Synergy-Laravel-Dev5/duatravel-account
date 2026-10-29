<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = \Illuminate\Support\Facades\Schema::getColumnListing('accounts');
print_r($cols);

$first = \App\Models\Account::first();
if ($first) {
    echo "\nFirst account:\n";
    print_r($first->toArray());
}
