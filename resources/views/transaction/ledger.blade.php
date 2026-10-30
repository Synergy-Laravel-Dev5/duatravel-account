@extends('layout.master')

@section('title', 'Client Ledger (Dr / Cr) - ' . $client->name)

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
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(25, 135, 84, 0.35) !important;
        }
        .btn-check:not(:checked) + .btn-pkr-toggle {
            background-color: #ffffff !important;
            border-color: #86efac !important;
            color: #15803d !important;
            opacity: 0.7;
        }
        .btn-check:not(:checked) + .btn-pkr-toggle:hover {
            opacity: 1;
            background-color: #f0fdf4 !important;
        }
        .btn-check:checked + .btn-sar-toggle {
            background-color: #f59e0b !important;
            border-color: #d97706 !important;
            color: #1f2937 !important;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35) !important;
        }
        .btn-check:not(:checked) + .btn-sar-toggle {
            background-color: #ffffff !important;
            border-color: #fde68a !important;
            color: #b45309 !important;
            opacity: 0.7;
        }
        .btn-check:not(:checked) + .btn-sar-toggle:hover {
            opacity: 1;
            background-color: #fffbeb !important;
        }

        @media print {
            .app-sidebar-menu,
            .topbar-custom,
            #sidebar-menu,
            .left-side-menu,
            .navbar-custom,
            .footer,
            .page-title-box,
            .no-print {
                display: none !important;
            }
            body {
                background: #fff !important;
            }
            .content-page,
            .content,
            .container-fluid {
                margin: 0 !important;
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .card {
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
            }
            .col-roe.d-none {
                display: none !important;
            }
            .col-pkr.d-none {
                display: none !important;
            }
            .col-sar.d-none {
                display: none !important;
            }
        }
    </style>

    @php
        $absClosing = abs($closingBalance);
        $closingTag = $closingBalance > 0 ? 'Dr (Due)' : ($closingBalance < 0 ? 'Cr (Advance)' : 'Nil');
        $closingBadgeClass = $closingBalance > 0 ? 'bg-danger text-white' : ($closingBalance < 0 ? 'bg-success text-white' : 'bg-secondary text-white');
    @endphp

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- TOP BAR -->
                <div class="py-3 d-flex flex-wrap align-items-center justify-content-between border-bottom mb-3 no-print">
                    <div>
                        <p class="text-muted mb-0 fs-12 text-uppercase fw-semibold">ACCOUNT STATEMENT</p>
                        <h4 class="fw-bold text-dark mb-0 fs-20">
                            <i class="mdi mdi-book-open-page-variant text-primary me-1"></i> Client Ledger (Debit / Credit)
                        </h4>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button onclick="exportExcelStatement()" class="btn btn-success btn-sm shadow-sm">
                            <i class="mdi mdi-file-excel me-1"></i> Export Excel
                        </button>
                        <button onclick="exportPDFStatement()" class="btn btn-danger btn-sm shadow-sm">
                            <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF
                        </button>
                        <button onclick="window.print()" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="mdi mdi-printer me-1"></i> Print
                        </button>
                        <a href="{{ route('transaction.ledger.filter') }}" class="btn btn-outline-primary btn-sm">
                            <i class="mdi mdi-filter-variant me-1"></i> Filter Client / Date
                        </a>
                        <a href="{{ route('reports.transactions') }}" class="btn btn-outline-dark btn-sm">
                            <i class="mdi mdi-format-list-bulleted me-1"></i> All Transactions Report
                        </a>
                    </div>
                </div>

                <!-- CLIENT PROFILE & FILTER INFO -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="row align-items-center g-3">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="mdi mdi-account fs-20"></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-bold text-dark m-0 fs-18">{{ $client->name }}</h4>
                                        @if(!empty($client->company_name))
                                            <span class="text-muted fs-12"><i class="mdi mdi-domain me-1"></i>{{ $client->company_name }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 fs-12 text-muted mt-2">
                                    @if(!empty($client->phone))
                                        <span><i class="mdi mdi-phone me-1 text-primary"></i><strong>Phone:</strong> {{ $client->phone }}</span>
                                    @endif
                                    @if(!empty($client->email))
                                        <span><i class="mdi mdi-email me-1 text-primary"></i><strong>Email:</strong> {{ $client->email }}</span>
                                    @endif
                                    @if(!empty($client->cnic))
                                        <span><i class="mdi mdi-card-account-details me-1 text-primary"></i><strong>CNIC:</strong> {{ $client->cnic }}</span>
                                    @endif
                                    @if(!empty($client->passport_number))
                                        <span><i class="mdi mdi-passport me-1 text-primary"></i><strong>Passport:</strong> {{ $client->passport_number }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Switch Client & Date Range -->
                            <div class="col-md-5 text-md-end no-print">
                                <form action="{{ route('transaction.ledger.view') }}" method="GET" class="d-inline-flex align-items-center gap-2">
                                    <select name="client_id" class="form-select form-select-sm" onchange="this.form.submit()" style="max-width: 200px;">
                                        <option value="all" {{ (string)$client->id === 'all' ? 'selected' : '' }}>-- All Clients --</option>
                                        @foreach($clients as $c)
                                            <option value="{{ $c->id }}" {{ (string)$client->id === (string)$c->id ? 'selected' : '' }}>
                                                {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($fromDate) <input type="hidden" name="from_date" value="{{ $fromDate }}"> @endif
                                    @if($toDate) <input type="hidden" name="to_date" value="{{ $toDate }}"> @endif
                                </form>
                                <div class="fs-12 text-muted mt-2">
                                    <strong>Statement Date:</strong> {{ now()->format('d M, Y h:i A') }}
                                </div>
                                @if($fromDate || $toDate)
                                    <div class="badge bg-soft-info text-info mt-1 fs-11">
                                        <i class="mdi mdi-calendar-clock me-1"></i> Filtered: {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M, Y') : 'Start' }} — {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M, Y') : 'Today' }}
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
                            <h3 class="fw-bold mb-0">DUA TRAVEL & TOURS</h3>
                            <h5 class="text-primary mb-0">CLIENT STATEMENT OF ACCOUNT (LEDGER)</h5>
                            <p class="fs-13 mb-0"><strong>Client:</strong> {{ $client->name }} | {{ $client->phone ?? '' }}</p>
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
                    <!-- Total Debits (Invoiced) -->
                    <div class="col-6 col-md-3">
                        <div class="card ledger-stat-card border-start border-4 border-danger p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label text-danger">Total Debits (Charges / Billed)</div>
                                    <div class="stat-value text-danger">PKR {{ number_format($totalDebits, 2) }}</div>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @if(($totalDebitsSar ?? 0) > 0)
                                            <span class="badge bg-soft-danger text-danger border fs-10" title="SAR Charges">
                                                SAR {{ number_format($totalDebitsSar, 2) }}
                                            </span>
                                        @endif
                                        <small class="text-muted fs-11">{{ $bookingsCount }} Bookings billed</small>
                                    </div>
                                </div>
                                <div class="icon-box bg-soft-danger text-danger">
                                    <i class="mdi mdi-arrow-up-bold fs-22"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Credits (Received) -->
                    <div class="col-6 col-md-3">
                        <div class="card ledger-stat-card border-start border-4 border-success p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label text-success">Total Credits (Paid / Received)</div>
                                    <div class="stat-value text-success">PKR {{ number_format($totalCredits, 2) }}</div>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @if(($totalCreditsSar ?? 0) > 0)
                                            <span class="badge bg-warning text-dark fs-10" title="SAR Received">
                                                SAR {{ number_format($totalCreditsSar, 2) }}
                                            </span>
                                        @endif
                                        @if(($currencySummary['PKR']['received_original'] ?? 0) > 0)
                                            <span class="badge bg-soft-success text-success fs-10" title="PKR Received">
                                                PKR {{ number_format($currencySummary['PKR']['received_original'], 2) }}
                                            </span>
                                        @endif
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
                        <div class="card ledger-stat-card border-start border-4 {{ $closingBalance > 0 ? 'border-danger' : ($closingBalance < 0 ? 'border-success' : 'border-primary') }} p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label {{ $closingBalance > 0 ? 'text-danger' : ($closingBalance < 0 ? 'text-success' : 'text-primary') }}">
                                        {{ $closingBalance > 0 ? 'Closing Balance (Due Dr)' : ($closingBalance < 0 ? 'Advance Balance (Cr)' : 'Balance Cleared') }}
                                    </div>
                                    <div class="stat-value {{ $closingBalance > 0 ? 'text-danger' : ($closingBalance < 0 ? 'text-success' : 'text-primary') }}">
                                        PKR {{ number_format($absClosing, 2) }}
                                        <span class="badge {{ $closingBadgeClass }} fs-11 ms-1">{{ $closingTag }}</span>
                                    </div>
                                    @if(isset($closingBalanceSar) && abs($closingBalanceSar) > 0)
                                        <div class="mt-1">
                                            <span class="badge {{ $closingBalanceSar > 0 ? 'bg-soft-danger text-danger' : 'bg-soft-success text-success' }} border fs-10">
                                                SAR {{ number_format(abs($closingBalanceSar), 2) }} {{ $closingBalanceSar > 0 ? 'Dr (Due)' : 'Cr (Advance)' }}
                                            </span>
                                        </div>
                                    @endif
                                    <small class="text-muted fs-11 d-block mt-1">{{ $closingBalance > 0 ? 'Payable by Client' : ($closingBalance < 0 ? 'Advance credit with agency' : 'All accounts settled') }}</small>
                                </div>
                                <div class="icon-box {{ $closingBalance > 0 ? 'bg-soft-danger text-danger' : 'bg-soft-success text-success' }}">
                                    <i class="mdi mdi-scale-balance fs-22"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Multi-Currency Summary Card -->
                    <div class="col-6 col-md-3">
                        <div class="card ledger-stat-card border-start border-4 border-info p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label text-info">Currencies Breakdown</div>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @php $hasCurrencies = false; @endphp
                                        @foreach($currencySummary as $currCode => $currData)
                                            @if(($currData['count'] ?? 0) > 0)
                                                @php $hasCurrencies = true; @endphp
                                                <span class="badge {{ $currCode === 'SAR' ? 'bg-warning text-dark' : ($currCode === 'PKR' ? 'bg-success text-white' : 'bg-primary text-white') }} fs-11">
                                                    {{ $currCode }}: {{ number_format($currData['received_original'], 2) }}
                                                </span>
                                            @endif
                                        @endforeach
                                        @if(!$hasCurrencies)
                                            <span class="text-muted fs-12">No receipts yet</span>
                                        @endif
                                    </div>
                                    <small class="text-muted fs-11 d-block mt-1">{{ count($statementLines) }} Ledger Entries Recorded</small>
                                </div>
                                <div class="icon-box bg-soft-info text-info">
                                    <i class="mdi mdi-cash-multiple fs-22"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VIEW SWITCHER TABS (TWO TYPES OF VIEWS) -->
                <div class="card border-0 shadow-sm mb-3 no-print">
                    <div class="card-body p-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <ul class="nav nav-pills" id="customerLedgerTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold fs-13 px-3 py-1-5" id="c-statement-tab" data-bs-toggle="pill" data-bs-target="#c-view-statement" type="button" role="tab" aria-controls="c-view-statement" aria-selected="true">
                                    <i class="mdi mdi-scale-balance me-1"></i> View 1: Statement of Account (Debit / Credit / Balance View)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold fs-13 px-3 py-1-5" id="c-breakdown-tab" data-bs-toggle="pill" data-bs-target="#c-view-breakdown" type="button" role="tab" aria-controls="c-view-breakdown" aria-selected="false">
                                    <i class="mdi mdi-format-list-bulleted-type me-1"></i> View 2: Bookings & Services Breakdown View
                                </button>
                            </li>
                        </ul>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <input type="checkbox" class="btn-check" id="togglePkrCols" checked autocomplete="off" onchange="togglePkrVisibility(this.checked)">
                            <label class="btn-currency-toggle btn-pkr-toggle shadow-sm border" for="togglePkrCols" id="lblTogglePkr" title="Toggle PKR Columns in Ledger">
                                <i class="mdi mdi-check-circle fs-14" id="iconTogglePkr"></i> Show PKR
                            </label>

                            <input type="checkbox" class="btn-check" id="toggleSarCols" checked autocomplete="off" onchange="toggleSarVisibility(this.checked)">
                            <label class="btn-currency-toggle btn-sar-toggle shadow-sm border" for="toggleSarCols" id="lblToggleSar" title="Toggle SAR Columns in Ledger">
                                <i class="mdi mdi-check-circle fs-14" id="iconToggleSar"></i> Show SAR
                            </label>

                            <input type="checkbox" class="btn-check" id="toggleRoeCols" checked autocomplete="off" onchange="toggleRoeVisibility(this.checked)">
                            <label class="btn-currency-toggle btn-roe-toggle shadow-sm border" for="toggleRoeCols" id="lblToggleRoe" title="Toggle ROE Column in Ledger">
                                <i class="mdi mdi-check-circle fs-14" id="iconToggleRoe"></i> Show ROE
                            </label>

                            @if(($currencySummary['SAR']['received_original'] ?? 0) > 0)
                                <span class="badge bg-warning text-dark px-3 py-1-5 fs-12 col-sar shadow-sm border border-warning rounded-pill">
                                    <i class="mdi mdi-cash-multiple me-1"></i>SAR Total: {{ number_format($currencySummary['SAR']['received_original'], 2) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="customerLedgerTabContent">

                    <!-- VIEW 1: STATEMENT OF ACCOUNT (DEBIT / CREDIT / BALANCE) -->
                    <div class="tab-pane fade show active" id="c-view-statement" role="tabpanel" aria-labelledby="c-statement-tab">

                        <!-- CORE ACCOUNTING STATEMENT (DEBIT / CREDIT / RUNNING BALANCE TABLE) -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                                <h5 class="fs-14 fw-bold m-0 text-dark">
                                    <i class="mdi mdi-bank-transfer text-primary me-2"></i> Statement of Account (Ledger)
                                </h5>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill fs-12">
                                        {{ count($statementLines) + ($openingBalance != 0 ? 1 : 0) }} Rows
                                    </span>
                                </div>
                            </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table statement-table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="ps-3 align-middle" style="width: 35px;">#</th>
                                        <th rowspan="2" class="align-middle" style="width: 95px;">Date</th>
                                        <th rowspan="2" class="align-middle" style="width: 100px;">Voucher #</th>
                                        <th rowspan="2" class="align-middle" style="width: 110px;">Type</th>
                                        <th rowspan="2" class="align-middle" style="width: 120px;">Contra A/C</th>
                                        <th rowspan="2" class="align-middle">Particulars / Narration</th>
                                        <th rowspan="2" class="align-middle" style="width: 90px;">Method</th>
                                        <th colspan="3" class="text-center bg-soft-success text-success border-bottom-0 py-1 col-pkr">PKR (Pakistani Rupee)</th>
                                        <th rowspan="2" class="text-center align-middle col-roe" style="width: 75px;">ROE</th>
                                        <th colspan="3" class="text-center bg-soft-warning text-dark border-bottom-0 py-1 col-sar">SAR (Saudi Riyal)</th>
                                        <th rowspan="2" class="no-print text-center align-middle" style="width: 45px;">Proof</th>
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
                                    @if(($openingBalancePkr ?? $openingBalance) != 0 || ($openingBalanceSar ?? 0) != 0)
                                        <tr class="opening-row">
                                            <td class="ps-3 text-muted">—</td>
                                            <td>{{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M, Y') : '—' }}</td>
                                            <td><span class="badge bg-warning text-dark border">OPENING</span></td>
                                            <td><span class="badge bg-soft-warning text-dark">B/F Balance</span></td>
                                            <td><span class="badge bg-light text-dark border">Opening Balance</span></td>
                                            <td><strong>Opening Balance Brought Forward</strong></td>
                                            <td>—</td>
                                            {{-- PKR Columns: Debit, Credit, Balance --}}
                                            <td class="text-end fw-bold text-danger col-pkr">
                                                {{ ($openingBalancePkr ?? $openingBalance) > 0 ? number_format($openingBalancePkr ?? $openingBalance, 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-success col-pkr">
                                                {{ ($openingBalancePkr ?? $openingBalance) < 0 ? number_format(abs($openingBalancePkr ?? $openingBalance), 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-dark col-pkr">
                                                {{ number_format(abs($openingBalancePkr ?? $openingBalance), 2) }}
                                                <small class="badge {{ ($openingBalancePkr ?? $openingBalance) > 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 9px;">
                                                    {{ ($openingBalancePkr ?? $openingBalance) > 0 ? 'Dr' : 'Cr' }}
                                                </small>
                                            </td>
                                            {{-- ROE Column --}}
                                            <td class="text-center col-roe">—</td>
                                            {{-- SAR Columns: Debit, Credit, Balance --}}
                                            <td class="text-end fw-bold text-danger col-sar">
                                                {{ ($openingBalanceSar ?? 0) > 0 ? number_format($openingBalanceSar, 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-success col-sar">
                                                {{ ($openingBalanceSar ?? 0) < 0 ? number_format(abs($openingBalanceSar), 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-dark col-sar">
                                                {{ number_format(abs($openingBalanceSar ?? 0), 2) }}
                                                @if(($openingBalanceSar ?? 0) != 0)
                                                    <small class="badge {{ ($openingBalanceSar ?? 0) > 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 9px;">
                                                        {{ ($openingBalanceSar ?? 0) > 0 ? 'Dr' : 'Cr' }}
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
                                                <span class="badge bg-light text-dark fw-bold border">{{ $row['voucher_no'] }}</span>
                                                @if(!empty($row['ref_no']) && $row['ref_no'] !== $row['voucher_no'])
                                                    <small class="text-muted d-block">{{ $row['ref_no'] }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $row['badge_class'] }} px-2 py-1">
                                                    {{ $row['type_label'] }}
                                                </span>
                                            </td>
                                            <!-- Contra Account Column -->
                                            <td>
                                                @if(!empty($row['contra_account']) && $row['contra_account'] !== '—')
                                                    <span class="badge bg-soft-primary text-primary border border-primary border-opacity-25 px-2 py-1 text-wrap" style="max-width: 140px; font-size: 11px;">
                                                        <i class="mdi mdi-bank me-1"></i>{{ $row['contra_account'] }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $cleanDesc = preg_replace('/\s*[\(\[]\s*[^)\]]*@\s*ROE[^)\]]*[\)\]]/i', '', $row['description'] ?? '');
                                                    $cleanDesc = preg_replace('/\s*@\s*ROE\s*[0-9]+(\.[0-9]+)?/i', '', $cleanDesc);
                                                @endphp
                                                @if($row['booking_id'])
                                                    <a href="{{ route('booking.show', $row['booking_id']) }}" target="_blank" class="fw-semibold text-primary text-decoration-none">
                                                        {{ $cleanDesc }}
                                                    </a>
                                                @else
                                                    <span class="text-dark fw-medium">{{ $cleanDesc }}</span>
                                                @endif
                                                @if($client->id === 'all' && !empty($row['client_name']))
                                                    <small class="text-muted d-block"><i class="mdi mdi-account me-1"></i>{{ $row['client_name'] }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-secondary border px-2 py-1">
                                                    {{ $row['payment_method'] }}
                                                </span>
                                            </td>
                                            <!-- PKR Debit Column -->
                                            <td class="text-end fw-bold col-pkr {{ ($row['debit_pkr'] ?? $row['debit']) > 0 ? 'text-danger' : 'text-muted' }}">
                                                {{ ($row['debit_pkr'] ?? $row['debit']) > 0 ? number_format($row['debit_pkr'] ?? $row['debit'], 2) : '—' }}
                                            </td>
                                            <!-- PKR Credit Column -->
                                            <td class="text-end fw-bold col-pkr {{ ($row['credit_pkr'] ?? $row['credit']) > 0 ? 'text-success' : 'text-muted' }}">
                                                {{ ($row['credit_pkr'] ?? $row['credit']) > 0 ? number_format($row['credit_pkr'] ?? $row['credit'], 2) : '—' }}
                                            </td>
                                            <!-- Running Balance PKR Column -->
                                            <td class="text-end fw-bold text-dark col-pkr">
                                                {{ number_format(abs($row['running_balance_pkr'] ?? $row['running_balance']), 2) }}
                                                <span class="badge {{ ($row['running_balance_pkr'] ?? $row['running_balance']) > 0 ? 'bg-soft-danger text-danger' : (($row['running_balance_pkr'] ?? $row['running_balance']) < 0 ? 'bg-soft-success text-success' : 'bg-light text-muted') }} ms-1" style="font-size: 9px;">
                                                    {{ $row['balance_type'] }}
                                                </span>
                                            </td>
                                            <!-- ROE Column -->
                                            <td class="text-center col-roe">
                                                @if(!empty($row['exchange_rate']) && (float)$row['exchange_rate'] > 1)
                                                    <span class="badge bg-soft-info text-info border border-info border-opacity-25 px-2 py-1 font-monospace fs-11" title="Rate of Exchange">
                                                        {{ number_format((float)$row['exchange_rate'], 2) }}
                                                    </span>
                                                @elseif(!empty($row['currency']) && $row['currency'] === 'SAR')
                                                    <span class="badge bg-light text-muted border px-1 font-monospace fs-10">1.00</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <!-- SAR Debit Column -->
                                            <td class="text-end fw-bold col-sar {{ ($row['debit_sar'] ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">
                                                {{ ($row['debit_sar'] ?? 0) > 0 ? number_format($row['debit_sar'], 2) : '—' }}
                                            </td>
                                            <!-- SAR Credit Column -->
                                            <td class="text-end fw-bold col-sar {{ ($row['credit_sar'] ?? 0) > 0 ? 'text-success' : 'text-muted' }}">
                                                {{ ($row['credit_sar'] ?? 0) > 0 ? number_format($row['credit_sar'], 2) : '—' }}
                                            </td>
                                            <!-- Running Balance SAR Column -->
                                            <td class="text-end fw-bold text-dark col-sar">
                                                {{ number_format(abs($row['running_balance_sar'] ?? 0), 2) }}
                                                @if(($row['running_balance_sar'] ?? 0) != 0)
                                                    <span class="badge {{ ($row['running_balance_sar'] ?? 0) > 0 ? 'bg-soft-danger text-danger' : 'bg-soft-success text-success' }} ms-1" style="font-size: 9px;">
                                                        {{ $row['balance_type_sar'] ?? (($row['running_balance_sar'] ?? 0) > 0 ? 'Dr' : 'Cr') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <!-- Proof Column -->
                                            <td class="no-print text-center">
                                                @if(!empty($row['proof']))
                                                    <a href="{{ $row['proof'] }}" target="_blank" class="btn btn-xs btn-outline-info" title="View Proof">
                                                        <i class="mdi mdi-file-document-outline"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        @if(($openingBalancePkr ?? $openingBalance) == 0 && ($openingBalanceSar ?? 0) == 0)
                                            <tr>
                                                <td colspan="15" class="text-center py-5 text-muted">
                                                    <i class="mdi mdi-book-remove-outline fs-36 text-muted d-block mb-2"></i>
                                                    <h6 class="fw-bold mb-1">No Ledger Records Found</h6>
                                                    <p class="fs-12 mb-0">No booking charges or payment receipts recorded for this client in the selected date range.</p>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforelse
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr style="border-top: 2px solid #cbd5e1;">
                                        <td colspan="7" class="text-end text-uppercase fs-12">Period Totals:</td>
                                        <td class="text-end text-danger fs-13 col-pkr">PKR {{ number_format($periodDebitsPkr ?? $periodDebits, 2) }}</td>
                                        <td class="text-end text-success fs-13 col-pkr">PKR {{ number_format($periodCreditsPkr ?? $periodCredits, 2) }}</td>
                                        <td class="text-end text-dark fs-13 col-pkr">PKR {{ number_format(abs($closingBalancePkr ?? $closingBalance), 2) }}</td>
                                        <td class="text-center col-roe">—</td>
                                        <td class="text-end text-danger fs-13 col-sar">SAR {{ number_format($periodDebitsSar ?? 0, 2) }}</td>
                                        <td class="text-end text-success fs-13 col-sar">SAR {{ number_format($periodCreditsSar ?? 0, 2) }}</td>
                                        <td class="text-end text-dark fs-13 col-sar">SAR {{ number_format(abs($closingBalanceSar ?? 0), 2) }}</td>
                                        <td class="no-print"></td>
                                    </tr>
                                    @if(($currencySummary['SAR']['received_original'] ?? 0) > 0 || ($currencySummary['PKR']['received_original'] ?? 0) > 0)
                                        <tr style="background: #fdfefe; border-top: 1px dashed #cbd5e1;">
                                            <td colspan="7" class="text-end text-uppercase fs-11 text-muted">
                                                <i class="mdi mdi-cash-multiple me-1"></i> Received Currency Breakdown:
                                            </td>
                                            <td colspan="7" class="fs-12 text-end">
                                                @foreach($currencySummary as $currCode => $currData)
                                                    @if(($currData['count'] ?? 0) > 0)
                                                        <span class="badge {{ $currCode === 'SAR' ? 'bg-warning text-dark col-sar' : 'bg-light text-dark border col-pkr' }} ms-1 px-2 py-1">
                                                            {{ $currCode }}: {{ number_format($currData['received_original'], 2) }}
                                                            @if($currCode !== 'PKR')
                                                                <small class="text-muted">(Eq. PKR {{ number_format($currData['received_pkr'], 2) }})</small>
                                                            @endif
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td class="no-print"></td>
                                        </tr>
                                    @endif
                                    <tr style="background: #f1f5f9; border-top: 1px solid #cbd5e1;">
                                        <td colspan="7" class="text-end text-uppercase fs-13 text-dark">
                                            Net Closing Balance:
                                        </td>
                                        <td colspan="3" class="text-end fs-14 col-pkr {{ $closingBalance > 0 ? 'text-danger' : ($closingBalance < 0 ? 'text-success' : 'text-dark') }}">
                                            PKR {{ number_format($absClosing, 2) }} {{ $closingTag }}
                                        </td>
                                        <td class="text-center col-roe">—</td>
                                        <td colspan="3" class="text-end fs-14 col-sar {{ ($closingBalanceSar ?? 0) > 0 ? 'text-danger' : (($closingBalanceSar ?? 0) < 0 ? 'text-success' : 'text-dark') }}">
                                            @if(($closingBalanceSar ?? 0) != 0)
                                                SAR {{ number_format(abs($closingBalanceSar), 2) }} {{ $closingBalanceSar > 0 ? 'Dr' : 'Cr' }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="no-print"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                </div>{{-- /#c-view-statement --}}

                <!-- ======================================================= -->
                <!-- VIEW 2: BOOKINGS & SERVICES BREAKDOWN VIEW (CATEGORIZED) -->
                <!-- ======================================================= -->
                <div class="tab-pane fade" id="c-view-breakdown" role="tabpanel" aria-labelledby="c-breakdown-tab">

                    <!-- 1. BOOKINGS LIST TABLE -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                            <h6 class="fs-13 fw-bold m-0 text-dark">
                                <i class="mdi mdi-book-open-outline text-primary me-1"></i> Client Bookings (Invoiced & Billed)
                            </h6>
                            <span class="badge bg-soft-primary text-primary">{{ isset($bookings) ? $bookings->count() : 0 }} Bookings</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle fs-12 mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Booking #</th>
                                            <th>Booking Date</th>
                                            <th>Package</th>
                                            <th class="text-center">Pax</th>
                                            <th class="text-end col-pkr">Total Amount (PKR)</th>
                                            <th class="text-end col-sar">Total Amount (SAR)</th>
                                            <th class="text-end text-success">Advance / Received</th>
                                            <th class="text-end text-danger">Balance Due</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center no-print">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($bookings))
                                            @forelse($bookings as $b)
                                                @php
                                                    $cur = strtoupper($b->currency ?: 'PKR');
                                                    $roe = (float) ($b->flight_roe > 1 ? $b->flight_roe : 75);
                                                    $totAmt = (float) $b->total_amount;
                                                    $recAmt = (float) $b->total_received;
                                                    $balAmt = (float) $b->balance_amount;
                                                    if ($cur === 'SAR') {
                                                        $totSar = $totAmt;
                                                        $totPkr = round($totAmt * $roe, 2);
                                                        $recPkr = round($recAmt * $roe, 2);
                                                        $balPkr = round($balAmt * $roe, 2);
                                                    } else {
                                                        $totPkr = $totAmt;
                                                        $totSar = $roe > 0 ? round($totAmt / $roe, 2) : 0;
                                                        $recPkr = $recAmt;
                                                        $balPkr = $balAmt;
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <a href="{{ route('booking.show', $b->id) }}" target="_blank" class="fw-semibold text-primary">
                                                            #{{ $b->id }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            {{ $b->booking_date ? \Carbon\Carbon::parse($b->booking_date)->format('d M, Y') : ($b->created_at ? $b->created_at->format('d M, Y') : '—') }}
                                                        </span>
                                                    </td>
                                                    <td><strong>{{ $b->package_name ?: ($b->package->name ?? 'Custom Package') }}</strong></td>
                                                    <td class="text-center">{{ $b->no_of_pax ?? 1 }}</td>
                                                    <td class="text-end fw-bold text-dark col-pkr">PKR {{ number_format($totPkr, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-sar">{{ $totSar > 0 ? ('SAR ' . number_format($totSar, 2)) : '—' }}</td>
                                                    <td class="text-end fw-bold text-success">PKR {{ number_format($recPkr, 2) }}</td>
                                                    <td class="text-end fw-bold text-danger">PKR {{ number_format($balPkr, 2) }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-soft-info text-info text-capitalize">{{ $b->status ?? 'Active' }}</span>
                                                    </td>
                                                    <td class="text-center no-print">
                                                        <a href="{{ route('booking.show', $b->id) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                                            <i class="mdi mdi-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center py-3 text-muted">No bookings recorded for this client.</td>
                                                </tr>
                                            @endforelse
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 2. HOTEL STAYS BREAKDOWN -->
                    @php
                        $allHotels = collect();
                        if(isset($bookings)) {
                            foreach($bookings as $b) {
                                if($b->hotels) {
                                    foreach($b->hotels as $ht) {
                                        $ht->booking_parent = $b;
                                        $allHotels->push($ht);
                                    }
                                }
                            }
                        }
                    @endphp
                    @if($allHotels->count() > 0)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                            <h6 class="fs-13 fw-bold m-0 text-dark">
                                <i class="mdi mdi-office-building text-primary me-1"></i> Hotel Accommodations Breakdown
                            </h6>
                            <span class="badge bg-soft-primary text-primary">{{ $allHotels->count() }} Hotels</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle fs-12 mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Booking #</th>
                                            <th>Booking Date</th>
                                            <th>Hotel Name</th>
                                            <th>Location</th>
                                            <th>Room Type</th>
                                            <th class="text-center">Rooms</th>
                                            <th class="text-center">Nights</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th class="text-end">Selling Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allHotels as $h)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ route('booking.show', $h->booking_id) }}" target="_blank" class="fw-semibold text-primary">
                                                        #{{ $h->booking_id }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        {{ $h->booking_date ? \Carbon\Carbon::parse($h->booking_date)->format('d M, Y') : ($h->booking_parent->booking_date ? \Carbon\Carbon::parse($h->booking_parent->booking_date)->format('d M, Y') : '—') }}
                                                    </span>
                                                </td>
                                                <td><strong>{{ $h->hotel_name ?? '-' }}</strong></td>
                                                <td>{{ $h->location ? ucfirst($h->location) : '-' }}</td>
                                                <td>{{ $h->room_type ? ucfirst($h->room_type) : '-' }}</td>
                                                <td class="text-center">{{ $h->no_of_rooms ?? 1 }}</td>
                                                <td class="text-center">{{ $h->no_of_nights ?? 1 }}</td>
                                                <td>{{ $h->check_in ? \Carbon\Carbon::parse($h->check_in)->format('d M, Y') : '-' }}</td>
                                                <td>{{ $h->check_out ? \Carbon\Carbon::parse($h->check_out)->format('d M, Y') : '-' }}</td>
                                                <td class="text-end fw-bold text-dark">{{ number_format($h->selling_amount ?? 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 3. VISA APPLICATIONS BREAKDOWN -->
                    @php
                        $allVisas = collect();
                        if(isset($bookings)) {
                            foreach($bookings as $b) {
                                if($b->visas) {
                                    foreach($b->visas as $vs) {
                                        $vs->booking_parent = $b;
                                        $allVisas->push($vs);
                                    }
                                }
                            }
                        }
                    @endphp
                    @if($allVisas->count() > 0)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                            <h6 class="fs-13 fw-bold m-0 text-dark">
                                <i class="mdi mdi-card-account-details text-success me-1"></i> Visa Applications Breakdown
                            </h6>
                            <span class="badge bg-soft-success text-success">{{ $allVisas->count() }} Visas</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle fs-12 mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Booking #</th>
                                            <th>Booking Date</th>
                                            <th>Pilgrim Name</th>
                                            <th>Passport #</th>
                                            <th>Send To</th>
                                            <th>Status</th>
                                            <th class="text-end">Selling Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allVisas as $v)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ route('booking.show', $v->booking_id) }}" target="_blank" class="fw-semibold text-primary">
                                                        #{{ $v->booking_id }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        {{ $v->booking_date ? \Carbon\Carbon::parse($v->booking_date)->format('d M, Y') : ($v->booking_parent->booking_date ? \Carbon\Carbon::parse($v->booking_parent->booking_date)->format('d M, Y') : '—') }}
                                                    </span>
                                                </td>
                                                <td>{{ $v->given_name ?? '-' }}</td>
                                                <td><strong>{{ $v->passport_number ?? '-' }}</strong></td>
                                                <td>{{ $v->send_to ?? '-' }}</td>
                                                <td><span class="badge bg-soft-info text-info">{{ ucfirst($v->status ?? 'pending') }}</span></td>
                                                <td class="text-end fw-bold text-dark">{{ number_format($v->selling_amount ?? 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 4. TRANSPORTS BREAKDOWN -->
                    @php
                        $allTransports = collect();
                        if(isset($bookings)) {
                            foreach($bookings as $b) {
                                if($b->transports) {
                                    foreach($b->transports as $tr) {
                                        $tr->booking_parent = $b;
                                        $allTransports->push($tr);
                                    }
                                }
                            }
                        }
                    @endphp
                    @if($allTransports->count() > 0)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                            <h6 class="fs-13 fw-bold m-0 text-dark">
                                <i class="mdi mdi-bus text-warning me-1"></i> Transport Bookings Breakdown
                            </h6>
                            <span class="badge bg-soft-warning text-warning">{{ $allTransports->count() }} Transports</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle fs-12 mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Booking #</th>
                                            <th>Booking Date</th>
                                            <th>Route</th>
                                            <th>Vehicle / Type</th>
                                            <th>Travel Date</th>
                                            <th class="text-end">Selling Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allTransports as $t)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ route('booking.show', $t->booking_id) }}" target="_blank" class="fw-semibold text-primary">
                                                        #{{ $t->booking_id }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        {{ $t->booking_date ? \Carbon\Carbon::parse($t->booking_date)->format('d M, Y') : ($t->booking_parent->booking_date ? \Carbon\Carbon::parse($t->booking_parent->booking_date)->format('d M, Y') : '—') }}
                                                    </span>
                                                </td>
                                                <td><strong>{{ $t->route ?? '-' }}</strong></td>
                                                <td>{{ $t->vehicle ?? ($t->transport_type ?? '-') }}</td>
                                                <td>{{ $t->travel_date ? \Carbon\Carbon::parse($t->travel_date)->format('d M, Y') : '-' }}</td>
                                                <td class="text-end fw-bold text-dark">{{ number_format($t->selling_amount ?? 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>{{-- /#c-view-breakdown --}}

            </div>{{-- /#customerLedgerTabContent --}}

            </div>
        </div>
    </div>

    <script>
        function exportExcelStatement() {
            if (typeof XLSX === 'undefined') {
                alert('Excel export library could not be loaded.');
                return;
            }

            const wb = XLSX.utils.book_new();

            // 1. Summary Sheet
            const summaryData = [
                ["CLIENT STATEMENT OF ACCOUNT (LEDGER)"],
                ["Client Name:", "{{ addslashes($client->name) }}"],
                ["Statement Date:", new Date().toLocaleString()],
                @if($fromDate || $toDate)
                ["Period:", "{{ $fromDate ?? 'Start' }} to {{ $toDate ?? 'Today' }}"],
                @endif
                [],
                ["FINANCIAL SUMMARY", ""],
                ["Total Debits (PKR):", {{ (float) $totalDebits }}],
                ["Total Credits (PKR):", {{ (float) $totalCredits }}],
                ["Closing Balance (PKR):", {{ (float) $closingBalance }}],
                ["Balance Status:", "{{ $closingTag }}"],
                @if(($totalDebitsSar ?? 0) > 0 || ($totalCreditsSar ?? 0) > 0)
                ["Total Debits (SAR):", {{ (float) ($totalDebitsSar ?? 0) }}],
                ["Total Credits (SAR):", {{ (float) ($totalCreditsSar ?? 0) }}],
                ["Closing Balance (SAR):", {{ (float) ($closingBalanceSar ?? 0) }}],
                @endif
            ];
            const ws1 = XLSX.utils.aoa_to_sheet(summaryData);
            XLSX.utils.book_append_sheet(wb, ws1, "Summary");

            // 2. Ledger Entries Sheet
            const ledgerHeaders = [
                "#",
                "Date",
                "Voucher #",
                "Type",
                "Contra A/C",
                "Particulars / Description",
                "Payment Method",
                "Currency",
                "Original Amount",
                "ROE",
                "Debit (Dr) PKR",
                "Credit (Cr) PKR",
                "Debit (Dr) SAR",
                "Credit (Cr) SAR",
                "Running Balance PKR",
                "Running Balance SAR"
            ];

            const ledgerRows = [];

            @if(($openingBalancePkr ?? $openingBalance) != 0 || ($openingBalanceSar ?? 0) != 0)
            ledgerRows.push([
                "—",
                "{{ $fromDate ?? '—' }}",
                "OPENING",
                "Opening Balance",
                "—",
                "Opening Balance Brought Forward",
                "—",
                "PKR",
                {{ abs($openingBalancePkr ?? $openingBalance) }},
                1.00,
                {{ ($openingBalancePkr ?? $openingBalance) > 0 ? ($openingBalancePkr ?? $openingBalance) : 0 }},
                {{ ($openingBalancePkr ?? $openingBalance) < 0 ? abs($openingBalancePkr ?? $openingBalance) : 0 }},
                {{ ($openingBalanceSar ?? 0) > 0 ? ($openingBalanceSar ?? 0) : 0 }},
                {{ ($openingBalanceSar ?? 0) < 0 ? abs($openingBalanceSar ?? 0) : 0 }},
                {{ abs($openingBalancePkr ?? $openingBalance) }},
                {{ abs($openingBalanceSar ?? 0) }}
            ]);
            @endif

            @foreach($statementLines as $row)
            @php
                $cleanRowDesc = preg_replace('/\s*[\(\[]\s*[^)\]]*@\s*ROE[^)\]]*[\)\]]/i', '', $row['description'] ?? '');
                $cleanRowDesc = preg_replace('/\s*@\s*ROE\s*[0-9]+(\.[0-9]+)?/i', '', $cleanRowDesc);
            @endphp
            ledgerRows.push([
                {{ $loop->iteration }},
                "{{ $row['date'] }}",
                "{{ addslashes($row['voucher_no']) }}",
                "{{ addslashes($row['type_label']) }}",
                "{{ addslashes($row['contra_account'] ?? '—') }}",
                "{{ addslashes($cleanRowDesc) }}",
                "{{ addslashes($row['payment_method']) }}",
                "{{ $row['currency'] ?? 'PKR' }}",
                {{ (float) ($row['original_amount'] ?? ($row['credit_pkr'] > 0 ? $row['credit_pkr'] : $row['debit_pkr'])) }},
                {{ (float) ($row['debit_pkr'] ?? $row['debit']) }},
                {{ (float) ($row['credit_pkr'] ?? $row['credit']) }},
                {{ (float) abs($row['running_balance_pkr'] ?? $row['running_balance']) }},
                {{ (float) ($row['exchange_rate'] ?? 1) }},
                {{ (float) ($row['debit_sar'] ?? 0) }},
                {{ (float) ($row['credit_sar'] ?? 0) }},
                {{ (float) abs($row['running_balance_sar'] ?? 0) }}
            ]);
            @endforeach

            ledgerRows.push([]);
            ledgerRows.push([
                "TOTALS", "", "", "", "", "", "Period Totals:", "", "",
                {{ (float) ($periodDebitsPkr ?? $periodDebits) }},
                {{ (float) ($periodCreditsPkr ?? $periodCredits) }},
                {{ (float) abs($closingBalancePkr ?? $closingBalance) }},
                "",
                {{ (float) ($periodDebitsSar ?? 0) }},
                {{ (float) ($periodCreditsSar ?? 0) }},
                {{ (float) abs($closingBalanceSar ?? 0) }}
            ]);

            const showRoe = document.getElementById('toggleRoeCols') ? document.getElementById('toggleRoeCols').checked : true;
            const showPkr = document.getElementById('togglePkrCols') ? document.getElementById('togglePkrCols').checked : true;
            const showSar = document.getElementById('toggleSarCols') ? document.getElementById('toggleSarCols').checked : true;

            // Dynamic Excel Headers
            let finalHeaders = ["#", "Date", "Voucher #", "Type", "Contra A/C", "Particulars / Description", "Payment Method", "Currency", "Original Amount"];
            if (showRoe) finalHeaders.push("ROE");
            if (showPkr) finalHeaders.push("Debit (Dr) PKR", "Credit (Cr) PKR", "Running Balance PKR");
            if (showSar) finalHeaders.push("Debit (Dr) SAR", "Credit (Cr) SAR", "Running Balance SAR");

            const finalLedgerRows = ledgerRows.map(row => {
                if (!row.length) return [];
                if (row[0] === "TOTALS") {
                    let tr = ["TOTALS", "", "", "", "", "", "Period Totals:", "", ""];
                    if (showRoe) tr.push("");
                    if (showPkr) tr.push(row[9], row[10], row[11]);
                    if (showSar) tr.push(row[13], row[14], row[15]);
                    return tr;
                }
                // row: 0:#, 1:date, 2:voucher, 3:type, 4:contra, 5:desc, 6:method, 7:curr, 8:orig_amt, 9:pkr_dr, 10:pkr_cr, 11:pkr_bal, 12:roe, 13:sar_dr, 14:sar_cr, 15:sar_bal
                let r = [row[0], row[1], row[2], row[3], row[4], row[5], row[6], row[7], row[8]];
                if (showRoe) r.push(row[12]);
                if (showPkr) r.push(row[9], row[10], row[11]);
                if (showSar) r.push(row[13], row[14], row[15]);
                return r;
            });

            const ws2 = XLSX.utils.aoa_to_sheet([finalHeaders, ...finalLedgerRows]);
            XLSX.utils.book_append_sheet(wb, ws2, "Statement");

            XLSX.writeFile(wb, "Client_Ledger_{{ Str::slug($client->name) }}_{{ date('Ymd_His') }}.xlsx");
        }

        function exportPDFStatement() {
            if (typeof window.jspdf === 'undefined' || typeof window.jspdf.jsPDF === 'undefined') {
                window.print();
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
            const pageW = doc.internal.pageSize.getWidth();

            // Header Banner
            doc.setFillColor(30, 41, 59);
            doc.rect(0, 0, pageW, 22, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(15);
            doc.setFont(undefined, 'bold');
            doc.text("DUA TRAVEL & TOURS — CLIENT STATEMENT OF ACCOUNT (LEDGER)", 14, 10);
            doc.setFontSize(8.5);
            doc.setFont(undefined, 'normal');
            doc.text("Client: {{ addslashes($client->name) }} | Statement Date: " + new Date().toLocaleString(), 14, 17);

            // Summary Info
            doc.setTextColor(30, 30, 30);
            doc.setFontSize(8.5);
            doc.setFont(undefined, 'bold');
            doc.text("PKR — Debits: {{ number_format($totalDebits, 2) }} | Credits: {{ number_format($totalCredits, 2) }} | Net: {{ number_format($absClosing, 2) }} ({{ $closingTag }})" +
                     @if(($totalCreditsSar ?? 0) > 0 || ($totalDebitsSar ?? 0) > 0)
                     "   |   SAR — Debits: {{ number_format($totalDebitsSar ?? 0, 2) }} | Credits: {{ number_format($totalCreditsSar ?? 0, 2) }} | Net: {{ number_format(abs($closingBalanceSar ?? 0), 2) }}"
                     @else
                     ""
                     @endif
                     , 14, 28);

            const tableRows = [];

            @if(($openingBalancePkr ?? $openingBalance) != 0 || ($openingBalanceSar ?? 0) != 0)
            tableRows.push([
                "—",
                "{{ $fromDate ?? '—' }}",
                "OPENING",
                "Opening Balance",
                "—",
                "Opening Balance Brought Forward",
                "—",
                "{{ ($openingBalancePkr ?? $openingBalance) > 0 ? number_format($openingBalancePkr ?? $openingBalance, 2) : '—' }}",
                "{{ ($openingBalancePkr ?? $openingBalance) < 0 ? number_format(abs($openingBalancePkr ?? $openingBalance), 2) : '—' }}",
                "{{ number_format(abs($openingBalancePkr ?? $openingBalance), 2) }}",
                "—",
                "{{ ($openingBalanceSar ?? 0) > 0 ? number_format($openingBalanceSar, 2) : '—' }}",
                "{{ ($openingBalanceSar ?? 0) < 0 ? number_format(abs($openingBalanceSar), 2) : '—' }}",
                "{{ number_format(abs($openingBalanceSar ?? 0), 2) }}"
            ]);
            @endif

            @foreach($statementLines as $row)
            @php
                $cleanRowDesc = preg_replace('/\s*[\(\[]\s*[^)\]]*@\s*ROE[^)\]]*[\)\]]/i', '', $row['description'] ?? '');
                $cleanRowDesc = preg_replace('/\s*@\s*ROE\s*[0-9]+(\.[0-9]+)?/i', '', $cleanRowDesc);
            @endphp
            tableRows.push([
                "{{ $loop->iteration }}",
                "{{ \Carbon\Carbon::parse($row['date'])->format('d M, Y') }}",
                "{{ addslashes($row['voucher_no']) }}",
                "{{ addslashes($row['type_label']) }}",
                "{{ addslashes($row['contra_account'] ?? '—') }}",
                "{{ addslashes($cleanRowDesc) }}",
                "{{ addslashes($row['payment_method']) }}",
                "{{ ($row['debit_pkr'] ?? $row['debit']) > 0 ? number_format($row['debit_pkr'] ?? $row['debit'], 2) : '—' }}",
                "{{ ($row['credit_pkr'] ?? $row['credit']) > 0 ? number_format($row['credit_pkr'] ?? $row['credit'], 2) : '—' }}",
                "{{ number_format(abs($row['running_balance_pkr'] ?? $row['running_balance']), 2) }} {{ $row['balance_type'] }}",
                "{{ !empty($row['exchange_rate']) && (float)$row['exchange_rate'] > 1 ? number_format((float)$row['exchange_rate'], 2) : '—' }}",
                "{{ ($row['debit_sar'] ?? 0) > 0 ? number_format($row['debit_sar'], 2) : '—' }}",
                "{{ ($row['credit_sar'] ?? 0) > 0 ? number_format($row['credit_sar'], 2) : '—' }}",
                "{{ number_format(abs($row['running_balance_sar'] ?? 0), 2) }} {{ ($row['running_balance_sar'] ?? 0) != 0 ? ($row['balance_type_sar'] ?? '') : '' }}"
            ]);
            @endforeach

            tableRows.push([
                "", "", "", "", "", "TOTALS:", "",
                "PKR {{ number_format($periodDebitsPkr ?? $periodDebits, 2) }}",
                "PKR {{ number_format($periodCreditsPkr ?? $periodCredits, 2) }}",
                "PKR {{ number_format(abs($closingBalancePkr ?? $closingBalance), 2) }}",
                "—",
                "SAR {{ number_format($periodDebitsSar ?? 0, 2) }}",
                "SAR {{ number_format($periodCreditsSar ?? 0, 2) }}",
                "SAR {{ number_format(abs($closingBalanceSar ?? 0), 2) }}"
            ]);

            const showRoePdf = document.getElementById('toggleRoeCols') ? document.getElementById('toggleRoeCols').checked : true;
            const showPkrPdf = document.getElementById('togglePkrCols') ? document.getElementById('togglePkrCols').checked : true;
            const showSarPdf = document.getElementById('toggleSarCols') ? document.getElementById('toggleSarCols').checked : true;

            let pdfHead = ["#", "Date", "Voucher", "Type", "Contra A/C", "Description", "Method"];
            if (showPkrPdf) pdfHead.push("Dr (PKR)", "Cr (PKR)", "Bal (PKR)");
            if (showRoePdf) pdfHead.push("ROE");
            if (showSarPdf) pdfHead.push("Dr (SAR)", "Cr (SAR)", "Bal (SAR)");

            let colStyles = {};
            let colIdx = 7;
            if (showPkrPdf) {
                colStyles[colIdx] = { halign: 'right', textColor: [180, 0, 0] };
                colStyles[colIdx + 1] = { halign: 'right', textColor: [0, 130, 0] };
                colStyles[colIdx + 2] = { halign: 'right', fontStyle: 'bold' };
                colIdx += 3;
            }
            if (showRoePdf) {
                colStyles[colIdx] = { halign: 'center' };
                colIdx += 1;
            }
            if (showSarPdf) {
                colStyles[colIdx] = { halign: 'right', textColor: [180, 0, 0] };
                colStyles[colIdx + 1] = { halign: 'right', textColor: [0, 130, 0] };
                colStyles[colIdx + 2] = { halign: 'right', fontStyle: 'bold' };
            }

            const finalPdfRows = tableRows.map(row => {
                let r = [row[0], row[1], row[2], row[3], row[4], row[5], row[6]];
                if (showPkrPdf) r.push(row[7], row[8], row[9]);
                if (showRoePdf) r.push(row[10]);
                if (showSarPdf) r.push(row[11], row[12], row[13]);
                return r;
            });

            doc.autoTable({
                startY: 32,
                head: [pdfHead],
                body: finalPdfRows,
                theme: 'striped',
                headStyles: { fillColor: [30, 41, 59], textColor: 255, fontSize: 7.5, fontStyle: 'bold' },
                bodyStyles: { fontSize: 7 },
                columnStyles: colStyles,
                margin: { left: 10, right: 10 }
            });

            doc.save("Client_Ledger_{{ Str::slug($client->name) }}_{{ date('Ymd') }}.pdf");
        }

        function updateRoeButtonUI(show) {
            const icon = document.getElementById('iconToggleRoe');
            if (icon) {
                icon.className = show ? 'mdi mdi-check-circle fs-14' : 'mdi mdi-eye-off-outline fs-14';
            }
        }

        function toggleRoeVisibility(show) {
            document.querySelectorAll('.col-roe').forEach(el => {
                if (show) {
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            });
            updateRoeButtonUI(show);
            try {
                localStorage.setItem('ledger_show_roe', show ? '1' : '0');
            } catch(e) {}
        }

        function updatePkrButtonUI(show) {
            const icon = document.getElementById('iconTogglePkr');
            if (icon) {
                icon.className = show ? 'mdi mdi-check-circle fs-14' : 'mdi mdi-eye-off-outline fs-14';
            }
        }

        function updateSarButtonUI(show) {
            const icon = document.getElementById('iconToggleSar');
            if (icon) {
                icon.className = show ? 'mdi mdi-check-circle fs-14' : 'mdi mdi-eye-off-outline fs-14';
            }
        }

        function togglePkrVisibility(show) {
            document.querySelectorAll('.col-pkr').forEach(el => {
                if (show) {
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            });
            updatePkrButtonUI(show);
            try {
                localStorage.setItem('ledger_show_pkr', show ? '1' : '0');
            } catch(e) {}
        }

        function toggleSarVisibility(show) {
            document.querySelectorAll('.col-sar').forEach(el => {
                if (show) {
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            });
            updateSarButtonUI(show);
            try {
                localStorage.setItem('ledger_show_sar', show ? '1' : '0');
            } catch(e) {}
        }

        document.addEventListener('DOMContentLoaded', function() {
            try {
                const savedPkr = localStorage.getItem('ledger_show_pkr');
                const chkPkr = document.getElementById('togglePkrCols');
                if (savedPkr !== null && chkPkr) {
                    chkPkr.checked = (savedPkr === '1');
                    togglePkrVisibility(chkPkr.checked);
                } else if (chkPkr) {
                    updatePkrButtonUI(chkPkr.checked);
                }

                const savedSar = localStorage.getItem('ledger_show_sar');
                const chkSar = document.getElementById('toggleSarCols');
                if (savedSar !== null && chkSar) {
                    chkSar.checked = (savedSar === '1');
                    toggleSarVisibility(chkSar.checked);
                } else if (chkSar) {
                    updateSarButtonUI(chkSar.checked);
                }

                const savedRoe = localStorage.getItem('ledger_show_roe');
                const chkRoe = document.getElementById('toggleRoeCols');
                if (savedRoe !== null && chkRoe) {
                    chkRoe.checked = (savedRoe === '1');
                    toggleRoeVisibility(chkRoe.checked);
                } else if (chkRoe) {
                    updateRoeButtonUI(chkRoe.checked);
                }
            } catch(e) {}
        });
    </script>
@endsection
