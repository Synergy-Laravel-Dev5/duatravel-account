<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Staff count: " . App\Models\Staff::count() . "\n";
foreach (App\Models\Staff::all() as $st) {
    echo "Staff #{$st->id}: {$st->first_name} {$st->last_name}\n";
}

echo "\nExpenses count: " . App\Models\Expense::count() . "\n";
foreach (App\Models\Expense::all() as $ex) {
    echo "Expense #{$ex->id}: {$ex->name}\n";
}
