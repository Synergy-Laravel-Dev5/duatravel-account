<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingTransport;
use App\Models\BookingVisa;
use App\Models\ExpenseTransaction;
use App\Models\Supplier;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashFlowReportController extends Controller
{
    /**
     * Display Cash Flow Statement.
     */
    public function index(Request $request)
    {
        $data = $this->gatherCashFlowData($request);
        return view('reports.cash_flow', $data);
    }

    /**
     * Export Cash Flow Statement to PDF.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->gatherCashFlowData($request);
        $pdf = Pdf::loadView('reports.cash_flow_pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Cash_Flow_Statement_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Gather and calculate all Cash Flow categories 100% dynamically from database.
     */
    private function gatherCashFlowData(Request $request): array
    {
        $activeDashboardPkg = session('dashboard_package', 'hajj');
        $packageType = $request->get('package_type', (in_array($activeDashboardPkg, ['hajj', 'umrah']) ? $activeDashboardPkg : 'all'));
        $year        = $request->get('year', session('dashboard_year', Carbon::now()->year));
        $dateFrom    = $request->get('date_from', Carbon::now()->startOfYear()->toDateString());
        $dateTo      = $request->get('date_to', Carbon::now()->toDateString());

        $availableYears = Booking::selectRaw('DISTINCT COALESCE(package_year, YEAR(COALESCE(booking_date, flight_booking_date, created_at))) as yr')
            ->pluck('yr')
            ->filter()
            ->map(fn($y) => (int)$y)
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [Carbon::now()->year];
        }

        $startDay = Carbon::parse($dateFrom)->subDay()->toDateString();

        // ══════════════════════════════════════════════════════════════
        // 1. CASH FLOW FROM OPERATIONS
        // ══════════════════════════════════════════════════════════════

        // A. Net Earnings / Net Profit for the period
        $bQuery = Booking::with(['hotels', 'visas', 'transports'])
            ->whereDate(DB::raw('COALESCE(booking_date, flight_booking_date, DATE(created_at))'), '>=', $dateFrom)
            ->whereDate(DB::raw('COALESCE(booking_date, flight_booking_date, DATE(created_at))'), '<=', $dateTo);

        if ($packageType === 'hajj') {
            $bQuery->where(function ($q) {
                $q->where('package_type', 'hajj')->orWhereNull('package_type')->orWhere('package_type', '');
            })->where(function ($q) {
                $q->whereNull('package_type')->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']);
            });
        } elseif ($packageType === 'umrah') {
            $bQuery->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']);
        }

        $bookings = $bQuery->get();
        $periodRevenuePkr = 0.0;
        $periodDirectCostsPkr = 0.0;

        foreach ($bookings as $b) {
            $curr = strtoupper($b->currency ?? 'PKR');
            $roe  = ($curr === 'PKR') ? 1 : (float) ($b->flight_roe > 0 ? $b->flight_roe : 75);
            $periodRevenuePkr += ((float)$b->total_amount * $roe);

            // Direct Costs
            foreach ($b->hotels as $h) {
                $hRoe = strtoupper($h->currency ?? 'SAR') === 'PKR' ? 1 : (float)($h->cost_roe ?: ($h->roe ?: 75));
                $periodDirectCostsPkr += ((float)$h->cost_amount ?: ((float)$h->cost_per_night * (int)($h->no_of_rooms ?: 1) * (int)($h->no_of_nights ?: 1) * $hRoe));
            }
            if ((float)$b->flight_cost > 0) {
                $fRoe = strtoupper($b->flight_currency ?? 'SAR') === 'PKR' ? 1 : (float)($b->flight_cost_roe ?: 75);
                $periodDirectCostsPkr += ((float)$b->flight_cost * $fRoe);
            }
            foreach ($b->visas as $v) {
                $vRoe = strtoupper($v->currency ?? 'SAR') === 'PKR' ? 1 : (float)($v->cost_roe ?: ($v->roe ?: 75));
                $periodDirectCostsPkr += ((float)$v->cost_amount ?: ((float)$v->cost_rate * $vRoe));
            }
            foreach ($b->transports as $t) {
                $tRoe = strtoupper($t->currency ?? 'SAR') === 'PKR' ? 1 : (float)($t->cost_roe ?: ($t->roe ?: 75));
                $periodDirectCostsPkr += ((float)$t->cost_amount ?: ((float)$t->cost_rate * $tRoe));
            }
        }

        // Overhead Operating Expenses during period
        $ovQuery = ExpenseTransaction::where('transaction_type', 'debit')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->where(function ($q) {
                $q->whereNotNull('expense_id')
                  ->orWhereIn('payee_type', ['expense', 'staff', 'general', 'office']);
            });

        if ($packageType === 'hajj') {
            $ovQuery->where(function ($q) {
                $q->where('hajj_umrah', 'hajj')->orWhereNull('hajj_umrah');
            });
        } elseif ($packageType === 'umrah') {
            $ovQuery->where('hajj_umrah', 'umrah');
        }

        $periodOverheadExpensesPkr = (float) $ovQuery->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total') ?? 0.0;

        // Net Operating Earnings for period
        $netEarnings = $periodRevenuePkr - $periodDirectCostsPkr - $periodOverheadExpensesPkr;

        // B. Non-cash Depreciation recorded in period
        $depreciation = (float) ExpenseTransaction::where('transaction_type', 'debit')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->where(function($q) {
                $q->whereHas('expense', fn($eq) => $eq->where('name', 'like', '%deprec%'))
                  ->orWhere('description', 'like', '%deprec%');
            })
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total') ?? 0.0;

        // C. Working Capital Changes (Accounts Receivable, Accounts Payable, Prepaids)
        $arStart = $this->calculateAccountsReceivable($startDay, $packageType);
        $arEnd   = $this->calculateAccountsReceivable($dateTo, $packageType);
        $deltaAr = $arEnd - $arStart;

        $decreaseInReceivables = $deltaAr < 0 ? abs($deltaAr) : 0.0;
        $increaseInReceivables = $deltaAr > 0 ? $deltaAr : 0.0;

        $apAdvStart = $this->calculatePayablesAndAdvances($startDay, $packageType);
        $apAdvEnd   = $this->calculatePayablesAndAdvances($dateTo, $packageType);

        $deltaAp  = $apAdvEnd['ap'] - $apAdvStart['ap'];
        $deltaAdv = $apAdvEnd['adv'] - $apAdvStart['adv'];

        $increaseInPayables = $deltaAp > 0 ? $deltaAp : 0.0;
        $decreaseInPayables = $deltaAp < 0 ? abs($deltaAp) : 0.0;

        $increaseInAdvances = $deltaAdv > 0 ? $deltaAdv : 0.0;
        $decreaseInAdvances = $deltaAdv < 0 ? abs($deltaAdv) : 0.0;

        // Accrued Taxes / Salaries Payable changes
        $taxSalariesStart = (float) ExpenseTransaction::where('payee_type', 'staff')
            ->where('transaction_type', 'credit')
            ->whereDate('date', '<=', $startDay)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total') ?? 0.0;

        $taxSalariesEnd = (float) ExpenseTransaction::where('payee_type', 'staff')
            ->where('transaction_type', 'credit')
            ->whereDate('date', '<=', $dateTo)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total') ?? 0.0;

        $deltaTaxes = $taxSalariesEnd - $taxSalariesStart;
        $increaseInTaxes = $deltaTaxes > 0 ? $deltaTaxes : 0.0;

        // Operations Totals
        $totalAdditionsToCash = $depreciation + $decreaseInReceivables + $increaseInPayables + $increaseInTaxes + $decreaseInAdvances;
        $totalSubtractionsFromCash = $increaseInReceivables + $decreaseInPayables + $increaseInAdvances;

        $netCashFromOperations = $netEarnings + $totalAdditionsToCash - $totalSubtractionsFromCash;

        // ══════════════════════════════════════════════════════════════
        // 2. CASH FLOW FROM INVESTING
        // ══════════════════════════════════════════════════════════════
        $equipmentPurchased = (float) ExpenseTransaction::where('transaction_type', 'debit')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->where(function($q) {
                $q->where('payee_type', 'asset')
                  ->orWhereHas('expense', fn($eq) => $eq->where('name', 'like', '%equipment%')
                                                       ->orWhere('name', 'like', '%furniture%')
                                                       ->orWhere('name', 'like', '%computer%')
                                                       ->orWhere('name', 'like', '%vehicle%'))
                  ->orWhere('description', 'like', '%equipment%')
                  ->orWhere('description', 'like', '%computer%')
                  ->orWhere('description', 'like', '%furniture%');
            })
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total') ?? 0.0;

        $netCashFromInvesting = -$equipmentPurchased;

        // ══════════════════════════════════════════════════════════════
        // 3. CASH FLOW FROM FINANCING
        // ══════════════════════════════════════════════════════════════
        $capitalInjected = (float) ExpenseTransaction::where('payee_type', 'capital')
            ->where('transaction_type', 'credit')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total') ?? 0.0;

        $notesPayableBorrowings = (float) ExpenseTransaction::where('payee_type', 'loan')
            ->where('transaction_type', 'credit')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total') ?? 0.0;

        $drawingsOrLoanRepayments = (float) ExpenseTransaction::where('payee_type', 'loan')
            ->where('transaction_type', 'debit')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total') ?? 0.0;

        $netCashFromFinancing = ($capitalInjected + $notesPayableBorrowings) - $drawingsOrLoanRepayments;

        // ══════════════════════════════════════════════════════════════
        // 4. NET CASH FLOW & CASH BALANCES
        // ══════════════════════════════════════════════════════════════
        $cashAtBeginning = $this->calculateCashAndBankBalance($startDay);
        $cashAtEnd       = $this->calculateCashAndBankBalance($dateTo);
        $netCashFlowPeriod = $cashAtEnd - $cashAtBeginning;

        // In the rare scenario where no bank/cash account exists yet, net flow reflects operations + investing + financing
        if ($cashAtBeginning == 0 && $cashAtEnd == 0 && abs($netCashFromOperations + $netCashFromInvesting + $netCashFromFinancing) > 0) {
            $netCashFlowPeriod = $netCashFromOperations + $netCashFromInvesting + $netCashFromFinancing;
            $cashAtEnd = $cashAtBeginning + $netCashFlowPeriod;
        }

        return compact(
            'packageType',
            'year',
            'dateFrom',
            'dateTo',
            'availableYears',

            // Operations
            'netEarnings',
            'depreciation',
            'decreaseInReceivables',
            'increaseInReceivables',
            'increaseInPayables',
            'decreaseInPayables',
            'increaseInAdvances',
            'decreaseInAdvances',
            'increaseInTaxes',
            'totalAdditionsToCash',
            'totalSubtractionsFromCash',
            'netCashFromOperations',

            // Investing
            'equipmentPurchased',
            'netCashFromInvesting',

            // Financing
            'capitalInjected',
            'notesPayableBorrowings',
            'drawingsOrLoanRepayments',
            'netCashFromFinancing',

            // Totals
            'netCashFlowPeriod',
            'cashAtBeginning',
            'cashAtEnd'
        );
    }

    /**
     * Compute total Accounts Receivable as of a specific date.
     */
    private function calculateAccountsReceivable(string $asOfDate, string $packageType): float
    {
        $bookingQuery = Booking::whereDate(DB::raw('COALESCE(booking_date, flight_booking_date, DATE(created_at))'), '<=', $asOfDate);

        if ($packageType === 'hajj') {
            $bookingQuery->where(function ($q) {
                $q->where('package_type', 'hajj')->orWhereNull('package_type')->orWhere('package_type', '');
            })->where(function ($q) {
                $q->whereNull('package_type')->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']);
            });
        } elseif ($packageType === 'umrah') {
            $bookingQuery->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']);
        }

        $bookings = $bookingQuery->get();
        $totalReceivable = 0.0;

        foreach ($bookings as $b) {
            $curr = strtoupper($b->currency ?? 'PKR');
            $roe  = ($curr === 'PKR') ? 1 : (float) ($b->flight_roe > 0 ? $b->flight_roe : 75);

            $bPkgSell = (float) $b->total_amount * $roe;
            $initialRec = (float) $b->total_received * $roe;

            $txPaid = (float) Transaction::where('booking_id', $b->id)
                ->where('status', 'confirmed')
                ->whereDate('payment_date', '<=', $asOfDate)
                ->sum('amount');

            $due = max(0, $bPkgSell - $initialRec - $txPaid);
            $totalReceivable += $due;
        }

        return $totalReceivable;
    }

    /**
     * Compute total Accounts Payable and Supplier Advances as of a specific date.
     */
    private function calculatePayablesAndAdvances(string $asOfDate, string $packageType): array
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $totalAp = 0.0;
        $totalAdv = 0.0;

        foreach ($suppliers as $s) {
            $sRoe = (float) ($s->opening_balance_roe ?: 75.0);
            $sObPkr = ((float)$s->opening_balance_pkr ?: 0) + (((float)$s->opening_balance_sar ?: 0) * $sRoe);

            $hQuery = BookingHotel::where('supplier_id', $s->id)
                ->whereDate(DB::raw('COALESCE(booking_date, check_in, DATE(created_at))'), '<=', $asOfDate);
            $fQuery = Booking::where('flight_supplier_id', $s->id)
                ->whereDate(DB::raw('COALESCE(flight_booking_date, booking_date, departure_date, DATE(created_at))'), '<=', $asOfDate);
            $vQuery = BookingVisa::where('supplier_id', $s->id)
                ->whereDate(DB::raw('COALESCE(booking_date, DATE(created_at))'), '<=', $asOfDate);
            $tQuery = BookingTransport::where('supplier_id', $s->id)
                ->whereDate(DB::raw('COALESCE(booking_date, travel_date, DATE(created_at))'), '<=', $asOfDate);

            if ($packageType === 'hajj') {
                $hQuery->whereHas('booking', fn($q) => $q->where(fn($sub) => $sub->where('package_type', 'hajj')->orWhereNull('package_type')->orWhere('package_type', ''))->where(fn($sub) => $sub->whereNull('package_type')->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah'])));
                $fQuery->where(fn($sub) => $sub->where('package_type', 'hajj')->orWhereNull('package_type')->orWhere('package_type', ''))->where(fn($sub) => $sub->whereNull('package_type')->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']));
                $vQuery->whereHas('booking', fn($q) => $q->where(fn($sub) => $sub->where('package_type', 'hajj')->orWhereNull('package_type')->orWhere('package_type', ''))->where(fn($sub) => $sub->whereNull('package_type')->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah'])));
                $tQuery->whereHas('booking', fn($q) => $q->where(fn($sub) => $sub->where('package_type', 'hajj')->orWhereNull('package_type')->orWhere('package_type', ''))->where(fn($sub) => $sub->whereNull('package_type')->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah'])));
            } elseif ($packageType === 'umrah') {
                $hQuery->whereHas('booking', fn($q) => $q->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']));
                $fQuery->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']);
                $vQuery->whereHas('booking', fn($q) => $q->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']));
                $tQuery->whereHas('booking', fn($q) => $q->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']));
            }

            $hCost = (float) $hQuery->selectRaw('SUM(COALESCE(cost_amount, cost_per_night * no_of_rooms * no_of_nights * COALESCE(cost_roe, roe, 75))) as total')->value('total');
            $fCost = (float) $fQuery->selectRaw('SUM(CASE WHEN flight_currency = "SAR" THEN flight_cost * COALESCE(flight_cost_roe, 75) ELSE flight_cost END) as total')->value('total');
            $vCost = (float) $vQuery->selectRaw('SUM(COALESCE(cost_amount, cost_rate * COALESCE(cost_roe, roe, 75))) as total')->value('total');
            $tCost = (float) $tQuery->selectRaw('SUM(COALESCE(cost_amount, cost_rate * COALESCE(cost_roe, roe, 75))) as total')->value('total');

            $totalBilled = $hCost + $fCost + $vCost + $tCost + $sObPkr;

            $paidToSupplier = (float) ExpenseTransaction::where('supplier_id', $s->id)
                ->where('transaction_type', 'debit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');

            $net = $totalBilled - $paidToSupplier;
            if ($net > 0) {
                $totalAp += $net;
            } elseif ($net < 0) {
                $totalAdv += abs($net);
            }
        }

        return ['ap' => $totalAp, 'adv' => $totalAdv];
    }

    /**
     * Compute total Cash and Bank balance across all accounts as of a specific date.
     */
    private function calculateCashAndBankBalance(string $asOfDate): float
    {
        $totalCashAndBank = 0.0;

        // 1. Bank Accounts
        $banks = Bank::where('status', 'active')->get();
        foreach ($banks as $b) {
            $initialPkr = (float) $b->opening_balance_pkr + ((float) $b->opening_balance_sar * (float) ($b->opening_balance_roe ?: 75.0));
            $inflows = (float) ExpenseTransaction::where('bank_id', $b->id)
                ->where('transaction_type', 'credit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');
            $outflows = (float) ExpenseTransaction::where('bank_id', $b->id)
                ->where('transaction_type', 'debit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');
            $totalCashAndBank += ($initialPkr + $inflows - $outflows);
        }

        // 2. Cash Accounts
        $cashAccounts = Account::where('status', 'active')->where('account_type', 'cash')->get();
        foreach ($cashAccounts as $ca) {
            $initialPkr = (float) $ca->opening_balance_pkr + ((float) $ca->opening_balance_sar * (float) ($ca->opening_balance_roe ?: 75.0));
            $inflows = (float) ExpenseTransaction::where('account_id', $ca->id)
                ->where('transaction_type', 'credit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');
            $outflows = (float) ExpenseTransaction::where('account_id', $ca->id)
                ->where('transaction_type', 'debit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');
            $totalCashAndBank += ($initialPkr + $inflows - $outflows);
        }

        // 3. Counter Collections & Unassigned Cash
        $unassignedIn = (float) ExpenseTransaction::whereNull('bank_id')
            ->whereNull('account_id')
            ->where('transaction_type', 'credit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $unassignedOut = (float) ExpenseTransaction::whereNull('bank_id')
            ->whereNull('account_id')
            ->where('transaction_type', 'debit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $counterClientReceipts = (float) Booking::whereDate(DB::raw('COALESCE(booking_date, flight_booking_date, DATE(created_at))'), '<=', $asOfDate)
            ->selectRaw('SUM(total_received * CASE WHEN currency = "SAR" THEN COALESCE(flight_roe, 75) ELSE 1 END) as total')
            ->value('total');

        $txDirectReceipts = (float) Transaction::where('status', 'confirmed')
            ->where(function($q) {
                $q->where('payment_type', 'cash')->orWhereNull('payment_type');
            })
            ->whereDate('payment_date', '<=', $asOfDate)
            ->sum('amount');

        $counterCash = max(0, $counterClientReceipts + $txDirectReceipts + $unassignedIn - $unassignedOut);
        $totalCashAndBank += $counterCash;

        return $totalCashAndBank;
    }
}
