<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingTransport;
use App\Models\BookingVisa;
use App\Models\Client;
use App\Models\ExpenseTransaction;
use App\Models\Staff;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsController extends Controller
{
    /**
     * Display Chart of Accounts listing with live balances and categories.
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
     * Compile complete 6-head Chart of Accounts with live PKR & SAR balances.
     */
    private function gatherChartOfAccountsData(Request $request): array
    {
        $activeRoe = (float) ($request->get('roe', 75.0) > 0 ? $request->get('roe', 75.0) : 75.0);
        $asOfDate  = $request->get('as_of_date', Carbon::now()->toDateString());
        $filterCat = $request->get('category', 'all'); // all, asset, liability, equity, revenue, cogs, expense

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
        $cashAccounts = Account::where('status', 'active')->where('account_type', 'cash')->get();
        foreach ($cashAccounts as $ca) {
            $init = ((float)$ca->opening_balance_pkr) + (((float)$ca->opening_balance_sar) * (float)($ca->opening_balance_roe ?: $activeRoe));
            $inflows = (float) ExpenseTransaction::where('account_id', $ca->id)->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $outflows = (float) ExpenseTransaction::where('account_id', $ca->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $balPkr = $init + $inflows - $outflows;

            $chart['assets'][] = [
                'code'        => $ca->account_code ?: '1110-' . str_pad($ca->id, 2, '0', STR_PAD_LEFT),
                'name'        => $ca->account_name . ' (Cash Register)',
                'sub_type'    => 'Cash & Cash Equivalent',
                'balance_pkr' => $balPkr,
                'balance_sar' => $balPkr / $activeRoe,
                'status'      => 'Active',
                'id'          => $ca->id,
            ];
        }

        // 1110-00 General Counter Cash
        $unassignedIn = (float) ExpenseTransaction::whereNull('bank_id')->whereNull('account_id')->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
        $unassignedOut = (float) ExpenseTransaction::whereNull('bank_id')->whereNull('account_id')->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
        $bookingDirectCash = (float) Booking::whereDate('booking_date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN total_received * COALESCE(flight_roe, 75) ELSE total_received END) as total')->value('total');
        $generalCashBal = ($unassignedIn + $bookingDirectCash) - $unassignedOut;

        $chart['assets'][] = [
            'code'        => '1110-00',
            'name'        => 'Counter / General Cash in Hand',
            'sub_type'    => 'Cash & Cash Equivalent',
            'balance_pkr' => $generalCashBal,
            'balance_sar' => $generalCashBal / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];

        // 1120 - Bank Accounts
        $banks = Bank::where('status', 'active')->get();
        foreach ($banks as $b) {
            $init = ((float)$b->opening_balance_pkr) + (((float)$b->opening_balance_sar) * (float)($b->opening_balance_roe ?: $activeRoe));
            $inflows = (float) ExpenseTransaction::where('bank_id', $b->id)->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $outflows = (float) ExpenseTransaction::where('bank_id', $b->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $balPkr = $init + $inflows - $outflows;

            $chart['assets'][] = [
                'code'        => '1120-' . str_pad($b->id, 2, '0', STR_PAD_LEFT),
                'name'        => $b->name . ' (' . ($b->account_number ?: 'Main') . ')',
                'sub_type'    => 'Bank Account',
                'balance_pkr' => $balPkr,
                'balance_sar' => $balPkr / $activeRoe,
                'status'      => 'Active',
                'id'          => null,
            ];
        }

        // 1200 - Accounts Receivable (Clients)
        $clientReceivables = (float) Booking::whereDate('booking_date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN (total_amount - total_received) * COALESCE(flight_roe, 75) ELSE (total_amount - total_received) END) as bal')
            ->value('bal');
        $chart['assets'][] = [
            'code'        => '1200-00',
            'name'        => 'Accounts Receivable (Trade Debtors / Clients)',
            'sub_type'    => 'Current Asset (Receivable)',
            'balance_pkr' => max(0, $clientReceivables),
            'balance_sar' => max(0, $clientReceivables) / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];

        // 1250 - Supplier Advances / Prepayments
        $suppliers = Supplier::all();
        $supplierAdvances = 0.0;
        foreach ($suppliers as $sp) {
            $initPkr = ((float)$sp->opening_balance_pkr) + (((float)$sp->opening_balance_sar) * (float)($sp->opening_balance_roe ?: $activeRoe));
            $paidPkr = (float) ExpenseTransaction::where('supplier_id', $sp->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $costPkr = 0.0; // Booking direct supplier costs
            $spNet = $initPkr + $paidPkr - $costPkr;
            if ($spNet > 0) {
                $supplierAdvances += $spNet;
            }
        }
        $chart['assets'][] = [
            'code'        => '1250-00',
            'name'        => 'Advances & Prepayments to Vendors / Suppliers',
            'sub_type'    => 'Current Asset (Prepayment)',
            'balance_pkr' => $supplierAdvances,
            'balance_sar' => $supplierAdvances / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];

        // 1300 - Loan Assets / Staff Advances
        $loanAssetAccounts = Account::where('status', 'active')->where('account_type', 'loan_asset')->get();
        foreach ($loanAssetAccounts as $la) {
            $init = ((float)$la->opening_balance_pkr) + (((float)$la->opening_balance_sar) * (float)($la->opening_balance_roe ?: $activeRoe));
            $given = (float) ExpenseTransaction::where('account_id', $la->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $recovered = (float) ExpenseTransaction::where('account_id', $la->id)->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $balPkr = $init + $given - $recovered;

            $chart['assets'][] = [
                'code'        => $la->account_code ?: '1300-' . str_pad($la->id, 2, '0', STR_PAD_LEFT),
                'name'        => $la->account_name . ' (Loan Asset)',
                'sub_type'    => 'Loan & Advance (Asset)',
                'balance_pkr' => $balPkr,
                'balance_sar' => $balPkr / $activeRoe,
                'status'      => 'Active',
                'id'          => $la->id,
            ];
        }

        // 1500 - Fixed Assets
        $fixedAssetAccounts = Account::where('status', 'active')->where('account_type', 'fixed_asset')->get();
        foreach ($fixedAssetAccounts as $fa) {
            $costPkr = ((float)$fa->opening_balance_pkr) + (((float)$fa->opening_balance_sar) * (float)($fa->opening_balance_roe ?: $activeRoe));
            $additions = (float) ExpenseTransaction::where('account_id', $fa->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $balPkr = $costPkr + $additions;

            $chart['assets'][] = [
                'code'        => $fa->account_code ?: '1500-' . str_pad($fa->id, 2, '0', STR_PAD_LEFT),
                'name'        => $fa->account_name . ' (Property, Plant & Equipment)',
                'sub_type'    => 'Non-Current Fixed Asset',
                'balance_pkr' => $balPkr,
                'balance_sar' => $balPkr / $activeRoe,
                'status'      => 'Active',
                'id'          => $fa->id,
            ];
        }

        // ══════════════════════════════════════════════════════════════
        // 2. LIABILITIES (2000 - 2999)
        // ══════════════════════════════════════════════════════════════

        // 2100 - Accounts Payable (Suppliers)
        $supplierPayables = 0.0;
        foreach ($suppliers as $sp) {
            $initPkr = ((float)$sp->opening_balance_pkr) + (((float)$sp->opening_balance_sar) * (float)($sp->opening_balance_roe ?: $activeRoe));
            $paidPkr = (float) ExpenseTransaction::where('supplier_id', $sp->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            // Check direct booking costs
            $spNet = 0.0;
            // For now simple balance calculation
            $payable = max(0, -($initPkr + $paidPkr));
            $supplierPayables += $payable;
        }
        $chart['liabilities'][] = [
            'code'        => '2100-00',
            'name'        => 'Accounts Payable (Trade Creditors / Suppliers)',
            'sub_type'    => 'Current Liability (Payable)',
            'balance_pkr' => $supplierPayables,
            'balance_sar' => $supplierPayables / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];

        // 2150 - Staff Salaries Payable
        $staffMembers = Staff::where('status', 'active')->get();
        $totalSalariesPayable = 0.0;
        foreach ($staffMembers as $st) {
            $earned = (float) $st->salary;
            $paid = (float) ExpenseTransaction::where('staff_id', $st->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->sum('amount');
            $pending = max(0, $earned - $paid);
            $totalSalariesPayable += $pending;
        }
        $chart['liabilities'][] = [
            'code'        => '2150-00',
            'name'        => 'Salaries & Staff Dues Payable',
            'sub_type'    => 'Accrued Payroll Liability',
            'balance_pkr' => $totalSalariesPayable,
            'balance_sar' => $totalSalariesPayable / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];

        // 2200 - Loan Liabilities / Borrowings
        $loanLiabAccounts = Account::where('status', 'active')->where('account_type', 'loan_liability')->get();
        foreach ($loanLiabAccounts as $ll) {
            $init = ((float)$ll->opening_balance_pkr) + (((float)$ll->opening_balance_sar) * (float)($ll->opening_balance_roe ?: $activeRoe));
            $received = (float) ExpenseTransaction::where('account_id', $ll->id)->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $repaid = (float) ExpenseTransaction::where('account_id', $ll->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $balPkr = $init + $received - $repaid;

            $chart['liabilities'][] = [
                'code'        => $ll->account_code ?: '2200-' . str_pad($ll->id, 2, '0', STR_PAD_LEFT),
                'name'        => $ll->account_name . ' (Loan Liability)',
                'sub_type'    => 'Financial Borrowing (Liability)',
                'balance_pkr' => $balPkr,
                'balance_sar' => $balPkr / $activeRoe,
                'status'      => 'Active',
                'id'          => $ll->id,
            ];
        }

        // 2300 - Customer Advances / Unearned Revenue
        $chart['liabilities'][] = [
            'code'        => '2300-00',
            'name'        => 'Client Advance Payments / Unearned Booking Revenue',
            'sub_type'    => 'Unearned Revenue (Liability)',
            'balance_pkr' => 0.00,
            'balance_sar' => 0.00,
            'status'      => 'Active',
            'id'          => null,
        ];

        // ══════════════════════════════════════════════════════════════
        // 3. EQUITY (3000 - 3999)
        // ══════════════════════════════════════════════════════════════

        // 3010 - Owner's Capital
        $capitalAccounts = Account::where('status', 'active')->where('account_type', 'capital')->get();
        if ($capitalAccounts->isNotEmpty()) {
            foreach ($capitalAccounts as $cap) {
                $balPkr = ((float)$cap->opening_balance_pkr) + (((float)$cap->opening_balance_sar) * (float)($cap->opening_balance_roe ?: $activeRoe));
                $chart['equity'][] = [
                    'code'        => $cap->account_code ?: '3010-' . str_pad($cap->id, 2, '0', STR_PAD_LEFT),
                    'name'        => $cap->account_name . " (Owner's Capital)",
                    'sub_type'    => 'Owner Equity',
                    'balance_pkr' => $balPkr,
                    'balance_sar' => $balPkr / $activeRoe,
                    'status'      => 'Active',
                    'id'          => $cap->id,
                ];
            }
        } else {
            $chart['equity'][] = [
                'code'        => '3010-00',
                'name'        => "Owner's Capital / Share Capital",
                'sub_type'    => 'Owner Equity',
                'balance_pkr' => 0.00,
                'balance_sar' => 0.00,
                'status'      => 'Active',
                'id'          => null,
            ];
        }

        // 3020 - Retained Earnings
        $retainedAccounts = Account::where('status', 'active')->where('account_type', 'retained_earnings')->get();
        if ($retainedAccounts->isNotEmpty()) {
            foreach ($retainedAccounts as $re) {
                $balPkr = ((float)$re->opening_balance_pkr) + (((float)$re->opening_balance_sar) * (float)($re->opening_balance_roe ?: $activeRoe));
                $chart['equity'][] = [
                    'code'        => $re->account_code ?: '3020-' . str_pad($re->id, 2, '0', STR_PAD_LEFT),
                    'name'        => $re->account_name . ' (Retained Earnings)',
                    'sub_type'    => 'Retained Earnings',
                    'balance_pkr' => $balPkr,
                    'balance_sar' => $balPkr / $activeRoe,
                    'status'      => 'Active',
                    'id'          => $re->id,
                ];
            }
        } else {
            $chart['equity'][] = [
                'code'        => '3020-00',
                'name'        => 'Retained Earnings / Prior Years Profit',
                'sub_type'    => 'Retained Earnings',
                'balance_pkr' => 0.00,
                'balance_sar' => 0.00,
                'status'      => 'Active',
                'id'          => null,
            ];
        }

        // ══════════════════════════════════════════════════════════════
        // 4. REVENUE / INCOME (4000 - 4999)
        // ══════════════════════════════════════════════════════════════
        $pkgSales = (float) Booking::whereDate('booking_date', '<=', $asOfDate)->where('package_type', 'umrah')->selectRaw('SUM(CASE WHEN currency = "SAR" THEN total_amount * COALESCE(flight_roe, 75) ELSE total_amount END) as total')->value('total');
        $flightSales = (float) Booking::whereDate('booking_date', '<=', $asOfDate)->where('package_type', 'flight')->selectRaw('SUM(CASE WHEN currency = "SAR" THEN total_amount * COALESCE(flight_roe, 75) ELSE total_amount END) as total')->value('total');
        $otherIncome = (float) ExpenseTransaction::where('transaction_type', 'credit')->whereNull('account_id')->whereNull('bank_id')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');

        $chart['revenue'][] = [
            'code'        => '4100-00',
            'name'        => 'Umrah & Hajj Package Sales Revenue',
            'sub_type'    => 'Operating Revenue',
            'balance_pkr' => $pkgSales,
            'balance_sar' => $pkgSales / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];
        $chart['revenue'][] = [
            'code'        => '4200-00',
            'name'        => 'Direct Flight Ticketing Revenue',
            'sub_type'    => 'Operating Revenue',
            'balance_pkr' => $flightSales,
            'balance_sar' => $flightSales / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];
        $chart['revenue'][] = [
            'code'        => '4900-00',
            'name'        => 'Other Operating Income / Commissions',
            'sub_type'    => 'Other Income',
            'balance_pkr' => $otherIncome,
            'balance_sar' => $otherIncome / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];

        // ══════════════════════════════════════════════════════════════
        // 5. COST OF SALES / COGS (5000 - 5999)
        // ══════════════════════════════════════════════════════════════
        $relevantBookings = Booking::with(['hotels', 'transports', 'visas'])
            ->whereDate('booking_date', '<=', $asOfDate)
            ->get();

        $directFlightCosts = 0.0;
        $hotelCosts = 0.0;
        $transportCosts = 0.0;
        $visaCosts = 0.0;

        foreach ($relevantBookings as $rb) {
            if ((float)$rb->flight_cost > 0) {
                $fCostRoe = strtoupper($rb->flight_currency ?? 'SAR') === 'PKR' ? 1 : (float)($rb->flight_cost_roe ?: 75);
                $directFlightCosts += ((float)$rb->flight_cost * $fCostRoe);
            }
            foreach ($rb->hotels as $h) {
                $hCostRoe = strtoupper($h->currency ?? 'SAR') === 'PKR' ? 1 : (float)($h->cost_roe ?: ($h->roe ?: 75));
                $hotelCosts += ((float)$h->cost_amount ?: (((float)$h->cost_per_night * (int)($h->no_of_rooms ?: 1) * (int)($h->no_of_nights ?: 1)) * $hCostRoe));
            }
            foreach ($rb->visas as $v) {
                $vCostRoe = strtoupper($v->currency ?? 'SAR') === 'PKR' ? 1 : (float)($v->cost_roe ?: ($v->roe ?: 75));
                $visaCosts += ((float)$v->cost_amount ?: ((float)$v->cost_rate * $vCostRoe));
            }
            foreach ($rb->transports as $t) {
                $tCostRoe = strtoupper($t->currency ?? 'SAR') === 'PKR' ? 1 : (float)($t->cost_roe ?: ($t->roe ?: 75));
                $transportCosts += ((float)$t->cost_amount ?: ((float)$t->cost_rate * $tCostRoe));
            }
        }

        $chart['cogs'][] = [
            'code'        => '5100-00',
            'name'        => 'Cost of Sales - Flight Tickets & Airlines',
            'sub_type'    => 'Direct Operating Cost',
            'balance_pkr' => $directFlightCosts,
            'balance_sar' => $directFlightCosts / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];
        $chart['cogs'][] = [
            'code'        => '5200-00',
            'name'        => 'Cost of Sales - Hotel Accommodations (Makkah & Madinah)',
            'sub_type'    => 'Direct Operating Cost',
            'balance_pkr' => $hotelCosts,
            'balance_sar' => $hotelCosts / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];
        $chart['cogs'][] = [
            'code'        => '5300-00',
            'name'        => 'Cost of Sales - Transport & Transfers',
            'sub_type'    => 'Direct Operating Cost',
            'balance_pkr' => $transportCosts,
            'balance_sar' => $transportCosts / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];
        $chart['cogs'][] = [
            'code'        => '5400-00',
            'name'        => 'Cost of Sales - Umrah & Tourist Visas',
            'sub_type'    => 'Direct Operating Cost',
            'balance_pkr' => $visaCosts,
            'balance_sar' => $visaCosts / $activeRoe,
            'status'      => 'Active',
            'id'          => null,
        ];

        // ══════════════════════════════════════════════════════════════
        // 6. OPERATING EXPENSES (6000 - 6999)
        // ══════════════════════════════════════════════════════════════
        $officeExpenses = ExpenseTransaction::with('expense')
            ->where('transaction_type', 'debit')
            ->whereNull('supplier_id')
            ->whereNull('client_id')
            ->whereDate('date', '<=', $asOfDate)
            ->get();

        $expenseGroups = [
            'Salaries & Allowances' => 0.0,
            'Office Rent'           => 0.0,
            'Utilities & Bills'     => 0.0,
            'Marketing & Ads'       => 0.0,
            'General & Admin'       => 0.0,
        ];

        foreach ($officeExpenses as $oe) {
            $amtPkr = $oe->currency === 'SAR' ? ((float)$oe->amount * (float)$oe->exchange_rate) : (float)$oe->amount;
            $headName = $oe->expense ? $oe->expense->name : 'General & Admin';
            if (!isset($expenseGroups[$headName])) {
                $expenseGroups[$headName] = 0.0;
            }
            $expenseGroups[$headName] += $amtPkr;
        }

        $expIdx = 1;
        foreach ($expenseGroups as $eName => $eAmt) {
            $chart['expenses'][] = [
                'code'        => '6100-' . str_pad($expIdx++, 2, '0', STR_PAD_LEFT),
                'name'        => 'Operating Expense - ' . $eName,
                'sub_type'    => 'General & Administrative Expense',
                'balance_pkr' => $eAmt,
                'balance_sar' => $eAmt / $activeRoe,
                'status'      => 'Active',
                'id'          => null,
            ];
        }

        // Summary Totals
        $totals = [
            'assets_pkr'      => array_sum(array_column($chart['assets'], 'balance_pkr')),
            'assets_sar'      => array_sum(array_column($chart['assets'], 'balance_sar')),
            'liabilities_pkr' => array_sum(array_column($chart['liabilities'], 'balance_pkr')),
            'liabilities_sar' => array_sum(array_column($chart['liabilities'], 'balance_sar')),
            'equity_pkr'      => array_sum(array_column($chart['equity'], 'balance_pkr')),
            'equity_sar'      => array_sum(array_column($chart['equity'], 'balance_sar')),
            'revenue_pkr'     => array_sum(array_column($chart['revenue'], 'balance_pkr')),
            'revenue_sar'     => array_sum(array_column($chart['revenue'], 'balance_sar')),
            'cogs_pkr'        => array_sum(array_column($chart['cogs'], 'balance_pkr')),
            'cogs_sar'        => array_sum(array_column($chart['cogs'], 'balance_sar')),
            'expenses_pkr'    => array_sum(array_column($chart['expenses'], 'balance_pkr')),
            'expenses_sar'    => array_sum(array_column($chart['expenses'], 'balance_sar')),
        ];

        $totalAccountsCount = count($chart['assets']) + count($chart['liabilities']) + count($chart['equity']) + count($chart['revenue']) + count($chart['cogs']) + count($chart['expenses']);

        return compact(
            'chart',
            'totals',
            'totalAccountsCount',
            'activeRoe',
            'asOfDate',
            'filterCat'
        );
    }
}
