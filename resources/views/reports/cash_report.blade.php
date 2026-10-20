@extends('layout.master')

@section('title', 'Cash Book & Cash Report')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">

<style>
    .cash-stat-card {
        border-radius: 12px;
        border: 1px solid #eef2f6;
        background: #fff;
        padding: 16px 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .cash-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .cash-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .cash-pill {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
    }
    .cash-pill-in {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }
    .cash-pill-out {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
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
                        <i class="ri-money-dollar-circle-fill text-success me-1"></i> Cash Book & Cash Report
                    </h4>
                    <span class="text-muted fs-12">Daily Cash Inflows, Outflows, Disbursements & Cash in Hand Ledger in both PKR & SAR.</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('expense.transaction.create') }}" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-plus-circle me-1"></i> New Transaction
                    </a>
                    <a href="{{ route('reports.cash.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm" target="_blank">
                        <i class="mdi mdi-file-pdf-box me-1"></i> Download PDF
                    </a>
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                        <i class="mdi mdi-printer me-1"></i> Print Cash Book
                    </button>
                    <a href="{{ route('reports.cash.export-excel', request()->query()) }}" class="btn btn-outline-success btn-sm">
                        <i class="mdi mdi-file-excel me-1"></i> Export Excel
                    </a>
                </div>
            </div>

            {{-- Quick Date Preset Bar --}}
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3 no-print">
                <span class="fs-12 fw-bold text-muted me-1"><i class="mdi mdi-clock-fast me-1"></i> Quick Filters:</span>
                <a href="{{ route('reports.cash', array_merge(request()->except('quick_date', 'date_from', 'date_to'), ['quick_date' => 'today'])) }}"
                    class="btn btn-xs {{ request('quick_date') === 'today' ? 'btn-primary' : 'btn-outline-secondary' }}">Today</a>
                <a href="{{ route('reports.cash', array_merge(request()->except('quick_date', 'date_from', 'date_to'), ['quick_date' => 'this_week'])) }}"
                    class="btn btn-xs {{ request('quick_date') === 'this_week' ? 'btn-primary' : 'btn-outline-secondary' }}">This Week</a>
                <a href="{{ route('reports.cash', array_merge(request()->except('quick_date', 'date_from', 'date_to'), ['quick_date' => 'this_month'])) }}"
                    class="btn btn-xs {{ request('quick_date') === 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }}">This Month</a>
                <a href="{{ route('reports.cash', array_merge(request()->except('quick_date', 'date_from', 'date_to'), ['quick_date' => 'last_month'])) }}"
                    class="btn btn-xs {{ request('quick_date') === 'last_month' ? 'btn-primary' : 'btn-outline-secondary' }}">Last Month</a>
                <a href="{{ route('reports.cash', array_merge(request()->except('quick_date', 'date_from', 'date_to'), ['quick_date' => 'this_year'])) }}"
                    class="btn btn-xs {{ request('quick_date') === 'this_year' ? 'btn-primary' : 'btn-outline-secondary' }}">This Year</a>
                <a href="{{ route('reports.cash', array_merge(request()->except('quick_date', 'date_from', 'date_to'), ['quick_date' => 'all'])) }}"
                    class="btn btn-xs {{ request('quick_date') === 'all' || !request('quick_date') ? 'btn-primary' : 'btn-outline-secondary' }}">All Time</a>
            </div>

            {{-- Filter Bar --}}
            <div class="card mb-3 border-0 shadow-sm no-print">
                <div class="card-body p-3 bg-light rounded-3">
                    <form action="{{ route('reports.cash') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">Cash Flow</label>
                            <select name="flow" class="form-select form-select-sm">
                                <option value="all" {{ request('flow') === 'all' ? 'selected' : '' }}>All Cash Flow</option>
                                <option value="inflow" {{ request('flow') === 'inflow' ? 'selected' : '' }}>Cash In (Receipts / Cr)</option>
                                <option value="outflow" {{ request('flow') === 'outflow' ? 'selected' : '' }}>Cash Out (Payments / Dr)</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">Category</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="all" {{ request('category') === 'all' ? 'selected' : '' }}>All Categories</option>
                                <option value="booking_advance" {{ request('category') === 'booking_advance' ? 'selected' : '' }}>Booking Advance</option>
                                <option value="client_receipt" {{ request('category') === 'client_receipt' ? 'selected' : '' }}>Client Receipts</option>
                                <option value="supplier_payment" {{ request('category') === 'supplier_payment' ? 'selected' : '' }}>Supplier Payments</option>
                                <option value="expense" {{ request('category') === 'expense' ? 'selected' : '' }}>Office Expenses</option>
                                <option value="staff_advance" {{ request('category') === 'staff_advance' ? 'selected' : '' }}>Staff Salary / Advances</option>
                                <option value="client_refund" {{ request('category') === 'client_refund' ? 'selected' : '' }}>Client Refunds</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">From Date</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', $dateFrom) }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">To Date</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', $dateTo) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">Search Keyword</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Voucher #, party name, note..." value="{{ request('search') }}">
                        </div>

                        <div class="col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply Filter">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                            <a href="{{ route('reports.cash') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                                <i class="mdi mdi-refresh"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 4 Metric Summary Cards with Dual Currency (PKR & SAR) --}}
            <div class="row g-3 mb-4">
                {{-- Total Cash Inflow (Cr) --}}
                <div class="col-md-3">
                    <div class="cash-stat-card border-start border-4 border-success">
                        <div class="d-flex align-items-center gap-3">
                            <div class="cash-icon-box bg-soft-success text-success">
                                <i class="ri-arrow-down-circle-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Total Cash Inflow (Cr)</div>
                                <div class="fs-17 fw-bold text-success mt-1">
                                    PKR {{ number_format($totalCashInPkr, 2) }}
                                </div>
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-soft-success text-success border border-success border-opacity-25 fs-11">
                                        SAR {{ number_format($totalCashInSar, 2) }}
                                    </span>
                                    <span class="fs-10 text-muted">{{ number_format($inflowCount) }} receipts</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Cash Outflow (Dr) --}}
                <div class="col-md-3">
                    <div class="cash-stat-card border-start border-4 border-danger">
                        <div class="d-flex align-items-center gap-3">
                            <div class="cash-icon-box bg-soft-danger text-danger">
                                <i class="ri-arrow-up-circle-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Total Cash Outflow (Dr)</div>
                                <div class="fs-17 fw-bold text-danger mt-1">
                                    PKR {{ number_format($totalCashOutPkr, 2) }}
                                </div>
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-soft-danger text-danger border border-danger border-opacity-25 fs-11">
                                        SAR {{ number_format($totalCashOutSar, 2) }}
                                    </span>
                                    <span class="fs-10 text-muted">{{ number_format($outflowCount) }} payments</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Net Cash in Hand --}}
                <div class="col-md-3">
                    <div class="cash-stat-card border-start border-4 {{ $netCashBalancePkr >= 0 ? 'border-primary' : 'border-warning' }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="cash-icon-box {{ $netCashBalancePkr >= 0 ? 'bg-soft-primary text-primary' : 'bg-soft-warning text-warning' }}">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Net Cash in Hand</div>
                                <div class="fs-17 fw-bold {{ $netCashBalancePkr >= 0 ? 'text-primary' : 'text-danger' }} mt-1">
                                    {{ $netCashBalancePkr >= 0 ? '+' : '' }} PKR {{ number_format($netCashBalancePkr, 2) }}
                                </div>
                                <div class="mt-1">
                                    <span class="badge {{ $netCashBalanceSar >= 0 ? 'bg-soft-primary text-primary border border-primary border-opacity-25' : 'bg-soft-danger text-danger border border-danger border-opacity-25' }} fs-11">
                                        {{ $netCashBalanceSar >= 0 ? '+' : '' }} SAR {{ number_format($netCashBalanceSar, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Cash Records --}}
                <div class="col-md-3">
                    <div class="cash-stat-card border-start border-4 border-info">
                        <div class="d-flex align-items-center gap-3">
                            <div class="cash-icon-box bg-soft-info text-info">
                                <i class="ri-file-list-3-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Total Cash Records</div>
                                <div class="fs-17 fw-bold text-dark mt-1">{{ number_format($totalCount) }} Entries</div>
                                <div class="fs-11 text-muted mt-1">Chronological Cash Book</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Full Detail Cash Book Statement Table with Dual Currency --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fs-14 fw-bold m-0 text-dark">
                        <i class="mdi mdi-book-open-page-variant text-primary me-1"></i> Cash Book Ledger ({{ count($transactions) }})
                    </h5>
                    <div class="fs-12 text-muted">
                        @if($dateFrom || $dateTo)
                            Period: <strong>{{ $dateFrom ?: 'Start' }}</strong> to <strong>{{ $dateTo ?: 'Present' }}</strong>
                        @else
                            Showing all-time records
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0" id="cashBookTable">
                            <thead class="table-light fs-11 text-uppercase">
                                <tr>
                                    <th rowspan="2" style="width: 38px;" class="align-middle text-center">S#</th>
                                    <th rowspan="2" class="align-middle">Date</th>
                                    <th rowspan="2" class="align-middle">Voucher #</th>
                                    <th rowspan="2" class="align-middle">Flow</th>
                                    <th rowspan="2" class="align-middle">Category</th>
                                    <th rowspan="2" class="align-middle">Party / Payee</th>
                                    <th rowspan="2" class="align-middle">Description</th>
                                    <th colspan="2" class="text-center bg-soft-success text-success border-bottom-0 py-1">PKR (Pakistani Rupee)</th>
                                    <th colspan="2" class="text-center bg-soft-warning text-dark border-bottom-0 py-1">SAR (Saudi Riyal)</th>
                                    <th colspan="2" class="text-center bg-light border-bottom-0 py-1">Running Balance</th>
                                    <th rowspan="2" class="text-center no-print align-middle" style="width: 55px;">Action</th>
                                </tr>
                                <tr>
                                    <th class="text-end text-success py-1">Cash In (Cr)</th>
                                    <th class="text-end text-danger py-1">Cash Out (Dr)</th>
                                    <th class="text-end text-success py-1">Cash In (Cr)</th>
                                    <th class="text-end text-danger py-1">Cash Out (Dr)</th>
                                    <th class="text-end py-1">PKR</th>
                                    <th class="text-end py-1">SAR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $t)
                                    <tr>
                                        <td class="text-center text-muted fs-11">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold text-dark text-nowrap">
                                                {{ \Carbon\Carbon::parse($t['date'])->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace fs-11">
                                                {{ $t['voucher_no'] }}
                                            </span>
                                            @if($t['currency'] === 'SAR')
                                                <div class="mt-1">
                                                    <span class="curr-pill-sar">SAR @ ROE {{ number_format($t['exchange_rate'], 2) }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($t['flow'] === 'inflow')
                                                <span class="cash-pill cash-pill-in">
                                                    <i class="mdi mdi-arrow-down-circle me-1"></i> In (Cr)
                                                </span>
                                            @else
                                                <span class="cash-pill cash-pill-out">
                                                    <i class="mdi mdi-arrow-up-circle me-1"></i> Out (Dr)
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-info text-info">{{ $t['category_label'] }}</span>
                                        </td>
                                        <td>
                                            @if(!empty($t['party_link']))
                                                <a href="{{ $t['party_link'] }}" class="fw-semibold text-primary">
                                                    {{ $t['party_name'] }}
                                                </a>
                                            @else
                                                <strong>{{ $t['party_name'] }}</strong>
                                            @endif
                                            <small class="text-muted d-block fs-10">{{ $t['party_type'] }}</small>
                                        </td>
                                        <td>
                                            <div class="text-dark fs-12 text-truncate" style="max-width: 240px;" title="{{ $t['description'] }}">
                                                {{ $t['description'] }}
                                            </div>
                                        </td>
                                        {{-- PKR Cash In (Cr) --}}
                                        <td class="text-end fw-bold text-success fs-13">
                                            {{ $t['cash_in_pkr'] > 0 ? '+ ' . number_format($t['cash_in_pkr'], 2) : '-' }}
                                        </td>
                                        {{-- PKR Cash Out (Dr) --}}
                                        <td class="text-end fw-bold text-danger fs-13">
                                            {{ $t['cash_out_pkr'] > 0 ? '- ' . number_format($t['cash_out_pkr'], 2) : '-' }}
                                        </td>
                                        {{-- SAR Cash In (Cr) --}}
                                        <td class="text-end fw-bold text-success fs-13">
                                            {{ $t['cash_in_sar'] > 0 ? '+ ' . number_format($t['cash_in_sar'], 2) : '-' }}
                                        </td>
                                        {{-- SAR Cash Out (Dr) --}}
                                        <td class="text-end fw-bold text-danger fs-13">
                                            {{ $t['cash_out_sar'] > 0 ? '- ' . number_format($t['cash_out_sar'], 2) : '-' }}
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
                                            @elseif($t['source'] === 'booking_advance')
                                                <a href="{{ route('booking.edit', $t['id']) }}" class="btn btn-outline-primary btn-xs" title="View Booking">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-alert-circle-outline fs-24 d-block mb-1"></i>
                                            No cash transactions found matching the specified filter criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($transactions) > 0)
                                <tfoot class="table-light fw-bold fs-12">
                                    <tr>
                                        <td colspan="7" class="text-end">CASH BOOK TOTALS:</td>
                                        <td class="text-end text-success fs-13">+ {{ number_format($totalCashInPkr, 2) }}</td>
                                        <td class="text-end text-danger fs-13">- {{ number_format($totalCashOutPkr, 2) }}</td>
                                        <td class="text-end text-success fs-13">+ {{ number_format($totalCashInSar, 2) }}</td>
                                        <td class="text-end text-danger fs-13">- {{ number_format($totalCashOutSar, 2) }}</td>
                                        <td class="text-end fs-13 {{ $netCashBalancePkr >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $netCashBalancePkr >= 0 ? '+' : '' }} {{ number_format($netCashBalancePkr, 2) }}
                                        </td>
                                        <td class="text-end fs-13 {{ $netCashBalanceSar >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $netCashBalanceSar >= 0 ? '+' : '' }} {{ number_format($netCashBalanceSar, 2) }}
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
@endsection
