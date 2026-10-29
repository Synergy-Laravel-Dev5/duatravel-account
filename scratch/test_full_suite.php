<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

view()->share('errors', new \Illuminate\Support\ViewErrorBag());
$user = \App\Models\User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
}

try {
    // 1. Umrah Invoice (Booking 1)
    $booking = \App\Models\Booking::with(['client', 'company', 'package', 'persons', 'hotels', 'transports', 'visas'])->find(1);
    $selectedPerson = null;
    $showRates = 1;
    $currencyMode = 'mix';
    $invHtml = view('booking.umrah_invoice', compact('booking', 'selectedPerson', 'showRates', 'currencyMode'))->render();
    echo "1. booking.umrah-invoice rendered successfully: " . strlen($invHtml) . " bytes\n";

    // 2. Account Index
    $accounts = \App\Models\Account::latest()->get();
    $accIdxHtml = view('account.index', compact('accounts'))->render();
    echo "2. account.index rendered successfully: " . strlen($accIdxHtml) . " bytes\n";

    // 3. Account Create
    $clients = \App\Models\Client::orderBy('name')->get();
    $staffMembers = \App\Models\Staff::where('status', 'active')->orderBy('first_name')->get();
    $suppliers = \App\Models\Supplier::orderBy('name')->get();
    $banks = \App\Models\Bank::orderBy('name')->get();
    $accCreateHtml = view('account.create', compact('clients', 'staffMembers', 'suppliers', 'banks'))->render();
    echo "3. account.create rendered successfully: " . strlen($accCreateHtml) . " bytes\n";

    // 4. Account Edit
    $account = \App\Models\Account::first() ?? new \App\Models\Account();
    $accEditHtml = view('account.edit', compact('account', 'clients', 'staffMembers', 'suppliers', 'banks'))->render();
    echo "4. account.edit rendered successfully: " . strlen($accEditHtml) . " bytes\n";

    // 5. Test Creating a Loan Asset for Staff and Loan Liability for Client
    $testStaffAcc = \App\Models\Account::create([
        'account_name' => 'Loan Asset - Staff: Test Staff',
        'account_type' => 'loan_asset',
        'entity_type'  => 'staff',
        'entity_id'    => $staffMembers->first()->id ?? null,
        'opening_balance_pkr' => 50000,
        'opening_balance_sar' => 666.67,
        'opening_balance_roe' => 75.0,
        'status' => 'active',
        'notes' => 'Advance salary loan 50,000 PKR'
    ]);
    echo "5. Loan Asset Account created (ID: {$testStaffAcc->id})\n";
    $testStaffAcc->forceDelete(); // Clean up

    echo "\nALL TESTS PASSED 100% PERFECTLY!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
}
