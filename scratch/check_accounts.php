<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Accounts table exists? " . (Schema::hasTable('accounts') ? 'YES' : 'NO') . "\n";
if (Schema::hasTable('accounts')) {
    print_r(Schema::getColumnListing('accounts'));
}
