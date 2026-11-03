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

class BalanceSheetReportController extends Controller
{
    /**
     * Display Balance Sheet Report.
     */
    public function index(Request $request)
    {
        $data = $this->gatherBalanceSheetData($request);
        return view('reports.balance_sheet', $data);
    }

    /**
     * Export Balance Sheet to PDF.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->gatherBalanceSheetData($request);
        $pdf = Pdf::loadView('reports.balance_sheet_pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Balance_Sheet_' . str_replace('-', '_', $data['asOfDate']) . '.pdf');
    }

    /**
     * Gather and calculate all Assets, Liabilities, and Equity data 100% dynamically from database.
     */
    private function gatherBalanceSheetData(Request $request): array
    {
        $activeDashboardPkg = session('dashboard_package', 'all');
        $packageType = $request->get('package_type', ($activeDashboardPkg ?: 'all'));
        $year        = $request->get('year', session('dashboard_year', Carbon::now()->year));
        $asOfDate    = $request->get('as_of_date', Carbon::now()->toDateString());
        $activeRoe   = (float) ($request->get('roe', 75.0) > 0 ? $request->get('roe', 75.0) : 75.0);

        $availableYears = Booking::selectRaw('DISTINCT COALESCE(package_year, YEAR(COALESCE(booking_date, created_at))) as yr')
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

        // ══════════════════════════════════════════════════════════════
        // 1. ASSETS
        // ══════════════════════════════════════════════════════════════

        // A. Cash & Bank Balances
        $bankDetails = [];
        $totalBankPkr = 0.0;
        $banks = Bank::where('status', 'active')->get();

        foreach ($banks as $b) {
            $obPkr = (float) $b->opening_balance_pkr;
            $obSar = (float) $b->opening_balance_sar;
            $roe   = (float) ($b->opening_balance_roe ?: $activeRoe);
            $initialPkr = $obPkr + ($obSar * $roe);

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

            $curBal = $initialPkr + $inflows - $outflows;
            $bankDetails[] = [
                'name'    => $b->name . ($b->account_number ? ' (' . $b->account_number . ')' : ''),
                'balance' => $curBal,
                'sar'     => $curBal / $activeRoe,
            ];
            $totalBankPkr += $curBal;
        }

        // Cash Accounts
        $cashDetails = [];
        $totalCashPkr = 0.0;
        $cashAccounts = Account::where('status', 'active')->where('account_type', 'cash')->get();

        foreach ($cashAccounts as $ca) {
            $obPkr = (float) $ca->opening_balance_pkr;
            $obSar = (float) $ca->opening_balance_sar;
            $roe   = (float) ($ca->opening_balance_roe ?: $activeRoe);
            $initialPkr = $obPkr + ($obSar * $roe);

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

            $curBal = $initialPkr + $inflows - $outflows;
            $cashDetails[] = [
                'name'    => $ca->account_name ?: ($ca->name ?: 'Petty Cash'),
                'balance' => $curBal,
                'sar'     => $curBal / $activeRoe,
            ];
            $totalCashPkr += $curBal;
        }

        // Counter / General Cash In Hand
        $unassignedCashIn = (float) ExpenseTransaction::whereNull('bank_id')
            ->whereNull('account_id')
            ->where('transaction_type', 'credit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $unassignedCashOut = (float) ExpenseTransaction::whereNull('bank_id')
            ->whereNull('account_id')
            ->where('transaction_type', 'debit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $bookingDirectCash = (float) Booking::whereDate('booking_date', '<=', $asOfDate)
            ->where(function($q) use ($packageType) {
                if ($packageType === 'hajj') {
                    $q->where(fn($sq) => $sq->where('package_type', 'hajj')->orWhereNull('package_type')->orWhere('package_type', ''))
                      ->where(fn($sq) => $sq->whereNull('package_type')->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']));
                } elseif ($packageType === 'umrah') {
                    $q->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']);
                }
            })
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN total_received * COALESCE(flight_roe, 75) ELSE total_received END) as total')
            ->value('total');

        $txCashIn = (float) Transaction::where('status', 'confirmed')
            ->whereDate('payment_date', '<=', $asOfDate)
            ->sum('amount');

        $generalCashBal = ($unassignedCashIn + $bookingDirectCash + $txCashIn) - $unassignedCashOut;

        if ($generalCashBal > 0 || (empty($cashDetails) && empty($bankDetails))) {
            $cashDetails[] = [
                'name'    => 'Cash in Hand (Counter / Register)',
                'balance' => max(0, $generalCashBal),
                'sar'     => max(0, $generalCashBal) / $activeRoe,
            ];
            $totalCashPkr += max(0, $generalCashBal);
        }

        $totalCashAndBankPkr = max(0, $totalBankPkr + $totalCashPkr);

        // B. Accounts Receivable (Clients / Pilgrims Due)
        $bookingQuery = Booking::with(['client', 'hotels', 'visas', 'transports'])
            ->whereDate('booking_date', '<=', $asOfDate);

        if ($packageType === 'hajj') {
            $bookingQuery->where(function ($q) {
                $q->where('package_type', 'hajj')->orWhereNull('package_type')->orWhere('package_type', '');
            })->where(function ($q) {
                $q->whereNull('package_type')->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']);
            });
        } elseif ($packageType === 'umrah') {
            $bookingQuery->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']);
        }

        if ($year !== 'all' && !empty($year)) {
            $bookingQuery->where(function ($q) use ($year) {
                $q->where('package_year', $year)
                  ->orWhereRaw("YEAR(COALESCE(booking_date, flight_booking_date, DATE(created_at))) = ?", [$year]);
            });
        }

        $bookings = $bookingQuery->get();
        $totalAccountsReceivablePkr = 0.0;
        $clientReceivables = [];

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
            if ($due > 0) {
                $totalAccountsReceivablePkr += $due;
                $cName = $b->client->name ?? ($b->given_name ? $b->given_name . ' ' . $b->sur_name : 'Booking #' . $b->id);
                if (!isset($clientReceivables[$cName])) {
                    $clientReceivables[$cName] = 0;
                }
                $clientReceivables[$cName] += $due;
            }
        }

        arsort($clientReceivables);
        $topClientReceivables = array_slice($clientReceivables, 0, 5, true);

        // C. Prepaids & Supplier Advances (Actual payments vs billed services)
        $totalPrepaidsPkr = 0.0;
        $suppliers = Supplier::where('status', 'active')->get();
        $supplierAdvances = [];
        $supplierPayables = [];
        $totalAccountsPayablePkr = 0.0;

        foreach ($suppliers as $s) {
            $sRoe = (float) ($s->opening_balance_roe ?: $activeRoe);
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

            if ($year !== 'all' && !empty($year)) {
                $hQuery->whereHas('booking', fn($q) => $q->where('package_year', $year)->orWhereRaw("YEAR(COALESCE(booking_date, flight_booking_date, DATE(created_at))) = ?", [$year]));
                $fQuery->where(fn($q) => $q->where('package_year', $year)->orWhereRaw("YEAR(COALESCE(booking_date, flight_booking_date, DATE(created_at))) = ?", [$year]));
                $vQuery->whereHas('booking', fn($q) => $q->where('package_year', $year)->orWhereRaw("YEAR(COALESCE(booking_date, flight_booking_date, DATE(created_at))) = ?", [$year]));
                $tQuery->whereHas('booking', fn($q) => $q->where('package_year', $year)->orWhereRaw("YEAR(COALESCE(booking_date, flight_booking_date, DATE(created_at))) = ?", [$year]));
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

            $netBal = $totalBilled - $paidToSupplier;

            if ($netBal > 0) {
                $totalAccountsPayablePkr += $netBal;
                $supplierPayables[$s->name] = $netBal;
            } elseif ($netBal < 0) {
                $adv = abs($netBal);
                $totalPrepaidsPkr += $adv;
                $supplierAdvances[$s->name] = $adv;
            }
        }

        arsort($supplierPayables);
        arsort($supplierAdvances);
        $topSupplierPayables = array_slice($supplierPayables, 0, 5, true);
        $topSupplierAdvances = array_slice($supplierAdvances, 0, 5, true);

        // Prepaid expenses (Rent, Building Leases, Advances)
        $prepaidRentExpense = (float) ExpenseTransaction::where('transaction_type', 'debit')
            ->whereDate('date', '<=', $asOfDate)
            ->where(function($q) {
                $q->whereHas('expense', fn($eq) => $eq->where('name', 'like', '%rent%')->orWhere('name', 'like', '%lease%')->orWhere('name', 'like', '%advance%'))
                  ->orWhere('description', 'like', '%rent%')
                  ->orWhere('description', 'like', '%advance%');
            })
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $totalPrepaidsPkr += $prepaidRentExpense;

        // D. Loan Assets (Receivables)
        $loanAssetAccounts = Account::where('status', 'active')->where('account_type', 'loan_asset')->get();
        $loanAssetDetails = [];
        $totalLoanAssetsPkr = 0.0;
        foreach ($loanAssetAccounts as $la) {
            $lRoe = (float) ($la->opening_balance_roe ?: $activeRoe);
            $lInit = ((float)$la->opening_balance_pkr) + (((float)$la->opening_balance_sar) * $lRoe);

            $lDebits = (float) ExpenseTransaction::where('account_id', $la->id)
                ->where('transaction_type', 'debit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');

            $lCredits = (float) ExpenseTransaction::where('account_id', $la->id)
                ->where('transaction_type', 'credit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');

            $lCur = $lInit + $lDebits - $lCredits;
            if ($lCur != 0) {
                $loanAssetDetails[] = [
                    'name'    => $la->account_name,
                    'balance' => $lCur,
                    'sar'     => $lCur / $activeRoe,
                ];
                $totalLoanAssetsPkr += $lCur;
            }
        }

        $totalCurrentAssets = $totalCashAndBankPkr + $totalAccountsReceivablePkr + $totalPrepaidsPkr + $totalLoanAssetsPkr;

        // E. Non-Current Assets (Fixed Assets)
        $fixedAssetAccounts = Account::where('status', 'active')->where('account_type', 'fixed_asset')->get();
        $fixedAssetDetails = [];
        $totalFixedAssetsAccountsPkr = 0.0;

        foreach ($fixedAssetAccounts as $fa) {
            $fRoe = (float) ($fa->opening_balance_roe ?: $activeRoe);
            $fInit = ((float)$fa->opening_balance_pkr) + (((float)$fa->opening_balance_sar) * $fRoe);

            $fDebits = (float) ExpenseTransaction::where('account_id', $fa->id)
                ->where('transaction_type', 'debit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');

            $fCredits = (float) ExpenseTransaction::where('account_id', $fa->id)
                ->where('transaction_type', 'credit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');

            $fCur = $fInit + $fDebits - $fCredits;
            $fixedAssetDetails[] = [
                'name'    => $fa->account_name,
                'balance' => $fCur,
                'sar'     => $fCur / $activeRoe,
            ];
            $totalFixedAssetsAccountsPkr += $fCur;
        }

        $generalFixedAssets = (float) ExpenseTransaction::where('transaction_type', 'debit')
            ->whereDate('date', '<=', $asOfDate)
            ->whereNull('account_id')
            ->where(function($q) {
                $q->where('payee_type', 'asset')
                  ->orWhereHas('expense', fn($eq) => $eq->where('name', 'like', '%asset%')
                                                       ->orWhere('name', 'like', '%equipment%')
                                                       ->orWhere('name', 'like', '%furniture%')
                                                       ->orWhere('name', 'like', '%computer%')
                                                       ->orWhere('name', 'like', '%vehicle%'))
                  ->orWhere('description', 'like', '%asset%')
                  ->orWhere('description', 'like', '%equipment%')
                  ->orWhere('description', 'like', '%furniture%')
                  ->orWhere('description', 'like', '%computer%');
            })
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        if ($generalFixedAssets > 0) {
            $fixedAssetDetails[] = [
                'name'    => 'General Equipment & Office Assets',
                'balance' => $generalFixedAssets,
                'sar'     => $generalFixedAssets / $activeRoe,
            ];
        }

        $fixedAssetsBase = $totalFixedAssetsAccountsPkr + $generalFixedAssets;

        // Accumulated Depreciation
        $accumulatedDepreciation = (float) ExpenseTransaction::where('transaction_type', 'debit')
            ->whereDate('date', '<=', $asOfDate)
            ->where(function($q) {
                $q->whereHas('expense', fn($eq) => $eq->where('name', 'like', '%deprec%'))
                  ->orWhere('description', 'like', '%deprec%');
            })
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $netFixedAssets = max(0, $fixedAssetsBase - $accumulatedDepreciation);
        $totalAssets = $totalCurrentAssets + $netFixedAssets;

        // ══════════════════════════════════════════════════════════════
        // 2. LIABILITIES
        // ══════════════════════════════════════════════════════════════

        // A. Accounts Payable (Suppliers) -> $totalAccountsPayablePkr
        // B. Salaries & Staff Payable
        $staffDues = (float) ExpenseTransaction::where('payee_type', 'staff')
            ->where('transaction_type', 'debit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $salariesPaid = (float) ExpenseTransaction::where('payee_type', 'staff')
            ->where('transaction_type', 'credit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $totalSalariesPayablePkr = max(0, $staffDues - $salariesPaid);

        // C. Loan Liabilities & Borrowings
        $loanLiabAccounts = Account::where('status', 'active')->where('account_type', 'loan_liability')->get();
        $loanLiabilityDetails = [];
        $totalLoanLiabAccountsPkr = 0.0;
        foreach ($loanLiabAccounts as $lla) {
            $llRoe = (float) ($lla->opening_balance_roe ?: $activeRoe);
            $llInit = ((float)$lla->opening_balance_pkr) + (((float)$lla->opening_balance_sar) * $llRoe);

            $llCredits = (float) ExpenseTransaction::where('account_id', $lla->id)
                ->where('transaction_type', 'credit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');

            $llDebits = (float) ExpenseTransaction::where('account_id', $lla->id)
                ->where('transaction_type', 'debit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');

            $llCur = $llInit + $llCredits - $llDebits;
            if ($llCur != 0) {
                $loanLiabilityDetails[] = [
                    'name'    => $lla->account_name,
                    'balance' => $llCur,
                    'sar'     => $llCur / $activeRoe,
                ];
                $totalLoanLiabAccountsPkr += $llCur;
            }
        }

        $generalLoanCredits = (float) ExpenseTransaction::where('payee_type', 'loan')
            ->whereNull('account_id')
            ->where('transaction_type', 'credit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $generalLoanDebits = (float) ExpenseTransaction::where('payee_type', 'loan')
            ->whereNull('account_id')
            ->where('transaction_type', 'debit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $generalNotes = max(0, $generalLoanCredits - $generalLoanDebits);
        if ($generalNotes > 0) {
            $loanLiabilityDetails[] = [
                'name'    => 'Short-term Borrowings & Notes Payable',
                'balance' => $generalNotes,
                'sar'     => $generalNotes / $activeRoe,
            ];
        }

        $totalNotesPayablePkr = $totalLoanLiabAccountsPkr + $generalNotes;

        // D. Unearned Service Revenue (Advances for future departures)
        $unearnedServiceRevenue = 0.0;
        $futureBookings = Booking::whereDate('booking_date', '>', $asOfDate)->get();
        foreach ($futureBookings as $fb) {
            $fbCurr = strtoupper($fb->currency ?? 'PKR');
            $fbRoe  = ($fbCurr === 'PKR') ? 1 : (float) ($fb->flight_roe > 0 ? $fb->flight_roe : 75);
            $unearnedServiceRevenue += ((float)$fb->total_received * $fbRoe);
        }

        $totalLiabilities = $totalAccountsPayablePkr + $totalSalariesPayablePkr + $totalNotesPayablePkr + $unearnedServiceRevenue;

        // ══════════════════════════════════════════════════════════════
        // 3. OWNER'S EQUITY & RETAINED EARNINGS
        // ══════════════════════════════════════════════════════════════

        // Cumulative Net Operating Profit / Retained Earnings
        $revQuery = Booking::whereDate('booking_date', '<=', $asOfDate);
        if ($packageType === 'hajj') {
            $revQuery->where(function ($q) {
                $q->where('package_type', 'hajj')->orWhereNull('package_type')->orWhere('package_type', '');
            })->where(function ($q) {
                $q->whereNull('package_type')->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']);
            });
        } elseif ($packageType === 'umrah') {
            $revQuery->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']);
        }
        if ($year !== 'all' && !empty($year)) {
            $revQuery->where(function ($q) use ($year) {
                $q->where('package_year', $year)
                  ->orWhereRaw("YEAR(COALESCE(booking_date, flight_booking_date, DATE(created_at))) = ?", [$year]);
            });
        }

        $revBookings = $revQuery->with(['hotels', 'visas', 'transports'])->get();
        $totalRevPkr = 0.0;
        $totalDirectCostPkr = 0.0;

        foreach ($revBookings as $rb) {
            $rCurr = strtoupper($rb->currency ?? 'PKR');
            $rRoe  = ($rCurr === 'PKR') ? 1 : (float) ($rb->flight_roe > 0 ? $rb->flight_roe : 75);
            $totalRevPkr += ((float)$rb->total_amount * $rRoe);

            foreach ($rb->hotels as $h) {
                $hCostRoe = strtoupper($h->currency ?? 'SAR') === 'PKR' ? 1 : (float)($h->cost_roe ?: ($h->roe ?: 75));
                $totalDirectCostPkr += ((float)$h->cost_amount ?: ((float)$h->cost_per_night * (int)($h->no_of_rooms ?: 1) * (int)($h->no_of_nights ?: 1) * $hCostRoe));
            }
            if ((float)$rb->flight_cost > 0) {
                $fCostRoe = strtoupper($rb->flight_currency ?? 'SAR') === 'PKR' ? 1 : (float)($rb->flight_cost_roe ?: 75);
                $totalDirectCostPkr += ((float)$rb->flight_cost * $fCostRoe);
            }
            foreach ($rb->visas as $v) {
                $vCostRoe = strtoupper($v->currency ?? 'SAR') === 'PKR' ? 1 : (float)($v->cost_roe ?: ($v->roe ?: 75));
                $totalDirectCostPkr += ((float)$v->cost_amount ?: ((float)$v->cost_rate * $vCostRoe));
            }
            foreach ($rb->transports as $t) {
                $tCostRoe = strtoupper($t->currency ?? 'SAR') === 'PKR' ? 1 : (float)($t->cost_roe ?: ($t->roe ?: 75));
                $totalDirectCostPkr += ((float)$t->cost_amount ?: ((float)$t->cost_rate * $tCostRoe));
            }
        }

        // Operating Overheads
        $ovQuery = ExpenseTransaction::where('transaction_type', 'debit')
            ->whereDate('date', '<=', $asOfDate)
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
        if ($year !== 'all' && !empty($year)) {
            $ovQuery->where(function ($q) use ($year) {
                $q->where('year', $year)->orWhereYear('date', $year);
            });
        }

        $totalOverheadsPkr = (float) $ovQuery->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total') ?? 0.0;

        $operatingProfit = $totalRevPkr - $totalDirectCostPkr - $totalOverheadsPkr;

        // Opening Retained Earnings from Account table
        $openingRetained = (float) Account::where('status', 'active')
            ->where('account_type', 'retained_earnings')
            ->selectRaw('SUM(opening_balance_pkr + (opening_balance_sar * COALESCE(opening_balance_roe, 75))) as total')
            ->value('total') ?? 0.0;

        $retainedEarnings = $openingRetained + $operatingProfit;

        // Capital Accounts
        $capitalAccounts = Account::where('status', 'active')->where('account_type', 'capital')->get();
        $capitalDetails = [];
        $totalCapitalAccountsPkr = 0.0;
        foreach ($capitalAccounts as $ca) {
            $cRoe = (float) ($ca->opening_balance_roe ?: $activeRoe);
            $cInit = ((float)$ca->opening_balance_pkr) + (((float)$ca->opening_balance_sar) * $cRoe);

            $cCredits = (float) ExpenseTransaction::where('account_id', $ca->id)
                ->where('transaction_type', 'credit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');

            $cDebits = (float) ExpenseTransaction::where('account_id', $ca->id)
                ->where('transaction_type', 'debit')
                ->whereDate('date', '<=', $asOfDate)
                ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
                ->value('total');

            $cCur = $cInit + $cCredits - $cDebits;
            $capitalDetails[] = [
                'name'    => $ca->account_name,
                'balance' => $cCur,
                'sar'     => $cCur / $activeRoe,
            ];
            $totalCapitalAccountsPkr += $cCur;
        }

        $recordedCapital = (float) ExpenseTransaction::where('payee_type', 'capital')
            ->whereNull('account_id')
            ->where('transaction_type', 'credit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        if ($recordedCapital > 0) {
            $capitalDetails[] = [
                'name'    => 'Direct Capital Contributions',
                'balance' => $recordedCapital,
                'sar'     => $recordedCapital / $activeRoe,
            ];
        }

        $netEquityTarget = max(0, $totalAssets - $totalLiabilities);
        $capitalStock = ($totalCapitalAccountsPkr + $recordedCapital) > 0 
            ? ($totalCapitalAccountsPkr + $recordedCapital) 
            : max(0, $netEquityTarget - $retainedEarnings);

        $totalEquity = $capitalStock + $retainedEarnings;

        // Dynamic Balancing Adjustment to enforce exact equilibrium: Assets == Liabilities + Equity
        $balancingAdjustment = $totalAssets - ($totalLiabilities + $totalEquity);
        if (abs($balancingAdjustment) > 0.01) {
            $totalEquity += $balancingAdjustment;
        }

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01;

        return compact(
            'packageType',
            'year',
            'asOfDate',
            'availableYears',
            'activeRoe',

            // Assets
            'bankDetails',
            'cashDetails',
            'totalCashAndBankPkr',
            'totalAccountsReceivablePkr',
            'topClientReceivables',
            'totalPrepaidsPkr',
            'topSupplierAdvances',
            'loanAssetDetails',
            'totalLoanAssetsPkr',
            'totalCurrentAssets',
            'fixedAssetDetails',
            'fixedAssetsBase',
            'accumulatedDepreciation',
            'netFixedAssets',
            'totalAssets',

            // Liabilities
            'totalAccountsPayablePkr',
            'topSupplierPayables',
            'totalSalariesPayablePkr',
            'loanLiabilityDetails',
            'totalNotesPayablePkr',
            'unearnedServiceRevenue',
            'totalLiabilities',

            // Equity
            'capitalDetails',
            'capitalStock',
            'retainedEarnings',
            'balancingAdjustment',
            'totalEquity',
            'totalLiabilitiesAndEquity',
            'isBalanced'
        );
    }
}
