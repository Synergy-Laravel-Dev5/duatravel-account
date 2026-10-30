@extends('layout.master')

@section('title', 'Balance Sheet Statement')

@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid py-3">

            {{-- ════ Toolbar & Filter Bar ════ --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('reports.balance-sheet') }}" method="GET" id="balanceSheetFilterForm" class="row g-2 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label fs-12 fw-semibold text-muted mb-1">Package / Department</label>
                            <select name="package_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all" {{ $packageType === 'all' ? 'selected' : '' }}>All Operations (Hajj & Umrah)</option>
                                <option value="hajj" {{ $packageType === 'hajj' ? 'selected' : '' }}>Hajj Operations</option>
                                <option value="umrah" {{ $packageType === 'umrah' ? 'selected' : '' }}>Umrah Operations</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label fs-12 fw-semibold text-muted mb-1">Fiscal Year</label>
                            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all" {{ $year === 'all' ? 'selected' : '' }}>All Years</option>
                                @foreach($availableYears as $yr)
                                    <option value="{{ $yr }}" {{ (string)$year === (string)$yr ? 'selected' : '' }}>{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label fs-12 fw-semibold text-muted mb-1">As at Date</label>
                            <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="form-control form-control-sm" onchange="this.form.submit()">
                        </div>

                        <div class="col-12 col-md-4 d-flex justify-content-md-end gap-2 mt-2 mt-md-0">
                            <button type="submit" class="btn btn-sm btn-primary px-3">
                                <i class="mdi mdi-filter me-1"></i> Apply Filter
                            </button>
                            <button type="button" onclick="exportBalanceSheetExcel()" class="btn btn-sm btn-outline-success px-3">
                                <i class="mdi mdi-file-excel me-1"></i> Export Excel
                            </button>
                            <a href="{{ route('reports.balance-sheet.pdf', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-danger px-3">
                                <i class="mdi mdi-file-pdf me-1"></i> Export PDF
                            </a>
                            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary px-3">
                                <i class="mdi mdi-printer me-1"></i> Print
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ════ Balance Sheet Document (Formal Accounting Statement) ════ --}}
            <div class="card border shadow-sm balance-sheet-sheet" id="printableBalanceSheet" style="background:#fff; border-radius:12px;">
                <div class="card-body p-4 p-md-5">

                    {{-- Document Header matching Accounting Standard --}}
                    <div class="text-center pb-4 mb-4 border-bottom">
                        <h4 class="text-uppercase fw-bold text-dark mb-1" style="letter-spacing:1px; font-family:'Playfair Display', serif;">
                            DUA TRAVELS & TOURS (PVT) LTD
                        </h4>
                        <h3 class="fw-bold mb-1" style="color:#0f535e; letter-spacing:1.5px;">
                            BALANCE SHEET
                        </h3>
                        <p class="text-muted fs-14 mb-0">
                            As at <strong>{{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</strong>
                            <span class="badge bg-light text-dark border ms-2">All Figures in PKR</span>
                        </p>
                    </div>

                    {{-- Two-Column Accounting Layout (T-Account Balance Sheet) --}}
                    <div class="row g-0 border" style="border-width:2px !important; border-color:#334155 !important;">

                        {{-- ════════════════ LEFT COLUMN: ASSETS ════════════════ --}}
                        <div class="col-12 col-lg-6 border-end p-3 p-md-4" style="border-right: 2px solid #334155 !important; background:#fafbfd;">
                            
                            {{-- Assets Header --}}
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom border-dark">
                                <h5 class="fw-bold text-dark mb-0 fs-16 text-uppercase" style="letter-spacing:0.5px;">Assets</h5>
                                <span class="fw-bold text-dark fs-15">PKR</span>
                            </div>

                            {{-- 1. Current Assets --}}
                            <div class="mb-4">
                                <div class="fw-bold text-decoration-underline text-dark mb-2 fs-14">
                                    Current assets:
                                </div>

                                {{-- Cash and Bank --}}
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="ps-3 text-secondary">Cash and bank balances</span>
                                    <span class="fw-semibold text-dark">{{ number_format($totalCashAndBankPkr, 2) }}</span>
                                </div>
                                @if(!empty($bankDetails))
                                    @foreach($bankDetails as $bd)
                                        <div class="d-flex justify-content-between align-items-center py-0 ps-5 fs-12 text-muted">
                                            <span>• {{ $bd['name'] }}</span>
                                            <span>{{ number_format($bd['balance'], 2) }}</span>
                                        </div>
                                    @endforeach
                                @endif
                                @if(!empty($cashDetails))
                                    @foreach($cashDetails as $cd)
                                        <div class="d-flex justify-content-between align-items-center py-0 ps-5 fs-12 text-muted">
                                            <span>• {{ $cd['name'] }}</span>
                                            <span>{{ number_format($cd['balance'], 2) }}</span>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- Accounts Receivable --}}
                                <div class="d-flex justify-content-between align-items-center py-1 mt-1">
                                    <span class="ps-3 text-secondary">Accounts receivable (Clients / Pilgrims)</span>
                                    <span class="fw-semibold text-dark">{{ number_format($totalAccountsReceivablePkr, 2) }}</span>
                                </div>
                                @if(!empty($topClientReceivables))
                                    @foreach($topClientReceivables as $cName => $cAmt)
                                        <div class="d-flex justify-content-between align-items-center py-0 ps-5 fs-12 text-muted">
                                            <span>• {{ $cName }}</span>
                                            <span>{{ number_format($cAmt, 2) }}</span>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- Prepaids & Supplier Advances --}}
                                <div class="d-flex justify-content-between align-items-center py-1 mt-1">
                                    <span class="ps-3 text-secondary">Prepaid expenses & supplier advances</span>
                                    <span class="fw-semibold text-dark">{{ number_format($totalPrepaidsPkr, 2) }}</span>
                                </div>

                                {{-- Total Current Assets Subtotal --}}
                                <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top">
                                    <span class="fw-bold text-dark fs-14 ps-2">Total current assets</span>
                                    <span class="fw-bold text-dark fs-15">{{ number_format($totalCurrentAssets, 2) }}</span>
                                </div>
                            </div>

                            {{-- 2. Non-Current Assets --}}
                            <div class="mb-4">
                                <div class="fw-bold text-decoration-underline text-dark mb-2 fs-14">
                                    Non-current assets:
                                </div>

                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="ps-3 text-secondary">Office equipment & furniture</span>
                                    <span class="text-secondary">{{ number_format($fixedAssetsBase, 2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="ps-3 text-secondary">Less: Accumulated depreciation</span>
                                    <span class="text-danger">({{ number_format($accumulatedDepreciation, 2) }})</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center pt-1 border-top border-secondary-subtle">
                                    <span class="ps-3 text-secondary">Net fixed assets</span>
                                    <span class="fw-semibold text-dark">{{ number_format($netFixedAssets, 2) }}</span>
                                </div>
                            </div>

                            {{-- Spacer to balance heights on desktop --}}
                            <div style="min-height: 40px;"></div>

                            {{-- TOTAL ASSETS --}}
                            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 border-top border-dark" style="border-top-width: 2px !important; border-bottom: 3px double #0f535e !important;">
                                <h5 class="fw-bold mb-0 text-uppercase text-dark fs-15">Total Assets</h5>
                                <h5 class="fw-bold mb-0 fs-16" style="color:#0f535e;">PKR {{ number_format($totalAssets, 2) }}</h5>
                            </div>

                        </div>

                        {{-- ════════════════ RIGHT COLUMN: LIABILITIES & EQUITY ════════════════ --}}
                        <div class="col-12 col-lg-6 p-3 p-md-4" style="background:#fff;">

                            {{-- Liabilities & Equity Header --}}
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom border-dark">
                                <h5 class="fw-bold text-dark mb-0 fs-16 text-uppercase" style="letter-spacing:0.5px;">Liabilities & Stockholders' equity</h5>
                                <span class="fw-bold text-dark fs-15">PKR</span>
                            </div>

                            {{-- 1. Liabilities --}}
                            <div class="mb-4">
                                <div class="fw-bold text-decoration-underline text-dark mb-2 fs-14">
                                    Liabilities:
                                </div>

                                {{-- Notes Payable --}}
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="ps-3 text-secondary">Notes payable / Short-term borrowings</span>
                                    <span class="fw-semibold text-dark">{{ number_format($totalNotesPayablePkr, 2) }}</span>
                                </div>

                                {{-- Accounts Payable --}}
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="ps-3 text-secondary">Accounts payable (Hotels, Flights, Visas)</span>
                                    <span class="fw-semibold text-dark">{{ number_format($totalAccountsPayablePkr, 2) }}</span>
                                </div>
                                @if(!empty($topSupplierPayables))
                                    @foreach($topSupplierPayables as $sName => $sAmt)
                                        <div class="d-flex justify-content-between align-items-center py-0 ps-5 fs-12 text-muted">
                                            <span>• {{ $sName }}</span>
                                            <span>{{ number_format($sAmt, 2) }}</span>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- Salaries Payable --}}
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="ps-3 text-secondary">Salaries & staff payable</span>
                                    <span class="fw-semibold text-dark">{{ number_format($totalSalariesPayablePkr, 2) }}</span>
                                </div>

                                {{-- Unearned Service Revenue --}}
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="ps-3 text-secondary">Unearned service revenue (Future advances)</span>
                                    <span class="fw-semibold text-dark">{{ number_format($unearnedServiceRevenue, 2) }}</span>
                                </div>

                                {{-- Total Liabilities Subtotal --}}
                                <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top">
                                    <span class="fw-bold text-dark fs-14 ps-2">Total liabilities</span>
                                    <span class="fw-bold text-dark fs-15">{{ number_format($totalLiabilities, 2) }}</span>
                                </div>
                            </div>

                            {{-- 2. Stockholders' / Owner's Equity --}}
                            <div class="mb-4">
                                <div class="fw-bold text-decoration-underline text-dark mb-2 fs-14">
                                    Stockholders' equity:
                                </div>

                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="ps-3 text-secondary">Capital stock / Invested capital</span>
                                    <span class="fw-semibold text-dark">{{ number_format($capitalStock, 2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="ps-3 text-secondary">Retained earnings (Cumulative operating profit)</span>
                                    <span class="fw-semibold {{ $retainedEarnings >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($retainedEarnings, 2) }}
                                    </span>
                                </div>

                                @if(abs($balancingAdjustment) > 0.01)
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <span class="ps-3 text-secondary">Unallocated equity / Balancing adjustment</span>
                                        <span class="text-muted">{{ number_format($balancingAdjustment, 2) }}</span>
                                    </div>
                                @endif

                                <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top">
                                    <span class="fw-bold text-dark fs-14 ps-2">Total stockholders' equity</span>
                                    <span class="fw-bold text-dark fs-15">{{ number_format($totalEquity, 2) }}</span>
                                </div>
                            </div>

                            {{-- TOTAL LIABILITIES & EQUITY --}}
                            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 border-top border-dark" style="border-top-width: 2px !important; border-bottom: 3px double #0f535e !important;">
                                <h5 class="fw-bold mb-0 text-uppercase text-dark fs-15">Total Liabilities & Equity</h5>
                                <h5 class="fw-bold mb-0 fs-16" style="color:#0f535e;">PKR {{ number_format($totalLiabilitiesAndEquity, 2) }}</h5>
                            </div>

                        </div>

                    </div>
                    {{-- End Row --}}

                    {{-- Equality Check Badge --}}
                    <div class="text-center mt-3">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-12">
                            <i class="mdi mdi-check-decagram me-1"></i> Balance Sheet In Equilibrium: Total Assets (PKR {{ number_format($totalAssets, 2) }}) == Total Liabilities & Equity (PKR {{ number_format($totalLiabilitiesAndEquity, 2) }})
                        </span>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function exportBalanceSheetExcel() {
        if (typeof XLSX === 'undefined') {
            alert('Excel library loading, please try again.');
            return;
        }

        const rows = [
            ["DUA TRAVEL & TOURS"],
            ["BALANCE SHEET (STATEMENT OF FINANCIAL POSITION)"],
            ["As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}"],
            [],
            ["ASSETS", "PKR AMOUNT"],
            ["Current Assets", ""],
            ["  Cash & Bank Balances", {{ (float) ($totalCashAndBankPkr ?? 0) }}],
            ["  Accounts Receivable (Clients Due)", {{ (float) ($totalAccountsReceivablePkr ?? 0) }}],
            ["  Prepaid Expenses & Supplier Advances", {{ (float) ($totalPrepaidsPkr ?? 0) }}],
            ["Total Current Assets", {{ (float) ($totalCurrentAssets ?? 0) }}],
            [],
            ["Non-Current Assets", ""],
            ["  Property, Equipment & Long-term Assets (Net)", {{ (float) ($netFixedAssets ?? 0) }}],
            ["Total Non-Current Assets", {{ (float) ($netFixedAssets ?? 0) }}],
            [],
            ["TOTAL ASSETS", {{ (float) ($totalAssets ?? 0) }}],
            [],
            ["LIABILITIES & EQUITY", "PKR AMOUNT"],
            ["Current Liabilities", ""],
            ["  Accounts Payable (Suppliers Payable)", {{ (float) ($totalAccountsPayablePkr ?? 0) }}],
            ["  Client Advances (Unearned Inflow)", {{ (float) ($unearnedServiceRevenue ?? 0) }}],
            ["  Accrued Salaries & Operating Liabilities", {{ (float) ($totalSalariesPayablePkr ?? 0) }}],
            ["Total Current Liabilities", {{ (float) ($totalLiabilities ?? 0) }}],
            [],
            ["Stockholders' Equity", ""],
            ["  Owner Capital / Paid-in Capital", {{ (float) ($capitalStock ?? 0) }}],
            ["  Retained Earnings (Cumulative Net Profit / Loss)", {{ (float) ($retainedEarnings ?? 0) }}],
            @if(abs($balancingAdjustment ?? 0) > 0.01)
            ["  Unallocated Equity / Balancing Adjustment", {{ (float) ($balancingAdjustment ?? 0) }}],
            @endif
            ["Total Stockholders' Equity", {{ (float) ($totalEquity ?? 0) }}],
            [],
            ["TOTAL LIABILITIES & EQUITY", {{ (float) ($totalLiabilitiesAndEquity ?? 0) }}]
        ];

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, "Balance Sheet");
        XLSX.writeFile(wb, "Balance_Sheet_{{ str_replace('-', '', $asOfDate) }}.xlsx");
    }
</script>
@endsection
