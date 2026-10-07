@extends('layout.master')

@section('title', 'Transaction Report')
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
                    <h5>Transaction Report</h5>
                    <p>
                        {{ !empty($filters['hajj_umrah']) && $filters['hajj_umrah'] !== 'all' ? ucfirst($filters['hajj_umrah']) : 'All Seasons' }} &bull; Year: {{ !empty($filters['year']) && $filters['year'] !== 'all' ? $filters['year'] : 'All Years' }}
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
                        <h4 class="fw-bold mb-1" style="color:#1e1b4b">Transaction Report</h4>
                        <p class="mb-0" style="font-size:13px;color:#64748b">
                            <span
                                class="et-badge {{ ($filters['hajj_umrah'] ?? '') === 'hajj' ? 'et-badge-hajj' : (($filters['hajj_umrah'] ?? '') === 'umrah' ? 'et-badge-umrah' : 'bg-secondary text-white') }}">
                                {{ !empty($filters['hajj_umrah']) && $filters['hajj_umrah'] !== 'all' ? ucfirst($filters['hajj_umrah']) : 'All Seasons' }}
                            </span>
                            &nbsp;{{ !empty($filters['year']) && $filters['year'] !== 'all' ? $filters['year'] : 'All Years' }}
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

                @if(!empty($selectedSupplier))
                    {{-- SUPPLIER REFLECTION CARD --}}
                    <div class="card border-0 mb-4 shadow-sm" style="border-radius:14px; overflow:hidden; border-left: 5px solid #f59e0b !important;">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="badge bg-warning text-dark px-3 py-1 mb-2 fs-12 fw-bold">Supplier Reflection Ledger</span>
                                    <h4 class="fw-bold text-dark m-0">{{ $selectedSupplier->name }}</h4>
                                    <div class="text-muted fs-13 mt-1">
                                        @if($selectedSupplier->company_name) <span class="me-3"><i class="ri-building-line me-1"></i>{{ $selectedSupplier->company_name }}</span> @endif
                                        @if($selectedSupplier->service_type) <span class="me-3"><i class="ri-service-line me-1"></i>Type: {{ ucfirst($selectedSupplier->service_type) }}</span> @endif
                                        @if($selectedSupplier->phone) <span class="me-3"><i class="ri-phone-line me-1"></i>{{ $selectedSupplier->phone }}</span> @endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted fs-12 d-block">Package & Year</span>
                                    <span class="badge bg-primary fs-13">{{ ucfirst($filters['hajj_umrah'] ?? 'All') }} - {{ $filters['year'] ?? '' }}</span>
                                </div>
                            </div>

                            {{-- Reflection Stats --}}
                            <div class="row g-3 mt-2">
                                <div class="col-md-3 col-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="text-muted small fw-semibold text-uppercase">Hotel Cost</div>
                                        <div class="fs-18 fw-bold text-dark mt-1">PKR {{ number_format($supplierSummary['total_hotel_cost'], 2) }}</div>
                                        <div class="small text-muted">{{ $supplierHotels->count() }} Hotel records</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="text-muted small fw-semibold text-uppercase">Flight Cost</div>
                                        <div class="fs-18 fw-bold text-dark mt-1">PKR {{ number_format($supplierSummary['total_flight_cost'], 2) }}</div>
                                        <div class="small text-muted">{{ $supplierFlights->count() }} Flight records</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="text-muted small fw-semibold text-uppercase">Visa Cost</div>
                                        <div class="fs-18 fw-bold text-dark mt-1">PKR {{ number_format($supplierSummary['total_visa_cost'], 2) }}</div>
                                        <div class="small text-muted">{{ $supplierVisas->count() }} Visa records</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="text-muted small fw-semibold text-uppercase">Transport Cost</div>
                                        <div class="fs-18 fw-bold text-dark mt-1">PKR {{ number_format($supplierSummary['total_transport_cost'], 2) }}</div>
                                        <div class="small text-muted">{{ $supplierTransports->count() }} Transport records</div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="p-3 rounded-3 border text-white" style="background: linear-gradient(135deg, #4f46e5, #4338ca);">
                                        <div class="small fw-semibold text-uppercase opacity-75">Total Booking Payable</div>
                                        <div class="fs-20 fw-bold mt-1">PKR {{ number_format($supplierSummary['total_payable'], 2) }}</div>
                                        <div class="small opacity-75">Sum of all service costs</div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="p-3 rounded-3 border text-white" style="background: linear-gradient(135deg, #16a34a, #15803d);">
                                        <div class="small fw-semibold text-uppercase opacity-75">Total Paid (Transactions)</div>
                                        <div class="fs-20 fw-bold mt-1">PKR {{ number_format($supplierSummary['total_paid'], 2) }}</div>
                                        <div class="small opacity-75">Paid via recorded transactions</div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="p-3 rounded-3 border text-white" style="background: linear-gradient(135deg, {{ $supplierSummary['balance_due'] > 0 ? '#dc2626, #b91c1c' : '#0284c7, #0369a1' }});">
                                        <div class="small fw-semibold text-uppercase opacity-75">Balance Due</div>
                                        <div class="fs-20 fw-bold mt-1">PKR {{ number_format($supplierSummary['balance_due'], 2) }}</div>
                                        <div class="small opacity-75">{{ $supplierSummary['balance_due'] > 0 ? 'Payable remaining' : 'Settled' }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Service Breakdown Details Tabs --}}
                            @if($supplierHotels->isNotEmpty() || $supplierFlights->isNotEmpty() || $supplierVisas->isNotEmpty() || $supplierTransports->isNotEmpty())
                                <div class="mt-4">
                                    <h5 class="fw-bold text-dark mb-3"><i class="ri-list-check-2 me-1 text-primary"></i> Booking Services Linked to Supplier</h5>
                                    
                                    <ul class="nav nav-pills mb-3" id="supplierServiceTabs" role="tablist">
                                        @if($supplierHotels->isNotEmpty())
                                            <li class="nav-item">
                                                <button class="nav-link active btn-sm" id="tab-hotel" data-bs-toggle="pill" data-bs-target="#pills-hotel" type="button">
                                                    <i class="ri-hotel-line me-1"></i> Hotels ({{ $supplierHotels->count() }})
                                                </button>
                                            </li>
                                        @endif
                                        @if($supplierFlights->isNotEmpty())
                                            <li class="nav-item">
                                                <button class="nav-link {{ $supplierHotels->isEmpty() ? 'active' : '' }} btn-sm" id="tab-flight" data-bs-toggle="pill" data-bs-target="#pills-flight" type="button">
                                                    <i class="ri-flight-takeoff-line me-1"></i> Flights ({{ $supplierFlights->count() }})
                                                </button>
                                            </li>
                                        @endif
                                        @if($supplierVisas->isNotEmpty())
                                            <li class="nav-item">
                                                <button class="nav-link {{ $supplierHotels->isEmpty() && $supplierFlights->isEmpty() ? 'active' : '' }} btn-sm" id="tab-visa" data-bs-toggle="pill" data-bs-target="#pills-visa" type="button">
                                                    <i class="ri-passport-line me-1"></i> Visas ({{ $supplierVisas->count() }})
                                                </button>
                                            </li>
                                        @endif
                                        @if($supplierTransports->isNotEmpty())
                                            <li class="nav-item">
                                                <button class="nav-link {{ $supplierHotels->isEmpty() && $supplierFlights->isEmpty() && $supplierVisas->isEmpty() ? 'active' : '' }} btn-sm" id="tab-transport" data-bs-toggle="pill" data-bs-target="#pills-transport" type="button">
                                                    <i class="ri-bus-line me-1"></i> Transport ({{ $supplierTransports->count() }})
                                                </button>
                                            </li>
                                        @endif
                                    </ul>

                                    <div class="tab-content border rounded-3 p-3 bg-white" id="supplierServiceTabsContent">
                                        @if($supplierHotels->isNotEmpty())
                                            <div class="tab-pane fade show active" id="pills-hotel">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Booking / Client</th>
                                                                <th>Hotel</th>
                                                                <th>Check-In</th>
                                                                <th>Check-Out</th>
                                                                <th>Nights</th>
                                                                <th>Rooms</th>
                                                                <th>Cost / Night</th>
                                                                <th class="text-end">Total Cost</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($supplierHotels as $idx => $h)
                                                                <tr>
                                                                    <td>{{ $idx + 1 }}</td>
                                                                    <td>
                                                                        <strong>#{{ $h->booking_id }}</strong> - {{ $h->booking->client->client_name ?? '—' }}
                                                                    </td>
                                                                    <td>{{ $h->hotel_name ?? '—' }} ({{ $h->location ?? '—' }})</td>
                                                                    <td>{{ $h->check_in ? \Carbon\Carbon::parse($h->check_in)->format('d M Y') : '—' }}</td>
                                                                    <td>{{ $h->check_out ? \Carbon\Carbon::parse($h->check_out)->format('d M Y') : '—' }}</td>
                                                                    <td>{{ $h->no_of_nights ?? 1 }}</td>
                                                                    <td>{{ $h->no_of_rooms ?? 1 }}</td>
                                                                    <td>{{ number_format($h->cost_per_night, 2) }}</td>
                                                                    <td class="text-end fw-bold text-danger">PKR {{ number_format($h->cost_amount, 2) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif

                                        @if($supplierFlights->isNotEmpty())
                                            <div class="tab-pane fade {{ $supplierHotels->isEmpty() ? 'show active' : '' }}" id="pills-flight">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Booking / Client</th>
                                                                <th>Airline</th>
                                                                <th>Departure Date</th>
                                                                <th>Departure PNR</th>
                                                                <th>Pax</th>
                                                                <th>Flight Cost Rate</th>
                                                                <th class="text-end">Total Flight Cost</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($supplierFlights as $idx => $f)
                                                                <tr>
                                                                    <td>{{ $idx + 1 }}</td>
                                                                    <td><strong>#{{ $f->id }}</strong> - {{ $f->client->client_name ?? '—' }}</td>
                                                                    <td>{{ $f->departure_airline ?? '—' }} ({{ $f->departure_flight ?? '—' }})</td>
                                                                    <td>{{ $f->departure_date ? \Carbon\Carbon::parse($f->departure_date)->format('d M Y') : '—' }}</td>
                                                                    <td>{{ $f->departure_pnr ?? '—' }}</td>
                                                                    <td>{{ $f->no_of_pax ?? 1 }}</td>
                                                                    <td>{{ number_format($f->flight_cost_rate, 2) }}</td>
                                                                    <td class="text-end fw-bold text-danger">PKR {{ number_format($f->flight_cost, 2) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif

                                        @if($supplierVisas->isNotEmpty())
                                            <div class="tab-pane fade {{ $supplierHotels->isEmpty() && $supplierFlights->isEmpty() ? 'show active' : '' }}" id="pills-visa">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Booking / Client</th>
                                                                <th>Pilgrim / Name</th>
                                                                <th>Passport #</th>
                                                                <th>Cost Rate</th>
                                                                <th class="text-end">Cost Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($supplierVisas as $idx => $v)
                                                                <tr>
                                                                    <td>{{ $idx + 1 }}</td>
                                                                    <td><strong>#{{ $v->booking_id }}</strong> - {{ $v->booking->client->client_name ?? '—' }}</td>
                                                                    <td>{{ $v->given_name ?? '—' }}</td>
                                                                    <td>{{ $v->passport_number ?? '—' }}</td>
                                                                    <td>{{ number_format($v->cost_rate, 2) }}</td>
                                                                    <td class="text-end fw-bold text-danger">PKR {{ number_format($v->cost_amount, 2) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif

                                        @if($supplierTransports->isNotEmpty())
                                            <div class="tab-pane fade {{ $supplierHotels->isEmpty() && $supplierFlights->isEmpty() && $supplierVisas->isEmpty() ? 'show active' : '' }}" id="pills-transport">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Booking / Client</th>
                                                                <th>Route</th>
                                                                <th>Transport Type</th>
                                                                <th>Cost Rate</th>
                                                                <th class="text-end">Cost Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($supplierTransports as $idx => $t)
                                                                <tr>
                                                                    <td>{{ $idx + 1 }}</td>
                                                                    <td><strong>#{{ $t->booking_id }}</strong> - {{ $t->booking->client->client_name ?? '—' }}</td>
                                                                    <td>{{ $t->route ?? '—' }}</td>
                                                                    <td>{{ $t->transport_type ?? '—' }}</td>
                                                                    <td>{{ number_format($t->cost_rate, 2) }}</td>
                                                                    <td class="text-end fw-bold text-danger">PKR {{ number_format($t->cost_amount, 2) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if(!empty($selectedClient))
                    {{-- CLIENT REFLECTION CARD --}}
                    <div class="card border-0 mb-4 shadow-sm" style="border-radius:14px; overflow:hidden; border-left: 5px solid #0284c7 !important;">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="badge bg-info text-dark px-3 py-1 mb-2 fs-12 fw-bold">Client Reflection Ledger</span>
                                    <h4 class="fw-bold text-dark m-0">{{ $selectedClient->client_name }}</h4>
                                    <div class="text-muted fs-13 mt-1">
                                        @if($selectedClient->phone) <span class="me-3"><i class="ri-phone-line me-1"></i>{{ $selectedClient->phone }}</span> @endif
                                        @if($selectedClient->email) <span class="me-3"><i class="ri-mail-line me-1"></i>{{ $selectedClient->email }}</span> @endif
                                        @if($selectedClient->city) <span class="me-3"><i class="ri-map-pin-line me-1"></i>{{ $selectedClient->city }}</span> @endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted fs-12 d-block">Package & Year</span>
                                    <span class="badge bg-primary fs-13">{{ ucfirst($filters['hajj_umrah'] ?? 'All') }} - {{ $filters['year'] ?? '' }}</span>
                                </div>
                            </div>

                            {{-- Reflection Stats --}}
                            <div class="row g-3 mt-2">
                                <div class="col-md-4 col-12">
                                    <div class="p-3 rounded-3 border text-white" style="background: linear-gradient(135deg, #4f46e5, #4338ca);">
                                        <div class="small fw-semibold text-uppercase opacity-75">Total Booking Amount</div>
                                        <div class="fs-20 fw-bold mt-1">PKR {{ number_format($clientSummary['total_bookings_amount'], 2) }}</div>
                                        <div class="small opacity-75">{{ $clientBookings->count() }} Total Bookings</div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="p-3 rounded-3 border text-white" style="background: linear-gradient(135deg, #16a34a, #15803d);">
                                        <div class="small fw-semibold text-uppercase opacity-75">Total Paid / Received</div>
                                        <div class="fs-20 fw-bold mt-1">PKR {{ number_format($clientSummary['total_received'], 2) }}</div>
                                        <div class="small opacity-75">Booking: PKR {{ number_format($clientSummary['booking_received'], 2) }} | Trans: PKR {{ number_format($clientSummary['transaction_paid'], 2) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="p-3 rounded-3 border text-white" style="background: linear-gradient(135deg, {{ $clientSummary['balance_due'] > 0 ? '#dc2626, #b91c1c' : '#0284c7, #0369a1' }});">
                                        <div class="small fw-semibold text-uppercase opacity-75">Balance Due</div>
                                        <div class="fs-20 fw-bold mt-1">PKR {{ number_format($clientSummary['balance_due'], 2) }}</div>
                                        <div class="small opacity-75">{{ $clientSummary['balance_due'] > 0 ? 'Receivable from client' : 'Settled' }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Client Bookings List --}}
                            @if($clientBookings->isNotEmpty())
                                <div class="mt-4">
                                    <h5 class="fw-bold text-dark mb-3"><i class="ri-calendar-todo-line me-1 text-primary"></i> Client Bookings ({{ ucfirst($filters['hajj_umrah'] ?? '') }} {{ $filters['year'] ?? '' }})</h5>
                                    <div class="table-responsive border rounded-3 bg-white p-2">
                                        <table class="table table-sm table-bordered align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Voucher #</th>
                                                    <th>Package Name</th>
                                                    <th>Pax</th>
                                                    <th>Departure Date</th>
                                                    <th class="text-end">Total Amount</th>
                                                    <th class="text-end">Received</th>
                                                    <th class="text-end">Balance</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($clientBookings as $idx => $b)
                                                    <tr>
                                                        <td>{{ $idx + 1 }}</td>
                                                        <td><span class="fw-bold text-primary">{{ $b->voucher_number ?? ('#' . $b->id) }}</span></td>
                                                        <td>{{ $b->package_name ?? '—' }}</td>
                                                        <td>{{ $b->no_of_pax ?? 1 }}</td>
                                                        <td>{{ $b->departure_date ? \Carbon\Carbon::parse($b->departure_date)->format('d M Y') : '—' }}</td>
                                                        <td class="text-end fw-bold">PKR {{ number_format($b->total_amount, 2) }}</td>
                                                        <td class="text-end text-success fw-bold">PKR {{ number_format($b->total_received, 2) }}</td>
                                                        <td class="text-end text-danger fw-bold">PKR {{ number_format($b->balance, 2) }}</td>
                                                        <td>
                                                            <span class="badge {{ $b->status === 'confirmed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                                {{ ucfirst($b->status ?? 'pending') }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

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
                                @if(!empty($selectedSupplier))
                                    <p class="fw-semibold text-dark mb-1">No direct payout / payment transactions recorded yet for <strong>{{ $selectedSupplier->name }}</strong>.</p>
                                    <p class="text-muted fs-12 mb-0">Supplier's booking payables and service breakdown are displayed in the summary card above.</p>
                                @elseif(!empty($selectedClient))
                                    <p class="fw-semibold text-dark mb-1">No separate transaction receipts recorded yet for <strong>{{ $selectedClient->client_name }}</strong>.</p>
                                    <p class="text-muted fs-12 mb-0">Client's bookings, total receivable and balances are displayed in the summary card above.</p>
                                @else
                                    <p>No transactions found for the selected filters.</p>
                                @endif
                                <a href="{{ route('expense.transaction.report.filter') }}"
                                    class="et-btn et-btn-filter no-print" style="display:inline-flex;margin-top:14px">
                                    <i class="ri-filter-3-line"></i> Change / Reset Filters
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table et-table mb-0" id="reportTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Category / Payee</th>
                                            <th>Bank Account</th>
                                            <th>Package</th>
                                            <th>Year</th>
                                            <th>Payment Method</th>
                                            <th>Reference No.</th>
                                            <th>Description</th>
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
                                                    'cheque' => 'et-badge-ewallet',
                                                    default => 'et-badge-bank',
                                                };
                                                $pmLabel = match ($txn->payment_type) {
                                                    'cash' => 'Cash',
                                                    'bank' => 'Bank Transfer',
                                                    'online' => 'Online',
                                                    'cheque' => 'Cheque',
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
                                                <td>
                                                    @if($txn->payee_type === 'supplier' || $txn->supplier)
                                                        <div>
                                                            <span class="badge bg-warning text-dark me-1">Supplier</span>
                                                            <span class="fw-bold">{{ $txn->supplier->name ?? '—' }}</span>
                                                        </div>
                                                    @elseif($txn->payee_type === 'client' || $txn->client)
                                                        <div>
                                                            <span class="badge bg-info text-dark me-1">Client</span>
                                                            <span class="fw-bold">{{ $txn->client->client_name ?? '—' }}</span>
                                                        </div>
                                                    @elseif($txn->payee_type === 'staff' || $txn->staff)
                                                        <div>
                                                            <span class="badge bg-success me-1">Staff</span>
                                                            <span class="fw-bold">{{ $txn->staff->first_name ?? '' }} {{ $txn->staff->last_name ?? '' }}</span>
                                                        </div>
                                                    @elseif($txn->payee_type === 'company' || $txn->company)
                                                        <div>
                                                            <span class="badge bg-primary me-1">Company</span>
                                                            <span class="fw-bold">{{ $txn->company->company_name ?? '—' }}</span>
                                                        </div>
                                                    @elseif($txn->payee_type === 'self')
                                                        <div>
                                                            <span class="badge bg-secondary me-1">Self</span>
                                                            <span class="fw-bold">Internal Transfer</span>
                                                        </div>
                                                    @else
                                                        <div>
                                                            <span class="badge bg-dark me-1">Expense Head</span>
                                                            <span class="fw-bold">{{ $txn->expense->name ?? 'General Expense' }}</span>
                                                        </div>
                                                    @endif
                                                    @if($txn->expense && $txn->payee_type !== 'expense')
                                                        <small class="text-muted d-block">Head: {{ $txn->expense->name }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($txn->bank)
                                                        <span class="fw-medium text-dark"><i class="ri-bank-line me-1 text-primary"></i>{{ $txn->bank->name }}</span>
                                                        @if(!empty($txn->bank->account_name) || (!empty($txn->bank->account_number) && $txn->bank->account_number !== '0'))
                                                            <small class="text-muted d-block">
                                                                {{ $txn->bank->account_name }}
                                                                @if(!empty($txn->bank->account_number) && $txn->bank->account_number !== '0')
                                                                    ({{ $txn->bank->account_number }})
                                                                @endif
                                                            </small>
                                                        @endif
                                                    @elseif($txn->payment_type === 'cash')
                                                        <span class="text-muted"><i class="ri-money-dollar-circle-line me-1 text-success"></i>Cash in Hand</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span
                                                        class="et-badge {{ $txn->hajj_umrah === 'hajj' ? 'et-badge-hajj' : 'et-badge-umrah' }}">{{ ucfirst($txn->hajj_umrah) }}</span>
                                                </td>
                                                <td>{{ $txn->year }}</td>
                                                <td><span class="et-badge {{ $pmBadge }}">{{ $pmLabel }}</span></td>
                                                <td class="text-muted">{{ $txn->reference_no ?: '—' }}</td>
                                                <td class="text-muted" style="max-width:200px;">{{ $txn->description ?: '—' }}</td>
                                                <td><span class="et-badge {{ $currBadge }}">{{ $txn->currency }}</span></td>
                                                <td class="text-end fw-bold" style="color:#1e1b4b">
                                                    {{ number_format($txn->amount, 2) }}
                                                    @if ($txn->currency === 'SAR')
                                                        <div style="font-size:10.5px;color:#94a3b8;font-weight:500;">
                                                             @ {{ number_format($txn->exchange_rate, 2) }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if ($pkrEquivalent !== null)
                                                        <span class="fw-bold" style="color:#be123c;">
                                                             {{ number_format($pkrEquivalent, 2) }}
                                                        </span>
                                                        <div style="font-size:10.5px;color:#94a3b8;font-weight:500;">PKR</div>
                                                    @else
                                                        <span class="fw-bold" style="color:#0e7490;">
                                                            {{ number_format($txn->amount, 2) }}
                                                        </span>
                                                        <div style="font-size:10.5px;color:#94a3b8;font-weight:500;">PKR</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        @if ($currencyTotals->count() > 1)
                                            @foreach ($currencyTotals as $curr => $total)
                                                <tr>
                                                    <td colspan="9" class="text-end"
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
                                                <td colspan="9" class="text-end" style="color:#4f46e5;font-size:13px">
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
            title: 'Transaction Report',
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
                        $payeeName = match($txn->payee_type) {
                            'supplier' => ($txn->supplier->name ?? 'Supplier'),
                            'client'   => ($txn->client->client_name ?? 'Client'),
                            'staff'    => (($txn->staff->first_name ?? '') . ' ' . ($txn->staff->last_name ?? '') . ' (Staff)'),
                            'company'  => ($txn->company->company_name ?? 'Company'),
                            'self'     => 'Internal Transfer',
                            default    => ($txn->expense->name ?? 'General Expense'),
                        };
                        $bankName = $txn->bank ? ($txn->bank->name . (!empty($txn->bank->account_name) ? (' - ' . $txn->bank->account_name) : '')) : ($txn->payment_type === 'cash' ? 'Cash in Hand' : '—');
                    @endphp {
                        no: {{ $i + 1 }},
                        date: '{{ \Carbon\Carbon::parse($txn->date)->format('d M Y') }}',
                        category: '{{ addslashes($payeeName) }}',
                        bank: '{{ addslashes($bankName) }}',
                        package: '{{ ucfirst($txn->hajj_umrah) }}',
                        year: {{ $txn->year }},
                        payment: '{{ match ($txn->payment_type) {'cash' => 'Cash','bank' => 'Bank','online' => 'Online','cheque' => 'Cheque',default => ucfirst($txn->payment_type)} }}',
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
            filename: 'transaction-report-{{ $filters['year'] ?? 'report' }}'
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
            doc.text('Transaction Report', 30, 23);

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
                `Transactions: ${REPORT.summary.transactions}   |   ${currencyLine}   |   Cash: ${REPORT.summary.cash.toLocaleString()}   |   Bank/Online: ${REPORT.summary.other.toLocaleString()}`;
            if (REPORT.sarPkrTotal > 0) {
                sumText +=
                    `   |   SAR in PKR: ${REPORT.sarPkrTotal.toLocaleString(undefined, {minimumFractionDigits:2,maximumFractionDigits:2})}`;
            }
            doc.text(sumText, 30, 63);

            /* foot rows */
            const footRows = Object.entries(REPORT.currencyTotals).map(([curr, total]) => [
                '', '', '', '', '', '', '', 'Grand Total', curr,
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
                    ['#', 'Date', 'Category / Payee', 'Bank', 'Package', 'Year', 'Payment Method', 'Reference No.',
                        'Currency', 'Amount', 'PKR Equivalent'
                    ]
                ],
                body: REPORT.rows.map(r => [r.no, r.date, r.category, r.bank, r.package, r.year, r.payment, r.ref,
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
                    9: {
                        halign: 'right'
                    },
                    10: {
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
            data.push(['#', 'Date', 'Category / Payee', 'Bank Account', 'Package', 'Year', 'Payment Method', 'Reference No.',
                'Currency', 'Amount', 'Exchange Rate', 'PKR Equivalent'
            ]);

            /* data rows */
            REPORT.rows.forEach(r => data.push([
                r.no, r.date, r.category, r.bank, r.package, r.year, r.payment, r.ref,
                r.currency, r.amount,
                r.currency === 'SAR' ? r.exchangeRate : 1,
                r.pkrEquiv
            ]));

            /* grand total rows */
            Object.entries(REPORT.currencyTotals).forEach(([curr, total]) => {
                data.push([
                    '', '', '', '', '', '', '', '', 'Grand Total (' + curr + ')', total, '',
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
                    wch: 20
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

            ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'].forEach(col => {
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
                ['I', 'J', 'L'].forEach(col => {
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
