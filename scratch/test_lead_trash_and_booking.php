<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\Client;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\BookingController;

echo "=== TEST: LEAD TRASH & BOOKING DROPDOWN EXCLUSION ===\n";

// Step 1: Create a Lead
$lead = Lead::create([
    'contact_person' => 'Dropdown Test Person',
    'phone' => '03009999999',
]);
echo "1. Created Lead #{$lead->id}\n";

// Step 2: Convert to Client
$leadCtrl = new LeadController();
$leadCtrl->convertToClient(request(), $lead->id);
$client = Client::where('lead_id', $lead->id)->first();
echo "2. Converted to Client #{$client->id} ({$client->name})\n";

// Step 3: Check Booking create dropdown (SHOULD BE PRESENT)
$bookingCtrl = new BookingController();
$viewData = $bookingCtrl->create()->getData();
$clientsInBooking = $viewData['clients'];
$isPresentBefore = $clientsInBooking->where('id', $client->id)->count() > 0;
echo "3. Present in Booking dropdown before deleting lead: " . ($isPresentBefore ? "YES" : "NO") . "\n";

// Step 4: Delete the Lead
$leadCtrl->destroy($lead);
echo "4. Deleted Lead #{$lead->id} via destroy()\n";

// Check if client was also deleted
$clientAfterDelete = Client::find($client->id);
$clientTrashed = Client::onlyTrashed()->find($client->id);
echo "   Client active after deleting lead? " . ($clientAfterDelete ? "YES" : "NO") . "\n";
echo "   Client in trash? " . ($clientTrashed ? "YES" : "NO") . "\n";

// Step 5: Check Booking create dropdown (MUST NOT BE PRESENT!)
$viewDataAfter = $bookingCtrl->create()->getData();
$clientsInBookingAfter = $viewDataAfter['clients'];
$isPresentAfter = $clientsInBookingAfter->where('id', $client->id)->count() > 0;
echo "5. Present in Booking dropdown after deleting lead: " . ($isPresentAfter ? "YES (BUG!)" : "NO (CORRECT!)") . "\n";

// Step 6: Test trash() method
$trashView = $leadCtrl->trash();
$trashedLeads = $trashView->getData()['leads'];
$leadFoundInTrash = $trashedLeads->where('id', $lead->id)->count() > 0;
echo "6. LeadController::trash() works? " . ($leadFoundInTrash ? "YES" : "NO") . "\n";

// Step 7: Test restore() method
$leadCtrl->restore($lead->id);
$leadRestored = Lead::find($lead->id);
$clientRestored = Client::find($client->id);
echo "7. Lead restored? " . ($leadRestored ? "YES" : "NO") . " | Client restored? " . ($clientRestored ? "YES" : "NO") . "\n";

$viewDataRestored = $bookingCtrl->create()->getData();
$isPresentRestored = $viewDataRestored['clients']->where('id', $client->id)->count() > 0;
echo "   Present in Booking dropdown after restore: " . ($isPresentRestored ? "YES (CORRECT!)" : "NO") . "\n";

// Step 8: Clean up test records
$leadCtrl->destroy($lead);
$leadCtrl->forceDelete($lead->id);
echo "8. Cleaned up test records via forceDelete.\n";
