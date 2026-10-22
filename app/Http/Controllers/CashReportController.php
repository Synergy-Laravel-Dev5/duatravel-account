<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseTransaction;
use App\Models\Supplier;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashReportController extends Controller
{
    /**
     * Display the comprehensive Cash Report / Cash Book with Dual Currency (PKR & SAR).
     */
    public function index(Request $request)
    {
        $data = $this->gatherCashData($request);
        return view('reports.cash_report', $data);
    }

    /**
     * Export Cash Report / Cash Book to Excel (CSV with UTF-8 BOM).
     */
    public function exportExcel(Request $request)
    {
        $data = $this->gatherCashData($request);
        $transactions = $data['transactions'];

        $filename = 'Cash_Book_Report_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($transactions, $data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Report Header
            fputcsv($handle, ['CASH BOOK / CASH STATEMENT REPORT (DUAL CURRENCY: PKR & SAR)']);
            fputcsv($handle, ['Generated At:', date('d M, Y h:i A')]);
            fputcsv($handle, ['Total Cash Inflow PKR:', 'PKR ' . number_format($data['totalCashInPkr'], 2), 'Total Cash Inflow SAR:', 'SAR ' . number_format($data['totalCashInSar'], 2)]);
            fputcsv($handle, ['Total Cash Outflow PKR:', 'PKR ' . number_format($data['totalCashOutPkr'], 2), 'Total Cash Outflow SAR:', 'SAR ' . number_format($data['totalCashOutSar'], 2)]);
            fputcsv($handle, ['Net Cash Balance PKR:', 'PKR ' . number_format($data['netCashBalancePkr'], 2), 'Net Cash Balance SAR:', 'SAR ' . number_format($data['netCashBalanceSar'], 2)]);
            fputcsv($handle, ['Total Cash Transactions:', count($transactions)]);
            fputcsv($handle, []);

            // Column Headers
            fputcsv($handle, [
                '#',
                'Date',
                'Voucher / Ref #',
                'Flow Type',
                'Category',
                'Party / Payee / Client',
                'Cash In (Cr) PKR',
                'Cash Out (Dr) PKR',
                'Cash In (Cr) SAR',
                'Cash Out (Dr) SAR',
                'Running Cash Balance PKR',
                'Running Cash Balance SAR',
                'Description / Narration',
            ]);

            $i = 1;
            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $i++,
                    $t['date'],
                    $t['voucher_no'],
                    strtoupper($t['flow']),
                    $t['category_label'],
                    $t['party_name'],
                    $t['cash_in_pkr'] > 0 ? number_format($t['cash_in_pkr'], 2, '.', '') : '0.00',
                    $t['cash_out_pkr'] > 0 ? number_format($t['cash_out_pkr'], 2, '.', '') : '0.00',
                    $t['cash_in_sar'] > 0 ? number_format($t['cash_in_sar'], 2, '.', '') : '0.00',
                    $t['cash_out_sar'] > 0 ? number_format($t['cash_out_sar'], 2, '.', '') : '0.00',
                    number_format($t['running_balance_pkr'], 2, '.', ''),
                    number_format($t['running_balance_sar'], 2, '.', ''),
                    $t['description'],
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTALS',
                '',
                '',
                '',
                '',
                '',
                number_format($data['totalCashInPkr'], 2, '.', ''),
                number_format($data['totalCashOutPkr'], 2, '.', ''),
                number_format($data['totalCashInSar'], 2, '.', ''),
                number_format($data['totalCashOutSar'], 2, '.', ''),
                'Net PKR: ' . number_format($data['netCashBalancePkr'], 2, '.', ''),
                'Net SAR: ' . number_format($data['netCashBalanceSar'], 2, '.', ''),
                '',
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Cash Report / Cash Book to PDF.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->gatherCashData($request);

        $pdf = Pdf::loadView('reports.cash_report_pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('cash_book_statement_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Shared logic to gather and process all Cash Inflows and Outflows for both PKR and SAR.
     */
    private function gatherCashData(Request $request): array
    {
        $activeDashboardPkg = session('dashboard_package', 'hajj');
        $packageType = $request->has('package_type') 
            ? $request->get('package_type') 
            : (in_array($activeDashboardPkg, ['hajj', 'umrah']) ? $activeDashboardPkg : 'hajj');

        $flow      = $request->get('flow', 'all'); // 'all', 'inflow', 'outflow'
        $category  = $request->get('category', 'all');
        $clientId  = $request->get('client_id');
        $dateFrom  = $request->get('date_from');
        $dateTo    = $request->get('date_to');
        $quickDate = $request->get('quick_date');
        $search    = trim((string) $request->get('search'));

        // Handle Quick Date Presets
        if (!empty($quickDate) && $quickDate !== 'custom') {
            $now = Carbon::now();
            switch ($quickDate) {
                case 'today':
                    $dateFrom = $now->format('Y-m-d');
                    $dateTo   = $now->format('Y-m-d');
                    break;
                case 'this_week':
                    $dateFrom = $now->startOfWeek()->format('Y-m-d');
                    $dateTo   = $now->endOfWeek()->format('Y-m-d');
                    break;
                case 'this_month':
                    $dateFrom = $now->startOfMonth()->format('Y-m-d');
                    $dateTo   = $now->endOfMonth()->format('Y-m-d');
                    break;
                case 'last_month':
                    $lastMonth = Carbon::now()->subMonth();
                    $dateFrom  = $lastMonth->startOfMonth()->format('Y-m-d');
                    $dateTo    = $lastMonth->endOfMonth()->format('Y-m-d');
                    break;
                case 'this_year':
                    $dateFrom = $now->startOfYear()->format('Y-m-d');
                    $dateTo   = $now->endOfYear()->format('Y-m-d');
                    break;
                case 'all':
                    $dateFrom = null;
                    $dateTo   = null;
                    break;
            }
        }

        $allRecords = [];

        // 1. Gather Cash Payments from `transactions` table (INFLOWS - PKR)
        if ($flow === 'all' || $flow === 'inflow') {
            if ($category === 'all' || $category === 'booking_payment') {
                $txQ = Transaction::with(['client', 'company', 'booking'])
                    ->where('payment_type', 'cash')
                    ->where('status', 'confirmed');

                if ($packageType === 'hajj') {
                    $txQ->where(function ($q) {
                        $q->whereHas('booking', function ($bq) {
                            $bq->where(function ($sq) {
                                $sq->where('package_type', 'hajj')->orWhereNull('package_type');
                            })->where('package_type', '!=', 'umrah');
                        })->orWhereNull('booking_id');
                    });
                } elseif ($packageType === 'umrah') {
                    $txQ->whereHas('booking', function ($bq) {
                        $bq->where('package_type', 'umrah');
                    });
                }

                if (!empty($clientId)) {
                    $txQ->where('client_id', $clientId);
                }
                if (!empty($dateFrom)) {
                    $txQ->whereDate('payment_date', '>=', $dateFrom);
                }
                if (!empty($dateTo)) {
                    $txQ->whereDate('payment_date', '<=', $dateTo);
                }

                $txList = $txQ->get();
                foreach ($txList as $tx) {
                    $d = $tx->payment_date ? Carbon::parse($tx->payment_date)->format('Y-m-d') : Carbon::parse($tx->created_at)->format('Y-m-d');
                    $ts = $tx->payment_date ? Carbon::parse($tx->payment_date)->startOfDay()->timestamp : Carbon::parse($tx->created_at)->timestamp;

                    $clientName = $tx->client ? $tx->client->name : ($tx->company ? $tx->company->name : 'Client');
                    $desc = 'Cash Payment for Booking (' . ($tx->booking ? '#' . $tx->booking->id : 'General') . ')' . ($tx->notes ? ' — ' . $tx->notes : '');

                    $allRecords[] = [
                        'source'         => 'transaction',
                        'id'             => $tx->id,
                        'date'           => $d,
                        'timestamp'      => $ts,
                        'flow'           => 'inflow',
                        'flow_badge'     => 'bg-soft-success text-success border border-success border-opacity-25',
                        'category'       => 'booking_payment',
                        'category_label' => 'Client Booking Receipt (Cash)',
                        'voucher_no'     => $tx->reference_number ?: ('#TXN-' . $tx->id),
                        'party_name'     => $clientName,
                        'party_type'     => 'Client',
                        'party_link'     => $tx->client_id ? route('client.edit', $tx->client_id) : null,
                        'cash_in_pkr'    => (float) $tx->amount,
                        'cash_out_pkr'   => 0.00,
                        'cash_in_sar'    => 0.00,
                        'cash_out_sar'   => 0.00,
                        'cash_in'        => (float) $tx->amount,
                        'cash_out'       => 0.00,
                        'currency'       => 'PKR',
                        'raw_amount'     => (float) $tx->amount,
                        'exchange_rate'  => 1,
                        'description'    => $desc,
                        'proof'          => $tx->proof_of_payment ? asset('assets/images/transaction_proofs/' . $tx->proof_of_payment) : null,
                    ];
                }
            }

            // 2. Gather Cash Advances from `bookings` table (INFLOWS - PKR)
            if ($category === 'all' || $category === 'booking_advance') {
                $bkQ = Booking::with(['client', 'company'])->where('total_received', '>', 0);

                if ($packageType === 'hajj') {
                    $bkQ->where(function ($q) {
                        $q->where('package_type', 'hajj')->orWhereNull('package_type');
                    })->where('package_type', '!=', 'umrah');
                } elseif ($packageType === 'umrah') {
                    $bkQ->where('package_type', 'umrah');
                }

                if (!empty($clientId)) {
                    $bkQ->where('client_id', $clientId);
                }
                if (!empty($dateFrom)) {
                    $bkQ->whereRaw("DATE(COALESCE(booking_date, created_at)) >= ?", [$dateFrom]);
                }
                if (!empty($dateTo)) {
                    $bkQ->whereRaw("DATE(COALESCE(booking_date, created_at)) <= ?", [$dateTo]);
                }

                $bkList = $bkQ->get();
                foreach ($bkList as $bk) {
                    $effectiveDate = !empty($bk->booking_date) ? $bk->booking_date : ($bk->created_at ? $bk->created_at->toDateString() : date('Y-m-d'));
                    $d = Carbon::parse($effectiveDate)->format('Y-m-d');
                    $ts = Carbon::parse($effectiveDate)->startOfDay()->timestamp + 1;

                    $clientName = $bk->client ? $bk->client->name : ($bk->company ? $bk->company->name : 'Client');
                    $desc = 'Initial Cash Advance Received for Booking #' . $bk->id . ' — ' . ($bk->package_name ?: 'Package');

                    $allRecords[] = [
                        'source'         => 'booking_advance',
                        'id'             => $bk->id,
                        'date'           => $d,
                        'timestamp'      => $ts,
                        'flow'           => 'inflow',
                        'flow_badge'     => 'bg-soft-success text-success border border-success border-opacity-25',
                        'category'       => 'booking_advance',
                        'category_label' => 'Booking Advance (Cash)',
                        'voucher_no'     => $bk->voucher_number ?: ('#ADV-' . $bk->id),
                        'party_name'     => $clientName,
                        'party_type'     => 'Client',
                        'party_link'     => $bk->client_id ? route('client.edit', $bk->client_id) : null,
                        'cash_in_pkr'    => (float) $bk->total_received,
                        'cash_out_pkr'   => 0.00,
                        'cash_in_sar'    => 0.00,
                        'cash_out_sar'   => 0.00,
                        'cash_in'        => (float) $bk->total_received,
                        'cash_out'       => 0.00,
                        'currency'       => 'PKR',
                        'raw_amount'     => (float) $bk->total_received,
                        'exchange_rate'  => 1,
                        'description'    => $desc,
                        'proof'          => null,
                    ];
                }
            }
        }

        // 3. Gather Cash Transactions from `expense_transactions` table (BOTH INFLOW & OUTFLOW, PKR & SAR)
        $etQ = ExpenseTransaction::with(['bank', 'supplier', 'client', 'staff', 'expense'])
            ->where('payment_type', 'cash')
            ->whereNull('deleted_at');

        if ($packageType === 'hajj') {
            $etQ->where(function ($q) {
                $q->where('hajj_umrah', 'hajj')->orWhereNull('hajj_umrah');
            })->where('hajj_umrah', '!=', 'umrah');
        } elseif ($packageType === 'umrah') {
            $etQ->where('hajj_umrah', 'umrah');
        }

        if (!empty($clientId)) {
            $etQ->where('client_id', $clientId);
        }
        if (!empty($dateFrom)) {
            $etQ->whereDate('date', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $etQ->whereDate('date', '<=', $dateTo);
        }

        $etList = $etQ->get();
        foreach ($etList as $et) {
            $d = Carbon::parse($et->date)->format('Y-m-d');
            $ts = Carbon::parse($et->date)->startOfDay()->timestamp + 2;
            $rawAmt = (float) $et->amount;
            $rate = (float) ($et->exchange_rate > 0 ? $et->exchange_rate : 1);
            $cur = strtoupper($et->currency ?: 'PKR');
            $amtPkr = ($cur === 'PKR') ? $rawAmt : round($rawAmt * $rate, 2);
            $amtSar = ($cur === 'SAR') ? $rawAmt : 0.00;

            $pType = $et->payee_type ?: 'expense';
            $isDebit = ($et->transaction_type === 'debit');
            $isClientInflow = ($pType === 'client' && !$isDebit);

            $currentFlow = $isClientInflow ? 'inflow' : 'outflow';

            // Filter by flow
            if ($flow !== 'all' && $flow !== $currentFlow) {
                continue;
            }

            // Category mapping
            $catKey = 'expense';
            $catLabel = 'General Expense';
            $partyName = '—';
            $partyType = 'General';
            $partyLink = null;

            if ($pType === 'supplier') {
                $catKey = 'supplier_payment';
                $catLabel = 'Supplier Cash Payment';
                $partyName = $et->supplier ? $et->supplier->name : 'Supplier';
                $partyType = 'Supplier';
                $partyLink = $et->supplier_id ? route('supplier.ledger.show', $et->supplier_id) : null;
            } elseif ($pType === 'client') {
                $catKey = $isDebit ? 'client_refund' : 'client_receipt';
                $catLabel = $isDebit ? 'Client Cash Refund / Debit' : 'Client Cash Receipt';
                $partyName = $et->client ? $et->client->name : 'Client';
                $partyType = 'Client';
                $partyLink = $et->client_id ? route('client.edit', $et->client_id) : null;
            } elseif ($pType === 'staff') {
                $catKey = 'staff_advance';
                $catLabel = 'Staff Cash Salary / Advance';
                $partyName = $et->staff ? ($et->staff->first_name . ' ' . $et->staff->last_name) : 'Staff';
                $partyType = 'Staff';
            } elseif ($pType === 'expense') {
                $catKey = 'expense';
                $catLabel = 'Office Cash Expense (' . ($et->expense ? $et->expense->name : 'Expense') . ')';
                $partyName = $et->expense ? $et->expense->name : 'Expense Head';
                $partyType = 'Expense';
            }

            // Filter by category
            if ($category !== 'all' && $category !== $catKey) {
                continue;
            }

            $desc = $et->description ?: ($catLabel . ' via Cash');
            if ($cur !== 'PKR') {
                $desc .= ' [' . $cur . ' ' . number_format($rawAmt, 2) . ' @ ROE ' . number_format($rate, 2) . ']';
            }

            $cashInPkr  = $isClientInflow ? $amtPkr : 0.00;
            $cashOutPkr = !$isClientInflow ? $amtPkr : 0.00;
            $cashInSar  = $isClientInflow ? $amtSar : 0.00;
            $cashOutSar = !$isClientInflow ? $amtSar : 0.00;

            $allRecords[] = [
                'source'         => 'expense_transaction',
                'id'             => $et->id,
                'date'           => $d,
                'timestamp'      => $ts,
                'flow'           => $currentFlow,
                'flow_badge'     => $isClientInflow ? 'bg-soft-success text-success border border-success border-opacity-25' : 'bg-soft-danger text-danger border border-danger border-opacity-25',
                'category'       => $catKey,
                'category_label' => $catLabel,
                'voucher_no'     => $et->reference_no ?: ('#ETXN-' . $et->id),
                'party_name'     => $partyName,
                'party_type'     => $partyType,
                'party_link'     => $partyLink,
                'cash_in_pkr'    => $cashInPkr,
                'cash_out_pkr'   => $cashOutPkr,
                'cash_in_sar'    => $cashInSar,
                'cash_out_sar'   => $cashOutSar,
                'cash_in'        => $cashInPkr,
                'cash_out'       => $cashOutPkr,
                'currency'       => $cur,
                'raw_amount'     => $rawAmt,
                'exchange_rate'  => $rate,
                'description'    => $desc,
                'proof'          => $et->proof ? asset('assets/images/expense_proofs/' . $et->proof) : null,
            ];
        }

        // Apply Keyword Search
        if (!empty($search)) {
            $sLower = strtolower($search);
            $allRecords = array_filter($allRecords, function ($item) use ($sLower) {
                return (
                    str_contains(strtolower($item['voucher_no']), $sLower) ||
                    str_contains(strtolower($item['party_name']), $sLower) ||
                    str_contains(strtolower($item['category_label']), $sLower) ||
                    str_contains(strtolower($item['description']), $sLower) ||
                    str_contains((string) $item['cash_in_pkr'], $sLower) ||
                    str_contains((string) $item['cash_out_pkr'], $sLower) ||
                    str_contains((string) $item['cash_in_sar'], $sLower) ||
                    str_contains((string) $item['cash_out_sar'], $sLower)
                );
            });
        }

        // Sort chronologically ascending to compute Running Cash Balances for PKR & SAR
        usort($allRecords, function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return $a['timestamp'] <=> $b['timestamp'];
            }
            return strcmp($a['date'], $b['date']);
        });

        $runningCashPkr = 0;
        $runningCashSar = 0;
        foreach ($allRecords as &$r) {
            $runningCashPkr += ($r['cash_in_pkr'] - $r['cash_out_pkr']);
            $runningCashSar += ($r['cash_in_sar'] - $r['cash_out_sar']);
            $r['running_balance_pkr'] = $runningCashPkr;
            $r['running_balance_sar'] = $runningCashSar;
            $r['running_balance']     = $runningCashPkr;
        }
        unset($r);

        // Compute KPI Totals for both PKR & SAR
        $totalCashInPkr  = 0;
        $totalCashOutPkr = 0;
        $totalCashInSar  = 0;
        $totalCashOutSar = 0;
        $inflowCount     = 0;
        $outflowCount    = 0;

        foreach ($allRecords as $r) {
            $totalCashInPkr  += $r['cash_in_pkr'];
            $totalCashOutPkr += $r['cash_out_pkr'];
            $totalCashInSar  += $r['cash_in_sar'];
            $totalCashOutSar += $r['cash_out_sar'];
            if ($r['cash_in_pkr'] > 0 || $r['cash_in_sar'] > 0) $inflowCount++;
            if ($r['cash_out_pkr'] > 0 || $r['cash_out_sar'] > 0) $outflowCount++;
        }

        $netCashBalancePkr = $totalCashInPkr - $totalCashOutPkr;
        $netCashBalanceSar = $totalCashInSar - $totalCashOutSar;

        // Reverse for display (latest first)
        $displayRecords = array_reverse($allRecords);

        $clientsQ = Client::where('status', 'active');
        if ($packageType === 'hajj') {
            $clientsQ->where(function ($q) {
                $q->where('package_type', 'hajj')->orWhereNull('package_type');
            })->where('package_type', '!=', 'umrah');
        } elseif ($packageType === 'umrah') {
            $clientsQ->where('package_type', 'umrah');
        }
        $clients = $clientsQ->orderBy('name')->get();

        return [
            'packageType'        => $packageType,
            'transactions'       => $displayRecords,
            'totalCashInPkr'     => $totalCashInPkr,
            'totalCashOutPkr'    => $totalCashOutPkr,
            'netCashBalancePkr'  => $netCashBalancePkr,
            'totalCashInSar'     => $totalCashInSar,
            'totalCashOutSar'    => $totalCashOutSar,
            'netCashBalanceSar'  => $netCashBalanceSar,
            'totalCashIn'        => $totalCashInPkr, // backward compatibility
            'totalCashOut'       => $totalCashOutPkr,
            'netCashBalance'     => $netCashBalancePkr,
            'inflowCount'        => $inflowCount,
            'outflowCount'       => $outflowCount,
            'totalCount'         => count($allRecords),
            'flow'               => $flow,
            'category'           => $category,
            'clientId'           => $clientId,
            'dateFrom'           => $dateFrom,
            'dateTo'             => $dateTo,
            'quickDate'          => $quickDate,
            'search'             => $search,
            'clients'            => $clients,
        ];
    }
}
