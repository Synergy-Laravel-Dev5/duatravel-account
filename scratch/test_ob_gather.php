<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bank;
use App\Models\ExpenseTransaction;
use App\Models\BankTransfer;
use Carbon\Carbon;

$allBanks = Bank::where('status', 'active')->orderBy('name')->get();
$allRecords = [];

$bankId = 1; // Samba
$activityType = 'all';
$fromDate = null;
$toDate = null;

// 0. Gather Bank Opening Balances
if ($activityType !== 'withdrawal' && $activityType !== 'transfer') {
    $banksQuery = Bank::where('status', 'active');
    if ($bankId) {
        $banksQuery->where('id', $bankId);
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

echo "Total records gathered: " . count($allRecords) . "\n";
print_r($allRecords[0]);
