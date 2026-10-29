<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ExpenseTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $reportDate = $request->input('date', Carbon::today()->toDateString());
        $dateObj = Carbon::parse($reportDate);

        // 1. Gather Daily Bookings / Sales
        $bookings = Booking::with(['client', 'company', 'package', 'hotels', 'visas', 'transports'])
            ->whereDate('created_at', $reportDate)
            ->orWhereDate('booking_date', $reportDate)
            ->latest('created_at')
            ->get();

        $totalSalesPkr = 0;
        $totalSalesSar = 0;
        $totalPaxToday = 0;

        foreach ($bookings as $b) {
            $amt = (float) $b->total_amount;
            $roe = (float) ($b->flight_sale_roe > 1 ? $b->flight_sale_roe : ($b->flight_roe > 1 ? $b->flight_roe : 75));
            $cur = strtoupper($b->currency ?: 'PKR');

            if ($cur === 'SAR') {
                $totalSalesSar += $amt;
                $totalSalesPkr += round($amt * $roe, 2);
            } else {
                $totalSalesPkr += $amt;
                $totalSalesSar += ($roe > 1) ? round($amt / $roe, 2) : 0;
            }
            $totalPaxToday += (int) ($b->no_of_pax ?: 1);
        }

        // 2. Gather All Transactions for the Day with Real Timestamps
        $transactionsList = [];

        // Receipts from transactions table
        $rawTxns = Transaction::with(['client', 'booking'])
            ->where(function ($q) use ($reportDate) {
                $q->whereDate('payment_date', $reportDate)
                  ->orWhereDate('created_at', $reportDate);
            })
            ->get();

        foreach ($rawTxns as $t) {
            $createdTime = $t->created_at ? $t->created_at->format('h:i A') : '—';
            $roe = (float) ($t->exchange_rate > 1 ? $t->exchange_rate : 75);
            $amt = (float) $t->amount;
            $sarAmt = (float) ($t->sar_amount > 0 ? $t->sar_amount : (($roe > 1) ? round($amt / $roe, 2) : 0));

            $transactionsList[] = [
                'time'           => $createdTime,
                'raw_timestamp'  => $t->created_at ? $t->created_at->timestamp : 0,
                'voucher_no'     => 'TXN-' . $t->id,
                'ref_no'         => $t->reference_number ?: ('#TXN-' . $t->id),
                'flow'           => 'inflow',
                'type_label'     => 'Client Receipt',
                'badge_class'    => 'bg-soft-success text-success',
                'party_name'     => $t->client->name ?? '—',
                'payment_method' => ucwords(str_replace('_', ' ', $t->payment_type ?? 'Cash')),
                'amount_pkr'     => $amt,
                'amount_sar'     => $sarAmt,
                'roe'            => $roe,
                'status'         => $t->status,
                'description'    => $t->notes ?: ('Payment for Booking #' . ($t->booking_id ?? '—')),
            ];
        }

        // ExpenseTransactions (Receipts, Payments, Staff, JV)
        $rawEts = ExpenseTransaction::with(['client', 'supplier', 'staff', 'expense', 'bank', 'account'])
            ->where(function ($q) use ($reportDate) {
                $q->whereDate('date', $reportDate)
                  ->orWhereDate('created_at', $reportDate);
            })
            ->get();

        foreach ($rawEts as $et) {
            $createdTime = $et->created_at ? $et->created_at->format('h:i A') : '—';
            $cur = strtoupper($et->currency ?: 'PKR');
            $roe = (float) ($et->exchange_rate > 0 ? $et->exchange_rate : 75);
            $amt = (float) $et->amount;

            if ($cur === 'SAR') {
                $sarAmt = $amt;
                $pkrAmt = round($amt * $roe, 2);
            } else {
                $pkrAmt = $amt;
                $sarAmt = ($roe > 1) ? round($amt / $roe, 2) : 0;
            }

            $isDebit = ($et->transaction_type === 'debit');
            $flow = $isDebit ? 'outflow' : 'inflow';

            // Party Name
            $partyName = '—';
            if ($et->client) $partyName = 'Client: ' . $et->client->name;
            elseif ($et->supplier) $partyName = 'Supplier: ' . $et->supplier->name;
            elseif ($et->staff) $partyName = 'Staff: ' . $et->staff->first_name . ' ' . $et->staff->last_name;
            elseif ($et->expense) $partyName = 'Expense: ' . $et->expense->name;
            elseif ($et->account) $partyName = 'Account: ' . $et->account->account_name;

            // Method
            $method = ucwords(str_replace('_', ' ', $et->payment_type ?? 'Cash'));
            if ($et->bank) {
                $method = 'Bank: ' . $et->bank->name;
            } elseif ($et->account) {
                $method = 'Cash: ' . $et->account->account_name;
            }

            $typeLabel = match($et->payee_type) {
                'supplier' => 'Supplier Payment',
                'client'   => $isDebit ? 'Client Debit' : 'Client Receipt',
                'staff'    => 'Staff Salary / Disbursement',
                'expense'  => 'Office Expense',
                'jv'       => 'JV Voucher',
                'loan'     => 'Loan Disbursement / Recovery',
                default    => 'General Transaction'
            };

            $badgeClass = match($flow) {
                'inflow'  => 'bg-soft-success text-success',
                'outflow' => 'bg-soft-danger text-danger',
            };

            $transactionsList[] = [
                'time'           => $createdTime,
                'raw_timestamp'  => $et->created_at ? $et->created_at->timestamp : 0,
                'voucher_no'     => $et->reference_no ?: ('ET-' . $et->id),
                'ref_no'         => $et->reference_no ?: ('#ET-' . $et->id),
                'flow'           => $flow,
                'type_label'     => $typeLabel,
                'badge_class'    => $badgeClass,
                'party_name'     => $partyName,
                'payment_method' => $method,
                'amount_pkr'     => $pkrAmt,
                'amount_sar'     => $sarAmt,
                'roe'            => $roe,
                'status'         => 'confirmed',
                'description'    => $et->description ?: '—',
            ];
        }

        // Sort all transactions chronologically by time
        usort($transactionsList, function ($a, $b) {
            return $b['raw_timestamp'] <=> $a['raw_timestamp'];
        });

        // Totals
        $totalInflowPkr = collect($transactionsList)->where('flow', 'inflow')->sum('amount_pkr');
        $totalInflowSar = collect($transactionsList)->where('flow', 'inflow')->sum('amount_sar');
        $totalOutflowPkr = collect($transactionsList)->where('flow', 'outflow')->sum('amount_pkr');
        $totalOutflowSar = collect($transactionsList)->where('flow', 'outflow')->sum('amount_sar');
        $netCashFlowPkr = $totalInflowPkr - $totalOutflowPkr;

        return view('reports.daily', compact(
            'reportDate',
            'dateObj',
            'bookings',
            'transactionsList',
            'totalSalesPkr',
            'totalSalesSar',
            'totalPaxToday',
            'totalInflowPkr',
            'totalInflowSar',
            'totalOutflowPkr',
            'totalOutflowSar',
            'netCashFlowPkr'
        ));
    }
}
