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
    'app/Models/BookingHotel.php',
    'app/Models/BookingPerson.php',
    'resources/views/booking/create.blade.php',
    'resources/views/booking/edit.blade.php',
    'resources/views/client/create.blade.php',
    'resources/views/client/edit.blade.php',
    'resources/views/client/index.blade.php',
    'resources/views/client/trash.blade.php',
    'resources/views/layout/header.blade.php',
    'resources/views/reports/profit_loss.blade.php',
    'database/migrations/2026_10_19_140000_add_flight_fields_to_booking_persons_table.php',
    'database/migrations/2026_10_19_153000_add_person_rates_to_booking_hotels_table.php',
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
