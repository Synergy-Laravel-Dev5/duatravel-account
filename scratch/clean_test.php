<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

App\Models\Client::where('name', 'LIKE', '%Test Lead%')->forceDelete();
App\Models\Lead::where('contact_person', 'LIKE', '%Test Lead%')->forceDelete();
echo "Cleaned up!\n";
