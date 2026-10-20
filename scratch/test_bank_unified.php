<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bank;
use App\Models\ExpenseTransaction;
use App\Models\BankTransfer;
use Carbon\Carbon;

$bankId = null; // test all banks
$allBanks = Bank::where('status', 'active')->orderBy('name')->get();

$allRecords = [];

// 1. Gather ExpenseTransactions with bank_id
$etQ = ExpenseTransaction::with(['bank', 'supplier', 'client', 'staff', 'expense'])
    ->whereNotNull('bank_id')
    ->whereNull('deleted_at');

if ($bankId) {
    $etQ->where('bank_id', $bankId);
}

$etList = $etQ->get();
foreach ($etList as $et) {
    $cur = strtoupper($et->currency ?: 'PKR');
    $rate = (float) ($et->exchange_rate > 0 ? $et->exchange_rate : 1);
    $rawAmt = (float) $et->amount;
    $pkrAmt = ($cur === 'PKR') ? $rawAmt : round($rawAmt * $rate, 2);

    $isCredit = ($et->transaction_type === 'credit');
    $deposit = $isCredit ? $pkrAmt : 0.00;
    $withdrawal = !$isCredit ? $pkrAmt : 0.00;

    $pType = $et->payee_type ?: 'expense';
    $partyName = '—';
    $categoryLabel = 'Office Expense';
    if ($pType === 'supplier') {
        $partyName = $et->supplier ? $et->supplier->name : 'Supplier';
        $categoryLabel = 'Supplier Payment';
    } elseif ($pType === 'client') {
        $partyName = $et->client ? $et->client->name : 'Client';
        $categoryLabel = $isCredit ? 'Client Receipt (Deposit)' : 'Client Refund (Withdrawal)';
    } elseif ($pType === 'staff') {
        $partyName = $et->staff ? ($et->staff->first_name . ' ' . $et->staff->last_name) : 'Staff';
        $categoryLabel = 'Staff Salary / Advance';
    } elseif ($pType === 'expense') {
        $partyName = $et->expense ? $et->expense->name : 'Expense';
        $categoryLabel = 'Office Expense (' . $partyName . ')';
    }

    $allRecords[] = [
        'source'         => 'expense_transaction',
        'id'             => $et->id,
        'date'           => $et->date ? Carbon::parse($et->date)->format('Y-m-d') : Carbon::parse($et->created_at)->format('Y-m-d'),
        'voucher_no'     => $et->reference_no ?: ('#ETXN-' . $et->id),
        'bank_id'        => $et->bank_id,
        'bank_name'      => $et->bank ? $et->bank->name : 'Bank',
        'bank_account'   => $et->bank?->account_number,
        'type'           => $isCredit ? 'credit' : 'debit',
        'category_label' => $categoryLabel,
        'party_name'     => $partyName,
        'payment_mode'   => ucwords(str_replace('_', ' ', $et->payment_type ?: 'bank')),
        'currency'       => $cur,
        'raw_amount'     => $rawAmt,
        'exchange_rate'  => $rate,
        'deposit'        => $deposit,
        'withdrawal'     => $withdrawal,
        'description'    => $et->description ?: ($categoryLabel . ' via ' . ucfirst($et->payment_type)),
    ];
}

// 2. Gather BankTransfers
$btQ = BankTransfer::with(['fromBank', 'toBank']);
if ($bankId) {
    $btQ->where(function ($q) use ($bankId) {
        $q->where('from_bank_id', $bankId)->orWhere('to_bank_id', $bankId);
    });
}
$btList = $btQ->get();
foreach ($btList as $bt) {
    $pkrAmt = (float) $bt->amount;
    $allRecords[] = [
        'source'         => 'bank_transfer',
        'id'             => $bt->id,
        'date'           => Carbon::parse($bt->transfer_date)->format('Y-m-d'),
        'voucher_no'     => 'TRF-' . $bt->id,
        'bank_id'        => $bt->from_bank_id,
        'bank_name'      => ($bt->fromBank?->name ?? 'Bank') . ' ➔ ' . ($bt->toBank?->name ?? 'Bank'),
        'bank_account'   => null,
        'type'           => 'transfer',
        'category_label' => 'Internal Bank Transfer',
        'party_name'     => 'From: ' . ($bt->fromBank?->name ?? 'Bank') . ' To: ' . ($bt->toBank?->name ?? 'Bank'),
        'payment_mode'   => 'Inter-Bank Transfer',
        'currency'       => 'PKR',
        'raw_amount'     => $pkrAmt,
        'exchange_rate'  => 1,
        'deposit'        => $bankId && $bt->to_bank_id == $bankId ? $pkrAmt : 0.00,
        'withdrawal'     => $bankId && $bt->from_bank_id == $bankId ? $pkrAmt : ($bankId ? 0.00 : $pkrAmt),
        'description'    => $bt->description ?: 'Internal transfer',
    ];
}

echo "Total Bank Ledger Records: " . count($allRecords) . "\n";
foreach ($allRecords as $r) {
    echo "Date: {$r['date']} | Voucher: {$r['voucher_no']} | Bank: {$r['bank_name']} | Type: {$r['type']} | Party: {$r['party_name']} | In: PKR {$r['deposit']} | Out: PKR {$r['withdrawal']}\n";
}
