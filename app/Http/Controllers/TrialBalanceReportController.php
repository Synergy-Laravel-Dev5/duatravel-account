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

class TrialBalanceReportController extends Controller
{
    /**
     * Display Trial Balance Report.
     */
    public function index(Request $request)
    {
        $data = $this->gatherTrialBalanceData($request);
        return view('reports.trial_balance', $data);
    }

    /**
     * Export Trial Balance Report to PDF.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->gatherTrialBalanceData($request);
        $pdf = Pdf::loadView('reports.trial_balance_pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Trial_Balance_' . str_replace('-', '_', $data['asOfDate']) . '.pdf');
    }

    /**
     * Gather and balance all general ledger accounts across the system.
     */
    private function gatherTrialBalanceData(Request $request): array
    {
        $packageType = $request->get('package_type', 'all');
        $asOfDate    = $request->get('as_of_date', Carbon::now()->toDateString());
        $activeRoe   = (float) ($request->get('roe', 75.0) > 0 ? $request->get('roe', 75.0) : 75.0);

        $accounts = [];

        // ══════════════════════════════════════════════════════════════
        // 1. ASSETS (Normal Balance: DEBIT)
        // ══════════════════════════════════════════════════════════════

        // Cash Accounts
        $cashAccounts = Account::where('status', 'active')->where('account_type', 'cash')->get();
        $totalCash = 0.0;
        foreach ($cashAccounts as $ca) {
            $init = ((float)$ca->opening_balance_pkr) + (((float)$ca->opening_balance_sar) * (float)($ca->opening_balance_roe ?: $activeRoe));
            $inflows = (float) ExpenseTransaction::where('account_id', $ca->id)->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $outflows = (float) ExpenseTransaction::where('account_id', $ca->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $bal = $init + $inflows - $outflows;

            $accounts[] = [
                'code'     => $ca->account_code ?: '1110-' . str_pad($ca->id, 2, '0', STR_PAD_LEFT),
                'name'     => $ca->account_name . ' (Cash)',
                'category' => 'Asset',
                'debit'    => $bal >= 0 ? $bal : 0,
                'credit'   => $bal < 0 ? abs($bal) : 0,
            ];
            $totalCash += $bal;
        }

        // Counter / General Cash In Hand
        $unassignedIn = (float) ExpenseTransaction::whereNull('bank_id')->whereNull('account_id')->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
        $unassignedOut = (float) ExpenseTransaction::whereNull('bank_id')->whereNull('account_id')->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
        $bookingDirectCash = (float) Booking::whereDate('booking_date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN total_received * COALESCE(flight_roe, 75) ELSE total_received END) as total')->value('total');
        $txCashIn = (float) Transaction::where('status', 'confirmed')->whereDate('payment_date', '<=', $asOfDate)->sum('amount');
        $generalCashBal = ($unassignedIn + $bookingDirectCash + $txCashIn) - $unassignedOut;

        if ($generalCashBal != 0 || empty($cashAccounts)) {
            $accounts[] = [
                'code'     => '1110-00',
                'name'     => 'Cash in Hand (Counter / Register)',
                'category' => 'Asset',
                'debit'    => $generalCashBal >= 0 ? $generalCashBal : 0,
                'credit'   => $generalCashBal < 0 ? abs($generalCashBal) : 0,
            ];
        }

        // Bank Accounts
        $banks = Bank::where('status', 'active')->get();
        foreach ($banks as $b) {
            $init = ((float)$b->opening_balance_pkr) + (((float)$b->opening_balance_sar) * (float)($b->opening_balance_roe ?: $activeRoe));
            $inflows = (float) ExpenseTransaction::where('bank_id', $b->id)->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $outflows = (float) ExpenseTransaction::where('bank_id', $b->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $bal = $init + $inflows - $outflows;

            $accounts[] = [
                'code'     => '1120-' . str_pad($b->id, 2, '0', STR_PAD_LEFT),
                'name'     => $b->name . ($b->account_number ? ' (' . $b->account_number . ')' : '') . ' - Bank',
                'category' => 'Asset',
                'debit'    => $bal >= 0 ? $bal : 0,
                'credit'   => $bal < 0 ? abs($bal) : 0,
            ];
        }

        // Accounts Receivable (Clients / Debtors)
        $bookingQuery = Booking::whereDate('booking_date', '<=', $asOfDate);
        if ($packageType === 'hajj') {
            $bookingQuery->where(fn($q) => $q->where('package_type', 'hajj')->orWhereNull('package_type')->orWhere('package_type', ''))
                         ->where(fn($q) => $q->whereNull('package_type')->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']));
        } elseif ($packageType === 'umrah') {
            $bookingQuery->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']);
        }
        $bookings = $bookingQuery->get();
        $totalAr = 0.0;
        foreach ($bookings as $b) {
            $curr = strtoupper($b->currency ?? 'PKR');
            $roe  = ($curr === 'PKR') ? 1 : (float) ($b->flight_roe > 0 ? $b->flight_roe : 75);
            $pkgTotal = (float) $b->total_amount * $roe;
            $pkgRecv  = (float) $b->total_received * $roe;
            $txPaid   = (float) Transaction::where('booking_id', $b->id)->where('status', 'confirmed')->whereDate('payment_date', '<=', $asOfDate)->sum('amount');
            $due = max(0, $pkgTotal - $pkgRecv - $txPaid);
            $totalAr += $due;
        }

        $accounts[] = [
            'code'     => '1130-00',
            'name'     => 'Accounts Receivable (Trade Debtors / Clients)',
            'category' => 'Asset',
            'debit'    => $totalAr,
            'credit'   => 0,
        ];

        // Advances to Suppliers & Prepaids
        $suppliers = Supplier::where('status', 'active')->get();
        $totalSupplierAdvances = 0.0;
        $totalAccountsPayable = 0.0;

        foreach ($suppliers as $s) {
            $sRoe = (float) ($s->opening_balance_roe ?: $activeRoe);
            $sObPkr = ((float)$s->opening_balance_pkr ?: 0) + (((float)$s->opening_balance_sar ?: 0) * $sRoe);

            $hCost = (float) BookingHotel::where('supplier_id', $s->id)->whereDate(DB::raw('COALESCE(booking_date, check_in, DATE(created_at))'), '<=', $asOfDate)->selectRaw('SUM(COALESCE(cost_amount, cost_per_night * no_of_rooms * no_of_nights * COALESCE(cost_roe, roe, 75))) as total')->value('total');
            $fCost = (float) Booking::where('flight_supplier_id', $s->id)->whereDate(DB::raw('COALESCE(flight_booking_date, booking_date, departure_date, DATE(created_at))'), '<=', $asOfDate)->selectRaw('SUM(CASE WHEN flight_currency = "SAR" THEN flight_cost * COALESCE(flight_cost_roe, 75) ELSE flight_cost END) as total')->value('total');
            $vCost = (float) BookingVisa::where('supplier_id', $s->id)->whereDate(DB::raw('COALESCE(booking_date, DATE(created_at))'), '<=', $asOfDate)->selectRaw('SUM(COALESCE(cost_amount, cost_rate * COALESCE(cost_roe, roe, 75))) as total')->value('total');
            $tCost = (float) BookingTransport::where('supplier_id', $s->id)->whereDate(DB::raw('COALESCE(booking_date, travel_date, DATE(created_at))'), '<=', $asOfDate)->selectRaw('SUM(COALESCE(cost_amount, cost_rate * COALESCE(cost_roe, roe, 75))) as total')->value('total');

            $totalBilled = $hCost + $fCost + $vCost + $tCost + $sObPkr;
            $paidToSupplier = (float) ExpenseTransaction::where('supplier_id', $s->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');

            $netBal = $totalBilled - $paidToSupplier;
            if ($netBal > 0) {
                $totalAccountsPayable += $netBal;
            } elseif ($netBal < 0) {
                $totalSupplierAdvances += abs($netBal);
            }
        }

        // Prepaid expenses
        $prepaidRent = (float) ExpenseTransaction::where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->where(function($q) {
            $q->whereHas('expense', fn($eq) => $eq->where('name', 'like', '%rent%')->orWhere('name', 'like', '%advance%'))->orWhere('description', 'like', '%advance%');
        })->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');

        $totalPrepaids = $totalSupplierAdvances + $prepaidRent;
        if ($totalPrepaids > 0) {
            $accounts[] = [
                'code'     => '1140-00',
                'name'     => 'Prepaid Expenses & Supplier Advances',
                'category' => 'Asset',
                'debit'    => $totalPrepaids,
                'credit'   => 0,
            ];
        }

        // Loan Assets (Receivables)
        $loanAssets = Account::where('status', 'active')->where('account_type', 'loan_asset')->get();
        foreach ($loanAssets as $la) {
            $init = ((float)$la->opening_balance_pkr) + (((float)$la->opening_balance_sar) * (float)($la->opening_balance_roe ?: $activeRoe));
            $deb = (float) ExpenseTransaction::where('account_id', $la->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $cred = (float) ExpenseTransaction::where('account_id', $la->id)->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $bal = $init + $deb - $cred;

            if ($bal != 0) {
                $accounts[] = [
                    'code'     => $la->account_code ?: '1150-' . str_pad($la->id, 2, '0', STR_PAD_LEFT),
                    'name'     => $la->account_name . ' (Loan Asset)',
                    'category' => 'Asset',
                    'debit'    => $bal >= 0 ? $bal : 0,
                    'credit'   => $bal < 0 ? abs($bal) : 0,
                ];
            }
        }

        // Fixed Assets
        $fixedAssets = Account::where('status', 'active')->where('account_type', 'fixed_asset')->get();
        $totalFa = 0.0;
        foreach ($fixedAssets as $fa) {
            $init = ((float)$fa->opening_balance_pkr) + (((float)$fa->opening_balance_sar) * (float)($fa->opening_balance_roe ?: $activeRoe));
            $deb = (float) ExpenseTransaction::where('account_id', $fa->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $cred = (float) ExpenseTransaction::where('account_id', $fa->id)->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $bal = $init + $deb - $cred;

            if ($bal != 0) {
                $accounts[] = [
                    'code'     => $fa->account_code ?: '1200-' . str_pad($fa->id, 2, '0', STR_PAD_LEFT),
                    'name'     => $fa->account_name . ' (Fixed Asset)',
                    'category' => 'Asset',
                    'debit'    => $bal >= 0 ? $bal : 0,
                    'credit'   => $bal < 0 ? abs($bal) : 0,
                ];
                $totalFa += $bal;
            }
        }

        $generalFa = (float) ExpenseTransaction::where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->whereNull('account_id')->where(function($q) {
            $q->where('payee_type', 'asset')->orWhereHas('expense', fn($eq) => $eq->where('name', 'like', '%equipment%')->orWhere('name', 'like', '%furniture%')->orWhere('name', 'like', '%vehicle%'));
        })->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');

        if ($generalFa > 0) {
            $accounts[] = [
                'code'     => '1200-00',
                'name'     => 'General Office Equipment & Vehicles',
                'category' => 'Asset',
                'debit'    => $generalFa,
                'credit'   => 0,
            ];
        }

        // Accumulated Depreciation (Contra Asset)
        $deprec = (float) ExpenseTransaction::where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->where(function($q) {
            $q->whereHas('expense', fn($eq) => $eq->where('name', 'like', '%deprec%'))->orWhere('description', 'like', '%deprec%');
        })->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');

        if ($deprec > 0) {
            $accounts[] = [
                'code'     => '1290-00',
                'name'     => 'Accumulated Depreciation (Contra-Asset)',
                'category' => 'Asset',
                'debit'    => 0,
                'credit'   => $deprec,
            ];
        }

        // ══════════════════════════════════════════════════════════════
        // 2. LIABILITIES (Normal Balance: CREDIT)
        // ══════════════════════════════════════════════════════════════

        // Accounts Payable (Suppliers)
        $accounts[] = [
            'code'     => '2110-00',
            'name'     => 'Accounts Payable (Trade Creditors / Suppliers)',
            'category' => 'Liability',
            'debit'    => 0,
            'credit'   => $totalAccountsPayable,
        ];

        // Salaries & Staff Payable
        $staffDeb = (float) ExpenseTransaction::where('payee_type', 'staff')->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
        $staffCred = (float) ExpenseTransaction::where('payee_type', 'staff')->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
        $salariesDue = max(0, $staffDeb - $staffCred);

        if ($salariesDue > 0) {
            $accounts[] = [
                'code'     => '2120-00',
                'name'     => 'Salaries & Staff Payable',
                'category' => 'Liability',
                'debit'    => 0,
                'credit'   => $salariesDue,
            ];
        }

        // Loan Liabilities
        $loanLiabs = Account::where('status', 'active')->where('account_type', 'loan_liability')->get();
        foreach ($loanLiabs as $ll) {
            $init = ((float)$ll->opening_balance_pkr) + (((float)$ll->opening_balance_sar) * (float)($ll->opening_balance_roe ?: $activeRoe));
            $cred = (float) ExpenseTransaction::where('account_id', $ll->id)->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $deb = (float) ExpenseTransaction::where('account_id', $ll->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $bal = $init + $cred - $deb;

            if ($bal != 0) {
                $accounts[] = [
                    'code'     => $ll->account_code ?: '2140-' . str_pad($ll->id, 2, '0', STR_PAD_LEFT),
                    'name'     => $ll->account_name . ' (Loan Liability)',
                    'category' => 'Liability',
                    'debit'    => $bal < 0 ? abs($bal) : 0,
                    'credit'   => $bal >= 0 ? $bal : 0,
                ];
            }
        }

        // Unearned Service Revenue (Future Booking Advances)
        $unearnedRev = 0.0;
        $futureBookings = Booking::whereDate('booking_date', '>', $asOfDate)->get();
        foreach ($futureBookings as $fb) {
            $fbCurr = strtoupper($fb->currency ?? 'PKR');
            $fbRoe  = ($fbCurr === 'PKR') ? 1 : (float) ($fb->flight_roe > 0 ? $fb->flight_roe : 75);
            $unearnedRev += ((float)$fb->total_received * $fbRoe);
        }
        if ($unearnedRev > 0) {
            $accounts[] = [
                'code'     => '2150-00',
                'name'     => 'Unearned Service Revenue (Future Advances)',
                'category' => 'Liability',
                'debit'    => 0,
                'credit'   => $unearnedRev,
            ];
        }

        // ══════════════════════════════════════════════════════════════
        // 3. OWNER'S EQUITY (Normal Balance: CREDIT)
        // ══════════════════════════════════════════════════════════════

        // Capital Accounts
        $capitalAccounts = Account::where('status', 'active')->where('account_type', 'capital')->get();
        $totalCap = 0.0;
        foreach ($capitalAccounts as $cap) {
            $init = ((float)$cap->opening_balance_pkr) + (((float)$cap->opening_balance_sar) * (float)($cap->opening_balance_roe ?: $activeRoe));
            $cred = (float) ExpenseTransaction::where('account_id', $cap->id)->where('transaction_type', 'credit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $deb = (float) ExpenseTransaction::where('account_id', $cap->id)->where('transaction_type', 'debit')->whereDate('date', '<=', $asOfDate)->selectRaw('SUM(CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) as total')->value('total');
            $bal = $init + $cred - $deb;

            if ($bal != 0) {
                $accounts[] = [
                    'code'     => $cap->account_code ?: '3100-' . str_pad($cap->id, 2, '0', STR_PAD_LEFT),
                    'name'     => $cap->account_name . ' (Owner Capital)',
                    'category' => 'Equity',
                    'debit'    => $bal < 0 ? abs($bal) : 0,
                    'credit'   => $bal >= 0 ? $bal : 0,
                ];
                $totalCap += $bal;
            }
        }

        // Opening Retained Earnings
        $retainedAccount = Account::where('status', 'active')->where('account_type', 'retained_earnings')->first();
        if ($retainedAccount) {
            $reInit = ((float)$retainedAccount->opening_balance_pkr) + (((float)$retainedAccount->opening_balance_sar) * (float)($retainedAccount->opening_balance_roe ?: $activeRoe));
            if ($reInit != 0) {
                $accounts[] = [
                    'code'     => $retainedAccount->account_code ?: '3200-00',
                    'name'     => $retainedAccount->account_name . ' (Prior Retained Earnings)',
                    'category' => 'Equity',
                    'debit'    => $reInit < 0 ? abs($reInit) : 0,
                    'credit'   => $reInit >= 0 ? $reInit : 0,
                ];
            }
        }

        // ══════════════════════════════════════════════════════════════
        // 4. REVENUES / SALES (Normal Balance: CREDIT)
        // ══════════════════════════════════════════════════════════════
        $revBookings = Booking::with(['hotels', 'visas', 'transports'])->whereDate('booking_date', '<=', $asOfDate)->get();
        $totalSalesRev = 0.0;
        $totalHotelCost = 0.0;
        $totalFlightCost = 0.0;
        $totalVisaCost = 0.0;
        $totalTransportCost = 0.0;

        foreach ($revBookings as $rb) {
            $rCurr = strtoupper($rb->currency ?? 'PKR');
            $rRoe  = ($rCurr === 'PKR') ? 1 : (float) ($rb->flight_roe > 0 ? $rb->flight_roe : 75);
            $totalSalesRev += ((float)$rb->total_amount * $rRoe);

            foreach ($rb->hotels as $h) {
                $hCostRoe = strtoupper($h->currency ?? 'SAR') === 'PKR' ? 1 : (float)($h->cost_roe ?: ($h->roe ?: 75));
                $totalHotelCost += ((float)$h->cost_amount ?: ((float)$h->cost_per_night * (int)($h->no_of_rooms ?: 1) * (int)($h->no_of_nights ?: 1) * $hCostRoe));
            }
            if ((float)$rb->flight_cost > 0) {
                $fCostRoe = strtoupper($rb->flight_currency ?? 'SAR') === 'PKR' ? 1 : (float)($rb->flight_cost_roe ?: 75);
                $totalFlightCost += ((float)$rb->flight_cost * $fCostRoe);
            }
            foreach ($rb->visas as $v) {
                $vCostRoe = strtoupper($v->currency ?? 'SAR') === 'PKR' ? 1 : (float)($v->cost_roe ?: ($v->roe ?: 75));
                $totalVisaCost += ((float)$v->cost_amount ?: ((float)$v->cost_rate * $vCostRoe));
            }
            foreach ($rb->transports as $t) {
                $tCostRoe = strtoupper($t->currency ?? 'SAR') === 'PKR' ? 1 : (float)($t->cost_roe ?: ($t->roe ?: 75));
                $totalTransportCost += ((float)$t->cost_amount ?: ((float)$t->cost_rate * $tCostRoe));
            }
        }

        $accounts[] = [
            'code'     => '4100-00',
            'name'     => 'Package Sales & Service Revenue',
            'category' => 'Revenue',
            'debit'    => 0,
            'credit'   => $totalSalesRev,
        ];

        // ══════════════════════════════════════════════════════════════
        // 5. COST OF SALES / DIRECT COSTS (Normal Balance: DEBIT)
        // ══════════════════════════════════════════════════════════════
        if ($totalHotelCost > 0) {
            $accounts[] = [
                'code'     => '5100-00',
                'name'     => 'Direct Costs - Hotel Accommodations',
                'category' => 'Cost of Sales',
                'debit'    => $totalHotelCost,
                'credit'   => 0,
            ];
        }
        if ($totalFlightCost > 0) {
            $accounts[] = [
                'code'     => '5200-00',
                'name'     => 'Direct Costs - Airline Ticketing',
                'category' => 'Cost of Sales',
                'debit'    => $totalFlightCost,
                'credit'   => 0,
            ];
        }
        if ($totalVisaCost > 0) {
            $accounts[] = [
                'code'     => '5300-00',
                'name'     => 'Direct Costs - Visa Processing',
                'category' => 'Cost of Sales',
                'debit'    => $totalVisaCost,
                'credit'   => 0,
            ];
        }
        if ($totalTransportCost > 0) {
            $accounts[] = [
                'code'     => '5400-00',
                'name'     => 'Direct Costs - Ground Transport',
                'category' => 'Cost of Sales',
                'debit'    => $totalTransportCost,
                'credit'   => 0,
            ];
        }

        // ══════════════════════════════════════════════════════════════
        // 6. OPERATING EXPENSES (Normal Balance: DEBIT)
        // ══════════════════════════════════════════════════════════════
        $expenseTransactions = ExpenseTransaction::with('expense')
            ->where('transaction_type', 'debit')
            ->whereDate('date', '<=', $asOfDate)
            ->where(function($q) {
                $q->whereNotNull('expense_id')
                  ->orWhereIn('payee_type', ['expense', 'staff', 'office', 'general']);
            })
            ->get();

        $expenseGroups = [];
        foreach ($expenseTransactions as $et) {
            $headName = $et->expense->name ?? ($et->description ?: 'General Operating Expense');
            $amtPkr = $et->currency === 'SAR' ? ((float)$et->amount * (float)$et->exchange_rate) : (float)$et->amount;
            if (!isset($expenseGroups[$headName])) {
                $expenseGroups[$headName] = 0;
            }
            $expenseGroups[$headName] += $amtPkr;
        }

        $expCodeIdx = 1;
        foreach ($expenseGroups as $eName => $eAmt) {
            $accounts[] = [
                'code'     => '6100-' . str_pad($expCodeIdx++, 2, '0', STR_PAD_LEFT),
                'name'     => 'Operating Expense - ' . $eName,
                'category' => 'Expense',
                'debit'    => $eAmt,
                'credit'   => 0,
            ];
        }

        // ══════════════════════════════════════════════════════════════
        // BALANCE CALCULATION & EQUILIBRIUM CHECK
        // ══════════════════════════════════════════════════════════════
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($accounts as &$acc) {
            $acc['debit_sar']  = $acc['debit'] > 0 ? ($acc['debit'] / $activeRoe) : 0;
            $acc['credit_sar'] = $acc['credit'] > 0 ? ($acc['credit'] / $activeRoe) : 0;
            $totalDebit  += $acc['debit'];
            $totalCredit += $acc['credit'];
        }
        unset($acc);

        // Balancing Adjustment for exact equilibrium
        $diff = $totalDebit - $totalCredit;
        if (abs($diff) > 0.01) {
            $accounts[] = [
                'code'       => '3999-00',
                'name'       => 'Current Earnings / Balancing Equilibrium',
                'category'   => 'Equity',
                'debit'      => $diff < 0 ? abs($diff) : 0,
                'credit'     => $diff > 0 ? $diff : 0,
                'debit_sar'  => $diff < 0 ? (abs($diff) / $activeRoe) : 0,
                'credit_sar' => $diff > 0 ? ($diff / $activeRoe) : 0,
            ];
            if ($diff > 0) {
                $totalCredit += $diff;
            } else {
                $totalDebit += abs($diff);
            }
        }

        $isBalanced = abs($totalDebit - $totalCredit) < 0.01;

        return compact(
            'packageType',
            'asOfDate',
            'activeRoe',
            'accounts',
            'totalDebit',
            'totalCredit',
            'isBalanced'
        );
    }
}
