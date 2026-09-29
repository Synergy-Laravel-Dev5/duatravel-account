@extends('layout.master')

@section('title', 'Expense Transaction Report')
@section('content')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">

    <style>
        /* ─── Badges ─────────────────────────────── */
        .et-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: .4px
        }

        .et-badge-cash {
            background: #dcfce7;
            color: #15803d
        }

        .et-badge-bank {
            background: #dbeafe;
            color: #1d4ed8
        }

        .et-badge-online {
            background: #fef9c3;
            color: #a16207
        }

        .et-badge-ewallet {
            background: #fae8ff;
            color: #7e22ce
        }

        .et-badge-hajj {
            background: #fff7ed;
            color: #c2410c
        }

        .et-badge-umrah {
            background: #eff6ff;
            color: #1d4ed8
        }

        .et-badge-pkr {
            background: #ecfeff;
            color: #0e7490
        }

        .et-badge-sar {
            background: #fff1f2;
            color: #be123c
        }

        /* ─── Stat cards ──────────────────────────── */
        .et-stat {
            border-radius: 14px;
            border: 1px solid #f1f5f9;
            background: #fff;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            transition: box-shadow .2s
        }

        .et-stat:hover {
            box-shadow: 0 4px 16px rgba(79, 70, 229, .12)
        }

        .et-stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0
        }

        .et-stat-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: .5px
        }

        .et-stat-value {
            font-size: 22px;
            font-weight: 800;
            color: #1e1b4b;
            line-height: 1
        }

        .et-stat-value .et-stat-multi {
            font-size: 16px;
            line-height: 1.4
        }

        /* ─── Table ───────────────────────────────── */
        .et-table thead th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: #94a3b8;
            font-weight: 700;
            border-bottom: 2px solid #f1f5f9;
            padding: 14px 12px;
            white-space: nowrap;
            background: #fafbff
        }

        .et-table thead th:first-child {
            padding-left: 24px
        }

        .et-table thead th:last-child {
            padding-right: 24px
        }

        .et-table tbody td {
            padding: 13px 12px;
            vertical-align: middle;
            font-size: 13.5px;
            color: #374151;
            border-bottom: 1px solid #f8fafc
        }

        .et-table tbody td:first-child {
            padding-left: 24px
        }

        .et-table tbody td:last-child {
            padding-right: 24px
        }

        .et-table tbody tr:hover {
            background: #f8faff
        }

        .et-table tfoot td {
            padding: 14px 12px;
            font-weight: 700;
            background: #fafbff;
            border-top: 2px solid #e2e8f0
        }

        .et-table tfoot td:first-child {
            padding-left: 24px
        }

        .et-table tfoot td:last-child {
            padding-right: 24px
        }

        /* ─── Buttons ─────────────────────────────── */
        .et-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: opacity .2s, transform .1s;
            text-decoration: none
        }

        .et-btn:hover {
            opacity: .88;
            transform: translateY(-1px);
            color: #fff
        }

        .et-btn:active {
            transform: translateY(0)
        }

        .et-btn-filter {
            background: #fff;
            color: #374151 !important;
            border: 1.5px solid #e2e8f0
        }

        .et-btn-filter:hover {
            border-color: #4f46e5;
            color: #4f46e5 !important;
            opacity: 1
        }

        .et-btn-pdf {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff
        }

        .et-btn-excel {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff
        }

        .et-btn-print {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff
        }

        .et-accent {
            height: 4px;
            background: linear-gradient(90deg, #4f46e5, #7c3aed, #a855f7)
        }

        .et-empty {
            text-align: center;
            padding: 60px 20px
        }

        .et-empty i {
            font-size: 48px;
            color: #cbd5e1
        }

        .et-empty p {
            color: #94a3b8;
            margin-top: 12px;
            font-size: 14px
        }

        /* SAR PKR equivalent cell */
        .sar-pkr-eq {
            font-size: 11.5px;
            color: #be123c;
            font-weight: 600;
            margin-top: 3px
        }

        /* ─── PRINT ───────────────────────────────── */
        .print-header {
            display: none
        }

        @media print {

            .left-side-menu,
            .sidebar-enable,
            .navbar-custom,
            .topbar,
            .footer,
            .page-title-box,
            .no-print,
            button,
            .et-btn {
                display: none !important
            }

            body,
            .content-page,
            .content,
            .wrapper,
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                width: 100% !important
            }

            .content-page {
                margin-left: 0 !important
            }

            .print-header {
                display: block !important;
                margin-bottom: 12px;
                padding-bottom: 10px;
                border-bottom: 2px solid #4f46e5
            }

            .print-header h5 {
                color: #1e1b4b;
                font-size: 16px;
                font-weight: 800;
                margin: 0 0 4px
            }

            .print-header p {
                color: #64748b;
                font-size: 11px;
                margin: 0
            }

            .et-badge,
            .et-stat-icon,
            .et-accent {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important
            }

            .et-stat {
                box-shadow: none;
                border: 1px solid #e2e8f0;
                break-inside: avoid
            }

            .et-table thead th {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important
            }

            .et-table tbody tr:nth-child(even) td {
                background: #f9f9ff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important
            }

            .et-table tfoot td {
                background: #fafbff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important
            }

            .row.g-3 {
                page-break-inside: avoid
            }
        }
    </style>

    @php
        // Group totals by currency so PKR and SAR amounts are never summed together.
        $currencyTotals = $transactions->groupBy('currency')->map(fn($group) => $group->sum('amount'));

        // SAR transactions ki PKR equivalent total (amount * exchange_rate)
        $sarPkrTotal = $transactions->where('currency', 'SAR')->sum(fn($t) => $t->amount * $t->exchange_rate);
    @endphp

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                {{-- Print-only header --}}
                <div class="print-header">
                    <h5>Expense Transaction Report</h5>
                    <p>
                        {{ ucfirst($filters['hajj_umrah'] ?? '') }} &bull; Year: {{ $filters['year'] ?? '' }}
                        @if (!empty($filters['currency']))
                            &bull; Currency: {{ $filters['currency'] }}
                        @endif
                        @if (!empty($filters['from_date']))
                            &bull; From: {{ \Carbon\Carbon::parse($filters['from_date'])->format('d M Y') }}
                        @endif
                        @if (!empty($filters['to_date']))
                            &bull; To: {{ \Carbon\Carbon::parse($filters['to_date'])->format('d M Y') }}
                        @endif
                        &bull; Printed: {{ now()->format('d M Y, h:i A') }}
                    </p>
                </div>

                {{-- Page header --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3 no-print">
                    <div>
                        <h4 class="fw-bold mb-1" style="color:#1e1b4b">Expense Transaction Report</h4>
                        <p class="mb-0" style="font-size:13px;color:#64748b">
                            <span
                                class="et-badge {{ ($filters['hajj_umrah'] ?? '') === 'hajj' ? 'et-badge-hajj' : 'et-badge-umrah' }}">
                                {{ ucfirst($filters['hajj_umrah'] ?? '—') }}
                            </span>
                            &nbsp;{{ $filters['year'] ?? '—' }}
                            @if (!empty($filters['currency']))
                                &nbsp;<span
                                    class="et-badge {{ $filters['currency'] === 'SAR' ? 'et-badge-sar' : 'et-badge-pkr' }}">{{ $filters['currency'] }}</span>
                            @endif
                            @if (!empty($filters['from_date']) || !empty($filters['to_date']))
                                &nbsp;&bull;&nbsp;
                                {{ !empty($filters['from_date']) ? \Carbon\Carbon::parse($filters['from_date'])->format('d M Y') : 'Start' }}
                                &nbsp;→&nbsp;
                                {{ !empty($filters['to_date']) ? \Carbon\Carbon::parse($filters['to_date'])->format('d M Y') : 'End' }}
                            @endif
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('expense.transaction.report.filter') }}" class="et-btn et-btn-filter">
                            <i class="ri-filter-3-line"></i> Change Filter
                        </a>
                        <button onclick="exportExcel()" class="et-btn et-btn-excel">
                            <i class="ri-file-excel-2-line"></i> Export Excel
                        </button>
                        <button onclick="exportPDF()" class="et-btn et-btn-pdf">
                            <i class="ri-file-pdf-line"></i> Export PDF
                        </button>
                        <button onclick="window.print()" class="et-btn et-btn-print">
                            <i class="ri-printer-line"></i> Print
                        </button>
                    </div>
                </div>

                {{-- Summary cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="et-stat">
                            <div class="et-stat-icon" style="background:#ede9fe"><i class="ri-receipt-line fs-5"
                                    style="color:#7c3aed"></i></div>
                            <div>
                                <div class="et-stat-label">Transactions</div>
                                <div class="et-stat-value">{{ $transactions->count() }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="et-stat">
                            <div class="et-stat-icon" style="background:#dcfce7"><i class="ri-money-dollar-circle-line fs-5"
                                    style="color:#16a34a"></i></div>
                            <div>
                                <div class="et-stat-label">Total Amount</div>
                                <div class="et-stat-value">
                                    @forelse ($currencyTotals as $curr => $total)
                                        <div class="{{ $currencyTotals->count() > 1 ? 'et-stat-multi' : '' }}">
                                            {{ number_format($total, 0) }}
                                            <span
                                                style="font-size:11px;color:#94a3b8;font-weight:600">{{ $curr }}</span>
                                        </div>
                                    @empty
                                        0
                                    @endforelse
                                    {{-- SAR ki PKR equivalent summary mein bhi dikhao --}}
                                    @if ($sarPkrTotal > 0)
                                        <div style="font-size:11px;color:#be123c;font-weight:600;margin-top:2px;">
                                            ≈ PKR {{ number_format($sarPkrTotal, 0) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="et-stat">
                            <div class="et-stat-icon" style="background:#fef9c3"><i class="ri-hand-coin-line fs-5"
                                    style="color:#a16207"></i></div>
                            <div>
                                <div class="et-stat-label">Cash</div>
                                <div class="et-stat-value">
                                    {{ number_format($transactions->where('payment_type', 'cash')->sum('amount'), 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="et-stat">
                            <div class="et-stat-icon" style="background:#dbeafe"><i class="ri-bank-line fs-5"
                                    style="color:#1d4ed8"></i></div>
                            <div>
                                <div class="et-stat-label">Bank / Online / Ewallet</div>
                                <div class="et-stat-value">
                                    {{ number_format($transactions->whereIn('payment_type', ['bank', 'online', 'ewallet'])->sum('amount'), 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="card border-0" style="border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.07)">
                    <div class="et-accent"></div>
                    <div class="card-body p-0">
                        @if ($transactions->isEmpty())
                            <div class="et-empty">
                                <i class="ri-inbox-2-line"></i>
                                <p>No transactions found for the selected filters.</p>
                                <a href="{{ route('expense.transaction.report.filter') }}"
                                    class="et-btn et-btn-filter no-print" style="display:inline-flex;margin-top:12px">
                                    <i class="ri-filter-3-line"></i> Try Different Filters
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table et-table mb-0" id="reportTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Expense Category</th>
                                            <th>Package</th>
                                            <th>Year</th>
                                            <th>Payment Method</th>
                                            <th>Reference No.</th>
                                            <th>Narration</th>
                                            <th>Currency</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">PKR Equivalent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $i => $txn)
                                            @php
                                                $pmBadge = match ($txn->payment_type) {
                                                    'cash' => 'et-badge-cash',
                                                    'bank' => 'et-badge-bank',
                                                    'online' => 'et-badge-online',
                                                    'ewallet' => 'et-badge-ewallet',
                                                    default => '',
                                                };
                                                $pmLabel = match ($txn->payment_type) {
                                                    'cash' => 'Cash',
                                                    'bank' => 'Bank',
                                                    'online' => 'Online',
                                                    'ewallet' => 'E-Wallet',
                                                    default => ucfirst($txn->payment_type),
                                                };
                                                $currBadge = $txn->currency === 'SAR' ? 'et-badge-sar' : 'et-badge-pkr';
                                                $pkrEquivalent =
                                                    $txn->currency === 'SAR'
                                                        ? $txn->amount * $txn->exchange_rate
                                                        : null;
                                            @endphp
                                            <tr>
                                                <td class="text-muted fw-medium">{{ $i + 1 }}</td>
                                                <td>{{ \Carbon\Carbon::parse($txn->date)->format('d M Y') }}</td>
                                                <td class="fw-medium">{{ $txn->expense->name ?? '—' }}</td>
                                                <td>
                                                    <span
                                                        class="et-badge {{ $txn->hajj_umrah === 'hajj' ? 'et-badge-hajj' : 'et-badge-umrah' }}">{{ ucfirst($txn->hajj_umrah) }}</span>
                                                </td>
                                                <td>{{ $txn->year }}</td>
                                                <td><span class="et-badge {{ $pmBadge }}">{{ $pmLabel }}</span>
                                                </td>
                                                <td class="text-muted">{{ $txn->reference_no ?: '—' }}</td>
                                                <td class="text-muted">{{ $txn->description ?: '—' }}</td>
                                                <td><span class="et-badge {{ $currBadge }}">{{ $txn->currency }}</span>
                                                </td>
                                                <td class="text-end fw-bold" style="color:#1e1b4b">
                                                    {{ number_format($txn->amount, 2) }}
                                                    @if ($txn->currency === 'SAR')
                                                        {{-- Exchange rate chota dikhao --}}
                                                        <div style="font-size:10.5px;color:#94a3b8;font-weight:500;">
                                                            @ {{ number_format($txn->exchange_rate, 2) }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if ($pkrEquivalent !== null)
                                                        {{-- SAR ka PKR equivalent --}}
                                                        <span class="fw-bold" style="color:#be123c;">
                                                            {{ number_format($pkrEquivalent, 2) }}
                                                        </span>
                                                        <div style="font-size:10.5px;color:#94a3b8;font-weight:500;">PKR
                                                        </div>
                                                    @else
                                                        {{-- PKR transaction ke liye same amount --}}
                                                        <span class="fw-bold" style="color:#0e7490;">
                                                            {{ number_format($txn->amount, 2) }}
                                                        </span>
                                                        <div style="font-size:10.5px;color:#94a3b8;font-weight:500;">PKR
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        @if ($currencyTotals->count() > 1)
                                            @foreach ($currencyTotals as $curr => $total)
                                                <tr>
                                                    <td colspan="8" class="text-end"
                                                        style="color:#4f46e5;font-size:13px">Grand Total</td>
                                                    <td><span
                                                            class="et-badge {{ $curr === 'SAR' ? 'et-badge-sar' : 'et-badge-pkr' }}">{{ $curr }}</span>
                                                    </td>
                                                    <td class="text-end" style="color:#4f46e5;font-size:16px">
                                                        {{ number_format($total, 2) }}</td>
                                                    <td class="text-end" style="color:#be123c;font-size:13px">
                                                        @if ($curr === 'SAR' && $sarPkrTotal > 0)
                                                            ≈ PKR {{ number_format($sarPkrTotal, 2) }}
                                                        @elseif ($curr === 'PKR')
                                                            PKR {{ number_format($total, 2) }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-end" style="color:#4f46e5;font-size:13px">
                                                    Grand Total</td>
                                                <td>
                                                    @if ($transactions->isNotEmpty())
                                                        <span
                                                            class="et-badge {{ $transactions->first()->currency === 'SAR' ? 'et-badge-sar' : 'et-badge-pkr' }}">{{ $transactions->first()->currency }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-end" style="color:#4f46e5;font-size:16px">
                                                    {{ number_format($transactions->sum('amount'), 2) }}</td>
                                                <td class="text-end" style="color:#be123c;font-size:13px">
                                                    @if ($transactions->isNotEmpty() && $transactions->first()->currency === 'SAR')
                                                        ≈ PKR {{ number_format($sarPkrTotal, 2) }}
                                                    @else
                                                        PKR {{ number_format($transactions->sum('amount'), 2) }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        /* ── PHP data passed to JS ─────────────────────────── */
        const REPORT = {
            title: 'Expense Transaction Report',
            package: '{{ ucfirst($filters['hajj_umrah'] ?? '') }}',
            year: '{{ $filters['year'] ?? '' }}',
            currency: '{{ $filters['currency'] ?? '' }}',
            fromDate: '{{ !empty($filters['from_date']) ? \Carbon\Carbon::parse($filters['from_date'])->format('d M Y') : '' }}',
            toDate: '{{ !empty($filters['to_date']) ? \Carbon\Carbon::parse($filters['to_date'])->format('d M Y') : '' }}',
            summary: {
                transactions: {{ $transactions->count() }},
                total: {{ $transactions->sum('amount') }},
                cash: {{ $transactions->where('payment_type', 'cash')->sum('amount') }},
                other: {{ $transactions->whereIn('payment_type', ['bank', 'online', 'ewallet'])->sum('amount') }},
            },
            sarPkrTotal: {{ $sarPkrTotal }},
            currencyTotals: {
                @foreach ($currencyTotals as $curr => $total)
                    '{{ $curr }}': {{ $total }},
                @endforeach
            },
            rows: [
                @foreach ($transactions as $i => $txn)
                    @php
                        $rowPkrEquiv = $txn->currency === 'SAR' ? $txn->amount * $txn->exchange_rate : $txn->amount;
                    @endphp {
                        no: {{ $i + 1 }},
                        date: '{{ \Carbon\Carbon::parse($txn->date)->format('d M Y') }}',
                        category: '{{ addslashes($txn->expense->name ?? '—') }}',
                        package: '{{ ucfirst($txn->hajj_umrah) }}',
                        year: {{ $txn->year }},
                        payment: '{{ match ($txn->payment_type) {'cash' => 'Cash','bank' => 'Bank','online' => 'Online','ewallet' => 'E-Wallet',default => ucfirst($txn->payment_type)} }}',
                        ref: '{{ addslashes($txn->reference_no ?: '—') }}',
                        currency: '{{ $txn->currency }}',
                        amount: {{ $txn->amount }},
                        amountFmt: '{{ number_format($txn->amount, 2) }}',
                        exchangeRate: {{ $txn->exchange_rate ?? 1 }},
                        pkrEquiv: {{ $rowPkrEquiv }},
                        pkrEquivFmt: '{{ number_format($rowPkrEquiv, 2) }}',
                    },
                @endforeach
            ],
            grandTotal: '{{ number_format($transactions->sum('amount'), 2) }}',
            filename: 'expense-transaction-report-{{ $filters['year'] ?? 'report' }}'
        };

        /* ── PDF Export ─────────────────────────────────────── */
        function exportPDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'landscape',
                unit: 'pt',
                format: 'a4'
            });
            const W = doc.internal.pageSize.width;

            /* purple header bar */
            doc.setFillColor(79, 70, 229);
            doc.rect(0, 0, W, 36, 'F');

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(14);
            doc.setTextColor(255, 255, 255);
            doc.text('Expense Transaction Report', 30, 23);

            /* meta line */
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.setTextColor(100, 116, 139);
            let metaParts = [REPORT.package, 'Year: ' + REPORT.year];
            if (REPORT.currency) metaParts.push('Currency: ' + REPORT.currency);
            if (REPORT.fromDate) metaParts.push('From: ' + REPORT.fromDate);
            if (REPORT.toDate) metaParts.push('To: ' + REPORT.toDate);
            metaParts.push('Generated: ' + new Date().toLocaleDateString('en-PK', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }));
            doc.text(metaParts.join('   |   '), 30, 50);

            /* summary row */
            doc.setFontSize(8);
            doc.setTextColor(55, 65, 81);
            const currencyLine = Object.entries(REPORT.currencyTotals)
                .map(([curr, total]) => `${curr}: ${total.toLocaleString()}`)
                .join('   |   ');
            let sumText =
                `Transactions: ${REPORT.summary.transactions}   |   ${currencyLine}   |   Cash: ${REPORT.summary.cash.toLocaleString()}   |   Bank/Online/Ewallet: ${REPORT.summary.other.toLocaleString()}`;
            if (REPORT.sarPkrTotal > 0) {
                sumText +=
                    `   |   SAR in PKR: ${REPORT.sarPkrTotal.toLocaleString(undefined, {minimumFractionDigits:2,maximumFractionDigits:2})}`;
            }
            doc.text(sumText, 30, 63);

            /* foot rows */
            const footRows = Object.entries(REPORT.currencyTotals).map(([curr, total]) => [
                '', '', '', '', '', '', 'Grand Total', curr,
                total.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }),
                curr === 'SAR' && REPORT.sarPkrTotal > 0 ?
                '≈ PKR ' + REPORT.sarPkrTotal.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) :
                'PKR ' + total.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })
            ]);

            /* table */
            doc.autoTable({
                startY: 75,
                head: [
                    ['#', 'Date', 'Expense Category', 'Package', 'Year', 'Payment Method', 'Reference No.',
                        'Currency', 'Amount', 'PKR Equivalent'
                    ]
                ],
                body: REPORT.rows.map(r => [r.no, r.date, r.category, r.package, r.year, r.payment, r.ref,
                    r.currency,
                    r.currency === 'SAR' ? r.amountFmt + ' (@ ' + r.exchangeRate.toFixed(2) + ')' : r
                    .amountFmt,
                    r.pkrEquivFmt
                ]),
                foot: footRows,
                headStyles: {
                    fillColor: [79, 70, 229],
                    textColor: 255,
                    fontSize: 8,
                    fontStyle: 'bold'
                },
                footStyles: {
                    fillColor: [248, 250, 252],
                    textColor: [79, 70, 229],
                    fontStyle: 'bold',
                    halign: 'right'
                },
                bodyStyles: {
                    fontSize: 8.5,
                    textColor: [55, 65, 81]
                },
                columnStyles: {
                    8: {
                        halign: 'right'
                    },
                    9: {
                        halign: 'right'
                    }
                },
                alternateRowStyles: {
                    fillColor: [248, 250, 255]
                },
                margin: {
                    left: 30,
                    right: 30
                },
                showFoot: 'lastPage',
                didDrawPage(data) {
                    doc.setFontSize(7);
                    doc.setTextColor(150);
                    doc.text('Page ' + data.pageNumber, W - 50, doc.internal.pageSize.height - 15);
                }
            });

            doc.save(REPORT.filename + '.pdf');
        }

        /* ── Excel Export ───────────────────────────────────── */
        function exportExcel() {
            const wb = XLSX.utils.book_new();

            const data = [];
            data.push([REPORT.title]);
            data.push([]);
            data.push(['Package', REPORT.package]);
            data.push(['Year', REPORT.year]);
            if (REPORT.currency) data.push(['Currency', REPORT.currency]);
            if (REPORT.fromDate) data.push(['From Date', REPORT.fromDate]);
            if (REPORT.toDate) data.push(['To Date', REPORT.toDate]);
            data.push(['Generated', new Date().toLocaleDateString('en-PK', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            })]);
            data.push([]);
            data.push(['─── Summary ───────────────────']);
            data.push(['Total Transactions', REPORT.summary.transactions]);
            Object.entries(REPORT.currencyTotals).forEach(([curr, total]) => {
                data.push(['Total Amount (' + curr + ')', total]);
            });
            if (REPORT.sarPkrTotal > 0) {
                data.push(['SAR in PKR Equivalent', REPORT.sarPkrTotal]);
            }
            data.push(['Cash', REPORT.summary.cash]);
            data.push(['Bank / Online / Ewallet', REPORT.summary.other]);
            data.push([]);

            /* header row */
            data.push(['#', 'Date', 'Expense Category', 'Package', 'Year', 'Payment Method', 'Reference No.',
                'Currency', 'Amount', 'Exchange Rate', 'PKR Equivalent'
            ]);

            /* data rows */
            REPORT.rows.forEach(r => data.push([
                r.no, r.date, r.category, r.package, r.year, r.payment, r.ref,
                r.currency, r.amount,
                r.currency === 'SAR' ? r.exchangeRate : 1,
                r.pkrEquiv
            ]));

            /* grand total rows */
            Object.entries(REPORT.currencyTotals).forEach(([curr, total]) => {
                data.push([
                    '', '', '', '', '', '', '', 'Grand Total (' + curr + ')', total, '',
                    curr === 'SAR' ? REPORT.sarPkrTotal : total
                ]);
            });

            const ws = XLSX.utils.aoa_to_sheet(data);

            ws['!cols'] = [{
                    wch: 5
                }, {
                    wch: 14
                }, {
                    wch: 26
                }, {
                    wch: 10
                }, {
                    wch: 7
                },
                {
                    wch: 16
                }, {
                    wch: 18
                }, {
                    wch: 10
                }, {
                    wch: 14
                }, {
                    wch: 14
                }, {
                    wch: 16
                }
            ];

            const headerRowIdx = data.findIndex(r => r[0] === '#');

            if (ws['A1']) {
                ws['A1'].s = {
                    font: {
                        bold: true,
                        sz: 14,
                        color: {
                            rgb: '1E1B4B'
                        }
                    }
                };
            }

            ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'].forEach(col => {
                const cell = ws[col + (headerRowIdx + 1)];
                if (cell) {
                    cell.s = {
                        font: {
                            bold: true,
                            color: {
                                rgb: 'FFFFFF'
                            }
                        },
                        fill: {
                            fgColor: {
                                rgb: '4F46E5'
                            }
                        },
                        alignment: {
                            horizontal: 'center'
                        }
                    };
                }
            });

            const currencyCount = Object.keys(REPORT.currencyTotals).length;
            for (let r = data.length - currencyCount + 1; r <= data.length; r++) {
                ['H', 'I', 'K'].forEach(col => {
                    const cell = ws[col + r];
                    if (cell) {
                        cell.s = {
                            font: {
                                bold: true,
                                color: {
                                    rgb: '4F46E5'
                                }
                            }
                        };
                    }
                });
            }

            XLSX.utils.book_append_sheet(wb, ws, 'Report');
            XLSX.writeFile(wb, REPORT.filename + '.xlsx');
        }
    </script>

@endsection
