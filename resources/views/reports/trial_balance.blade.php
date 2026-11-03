@extends('layout.master')

@section('title', 'Trial Balance Worksheet')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">

<style>
    .tb-metric-box {
        border-radius: 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 16px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .tb-metric-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .tb-account-badge {
        font-family: monospace;
        font-weight: 700;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 6px;
    }
    .accounting-table th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        background-color: #f8fafc;
        border-bottom: 2px solid #cbd5e1 !important;
    }
    .accounting-table td {
        font-size: 13px;
        vertical-align: middle;
    }
    .double-underline {
        border-bottom: 3px double #334155 !important;
        border-top: 1px solid #334155 !important;
    }
    @media print {
        body * {
            visibility: hidden;
        }
        #printableTrialBalance, #printableTrialBalance * {
            visibility: visible;
        }
        #printableTrialBalance {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
            border: none !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid py-3">

            {{-- ════ Toolbar & Filter Bar ════ --}}
            <div class="card border-0 shadow-sm mb-4 no-print">
                <div class="card-body p-3">
                    <form action="{{ route('reports.trial-balance') }}" method="GET" id="tbFilterForm" class="row g-2 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label fs-12 fw-semibold text-muted mb-1">Operations Sector</label>
                            <select name="package_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all" {{ $packageType === 'all' ? 'selected' : '' }}>All Operations (Hajj & Umrah)</option>
                                <option value="hajj" {{ $packageType === 'hajj' ? 'selected' : '' }}>Hajj Operations</option>
                                <option value="umrah" {{ $packageType === 'umrah' ? 'selected' : '' }}>Umrah Operations</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label fs-12 fw-semibold text-muted mb-1">As at Date</label>
                            <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="form-control form-control-sm" onchange="this.form.submit()">
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label fs-12 fw-semibold text-muted mb-1">SAR Conversion ROE</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted">1 SAR =</span>
                                <input type="number" step="0.01" name="roe" value="{{ $activeRoe }}" class="form-control form-control-sm" onchange="this.form.submit()">
                            </div>
                        </div>

                        <div class="col-12 col-md-5 d-flex justify-content-md-end gap-2 mt-2 mt-md-0 flex-wrap">
                            <button type="submit" class="btn btn-sm btn-primary px-3">
                                <i class="ri-filter-3-line me-1"></i> Apply
                            </button>
                            <button type="button" onclick="exportTrialBalanceCsv()" class="btn btn-sm btn-outline-success px-3">
                                <i class="ri-file-excel-line me-1"></i> Export Excel
                            </button>
                            <a href="{{ route('reports.trial-balance.pdf', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-danger px-3">
                                <i class="ri-file-pdf-line me-1"></i> Export PDF
                            </a>
                            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary px-3">
                                <i class="ri-printer-line me-1"></i> Print
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ════ Metric Equilibrium Cards ════ --}}
            <div class="row g-3 mb-4 no-print">
                <div class="col-12 col-md-4">
                    <div class="tb-metric-box border-start border-4 border-primary">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fs-12 fw-bold text-uppercase">Total Debit Balance</span>
                                <h3 class="fs-20 fw-bold text-dark my-1">
                                    PKR {{ number_format($totalDebit, 2) }}
                                </h3>
                                <span class="text-primary fs-12 fw-semibold">
                                    SAR {{ number_format($totalDebit / $activeRoe, 2) }}
                                </span>
                            </div>
                            <div class="bg-soft-primary p-2 rounded text-primary fs-24">
                                <i class="ri-arrow-down-circle-line"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="tb-metric-box border-start border-4 border-success">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fs-12 fw-bold text-uppercase">Total Credit Balance</span>
                                <h3 class="fs-20 fw-bold text-dark my-1">
                                    PKR {{ number_format($totalCredit, 2) }}
                                </h3>
                                <span class="text-success fs-12 fw-semibold">
                                    SAR {{ number_format($totalCredit / $activeRoe, 2) }}
                                </span>
                            </div>
                            <div class="bg-soft-success p-2 rounded text-success fs-24">
                                <i class="ri-arrow-up-circle-line"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="tb-metric-box border-start border-4 {{ $isBalanced ? 'border-info' : 'border-danger' }}">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fs-12 fw-bold text-uppercase">Equilibrium Status</span>
                                <h4 class="my-1">
                                    @if($isBalanced)
                                        <span class="badge bg-success-subtle text-success fs-13 border border-success px-2 py-1">
                                            <i class="ri-checkbox-circle-line me-1"></i> PERFECTLY BALANCED
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger fs-13 border border-danger px-2 py-1">
                                            <i class="ri-error-warning-line me-1"></i> Discrepancy: PKR {{ number_format(abs($totalDebit - $totalCredit), 2) }}
                                        </span>
                                    @endif
                                </h4>
                                <span class="text-muted fs-11">
                                    Assets + Expenses = Liabilities + Equity + Revenue
                                </span>
                            </div>
                            <div class="bg-soft-info p-2 rounded text-info fs-24">
                                <i class="ri-scales-3-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════ Printable Trial Balance Document ════ --}}
            <div class="card border shadow-sm" id="printableTrialBalance" style="background:#fff; border-radius:12px;">
                <div class="card-body p-4 p-md-5">

                    {{-- Document Header --}}
                    <div class="text-center pb-4 mb-4 border-bottom">
                        <h4 class="text-uppercase fw-bold text-dark mb-1" style="letter-spacing:1px; font-family:'Playfair Display', serif;">
                            DUA TRAVELS & TOURS (PVT) LTD
                        </h4>
                        <h3 class="fw-bold mb-1" style="color:#0f535e; letter-spacing:1.5px;">
                            TRIAL BALANCE WORKSHEET
                        </h3>
                        <p class="text-muted fs-13 mb-0">
                            As at <strong>{{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</strong>
                            <span class="badge bg-light text-dark border ms-2">Dual Currency: PKR & SAR (ROE: {{ number_format($activeRoe, 2) }})</span>
                        </p>
                    </div>

                    {{-- Search & Counter Strip --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
                        <div class="text-muted fs-12">
                            Showing <strong>{{ count($accounts) }}</strong> General Ledger Accounts
                        </div>
                        <div style="max-width: 280px;">
                            <input type="text" id="tbTableSearch" class="form-control form-control-sm" placeholder="Search account or code..." onkeyup="filterTbTable()">
                        </div>
                    </div>

                    {{-- Detailed Accounts Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover accounting-table align-middle" id="trialBalanceTable">
                            <thead>
                                <tr>
                                    <th style="width: 120px;" class="text-center">Code</th>
                                    <th>Account Title</th>
                                    <th style="width: 140px;" class="text-center">Category</th>
                                    <th style="width: 140px;" class="text-end bg-soft-primary">Debit (PKR)</th>
                                    <th style="width: 140px;" class="text-end bg-soft-success">Credit (PKR)</th>
                                    <th style="width: 130px;" class="text-end text-muted">Debit (SAR)</th>
                                    <th style="width: 130px;" class="text-end text-muted">Credit (SAR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $acc)
                                    @php
                                        $catColor = match($acc['category']) {
                                            'Asset'     => 'primary',
                                            'Liability' => 'warning',
                                            'Equity'    => 'dark',
                                            'Revenue'   => 'success',
                                            'COGS'      => 'info',
                                            'Expense'   => 'danger',
                                            default     => 'secondary'
                                        };
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <span class="tb-account-badge bg-light text-dark border">{{ $acc['code'] }}</span>
                                        </td>
                                        <td class="fw-semibold text-dark">
                                            {{ $acc['name'] }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $catColor }}-subtle text-{{ $catColor }} border border-{{ $catColor }} px-2 py-1 fs-11">
                                                {{ $acc['category'] }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-semibold text-dark">
                                            {{ $acc['debit'] > 0 ? number_format($acc['debit'], 2) : '-' }}
                                        </td>
                                        <td class="text-end fw-semibold text-dark">
                                            {{ $acc['credit'] > 0 ? number_format($acc['credit'], 2) : '-' }}
                                        </td>
                                        <td class="text-end text-muted fs-12">
                                            {{ $acc['debit_sar'] > 0 ? number_format($acc['debit_sar'], 2) : '-' }}
                                        </td>
                                        <td class="text-end text-muted fs-12">
                                            {{ $acc['credit_sar'] > 0 ? number_format($acc['credit_sar'], 2) : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No accounts found for the specified period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="double-underline bg-light fw-bold fs-14">
                                    <td colspan="3" class="text-uppercase text-end pe-3">Total General Ledger:</td>
                                    <td class="text-end text-primary fw-bold">{{ number_format($totalDebit, 2) }}</td>
                                    <td class="text-end text-success fw-bold">{{ number_format($totalCredit, 2) }}</td>
                                    <td class="text-end text-muted fs-12">{{ number_format($totalDebit / $activeRoe, 2) }}</td>
                                    <td class="text-end text-muted fs-12">{{ number_format($totalCredit / $activeRoe, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Auditor / Sign-off Block --}}
                    <div class="row pt-5 mt-4 text-center">
                        <div class="col-4">
                            <div class="border-top border-dark pt-2 mx-4 text-muted fs-12">
                                Prepared By (Accountant)
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-top border-dark pt-2 mx-4 text-muted fs-12">
                                Verified By (Internal Audit)
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-top border-dark pt-2 mx-4 text-muted fs-12">
                                Authorized Signatory
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function filterTbTable() {
        const query = document.getElementById('tbTableSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#trialBalanceTable tbody tr');
        rows.forEach(r => {
            const text = r.innerText.toLowerCase();
            r.style.display = text.includes(query) ? '' : 'none';
        });
    }

    function exportTrialBalanceCsv() {
        let csv = [];
        const rows = document.querySelectorAll('#trialBalanceTable tr');
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll('td, th');
            for (let j = 0; j < cols.length; j++) {
                let cellText = cols[j].innerText.replace(/,/g, '').replace(/\n/g, ' ').trim();
                row.push('"' + cellText + '"');
            }
            csv.push(row.join(','));
        }
        let csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
        let downloadLink = document.createElement('a');
        downloadLink.download = 'Trial_Balance_{{ $asOfDate }}.csv';
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
</script>
@endsection
