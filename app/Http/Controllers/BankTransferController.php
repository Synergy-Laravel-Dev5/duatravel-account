<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankTransfer;
use App\Models\Bank;
use App\Models\ExpenseTransaction;
use App\Models\Client;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BankTransferController extends Controller
{
    public function index()
    {
        $transfers = BankTransfer::with(['fromBank', 'toBank'])->latest('transfer_date')->latest('id')->get();
        return view('bank_transfer.index', compact('transfers'));
    }

    public function create()
    {
        $banks = Bank::where('status', 'active')->orderBy('name')->get();
        return view('bank_transfer.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_bank_id' => 'required|exists:banks,id|different:to_bank_id',
            'to_bank_id'   => 'required|exists:banks,id',
            'amount'       => 'required|numeric|min:1',
            'transfer_date'=> 'required|date',
        ]);
        
        $transfer = BankTransfer::create($request->all());

        if (function_exists('logUserActivity')) {
            $fromName = Bank::find($request->from_bank_id)?->name ?? 'Bank';
            $toName = Bank::find($request->to_bank_id)?->name ?? 'Bank';
            logUserActivity('Bank Transfer Created', "Transferred PKR " . number_format($request->amount, 2) . " from {$fromName} to {$toName}", $transfer->id, 'BankTransfer');
        }

        return redirect()->route('bank-transfer.index')->with('success', 'Transfer recorded successfully.');
    }

    public function destroy(BankTransfer $bankTransfer)
    {
        $bankTransfer->delete();
        return redirect()->route('bank-transfer.index')->with('success', 'Transfer deleted successfully.');
    }

    public function reportFilter()
    {
        $banks = Bank::where('status', 'active')->orderBy('name')->get();
        return view('bank_transfer.report_filter', compact('banks'));
    }

    /**
     * Comprehensive Bank Transaction & Movement Report with Dual PKR & SAR Tracking.
     */
    public function report(Request $request)
    {
        $data = $this->gatherBankReportData($request);
        return view('bank_transfer.report', $data);
    }

    /**
     * PDF Export of Bank Transactions & Movement Report.
     */
    public function reportPdf(Request $request)
    {
        $data = $this->gatherBankReportData($request);

        $pdf = Pdf::loadView('bank_transfer.report_pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('bank_statement_report_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Shared logic to gather and process bank transactions and transfers with both PKR & SAR amounts.
     */
    private function gatherBankReportData(Request $request): array
    {
        $fromBankId   = $request->get('from_bank_id');
        $toBankId     = $request->get('to_bank_id');
        $bankId       = $request->get('bank_id');
        $fromDate     = $request->get('from_date');
        $toDate       = $request->get('to_date');
        $minAmount    = $request->get('min_amount');
        $maxAmount    = $request->get('max_amount');
        $search       = trim((string) $request->get('search'));
        $activityType = $request->get('activity_type', 'all'); // 'all', 'deposit', 'withdrawal', 'transfer'

        $allBanks = Bank::where('status', 'active')->orderBy('name')->get();
        $allRecords = [];

        // 0. Gather Bank Opening Balances (INFLOWS / DEPOSITS - PKR & SAR)
        if ($activityType !== 'withdrawal' && $activityType !== 'transfer') {
            $banksQuery = Bank::where('status', 'active');
            if ($bankId) {
                $banksQuery->where('id', $bankId);
            } elseif ($fromBankId) {
                $banksQuery->where('id', $fromBankId);
            } elseif ($toBankId) {
                $banksQuery->where('id', $toBankId);
            }

            $opBanks = $banksQuery->get();

            foreach ($opBanks as $b) {
                $obPkr = (float) ($b->opening_balance_pkr ?? 0);
                $obSar = (float) ($b->opening_balance_sar ?? 0);
                if ($obPkr <= 0 && $obSar <= 0) {
                    continue;
                }

                $effectiveDate = $b->created_at ? $b->created_at->format('Y-m-d') : '2026-01-01';
                $dTs = $b->created_at ? ($b->created_at->startOfDay()->timestamp - 100) : 0;
                $effectiveObPkr = $obPkr;
                $effectiveObSar = $obSar;
                $categoryLabel = 'Bank Opening Balance';
                $voucherPrefix = 'OB-BANK-';

                if ($fromDate) {
                    // Prior expense transactions before from_date
                    $priorEt = ExpenseTransaction::where('bank_id', $b->id)
                        ->whereNull('deleted_at')
                        ->whereDate('date', '<', $fromDate)
                        ->get();
                    $priorDepositsPkr = 0; $priorWithdrawalsPkr = 0;
                    $priorDepositsSar = 0; $priorWithdrawalsSar = 0;
                    foreach ($priorEt as $pet) {
                        $cur = strtoupper($pet->currency ?: 'PKR');
                        $rate = (float) ($pet->exchange_rate > 0 ? $pet->exchange_rate : 1);
                        $rawAmt = (float) $pet->amount;
                        $pAmt = ($cur === 'PKR') ? $rawAmt : round($rawAmt * $rate, 2);
                        $sAmt = ($cur === 'SAR') ? $rawAmt : 0.00;
                        if ($pet->transaction_type === 'credit') {
                            $priorDepositsPkr += $pAmt;
                            $priorDepositsSar += $sAmt;
                        } else {
                            $priorWithdrawalsPkr += $pAmt;
                            $priorWithdrawalsSar += $sAmt;
                        }
                    }

                    // Prior bank transfers before from_date
                    $priorBtIn = (float) BankTransfer::where('to_bank_id', $b->id)
                        ->whereDate('transfer_date', '<', $fromDate)
                        ->sum('amount');
                    $priorBtOut = (float) BankTransfer::where('from_bank_id', $b->id)
                        ->whereDate('transfer_date', '<', $fromDate)
                        ->sum('amount');

                    $priorDepositsPkr += $priorBtIn;
                    $priorWithdrawalsPkr += $priorBtOut;

                    $effectiveObPkr = $obPkr + $priorDepositsPkr - $priorWithdrawalsPkr;
                    $effectiveObSar = $obSar + $priorDepositsSar - $priorWithdrawalsSar;

                    $effectiveDate = $fromDate;
                    $dTs = Carbon::parse($fromDate)->startOfDay()->timestamp - 100;
                    $categoryLabel = 'Opening Balance Brought Forward';
                    $voucherPrefix = 'OB-BF-';
                }

                if ($toDate && $effectiveDate > $toDate) {
                    continue;
                }

                $depositPkr    = $effectiveObPkr >= 0 ? $effectiveObPkr : 0.00;
                $withdrawalPkr = $effectiveObPkr < 0 ? abs($effectiveObPkr) : 0.00;
                $depositSar    = $effectiveObSar >= 0 ? $effectiveObSar : 0.00;
                $withdrawalSar = $effectiveObSar < 0 ? abs($effectiveObSar) : 0.00;

                $allRecords[] = [
                    'source'         => 'bank_opening_balance',
                    'id'             => $b->id,
                    'date'           => $effectiveDate,
                    'timestamp'      => $dTs,
                    'voucher_no'     => $voucherPrefix . str_pad($b->id, 3, '0', STR_PAD_LEFT),
                    'bank_id'        => $b->id,
                    'bank_name'      => $b->name,
                    'bank_account'   => $b->account_number,
                    'flow'           => $effectiveObPkr >= 0 ? 'deposit' : 'withdrawal',
                    'type'           => $effectiveObPkr >= 0 ? 'credit' : 'debit',
                    'type_badge'     => $effectiveObPkr >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger',
                    'category_label' => $categoryLabel,
                    'party_name'     => $b->name . ' (' . ($fromDate ? 'Opening Balance B/F' : 'Opening Balance') . ')',
                    'party_type'     => 'Bank Account',
                    'payment_mode'   => 'Opening Balance',
                    'currency'       => 'PKR',
                    'raw_amount'     => abs($effectiveObPkr),
                    'exchange_rate'  => (float) ($b->opening_balance_roe ?: 1),
                    'deposit_pkr'    => $depositPkr,
                    'withdrawal_pkr' => $withdrawalPkr,
                    'deposit_sar'    => $depositSar,
                    'withdrawal_sar' => $withdrawalSar,
                    'deposit'        => $depositPkr,
                    'withdrawal'     => $withdrawalPkr,
                    'description'    => ($fromDate ? 'Opening Balance Brought Forward as of ' . Carbon::parse($fromDate)->format('d M Y') : 'Initial Opening Balance') . ' of ' . $b->name . (!empty($b->account_number) ? ' (' . $b->account_number . ')' : ''),
                    'proof'          => null,
                ];
            }
        }

        // 1. Gather ExpenseTransactions associated with bank accounts
        if ($activityType !== 'transfer') {
            $etQ = ExpenseTransaction::with(['bank', 'supplier', 'client', 'staff', 'expense'])
                ->whereNotNull('bank_id')
                ->whereNull('deleted_at');

            if ($bankId) {
                $etQ->where('bank_id', $bankId);
            } elseif ($fromBankId) {
                $etQ->where('bank_id', $fromBankId)->where('transaction_type', 'debit');
            } elseif ($toBankId) {
                $etQ->where('bank_id', $toBankId)->where('transaction_type', 'credit');
            }

            if ($fromDate) {
                $etQ->whereDate('date', '>=', $fromDate);
            }
            if ($toDate) {
                $etQ->whereDate('date', '<=', $toDate);
            }

            $etList = $etQ->get();

            foreach ($etList as $et) {
                $cur = strtoupper($et->currency ?: 'PKR');
                $rate = (float) ($et->exchange_rate > 0 ? $et->exchange_rate : 1);
                $rawAmt = (float) $et->amount;
                $pkrAmt = ($cur === 'PKR') ? $rawAmt : round($rawAmt * $rate, 2);
                $sarAmt = ($cur === 'SAR') ? $rawAmt : 0.00;

                if ($minAmount && $pkrAmt < (float) $minAmount) continue;
                if ($maxAmount && $pkrAmt > (float) $maxAmount) continue;

                $isCredit = ($et->transaction_type === 'credit');
                $flow = $isCredit ? 'deposit' : 'withdrawal';

                if ($activityType === 'deposit' && !$isCredit) continue;
                if ($activityType === 'withdrawal' && $isCredit) continue;

                $depositPkr    = $isCredit ? $pkrAmt : 0.00;
                $withdrawalPkr = !$isCredit ? $pkrAmt : 0.00;
                $depositSar    = $isCredit ? $sarAmt : 0.00;
                $withdrawalSar = !$isCredit ? $sarAmt : 0.00;

                $pType = $et->payee_type ?: 'expense';
                $partyName = '—';
                $partyType = 'General';
                $categoryLabel = 'Office Expense';

                if ($pType === 'supplier') {
                    $partyName = $et->supplier ? $et->supplier->name : 'Supplier';
                    $partyType = 'Supplier';
                    $categoryLabel = 'Supplier Payment';
                } elseif ($pType === 'client') {
                    $partyName = $et->client ? $et->client->name : 'Client';
                    $partyType = 'Client';
                    $categoryLabel = $isCredit ? 'Client Receipt (Bank Deposit)' : 'Client Refund (Bank Outflow)';
                } elseif ($pType === 'staff') {
                    $partyName = $et->staff ? ($et->staff->first_name . ' ' . $et->staff->last_name) : 'Staff';
                    $partyType = 'Staff';
                    $categoryLabel = 'Staff Salary / Advance';
                } elseif ($pType === 'expense') {
                    $partyName = $et->expense ? $et->expense->name : 'Expense Head';
                    $partyType = 'Expense';
                    $categoryLabel = 'Office Expense (' . $partyName . ')';
                }

                $dStr = $et->date ? Carbon::parse($et->date)->format('Y-m-d') : Carbon::parse($et->created_at)->format('Y-m-d');
                $dTs = $et->date ? Carbon::parse($et->date)->startOfDay()->timestamp : Carbon::parse($et->created_at)->timestamp;

                $desc = $et->description ?: ($categoryLabel . ' via ' . ucfirst($et->payment_type));
                if ($cur !== 'PKR') {
                    $desc .= ' [' . $cur . ' ' . number_format($rawAmt, 2) . ' @ ROE ' . number_format($rate, 2) . ']';
                }

                $allRecords[] = [
                    'source'         => 'expense_transaction',
                    'id'             => $et->id,
                    'date'           => $dStr,
                    'timestamp'      => $dTs,
                    'voucher_no'     => $et->reference_no ?: ('#ETXN-' . $et->id),
                    'bank_id'        => $et->bank_id,
                    'bank_name'      => $et->bank ? $et->bank->name : 'Bank',
                    'bank_account'   => $et->bank?->account_number,
                    'flow'           => $flow,
                    'type'           => $isCredit ? 'credit' : 'debit',
                    'type_badge'     => $isCredit ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger',
                    'category_label' => $categoryLabel,
                    'party_name'     => $partyName,
                    'party_type'     => $partyType,
                    'payment_mode'   => ucwords(str_replace('_', ' ', $et->payment_type ?: 'bank')),
                    'currency'       => $cur,
                    'raw_amount'     => $rawAmt,
                    'exchange_rate'  => $rate,
                    'deposit_pkr'    => $depositPkr,
                    'withdrawal_pkr' => $withdrawalPkr,
                    'deposit_sar'    => $depositSar,
                    'withdrawal_sar' => $withdrawalSar,
                    'deposit'        => $depositPkr, // backward compatibility
                    'withdrawal'     => $withdrawalPkr,
                    'description'    => $desc,
                    'proof'          => $et->proof ? asset('assets/images/expense_proofs/' . $et->proof) : null,
                ];
            }
        }

        // 2. Gather BankTransfers (Inter-Bank Transfers - Always PKR)
        if ($activityType === 'all' || $activityType === 'transfer') {
            $btQ = BankTransfer::with(['fromBank', 'toBank']);

            if ($fromBankId) {
                $btQ->where('from_bank_id', $fromBankId);
            }
            if ($toBankId) {
                $btQ->where('to_bank_id', $toBankId);
            }
            if ($bankId) {
                $btQ->where(function ($q) use ($bankId) {
                    $q->where('from_bank_id', $bankId)
                      ->orWhere('to_bank_id', $bankId);
                });
            }
            if ($fromDate) {
                $btQ->whereDate('transfer_date', '>=', $fromDate);
            }
            if ($toDate) {
                $btQ->whereDate('transfer_date', '<=', $toDate);
            }
            if ($minAmount) {
                $btQ->where('amount', '>=', $minAmount);
            }
            if ($maxAmount) {
                $btQ->where('amount', '<=', $maxAmount);
            }

            $btList = $btQ->get();

            foreach ($btList as $bt) {
                $pkrAmt = (float) $bt->amount;
                $dStr = Carbon::parse($bt->transfer_date)->format('Y-m-d');
                $dTs = Carbon::parse($bt->transfer_date)->startOfDay()->timestamp + 1;

                $fromName = $bt->fromBank ? $bt->fromBank->name : 'Source Bank';
                $toName   = $bt->toBank ? $bt->toBank->name : 'Destination Bank';

                $depositPkr = 0.00;
                $withdrawalPkr = 0.00;
                $bankNameDisplay = $fromName . ' ➔ ' . $toName;

                if ($bankId) {
                    if ($bt->to_bank_id == $bankId) {
                        $depositPkr = $pkrAmt;
                        $bankNameDisplay = $toName;
                    }
                    if ($bt->from_bank_id == $bankId) {
                        $withdrawalPkr = $pkrAmt;
                        $bankNameDisplay = $fromName;
                    }
                } else {
                    $withdrawalPkr = $pkrAmt;
                    $depositPkr = $pkrAmt;
                }

                $allRecords[] = [
                    'source'         => 'bank_transfer',
                    'id'             => $bt->id,
                    'date'           => $dStr,
                    'timestamp'      => $dTs,
                    'voucher_no'     => 'TRF-' . $bt->id,
                    'bank_id'        => $bankId ?: $bt->from_bank_id,
                    'bank_name'      => $bankNameDisplay,
                    'bank_account'   => $bt->fromBank?->account_number,
                    'flow'           => 'transfer',
                    'type'           => 'transfer',
                    'type_badge'     => 'bg-soft-primary text-primary',
                    'category_label' => 'Inter-Bank Transfer',
                    'party_name'     => "From: {$fromName} ➔ To: {$toName}",
                    'party_type'     => 'Bank Transfer',
                    'payment_mode'   => 'Bank Transfer',
                    'currency'       => 'PKR',
                    'raw_amount'     => $pkrAmt,
                    'exchange_rate'  => 1,
                    'deposit_pkr'    => $depositPkr,
                    'withdrawal_pkr' => $withdrawalPkr,
                    'deposit_sar'    => 0.00,
                    'withdrawal_sar' => 0.00,
                    'deposit'        => $depositPkr,
                    'withdrawal'     => $withdrawalPkr,
                    'description'    => $bt->description ?: ("Transferred from {$fromName} to {$toName}"),
                    'proof'          => null,
                ];
            }
        }

        // Apply Keyword Search
        if (!empty($search)) {
            $sLower = strtolower($search);
            $allRecords = array_filter($allRecords, function ($item) use ($sLower) {
                return (
                    str_contains(strtolower($item['voucher_no']), $sLower) ||
                    str_contains(strtolower($item['bank_name']), $sLower) ||
                    str_contains(strtolower($item['party_name']), $sLower) ||
                    str_contains(strtolower($item['category_label']), $sLower) ||
                    str_contains(strtolower($item['payment_mode']), $sLower) ||
                    str_contains(strtolower($item['description']), $sLower) ||
                    str_contains((string) $item['deposit_pkr'], $sLower) ||
                    str_contains((string) $item['withdrawal_pkr'], $sLower) ||
                    str_contains((string) $item['deposit_sar'], $sLower) ||
                    str_contains((string) $item['withdrawal_sar'], $sLower)
                );
            });
        }

        // Sort chronologically ascending to compute Running Balances for both PKR and SAR
        usort($allRecords, function ($a, $b) {
            if ($a['date'] === $b['date']) {
                if ($a['source'] === 'bank_opening_balance' && $b['source'] !== 'bank_opening_balance') {
                    return -1;
                }
                if ($b['source'] === 'bank_opening_balance' && $a['source'] !== 'bank_opening_balance') {
                    return 1;
                }
                return $a['timestamp'] <=> $b['timestamp'];
            }
            return strcmp($a['date'], $b['date']);
        });

        $runningPkr = 0;
        $runningSar = 0;
        foreach ($allRecords as &$r) {
            $runningPkr += ($r['deposit_pkr'] - $r['withdrawal_pkr']);
            $runningSar += ($r['deposit_sar'] - $r['withdrawal_sar']);
            $r['running_balance_pkr'] = $runningPkr;
            $r['running_balance_sar'] = $runningSar;
            $r['running_balance']     = $runningPkr; // backward compatibility
        }
        unset($r);

        // Compute Bank-wise Summary Matrix (Dual Currency)
        $bankBreakdown = [];
        foreach ($allBanks as $b) {
            $bTxns = array_filter($allRecords, function ($it) use ($b) {
                return (isset($it['bank_id']) && $it['bank_id'] == $b->id) ||
                       (isset($it['bank_name']) && str_contains($it['bank_name'], $b->name));
            });

            $bOpPkr = (float) ($b->opening_balance_pkr ?? 0);
            $bOpSar = (float) ($b->opening_balance_sar ?? 0);
            $inPkr = 0;  $outPkr = 0;
            $inSar = 0;  $outSar = 0;
            $inCount = 0; $outCount = 0;

            foreach ($bTxns as $tx) {
                if ($tx['source'] === 'bank_opening_balance') {
                    continue; // Opening balance tracked separately in matrix column
                }
                if ($tx['deposit_pkr'] > 0 || $tx['deposit_sar'] > 0) {
                    $inPkr += $tx['deposit_pkr'];
                    $inSar += $tx['deposit_sar'];
                    $inCount++;
                }
                if ($tx['withdrawal_pkr'] > 0 || $tx['withdrawal_sar'] > 0) {
                    $outPkr += $tx['withdrawal_pkr'];
                    $outSar += $tx['withdrawal_sar'];
                    $outCount++;
                }
            }

            if ($bOpPkr > 0 || $bOpSar > 0 || $inPkr > 0 || $outPkr > 0 || $inSar > 0 || $outSar > 0) {
                $bankBreakdown[] = [
                    'bank'                => $b,
                    'opening_balance_pkr' => $bOpPkr,
                    'opening_balance_sar' => $bOpSar,
                    'incoming_pkr'        => $inPkr,
                    'outgoing_pkr'        => $outPkr,
                    'net_pkr'             => $bOpPkr + $inPkr - $outPkr,
                    'incoming_sar'        => $inSar,
                    'outgoing_sar'        => $outSar,
                    'net_sar'             => $bOpSar + $inSar - $outSar,
                    'in_count'            => $inCount,
                    'out_count'           => $outCount,
                    'total_tx'            => count($bTxns),
                ];
            }
        }

        // Compute Grand Totals for both PKR and SAR
        $totalOpeningBalancePkr = 0;
        $totalOpeningBalanceSar = 0;
        $totalPeriodDepositsPkr = 0;
        $totalPeriodDepositsSar = 0;
        $totalWithdrawalsPkr    = 0;
        $totalWithdrawalsSar    = 0;
        $depositCount           = 0;
        $withdrawalCount        = 0;

        foreach ($allRecords as $r) {
            if ($r['source'] === 'bank_opening_balance') {
                $totalOpeningBalancePkr += $r['deposit_pkr'];
                $totalOpeningBalanceSar += $r['deposit_sar'];
            } else {
                $totalPeriodDepositsPkr += $r['deposit_pkr'];
                $totalPeriodDepositsSar += $r['deposit_sar'];
                if ($r['deposit_pkr'] > 0 || $r['deposit_sar'] > 0) $depositCount++;
            }

            $totalWithdrawalsPkr += $r['withdrawal_pkr'];
            $totalWithdrawalsSar += $r['withdrawal_sar'];
            if ($r['withdrawal_pkr'] > 0 || $r['withdrawal_sar'] > 0) $withdrawalCount++;
        }

        $totalDepositsPkr = $totalOpeningBalancePkr + $totalPeriodDepositsPkr;
        $totalDepositsSar = $totalOpeningBalanceSar + $totalPeriodDepositsSar;

        $netMovementPkr = $totalDepositsPkr - $totalWithdrawalsPkr;
        $netMovementSar = $totalDepositsSar - $totalWithdrawalsSar;

        // Reverse for display (newest first)
        $displayRecords = array_reverse($allRecords);

        $selectedBank     = $bankId ? Bank::find($bankId) : null;
        $selectedFromBank = $fromBankId ? Bank::find($fromBankId) : null;
        $selectedToBank   = $toBankId ? Bank::find($toBankId) : null;

        return [
            'transactions'           => $displayRecords,
            'allBanks'               => $allBanks,
            'banks'                  => $allBanks,
            'bankBreakdown'          => $bankBreakdown,
            'totalOpeningBalancePkr' => $totalOpeningBalancePkr,
            'totalOpeningBalanceSar' => $totalOpeningBalanceSar,
            'totalPeriodDepositsPkr' => $totalPeriodDepositsPkr,
            'totalPeriodDepositsSar' => $totalPeriodDepositsSar,
            'totalDepositsPkr'       => $totalDepositsPkr,
            'totalWithdrawalsPkr'    => $totalWithdrawalsPkr,
            'netMovementPkr'         => $netMovementPkr,
            'closingBalancePkr'      => $netMovementPkr,
            'totalDepositsSar'       => $totalDepositsSar,
            'totalWithdrawalsSar'    => $totalWithdrawalsSar,
            'netMovementSar'         => $netMovementSar,
            'closingBalanceSar'      => $netMovementSar,
            'totalDeposits'          => $totalDepositsPkr, // backward compatibility
            'totalWithdrawals'       => $totalWithdrawalsPkr,
            'netMovement'            => $netMovementPkr,
            'depositCount'           => $depositCount,
            'withdrawalCount'        => $withdrawalCount,
            'totalCount'             => count($allRecords),
            'selectedBank'           => $selectedBank,
            'selectedFromBank'       => $selectedFromBank,
            'selectedToBank'         => $selectedToBank,
            'fromBankId'             => $fromBankId,
            'toBankId'               => $toBankId,
            'bankId'                 => $bankId,
            'fromDate'               => $fromDate,
            'toDate'                 => $toDate,
            'minAmount'              => $minAmount,
            'maxAmount'              => $maxAmount,
            'search'                 => $search,
            'activityType'           => $activityType,
        ];
    }
}
