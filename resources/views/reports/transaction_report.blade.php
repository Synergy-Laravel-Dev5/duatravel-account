@extends('layout.master')

@section('title', 'All Transactions Report')

@section('content')

<style>
    .trans-stat-card {
        border-radius: 14px;
        border: none;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .trans-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.08) !important;
    }
    .quick-pill {
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
        padding: 4px 12px;
        text-decoration: none;
        transition: all .15s ease;
    }
    .quick-pill.active {
        background: #1e1b4b !important;
        color: #fff !important;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: #fff;
        }
        .content-page {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- TOP HEADER -->
            <div class="py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 no-print border-bottom mb-3">
                <div>
                    <h4 class="fs-20 fw-bold m-0 text-dark">
                        <i class="mdi mdi-receipt-text-outline text-primary me-1"></i> All Transactions Report
                    </h4>
                    <p class="text-muted fs-12 mb-0">Unified financial ledger for all incoming client receipts, booking advances, supplier payments & operational expenses.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('reports.transactions.export-excel') }}?{{ http_build_query(request()->all()) }}" class="btn btn-success btn-sm shadow-sm">
                        <i class="mdi mdi-file-excel me-1"></i> Export Excel
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary btn-sm shadow-sm">
                        <i class="mdi mdi-printer me-1"></i> Print Report
                    </button>
                    <a href="{{ route('transaction.ledger.filter') }}" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-book-open-outline me-1"></i> Client Ledger (Dr/Cr)
                    </a>
                </div>
            </div>

            <!-- EXECUTIVE SUMMARY KPI CARDS -->
            <div class="row g-3 mb-3">
                <!-- Total Inflow -->
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm trans-stat-card border-start border-4 border-success bg-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-11 fw-bold text-uppercase d-block">Total Inflows (Receipts / Cr)</span>
                                    <h4 class="fw-bold text-success mt-1 mb-0 fs-17">PKR {{ number_format($totalInflowPkr, 2) }}</h4>
                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        <span class="badge bg-soft-success text-success border border-success border-opacity-25 fs-11">
                                            SAR {{ number_format($totalInflowSar, 2) }}
                                        </span>
                                        <small class="text-muted fs-11">{{ $inflowCount }} receipts</small>
                                    </div>
                                </div>
                                <div class="bg-soft-success text-success p-2 rounded-3">
                                    <i class="mdi mdi-cash-plus fs-22"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Outflow -->
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm trans-stat-card border-start border-4 border-danger bg-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-11 fw-bold text-uppercase d-block">Total Outflows (Payments / Dr)</span>
                                    <h4 class="fw-bold text-danger mt-1 mb-0 fs-17">PKR {{ number_format($totalOutflowPkr, 2) }}</h4>
                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        <span class="badge bg-soft-danger text-danger border border-danger border-opacity-25 fs-11">
                                            SAR {{ number_format($totalOutflowSar, 2) }}
                                        </span>
                                        <small class="text-muted fs-11">{{ $outflowCount }} payments</small>
                                    </div>
                                </div>
                                <div class="bg-soft-danger text-danger p-2 rounded-3">
                                    <i class="mdi mdi-cash-minus fs-22"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Net Cash Position -->
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm trans-stat-card border-start border-4 {{ $netCashFlowPkr >= 0 ? 'border-primary' : 'border-warning' }} bg-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <span class="text-muted fs-11 fw-bold text-uppercase d-block">Net Cash Flow (Surplus)</span>
                                    <h4 class="fw-bold {{ $netCashFlowPkr >= 0 ? 'text-primary' : 'text-danger' }} mt-1 mb-0 fs-17">
                                        {{ $netCashFlowPkr >= 0 ? '+' : '' }} PKR {{ number_format($netCashFlowPkr, 2) }}
                                    </h4>
                                    <div class="mt-1">
                                        <span class="badge {{ $netCashFlowSar >= 0 ? 'bg-soft-primary text-primary border border-primary border-opacity-25' : 'bg-soft-danger text-danger border border-danger border-opacity-25' }} fs-11">
                                            {{ $netCashFlowSar >= 0 ? '+' : '' }} SAR {{ number_format($netCashFlowSar, 2) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="{{ $netCashFlowPkr >= 0 ? 'bg-soft-primary text-primary' : 'bg-soft-warning text-warning' }} p-2 rounded-3">
                                    <i class="mdi mdi-scale-balance fs-22"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Transactions -->
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm trans-stat-card border-start border-4 border-info bg-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="text-muted fs-11 fw-bold text-uppercase d-block">Total Records</span>
                                    <h4 class="fw-bold text-dark mt-1 mb-0 fs-18">{{ $totalCount }}</h4>
                                    <small class="text-muted fs-11">Transactions recorded</small>
                                </div>
                                <div class="bg-soft-info text-info p-2 rounded-3">
                                    <i class="mdi mdi-format-list-numbered fs-22"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTER BAR -->
            <div class="card shadow-sm border-0 mb-3 no-print">
                <div class="card-body p-3">
                    <!-- Quick Date Pills -->
                    <div class="d-flex flex-wrap align-items-center gap-1 mb-3 pb-2 border-bottom">
                        <span class="text-muted fs-12 fw-bold me-2"><i class="mdi mdi-calendar-range me-1"></i> Quick Presets:</span>
                        <a href="{{ route('reports.transactions', array_merge(request()->except(['quick_date', 'date_from', 'date_to']), ['quick_date' => 'all'])) }}"
                           class="btn btn-sm quick-pill {{ empty($quickDate) || $quickDate === 'all' ? 'active' : 'btn-light' }}">All Time</a>
                        <a href="{{ route('reports.transactions', array_merge(request()->except(['quick_date', 'date_from', 'date_to']), ['quick_date' => 'today'])) }}"
                           class="btn btn-sm quick-pill {{ $quickDate === 'today' ? 'active' : 'btn-light' }}">Today</a>
                        <a href="{{ route('reports.transactions', array_merge(request()->except(['quick_date', 'date_from', 'date_to']), ['quick_date' => 'this_week'])) }}"
                           class="btn btn-sm quick-pill {{ $quickDate === 'this_week' ? 'active' : 'btn-light' }}">This Week</a>
                        <a href="{{ route('reports.transactions', array_merge(request()->except(['quick_date', 'date_from', 'date_to']), ['quick_date' => 'this_month'])) }}"
                           class="btn btn-sm quick-pill {{ $quickDate === 'this_month' ? 'active' : 'btn-light' }}">This Month</a>
                        <a href="{{ route('reports.transactions', array_merge(request()->except(['quick_date', 'date_from', 'date_to']), ['quick_date' => 'last_month'])) }}"
                           class="btn btn-sm quick-pill {{ $quickDate === 'last_month' ? 'active' : 'btn-light' }}">Last Month</a>
                        <a href="{{ route('reports.transactions', array_merge(request()->except(['quick_date', 'date_from', 'date_to']), ['quick_date' => 'this_year'])) }}"
                           class="btn btn-sm quick-pill {{ $quickDate === 'this_year' ? 'active' : 'btn-light' }}">This Year</a>
                    </div>

                    <form action="{{ route('reports.transactions') }}" method="GET">
                        <input type="hidden" name="quick_date" value="{{ $quickDate }}">
                        <div class="row g-2 align-items-end">
                            <!-- Flow (Inflow / Outflow) -->
                            <div class="col-6 col-md-2">
                                <label class="form-label fs-11 fw-bold text-muted mb-1">Flow Type</label>
                                <select name="flow" class="form-select form-select-sm">
                                    <option value="all" {{ $flow === 'all' ? 'selected' : '' }}>-- All (In & Out) --</option>
                                    <option value="inflow" {{ $flow === 'inflow' ? 'selected' : '' }}>🟢 Inflows (Receipts)</option>
                                    <option value="outflow" {{ $flow === 'outflow' ? 'selected' : '' }}>🔴 Outflows (Payments)</option>
                                </select>
                            </div>

                            <!-- Category -->
                            <div class="col-6 col-md-2">
                                <label class="form-label fs-11 fw-bold text-muted mb-1">Category</label>
                                <select name="category" class="form-select form-select-sm">
                                    <option value="all" {{ $category === 'all' ? 'selected' : '' }}>-- All Categories --</option>
                                    <option value="booking_payment" {{ $category === 'booking_payment' ? 'selected' : '' }}>Client Booking Payment</option>
                                    <option value="booking_advance" {{ $category === 'booking_advance' ? 'selected' : '' }}>Booking Advance Deposit</option>
                                    <option value="client_receipt" {{ $category === 'client_receipt' ? 'selected' : '' }}>Client General Receipt</option>
                                    <option value="supplier_payment" {{ $category === 'supplier_payment' ? 'selected' : '' }}>Supplier Payment</option>
                                    <option value="expense" {{ $category === 'expense' ? 'selected' : '' }}>Office / General Expense</option>
                                    <option value="staff_salary" {{ $category === 'staff_salary' ? 'selected' : '' }}>Staff Salary / Advance</option>
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div class="col-6 col-md-2">
                                <label class="form-label fs-11 fw-bold text-muted mb-1">Payment Method</label>
                                <select name="payment_method" class="form-select form-select-sm">
                                    <option value="all" {{ $paymentMethod === 'all' ? 'selected' : '' }}>-- All Methods --</option>
                                    <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ $paymentMethod === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="online" {{ $paymentMethod === 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="cheque" {{ $paymentMethod === 'cheque' ? 'selected' : '' }}>Cheque</option>
                                </select>
                            </div>

                            <!-- Client Filter -->
                            <div class="col-6 col-md-2">
                                <label class="form-label fs-11 fw-bold text-muted mb-1">Client</label>
                                <select name="client_id" class="form-select form-select-sm">
                                    <option value="">-- All Clients --</option>
                                    @foreach($clients as $c)
                                        <option value="{{ $c->id }}" {{ (string)$clientId === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date From -->
                            <div class="col-6 col-md-2">
                                <label class="form-label fs-11 fw-bold text-muted mb-1">Date From</label>
                                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                            </div>

                            <!-- Date To -->
                            <div class="col-6 col-md-2">
                                <label class="form-label fs-11 fw-bold text-muted mb-1">Date To</label>
                                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                            </div>

                            <!-- Search Keyword -->
                            <div class="col-12 col-md-4 mt-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search ref #, voucher, party name, narration...">
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12 col-md-8 mt-2 text-md-end">
                                <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">
                                    <i class="mdi mdi-filter-check me-1"></i> Apply Filters
                                </button>
                                <a href="{{ route('reports.transactions') }}" class="btn btn-light btn-sm px-3 border ms-1">
                                    <i class="mdi mdi-refresh me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PRINTABLE STATEMENT HEADER (Print Only) -->
            <div class="d-none d-print-block mb-3 p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0">DUA TRAVEL & TOURS</h3>
                        <p class="fs-12 text-muted mb-0">ALL TRANSACTIONS REPORT & AUDIT STATEMENT</p>
                    </div>
                    <div class="text-end fs-12">
                        <div><strong>Printed Date:</strong> {{ now()->format('d M, Y h:i A') }}</div>
                        @if($dateFrom || $dateTo)
                            <div><strong>Period:</strong> {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M, Y') : 'Start' }} to {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M, Y') : 'Today' }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- DATA TABLE -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="fs-14 fw-bold m-0 text-dark">
                        <i class="mdi mdi-format-list-bulleted text-primary me-1"></i> Transactions History
                    </h5>
                    <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill fs-12">
                        Showing {{ count($transactions) }} Records
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-12">
                            <thead class="table-light">
                                <tr>
                                    <th rowspan="2" class="ps-3 align-middle" style="width: 38px;">#</th>
                                    <th rowspan="2" class="align-middle">Date</th>
                                    <th rowspan="2" class="align-middle">Voucher / Ref #</th>
                                    <th rowspan="2" class="align-middle">Flow</th>
                                    <th rowspan="2" class="align-middle">Category</th>
                                    <th rowspan="2" class="align-middle">Party / Payee</th>
                                    <th rowspan="2" class="align-middle">Method</th>
                                    <th colspan="2" class="text-center bg-soft-success text-success border-bottom-0 py-1">PKR (Pakistani Rupee)</th>
                                    <th colspan="2" class="text-center bg-soft-warning text-dark border-bottom-0 py-1">SAR (Saudi Riyal)</th>
                                    <th rowspan="2" class="align-middle">Status</th>
                                    <th rowspan="2" class="no-print text-center align-middle" style="width: 60px;">Proof</th>
                                </tr>
                                <tr>
                                    <th class="text-end text-success py-1">Inflow (Cr)</th>
                                    <th class="text-end text-danger py-1">Outflow (Dr)</th>
                                    <th class="text-end text-success py-1">Inflow (Cr)</th>
                                    <th class="text-end text-danger py-1">Outflow (Dr)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $t)
                                    <tr>
                                        <td class="ps-3 text-muted fw-semibold">{{ $loop->iteration }}</td>
                                        <td class="fw-medium text-nowrap">
                                            {{ \Carbon\Carbon::parse($t['date'])->format('d M, Y') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark fw-bold border">{{ $t['voucher_no'] }}</span>
                                            @if($t['ref_no'] && $t['ref_no'] !== $t['voucher_no'])
                                                <small class="text-muted d-block">{{ $t['ref_no'] }}</small>
                                            @endif
                                            @if(!empty($t['currency']) && $t['currency'] === 'SAR')
                                                <small class="badge bg-soft-warning text-dark border border-warning border-opacity-25 mt-1">SAR @ ROE {{ number_format($t['exchange_rate'], 2) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $t['flow_badge'] }} px-2 py-1">
                                                {{ $t['flow'] === 'inflow' ? '🟢 Inflow (Cr)' : '🔴 Outflow (Dr)' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $t['category_label'] }}</span>
                                            <small class="text-muted d-block text-truncate" style="max-width: 250px;" title="{{ $t['description'] }}">
                                                {{ $t['description'] }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($t['party_link'])
                                                <a href="{{ $t['party_link'] }}" class="fw-semibold text-primary text-decoration-none">
                                                    {{ $t['party_name'] }}
                                                </a>
                                            @else
                                                <span class="fw-semibold text-dark">{{ $t['party_name'] }}</span>
                                            @endif
                                            <small class="text-muted d-block">{{ $t['party_type'] }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border px-2 py-1">
                                                {{ $t['payment_method'] }}
                                            </span>
                                        </td>
                                        {{-- PKR Inflow --}}
                                        <td class="text-end fw-bold {{ $t['inflow_pkr'] > 0 ? 'text-success' : 'text-muted' }}">
                                            {{ $t['inflow_pkr'] > 0 ? number_format($t['inflow_pkr'], 2) : '—' }}
                                        </td>
                                        {{-- PKR Outflow --}}
                                        <td class="text-end fw-bold {{ $t['outflow_pkr'] > 0 ? 'text-danger' : 'text-muted' }}">
                                            {{ $t['outflow_pkr'] > 0 ? number_format($t['outflow_pkr'], 2) : '—' }}
                                        </td>
                                        {{-- SAR Inflow --}}
                                        <td class="text-end fw-bold {{ $t['inflow_sar'] > 0 ? 'text-success' : 'text-muted' }}">
                                            {{ $t['inflow_sar'] > 0 ? number_format($t['inflow_sar'], 2) : '—' }}
                                        </td>
                                        {{-- SAR Outflow --}}
                                        <td class="text-end fw-bold {{ $t['outflow_sar'] > 0 ? 'text-danger' : 'text-muted' }}">
                                            {{ $t['outflow_sar'] > 0 ? number_format($t['outflow_sar'], 2) : '—' }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $t['status'] === 'confirmed' ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning' }} px-2 py-1">
                                                {{ ucfirst($t['status']) }}
                                            </span>
                                        </td>
                                        <td class="no-print text-center">
                                            @if($t['proof'])
                                                <a href="{{ $t['proof'] }}" target="_blank" class="btn btn-xs btn-outline-info" title="View Proof">
                                                    <i class="mdi mdi-file-document-outline"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center py-5 text-muted">
                                            <i class="mdi mdi-cash-remove fs-36 text-muted d-block mb-2"></i>
                                            <h6 class="fw-bold mb-1">No Transactions Found</h6>
                                            <p class="fs-12 mb-0">Try adjusting your filters or date range.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="7" class="text-end text-uppercase fs-12">Grand Totals:</td>
                                    <td class="text-end text-success fs-13">+ PKR {{ number_format($totalInflowPkr, 2) }}</td>
                                    <td class="text-end text-danger fs-13">- PKR {{ number_format($totalOutflowPkr, 2) }}</td>
                                    <td class="text-end text-success fs-13">+ SAR {{ number_format($totalInflowSar, 2) }}</td>
                                    <td class="text-end text-danger fs-13">- SAR {{ number_format($totalOutflowSar, 2) }}</td>
                                    <td colspan="2" class="text-end fs-12">
                                        <span class="{{ $netCashFlowPkr >= 0 ? 'text-success' : 'text-danger' }} d-block">
                                            Net PKR: {{ number_format($netCashFlowPkr, 2) }}
                                        </span>
                                        <span class="{{ $netCashFlowSar >= 0 ? 'text-success' : 'text-danger' }} d-block">
                                            Net SAR: {{ number_format($netCashFlowSar, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
