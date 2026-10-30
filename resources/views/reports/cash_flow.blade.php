@extends('layout.master')

@section('title', 'Cash Flow Statement')

@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid py-3">

            {{-- ════ Toolbar & Filter Bar ════ --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('reports.cash-flow') }}" method="GET" id="cashFlowFilterForm" class="row g-2 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label fs-12 fw-semibold text-muted mb-1">Package / Department</label>
                            <select name="package_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all" {{ $packageType === 'all' ? 'selected' : '' }}>All Operations (Hajj & Umrah)</option>
                                <option value="hajj" {{ $packageType === 'hajj' ? 'selected' : '' }}>Hajj Operations</option>
                                <option value="umrah" {{ $packageType === 'umrah' ? 'selected' : '' }}>Umrah Operations</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label fs-12 fw-semibold text-muted mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm" onchange="this.form.submit()">
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label fs-12 fw-semibold text-muted mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm" onchange="this.form.submit()">
                        </div>

                        <div class="col-12 col-md-5 d-flex justify-content-md-end gap-2 mt-2 mt-md-0">
                            <button type="submit" class="btn btn-sm btn-primary px-3">
                                <i class="mdi mdi-filter me-1"></i> Apply Filter
                            </button>
                            <button type="button" onclick="exportCashFlowExcel()" class="btn btn-sm btn-outline-success px-3">
                                <i class="mdi mdi-file-excel me-1"></i> Export Excel
                            </button>
                            <a href="{{ route('reports.cash-flow.pdf', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-danger px-3">
                                <i class="mdi mdi-file-pdf me-1"></i> Export PDF
                            </a>
                            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary px-3">
                                <i class="mdi mdi-printer me-1"></i> Print
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ════ Cash Flow Document (Matching Standard Image) ════ --}}
            <div class="card border shadow-sm mx-auto" style="max-width:900px; background:#fff; border-radius:12px;">
                <div class="card-body p-4 p-md-5">

                    {{-- Document Header matching accounting image --}}
                    <div class="text-center pb-3 mb-4 border-bottom">
                        <h4 class="text-uppercase fw-bold text-dark mb-1" style="letter-spacing:1px; font-family:'Playfair Display', serif;">
                            Cash Flow Statement Dua Travels & Tours
                        </h4>
                        <h5 class="fw-semibold text-muted mb-1">
                            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                        </h5>
                        <small class="text-muted fs-12">All Figures in PKR</small>
                    </div>

                    {{-- Accounting Cash Flow Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" style="border: 2px solid #334155;">
                            <tbody>
                                {{-- 1. Cash Flow From Operations Header --}}
                                <tr style="background:#f8fafc;">
                                    <td colspan="2" class="fw-bold text-dark fs-15 py-2">
                                        Cash Flow From Operations
                                    </td>
                                    <td class="text-end fw-bold text-dark fs-14 py-2" style="width:200px;"></td>
                                </tr>

                                {{-- Net Earnings --}}
                                <tr>
                                    <td colspan="2" class="ps-4 text-dark fs-14">Net Earnings / Net Profit</td>
                                    <td class="text-end fw-semibold {{ $netEarnings >= 0 ? 'text-dark' : 'text-danger' }} fs-14">
                                        {{ $netEarnings >= 0 ? number_format($netEarnings, 2) : '(' . number_format(abs($netEarnings), 2) . ')' }}
                                    </td>
                                </tr>

                                {{-- Additions to Cash --}}
                                <tr>
                                    <td colspan="3" class="ps-4 fw-semibold fst-italic text-secondary fs-13 py-1" style="background:#fafafa;">
                                        Additions to Cash
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="ps-5 text-muted fs-13">Depreciations</td>
                                    <td class="text-end text-dark fs-13">{{ number_format($depreciation, 2) }}</td>
                                </tr>
                                @if($decreaseInReceivables > 0)
                                <tr>
                                    <td colspan="2" class="ps-5 text-muted fs-13">Decrease in Accounts Receivable</td>
                                    <td class="text-end text-dark fs-13">{{ number_format($decreaseInReceivables, 2) }}</td>
                                </tr>
                                @endif
                                @if($increaseInPayables > 0)
                                <tr>
                                    <td colspan="2" class="ps-5 text-muted fs-13">Increase in Accounts Payable</td>
                                    <td class="text-end text-dark fs-13">{{ number_format($increaseInPayables, 2) }}</td>
                                </tr>
                                @endif
                                @if(($increaseInTaxes ?? 0) > 0)
                                <tr>
                                    <td colspan="2" class="ps-5 text-muted fs-13">Increase in Taxes / Accrued Payables</td>
                                    <td class="text-end text-dark fs-13">{{ number_format($increaseInTaxes, 2) }}</td>
                                </tr>
                                @endif
                                @if(($decreaseInAdvances ?? 0) > 0)
                                <tr>
                                    <td colspan="2" class="ps-5 text-muted fs-13">Decrease in Supplier Advances / Prepaids</td>
                                    <td class="text-end text-dark fs-13">{{ number_format($decreaseInAdvances, 2) }}</td>
                                </tr>
                                @endif
                                @if($depreciation == 0 && $decreaseInReceivables == 0 && $increaseInPayables == 0 && ($increaseInTaxes ?? 0) == 0 && ($decreaseInAdvances ?? 0) == 0)
                                <tr>
                                    <td colspan="2" class="ps-5 text-muted fs-13">Other Non-cash / Working Capital Additions</td>
                                    <td class="text-end text-dark fs-13">0.00</td>
                                </tr>
                                @endif

                                {{-- Subtractions From Cash --}}
                                <tr>
                                    <td colspan="3" class="ps-4 fw-semibold fst-italic text-secondary fs-13 py-1" style="background:#fafafa;">
                                        Subtractions From Cash
                                    </td>
                                </tr>
                                @if($increaseInReceivables > 0)
                                <tr>
                                    <td colspan="2" class="ps-5 text-muted fs-13">Increase in Accounts Receivable (Uncollected Revenue)</td>
                                    <td class="text-end text-danger fs-13">({{ number_format($increaseInReceivables, 2) }})</td>
                                </tr>
                                @endif
                                @if($decreaseInPayables > 0)
                                <tr>
                                    <td colspan="2" class="ps-5 text-muted fs-13">Decrease in Accounts Payable (Supplier Dues Paid Down)</td>
                                    <td class="text-end text-danger fs-13">({{ number_format($decreaseInPayables, 2) }})</td>
                                </tr>
                                @endif
                                @if(($increaseInAdvances ?? 0) > 0)
                                <tr>
                                    <td colspan="2" class="ps-5 text-muted fs-13">Increase in Supplier Advances / Prepaids</td>
                                    <td class="text-end text-danger fs-13">({{ number_format($increaseInAdvances, 2) }})</td>
                                </tr>
                                @endif
                                @if($increaseInReceivables == 0 && $decreaseInPayables == 0 && ($increaseInAdvances ?? 0) == 0)
                                <tr>
                                    <td colspan="2" class="ps-5 text-muted fs-13">Other Working Capital Subtractions</td>
                                    <td class="text-end text-dark fs-13">0.00</td>
                                </tr>
                                @endif

                                {{-- Net Cash From Operations Total --}}
                                <tr style="background:#f1f5f9; border-top: 2px solid #64748b;">
                                    <td colspan="2" class="fw-bold text-dark fs-14 ps-3 text-decoration-underline">
                                        Net Cash From Operations
                                    </td>
                                    <td class="text-end fw-bold text-dark fs-14">
                                        {{ $netCashFromOperations >= 0 ? number_format($netCashFromOperations, 2) : '(' . number_format(abs($netCashFromOperations), 2) . ')' }}
                                    </td>
                                </tr>

                                {{-- 2. Cash Flow From Investing --}}
                                <tr style="background:#f8fafc; border-top: 2px solid #334155;">
                                    <td colspan="2" class="fw-bold text-dark fs-15 py-2">
                                        Cash Flow From Investing
                                    </td>
                                    <td class="text-end fw-bold text-dark fs-14 py-2"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="ps-4 text-muted fs-13">Office Equipment & Assets Purchased</td>
                                    <td class="text-end {{ $equipmentPurchased > 0 ? 'text-danger' : 'text-dark' }} fs-13">
                                        {{ $equipmentPurchased > 0 ? '(' . number_format($equipmentPurchased, 2) . ')' : '0.00' }}
                                    </td>
                                </tr>
                                <tr style="background:#f1f5f9; border-top: 1px solid #64748b;">
                                    <td colspan="2" class="fw-bold text-dark fs-14 ps-3 text-decoration-underline">
                                        Net Cash From Investing
                                    </td>
                                    <td class="text-end fw-bold text-dark fs-14">
                                        {{ $netCashFromInvesting >= 0 ? number_format($netCashFromInvesting, 2) : '(' . number_format(abs($netCashFromInvesting), 2) . ')' }}
                                    </td>
                                </tr>

                                {{-- 3. Cash Flow From Financing --}}
                                <tr style="background:#f8fafc; border-top: 2px solid #334155;">
                                    <td colspan="2" class="fw-bold text-dark fs-15 py-2">
                                        Cash Flow From Financing
                                    </td>
                                    <td class="text-end fw-bold text-dark fs-14 py-2"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="ps-4 text-muted fs-13">Owner Capital Injected / Equity Contributions</td>
                                    <td class="text-end text-dark fs-13">{{ number_format($capitalInjected, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="ps-4 text-muted fs-13">Notes Payable / Short-term Borrowings Received</td>
                                    <td class="text-end text-dark fs-13">{{ number_format($notesPayableBorrowings, 2) }}</td>
                                </tr>
                                @if($drawingsOrLoanRepayments > 0)
                                <tr>
                                    <td colspan="2" class="ps-4 text-muted fs-13">Loan Repayments / Owner Drawings</td>
                                    <td class="text-end text-danger fs-13">({{ number_format($drawingsOrLoanRepayments, 2) }})</td>
                                </tr>
                                @endif
                                <tr style="background:#f1f5f9; border-top: 1px solid #64748b;">
                                    <td colspan="2" class="fw-bold text-dark fs-14 ps-3 text-decoration-underline">
                                        Net Cash From Financing
                                    </td>
                                    <td class="text-end fw-bold text-dark fs-14">
                                        {{ $netCashFromFinancing >= 0 ? number_format($netCashFromFinancing, 2) : '(' . number_format(abs($netCashFromFinancing), 2) . ')' }}
                                    </td>
                                </tr>

                                {{-- 4. Net Cash Flow for the Period --}}
                                <tr style="border-top: 2.5px solid #0f535e; border-bottom: 3.5px double #0f535e; background:#e6f3f5;">
                                    <td colspan="2" class="fw-bold text-dark fs-15 py-3">
                                        Cash Flow for Period Ended {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                                    </td>
                                    <td class="text-end fw-bold fs-16 py-3" style="color:#0f535e;">
                                        PKR {{ number_format($netCashFlowPeriod, 2) }}
                                    </td>
                                </tr>

                                {{-- Reconciliation: Beginning & Ending Cash --}}
                                <tr class="table-light">
                                    <td colspan="2" class="ps-4 text-muted fs-13">Cash & bank balance at beginning of period</td>
                                    <td class="text-end fw-semibold text-secondary fs-13">{{ number_format($cashAtBeginning, 2) }}</td>
                                </tr>
                                <tr class="table-light border-bottom border-dark">
                                    <td colspan="2" class="ps-4 fw-bold text-dark fs-14">Cash & bank balance at end of period</td>
                                    <td class="text-end fw-bold text-dark fs-14">{{ number_format($cashAtEnd, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function exportCashFlowExcel() {
        if (typeof XLSX === 'undefined') {
            alert('Excel library loading, please try again.');
            return;
        }

        const rows = [
            ["DUA TRAVEL & TOURS"],
            ["STATEMENT OF CASH FLOWS"],
            ["Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}"],
            [],
            ["CASH FLOWS FROM OPERATING ACTIVITIES", "PKR AMOUNT"],
            ["  Net Earnings / Net Profit", {{ (float) ($netEarnings ?? 0) }}],
            ["  Depreciation", {{ (float) ($depreciation ?? 0) }}],
            ["  Decrease in Accounts Receivable", {{ (float) ($decreaseInReceivables ?? 0) }}],
            ["  Increase in Accounts Payable", {{ (float) ($increaseInPayables ?? 0) }}],
            ["  Increase in Accounts Receivable", {{ (float) (-($increaseInReceivables ?? 0)) }}],
            ["  Decrease in Accounts Payable", {{ (float) (-($decreaseInPayables ?? 0)) }}],
            ["Net Cash From Operations", {{ (float) ($netCashFromOperations ?? 0) }}],
            [],
            ["CASH FLOWS FROM INVESTING ACTIVITIES", "PKR AMOUNT"],
            ["  Equipment & Capital Assets Purchased", {{ (float) (-($equipmentPurchased ?? 0)) }}],
            ["Net Cash From Investing Activities", {{ (float) ($netCashFromInvesting ?? 0) }}],
            [],
            ["CASH FLOWS FROM FINANCING ACTIVITIES", "PKR AMOUNT"],
            ["  Capital Injected", {{ (float) ($capitalInjected ?? 0) }}],
            ["  Notes Payable Borrowings", {{ (float) ($notesPayableBorrowings ?? 0) }}],
            ["  Drawings / Loan Repayments", {{ (float) (-($drawingsOrLoanRepayments ?? 0)) }}],
            ["Net Cash From Financing Activities", {{ (float) ($netCashFromFinancing ?? 0) }}],
            [],
            ["NET CASH FLOW FOR PERIOD", {{ (float) ($netCashFlowPeriod ?? 0) }}],
            [],
            ["CASH RECONCILIATION", "PKR AMOUNT"],
            ["  Cash & bank balance at beginning of period", {{ (float) ($cashAtBeginning ?? 0) }}],
            ["  Cash & bank balance at end of period", {{ (float) ($cashAtEnd ?? 0) }}]
        ];

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, "Cash Flow");
        XLSX.writeFile(wb, "Cash_Flow_{{ str_replace('-', '', $dateFrom) }}_{{ str_replace('-', '', $dateTo) }}.xlsx");
    }
</script>
@endsection
