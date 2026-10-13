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
                                <div>
                                    <span class="text-muted fs-11 fw-bold text-uppercase d-block">Total Inflows (Receipts)</span>
                                    <h4 class="fw-bold text-success mt-1 mb-0 fs-18">PKR {{ number_format($totalInflow, 2) }}</h4>
                                    <small class="text-muted fs-11"><i class="mdi mdi-arrow-down-bold text-success"></i> {{ $inflowCount }} incoming receipts</small>
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
                                <div>
                                    <span class="text-muted fs-11 fw-bold text-uppercase d-block">Total Outflows (Payments)</span>
                                    <h4 class="fw-bold text-danger mt-1 mb-0 fs-18">PKR {{ number_format($totalOutflow, 2) }}</h4>
                                    <small class="text-muted fs-11"><i class="mdi mdi-arrow-up-bold text-danger"></i> {{ $outflowCount }} outgoing payments</small>
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
                    <div class="card shadow-sm trans-stat-card border-start border-4 {{ $netCashFlow >= 0 ? 'border-primary' : 'border-warning' }} bg-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="text-muted fs-11 fw-bold text-uppercase d-block">Net Cash Flow (Surplus)</span>
                                    <h4 class="fw-bold {{ $netCashFlow >= 0 ? 'text-primary' : 'text-danger' }} mt-1 mb-0 fs-18">
                                        PKR {{ number_format($netCashFlow, 2) }}
                                    </h4>
                                    <small class="text-muted fs-11">{{ $netCashFlow >= 0 ? 'Cash Surplus / Net Positive' : 'Deficit / Net Outflow' }}</small>
                                </div>
                                <div class="{{ $netCashFlow >= 0 ? 'bg-soft-primary text-primary' : 'bg-soft-warning text-warning' }} p-2 rounded-3">
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
                        <table class="table table-hover align-middle mb-0 fs-13">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 40px;">#</th>
                                    <th>Date</th>
                                    <th>Voucher / Ref #</th>
                                    <th>Flow</th>
                                    <th>Category</th>
                                    <th>Party / Payee</th>
                                    <th>Method</th>
                                    <th class="text-end text-success">Inflow (Cr) PKR</th>
                                    <th class="text-end text-danger">Outflow (Dr) PKR</th>
                                    <th>Status</th>
                                    <th class="no-print text-center" style="width: 70px;">Proof</th>
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
                                        </td>
                                        <td>
                                            <span class="badge {{ $t['flow_badge'] }} px-2 py-1">
                                                {{ $t['flow'] === 'inflow' ? '🟢 Inflow (Cr)' : '🔴 Outflow (Dr)' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $t['category_label'] }}</span>
                                            <small class="text-muted d-block text-truncate" style="max-width: 280px;" title="{{ $t['description'] }}">
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
                                        <td class="text-end fw-bold {{ $t['inflow'] > 0 ? 'text-success' : 'text-muted' }}">
                                            {{ $t['inflow'] > 0 ? number_format($t['inflow'], 2) : '—' }}
                                        </td>
                                        <td class="text-end fw-bold {{ $t['outflow'] > 0 ? 'text-danger' : 'text-muted' }}">
                                            {{ $t['outflow'] > 0 ? number_format($t['outflow'], 2) : '—' }}
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
                                        <td colspan="11" class="text-center py-5 text-muted">
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
                                    <td class="text-end text-success fs-14">PKR {{ number_format($totalInflow, 2) }}</td>
                                    <td class="text-end text-danger fs-14">PKR {{ number_format($totalOutflow, 2) }}</td>
                                    <td colspan="2" class="text-end {{ $netCashFlow >= 0 ? 'text-primary' : 'text-danger' }} fs-13">
                                        Net: PKR {{ number_format($netCashFlow, 2) }}
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
