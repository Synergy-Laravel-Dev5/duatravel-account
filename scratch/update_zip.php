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
    'app/Http/Controllers/CheckInReportController.php',
    'resources/views/reports/checkin_report.blade.php',
    'resources/views/expense_transaction/index.blade.php',
    'routes/web.php',
    'database/migrations/2026_10_19_140000_add_flight_fields_to_booking_persons_table.php',
    'database/migrations/2026_10_19_153000_add_person_rates_to_booking_hotels_table.php',
    'database/migrations/2026_10_19_173000_add_travel_and_flight_details_to_booking_transports_table.php',
    'database/migrations/2026_10_19_174000_add_voucher_columns_to_booking_transports_table.php',
    'database/migrations/2026_10_22_140000_create_travel_groups_table.php',
    'database/migrations/2026_10_22_141000_create_haji_groups_table.php',
    'app/Http/Controllers/RoomingListController.php',
    'app/Http/Controllers/HajiGroupController.php',
    'app/Http/Controllers/ArrivalGroupController.php',
    'app/Http/Controllers/DepartureGroupController.php',
    'app/Models/TravelGroup.php',
    'app/Models/HajiGroup.php',
    'app/Models/HajiGroupPerson.php',
    'app/Models/Vehicle.php',
    'app/Models/Route.php',
    'app/Models/TravelRoute.php',
    'app/Helpers/helpers.php',
    'resources/views/reports/rooming_list.blade.php',
    'resources/views/haji_group/create.blade.php',
    'resources/views/haji_group/edit.blade.php',
    'resources/views/haji_group/index.blade.php',
    'resources/views/haji_group/show.blade.php',
    'resources/views/arrival_group/create.blade.php',
    'resources/views/arrival_group/edit.blade.php',
    'resources/views/arrival_group/index.blade.php',
    'resources/views/arrival_group/show.blade.php',
    'resources/views/departure_group/create.blade.php',
    'resources/views/departure_group/edit.blade.php',
    'resources/views/departure_group/index.blade.php',
    'resources/views/departure_group/show.blade.php',
    'database/seeders/MasterDataSeeder.php',
    'database/seeders/seed_airlines_routes.sql',
    'database/migrations/2026_10_22_143000_add_package_type_to_clients_table.php',
    'database/migrations/2026_10_22_194500_add_documents_to_clients_table.php',
    'app/Models/Client.php',
    'app/Http/Controllers/ExpenseTransactionController.php',
    'app/Http/Controllers/LeadController.php',
    'resources/views/lead/show.blade.php',
    'resources/views/lead/trash.blade.php',
    'resources/views/expense_transaction/create.blade.php',
    'resources/views/expense_transaction/edit.blade.php',
    'database/migrations/2026_10_22_150000_create_seasons_table.php',
    'app/Models/Season.php',
    'app/Http/Controllers/SeasonController.php',
    'app/Http/Controllers/DashboardController.php',
    'resources/views/season/index.blade.php',
    'resources/views/season/create.blade.php',
    'resources/views/season/edit.blade.php',
    'resources/views/season/trash.blade.php',
    'resources/views/welcome.blade.php',
    'app/Models/Booking.php',
    'app/Models/BookingVisa.php',
    'app/Models/Bank.php',
    'app/Models/Account.php',
    'app/Models/Transaction.php',
    'app/Models/Supplier.php',
    'app/Enums/PackageType.php',
    'app/Http/Controllers/PackageController.php',
    'app/Http/Controllers/BankController.php',
    'app/Http/Controllers/AccountController.php',
    'resources/views/suppliers/create.blade.php',
    'resources/views/suppliers/edit.blade.php',
    'resources/views/bank/create.blade.php',
    'resources/views/bank/edit.blade.php',
    'resources/views/account/create.blade.php',
    'resources/views/account/edit.blade.php',
    'resources/views/account/index.blade.php',
    'resources/views/transaction/create.blade.php',
    'resources/views/transaction/edit.blade.php',
    'resources/views/package/create.blade.php',
    'resources/views/package/edit.blade.php',
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
