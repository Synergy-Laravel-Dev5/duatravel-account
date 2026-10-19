<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$airlines = \App\Models\Airline::all();
$routes = \App\Models\Route::all();

$sql = "-- Seed Master Data: Airlines & Routes\n";
$sql .= "-- Generated for Dua Travel System\n\n";

$sql .= "-- 1. AIRLINES (Total: " . $airlines->count() . ")\n";
foreach ($airlines as $a) {
    $name = addslashes($a->name);
    $code = addslashes($a->code ?? '');
    $iata = addslashes($a->iata_code ?? '');
    $icao = addslashes($a->icao_code ?? '');
    $country = addslashes($a->country ?? '');
    $callSign = addslashes($a->call_sign ?? '');
    $status = addslashes($a->status ?? 'active');
    
    $sql .= "INSERT INTO `airlines` (`name`, `code`, `iata_code`, `icao_code`, `country`, `call_sign`, `status`, `created_at`, `updated_at`) ";
    $sql .= "VALUES ('{$name}', '{$code}', '{$iata}', '{$icao}', '{$country}', '{$callSign}', '{$status}', NOW(), NOW()) ";
    $sql .= "ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `iata_code`=VALUES(`iata_code`), `status`='active';\n";
}

$sql .= "\n-- 2. ROUTES (Total: " . $routes->count() . ")\n";
foreach ($routes as $r) {
    $start = addslashes($r->start_place);
    $end = addslashes($r->end_place);
    $status = addslashes($r->status ?? 'active');
    
    $sql .= "INSERT INTO `routes` (`start_place`, `end_place`, `status`, `created_at`, `updated_at`) ";
    $sql .= "SELECT '{$start}', '{$end}', '{$status}', NOW(), NOW() ";
    $sql .= "WHERE NOT EXISTS (SELECT 1 FROM `routes` WHERE `start_place`='{$start}' AND `end_place`='{$end}' AND `deleted_at` IS NULL);\n";
}

file_put_contents(__DIR__ . '/seed_airlines_routes.sql', $sql);
file_put_contents(__DIR__ . '/../database/seeders/seed_airlines_routes.sql', $sql);

echo "Exported SQL successfully! Length: " . strlen($sql) . " bytes\n";
