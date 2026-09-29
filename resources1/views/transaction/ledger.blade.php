@extends('layout.master')

@section('title', 'Client Ledger - ' . $client->name)

@section('content')
    <style>
        .ledger-stat-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            transition: transform .15s;
        }

        .ledger-stat-card:hover {
            transform: translateY(-2px);
        }

        .ledger-stat-card .icon-box {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ledger-stat-card .stat-label {
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: .7px;
            text-transform: uppercase;
            color: #8c96a5;
            margin-bottom: 4px;
        }

        .ledger-stat-card .stat-value {
            font-size: 22px;
            font-weight: 700;
            line-height: 1.2;
        }

        .ledger-stat-card .stat-sub {
            font-size: 11px;
            margin-top: 3px;
        }

        .info-label {
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: #8c96a5;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 14.5px;
            font-weight: 600;
            color: #1a1f36;
        }

        .breakdown-table td {
            padding: 13px 20px;
            vertical-align: middle;
            font-size: 13.5px;
        }

        .breakdown-table .bd-label {
            color: #5a6278;
        }

        .breakdown-table .bd-value {
            font-weight: 600;
            white-space: nowrap;
        }

        .txn-table thead th {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #8c96a5;
            border-bottom: 1px solid #eef0f4;
            background: #f8f9fc;
            padding: 12px 16px;
        }

        .txn-table tbody td {
            font-size: 13.5px;
            padding: 13px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f8;
        }

        .txn-table tbody tr:hover {
            background: #f8f9fc;
        }

        .section-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            overflow: hidden;
        }

        .section-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f2f7;
            padding: 16px 24px;
        }

        .section-card .card-header h5 {
            font-size: 14.5px;
            font-weight: 700;
            color: #1a1f36;
            margin: 0;
        }

        .booking-row {
            background: #f0fff8 !important;
        }

        .booking-row:hover {
            background: #e6fff4 !important;
        }
    </style>

    {{-- ── Single source of truth for balance sign/labels, reused everywhere below ── --}}
    @php
        $absBalance = abs($balance);
        $balanceLabel = $balance > 0 ? 'Balance Due' : ($balance < 0 ? 'Advance Balance' : 'Balance Remaining');
        $balanceSign = $balance < 0 ? '+' : '';
    @endphp

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- TOP BAR -->
                <div class="py-4 d-flex align-items-center justify-content-between border-bottom mb-1">
                    <div>
                        <p class="text-muted mb-0" style="font-size:12px;letter-spacing:.5px;">ACCOUNT STATEMENT</p>
                        <h4 class="fw-bold text-dark mb-0" style="font-size:22px;">Client Ledger</h4>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <i class="mdi mdi-account-circle text-primary" style="font-size:18px;"></i>
                            <span class="fw-semibold text-primary" style="font-size:16px;">{{ $client->name }}</span>
                            <span
                                class="badge {{ $client->status == 'active' ? 'bg-success' : 'bg-secondary' }} ms-1 px-2 py-1"
                                style="font-size:10px;">
                                {{ ucfirst($client->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button onclick="exportExcel()" class="btn btn-success btn-sm d-flex align-items-center gap-2 px-3">
                            <i class="mdi mdi-microsoft-excel"></i> Export Excel
                        </button>
                        <button onclick="exportPDF()" class="btn btn-danger btn-sm d-flex align-items-center gap-2 px-3">
                            <i class="mdi mdi-file-pdf-box"></i> Export PDF
                        </button>
                        <a href="{{ route('transaction.index') }}"
                            class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2 px-3">
                            <i class="mdi mdi-arrow-left"></i> Back to Transactions
                        </a>
                    </div>

                </div>

                <!-- SUMMARY CARDS -->
                <div class="row g-3 mt-1">

                    <div class="col-lg-3 col-md-6">
                        <div class="card ledger-stat-card p-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="stat-label">Total Booking Amount</div>
                                    <div class="stat-value text-dark">PKR {{ number_format($totalBooking, 0) }}</div>
                                    <div class="stat-sub text-muted">All bookings combined</div>
                                </div>
                                <div class="icon-box bg-primary bg-opacity-10">
                                    <i class="mdi mdi-file-document-multiple text-primary" style="font-size:26px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card ledger-stat-card p-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="stat-label">Received at Booking</div>
                                    <div class="stat-value text-info">PKR {{ number_format($bookingReceived, 0) }}</div>
                                    <div class="stat-sub text-muted">Initial payment on booking</div>
                                </div>
                                <div class="icon-box bg-info bg-opacity-10">
                                    <i class="mdi mdi-cash-fast text-info" style="font-size:26px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card ledger-stat-card p-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="stat-label">Total Paid</div>
                                    <div class="stat-value text-success">PKR {{ number_format($totalPaid, 0) }}</div>
                                    <div class="stat-sub text-muted">
                                        Booking + {{ number_format($transactionsPaid, 0) }} via transactions
                                    </div>
                                </div>
                                <div class="icon-box bg-success bg-opacity-10">
                                    <i class="mdi mdi-check-decagram text-success" style="font-size:26px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card ledger-stat-card p-4"
                            style="border-left: 4px solid {{ $balance > 0 ? '#f44336' : '#4caf50' }} !important;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="stat-label">Remaining Balance</div>
                                    <div class="stat-value {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                        PKR {{ $balanceSign }}{{ number_format($absBalance, 0) }}
                                    </div>
                                    <div class="stat-sub {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                        @if ($balance > 0)
                                            <i class="mdi mdi-alert-circle-outline me-1"></i>Amount Due
                                        @elseif ($balance < 0)
                                            <i class="mdi mdi-check-circle-outline me-1"></i>Advance Paid
                                        @else
                                            <i class="mdi mdi-check-circle-outline me-1"></i>Fully Cleared
                                        @endif
                                    </div>
                                </div>
                                <div class="icon-box {{ $balance > 0 ? 'bg-danger' : 'bg-success' }} bg-opacity-10">
                                    <i class="mdi mdi-scale-balance {{ $balance > 0 ? 'text-danger' : 'text-success' }}"
                                        style="font-size:26px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- CLIENT INFO + BREAKDOWN -->
                <div class="row g-3 mt-1">

                    <div class="col-lg-7">
                        <div class="card section-card h-100">
                            <div class="card-header">
                                <h5><i class="mdi mdi-account-circle me-2 text-primary"></i>Client Information</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-sm-6">
                                        <div class="info-label">Full Name</div>
                                        <div class="info-value">{{ $client->name }}</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-label">Phone Number</div>
                                        <div class="info-value">
                                            @if ($client->phone)
                                                <i class="mdi mdi-phone-outline text-muted me-1"></i>{{ $client->phone }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-label">Email Address</div>
                                        <div class="info-value text-primary">
                                            @if ($client->email)
                                                <i class="mdi mdi-email-outline me-1"></i>{{ $client->email }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-label">Account Status</div>
                                        <span
                                            class="badge {{ $client->status == 'active' ? 'bg-success' : 'bg-secondary' }} px-3 py-2 mt-1">
                                            <i
                                                class="mdi {{ $client->status == 'active' ? 'mdi-check-circle' : 'mdi-close-circle' }} me-1"></i>
                                            {{ ucfirst($client->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card section-card h-100">
                            <div class="card-header">
                                <h5><i class="mdi mdi-calculator me-2 text-primary"></i>Payment Breakdown</h5>
                            </div>
                            <div class="card-body p-0">
                                <table class="table breakdown-table mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="bd-label">
                                                <i class="mdi mdi-file-document-outline text-primary me-2"></i>
                                                Total Booking Amount
                                            </td>
                                            <td class="bd-value text-dark text-end">PKR
                                                {{ number_format($totalBooking, 0) }}</td>
                                        </tr>
                                        <tr style="background:#f8fffe;">
                                            <td class="bd-label">
                                                <i class="mdi mdi-minus-circle text-success me-2"></i>
                                                Received at Booking
                                            </td>
                                            <td class="bd-value text-success text-end">− PKR
                                                {{ number_format($bookingReceived, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="bd-label">
                                                <i class="mdi mdi-minus-circle text-success me-2"></i>
                                                Via Transactions
                                            </td>
                                            <td class="bd-value text-success text-end">− PKR
                                                {{ number_format($transactionsPaid, 0) }}</td>
                                        </tr>
                                        <tr
                                            style="background:{{ $balance > 0 ? '#fff5f5' : '#f0fff4' }}; border-top: 2px solid {{ $balance > 0 ? '#ffcdd2' : '#c8e6c9' }};">
                                            <td class="fw-bold"
                                                style="font-size:14px; color:{{ $balance > 0 ? '#c62828' : '#2e7d32' }};">
                                                <i class="mdi mdi-scale-balance me-2"></i>{{ $balanceLabel }}
                                            </td>
                                            <td class="text-end fw-bold"
                                                style="font-size:14px; color:{{ $balance > 0 ? '#c62828' : '#2e7d32' }};">
                                                PKR {{ $balanceSign }}{{ number_format($absBalance, 0) }}
                                                @if ($balance > 0)
                                                    <span class="badge bg-danger ms-1" style="font-size:10px;">Due</span>
                                                @elseif ($balance < 0)
                                                    <span class="badge bg-success ms-1"
                                                        style="font-size:10px;">Advance</span>
                                                @else
                                                    <span class="badge bg-success ms-1" style="font-size:10px;">✓
                                                        Clear</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- TRANSACTION HISTORY -->
                <div class="card section-card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="mdi mdi-history me-2 text-primary"></i>Transaction History</h5>
                        <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size:12px;">
                            {{ $transactions->count() + ($bookingReceived > 0 ? 1 : 0) }} Records
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="datatable" class="table txn-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Package</th>
                                        <th>Payment Type</th>
                                        <th class="text-end">Amount</th>
                                        <th>Date</th>
                                        <th>Ref No</th>
                                        <th>Narration</th>
                                        <th>Proof</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($transactions->count() > 0 || $bookingReceived > 0)
                                        @if ($bookingReceived > 0)
                                            <tr class="booking-row">
                                                <td class="ps-4 fw-medium text-muted">—</td>
                                                <td>
                                                    <span style="font-size:13px;">
                                                        <i class="mdi mdi-book-open-outline me-1 text-success"></i>
                                                        <span class="fw-semibold text-dark">Received at Booking</span>
                                                        <br>
                                                        <small class="text-muted">Initial advance payment</small>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success px-2 py-1" style="font-size:11px;">
                                                        Booking Payment
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold text-success">
                                                    PKR {{ number_format($bookingReceived, 0) }}
                                                </td>
                                                <td class="text-muted">—</td>
                                                <td class="text-muted">—</td>
                                                <td class="text-muted">—</td>
                                                <td class="text-center">
                                                    <span class="badge bg-success px-2 py-1" style="font-size:11px;">
                                                        Confirmed
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- ── Normal Transactions ── --}}
                                        @foreach ($transactions as $t)
                                            <tr>
                                                <td class="ps-4 fw-medium text-muted">{{ $loop->iteration }}</td>

                                                <td>
                                                    @if ($t->booking)
                                                        <a href="{{ route('booking.show', $t->booking->id) }}"
                                                            class="text-decoration-none fw-semibold text-dark">
                                                            <span
                                                                class="badge bg-primary bg-opacity-10 text-primary me-1">#{{ $t->booking->id }}</span>
                                                            <small
                                                                class="text-muted">{{ ucfirst($t->booking->package_name) }}</small>
                                                        </a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @php
                                                        $typeColors = [
                                                            'cash' => 'bg-success',
                                                            'bank_transfer' => 'bg-primary',
                                                            'cheque' => 'bg-warning text-dark',
                                                            'online' => 'bg-info',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge {{ $typeColors[$t->payment_type] ?? 'bg-secondary' }} px-2 py-1"
                                                        style="font-size:11px;">
                                                        {{ ucfirst(str_replace('_', ' ', $t->payment_type)) }}
                                                    </span>
                                                </td>

                                                <td
                                                    class="text-end fw-bold {{ $t->status == 'confirmed' ? 'text-success' : 'text-muted' }}">
                                                    PKR {{ number_format($t->amount, 0) }}
                                                </td>

                                                <td class="text-muted">
                                                    <i class="mdi mdi-calendar-outline me-1"></i>
                                                    {{ \Carbon\Carbon::parse($t->payment_date)->format('d M, Y') }}
                                                </td>

                                                <td class="fw-medium">{{ $t->reference_number ?? '—' }}</td>
                                                <td class="fw-medium">{{ $t->notes ?? '—' }}</td>
                                                <td>
                                                    @if ($t->proof_of_payment)
                                                        <a href="{{ asset('assets/images/transaction_proofs/' . $t->proof_of_payment) }}"
                                                            target="_blank"
                                                            class="btn btn-sm btn-outline-primary px-2 py-1"
                                                            style="font-size:12px;">
                                                            <i class="mdi mdi-eye me-1"></i>View
                                                        </a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    @php
                                                        $statusColors = [
                                                            'confirmed' => 'bg-success',
                                                            'pending' => 'bg-warning text-dark',
                                                            'rejected' => 'bg-danger',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge {{ $statusColors[$t->status] ?? 'bg-secondary' }} px-2 py-1"
                                                        style="font-size:11px;">
                                                        {{ ucfirst($t->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach

                                        {{-- ── Footer Summary Rows ── --}}
                                        <tr style="background:#f8f9fc; border-top: 2px solid #e0e4ef;">
                                            <td colspan="3" class="text-end py-3 fw-semibold text-muted ps-4">
                                                Confirmed Transactions:
                                            </td>
                                            <td class="text-end py-3 fw-bold text-success">
                                                PKR {{ number_format($transactionsPaid, 0) }}
                                            </td>
                                            <td colspan="4"></td>
                                        </tr>
                                        <tr style="background:#e8f5e9; border-top: 1px solid #c8e6c9;">
                                            <td colspan="3" class="text-end py-3 fw-bold ps-4" style="color:#1b5e20;">
                                                Grand Total Paid (incl. Booking):
                                            </td>
                                            <td class="text-end py-3 fw-bold" style="color:#1b5e20; font-size:15px;">
                                                PKR {{ number_format($totalPaid, 0) }}
                                            </td>
                                            <td colspan="4"></td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <i class="mdi mdi-inbox-outline d-block mb-2"
                                                    style="font-size:42px; opacity:.4;"></i>
                                                <span style="font-size:14px;">No payment records found for this
                                                    client.</span>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <script>
        function exportExcel() {

            if (typeof XLSX === 'undefined') {
                alert('Excel library load nahi hui. Internet connection check karein.');
                return;
            }

            const wb = XLSX.utils.book_new();

            /* ── Sheet 1: Summary ── */
            const summaryData = [
                ["CLIENT LEDGER REPORT"],
                [],
                ["Client Name", "{{ addslashes($client->name) }}"],
                ["Generated On", new Date().toLocaleString()],
                [],
                ["─────────────────────────────", "──────────────────"],
                ["PAYMENT SUMMARY", ""],
                ["─────────────────────────────", "──────────────────"],
                ["Total Booking Amount", {{ (int) $totalBooking }}],
                ["Received at Booking", {{ (int) $bookingReceived }}],
                ["Via Transactions", {{ (int) $transactionsPaid }}],
                ["Total Paid", {{ (int) $totalPaid }}],
                ["{{ $balanceLabel }}{{ $balance < 0 ? ' (Overpaid)' : '' }}", {{ (int) $absBalance }}],
            ];

            const ws1 = XLSX.utils.aoa_to_sheet(summaryData);
            ws1['!cols'] = [{
                wch: 30
            }, {
                wch: 22
            }];
            XLSX.utils.book_append_sheet(wb, ws1, "Summary");

            /* ── Sheet 2: Transaction History ── */
            const headers = [
                "#",
                "Client",
                "Package / Booking",
                "Payment Type",
                "Amount (PKR)",
                "Date",
                "Reference No",
                "Status"
            ];

            const rows = [];

            @if ($bookingReceived > 0)
                rows.push([
                    "—",
                    "{{ addslashes($client->name) }}",
                    "Received at Booking (Initial Advance)",
                    "Booking Payment",
                    {{ (int) $bookingReceived }},
                    "—",
                    "—",
                    "Confirmed"
                ]);
            @endif

            @foreach ($transactions as $t)
                rows.push([
                    {{ $loop->iteration }},
                    "{{ addslashes($client->name) }}",
                    "{{ $t->booking ? '#' . $t->booking->id . ' - ' . addslashes(ucfirst($t->booking->package_name)) : '—' }}",
                    "{{ ucfirst(str_replace('_', ' ', $t->payment_type)) }}",
                    {{ (int) $t->amount }},
                    "{{ \Carbon\Carbon::parse($t->payment_date)->format('d M, Y') }}",
                    "{{ addslashes($t->reference_number ?? '—') }}",
                    "{{ ucfirst($t->status) }}"
                ]);
            @endforeach

            // Spacer
            rows.push(["", "", "", "", "", "", "", ""]);

            // Footer totals
            rows.push(["", "", "", "Confirmed Transactions (PKR):", {{ (int) $transactionsPaid }}, "", "", ""]);
            rows.push(["", "", "", "Grand Total Paid (PKR):", {{ (int) $totalPaid }}, "", "", ""]);
            rows.push(["", "", "", "{{ $balanceLabel }} (PKR):", {{ (int) $absBalance }}, "", "", ""]);

            const ws2 = XLSX.utils.aoa_to_sheet([headers, ...rows]);
            ws2['!cols'] = [{
                    wch: 5
                }, // #
                {
                    wch: 22
                }, // Client
                {
                    wch: 35
                }, // Package
                {
                    wch: 18
                }, // Payment Type
                {
                    wch: 16
                }, // Amount
                {
                    wch: 14
                }, // Date
                {
                    wch: 18
                }, // Ref No
                {
                    wch: 12
                }, // Status
            ];

            XLSX.utils.book_append_sheet(wb, ws2, "Transaction History");

            /* ── Download ── */
            XLSX.writeFile(wb, "Ledger_{{ Str::slug($client->name) }}_{{ now()->format('Ymd') }}.xlsx");
        }

        // ══════════════════════════════════════════════
        //  PDF EXPORT
        // ══════════════════════════════════════════════
        function exportPDF() {

            if (typeof window.jspdf === 'undefined') {
                alert('PDF library load nahi hui. Internet connection check karein.');
                return;
            }

            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: 'a4'
            });
            const pageW = doc.internal.pageSize.getWidth();

            /* ── Header Banner ── */
            doc.setFillColor(30, 64, 175);
            doc.rect(0, 0, pageW, 24, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(17);
            doc.setFont(undefined, 'bold');
            doc.text("Client Ledger Report", 14, 11);
            doc.setFontSize(9);
            doc.setFont(undefined, 'normal');
            doc.text("Client: {{ addslashes($client->name) }}", 14, 19);
            doc.text("Generated: " + new Date().toLocaleString(), pageW - 14, 19, {
                align: 'right'
            });

            /* ── Summary Table ── */
            doc.setTextColor(30, 30, 30);
            doc.setFontSize(10);
            doc.setFont(undefined, 'bold');
            doc.text("Payment Summary", 14, 33);

            doc.autoTable({
                startY: 36,
                head: [
                    ["Description", "Amount (PKR)"]
                ],
                body: [
                    ["Total Booking Amount", "{{ number_format($totalBooking, 0) }}"],
                    ["Received at Booking", "{{ number_format($bookingReceived, 0) }}"],
                    ["Via Transactions", "{{ number_format($transactionsPaid, 0) }}"],
                    ["Total Paid", "{{ number_format($totalPaid, 0) }}"],
                    ["{{ $balanceLabel }}", "{{ $balanceSign }}{{ number_format($absBalance, 0) }}"],
                ],
                theme: 'grid',
                headStyles: {
                    fillColor: [30, 64, 175],
                    textColor: 255,
                    fontSize: 9,
                    fontStyle: 'bold'
                },
                bodyStyles: {
                    fontSize: 9
                },
                columnStyles: {
                    1: {
                        halign: 'right'
                    }
                },
                margin: {
                    left: 14
                },
                tableWidth: 110,
                didParseCell: (d) => {
                    if (d.section === 'body' && d.row.index === 4) {
                        d.cell.styles.fontStyle = 'bold';
                        d.cell.styles.textColor = {{ $balance > 0 ? '[180,0,0]' : '[0,128,0]' }};
                    }
                    if (d.section === 'body' && d.row.index === 3) {
                        d.cell.styles.fontStyle = 'bold';
                        d.cell.styles.textColor = [0, 100, 0];
                    }
                }
            });

            /* ── Transaction History Table ── */
            const txnY = doc.lastAutoTable.finalY + 9;
            doc.setFontSize(10);
            doc.setFont(undefined, 'bold');
            doc.setTextColor(30, 30, 30);
            doc.text("Transaction History", 14, txnY);

            const txnBody = [];

            @if ($bookingReceived > 0)
                txnBody.push([
                    "—",
                    "{{ addslashes($client->name) }}",
                    "Received at Booking",
                    "Booking Payment",
                    "PKR {{ number_format($bookingReceived, 0) }}",
                    "—",
                    "—",
                    "Confirmed"
                ]);
            @endif

            @foreach ($transactions as $t)
                txnBody.push([
                    "{{ $loop->iteration }}",
                    "{{ addslashes($client->name) }}",
                    "{{ $t->booking ? '#' . $t->booking->id . ' ' . addslashes(ucfirst($t->booking->package_name)) : '—' }}",
                    "{{ ucfirst(str_replace('_', ' ', $t->payment_type)) }}",
                    "PKR {{ number_format($t->amount, 0) }}",
                    "{{ \Carbon\Carbon::parse($t->payment_date)->format('d M, Y') }}",
                    "{{ addslashes($t->reference_number ?? '—') }}",
                    "{{ ucfirst($t->status) }}"
                ]);
            @endforeach

            // Footer totals rows
            const footerStart = txnBody.length;
            txnBody.push(["", "", "", "Confirmed Transactions:", "PKR {{ number_format($transactionsPaid, 0) }}", "", "",
                ""
            ]);
            txnBody.push(["", "", "", "Grand Total Paid:", "PKR {{ number_format($totalPaid, 0) }}", "", "", ""]);
            txnBody.push(["", "", "", "{{ $balanceLabel }}:",
                "PKR {{ $balanceSign }}{{ number_format($absBalance, 0) }}", "", "", ""
            ]);

            doc.autoTable({
                startY: txnY + 3,
                head: [
                    ["#", "Client", "Package / Booking", "Payment Type", "Amount (PKR)", "Date", "Ref No",
                        "Status"
                    ]
                ],
                body: txnBody,
                theme: 'striped',
                headStyles: {
                    fillColor: [30, 64, 175],
                    textColor: 255,
                    fontSize: 7.5,
                    fontStyle: 'bold'
                },
                bodyStyles: {
                    fontSize: 7.5
                },
                columnStyles: {
                    4: {
                        halign: 'right'
                    }
                },
                margin: {
                    left: 14,
                    right: 14
                },
                didParseCell: (d) => {
                    if (d.section === 'body' && d.row.index >= footerStart) {
                        d.cell.styles.fontStyle = 'bold';
                        d.cell.styles.fillColor = [232, 245, 233];
                        d.cell.styles.textColor = [27, 94, 32];
                    }
                }
            });

            doc.save("Ledger_{{ Str::slug($client->name) }}_{{ now()->format('Ymd') }}.pdf");
        }
    </script>
@endsection
