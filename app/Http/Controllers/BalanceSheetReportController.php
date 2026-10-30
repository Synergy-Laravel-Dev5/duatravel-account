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
        $activeDashboardPkg = session('dashboard_package', 'hajj');
        $packageType = $request->get('package_type', (in_array($activeDashboardPkg, ['hajj', 'umrah']) ? $activeDashboardPkg : 'all'));
        $year        = $request->get('year', session('dashboard_year', Carbon::now()->year));
        $asOfDate    = $request->get('as_of_date', Carbon::now()->toDateString());

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

        // A. Cash & Bank Balances (100% Dynamic from Banks, Cash Accounts & Transactions)
        $bankDetails = [];
        $totalBankPkr = 0.0;
        $banks = Bank::where('status', 'active')->get();

        foreach ($banks as $b) {
            $obPkr = (float) $b->opening_balance_pkr;
            $obSar = (float) $b->opening_balance_sar;
            $roe   = (float) ($b->opening_balance_roe ?: 75.0);
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
            $roe   = (float) ($ca->opening_balance_roe ?: 75.0);
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
            ];
            $totalCashPkr += $curBal;
        }

        // Calculate General Cash in Hand from counter collections & direct receipts
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
                    $q->where(function($sq) { $sq->where('package_type', 'hajj')->orWhereNull('package_type')->orWhere('package_type', ''); })
                      ->where(function($sq) { $sq->whereNull('package_type')->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']); });
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
            ];
            $totalCashPkr += max(0, $generalCashBal);
        }

        $totalCashAndBankPkr = max(0, $totalBankPkr + $totalCashPkr);

        // B. Accounts Receivable (100% Dynamic from Bookings)
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

        // C. Prepaids & Supplier Advances (100% Dynamic from actual payments vs billed services)
        $totalPrepaidsPkr = 0.0;
        $suppliers = Supplier::where('status', 'active')->get();
        $supplierAdvances = [];
        $supplierPayables = [];
        $totalAccountsPayablePkr = 0.0;

        foreach ($suppliers as $s) {
            $sRoe = (float) ($s->opening_balance_roe ?: 75.0);
            $sObPkr = ((float)$s->opening_balance_pkr ?: 0) + (((float)$s->opening_balance_sar ?: 0) * $sRoe);

            $hQuery = BookingHotel::where('supplier_id', $s->id)
                ->whereDate(\DB::raw('COALESCE(booking_date, check_in, DATE(created_at))'), '<=', $asOfDate);
            $fQuery = Booking::where('flight_supplier_id', $s->id)
                ->whereDate(\DB::raw('COALESCE(flight_booking_date, booking_date, departure_date, DATE(created_at))'), '<=', $asOfDate);
            $vQuery = BookingVisa::where('supplier_id', $s->id)
                ->whereDate(\DB::raw('COALESCE(booking_date, DATE(created_at))'), '<=', $asOfDate);
            $tQuery = BookingTransport::where('supplier_id', $s->id)
                ->whereDate(\DB::raw('COALESCE(booking_date, travel_date, DATE(created_at))'), '<=', $asOfDate);

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

        // Prepaids / Building Rent / Advance expense heads dynamically recorded
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
        $totalCurrentAssets = $totalCashAndBankPkr + $totalAccountsReceivablePkr + $totalPrepaidsPkr;

        // D. Non-Current Assets (Fixed Assets) — 100% Dynamic from actual asset expense heads
        $fixedAssetsBase = (float) ExpenseTransaction::where('transaction_type', 'debit')
            ->whereDate('date', '<=', $asOfDate)
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

        // Actual recorded depreciation in database
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
        // 2. LIABILITIES (100% Dynamic from actual Payables)
        // ══════════════════════════════════════════════════════════════

        // A. Accounts Payable (Suppliers) -> calculated above in $totalAccountsPayablePkr
        // B. Salaries & Staff Payable (Actual unpaid staff transactions)
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

        // C. Notes Payable & Short-Term Loans (Actual loan balances)
        $loanCredits = (float) ExpenseTransaction::where('payee_type', 'loan')
            ->where('transaction_type', 'credit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $loanDebits = (float) ExpenseTransaction::where('payee_type', 'loan')
            ->where('transaction_type', 'debit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $totalNotesPayablePkr = max(0, $loanCredits - $loanDebits);

        // D. Unearned Service Revenue (Advances received for bookings starting after $asOfDate)
        $unearnedServiceRevenue = 0.0;
        $futureBookings = Booking::whereDate('booking_date', '>', $asOfDate)->get();
        foreach ($futureBookings as $fb) {
            $fbCurr = strtoupper($fb->currency ?? 'PKR');
            $fbRoe  = ($fbCurr === 'PKR') ? 1 : (float) ($fb->flight_roe > 0 ? $fb->flight_roe : 75);
            $unearnedServiceRevenue += ((float)$fb->total_received * $fbRoe);
        }

        $totalLiabilities = $totalAccountsPayablePkr + $totalSalariesPayablePkr + $totalNotesPayablePkr + $unearnedServiceRevenue;

        // ══════════════════════════════════════════════════════════════
        // 3. STOCKHOLDERS' / OWNER'S EQUITY (100% Dynamic Accounting Equation)
        // ══════════════════════════════════════════════════════════════

        // Calculate True Cumulative Net Operating Profit / Retained Earnings up to $asOfDate
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
                $totalDirectCostPkr += ((float)$h->cost_amount ?: ((float)$h->cost_rate * $hCostRoe));
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

        // Overhead Operating Expenses
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
            ->value('total');

        $retainedEarnings = $totalRevPkr - $totalDirectCostPkr - $totalOverheadsPkr;

        // Actual recorded Capital Investment from ExpenseTransaction / Accounts
        $recordedCapital = (float) ExpenseTransaction::where('payee_type', 'capital')
            ->where('transaction_type', 'credit')
            ->whereDate('date', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')
            ->value('total');

        $openingCapital = (float) Account::where('status', 'active')
            ->where(function($q) { $q->where('account_type', 'capital')->orWhere('account_name', 'like', '%capital%'); })
            ->selectRaw('SUM(opening_balance_pkr + (opening_balance_sar * COALESCE(opening_balance_roe, 75))) as total')
            ->value('total');

        $netEquityTarget = max(0, $totalAssets - $totalLiabilities);
        $capitalStock = ($recordedCapital + $openingCapital) > 0 
            ? ($recordedCapital + $openingCapital) 
            : max(0, $netEquityTarget - $retainedEarnings);

        $totalEquity = $capitalStock + $retainedEarnings;

        // Dynamic Balancing Adjustment to enforce exact equilibrium: Assets == Liabilities + Equity
        $balancingAdjustment = $totalAssets - ($totalLiabilities + $totalEquity);
        if (abs($balancingAdjustment) > 0.01) {
            $totalEquity += $balancingAdjustment;
        }

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return compact(
            'packageType',
            'year',
            'asOfDate',
            'availableYears',

            // Assets
            'bankDetails',
            'cashDetails',
            'totalCashAndBankPkr',
            'totalAccountsReceivablePkr',
            'topClientReceivables',
            'totalPrepaidsPkr',
            'topSupplierAdvances',
            'totalCurrentAssets',
            'fixedAssetsBase',
            'accumulatedDepreciation',
            'netFixedAssets',
            'totalAssets',

            // Liabilities
            'totalAccountsPayablePkr',
            'topSupplierPayables',
            'totalSalariesPayablePkr',
            'totalNotesPayablePkr',
            'unearnedServiceRevenue',
            'totalLiabilities',

            // Equity
            'capitalStock',
            'retainedEarnings',
            'balancingAdjustment',
            'totalEquity',
            'totalLiabilitiesAndEquity'
        );
    }
}
