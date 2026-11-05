@extends('layout.master')

@section('title', 'Expense Ledger (Dr / Cr) - ' . $expense->name)

@section('content')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

    <style>
        .ledger-stat-card {
            border-radius: 14px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            transition: transform .15s ease, box-shadow .15s ease;
            background: #fff;
        }

        .ledger-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
        }

        .ledger-stat-card .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ledger-stat-card .stat-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #8c96a5;
            margin-bottom: 2px;
        }

        .ledger-stat-card .stat-value {
            font-size: 20px;
            font-weight: 800;
            line-height: 1.2;
        }

        .statement-table thead th {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            background: #f8fafc;
            padding: 12px 14px;
            vertical-align: middle;
        }

        .statement-table tbody td {
            font-size: 13px;
            padding: 12px 14px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .statement-table tbody tr:hover {
            background: #f8fafc;
        }

        .opening-row {
            background: #fffbeb !important;
            font-weight: 600;
        }

        .btn-currency-toggle {
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 50rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            user-select: none;
        }

        .btn-check:checked + .btn-pkr-toggle {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(220, 53, 69, 0.35) !important;
        }

        .btn-check:not(:checked) + .btn-pkr-toggle {
            background-color: #ffffff !important;
            border-color: #fca5a5 !important;
            color: #b91c1c !important;
            opacity: 0.7;
        }

        .btn-check:checked + .btn-sar-toggle {
            background-color: #d97706 !important;
            border-color: #d97706 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(217, 119, 6, 0.35) !important;
        }

        .btn-check:not(:checked) + .btn-sar-toggle {
            background-color: #ffffff !important;
            border-color: #fcd34d !important;
            color: #b45309 !important;
            opacity: 0.7;
        }

        .btn-check:checked + .btn-roe-toggle {
            background-color: #0284c7 !important;
            border-color: #0284c7 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35) !important;
        }

        .btn-check:not(:checked) + .btn-roe-toggle {
            background-color: #ffffff !important;
            border-color: #7dd3fc !important;
            color: #0369a1 !important;
            opacity: 0.7;
        }

        @media print {
            .no-print, .content-page .header, .left-side-menu, .footer, .btn, .nav, .modal, .card-header button {
                display: none !important;
            }
            .content-page {
                margin: 0 !important;
                padding: 0 !important;
            }
            body {
                background: #fff !important;
                font-size: 11px !important;
            }
            .statement-table th, .statement-table td {
                padding: 6px 8px !important;
                font-size: 10px !important;
            }
            .ledger-stat-card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }
        }
    </style>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- TOP ACTION BAR -->
                <div class="py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom mb-3 no-print">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('reports.hub', ['tab' => 'expense']) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to All-in-One Hub
                        </a>
                        <span class="badge bg-soft-danger text-danger fs-12 px-3 py-1-5 rounded-pill border border-danger border-opacity-25">
                            <i class="mdi mdi-book-open-page-variant me-1"></i> Expense Ledger Statement
                        </span>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="mdi mdi-filter-variant me-1"></i> Filter / Change Dates
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill" onclick="exportLedgerExcel()">
                            <i class="mdi mdi-file-excel me-1"></i> Export Excel
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" onclick="exportLedgerPdf()">
                            <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF
                        </button>
                        <button type="button" class="btn btn-danger btn-sm rounded-pill shadow-sm" onclick="window.print()">
                            <i class="mdi mdi-printer me-1"></i> Print Formal Statement
                        </button>
                    </div>
                </div>

                <!-- EXPENSE HEADER PROFILE CARD -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="row align-items-center g-3">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                        <i class="mdi mdi-receipt-text fs-22"></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-bold text-dark m-0 fs-18">{{ $expense->name }}</h4>
                                        <span class="text-muted fs-12">
                                            <i class="mdi mdi-information-outline me-1"></i>{{ $expense->description ?? 'Official Expense Head Ledger & Audit Trail' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 fs-12 text-muted mt-2">
                                    <span><i class="mdi mdi-tag me-1 text-danger"></i><strong>Type:</strong> Operational / Administrative Expense</span>
                                    <span><i class="mdi mdi-domain me-1 text-danger"></i><strong>Agency:</strong> Dua Travels & Tours</span>
                                    <span><i class="mdi mdi-calendar-range me-1 text-danger"></i><strong>Season:</strong> {{ ucfirst($package ?: 'Consolidated') }}</span>
                                </div>
                            </div>

                            <!-- Switch Expense & Date Range -->
                            <div class="col-md-5 text-md-end no-print">
                                <form action="{{ route('expense.ledger.view') }}" method="GET" class="d-inline-flex align-items-center gap-2">
                                    <select name="expense_id" class="form-select form-select-sm" onchange="this.form.submit()" style="max-width: 230px;">
                                        <option value="all" {{ (string)$expense->id === 'all' ? 'selected' : '' }}>🌟 All Expenses (Consolidated)</option>
                                        @foreach($allExpenses as $exp)
                                            <option value="{{ $exp->id }}" {{ (string)$expense->id === (string)$exp->id ? 'selected' : '' }}>
                                                {{ $exp->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($fromDate) <input type="hidden" name="from_date" value="{{ $fromDate }}"> @endif
                                    @if($toDate) <input type="hidden" name="to_date" value="{{ $toDate }}"> @endif
                                    @if($package && $package !== 'all') <input type="hidden" name="package_type" value="{{ $package }}"> @endif
                                </form>
                                <div class="fs-12 text-muted mt-2">
                                    <strong>Statement Date:</strong> {{ now()->format('d M, Y h:i A') }}
                                </div>
                                @if($fromDate || $toDate)
                                    <div class="badge bg-soft-info text-info mt-1 fs-11">
                                        <i class="mdi mdi-calendar-clock me-1"></i> Period: {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M, Y') : 'Start' }} — {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M, Y') : 'Today' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRINT HEADER ONLY -->
                <div class="d-none d-print-block mb-3 p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-0">DUA TRAVELS & TOURS</h3>
                            <h5 class="text-danger mb-0">EXPENSE STATEMENT OF ACCOUNT (LEDGER)</h5>
                            <p class="fs-13 mb-0"><strong>Expense Head:</strong> {{ $expense->name }}</p>
                        </div>
                        <div class="text-end fs-12">
                            <div><strong>Statement Date:</strong> {{ now()->format('d M, Y h:i A') }}</div>
                            @if($fromDate || $toDate)
                                <div><strong>Period:</strong> {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M, Y') : 'Start' }} to {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M, Y') : 'Today' }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 4 EXECUTIVE KPI SUMMARY CARDS -->
                <div class="row g-3 mb-3">
                    <!-- Total Debits (Expenses Incurred) -->
                    <div class="col-6 col-md-3">
                        <div class="card ledger-stat-card border-start border-4 border-danger p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label text-danger">Total Expenses (Debits)</div>
                                    <div class="stat-value text-danger">PKR {{ number_format($totalDebitsPkr, 2) }}</div>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @if(($totalDebitsSar ?? 0) > 0)
                                            <span class="badge bg-soft-danger text-danger border fs-10" title="SAR Expenses">
                                                SAR {{ number_format($totalDebitsSar, 2) }}
                                            </span>
                                        @endif
                                        <small class="text-muted fs-11">{{ $entriesCount }} Transactions</small>
                                    </div>
                                </div>
                                <div class="icon-box bg-soft-danger text-danger">
                                    <i class="mdi mdi-arrow-up-bold fs-22"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Credits (Refunds / Reversals) -->
                    <div class="col-6 col-md-3">
                        <div class="card ledger-stat-card border-start border-4 border-success p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label text-success">Total Reversals (Credits)</div>
                                    <div class="stat-value text-success">PKR {{ number_format($totalCreditsPkr, 2) }}</div>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @if(($totalCreditsSar ?? 0) > 0)
                                            <span class="badge bg-soft-success text-success border fs-10" title="SAR Reversals">
                                                SAR {{ number_format($totalCreditsSar, 2) }}
                                            </span>
                                        @endif
                                        <small class="text-muted fs-11">Refunds & adjustments</small>
                                    </div>
                                </div>
                                <div class="icon-box bg-soft-success text-success">
                                    <i class="mdi mdi-arrow-down-bold fs-22"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Closing Balance -->
                    <div class="col-6 col-md-3">
                        <div class="card ledger-stat-card border-start border-4 border-danger p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label text-danger">Net Expense Balance</div>
                                    <div class="stat-value text-danger">
                                        PKR {{ number_format($absClosingPkr, 2) }}
                                        <span class="badge {{ $statusBadgeClass }} fs-11 ms-1">{{ $statusLabel }}</span>
                                    </div>
                                    @if(isset($closingBalanceSar) && abs($closingBalanceSar) > 0)
                                        <div class="mt-1">
                                            <span class="badge bg-soft-warning text-dark border fs-10">
                                                SAR {{ number_format($absClosingSar, 2) }}
                                            </span>
                                        </div>
                                    @endif
                                    <small class="text-muted fs-11 d-block mt-1">Net operational outlay</small>
                                </div>
                                <div class="icon-box bg-soft-danger text-danger">
                                    <i class="mdi mdi-scale-balance fs-22"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Opening Balance -->
                    <div class="col-6 col-md-3">
                        <div class="card ledger-stat-card border-start border-4 border-secondary p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label text-secondary">Opening Balance (B/F)</div>
                                    <div class="stat-value text-dark">
                                        PKR {{ number_format(abs($openingBalancePkr), 2) }}
                                        <small class="badge {{ $openingBalancePkr >= 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 10px;">
                                            {{ $openingBalancePkr >= 0 ? 'Dr' : 'Cr' }}
                                        </small>
                                    </div>
                                    @if(abs($openingBalanceSar) > 0)
                                        <div class="mt-1">
                                            <span class="badge bg-light text-dark border fs-10">
                                                SAR {{ number_format(abs($openingBalanceSar), 2) }}
                                            </span>
                                        </div>
                                    @endif
                                    <small class="text-muted fs-11 d-block mt-1">{{ $fromDate ? 'Prior to ' . \Carbon\Carbon::parse($fromDate)->format('d M, Y') : 'Start of Account' }}</small>
                                </div>
                                <div class="icon-box bg-light text-secondary">
                                    <i class="mdi mdi-history fs-22"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CURRENCY TOGGLE BAR -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-2 bg-white rounded shadow-sm border no-print">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold fs-12 text-muted text-uppercase me-2"><i class="mdi mdi-currency-usd me-1"></i>Currency Columns:</span>

                        <input type="checkbox" class="btn-check" id="togglePkrCols" checked autocomplete="off" onchange="toggleCurrencyVisibility('pkr', this.checked)">
                        <label class="btn-currency-toggle btn-pkr-toggle shadow-sm border" for="togglePkrCols" id="lblTogglePkr">
                            <i class="mdi mdi-check-circle fs-14" id="iconTogglePkr"></i> Show PKR
                        </label>

                        <input type="checkbox" class="btn-check" id="toggleSarCols" checked autocomplete="off" onchange="toggleCurrencyVisibility('sar', this.checked)">
                        <label class="btn-currency-toggle btn-sar-toggle shadow-sm border" for="toggleSarCols" id="lblToggleSar">
                            <i class="mdi mdi-check-circle fs-14" id="iconToggleSar"></i> Show SAR
                        </label>

                        <input type="checkbox" class="btn-check" id="toggleRoeCols" checked autocomplete="off" onchange="toggleRoeVisibility(this.checked)">
                        <label class="btn-currency-toggle btn-roe-toggle shadow-sm border" for="toggleRoeCols" id="lblToggleRoe">
                            <i class="mdi mdi-check-circle fs-14" id="iconToggleRoe"></i> Show ROE
                        </label>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                        <span class="badge bg-soft-danger text-danger px-3 py-1-5 fs-12 rounded-pill">
                            <i class="mdi mdi-database me-1"></i> {{ $entriesCount }} Recorded Transaction(s)
                        </span>
                    </div>
                </div>

                <!-- CORE ACCOUNTING STATEMENT (DEBIT / CREDIT / RUNNING BALANCE TABLE) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="fs-14 fw-bold m-0 text-dark">
                            <i class="mdi mdi-book-open-page-variant text-danger me-2"></i> Statement of Account (Expense Ledger)
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-soft-danger text-danger px-3 py-2 rounded-pill fs-12">
                                {{ count($statementLines) + ($openingBalancePkr != 0 ? 1 : 0) }} Rows
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table statement-table table-hover align-middle mb-0" id="expenseLedgerTable">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="ps-3 align-middle" style="width: 35px;">#</th>
                                        <th rowspan="2" class="align-middle" style="width: 95px;">Date</th>
                                        <th rowspan="2" class="align-middle" style="width: 110px;">Voucher #</th>
                                        <th rowspan="2" class="align-middle" style="width: 130px;">Expense Head</th>
                                        <th rowspan="2" class="align-middle" style="width: 140px;">Contra Account</th>
                                        <th rowspan="2" class="align-middle">Narration / Particulars</th>
                                        <th rowspan="2" class="align-middle" style="width: 90px;">Method</th>
                                        <th colspan="3" class="text-center bg-soft-danger text-danger border-bottom-0 py-1 col-pkr">PKR (Pakistani Rupee)</th>
                                        <th rowspan="2" class="text-center align-middle col-roe" style="width: 75px;">ROE</th>
                                        <th colspan="3" class="text-center bg-soft-warning text-dark border-bottom-0 py-1 col-sar">SAR (Saudi Riyal)</th>
                                        <th rowspan="2" class="no-print text-center align-middle" style="width: 50px;">Proof</th>
                                    </tr>
                                    <tr>
                                        <th class="text-end text-danger py-1 col-pkr" style="min-width: 95px;">Debit (Dr)</th>
                                        <th class="text-end text-success py-1 col-pkr" style="min-width: 95px;">Credit (Cr)</th>
                                        <th class="text-end py-1 text-dark col-pkr" style="min-width: 95px;">Balance PKR</th>
                                        <th class="text-end text-danger py-1 col-sar" style="min-width: 85px;">Debit (Dr)</th>
                                        <th class="text-end text-success py-1 col-sar" style="min-width: 85px;">Credit (Cr)</th>
                                        <th class="text-end py-1 text-dark col-sar" style="min-width: 85px;">Balance SAR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Opening Balance Row if date filter applied and balance exists --}}
                                    @if($openingBalancePkr != 0 || $openingBalanceSar != 0)
                                        <tr class="opening-row">
                                            <td class="ps-3 text-muted">—</td>
                                            <td>{{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M, Y') : '—' }}</td>
                                            <td><span class="badge bg-warning text-dark border">OPENING</span></td>
                                            <td><span class="badge bg-soft-danger text-danger">B/F Balance</span></td>
                                            <td><span class="badge bg-light text-dark border">Opening Balance</span></td>
                                            <td><strong>Opening Balance Brought Forward</strong></td>
                                            <td>—</td>
                                            {{-- PKR Columns --}}
                                            <td class="text-end fw-bold text-danger col-pkr">
                                                {{ $openingBalancePkr > 0 ? number_format($openingBalancePkr, 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-success col-pkr">
                                                {{ $openingBalancePkr < 0 ? number_format(abs($openingBalancePkr), 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-dark col-pkr">
                                                {{ number_format(abs($openingBalancePkr), 2) }}
                                                <small class="badge {{ $openingBalancePkr > 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 9px;">
                                                    {{ $openingBalancePkr > 0 ? 'Dr' : 'Cr' }}
                                                </small>
                                            </td>
                                            {{-- ROE --}}
                                            <td class="text-center col-roe">—</td>
                                            {{-- SAR Columns --}}
                                            <td class="text-end fw-bold text-danger col-sar">
                                                {{ $openingBalanceSar > 0 ? number_format($openingBalanceSar, 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-success col-sar">
                                                {{ $openingBalanceSar < 0 ? number_format(abs($openingBalanceSar), 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-dark col-sar">
                                                {{ number_format(abs($openingBalanceSar), 2) }}
                                                @if($openingBalanceSar != 0)
                                                    <small class="badge {{ $openingBalanceSar > 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 9px;">
                                                        {{ $openingBalanceSar > 0 ? 'Dr' : 'Cr' }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="no-print text-center">—</td>
                                        </tr>
                                    @endif

                                    @forelse($statementLines as $row)
                                        <tr>
                                            <td class="ps-3 text-muted fw-semibold">{{ $loop->iteration }}</td>
                                            <td class="fw-medium text-nowrap">
                                                {{ \Carbon\Carbon::parse($row['date'])->format('d M, Y') }}
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-primary text-primary border border-primary border-opacity-25 fs-11">
                                                    {{ $row['voucher_no'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-danger text-danger fs-11">
                                                    {{ $row['expense_category'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border fs-11">
                                                    {{ $row['contra_account'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-dark">{{ $row['description'] }}</span>
                                                <div class="fs-11 text-muted">
                                                    <span class="badge bg-soft-secondary text-secondary me-1">{{ $row['hajj_umrah'] }}</span>
                                                    @if($row['year']) <span class="badge bg-soft-info text-info">{{ $row['year'] }}</span> @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border fs-11">{{ $row['payment_method'] }}</span>
                                            </td>

                                            {{-- PKR: Debit, Credit, Balance --}}
                                            <td class="text-end fw-semibold text-danger col-pkr">
                                                {{ $row['debit_pkr'] > 0 ? number_format($row['debit_pkr'], 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-semibold text-success col-pkr">
                                                {{ $row['credit_pkr'] > 0 ? number_format($row['credit_pkr'], 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-dark col-pkr">
                                                {{ number_format(abs($row['balance_pkr']), 2) }}
                                                <small class="badge {{ $row['balance_pkr'] >= 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 9px;">
                                                    {{ $row['balance_pkr'] >= 0 ? 'Dr' : 'Cr' }}
                                                </small>
                                            </td>

                                            {{-- ROE --}}
                                            <td class="text-center text-muted fs-11 col-roe">
                                                {{ number_format($row['roe'], 2) }}
                                            </td>

                                            {{-- SAR: Debit, Credit, Balance --}}
                                            <td class="text-end fw-semibold text-danger col-sar">
                                                {{ $row['debit_sar'] > 0 ? number_format($row['debit_sar'], 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-semibold text-success col-sar">
                                                {{ $row['credit_sar'] > 0 ? number_format($row['credit_sar'], 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-dark col-sar">
                                                {{ number_format(abs($row['balance_sar']), 2) }}
                                                @if($row['balance_sar'] != 0)
                                                    <small class="badge {{ $row['balance_sar'] >= 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 9px;">
                                                        {{ $row['balance_sar'] >= 0 ? 'Dr' : 'Cr' }}
                                                    </small>
                                                @endif
                                            </td>

                                            {{-- Proof Attachment --}}
                                            <td class="no-print text-center">
                                                @if($row['proof'])
                                                    <a href="{{ $row['proof'] }}" target="_blank" class="btn btn-outline-info btn-xs py-0 px-1" title="View Voucher Proof">
                                                        <i class="mdi mdi-file-document-outline fs-14"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13" class="text-center py-5 text-muted">
                                                <i class="mdi mdi-receipt-text-outline fs-36 text-muted mb-2 d-block"></i>
                                                <div class="fw-bold fs-14">No expense transactions found for this period.</div>
                                                <small class="text-muted">Change your date filters or select a different expense category.</small>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-light fw-bold fs-12 border-top-2">
                                    <tr>
                                        <td colspan="7" class="text-end text-dark pe-3">GRAND TOTALS:</td>
                                        {{-- PKR Totals --}}
                                        <td class="text-end text-danger col-pkr">PKR {{ number_format($totalDebitsPkr, 2) }}</td>
                                        <td class="text-end text-success col-pkr">PKR {{ number_format($totalCreditsPkr, 2) }}</td>
                                        <td class="text-end text-dark col-pkr">
                                            PKR {{ number_format($absClosingPkr, 2) }}
                                            <small class="badge {{ $closingBalancePkr >= 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 9px;">
                                                {{ $closingBalancePkr >= 0 ? 'Dr' : 'Cr' }}
                                            </small>
                                        </td>
                                        {{-- ROE --}}
                                        <td class="text-center col-roe">—</td>
                                        {{-- SAR Totals --}}
                                        <td class="text-end text-danger col-sar">SAR {{ number_format($totalDebitsSar, 2) }}</td>
                                        <td class="text-end text-success col-sar">SAR {{ number_format($totalCreditsSar, 2) }}</td>
                                        <td class="text-end text-dark col-sar">
                                            SAR {{ number_format($absClosingSar, 2) }}
                                            @if($closingBalanceSar != 0)
                                                <small class="badge {{ $closingBalanceSar >= 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 9px;">
                                                    {{ $closingBalanceSar >= 0 ? 'Dr' : 'Cr' }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="no-print"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- FILTER MODAL -->
    <div class="modal fade no-print" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title fs-15 fw-bold" id="filterModalLabel">
                        <i class="mdi mdi-filter-variant me-1"></i> Filter Expense Ledger
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('expense.ledger.view') }}" method="GET">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Expense Category</label>
                                <select name="expense_id" class="form-select form-select-sm">
                                    <option value="all" {{ (string)$expense->id === 'all' ? 'selected' : '' }}>🌟 All Expenses (Consolidated)</option>
                                    @foreach($allExpenses as $exp)
                                        <option value="{{ $exp->id }}" {{ (string)$expense->id === (string)$exp->id ? 'selected' : '' }}>
                                            {{ $exp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Package Season</label>
                                <select name="package_type" class="form-select form-select-sm">
                                    <option value="all" {{ $package === 'all' ? 'selected' : '' }}>Consolidated (Hajj & Umrah)</option>
                                    <option value="umrah" {{ $package === 'umrah' ? 'selected' : '' }}>Umrah Only</option>
                                    <option value="hajj" {{ $package === 'hajj' ? 'selected' : '' }}>Hajj Only</option>
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">From Date</label>
                                <input type="date" name="from_date" id="m_from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">To Date</label>
                                <input type="date" name="to_date" id="m_to_date" class="form-control form-control-sm" value="{{ $toDate }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Search Keyword</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Voucher, narration, amount..." value="{{ $search }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger btn-sm px-4 fw-bold">
                            <i class="mdi mdi-check me-1"></i> Apply Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT FOR DUAL CURRENCY, EXCEL & PDF EXPORT -->
    <script>
        function toggleCurrencyVisibility(curr, isVisible) {
            const cols = document.querySelectorAll('.col-' + curr);
            cols.forEach(el => {
                el.style.display = isVisible ? '' : 'none';
            });
        }

        function toggleRoeVisibility(isVisible) {
            const cols = document.querySelectorAll('.col-roe');
            cols.forEach(el => {
                el.style.display = isVisible ? '' : 'none';
            });
        }

        function exportLedgerExcel() {
            const table = document.getElementById('expenseLedgerTable');
            if (!table) return;

            // Clone table without action / proof column for clean spreadsheet
            const clone = table.cloneNode(true);
            clone.querySelectorAll('.no-print').forEach(el => el.remove());

            const wb = XLSX.utils.table_to_book(clone, { sheet: "Expense_Ledger" });
            const expName = "{{ preg_replace('/[^a-zA-Z0-9_-]/', '_', $expense->name) }}";
            XLSX.writeFile(wb, `Expense_Ledger_${expName}_${new Date().toISOString().slice(0,10)}.xlsx`);
        }

        function exportLedgerPdf() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'pt', 'a4');

            doc.setFontSize(16);
            doc.text("Dua Travels & Tours - Expense Statement of Account", 40, 35);
            doc.setFontSize(11);
            doc.text(`Expense Head: {{ $expense->name }}`, 40, 52);
            doc.text(`Period: {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M, Y') : 'Start' }} to {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M, Y') : 'Today' }}`, 40, 68);
            doc.text(`Generated: ${new Date().toLocaleString()}`, 40, 84);

            doc.autoTable({
                html: '#expenseLedgerTable',
                startY: 95,
                theme: 'striped',
                headStyles: { fillColor: [220, 53, 69] },
                styles: { fontSize: 8, cellPadding: 3 },
                columnStyles: {
                    12: { display: 'none' } // hide proof column
                }
            });

            const expName = "{{ preg_replace('/[^a-zA-Z0-9_-]/', '_', $expense->name) }}";
            doc.save(`Expense_Ledger_${expName}_${new Date().toISOString().slice(0,10)}.pdf`);
        }
    </script>
@endsection
