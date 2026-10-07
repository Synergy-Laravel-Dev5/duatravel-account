@extends('layout.master')

@section('title', 'Bank Transfer Report')

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
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .bank-pill-from {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    .bank-pill-to {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
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
                        <i class="mdi mdi-repeat text-primary me-1"></i> Bank Transfer Movement Report
                    </h4>
                    <span class="text-muted fs-12">Comprehensive audit log of internal bank-to-bank balance transfers.</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('bank-transfer.report.filter') }}" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-filter-variant me-1"></i> Modify Filter
                    </a>
                    <a href="{{ route('bank-transfer.create') }}" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-plus-circle me-1"></i> New Transfer
                    </a>
                    <a href="{{ route('bank-transfer.report.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm" target="_blank">
                        <i class="mdi mdi-file-pdf-box me-1"></i> Download PDF
                    </a>
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                        <i class="mdi mdi-printer me-1"></i> Print Report
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportTableToCSV('bank_transfers_report.csv')">
                        <i class="mdi mdi-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </div>

            {{-- Active Filter Bar (Collapsible / Summary) --}}
            <div class="card mb-3 border-0 shadow-sm no-print">
                <div class="card-body p-3 bg-light rounded-3">
                    <form action="{{ route('bank-transfer.report') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">Bank (All Activity)</label>
                            <select name="bank_id" class="form-select form-select-sm">
                                <option value="">-- All Banks --</option>
                                @foreach ($banks as $b)
                                    <option value="{{ $b->id }}" {{ request('bank_id') == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }} {{ !empty($b->account_number) ? '(' . $b->account_number . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">From Bank</label>
                            <select name="from_bank_id" class="form-select form-select-sm">
                                <option value="">-- Source --</option>
                                @foreach ($banks as $b)
                                    <option value="{{ $b->id }}" {{ request('from_bank_id') == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fs-11 fw-bold text-muted text-uppercase mb-1">To Bank</label>
                            <select name="to_bank_id" class="form-select form-select-sm">
                                <option value="">-- Destination --</option>
                                @foreach ($banks as $b)
                                    <option value="{{ $b->id }}" {{ request('to_bank_id') == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
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

            {{-- 4 Metric Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="bt-stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bt-icon-box bg-soft-primary text-primary">
                                <i class="ri-repeat-line"></i>
                            </div>
                            <div>
                                <div class="text-muted fs-11 fw-bold text-uppercase">Total Transfers</div>
                                <div class="fs-20 fw-bold text-dark">{{ number_format($totalCount) }}</div>
                                <div class="fs-11 text-muted">Transactions logged</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bt-stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bt-icon-box bg-soft-success text-success">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                            <div>
                                <div class="text-muted fs-11 fw-bold text-uppercase">Total Amount Transferred</div>
                                <div class="fs-20 fw-bold text-success">PKR {{ number_format($totalAmount, 2) }}</div>
                                <div class="fs-11 text-muted">Total balance shifted</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="bt-stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bt-icon-box bg-soft-info text-info">
                                <i class="ri-bank-line"></i>
                            </div>
                            <div>
                                <div class="text-muted fs-11 fw-bold text-uppercase">Active Participating Banks</div>
                                <div class="fs-20 fw-bold text-dark">{{ count($bankBreakdown) }} Bank(s)</div>
                                <div class="fs-11 text-muted">Involved in current filter period</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bank Movement Summary Matrix --}}
            @if(count($bankBreakdown) > 0)
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="fs-14 fw-bold m-0 text-dark">
                            <i class="mdi mdi-swap-horizontal text-primary me-1"></i> Bank-Wise Net Movement Summary
                        </h5>
                        <span class="badge bg-soft-info text-info fs-11">Breakdown by Bank Account</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover align-middle mb-0">
                                <thead class="table-light fs-12">
                                    <tr>
                                        <th>Bank Account</th>
                                        <th class="text-end text-danger">Total Outgoing (Debit)</th>
                                        <th class="text-end text-success">Total Incoming (Credit)</th>
                                        <th class="text-end">Net Movement</th>
                                        <th class="text-center">Activity Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bankBreakdown as $bData)
                                        <tr>
                                            <td>
                                                <strong>{{ $bData['bank']->name }}</strong>
                                                @if(!empty($bData['bank']->account_number))
                                                    <span class="text-muted fs-11 ms-1">({{ $bData['bank']->account_number }})</span>
                                                @endif
                                            </td>
                                            <td class="text-end text-danger fw-semibold">
                                                - PKR {{ number_format($bData['outgoing'], 2) }}
                                                <small class="text-muted d-block fs-10">({{ $bData['out_count'] }} transfers out)</small>
                                            </td>
                                            <td class="text-end text-success fw-semibold">
                                                + PKR {{ number_format($bData['incoming'], 2) }}
                                                <small class="text-muted d-block fs-10">({{ $bData['in_count'] }} transfers in)</small>
                                            </td>
                                            <td class="text-end fw-bold {{ $bData['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $bData['net'] >= 0 ? '+' : '' }} PKR {{ number_format($bData['net'], 2) }}
                                            </td>
                                            <td class="text-center">
                                                @if($bData['net'] > 0)
                                                    <span class="badge bg-soft-success text-success fs-10">Net Receiver</span>
                                                @elseif($bData['net'] < 0)
                                                    <span class="badge bg-soft-danger text-danger fs-10">Net Sender</span>
                                                @else
                                                    <span class="badge bg-soft-secondary text-secondary fs-10">Balanced</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Full Detail Transfers Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fs-14 fw-bold m-0 text-dark">
                        <i class="mdi mdi-format-list-bulleted text-primary me-1"></i> Detailed Transfer Transactions ({{ $transfers->count() }})
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
                            <thead class="table-light fs-12 text-uppercase">
                                <tr>
                                    <th style="width: 50px;">S#</th>
                                    <th>Transfer Date</th>
                                    <th>Source (From Bank)</th>
                                    <th class="text-center" style="width: 40px;"><i class="mdi mdi-arrow-right"></i></th>
                                    <th>Destination (To Bank)</th>
                                    <th class="text-end">Transferred Amount</th>
                                    <th>Description / Notes</th>
                                    <th class="text-center no-print" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $t)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold text-dark">
                                                {{ \Carbon\Carbon::parse($t->transfer_date)->format('d M Y') }}
                                            </span>
                                            <small class="text-muted d-block fs-10">{{ \Carbon\Carbon::parse($t->transfer_date)->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <span class="bank-pill bank-pill-from">
                                                <i class="mdi mdi-arrow-up-circle me-1"></i>
                                                {{ $t->fromBank->name ?? 'Deleted Bank' }}
                                            </span>
                                            @if(!empty($t->fromBank->account_number))
                                                <div class="fs-11 text-muted ms-2 mt-1">{{ $t->fromBank->account_number }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center text-primary fs-16">
                                            <i class="mdi mdi-arrow-right-thick"></i>
                                        </td>
                                        <td>
                                            <span class="bank-pill bank-pill-to">
                                                <i class="mdi mdi-arrow-down-circle me-1"></i>
                                                {{ $t->toBank->name ?? 'Deleted Bank' }}
                                            </span>
                                            @if(!empty($t->toBank->account_number))
                                                <div class="fs-11 text-muted ms-2 mt-1">{{ $t->toBank->account_number }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold fs-14 text-dark">
                                            PKR {{ number_format($t->amount, 2) }}
                                        </td>
                                        <td>
                                            {{ $t->description ?: '-' }}
                                        </td>
                                        <td class="text-center no-print">
                                            <form action="{{ route('bank-transfer.destroy', $t->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-xs" onclick="return confirm('Delete this transfer record?')" title="Delete Transfer">
                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-alert-circle-outline fs-24 d-block mb-1"></i>
                                            No bank transfer records found matching the specified filter criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($transfers->count() > 0)
                                <tfoot class="table-light fw-bold fs-13">
                                    <tr>
                                        <td colspan="5" class="text-end">Total Amount Transferred:</td>
                                        <td class="text-end text-success fs-15">PKR {{ number_format($totalAmount, 2) }}</td>
                                        <td colspan="2"></td>
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
                if (j === 3) continue; // skip arrow icon col
                let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").trim();
                text = text.replace(/"/g, '""');
                row.push('"' + text + '"');
            }
            if (row.length > 0) csv.push(row.join(","));
        }

        const csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
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
