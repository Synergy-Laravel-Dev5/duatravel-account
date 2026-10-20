<?php

$zipFile = __DIR__ . '/../duatravel-account.zip';
if (!file_exists($zipFile)) {
    die("Error: duatravel-account.zip not found!\n");
}

$zip = new ZipArchive();
if ($zip->open($zipFile) !== true) {
    die("Error: Cannot open duatravel-account.zip!\n");
}

$filesToUpdate = [
    'app/Http/Controllers/BookingController.php',
    'app/Http/Controllers/ClientController.php',
    'app/Http/Controllers/ProfitLossReportController.php',
    'app/Http/Controllers/BankTransferController.php',
    'app/Http/Controllers/CashReportController.php',
    'app/Http/Controllers/TransactionReportController.php',
    'app/Http/Controllers/TransactionController.php',
    'app/Http/Controllers/SupplierController.php',
    'app/Models/BookingHotel.php',
    'app/Models/BookingPerson.php',
    'app/Models/BookingTransport.php',
    'resources/views/booking/create.blade.php',
    'resources/views/booking/edit.blade.php',
    'resources/views/booking/show.blade.php',
    'resources/views/booking/voucher.blade.php',
    'resources/views/pilgrim/epass.blade.php',
    'resources/views/client/create.blade.php',
    'resources/views/client/edit.blade.php',
    'resources/views/client/index.blade.php',
    'resources/views/client/trash.blade.php',
    'resources/views/layout/header.blade.php',
    'resources/views/reports/profit_loss.blade.php',
    'resources/views/bank_transfer/report.blade.php',
    'resources/views/bank_transfer/report_filter.blade.php',
    'resources/views/bank_transfer/report_pdf.blade.php',
    'resources/views/reports/cash_report.blade.php',
    'resources/views/reports/cash_report_pdf.blade.php',
    'resources/views/reports/transaction_report.blade.php',
    'resources/views/transaction/ledger.blade.php',
    'resources/views/suppliers/ledger.blade.php',
    'resources/views/transaction/company-ledger.blade.php',
    'routes/web.php',
    'database/migrations/2026_10_19_140000_add_flight_fields_to_booking_persons_table.php',
    'database/migrations/2026_10_19_153000_add_person_rates_to_booking_hotels_table.php',
    'database/migrations/2026_10_19_173000_add_travel_and_flight_details_to_booking_transports_table.php',
    'database/migrations/2026_10_19_174000_add_voucher_columns_to_booking_transports_table.php',
    'database/seeders/MasterDataSeeder.php',
    'database/seeders/seed_airlines_routes.sql',
];

$baseDir = realpath(__DIR__ . '/..');

foreach ($filesToUpdate as $relPath) {
    $fullPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
    if (!file_exists($fullPath)) {
        echo "Warning: File not found: $fullPath\n";
        continue;
    }
    // ZipArchive uses forward slashes
    $zip->addFile($fullPath, str_replace('\\', '/', $relPath));
    echo "Added/Updated in zip: $relPath\n";
}

$zip->close();
echo "Successfully updated duatravel-account.zip! Total files updated: " . count($filesToUpdate) . "\n";
