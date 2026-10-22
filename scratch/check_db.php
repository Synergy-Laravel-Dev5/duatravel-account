<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\Client;

echo "=== ALL LEADS (INCLUDING TRASHED) ===\n";
foreach (Lead::withTrashed()->get() as $l) {
    echo "Lead ID: {$l->id} | Contact: {$l->contact_person} | Phone: {$l->phone} | DeletedAt: {$l->deleted_at}\n";
}

echo "\n=== ALL CLIENTS (INCLUDING TRASHED) ===\n";
foreach (Client::withTrashed()->get() as $c) {
    echo "Client ID: {$c->id} | Name: {$c->name} | LeadID: {$c->lead_id} | Status: {$c->status} | PkgType: '{$c->package_type}' | DeletedAt: {$c->deleted_at}\n";
}
