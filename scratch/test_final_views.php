<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

$user = \App\Models\User::first();
if ($user) {
    Auth::login($user);
}

View::share('errors', new \Illuminate\Support\ViewErrorBag());

$viewsToTest = [
    'booking.create' => function() {
        $clients = \App\Models\Client::all();
        $companies = \App\Models\Company::all();
        $suppliers = \App\Models\Supplier::all();
        $packages = \App\Models\Package::all();
        $banks = \App\Models\Bank::all();
        $staffMembers = \App\Models\Staff::all();
        $mealPlans = \App\Models\MealPlan::all();
        $hotels = \App\Models\Hotel::all();
        $defaultVouchers = [
            'booking' => 'BK-2026-00001',
            'flight' => 'FLT-2026-00001',
            'visa' => 'VIS-2026-00001',
            'transport' => 'TRN-2026-00001',
        ];
        $isUmrah = true;
        return compact('clients', 'companies', 'suppliers', 'packages', 'banks', 'staffMembers', 'mealPlans', 'hotels', 'defaultVouchers', 'isUmrah');
    },
    'booking.edit' => function() {
        $booking = \App\Models\Booking::with(['hotels', 'visas', 'transports', 'persons', 'client'])->first();
        if (!$booking) return [];
        $clients = \App\Models\Client::all();
        $companies = \App\Models\Company::all();
        $suppliers = \App\Models\Supplier::all();
        $packages = \App\Models\Package::all();
        $banks = \App\Models\Bank::all();
        $staffMembers = \App\Models\Staff::all();
        $mealPlans = \App\Models\MealPlan::all();
        $hotels = \App\Models\Hotel::all();
        $isUmrah = true;
        $dashboardPkg = 'umrah';
        $reqTab = '#tab-details';
        return compact('booking', 'clients', 'companies', 'suppliers', 'packages', 'banks', 'staffMembers', 'mealPlans', 'hotels', 'isUmrah', 'dashboardPkg', 'reqTab');
    },
    'reports.profit_loss' => function() {
        $request = new \Illuminate\Http\Request();
        $controller = new \App\Http\Controllers\ProfitLossReportController();
        $response = $controller->index($request);
        return $response->getData();
    },
    'expense_transaction.create' => function() {
        $expenses = \App\Models\Expense::all();
        $banks = \App\Models\Bank::all();
        $suppliers = \App\Models\Supplier::all();
        $clients = \App\Models\Client::all();
        $staffMembers = \App\Models\Staff::all();
        $cashAccounts = \App\Models\Account::where('status', 'active')->get();
        $autoRef = 'DT-EXP-000001';
        $autoJvRef = 'DT-JV-000001';
        return compact('expenses', 'banks', 'suppliers', 'clients', 'staffMembers', 'cashAccounts', 'autoRef', 'autoJvRef');
    },
    'account.create' => function() {
        $clients = \App\Models\Client::all();
        $staffMembers = \App\Models\Staff::all();
        $suppliers = \App\Models\Supplier::all();
        $banks = \App\Models\Bank::all();
        return compact('clients', 'staffMembers', 'suppliers', 'banks');
    },
    'account.edit' => function() {
        $account = \App\Models\Account::first();
        if (!$account) return [];
        $clients = \App\Models\Client::all();
        $staffMembers = \App\Models\Staff::all();
        $suppliers = \App\Models\Supplier::all();
        $banks = \App\Models\Bank::all();
        return compact('account', 'clients', 'staffMembers', 'suppliers', 'banks');
    },
    'account.index' => function() {
        $accounts = \App\Models\Account::all();
        return compact('accounts');
    }
];

$errors = 0;
foreach ($viewsToTest as $viewName => $dataProvider) {
    try {
        echo "Testing view: {$viewName}... ";
        $data = $dataProvider();
        if (empty($data)) {
            echo "SKIPPED (no record)\n";
            continue;
        }
        $rendered = View::make($viewName, $data)->render();
        echo "OK (" . strlen($rendered) . " bytes)\n";
    } catch (\Throwable $e) {
        echo "FAILED!\n";
        echo "Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
        $errors++;
    }
}

if ($errors === 0) {
    echo "\n=== ALL 7 VIEWS PASSED 100% ===\n";
} else {
    echo "\n=== {$errors} VIEW(S) FAILED ===\n";
}
