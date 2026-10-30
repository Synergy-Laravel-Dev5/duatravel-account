@extends('layout.master')

@section('title', 'Staff Ledger - ' . $staff->first_name . ' ' . $staff->last_name)
@section('header-title', 'Staff Ledger')

@section('content')

<style>
    .ledger-stat-card {
        border-radius: 8px;
        transition: transform 0.15s ease;
    }
    .ledger-stat-card:hover {
        transform: translateY(-2px);
    }
    .statement-table thead th {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: #475569;
        background: #f1f5f9;
        vertical-align: middle;
    }
    .statement-table tbody td {
        font-size: 12px;
        vertical-align: middle;
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
        .card {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
        }
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid" id="printableLedger">

            <!-- TOP ACTIONS & BREADCRUMB (No-print) -->
            <div class="py-3 d-flex flex-wrap justify-content-between align-items-center border-bottom mb-3 no-print">
                <div>
                    <h4 class="fs-18 fw-bold m-0 text-dark">
                        <i class="mdi mdi-badge-account-horizontal text-primary me-2"></i> Staff Ledger Statement
                    </h4>
                    <span class="text-muted fs-12">
                        Employee: <strong>{{ $staff->first_name }} {{ $staff->last_name }}</strong> 
                        ({{ $staff->designation->name ?? 'Staff' }} &bull; {{ $staff->department->name ?? 'Dept' }})
                    </span>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    <button type="button" class="btn btn-success btn-sm shadow-sm" onclick="exportStaffLedgerExcel()">
                        <i class="mdi mdi-file-excel me-1"></i> Export Excel
                    </button>
                    <button type="button" class="btn btn-danger btn-sm shadow-sm" onclick="exportStaffLedgerPDF()">
                        <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm shadow-sm" onclick="window.print()">
                        <i class="mdi mdi-printer me-1"></i> Print Statement
                    </button>
                    <a href="{{ route('reports.hub', ['tab' => 'staff']) }}" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-layers-outline me-1"></i> All-In-One Hub
                    </a>
                    <a href="{{ route('staff.ledger.filter') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-filter me-1"></i> Change Filter
                    </a>
                    <a href="{{ route('staff.index') }}" class="btn btn-dark btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>

            <!-- FILTER BAR (No-print) -->
            <div class="card border-0 shadow-sm mb-3 no-print">
                <div class="card-body p-3">
                    <form action="{{ route('staff.ledger') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fs-12 fw-semibold mb-1">Switch Staff Member</label>
                            <select name="staff_id" class="form-select form-select-sm select2" onchange="this.form.submit()">
                                @foreach ($staffMembers as $s)
                                    <option value="{{ $s->id }}" {{ $staff->id == $s->id ? 'selected' : '' }}>
                                        {{ $s->first_name }} {{ $s->last_name }} ({{ $s->designation->name ?? 'Staff' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-12 fw-semibold mb-1">From Date</label>
                            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-12 fw-semibold mb-1">To Date</label>
                            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="mdi mdi-refresh me-1"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- STAFF PROFILE & INFO BANNER -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-7">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-lg bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-20" style="width: 50px; height: 50px;">
                                    {{ strtoupper(substr($staff->first_name, 0, 1) . substr($staff->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <h5 class="fw-bold m-0 text-dark">{{ $staff->first_name }} {{ $staff->last_name }}</h5>
                                    <div class="text-muted fs-12 mt-1">
                                        <span><i class="mdi mdi-briefcase-outline me-1"></i> {{ $staff->designation->name ?? 'Designation N/A' }}</span> &bull;
                                        <span><i class="mdi mdi-office-building-outline me-1"></i> {{ $staff->department->name ?? 'Department N/A' }}</span> &bull;
                                        <span><i class="mdi mdi-phone me-1"></i> {{ $staff->phone ?: 'No Phone' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <div class="fs-12 text-muted">Statement Period:</div>
                            <div class="fw-bold fs-13 text-dark">
                                {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M, Y') : 'Inception' }}
                                &nbsp;➔&nbsp;
                                {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M, Y') : 'Present Date' }}
                            </div>
                            <div class="mt-1">
                                <span class="badge bg-soft-info text-info fs-11">Basic Monthly Salary: PKR {{ number_format($staff->salary, 2) }}</span>
                                <span class="badge bg-{{ $staff->status === 'active' ? 'success' : 'secondary' }} fs-11 ms-1">{{ ucfirst($staff->status) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUMMARY STAT CARDS -->
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm p-3 ledger-stat-card bg-light">
                        <small class="text-muted d-block fw-semibold fs-11">Opening Balance</small>
                        <h4 class="fw-bold mb-0 fs-16 text-dark mt-1">
                            PKR {{ number_format(abs($openingBalance), 2) }}
                            <small class="fs-11 fw-normal text-muted">({{ $openingBalance >= 0 ? 'Payable' : 'Advance' }})</small>
                        </h4>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm p-3 ledger-stat-card" style="background: #f0fdf4;">
                        <small class="text-success d-block fw-semibold fs-11">Total Salary Credited (Earned)</small>
                        <h4 class="fw-bold mb-0 fs-16 text-success mt-1">
                            PKR {{ number_format($totalCredit, 2) }}
                        </h4>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm p-3 ledger-stat-card" style="background: #fef2f2;">
                        <small class="text-danger d-block fw-semibold fs-11">Total Paid / Advances (Debited)</small>
                        <h4 class="fw-bold mb-0 fs-16 text-danger mt-1">
                            PKR {{ number_format($totalDebit, 2) }}
                        </h4>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm p-3 ledger-stat-card {{ $finalBalance >= 0 ? 'border-success' : 'border-danger' }}" style="background: #ffffff; border-left: 4px solid {{ $finalBalance >= 0 ? '#10b981' : '#ef4444' }} !important;">
                        <small class="d-block fw-semibold fs-11 {{ $finalBalance >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $finalBalance >= 0 ? 'Net Payable to Staff' : 'Net Advance Due from Staff' }}
                        </small>
                        <h4 class="fw-bold mb-0 fs-16 {{ $finalBalance >= 0 ? 'text-success' : 'text-danger' }} mt-1">
                            PKR {{ number_format(abs($finalBalance), 2) }}
                        </h4>
                    </div>
                </div>
            </div>

            <!-- STATEMENT TABLE -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                    <h5 class="fs-14 fw-bold m-0 text-dark">
                        <i class="mdi mdi-format-list-bulleted text-primary me-1"></i> Transaction Ledger
                    </h5>
                    <span class="badge bg-light text-muted border fs-11">{{ count($rawEntries) }} Transactions</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover align-middle mb-0 statement-table" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 45px;">#</th>
                                <th style="width: 95px;">Date</th>
                                <th style="width: 120px;">Voucher #</th>
                                <th style="width: 170px;">Type</th>
                                <th>Description / Details</th>
                                <th style="width: 110px;">Method</th>
                                <th class="text-end text-danger" style="width: 130px;">Debit (Paid / Adv)</th>
                                <th class="text-end text-success" style="width: 130px;">Credit (Salary)</th>
                                <th class="text-end text-dark" style="width: 140px;">Balance (PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($fromDate && $openingBalance != 0)
                                <tr class="opening-row">
                                    <td class="text-center text-muted fw-bold">-</td>
                                    <td>{{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }}</td>
                                    <td class="font-monospace text-muted">OPENING</td>
                                    <td><span class="badge bg-warning text-dark">Opening Balance</span></td>
                                    <td>Balance brought forward prior to {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }}</td>
                                    <td>—</td>
                                    <td class="text-end text-danger">{{ $openingBalance < 0 ? number_format(abs($openingBalance), 2) : '0.00' }}</td>
                                    <td class="text-end text-success">{{ $openingBalance > 0 ? number_format($openingBalance, 2) : '0.00' }}</td>
                                    <td class="text-end fw-bold text-dark">{{ number_format($openingBalance, 2) }}</td>
                                </tr>
                            @endif

                            @forelse ($rawEntries as $i => $row)
                                <tr>
                                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ \Carbon\Carbon::parse($row['date'])->format('d M, Y') }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace fs-11">
                                            {{ $row['voucher_no'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $row['badge_class'] }} fs-11">
                                            {{ $row['type'] }}
                                        </span>
                                    </td>
                                    <td class="text-wrap" style="max-width: 320px;">
                                        {{ $row['description'] }}
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-secondary text-secondary fs-11">{{ $row['payment_method'] }}</span>
                                    </td>
                                    <td class="text-end fw-semibold text-danger">
                                        {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}
                                    </td>
                                    <td class="text-end fw-semibold text-success">
                                        {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}
                                    </td>
                                    <td class="text-end fw-bold {{ $row['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($row['balance'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="mdi mdi-inbox-outline fs-24 d-block mb-1 text-muted"></i>
                                        No ledger transactions or payrolls found for this staff member in the selected period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="fw-bold bg-white border-top">
                            <tr>
                                <td colspan="6" class="text-end pe-3 fw-bold">Period Totals</td>
                                <td class="text-end text-danger fs-13">PKR {{ number_format($totalDebit, 2) }}</td>
                                <td class="text-end text-success fs-13">PKR {{ number_format($totalCredit, 2) }}</td>
                                <td class="text-end fs-13 {{ $finalBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                    PKR {{ number_format($finalBalance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function exportStaffLedgerExcel() {
        if (typeof XLSX === 'undefined') {
            alert('Excel export library is loading, please try again.');
            return;
        }

        const wb = XLSX.utils.book_new();
        const headers = ["#", "Date", "Voucher #", "Type", "Description / Details", "Method", "Debit (Paid / Adv) PKR", "Credit (Salary) PKR", "Balance PKR"];
        const rows = [];

        @if ($fromDate && $openingBalance != 0)
        rows.push([
            "—",
            "{{ \Carbon\Carbon::parse($fromDate)->format('Y-m-d') }}",
            "OPENING",
            "Opening Balance",
            "Balance brought forward prior to {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }}",
            "—",
            {{ $openingBalance < 0 ? abs($openingBalance) : 0 }},
            {{ $openingBalance > 0 ? $openingBalance : 0 }},
            {{ $openingBalance }}
        ]);
        @endif

        @foreach ($rawEntries as $i => $row)
        rows.push([
            {{ $i + 1 }},
            "{{ $row['date'] }}",
            "{{ addslashes($row['voucher_no']) }}",
            "{{ addslashes($row['type']) }}",
            "{{ addslashes($row['description']) }}",
            "{{ addslashes($row['payment_method']) }}",
            {{ (float) ($row['debit'] ?? 0) }},
            {{ (float) ($row['credit'] ?? 0) }},
            {{ (float) ($row['balance'] ?? 0) }}
        ]);
        @endforeach

        rows.push([]);
        rows.push([
            "TOTALS", "", "", "", "Period Totals:", "",
            {{ (float) $totalDebit }},
            {{ (float) $totalCredit }},
            {{ (float) $finalBalance }}
        ]);

        const ws = XLSX.utils.aoa_to_sheet([headers, ...rows]);
        XLSX.utils.book_append_sheet(wb, ws, "Staff Statement");
        XLSX.writeFile(wb, "Staff_Ledger_{{ Str::slug($staff->first_name . '_' . $staff->last_name) }}_{{ date('Ymd_His') }}.xlsx");
    }

    function exportStaffLedgerPDF() {
        if (typeof window.jspdf === 'undefined' || typeof window.jspdf.jsPDF === 'undefined') {
            window.print();
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const pageW = doc.internal.pageSize.getWidth();

        // Header Banner
        doc.setFillColor(15, 83, 94);
        doc.rect(0, 0, pageW, 22, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(14);
        doc.setFont(undefined, 'bold');
        doc.text("DUA TRAVEL & TOURS — STAFF STATEMENT OF ACCOUNT", 14, 10);
        doc.setFontSize(8.5);
        doc.setFont(undefined, 'normal');
        doc.text("Staff: {{ addslashes($staff->first_name . ' ' . $staff->last_name) }} ({{ addslashes($staff->designation->name ?? 'Staff') }}) | Date: " + new Date().toLocaleString(), 14, 17);

        // Summary
        doc.setTextColor(30, 30, 30);
        doc.setFontSize(8.5);
        doc.setFont(undefined, 'bold');
        doc.text("Total Paid / Advances: PKR {{ number_format($totalDebit, 2) }} | Total Salary Earned: PKR {{ number_format($totalCredit, 2) }} | Closing: PKR {{ number_format(abs($finalBalance), 2) }} ({{ $finalBalance >= 0 ? 'Payable' : 'Advance' }})", 14, 28);

        const tableRows = [];

        @if ($fromDate && $openingBalance != 0)
        tableRows.push([
            "—",
            "{{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }}",
            "OPENING",
            "Opening Balance",
            "Balance B/F",
            "—",
            "{{ $openingBalance < 0 ? number_format(abs($openingBalance), 2) : '—' }}",
            "{{ $openingBalance > 0 ? number_format($openingBalance, 2) : '—' }}",
            "{{ number_format($openingBalance, 2) }}"
        ]);
        @endif

        @foreach ($rawEntries as $i => $row)
        tableRows.push([
            "{{ $i + 1 }}",
            "{{ \Carbon\Carbon::parse($row['date'])->format('d M, Y') }}",
            "{{ addslashes($row['voucher_no']) }}",
            "{{ addslashes($row['type']) }}",
            "{{ addslashes(Str::limit($row['description'], 45)) }}",
            "{{ addslashes($row['payment_method']) }}",
            "{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}",
            "{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}",
            "{{ number_format($row['balance'], 2) }}"
        ]);
        @endforeach

        tableRows.push([
            "", "", "", "TOTALS:", "", "",
            "PKR {{ number_format($totalDebit, 2) }}",
            "PKR {{ number_format($totalCredit, 2) }}",
            "PKR {{ number_format($finalBalance, 2) }}"
        ]);

        doc.autoTable({
            startY: 32,
            head: [["#", "Date", "Voucher #", "Type", "Description", "Method", "Debit (Paid)", "Credit (Earned)", "Balance (PKR)"]],
            body: tableRows,
            theme: 'striped',
            headStyles: { fillColor: [15, 83, 94], textColor: 255, fontSize: 8, fontStyle: 'bold' },
            bodyStyles: { fontSize: 7.5 },
            columnStyles: {
                6: { halign: 'right', textColor: [180, 0, 0] },
                7: { halign: 'right', textColor: [0, 130, 0] },
                8: { halign: 'right', fontStyle: 'bold' }
            },
            margin: { left: 10, right: 10 }
        });

        doc.save("Staff_Ledger_{{ Str::slug($staff->first_name . '_' . $staff->last_name) }}_{{ date('Ymd') }}.pdf");
    }
</script>

@endsection
