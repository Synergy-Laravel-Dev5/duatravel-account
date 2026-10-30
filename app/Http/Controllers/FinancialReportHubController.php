<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Account;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Expense;
use App\Models\Staff;
use Illuminate\Http\Request;

class FinancialReportHubController extends Controller
{
    /**
     * Unified Central Hub for All Financial Statement & Ledger Filters.
     * Consolidates:
     * 1. Bank Transaction & Statement Reports Filter
     * 2. Cash Book & Cash Report Filter
     * 3. Client Ledger Filter (Dr / Cr)
     * 4. Search Supplier Ledger Filter
     * 5. Expense & Transaction Report Filter
     */
    public function index(Request $request)
    {
        $banks = Bank::where('status', 'active')->orderBy('name')->get();
        $cashAccounts = Account::where('status', 'active')
            ->where('account_type', 'cash')
            ->orderBy('account_name')
            ->get();
        $clients = Client::where('status', 'active')
            ->orWhereNull('status')
            ->orderBy('name')
            ->get();
        $suppliers = Supplier::where('status', 'active')
            ->orWhereNull('status')
            ->orderBy('name')
            ->get();
        $expenses = Expense::orderBy('name')->get();
        $staffMembers = Staff::where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $activeTab = $request->get('tab', 'bank');
        if (!in_array($activeTab, ['bank', 'cash', 'client', 'supplier', 'staff'])) {
            $activeTab = 'bank';
        }

        return view('reports.hub', compact(
            'banks',
            'cashAccounts',
            'clients',
            'suppliers',
            'staffMembers',
            'activeTab'
        ));
    }
}
