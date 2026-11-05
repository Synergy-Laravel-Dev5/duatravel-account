<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Bank;
use App\Models\Expense;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChartOfAccountsController extends Controller
{
    /**
     * Display Chart of Accounts listing as pure head directory (no balances).
     */
    public function index(Request $request)
    {
        $data = $this->gatherChartOfAccountsData($request);
        return view('reports.chart_of_accounts', $data);
    }

    /**
     * Export Chart of Accounts to PDF.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->gatherChartOfAccountsData($request);
        $pdf = Pdf::loadView('reports.chart_of_accounts_pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Chart_of_Accounts_' . date('Y_m_d') . '.pdf');
    }

    /**
     * Compile standard 6-head Chart of Accounts directory with individual suppliers, banks, cash, and expense heads.
     * Note: Standard COA lists account heads, classifications, and sub-types without monetary balances.
     */
    private function gatherChartOfAccountsData(Request $request): array
    {
        $filterCat = $request->get('category', 'all');

        $chart = [
            'assets'      => [],
            'liabilities' => [],
            'equity'      => [],
            'revenue'     => [],
            'cogs'        => [],
            'expenses'    => [],
        ];

        // ══════════════════════════════════════════════════════════════
        // 1. ASSETS (1000 - 1999)
        // ══════════════════════════════════════════════════════════════

        // 1110 - Cash Accounts
        $chart['assets'][] = [
            'code'           => '1110-00',
            'name'           => 'Counter Cash in Hand (General Till / Register)',
            'classification' => 'Asset',
            'sub_type'       => 'Cash & Cash Equivalent',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];

        $cashAccounts = Account::where('status', 'active')->where('account_type', 'cash')->orderBy('account_name')->get();
        foreach ($cashAccounts as $ca) {
            $chart['assets'][] = [
                'code'           => $ca->account_code ?: ('1110-' . str_pad($ca->id, 2, '0', STR_PAD_LEFT)),
                'name'           => $ca->account_name . ' (Cash Register)',
                'classification' => 'Asset',
                'sub_type'       => 'Cash & Cash Equivalent',
                'status'         => ucfirst($ca->status ?: 'Active'),
                'id'             => $ca->id,
                'edit_url'       => route('account.edit', $ca->id),
            ];
        }

        // 1120 - Bank Accounts
        $banks = Bank::where('status', 'active')->orderBy('name')->get();
        foreach ($banks as $b) {
            $chart['assets'][] = [
                'code'           => '1120-' . str_pad($b->id, 2, '0', STR_PAD_LEFT),
                'name'           => $b->name . (!empty($b->account_number) ? ' (' . $b->account_number . ')' : ''),
                'classification' => 'Asset',
                'sub_type'       => 'Bank Account',
                'status'         => ucfirst($b->status ?: 'Active'),
                'id'             => $b->id,
                'edit_url'       => route('bank.edit', $b->id),
            ];
        }

        // 1200 - Accounts Receivable (Clients)
        $chart['assets'][] = [
            'code'           => '1200-00',
            'name'           => 'Accounts Receivable (Trade Debtors / Client Pilgrims - Control)',
            'classification' => 'Asset',
            'sub_type'       => 'Current Asset (Receivables)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];

        // 1250 - Supplier Advances / Prepayments
        $chart['assets'][] = [
            'code'           => '1250-00',
            'name'           => 'Advances & Prepayments to Vendors / Suppliers (Control)',
            'classification' => 'Asset',
            'sub_type'       => 'Current Asset (Prepayments & Advances)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];

        // 1300 - Loan Assets / Staff Advances
        $chart['assets'][] = [
            'code'           => '1300-00',
            'name'           => 'Staff Advances & Short-Term Loan Assets (Control)',
            'classification' => 'Asset',
            'sub_type'       => 'Loan & Advance (Asset)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];

        $loanAssetAccounts = Account::where('status', 'active')->where('account_type', 'loan_asset')->orderBy('account_name')->get();
        foreach ($loanAssetAccounts as $la) {
            $chart['assets'][] = [
                'code'           => $la->account_code ?: ('1300-' . str_pad($la->id, 2, '0', STR_PAD_LEFT)),
                'name'           => $la->account_name . ' (Loan Asset)',
                'classification' => 'Asset',
                'sub_type'       => 'Loan & Advance (Asset)',
                'status'         => ucfirst($la->status ?: 'Active'),
                'id'             => $la->id,
                'edit_url'       => route('account.edit', $la->id),
            ];
        }

        // 1500 - Fixed Assets
        $fixedAssetAccounts = Account::where('status', 'active')->where('account_type', 'fixed_asset')->orderBy('account_name')->get();
        if ($fixedAssetAccounts->isNotEmpty()) {
            foreach ($fixedAssetAccounts as $fa) {
                $chart['assets'][] = [
                    'code'           => $fa->account_code ?: ('1500-' . str_pad($fa->id, 2, '0', STR_PAD_LEFT)),
                    'name'           => $fa->account_name . ' (Property, Plant & Equipment)',
                    'classification' => 'Asset',
                    'sub_type'       => 'Non-Current Fixed Asset',
                    'status'         => ucfirst($fa->status ?: 'Active'),
                    'id'             => $fa->id,
                    'edit_url'       => route('account.edit', $fa->id),
                ];
            }
        } else {
            $chart['assets'][] = [
                'code'           => '1500-01',
                'name'           => 'Office Equipment & Computers',
                'classification' => 'Asset',
                'sub_type'       => 'Non-Current Fixed Asset',
                'status'         => 'Active',
                'id'             => null,
                'edit_url'       => null,
            ];
            $chart['assets'][] = [
                'code'           => '1500-02',
                'name'           => 'Office Furniture & Fixtures',
                'classification' => 'Asset',
                'sub_type'       => 'Non-Current Fixed Asset',
                'status'         => 'Active',
                'id'             => null,
                'edit_url'       => null,
            ];
        }

        // ══════════════════════════════════════════════════════════════
        // 2. LIABILITIES (2000 - 2999)
        // ══════════════════════════════════════════════════════════════

        // 2100 - Accounts Payable (Trade Creditors / Suppliers) Control
        $chart['liabilities'][] = [
            'code'           => '2100-00',
            'name'           => 'Accounts Payable (Trade Creditors / Suppliers - Master Control)',
            'classification' => 'Liability',
            'sub_type'       => 'Accounts Payable (Control)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];

        // Individual Suppliers / Vendors
        $suppliers = Supplier::orderBy('name')->get();
        foreach ($suppliers as $sp) {
            $svcType = $sp->service_type ? ucfirst(str_replace('_', ' ', $sp->service_type)) : 'Vendor';
            $chart['liabilities'][] = [
                'code'           => '2100-' . str_pad($sp->id, 3, '0', STR_PAD_LEFT),
                'name'           => $sp->name . (!empty($sp->company_name) && $sp->company_name !== $sp->name ? ' (' . $sp->company_name . ')' : '') . ' [Supplier]',
                'classification' => 'Liability',
                'sub_type'       => 'Trade Creditor / Supplier (' . $svcType . ')',
                'status'         => ucfirst($sp->status ?: 'Active'),
                'id'             => $sp->id,
                'edit_url'       => route('supplier.edit', $sp->id),
            ];
        }

        // 2150 - Staff Salaries Payable
        $chart['liabilities'][] = [
            'code'           => '2150-00',
            'name'           => 'Salaries & Staff Dues Payable',
            'classification' => 'Liability',
            'sub_type'       => 'Accrued Payroll Liability',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];

        // 2200 - Loan Liabilities / Borrowings
        $loanLiabAccounts = Account::where('status', 'active')->where('account_type', 'loan_liability')->orderBy('account_name')->get();
        foreach ($loanLiabAccounts as $ll) {
            $chart['liabilities'][] = [
                'code'           => $ll->account_code ?: ('2200-' . str_pad($ll->id, 2, '0', STR_PAD_LEFT)),
                'name'           => $ll->account_name . ' (Loan Liability)',
                'classification' => 'Liability',
                'sub_type'       => 'Financial Borrowing (Liability)',
                'status'         => ucfirst($ll->status ?: 'Active'),
                'id'             => $ll->id,
                'edit_url'       => route('account.edit', $ll->id),
            ];
        }

        // 2300 - Customer Advances / Unearned Revenue
        $chart['liabilities'][] = [
            'code'           => '2300-00',
            'name'           => 'Client Advance Booking Deposits / Unearned Service Revenue',
            'classification' => 'Liability',
            'sub_type'       => 'Unearned Revenue (Liability)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];

        // ══════════════════════════════════════════════════════════════
        // 3. EQUITY (3000 - 3999)
        // ══════════════════════════════════════════════════════════════

        // 3010 - Owner's Capital
        $capitalAccounts = Account::where('status', 'active')->where('account_type', 'capital')->orderBy('account_name')->get();
        if ($capitalAccounts->isNotEmpty()) {
            foreach ($capitalAccounts as $cap) {
                $chart['equity'][] = [
                    'code'           => $cap->account_code ?: ('3010-' . str_pad($cap->id, 2, '0', STR_PAD_LEFT)),
                    'name'           => $cap->account_name . " (Owner's Capital / Invested)",
                    'classification' => 'Equity',
                    'sub_type'       => 'Owner Equity',
                    'status'         => ucfirst($cap->status ?: 'Active'),
                    'id'             => $cap->id,
                    'edit_url'       => route('account.edit', $cap->id),
                ];
            }
        } else {
            $chart['equity'][] = [
                'code'           => '3010-00',
                'name'           => "Owner's Capital / Share Capital",
                'classification' => 'Equity',
                'sub_type'       => 'Owner Equity',
                'status'         => 'Active',
                'id'             => null,
                'edit_url'       => null,
            ];
        }

        // 3020 - Retained Earnings
        $retainedAccounts = Account::where('status', 'active')->where('account_type', 'retained_earnings')->orderBy('account_name')->get();
        if ($retainedAccounts->isNotEmpty()) {
            foreach ($retainedAccounts as $re) {
                $chart['equity'][] = [
                    'code'           => $re->account_code ?: ('3020-' . str_pad($re->id, 2, '0', STR_PAD_LEFT)),
                    'name'           => $re->account_name . ' (Retained Earnings)',
                    'classification' => 'Equity',
                    'sub_type'       => 'Retained Earnings',
                    'status'         => ucfirst($re->status ?: 'Active'),
                    'id'             => $re->id,
                    'edit_url'       => route('account.edit', $re->id),
                ];
            }
        } else {
            $chart['equity'][] = [
                'code'           => '3020-00',
                'name'           => 'Retained Earnings / Prior Years Profit & Loss',
                'classification' => 'Equity',
                'sub_type'       => 'Retained Earnings',
                'status'         => 'Active',
                'id'             => null,
                'edit_url'       => null,
            ];
        }

        // ══════════════════════════════════════════════════════════════
        // 4. REVENUE / INCOME (4000 - 4999)
        // ══════════════════════════════════════════════════════════════
        $chart['revenue'][] = [
            'code'           => '4100-00',
            'name'           => 'Umrah & Hajj Package Sales Revenue',
            'classification' => 'Revenue',
            'sub_type'       => 'Operating Revenue (Pilgrimage Packages)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];
        $chart['revenue'][] = [
            'code'           => '4200-00',
            'name'           => 'Flight Ticketing Revenue (Domestic & International)',
            'classification' => 'Revenue',
            'sub_type'       => 'Operating Revenue (Airlines)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];
        $chart['revenue'][] = [
            'code'           => '4300-00',
            'name'           => 'Hotel Accommodation & Room Reservation Revenue',
            'classification' => 'Revenue',
            'sub_type'       => 'Operating Revenue (Hotels)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];
        $chart['revenue'][] = [
            'code'           => '4400-00',
            'name'           => 'Visa Processing Service Revenue',
            'classification' => 'Revenue',
            'sub_type'       => 'Operating Revenue (Visas)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];
        $chart['revenue'][] = [
            'code'           => '4500-00',
            'name'           => 'Transportation & Airport Transfers Revenue',
            'classification' => 'Revenue',
            'sub_type'       => 'Operating Revenue (Transport)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];
        $chart['revenue'][] = [
            'code'           => '4900-00',
            'name'           => 'Other Operating Income, Commission & Rebates',
            'classification' => 'Revenue',
            'sub_type'       => 'Other Income',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];

        // ══════════════════════════════════════════════════════════════
        // 5. COST OF SALES / COGS (5000 - 5999)
        // ══════════════════════════════════════════════════════════════
        $chart['cogs'][] = [
            'code'           => '5100-00',
            'name'           => 'Cost of Sales - Flight Tickets & Airline Fares',
            'classification' => 'Cost of Sales',
            'sub_type'       => 'Direct Operating Cost (Airlines)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];
        $chart['cogs'][] = [
            'code'           => '5200-00',
            'name'           => 'Cost of Sales - Hotel Accommodations (Makkah & Madinah)',
            'classification' => 'Cost of Sales',
            'sub_type'       => 'Direct Operating Cost (Hotels)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];
        $chart['cogs'][] = [
            'code'           => '5300-00',
            'name'           => 'Cost of Sales - Transportation & Vehicle Transfers',
            'classification' => 'Cost of Sales',
            'sub_type'       => 'Direct Operating Cost (Transport)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];
        $chart['cogs'][] = [
            'code'           => '5400-00',
            'name'           => 'Cost of Sales - Umrah & Tourist Visas',
            'classification' => 'Cost of Sales',
            'sub_type'       => 'Direct Operating Cost (Visas)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];
        $chart['cogs'][] = [
            'code'           => '5900-00',
            'name'           => 'Cost of Sales - Additional Services & Ancillary Charges',
            'classification' => 'Cost of Sales',
            'sub_type'       => 'Direct Operating Cost (Services)',
            'status'         => 'Active',
            'id'             => null,
            'edit_url'       => null,
        ];

        // ══════════════════════════════════════════════════════════════
        // 6. OPERATING EXPENSES (6000 - 6999)
        // ══════════════════════════════════════════════════════════════
        $expenseCategories = Expense::orderBy('name')->get();
        if ($expenseCategories->isNotEmpty()) {
            foreach ($expenseCategories as $ec) {
                $chart['expenses'][] = [
                    'code'           => '6100-' . str_pad($ec->id, 3, '0', STR_PAD_LEFT),
                    'name'           => 'Operating Expense - ' . $ec->name,
                    'classification' => 'Expense',
                    'sub_type'       => 'General & Administrative Overhead',
                    'status'         => 'Active',
                    'id'             => $ec->id,
                    'edit_url'       => null,
                ];
            }
        }

        // Standard core operating expense heads
        $standardExpenses = [
            ['code' => '6100-01', 'name' => 'Salaries & Staff Allowances',                  'sub_type' => 'Payroll & Compensation'],
            ['code' => '6100-02', 'name' => 'Office Rent & Property Leases',                'sub_type' => 'Facility & Occupancy'],
            ['code' => '6100-03', 'name' => 'Utilities (Electricity, Water, Internet)',    'sub_type' => 'Utility Overheads'],
            ['code' => '6100-04', 'name' => 'Marketing, Social Media & Advertising',        'sub_type' => 'Sales & Promotion'],
            ['code' => '6100-05', 'name' => 'Office Supplies, Printing & Stationery',      'sub_type' => 'Administrative Supplies'],
            ['code' => '6100-06', 'name' => 'Entertainment, Refreshments & Hospitality',    'sub_type' => 'Office Refreshment'],
            ['code' => '6100-07', 'name' => 'Bank Service Charges & Currency Exchange Fee', 'sub_type' => 'Financial Fees'],
            ['code' => '6100-08', 'name' => 'Legal, Audit & Professional Consultancy',      'sub_type' => 'Professional Fees'],
            ['code' => '6100-09', 'name' => 'General Maintenance & Repairs',                'sub_type' => 'Repairs & Upkeep'],
        ];

        // Check if standard expenses are already covered by user expense categories
        $existingExpNames = $expenseCategories->pluck('name')->map(fn($n) => strtolower(trim($n)))->toArray();
        foreach ($standardExpenses as $se) {
            if (!in_array(strtolower($se['name']), $existingExpNames)) {
                $chart['expenses'][] = [
                    'code'           => $se['code'],
                    'name'           => 'Operating Expense - ' . $se['name'],
                    'classification' => 'Expense',
                    'sub_type'       => $se['sub_type'],
                    'status'         => 'Active',
                    'id'             => null,
                    'edit_url'       => null,
                ];
            }
        }

        // Custom expense accounts from accounts table
        $customExpAccounts = Account::where('status', 'active')->where('account_type', 'expense')->orderBy('account_name')->get();
        foreach ($customExpAccounts as $ce) {
            $chart['expenses'][] = [
                'code'           => $ce->account_code ?: ('6200-' . str_pad($ce->id, 2, '0', STR_PAD_LEFT)),
                'name'           => $ce->account_name . ' (Expense Account)',
                'classification' => 'Expense',
                'sub_type'       => 'Operating Expense',
                'status'         => ucfirst($ce->status ?: 'Active'),
                'id'             => $ce->id,
                'edit_url'       => route('account.edit', $ce->id),
            ];
        }

        // Summary Counts
        $headCounts = [
            'assets'      => count($chart['assets']),
            'liabilities' => count($chart['liabilities']),
            'equity'      => count($chart['equity']),
            'revenue'     => count($chart['revenue']),
            'cogs'        => count($chart['cogs']),
            'expenses'    => count($chart['expenses']),
        ];

        $totalAccountsCount = array_sum($headCounts);

        return compact(
            'chart',
            'headCounts',
            'totalAccountsCount',
            'filterCat'
        );
    }
}
