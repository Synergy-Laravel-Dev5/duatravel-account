<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== bookings columns ===\n";
print_r(Schema::getColumnListing('bookings'));

echo "\n=== booking_hotels columns ===\n";
print_r(Schema::getColumnListing('booking_hotels'));

echo "\n=== transactions columns ===\n";
print_r(Schema::getColumnListing('transactions'));

echo "\n=== banks columns ===\n";
print_r(Schema::getColumnListing('banks'));
