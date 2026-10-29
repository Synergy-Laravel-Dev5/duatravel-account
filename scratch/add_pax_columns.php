<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Checking bookings table for pax breakdown columns...\n";
if (!Schema::hasColumn('bookings', 'pax_adults')) {
    Schema::table('bookings', function (Blueprint $table) {
        $table->integer('pax_adults')->default(1)->after('no_of_pax');
        $table->integer('pax_children')->default(0)->after('pax_adults');
        $table->integer('pax_infants')->default(0)->after('pax_children');
    });
    echo "Added pax_adults, pax_children, pax_infants to bookings table.\n";
} else {
    echo "Columns already exist in bookings table.\n";
}

echo "\nChecking packages table schema:\n";
$col = Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM packages LIKE 'package_type'");
print_r($col);
