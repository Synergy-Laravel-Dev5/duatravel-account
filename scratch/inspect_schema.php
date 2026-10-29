<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== BOOKINGS COLUMNS ===\n";
$bCols = Schema::getColumnListing('bookings');
echo "pax_adults: " . (in_array('pax_adults', $bCols) ? 'YES' : 'NO') . "\n";
echo "pax_children: " . (in_array('pax_children', $bCols) ? 'YES' : 'NO') . "\n";
echo "pax_infants: " . (in_array('pax_infants', $bCols) ? 'YES' : 'NO') . "\n";
echo "flight_time_format: " . (in_array('flight_time_format', $bCols) ? 'YES' : 'NO') . "\n";
echo "flight_voucher_no: " . (in_array('flight_voucher_no', $bCols) ? 'YES' : 'NO') . "\n";
echo "hotel_voucher_no: " . (in_array('hotel_voucher_no', $bCols) ? 'YES' : 'NO') . "\n";
echo "transport_voucher_no: " . (in_array('transport_voucher_no', $bCols) ? 'YES' : 'NO') . "\n";
echo "visa_voucher_no: " . (in_array('visa_voucher_no', $bCols) ? 'YES' : 'NO') . "\n";

echo "\n=== BANKS COLUMNS ===\n";
print_r(Schema::getColumnListing('banks'));

echo "\n=== ACCOUNTS COLUMNS ===\n";
if (Schema::hasTable('accounts')) {
    print_r(Schema::getColumnListing('accounts'));
} else {
    echo "No accounts table\n";
}

echo "\n=== TRANSACTIONS COLUMNS ===\n";
print_r(Schema::getColumnListing('transactions'));

echo "\n=== ALL TABLES MATCHING ===\n";
$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
foreach ($tables as $t) {
    foreach ($t as $k => $v) {
        if (strpos($v, 'pack') !== false || strpos($v, 'type') !== false || strpos($v, 'cash') !== false || strpos($v, 'acc') !== false) {
            echo "Matched table: $v\n";
        }
    }
}
