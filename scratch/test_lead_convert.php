<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\Client;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ClientController;

echo "=== TEST 1: CONVERTING LEAD IN UMRAH MODE ===\n";
session(['dashboard_package' => 'umrah']);
$leadUmrah = Lead::create([
    'contact_person' => 'Umrah Lead Convert Person',
    'phone' => '03001111111',
]);
$leadCtrl = new LeadController();
$leadCtrl->convertToClient(request(), $leadUmrah->id);

$clientUmrah = Client::where('lead_id', $leadUmrah->id)->first();
echo "Client created! ID: {$clientUmrah->id} | Name: {$clientUmrah->name} | PackageType: '{$clientUmrah->package_type}'\n";

session(['dashboard_package' => 'umrah']);
$clientCtrl = new ClientController();
$clientsUmrah = $clientCtrl->index()->getData()['clients'];
$foundInUmrah = $clientsUmrah->where('id', $clientUmrah->id)->count() > 0;
echo "Found in UMRAH listing: " . ($foundInUmrah ? "YES" : "NO") . "\n";

session(['dashboard_package' => 'hajj']);
$clientsHajj = $clientCtrl->index()->getData()['clients'];
$foundInHajj = $clientsHajj->where('id', $clientUmrah->id)->count() > 0;
echo "Found in HAJJ listing: " . ($foundInHajj ? "YES" : "NO") . "\n";

echo "\n=== TEST 2: CONVERTING LEAD IN HAJJ MODE ===\n";
session(['dashboard_package' => 'hajj']);
$leadHajj = Lead::create([
    'contact_person' => 'Hajj Lead Convert Person',
    'phone' => '03002222222',
]);
$leadCtrl->convertToClient(request(), $leadHajj->id);

$clientHajj = Client::where('lead_id', $leadHajj->id)->first();
echo "Client created! ID: {$clientHajj->id} | Name: {$clientHajj->name} | PackageType: '{$clientHajj->package_type}'\n";

session(['dashboard_package' => 'hajj']);
$clientsHajj = $clientCtrl->index()->getData()['clients'];
$foundInHajj = $clientsHajj->where('id', $clientHajj->id)->count() > 0;
echo "Found in HAJJ listing: " . ($foundInHajj ? "YES" : "NO") . "\n";

session(['dashboard_package' => 'umrah']);
$clientsUmrah = $clientCtrl->index()->getData()['clients'];
$foundInUmrah = $clientsUmrah->where('id', $clientHajj->id)->count() > 0;
echo "Found in UMRAH listing: " . ($foundInUmrah ? "YES" : "NO") . "\n";

echo "\n=== TEST 3: ALL CLIENTS FILTER TAB ===\n";
$reqAll = new \Illuminate\Http\Request(['package_type' => 'all']);
$clientsAll = $clientCtrl->index($reqAll)->getData()['clients'];
echo "Total in 'all' view: " . $clientsAll->count() . "\n";

// Clean up
$clientUmrah->forceDelete();
$leadUmrah->forceDelete();
$clientHajj->forceDelete();
$leadHajj->forceDelete();
echo "\nAll test records cleaned up successfully.\n";
