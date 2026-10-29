@extends('layout.master')

@section('title', 'Bank Transaction & Statement Report')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">

<style>
    .bt-stat-card {
        border-radius: 12px;
        border: 1px solid #eef2f6;
        background: #fff;
        padding: 16px 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .bt-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .bt-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .bank-pill {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
    }
    .bank-pill-deposit {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }
    .bank-pill-withdrawal {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    .bank-pill-transfer {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .curr-pill-pkr {
        background-color: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .curr-pill-sar {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 4px;
    }
    @media print {
        .no-print, .app-sidebar-menu, .topbar-custom, footer {
            display: none !important;
        }
        .content-page {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            {{-- Top Action Header --}}
            <div class="py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom mb-3 no-print">
                <div>
                    <h4 class="fs-18 fw-bold m-0 text-dark">
                        <i class="mdi mdi-bank text-primary me-1"></i> Bank Transaction & Statement Report
                    </h4>
                    <span class="text-muted fs-12">Dual-Currency (PKR & SAR) Bank statement tracking Client Receipts, Supplier Payments, Expenses, and Transfers.</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('bank-transfer.report.filter') }}" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-filter-variant me-1"></i> Modify Filter
                    </a>
                    <a href="{{ route('bank-transfer.create') }}" class="btn btn-outline-info btn-sm">
                        <i class="mdi mdi-repeat me-1"></i> New Transfer
                    </a>
                    <a href="{{ route('expense.transaction.create') }}" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-plus-circle me-1"></i> New Transaction
                    </a>
                    <a href="{{ route('bank-transfer.report.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm" target="_blank">
                        <i class="mdi mdi-file-pdf-box me-1"></i> Download PDF
                    </a>
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                        <i class="mdi mdi-printer me-1"></i> Print Statement
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportTableToCSV('bank_transactions_report.csv')">
                        <i class="mdi mdi-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </div>

            {{-- Active Filter Bar --}}
            <div class="card mb-3 border-0 shadow-sm no-print">
                <div class="card-body p-3 bg-light rounded-3">
                    <form action="{{ route('bank-transfer.report') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">Bank Account</label>
                            <select name="bank_id" class="form-select form-select-sm">
                                <option value="">-- All Banks (Consolidated) --</option>
                                @foreach ($banks as $b)
                                    <option value="{{ $b->id }}" {{ request('bank_id') == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }} {{ !empty($b->account_number) ? '(' . $b->account_number . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">Activity Type</label>
                            <select name="activity_type" class="form-select form-select-sm">
                                <option value="all" {{ request('activity_type') == 'all' ? 'selected' : '' }}>All Activity</option>
                                <option value="deposit" {{ request('activity_type') == 'deposit' ? 'selected' : '' }}>Deposits (Credits / Cr)</option>
                                <option value="withdrawal" {{ request('activity_type') == 'withdrawal' ? 'selected' : '' }}>Withdrawals (Debits / Dr)</option>
                                <option value="transfer" {{ request('activity_type') == 'transfer' ? 'selected' : '' }}>Internal Transfers</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">From Date</label>
                            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">To Date</label>
                            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">Search Keyword</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Voucher, party, note..." value="{{ request('search') }}">
                        </div>

                        <div class="col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply Filter">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                            <a href="{{ route('bank-transfer.report') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                                <i class="mdi mdi-refresh"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 4 Metric Summary Cards with Dual Currency (PKR & SAR) --}}
            <div class="row g-3 mb-4">
                {{-- 1. Bank Opening Balance --}}
                <div class="col-md-3">
                    <div class="bt-stat-card border-start border-4 border-primary">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bt-icon-box bg-soft-primary text-primary">
                                <i class="ri-bank-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Bank Opening Balance</div>
                                <div class="fs-17 fw-bold text-primary mt-1">
                                    PKR {{ number_format($totalOpeningBalancePkr ?? 0, 2) }}
                                </div>
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    @if(($totalOpeningBalanceSar ?? 0) > 0)
                                        <span class="badge bg-soft-primary text-primary border border-primary border-opacity-25 fs-11">
                                            SAR {{ number_format($totalOpeningBalanceSar, 2) }}
                                        </span>
                                    @endif
                                    <span class="fs-10 text-muted">{{ $selectedBank ? $selectedBank->name : count($allBanks) . ' Bank Account(s)' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Total Period Deposits / Credits --}}
                <div class="col-md-3">
                    <div class="bt-stat-card border-start border-4 border-success">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bt-icon-box bg-soft-success text-success">
                                <i class="ri-arrow-down-circle-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Total Deposits (Cr / Inflow)</div>
                                <div class="fs-17 fw-bold text-success mt-1">
                                    PKR {{ number_format($totalPeriodDepositsPkr ?? 0, 2) }}
                                </div>
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-soft-success text-success border border-success border-opacity-25 fs-11">
                                        SAR {{ number_format($totalPeriodDepositsSar ?? 0, 2) }}
                                    </span>
                                    <span class="fs-10 text-muted">{{ number_format($depositCount) }} deposits</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Total Withdrawals / Debits --}}
                <div class="col-md-3">
                    <div class="bt-stat-card border-start border-4 border-danger">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bt-icon-box bg-soft-danger text-danger">
                                <i class="ri-arrow-up-circle-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Total Withdrawals (Dr / Outflow)</div>
                                <div class="fs-17 fw-bold text-danger mt-1">
                                    PKR {{ number_format($totalWithdrawalsPkr, 2) }}
                                </div>
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-soft-danger text-danger border border-danger border-opacity-25 fs-11">
                                        SAR {{ number_format($totalWithdrawalsSar, 2) }}
                                    </span>
                                    <span class="fs-10 text-muted">{{ number_format($withdrawalCount) }} withdrawals</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Current Net Balance --}}
                <div class="col-md-3">
                    <div class="bt-stat-card border-start border-4 {{ ($closingBalancePkr ?? $netMovementPkr) >= 0 ? 'border-info' : 'border-warning' }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bt-icon-box {{ ($closingBalancePkr ?? $netMovementPkr) >= 0 ? 'bg-soft-info text-info' : 'bg-soft-warning text-warning' }}">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Current / Net Balance</div>
                                <div class="fs-17 fw-bold {{ ($closingBalancePkr ?? $netMovementPkr) >= 0 ? 'text-dark' : 'text-danger' }} mt-1">
                                    {{ ($closingBalancePkr ?? $netMovementPkr) >= 0 ? '+' : '' }} PKR {{ number_format($closingBalancePkr ?? $netMovementPkr, 2) }}
                                </div>
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge {{ ($closingBalanceSar ?? $netMovementSar) >= 0 ? 'bg-soft-info text-dark border border-info border-opacity-25' : 'bg-soft-danger text-danger border border-danger border-opacity-25' }} fs-11">
                                        {{ ($closingBalanceSar ?? $netMovementSar) >= 0 ? '+' : '' }} SAR {{ number_format($closingBalanceSar ?? $netMovementSar, 2) }}
                                    </span>
                                    <span class="fs-10 text-muted">Closing Position</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bank Movement Summary Matrix (Dual Currency) --}}
            @if(count($bankBreakdown) > 0)
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="fs-14 fw-bold m-0 text-dark">
                            <i class="mdi mdi-swap-horizontal text-primary me-1"></i> Bank-Wise Balance & Currency Activity Matrix
                        </h5>
                        <span class="badge bg-soft-info text-info fs-11">Dual Currency: PKR & SAR</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover align-middle mb-0">
                                <thead class="table-light fs-12 text-uppercase">
                                    <tr>
                                        <th rowspan="2" class="align-middle">Bank Account</th>
                                        <th colspan="2" class="text-center text-primary bg-soft-primary py-1 border-bottom-0">Opening Balance</th>
                                        <th colspan="2" class="text-center text-success bg-soft-success py-1 border-bottom-0">Incoming (Cr / Deposits)</th>
                                        <th colspan="2" class="text-center text-danger bg-soft-danger py-1 border-bottom-0">Outgoing (Dr / Withdrawals)</th>
                                        <th colspan="2" class="text-center text-dark bg-light py-1 border-bottom-0">Current / Net Balance</th>
                                        <th rowspan="2" class="text-center align-middle" style="width: 80px;">Activity</th>
                                    </tr>
                                    <tr>
                                        <th class="text-end text-primary py-1">PKR</th>
                                        <th class="text-end text-primary py-1">SAR</th>
                                        <th class="text-end text-success py-1">PKR</th>
                                        <th class="text-end text-success py-1">SAR</th>
                                        <th class="text-end text-danger py-1">PKR</th>
                                        <th class="text-end text-danger py-1">SAR</th>
                                        <th class="text-end py-1">PKR</th>
                                        <th class="text-end py-1">SAR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bankBreakdown as $bData)
                                        <tr>
                                            <td>
                                                <i class="mdi mdi-bank text-primary me-1"></i>
                                                <strong>{{ $bData['bank']->name }}</strong>
                                                @if(!empty($bData['bank']->account_number))
                                                    <span class="text-muted fs-11 ms-1">({{ $bData['bank']->account_number }})</span>
                                                @endif
                                            </td>
                                            <td class="text-end text-primary fw-semibold">
                                                {{ number_format($bData['opening_balance_pkr'] ?? 0, 2) }}
                                            </td>
                                            <td class="text-end text-primary fw-semibold">
                                                {{ number_format($bData['opening_balance_sar'] ?? 0, 2) }}
                                            </td>
                                            <td class="text-end text-success fw-semibold">
                                                + {{ number_format($bData['incoming_pkr'], 2) }}
                                            </td>
                                            <td class="text-end text-success fw-semibold">
                                                + {{ number_format($bData['incoming_sar'], 2) }}
                                            </td>
                                            <td class="text-end text-danger fw-semibold">
                                                - {{ number_format($bData['outgoing_pkr'], 2) }}
                                            </td>
                                            <td class="text-end text-danger fw-semibold">
                                                - {{ number_format($bData['outgoing_sar'], 2) }}
                                            </td>
                                            <td class="text-end fw-bold {{ $bData['net_pkr'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $bData['net_pkr'] >= 0 ? '+' : '' }} {{ number_format($bData['net_pkr'], 2) }}
                                            </td>
                                            <td class="text-end fw-bold {{ $bData['net_sar'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $bData['net_sar'] >= 0 ? '+' : '' }} {{ number_format($bData['net_sar'], 2) }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-soft-primary text-primary fs-11">{{ $bData['total_tx'] }} txns</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Full Detail Bank Statement Table with Dual Currency --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fs-14 fw-bold m-0 text-dark">
                        <i class="mdi mdi-format-list-bulleted text-primary me-1"></i> Bank Statement Ledger ({{ count($transactions) }})
                    </h5>
                    <div class="fs-12 text-muted">
                        @if(request('from_date') || request('to_date'))
                            Period: <strong>{{ request('from_date') ?: 'Start' }}</strong> to <strong>{{ request('to_date') ?: 'Present' }}</strong>
                        @else
                            Showing all-time records
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0" id="transfersTable">
                            <thead class="table-light fs-11 text-uppercase">
                                <tr>
                                    <th rowspan="2" style="width: 38px;" class="align-middle text-center">S#</th>
                                    <th rowspan="2" class="align-middle">Date</th>
                                    <th rowspan="2" class="align-middle">Voucher #</th>
                                    <th rowspan="2" class="align-middle">Bank Account</th>
                                    <th rowspan="2" class="align-middle">Party / Narration</th>
                                    <th rowspan="2" class="align-middle">Mode</th>
                                    <th colspan="2" class="text-center bg-soft-success text-success border-bottom-0 py-1">PKR (Pakistani Rupee)</th>
                                    <th colspan="2" class="text-center bg-soft-warning text-dark border-bottom-0 py-1">SAR (Saudi Riyal)</th>
                                    <th colspan="2" class="text-center bg-light border-bottom-0 py-1">Running Balance</th>
                                    <th rowspan="2" class="text-center no-print align-middle" style="width: 55px;">Action</th>
                                </tr>
                                <tr>
                                    <th class="text-end text-success py-1">Deposit (Cr)</th>
                                    <th class="text-end text-danger py-1">Withdrawal (Dr)</th>
                                    <th class="text-end text-success py-1">Deposit (Cr)</th>
                                    <th class="text-end text-danger py-1">Withdrawal (Dr)</th>
                                    <th class="text-end py-1">PKR</th>
                                    <th class="text-end py-1">SAR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $t)
                                    <tr class="{{ $t['source'] === 'bank_opening_balance' ? 'table-warning bg-opacity-25' : '' }}">
                                        <td class="text-center text-muted fs-11">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold text-dark text-nowrap">
                                                {{ \Carbon\Carbon::parse($t['date'])->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($t['source'] === 'bank_opening_balance')
                                                <span class="badge bg-warning text-dark border font-monospace fs-11">
                                                    {{ $t['voucher_no'] }}
                                                </span>
                                                <div class="mt-1">
                                                    <span class="badge bg-soft-warning text-dark border border-warning border-opacity-25 fs-10">OPENING BAL</span>
                                                </div>
                                            @else
                                                <span class="badge bg-light text-dark border font-monospace fs-11">
                                                    {{ $t['voucher_no'] }}
                                                </span>
                                                @if($t['currency'] === 'SAR')
                                                    <div class="mt-1">
                                                        <span class="curr-pill-sar">SAR @ ROE {{ number_format($t['exchange_rate'], 2) }}</span>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-primary">
                                                <i class="mdi mdi-bank me-1"></i>{{ $t['bank_name'] }}
                                            </span>
                                            @if(!empty($t['bank_account']))
                                                <div class="fs-10 text-muted">{{ $t['bank_account'] }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $t['party_name'] }}</strong>
                                            @if(!empty($t['description']))
                                                <div class="text-muted fs-11 mt-1 text-truncate" style="max-width: 250px;" title="{{ $t['description'] }}">
                                                    {{ $t['description'] }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($t['source'] === 'bank_opening_balance')
                                                <span class="badge bg-soft-warning text-dark border border-warning border-opacity-25">Opening Balance</span>
                                            @else
                                                <span class="badge bg-soft-secondary text-dark">{{ $t['payment_mode'] }}</span>
                                            @endif
                                        </td>
                                        {{-- PKR Deposit (Cr) --}}
                                        <td class="text-end fw-bold text-success fs-13">
                                            {{ $t['deposit_pkr'] > 0 ? '+ ' . number_format($t['deposit_pkr'], 2) : '-' }}
                                        </td>
                                        {{-- PKR Withdrawal (Dr) --}}
                                        <td class="text-end fw-bold text-danger fs-13">
                                            {{ $t['withdrawal_pkr'] > 0 ? '- ' . number_format($t['withdrawal_pkr'], 2) : '-' }}
                                        </td>
                                        {{-- SAR Deposit (Cr) --}}
                                        <td class="text-end fw-bold text-success fs-13">
                                            {{ $t['deposit_sar'] > 0 ? '+ ' . number_format($t['deposit_sar'], 2) : '-' }}
                                        </td>
                                        {{-- SAR Withdrawal (Dr) --}}
                                        <td class="text-end fw-bold text-danger fs-13">
                                            {{ $t['withdrawal_sar'] > 0 ? '- ' . number_format($t['withdrawal_sar'], 2) : '-' }}
                                        </td>
                                        {{-- Running Balance PKR --}}
                                        <td class="text-end fw-semibold fs-12 {{ $t['running_balance_pkr'] >= 0 ? 'text-dark' : 'text-danger' }}">
                                            {{ number_format($t['running_balance_pkr'], 2) }}
                                        </td>
                                        {{-- Running Balance SAR --}}
                                        <td class="text-end fw-semibold fs-12 {{ $t['running_balance_sar'] >= 0 ? 'text-dark' : 'text-danger' }}">
                                            {{ number_format($t['running_balance_sar'], 2) }}
                                        </td>
                                        <td class="text-center no-print">
                                            @if($t['source'] === 'expense_transaction')
                                                <a href="{{ route('expense.transaction.edit', $t['id']) }}" class="btn btn-outline-info btn-xs" title="Edit Transaction">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                            @elseif($t['source'] === 'bank_transfer')
                                                <form action="{{ route('bank-transfer.destroy', $t['id']) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-xs" onclick="return confirm('Delete this transfer record?')" title="Delete Transfer">
                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                    </button>
                                                </form>
                                            @elseif($t['source'] === 'bank_opening_balance')
                                                <a href="{{ route('bank.edit', $t['id']) }}" class="btn btn-outline-secondary btn-xs" title="Edit Bank Opening Balance">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-alert-circle-outline fs-24 d-block mb-1"></i>
                                            No bank transactions or transfers found matching the specified filter criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($transactions) > 0)
                                <tfoot class="table-light fw-bold fs-12">
                                    <tr>
                                        <td colspan="6" class="text-end">STATEMENT TOTALS:</td>
                                        <td class="text-end text-success fs-13">+ {{ number_format($totalDepositsPkr, 2) }}</td>
                                        <td class="text-end text-danger fs-13">- {{ number_format($totalWithdrawalsPkr, 2) }}</td>
                                        <td class="text-end text-success fs-13">+ {{ number_format($totalDepositsSar, 2) }}</td>
                                        <td class="text-end text-danger fs-13">- {{ number_format($totalWithdrawalsSar, 2) }}</td>
                                        <td class="text-end fs-13 {{ $netMovementPkr >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $netMovementPkr >= 0 ? '+' : '' }} {{ number_format($netMovementPkr, 2) }}
                                        </td>
                                        <td class="text-end fs-13 {{ $netMovementSar >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $netMovementSar >= 0 ? '+' : '' }} {{ number_format($netMovementSar, 2) }}
                                        </td>
                                        <td class="no-print"></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function exportTableToCSV(filename) {
        let csv = [];
        const rows = document.querySelectorAll("#transfersTable tr");
        
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll("td, th");
            for (let j = 0; j < cols.length - 1; j++) {
                let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").trim();
                text = text.replace(/"/g, '""');
                row.push('"' + text + '"');
            }
            if (row.length > 0) csv.push(row.join(","));
        }

        const csvFile = new Blob([csv.join("\n")], { type: "text/csv;charset=utf-8;" });
        const downloadLink = document.createElement("a");
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
</script>
@endsection
