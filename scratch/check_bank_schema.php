<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = Illuminate\Support\Facades\Schema::getColumnListing('banks');
print_r($cols);
$bank = App\Models\Bank::first();
if ($bank) {
    print_r($bank->toArray());
}
