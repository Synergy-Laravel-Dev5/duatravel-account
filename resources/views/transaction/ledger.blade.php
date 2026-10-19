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
                        <p class="text-muted mb-0 fs-12 text-uppercase fw-semibold">ACCOUNT STATEMENT / KHATA</p>
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
                                    <small class="text-muted fs-11">{{ $bookingsCount }} Bookings billed</small>
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
                                        @if(($currencySummary['SAR']['received_original'] ?? 0) > 0)
                                            <span class="badge bg-warning text-dark fs-10" title="SAR Received">
                                                SAR {{ number_format($currencySummary['SAR']['received_original'], 2) }}
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
                                    <small class="text-muted fs-11">{{ $closingBalance > 0 ? 'Payable by Client' : ($closingBalance < 0 ? 'Advance credit with agency' : 'All accounts settled') }}</small>
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

                <!-- CORE ACCOUNTING STATEMENT (DEBIT / CREDIT / RUNNING BALANCE TABLE) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="fs-14 fw-bold m-0 text-dark">
                            <i class="mdi mdi-bank-transfer text-primary me-2"></i> Statement of Account (Khata / Ledger)
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            @if(($currencySummary['SAR']['received_original'] ?? 0) > 0)
                                <span class="badge bg-warning text-dark px-2 py-1 fs-12">
                                    <i class="mdi mdi-cash-multiple me-1"></i>SAR Total: {{ number_format($currencySummary['SAR']['received_original'], 2) }}
                                </span>
                            @endif
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
                                        <th class="ps-3" style="width: 35px;">#</th>
                                        <th style="width: 100px;">Date</th>
                                        <th style="width: 105px;">Voucher #</th>
                                        <th style="width: 120px;">Type</th>
                                        <th style="width: 130px;">Contra A/C</th>
                                        <th>Particulars / Narration</th>
                                        <th style="width: 95px;">Method</th>
                                        <th class="text-end text-danger" style="width: 130px;">Debit (Dr) Charges</th>
                                        <th class="text-end text-success" style="width: 140px;">Credit (Cr) Received</th>
                                        <th class="text-end text-primary" style="width: 140px;">Running Balance</th>
                                        <th class="no-print text-center" style="width: 50px;">Proof</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Opening Balance Row if date filter applied and balance exists --}}
                                    @if($openingBalance != 0)
                                        <tr class="opening-row">
                                            <td class="ps-3 text-muted">—</td>
                                            <td>{{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M, Y') : '—' }}</td>
                                            <td><span class="badge bg-warning text-dark border">OPENING</span></td>
                                            <td><span class="badge bg-soft-warning text-dark">B/F Balance</span></td>
                                            <td><span class="badge bg-light text-dark border">Opening Balance</span></td>
                                            <td><strong>Opening Balance Brought Forward</strong></td>
                                            <td>—</td>
                                            <td class="text-end fw-bold text-danger">
                                                {{ $openingBalance > 0 ? number_format($openingBalance, 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                {{ $openingBalance < 0 ? number_format(abs($openingBalance), 2) : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-dark">
                                                {{ number_format(abs($openingBalance), 2) }}
                                                <small class="badge {{ $openingBalance > 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 10px;">
                                                    {{ $openingBalance > 0 ? 'Dr' : 'Cr' }}
                                                </small>
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
                                                @if($row['booking_id'])
                                                    <a href="{{ route('booking.show', $row['booking_id']) }}" target="_blank" class="fw-semibold text-primary text-decoration-none">
                                                        {{ $row['description'] }}
                                                    </a>
                                                @else
                                                    <span class="text-dark fw-medium">{{ $row['description'] }}</span>
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
                                            <!-- Debit Column -->
                                            <td class="text-end fw-bold {{ $row['debit'] > 0 ? 'text-danger' : 'text-muted' }}">
                                                @if($row['debit'] > 0)
                                                    <div>PKR {{ number_format($row['debit'], 2) }}</div>
                                                    @if(!empty($row['currency']) && $row['currency'] !== 'PKR')
                                                        <div class="mt-1">
                                                            <span class="badge bg-soft-warning text-dark border px-1" style="font-size: 10px;">
                                                                {{ $row['currency'] }} {{ number_format($row['original_amount'], 2) }}
                                                            </span>
                                                            <small class="text-muted d-block fw-normal" style="font-size: 9px;">@ ROE {{ number_format($row['exchange_rate'], 2) }}</small>
                                                        </div>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <!-- Credit Column (with foreign currency & ROE badge) -->
                                            <td class="text-end fw-bold {{ $row['credit'] > 0 ? 'text-success' : 'text-muted' }}">
                                                @if($row['credit'] > 0)
                                                    <div>PKR {{ number_format($row['credit'], 2) }}</div>
                                                    @if(!empty($row['currency']) && $row['currency'] !== 'PKR')
                                                        <div class="mt-1">
                                                            <span class="badge bg-soft-warning text-dark border px-1" style="font-size: 10px;">
                                                                <i class="mdi mdi-cash-multiple me-1"></i>{{ $row['currency'] }} {{ number_format($row['original_amount'], 2) }}
                                                            </span>
                                                            <small class="text-muted d-block fw-normal" style="font-size: 9px;">@ ROE {{ number_format($row['exchange_rate'], 2) }}</small>
                                                        </div>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <!-- Running Balance Column -->
                                            <td class="text-end fw-bold text-dark">
                                                PKR {{ number_format(abs($row['running_balance']), 2) }}
                                                <span class="badge {{ $row['running_balance'] > 0 ? 'bg-soft-danger text-danger' : ($row['running_balance'] < 0 ? 'bg-soft-success text-success' : 'bg-light text-muted') }} ms-1" style="font-size: 10px;">
                                                    {{ $row['balance_type'] }}
                                                </span>
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
                                        @if($openingBalance == 0)
                                            <tr>
                                                <td colspan="11" class="text-center py-5 text-muted">
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
                                        <td class="text-end text-danger fs-14">PKR {{ number_format($periodDebits, 2) }}</td>
                                        <td class="text-end text-success fs-14">PKR {{ number_format($periodCredits, 2) }}</td>
                                        <td colspan="2" class="text-end text-dark fs-13"></td>
                                    </tr>
                                    @if(($currencySummary['SAR']['received_original'] ?? 0) > 0 || ($currencySummary['PKR']['received_original'] ?? 0) > 0)
                                        <tr style="background: #fdfefe; border-top: 1px dashed #cbd5e1;">
                                            <td colspan="7" class="text-end text-uppercase fs-11 text-muted">
                                                <i class="mdi mdi-cash-multiple me-1"></i> Received Currency Breakdown:
                                            </td>
                                            <td colspan="3" class="fs-12 text-end">
                                                @foreach($currencySummary as $currCode => $currData)
                                                    @if(($currData['count'] ?? 0) > 0)
                                                        <span class="badge {{ $currCode === 'SAR' ? 'bg-warning text-dark' : 'bg-light text-dark border' }} ms-1 px-2 py-1">
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
                                        <td colspan="2" class="text-center fs-12 text-muted">
                                            (Total Billed: PKR {{ number_format($totalDebits, 2) }} | Total Received: PKR {{ number_format($totalCredits, 2) }})
                                        </td>
                                        <td class="text-end fs-15 {{ $closingBalance > 0 ? 'text-danger' : ($closingBalance < 0 ? 'text-success' : 'text-dark') }}">
                                            PKR {{ number_format($absClosing, 2) }} {{ $closingTag }}
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
                ["Total Debits (Charges / Billed):", {{ (float) $totalDebits }}],
                ["Total Credits (Paid / Received):", {{ (float) $totalCredits }}],
                ["Closing Balance:", {{ (float) $closingBalance }}],
                ["Balance Status:", "{{ $closingTag }}"],
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
                "Running Balance PKR",
                "Balance Type"
            ];

            const ledgerRows = [];

            @if($openingBalance != 0)
            ledgerRows.push([
                "—",
                "{{ $fromDate ?? '—' }}",
                "OPENING",
                "Opening Balance",
                "—",
                "Opening Balance Brought Forward",
                "—",
                "PKR",
                {{ abs($openingBalance) }},
                1.00,
                {{ $openingBalance > 0 ? $openingBalance : 0 }},
                {{ $openingBalance < 0 ? abs($openingBalance) : 0 }},
                {{ abs($openingBalance) }},
                "{{ $openingBalance > 0 ? 'Dr' : 'Cr' }}"
            ]);
            @endif

            @foreach($statementLines as $row)
            ledgerRows.push([
                {{ $loop->iteration }},
                "{{ $row['date'] }}",
                "{{ addslashes($row['voucher_no']) }}",
                "{{ addslashes($row['type_label']) }}",
                "{{ addslashes($row['contra_account'] ?? '—') }}",
                "{{ addslashes($row['description']) }}",
                "{{ addslashes($row['payment_method']) }}",
                "{{ $row['currency'] ?? 'PKR' }}",
                {{ (float) ($row['original_amount'] ?? ($row['credit'] > 0 ? $row['credit'] : $row['debit'])) }},
                {{ (float) ($row['exchange_rate'] ?? 1) }},
                {{ (float) $row['debit'] }},
                {{ (float) $row['credit'] }},
                {{ (float) abs($row['running_balance']) }},
                "{{ $row['balance_type'] }}"
            ]);
            @endforeach

            ledgerRows.push([]);
            ledgerRows.push([
                "TOTALS", "", "", "", "", "", "Period Totals:", "", "", "",
                {{ (float) $periodDebits }},
                {{ (float) $periodCredits }},
                {{ (float) abs($closingBalance) }},
                "{{ $closingTag }}"
            ]);

            const ws2 = XLSX.utils.aoa_to_sheet([ledgerHeaders, ...ledgerRows]);
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
            doc.setFontSize(9);
            doc.setFont(undefined, 'bold');
            doc.text("Total Debits: PKR {{ number_format($totalDebits, 2) }}  |  Total Credits: PKR {{ number_format($totalCredits, 2) }}  |  Net Closing: PKR {{ number_format($absClosing, 2) }} ({{ $closingTag }})", 14, 28);

            const tableRows = [];

            @if($openingBalance != 0)
            tableRows.push([
                "—",
                "{{ $fromDate ?? '—' }}",
                "OPENING",
                "Opening Balance",
                "—",
                "Opening Balance Brought Forward",
                "—",
                "{{ $openingBalance > 0 ? number_format($openingBalance, 2) : '—' }}",
                "{{ $openingBalance < 0 ? number_format(abs($openingBalance), 2) : '—' }}",
                "{{ number_format(abs($openingBalance), 2) }} {{ $openingBalance > 0 ? 'Dr' : 'Cr' }}"
            ]);
            @endif

            @foreach($statementLines as $row)
            tableRows.push([
                "{{ $loop->iteration }}",
                "{{ \Carbon\Carbon::parse($row['date'])->format('d M, Y') }}",
                "{{ addslashes($row['voucher_no']) }}",
                "{{ addslashes($row['type_label']) }}",
                "{{ addslashes($row['contra_account'] ?? '—') }}",
                "{{ addslashes($row['description']) }}",
                "{{ addslashes($row['payment_method']) }}",
                "{{ $row['debit'] > 0 ? (number_format($row['debit'], 2) . (!empty($row['currency']) && $row['currency'] !== 'PKR' ? ' [' . $row['currency'] . ' ' . number_format($row['original_amount'], 2) . ']' : '')) : '—' }}",
                "{{ $row['credit'] > 0 ? (number_format($row['credit'], 2) . (!empty($row['currency']) && $row['currency'] !== 'PKR' ? ' [' . $row['currency'] . ' ' . number_format($row['original_amount'], 2) . ' @ ' . number_format($row['exchange_rate'], 2) . ']' : '')) : '—' }}",
                "{{ number_format(abs($row['running_balance']), 2) }} {{ $row['balance_type'] }}"
            ]);
            @endforeach

            tableRows.push([
                "", "", "", "", "", "TOTALS:", "",
                "PKR {{ number_format($periodDebits, 2) }}",
                "PKR {{ number_format($periodCredits, 2) }}",
                "PKR {{ number_format($absClosing, 2) }} {{ $closingTag }}"
            ]);

            doc.autoTable({
                startY: 32,
                head: [["#", "Date", "Voucher", "Type", "Contra A/C", "Particulars / Narration", "Method", "Debit (Dr) PKR", "Credit (Cr) PKR", "Running Balance"]],
                body: tableRows,
                theme: 'striped',
                headStyles: { fillColor: [30, 41, 59], textColor: 255, fontSize: 8, fontStyle: 'bold' },
                bodyStyles: { fontSize: 7.5 },
                columnStyles: {
                    7: { halign: 'right', textColor: [180, 0, 0] },
                    8: { halign: 'right', textColor: [0, 130, 0] },
                    9: { halign: 'right', fontStyle: 'bold' }
                },
                margin: { left: 14, right: 14 }
            });

            doc.save("Client_Ledger_{{ Str::slug($client->name) }}_{{ date('Ymd') }}.pdf");
        }
    </script>
@endsection
