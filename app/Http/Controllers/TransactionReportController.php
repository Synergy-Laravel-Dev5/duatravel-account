<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseTransaction;
use App\Models\Supplier;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionReportController extends Controller
{
    /**
     * Display the comprehensive All Transactions Report.
     */
    public function index(Request $request)
    {
        $data = $this->gatherTransactionsData($request);

        return view('reports.transaction_report', $data);
    }

    /**
     * Export the filtered transactions to Excel (CSV format with UTF-8 BOM).
     */
    public function exportExcel(Request $request)
    {
        $data = $this->gatherTransactionsData($request);
        $transactions = $data['transactions'];

        $filename = 'Transactions_Report_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($transactions, $data) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Summary Info
            fputcsv($handle, ['ALL TRANSACTIONS REPORT — STATEMENT']);
            fputcsv($handle, ['Generated At:', date('d M, Y h:i A')]);
            fputcsv($handle, ['Total Inflow (Receipts / Cr):', 'PKR ' . number_format($data['totalInflow'], 2)]);
            fputcsv($handle, ['Total Outflow (Payments / Dr):', 'PKR ' . number_format($data['totalOutflow'], 2)]);
            fputcsv($handle, ['Net Balance / Cash Flow:', 'PKR ' . number_format($data['netCashFlow'], 2)]);
            fputcsv($handle, ['Total Records:', count($transactions)]);
            fputcsv($handle, []);

            // Column Headers
            fputcsv($handle, [
                '#',
                'Date',
                'Voucher / Ref #',
                'Flow Type',
                'Category',
                'Party / Payee / Client',
                'Payment Method',
                'Inflow (Cr) PKR',
                'Outflow (Dr) PKR',
                'Status',
                'Description / Narration',
            ]);

            $i = 1;
            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $i++,
                    $t['date'],
                    $t['ref_no'],
                    strtoupper($t['flow']),
                    $t['category_label'],
                    $t['party_name'],
                    $t['payment_method'],
                    $t['inflow'] > 0 ? number_format($t['inflow'], 2, '.', '') : '0.00',
                    $t['outflow'] > 0 ? number_format($t['outflow'], 2, '.', '') : '0.00',
                    ucfirst($t['status']),
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
                '',
                number_format($data['totalInflow'], 2, '.', ''),
                number_format($data['totalOutflow'], 2, '.', ''),
                '',
                'Net Cash: ' . number_format($data['netCashFlow'], 2, '.', ''),
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Shared logic to gather and process transactions with filters.
     */
    private function gatherTransactionsData(Request $request): array
    {
        $activeDashboardPkg = session('dashboard_package', 'hajj');
        $packageType = $request->has('package_type') 
            ? $request->get('package_type') 
            : (in_array($activeDashboardPkg, ['hajj', 'umrah']) ? $activeDashboardPkg : 'hajj');

        $flow          = $request->get('flow', 'all'); // 'all', 'inflow', 'outflow'
        $category      = $request->get('category', 'all'); // 'all', 'booking_payment', 'booking_advance', 'supplier_payment', 'expense', 'staff_salary', 'client_receipt'
        $paymentMethod = $request->get('payment_method', 'all'); // 'all', 'cash', 'bank', 'bank_transfer', 'online', 'cheque'
        $clientId      = $request->get('client_id');
        $dateFrom      = $request->get('date_from');
        $dateTo        = $request->get('date_to');
        $quickDate     = $request->get('quick_date');
        $search        = trim((string) $request->get('search'));

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

        // 1. Gather Client Booking Payments from `transactions` table (INFLOWS)
        if ($flow === 'all' || $flow === 'inflow') {
            if ($category === 'all' || $category === 'booking_payment') {
                $txQ = Transaction::with(['client', 'company', 'booking'])->where('status', 'confirmed');

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

                if ($paymentMethod !== 'all' && !empty($paymentMethod)) {
                    $txQ->where('payment_type', $paymentMethod);
                }

                $txList = $txQ->get();

                foreach ($txList as $tx) {
                    $d = $tx->payment_date ? Carbon::parse($tx->payment_date)->format('Y-m-d') : Carbon::parse($tx->created_at)->format('Y-m-d');
                    $ts = $tx->payment_date ? Carbon::parse($tx->payment_date)->startOfDay()->timestamp : Carbon::parse($tx->created_at)->timestamp;

                    $clientName = $tx->client ? $tx->client->name : ($tx->company ? $tx->company->name : 'Client');
                    $desc = 'Booking Payment (' . ($tx->booking ? '#' . $tx->booking->id : 'General') . ')' . ($tx->notes ? ' — ' . $tx->notes : '');

                    $allRecords[] = [
                        'source'         => 'transaction',
                        'id'             => $tx->id,
                        'date'           => $d,
                        'timestamp'      => $ts,
                        'flow'           => 'inflow',
                        'flow_badge'     => 'bg-soft-success text-success border border-success border-opacity-25',
                        'category'       => 'booking_payment',
                        'category_label' => 'Client Booking Receipt',
                        'voucher_no'     => 'TXN-' . $tx->id,
                        'ref_no'         => $tx->reference_number ?: ('#TXN-' . $tx->id),
                        'party_name'     => $clientName,
                        'party_type'     => 'Client',
                        'party_link'     => $tx->client_id ? route('client.edit', $tx->client_id) : null,
                        'booking_id'     => $tx->booking_id,
                        'payment_method' => ucwords(str_replace('_', ' ', $tx->payment_type)),
                        'inflow_pkr'     => (float) $tx->amount,
                        'outflow_pkr'    => 0.00,
                        'inflow_sar'     => 0.00,
                        'outflow_sar'    => 0.00,
                        'inflow'         => (float) $tx->amount,
                        'outflow'        => 0.00,
                        'currency'       => 'PKR',
                        'raw_amount'     => (float) $tx->amount,
                        'exchange_rate'  => 1,
                        'status'         => $tx->status,
                        'description'    => $desc,
                        'proof'          => $tx->proof_of_payment ? asset('assets/images/transaction_proofs/' . $tx->proof_of_payment) : null,
                    ];
                }
            }

            // 2. Gather Booking Advances from `bookings` table (INFLOWS)
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
                    $bkQ->whereDate('booking_date', '>=', $dateFrom);
                }

                if (!empty($dateTo)) {
                    $bkQ->whereDate('booking_date', '<=', $dateTo);
                }

                $bkList = $bkQ->get();

                foreach ($bkList as $bk) {
                    $d = $bk->booking_date ? Carbon::parse($bk->booking_date)->format('Y-m-d') : Carbon::parse($bk->created_at)->format('Y-m-d');
                    $ts = $bk->booking_date ? Carbon::parse($bk->booking_date)->startOfDay()->timestamp + 1 : Carbon::parse($bk->created_at)->timestamp;

                    $clientName = $bk->client ? $bk->client->name : ($bk->company ? $bk->company->name : 'Client');
                    $desc = 'Initial Advance Received for Booking #' . $bk->id . ' — ' . ($bk->package_name ?: 'Package');

                    $allRecords[] = [
                        'source'         => 'booking_advance',
                        'id'             => $bk->id,
                        'date'           => $d,
                        'timestamp'      => $ts,
                        'flow'           => 'inflow',
                        'flow_badge'     => 'bg-soft-success text-success border border-success border-opacity-25',
                        'category'       => 'booking_advance',
                        'category_label' => 'Booking Advance',
                        'voucher_no'     => 'ADV-' . $bk->id,
                        'ref_no'         => $bk->voucher_number ?: ('#ADV-' . $bk->id),
                        'party_name'     => $clientName,
                        'party_type'     => 'Client',
                        'party_link'     => $bk->client_id ? route('client.edit', $bk->client_id) : null,
                        'booking_id'     => $bk->id,
                        'payment_method' => 'Advance / Cash',
                        'inflow_pkr'     => (float) $bk->total_received,
                        'outflow_pkr'    => 0.00,
                        'inflow_sar'     => 0.00,
                        'outflow_sar'    => 0.00,
                        'inflow'         => (float) $bk->total_received,
                        'outflow'        => 0.00,
                        'currency'       => 'PKR',
                        'raw_amount'     => (float) $bk->total_received,
                        'exchange_rate'  => 1,
                        'status'         => 'confirmed',
                        'description'    => $desc,
                        'proof'          => null,
                    ];
                }
            }
        }

        // 3. Gather ExpenseTransactions (Can be OUTFLOW or INFLOW depending on payee_type)
        $etQ = ExpenseTransaction::with(['bank', 'supplier', 'client', 'staff', 'expense'])
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

        if ($paymentMethod !== 'all' && !empty($paymentMethod)) {
            $etQ->where('payment_type', $paymentMethod);
        }

        $etList = $etQ->get();

        foreach ($etList as $et) {
            $d = Carbon::parse($et->date)->format('Y-m-d');
            $ts = Carbon::parse($et->date)->startOfDay()->timestamp + 2;
            $rawAmt = (float) $et->amount;
            $rate = (float) ($et->exchange_rate > 0 ? $et->exchange_rate : 1);
            $cur = strtoupper($et->currency ?: 'PKR');
            $amtPkr = ($cur === 'PKR') ? $rawAmt : round($rawAmt * $rate, 2);

            // Classify by payee_type & transaction_type
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
                $catLabel = 'Supplier Payment';
                $partyName = $et->supplier ? $et->supplier->name : 'Supplier';
                $partyType = 'Supplier';
                $partyLink = $et->supplier_id ? route('supplier.ledger.show', $et->supplier_id) : null;
            } elseif ($pType === 'client') {
                $catKey = $isDebit ? 'client_debit' : 'client_receipt';
                $catLabel = $isDebit ? 'Client Debit / Refund' : 'Client Receipt';
                $partyName = $et->client ? $et->client->name : 'Client';
                $partyType = 'Client';
                $partyLink = $et->client_id ? route('client.edit', $et->client_id) : null;
            } elseif ($pType === 'staff') {
                $catKey = 'staff_salary';
                $catLabel = 'Staff Salary / Advance';
                $partyName = $et->staff ? ($et->staff->first_name . ' ' . $et->staff->last_name) : 'Staff';
                $partyType = 'Staff';
            } elseif ($pType === 'expense') {
                $catKey = 'expense';
                $catLabel = 'Office Expense (' . ($et->expense ? $et->expense->name : 'Expense') . ')';
                $partyName = $et->expense ? $et->expense->name : 'Expense';
                $partyType = 'Expense Head';
            } elseif ($pType === 'self') {
                $catKey = 'self_transfer';
                $catLabel = 'Internal / Bank Transfer';
                $partyName = $et->bank ? $et->bank->name : 'Bank';
                $partyType = 'Bank';
            } elseif ($pType === 'jv') {
                $catKey = 'jv_voucher';
                $catLabel = 'Journal Voucher (JV)';
                if ($et->client && $et->supplier) {
                    $partyName = $et->client->name . ' ⇄ ' . $et->supplier->name;
                } elseif ($et->client) {
                    $sibSup = !empty($et->reference_no) ? ExpenseTransaction::where('payee_type', 'jv')->where('reference_no', $et->reference_no)->whereNotNull('supplier_id')->with('supplier')->first() : null;
                    $partyName = ($sibSup && $sibSup->supplier) ? ($et->client->name . ' ⇄ ' . $sibSup->supplier->name) : $et->client->name;
                } elseif ($et->supplier) {
                    $sibCl = !empty($et->reference_no) ? ExpenseTransaction::where('payee_type', 'jv')->where('reference_no', $et->reference_no)->whereNotNull('client_id')->with('client')->first() : null;
                    $partyName = ($sibCl && $sibCl->client) ? ($sibCl->client->name . ' ⇄ ' . $et->supplier->name) : $et->supplier->name;
                } else {
                    $partyName = 'JV Adjustment';
                }
                $partyType = 'JV Voucher';
                $partyLink = $et->client_id ? route('client.edit', $et->client_id) : ($et->supplier_id ? route('supplier.ledger.show', $et->supplier_id) : null);
            }

            // Filter by category
            if ($category !== 'all' && $category !== $catKey) {
                continue;
            }

            $desc = $et->description ?: ($catLabel . ' via ' . ucfirst($et->payment_type));
            if ($cur !== 'PKR') {
                $desc .= ' [' . $cur . ' ' . number_format($rawAmt, 2) . ' @ ROE ' . number_format($rate, 2) . ']';
            }

            $sarAmt = ($cur === 'SAR') ? $rawAmt : 0.00;
            $inflowPkr  = $isClientInflow ? $amtPkr : 0.00;
            $outflowPkr = !$isClientInflow ? $amtPkr : 0.00;
            $inflowSar  = $isClientInflow ? $sarAmt : 0.00;
            $outflowSar = !$isClientInflow ? $sarAmt : 0.00;

            $allRecords[] = [
                'source'         => 'expense_transaction',
                'id'             => $et->id,
                'date'           => $d,
                'timestamp'      => $ts,
                'flow'           => $currentFlow,
                'flow_badge'     => $isClientInflow ? 'bg-soft-success text-success border border-success border-opacity-25' : 'bg-soft-danger text-danger border border-danger border-opacity-25',
                'category'       => $catKey,
                'category_label' => $catLabel,
                'voucher_no'     => 'ETXN-' . $et->id,
                'ref_no'         => $et->reference_no ?: ('#ETXN-' . $et->id),
                'party_name'     => $partyName,
                'party_type'     => $partyType,
                'party_link'     => $partyLink,
                'booking_id'     => null,
                'payment_method' => ucwords(str_replace('_', ' ', $et->payment_type)),
                'currency'       => $cur,
                'raw_amount'     => $rawAmt,
                'exchange_rate'  => $rate,
                'inflow_pkr'     => $inflowPkr,
                'outflow_pkr'    => $outflowPkr,
                'inflow_sar'     => $inflowSar,
                'outflow_sar'    => $outflowSar,
                'inflow'         => $inflowPkr,
                'outflow'        => $outflowPkr,
                'status'         => 'confirmed',
                'description'    => $desc,
                'proof'          => $et->proof ? asset('assets/images/expense_proofs/' . $et->proof) : null,
            ];
        }

        // Apply Keyword Search if present
        if (!empty($search)) {
            $sLower = strtolower($search);
            $allRecords = array_filter($allRecords, function ($item) use ($sLower) {
                return (
                    str_contains(strtolower($item['voucher_no']), $sLower) ||
                    str_contains(strtolower($item['ref_no']), $sLower) ||
                    str_contains(strtolower($item['party_name']), $sLower) ||
                    str_contains(strtolower($item['category_label']), $sLower) ||
                    str_contains(strtolower($item['payment_method']), $sLower) ||
                    str_contains(strtolower($item['description']), $sLower) ||
                    str_contains((string) $item['inflow'], $sLower) ||
                    str_contains((string) $item['outflow'], $sLower)
                );
            });
        }

        // Sort chronologically descending (latest first for report browsing)
        usort($allRecords, function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return $b['timestamp'] <=> $a['timestamp'];
            }
            return strcmp($b['date'], $a['date']);
        });

        // Compute KPI Totals
        $totalInflowPkr  = 0;
        $totalOutflowPkr = 0;
        $totalInflowSar  = 0;
        $totalOutflowSar = 0;
        $inflowCount     = 0;
        $outflowCount    = 0;

        foreach ($allRecords as $r) {
            $totalInflowPkr  += $r['inflow_pkr'];
            $totalOutflowPkr += $r['outflow_pkr'];
            $totalInflowSar  += $r['inflow_sar'];
            $totalOutflowSar += $r['outflow_sar'];
            if ($r['flow'] === 'inflow') $inflowCount++;
            if ($r['flow'] === 'outflow') $outflowCount++;
        }

        $netCashFlowPkr = $totalInflowPkr - $totalOutflowPkr;
        $netCashFlowSar = $totalInflowSar - $totalOutflowSar;
        $totalInflow    = $totalInflowPkr;
        $totalOutflow   = $totalOutflowPkr;
        $netCashFlow    = $netCashFlowPkr;

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
            'packageType'      => $packageType,
            'transactions'     => $allRecords,
            'totalInflowPkr'   => $totalInflowPkr,
            'totalOutflowPkr'  => $totalOutflowPkr,
            'netCashFlowPkr'   => $netCashFlowPkr,
            'totalInflowSar'   => $totalInflowSar,
            'totalOutflowSar'  => $totalOutflowSar,
            'netCashFlowSar'   => $netCashFlowSar,
            'totalInflow'      => $totalInflowPkr,
            'totalOutflow'     => $totalOutflowPkr,
            'netCashFlow'      => $netCashFlowPkr,
            'inflowCount'      => $inflowCount,
            'outflowCount'     => $outflowCount,
            'totalCount'       => count($allRecords),
            'flow'         => $flow,
            'category'     => $category,
            'paymentMethod'=> $paymentMethod,
            'clientId'     => $clientId,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'quickDate'    => $quickDate,
            'search'       => $search,
            'clients'      => $clients,
        ];
    }
}
