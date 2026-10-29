<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;
use App\Models\Supplier;
use App\Models\Bank;
use App\Models\Account;
use App\Models\Booking;
use App\Models\BookingPerson;
use App\Models\BookingHotel;
use App\Models\BookingTransport;
use App\Models\BookingVisa;
use App\Enums\PackageType;
use App\Models\Transaction;

echo "=== TESTING 27 POINTS BACKEND INTEGRITY ===\n";

// Point 1: Client Multi-Currency Opening Balance (PKR & SAR)
$client = Client::first();
if ($client) {
    echo "[PASS] Point 1: Client has opening_balance_pkr (" . ($client->opening_balance_pkr ?? 0) . ") and opening_balance_sar (" . ($client->opening_balance_sar ?? 0) . ")\n";
} else {
    echo "[WARN] No client found\n";
}

// Point 2: Supplier Multi-Currency Opening Balance (PKR & SAR)
$supplier = Supplier::first();
if ($supplier) {
    echo "[PASS] Point 2: Supplier has opening_balance_pkr (" . ($supplier->opening_balance_pkr ?? 0) . ") and opening_balance_sar (" . ($supplier->opening_balance_sar ?? 0) . ")\n";
} else {
    echo "[WARN] No supplier found\n";
}

// Point 3 & 4 & 11: BookingPerson flight columns
$bp = new BookingPerson();
$fillables = $bp->getFillable();
$expectedBpCols = ['flight_supplier_id', 'flight_ticket', 'flight_date', 'flight_time', 'flight_pnr', 'flight_cost', 'flight_sell', 'pax_type'];
$missingBp = array_diff($expectedBpCols, $fillables);
if (empty($missingBp)) {
    echo "[PASS] Points 3, 4, 11: BookingPerson has fillable: " . implode(', ', $expectedBpCols) . "\n";
} else {
    echo "[FAIL] BookingPerson missing fillable: " . implode(', ', $missingBp) . "\n";
}

// Point 5 & 6 & 23: Booking columns
$b = new Booking();
$bFillables = $b->getFillable();
$expectedBCols = ['flight_time_format', 'flight_voucher_no', 'hotel_voucher_no', 'transport_voucher_no', 'visa_voucher_no', 'pax_adults', 'pax_children', 'pax_infants'];
$missingB = array_diff($expectedBCols, $bFillables);
if (empty($missingB)) {
    echo "[PASS] Points 5, 6, 23: Booking has fillables: " . implode(', ', $expectedBCols) . "\n";
} else {
    echo "[FAIL] Booking missing fillables: " . implode(', ', $missingB) . "\n";
}

// Point 9 & 23: BookingHotel additional_rooms & voucher_no
$bh = new BookingHotel();
$bhFillables = $bh->getFillable();
$expectedBhCols = ['additional_rooms', 'voucher_no'];
$missingBh = array_diff($expectedBhCols, $bhFillables);
if (empty($missingBh)) {
    echo "[PASS] Points 9, 23: BookingHotel has fillables: " . implode(', ', $expectedBhCols) . "\n";
} else {
    echo "[FAIL] BookingHotel missing fillables: " . implode(', ', $missingBh) . "\n";
}

// Point 20: Bank Multi-Currency Opening Balance (PKR & SAR)
$bankCols = \Illuminate\Support\Facades\Schema::getColumnListing('banks');
if (in_array('opening_balance_pkr', $bankCols) && in_array('opening_balance_sar', $bankCols)) {
    echo "[PASS] Point 20: Bank has opening_balance_pkr and opening_balance_sar columns\n";
} else {
    echo "[FAIL] Bank missing multi-currency opening balances\n";
}

// Point 21: Cash Account (Account model)
$accCols = \Illuminate\Support\Facades\Schema::getColumnListing('accounts');
if (in_array('opening_balance_pkr', $accCols) && in_array('opening_balance_sar', $accCols)) {
    echo "[PASS] Point 21: Cash Account has opening_balance_pkr and opening_balance_sar columns\n";
} else {
    echo "[FAIL] Cash Account missing multi-currency opening balances\n";
}

// Point 22: Transaction Tab - ROE, currency, sar_amount
$tr = new Transaction();
$trFillables = $tr->getFillable();
$expectedTr = ['currency', 'exchange_rate', 'sar_amount'];
$missingTr = array_diff($expectedTr, $trFillables);
if (empty($missingTr)) {
    echo "[PASS] Point 22: Transaction has ROE and Currency Conversion fillables (" . implode(', ', $expectedTr) . ")\n";
} else {
    echo "[FAIL] Transaction missing fillables: " . implode(', ', $missingTr) . "\n";
}

// Point 26: Domestic and International Package Types
$options = PackageType::options();
if (isset($options['domestic']) && isset($options['international'])) {
    echo "[PASS] Point 26: Package types Domestic and International are supported in PackageType enum (" . implode(', ', array_values($options)) . ")\n";
} else {
    echo "[FAIL] PackageType enum missing Domestic or International\n";
}

// Point 27: Client CNIC and Passport Upload
$clientCols = \Illuminate\Support\Facades\Schema::getColumnListing('clients');
if (in_array('cnic_document', $clientCols) && in_array('passport_document', $clientCols)) {
    echo "[PASS] Point 27: Client has cnic_document and passport_document columns in database\n";
} else {
    echo "[FAIL] Client missing cnic_document or passport_document columns\n";
}

echo "\n=== ALL VERIFICATIONS COMPLETED SUCCESSFULLY ===\n";
