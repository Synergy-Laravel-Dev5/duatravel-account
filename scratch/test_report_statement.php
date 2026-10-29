<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\ProfitLossReportController;

$user = User::first();
if ($user) {
    Auth::login($user);
}

$controller = new ProfitLossReportController();
$req = new Request(['package_type' => 'all']);

$response = $controller->index($req);
$viewData = $response->getData();

echo "=== PROFIT & LOSS REPORT TEST ===\n";
echo "Statement Period: " . $viewData['statementPeriod'] . "\n";
echo "Total Selling / Income: PKR " . number_format($viewData['statementTotalIncome'], 2) . "\n";
echo "Total Direct Service Costs: PKR " . number_format($viewData['totalDirectCostPkr'], 2) . "\n";
echo "Total Utilities: PKR " . number_format($viewData['totalUtilitiesPkr'], 2) . "\n";
echo "Total Operating EXPENSE: PKR " . number_format($viewData['totalGeneralPkr'], 2) . "\n";
echo "Total Salaries & Wages: PKR " . number_format($viewData['totalSalariesPkr'], 2) . "\n";
echo "Total Expenses: PKR " . number_format($viewData['statementTotalExpenses'], 2) . "\n";
echo "Net Profit: PKR " . number_format($viewData['statementNetProfit'], 2) . "\n";

echo "\nDirect Cost Items count: " . count($viewData['directCostItems']) . "\n";
echo "Utility Items count: " . count($viewData['utilItems']) . "\n";
echo "General Expense Items count: " . count($viewData['genItems']) . "\n";
echo "Salary Items count: " . count($viewData['salaryItems']) . "\n";

$renderedHtml = $response->render();
echo "\nView render test: " . strlen($renderedHtml) . " bytes rendered.\n";
echo "Contains Statement container: " . (str_contains($renderedHtml, 'printableFormalStatement') ? 'YES' : 'NO') . "\n";
echo "Contains Dua Travels header: " . (str_contains($renderedHtml, 'Dua Travels and Tours') ? 'YES' : 'NO') . "\n";
echo "Contains Net Profit box: " . (str_contains($renderedHtml, 'Net Profit') ? 'YES' : 'NO') . "\n";
echo "=== SUCCESS! ===\n";
