@extends('layout.master')

@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid py-3">
    <!-- Header -->
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h4 class="mb-0 text-dark fw-bold">
                <i class="mdi mdi-cash-refund text-danger me-2"></i>Refund Management
            </h4>
            <p class="text-muted small mb-0">Record and monitor Client and Supplier refunds with automatic double-entry ledger posting.</p>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
            <a href="{{ route('refunds.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="mdi mdi-plus-circle me-1"></i> New Refund Transaction
            </a>
            <a href="{{ route('reports.hub') }}" class="btn btn-outline-secondary btn-sm shadow-sm ms-1">
                <i class="mdi mdi-layers-outline me-1"></i> Reports Hub
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Client Refunds (Paid)</span>
                            <h4 class="fw-bold text-danger mb-0 mt-1">PKR {{ number_format($totalClientRefundsPkr, 2) }}</h4>
                            <small class="text-muted">SAR {{ number_format($totalClientRefundsSar, 2) }}</small>
                        </div>
                        <div class="avatar-sm rounded-circle bg-soft-danger d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-account-arrow-right text-danger fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Supplier Refunds (Received)</span>
                            <h4 class="fw-bold text-success mb-0 mt-1">PKR {{ number_format($totalSupplierRefundsPkr, 2) }}</h4>
                            <small class="text-muted">SAR {{ number_format($totalSupplierRefundsSar, 2) }}</small>
                        </div>
                        <div class="avatar-sm rounded-circle bg-soft-success d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-office-building-marker text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Net Refund Impact</span>
                            @php $netImpact = $totalSupplierRefundsPkr - $totalClientRefundsPkr; @endphp
                            <h4 class="fw-bold {{ $netImpact >= 0 ? 'text-success' : 'text-danger' }} mb-0 mt-1">
                                PKR {{ number_format($netImpact, 2) }}
                            </h4>
                            <small class="text-muted">Inflow vs Outflow Net</small>
                        </div>
                        <div class="avatar-sm rounded-circle bg-soft-info d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-scale-balance text-info fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Total Refund Records</span>
                            <h4 class="fw-bold text-primary mb-0 mt-1">{{ $refunds->total() }}</h4>
                            <small class="text-muted">Filtered transactions</small>
                        </div>
                        <div class="avatar-sm rounded-circle bg-soft-primary d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-receipt text-primary fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <form action="{{ route('refunds.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small mb-1 fw-bold text-muted">Refund Type</label>
                    <select name="refund_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ ($refundType ?? 'all') === 'all' ? 'selected' : '' }}>All Refunds (Client & Supplier)</option>
                        <option value="client" {{ ($refundType ?? '') === 'client' ? 'selected' : '' }}>Client Refunds Only</option>
                        <option value="supplier" {{ ($refundType ?? '') === 'supplier' ? 'selected' : '' }}>Supplier Refunds Only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1 fw-bold text-muted">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1 fw-bold text-muted">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1 fw-bold text-muted">Search Voucher / Party</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, voucher..." value="{{ $search ?? '' }}">
                </div>
                <div class="col-md-2 d-flex gap-1 align-self-end">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="mdi mdi-filter me-1"></i>Filter</button>
                    <a href="{{ route('refunds.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="mdi mdi-refresh"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Refund Transactions Listing Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th>Date</th>
                            <th>Voucher / Ref #</th>
                            <th>Type</th>
                            <th>Beneficiary / Party</th>
                            <th>Account / Channel</th>
                            <th>Service / Booking</th>
                            <th class="text-end">Original Amount</th>
                            <th class="text-end">Amount (PKR)</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($refunds as $i => $ref)
                            @php
                                $isClient = ($ref->refund_type === 'client');
                                $partyName = $isClient ? ($ref->client->name ?? 'Client #' . $ref->client_id) : ($ref->supplier->name ?? 'Supplier #' . $ref->supplier_id);
                                $ledgerUrl = $isClient 
                                    ? route('transaction.ledger.view', ['client_id' => $ref->client_id]) 
                                    : ($ref->supplier_id ? route('supplier.ledger.show', $ref->supplier_id) : '#');
                                $curr = strtoupper($ref->currency ?: 'PKR');
                                $roe = (float)($ref->exchange_rate ?: 1);
                                $amtPkr = (float)$ref->amount_pkr ?: (float)$ref->amount;
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $refunds->firstItem() + $i }}</td>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($ref->date)->format('d M, Y') }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">{{ $ref->reference_no ?: ('REF-' . $ref->id) }}</span>
                                </td>
                                <td>
                                    @if($isClient)
                                        <span class="badge bg-soft-danger text-danger border border-danger border-opacity-25 px-2 py-1">
                                            <i class="mdi mdi-account-arrow-right me-1"></i>Client Refund (Outflow)
                                        </span>
                                    @else
                                        <span class="badge bg-soft-success text-success border border-success border-opacity-25 px-2 py-1">
                                            <i class="mdi mdi-office-building-marker me-1"></i>Supplier Refund (Inflow)
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ $ledgerUrl }}" target="_blank" class="fw-bold {{ $isClient ? 'text-primary' : 'text-dark' }}" title="Open Ledger Statement">
                                        {{ $partyName }} <i class="mdi mdi-open-in-new fs-11 ms-1 text-muted"></i>
                                    </a>
                                    @if($ref->description)
                                        <small class="d-block text-muted text-truncate" style="max-width: 240px;">{{ $ref->description }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($ref->payment_type === 'bank' && $ref->bank)
                                        <span class="text-dark"><i class="mdi mdi-bank text-primary me-1"></i>{{ $ref->bank->name }}</span>
                                    @elseif($ref->account)
                                        <span class="text-dark"><i class="mdi mdi-cash-multiple text-success me-1"></i>{{ $ref->account->account_name }}</span>
                                    @else
                                        <span class="text-muted"><i class="mdi mdi-cash me-1"></i>Cash Account</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ref->booking_id)
                                        <a href="{{ route('booking.edit', $ref->booking_id) }}" target="_blank" class="badge bg-soft-info text-info border border-info border-opacity-25">
                                            Booking #{{ $ref->booking_id }}
                                        </a>
                                    @endif
                                    @if($ref->service_type)
                                        <span class="badge bg-light text-secondary border text-capitalize">{{ $ref->service_type }}</span>
                                    @endif
                                    @if(!$ref->booking_id && !$ref->service_type)
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold">{{ number_format($ref->amount, 2) }} {{ $curr }}</span>
                                    @if($curr !== 'PKR' && $roe > 1)
                                        <br><small class="text-muted">ROE: {{ number_format($roe, 2) }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong class="{{ $isClient ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($amtPkr, 2) }} PKR
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('refunds.destroy', $ref->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this refund transaction? Ledger balances will revert.');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete Refund">
                                            <i class="mdi mdi-delete-outline"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="mdi mdi-cash-remove fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No refund transactions found matching your criteria.
                                    <div class="mt-2">
                                        <a href="{{ route('refunds.create') }}" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-plus-circle me-1"></i> Record First Refund
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($refunds->hasPages())
                <div class="card-footer bg-white border-0 py-2 d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing {{ $refunds->firstItem() }} to {{ $refunds->lastItem() }} of {{ $refunds->total() }} entries</small>
                    <div>{{ $refunds->links('pagination::bootstrap-5') }}</div>
                </div>
            @endif
        </div>
    </div>
        </div>
    </div>
</div>
@endsection
