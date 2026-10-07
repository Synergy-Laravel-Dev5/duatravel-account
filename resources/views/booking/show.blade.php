<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Voucher #{{ $booking->voucher_number ?? $booking->id }} — {{ $booking->client->name ?? 'Booking' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.2.96/css/materialdesignicons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    @php
        $pkgType = strtolower($booking->package_type ?? 'umrah');
        $isHajj = ($pkgType === 'hajj');
        $currency = $booking->currency ?: 'PKR';
        
        $percentPaid = $booking->total_amount > 0 ? min(100, round(($booking->total_received / $booking->total_amount) * 100)) : 0;
        $displayName = $booking->company->name ?? $booking->company->company_name ?? $booking->client->name ?? 'Client';
    @endphp

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #0f535e;
            --primary-dark: #0a3a42;
            --primary-light: #e6f2f4;
            --secondary: #c9a84c;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --card-bg: #ffffff;
            --bg-page: #f8fafc;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        body {
            background-color: var(--bg-page);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-main);
            font-size: 12.5px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .show-container {
            max-width: 1080px;
            margin: 0 auto;
            padding: 20px 16px 50px;
        }

        /* Top Bar */
        .top-action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 6px 14px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .brand-pill img {
            height: 28px;
            width: auto;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 7px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }

        .btn-action-primary {
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(15, 83, 94, 0.25);
        }
        .btn-action-primary:hover {
            background: var(--primary-dark);
            color: #ffffff;
        }

        .btn-action-secondary {
            background: #ffffff;
            color: var(--text-main);
            border-color: var(--border);
        }
        .btn-action-secondary:hover {
            background: #f1f5f9;
        }

        /* Voucher Header Banner */
        .voucher-header {
            background: linear-gradient(135deg, #0b3d45 0%, #0f535e 60%, #174852 100%);
            border-radius: 12px;
            color: #ffffff;
            padding: 20px 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(15, 83, 94, 0.12);
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .voucher-header-grid {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            position: relative;
            z-index: 2;
        }

        .voucher-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 2px;
            letter-spacing: -0.2px;
        }

        .badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .badge-status-confirmed { background: #10b981; color: #fff; }
        .badge-status-pending   { background: #f59e0b; color: #fff; }
        .badge-status-cancelled { background: #ef4444; color: #fff; }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-box {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .stat-box-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 2px;
        }

        .stat-box-val {
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
        }

        /* Standard App Card */
        .app-card {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .app-card-head {
            padding: 10px 16px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .app-card-head .head-title {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .app-card-head i {
            color: var(--primary);
            font-size: 15px;
        }

        .app-card-body {
            padding: 14px 16px;
        }

        /* Clean Tables */
        .voucher-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }

        .voucher-table thead th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }

        .voucher-table tbody td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            color: var(--text-main);
        }

        .voucher-table tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        /* Key-Value Pair Grid */
        .kv-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px 16px;
        }

        @media (max-width: 768px) {
            .kv-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .kv-item {
            display: flex;
            flex-direction: column;
        }

        .kv-key {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }

        .kv-val {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
        }

        /* Flight Route Ribbon */
        .flight-route-box {
            background: linear-gradient(90deg, #f8fafc, #f1f5f9, #f8fafc);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .flight-sector {
            text-align: center;
        }

        .flight-sector-code {
            font-size: 16px;
            font-weight: 800;
            color: var(--primary);
        }

        .flight-sector-time {
            font-size: 10.5px;
            color: var(--text-muted);
        }

        .flight-sector-arrow {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 16px;
        }

        .flight-sector-line {
            width: 100%;
            height: 1px;
            background: #cbd5e1;
            margin: 3px 0;
        }

        /* Doc Thumbnails */
        .doc-thumb-mini {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border: 1px solid var(--border);
            border-radius: 5px;
            background: #ffffff;
            cursor: pointer;
            font-size: 10.5px;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            transition: all 0.15s;
        }

        .doc-thumb-mini:hover {
            background: var(--primary-light);
            border-color: var(--primary);
        }

        /* Costing Table */
        .costing-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .costing-table td {
            padding: 8px 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .costing-table tr.total-row td {
            background: #f8fafc;
            border-top: 1.5px solid #cbd5e1;
            border-bottom: 1.5px solid #cbd5e1;
            font-weight: 700;
        }

        .costing-section.costing-hidden .costing-table {
            display: none;
        }

        .costing-hidden-msg {
            display: none;
            padding: 24px;
            text-align: center;
            color: var(--text-muted);
        }

        .costing-section.costing-hidden .costing-hidden-msg {
            display: block;
        }

        /* Modal */
        .doc-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.8);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .doc-modal.show {
            display: flex;
        }

        .doc-modal-content {
            background: #ffffff;
            border-radius: 12px;
            width: 760px;
            max-width: 95vw;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35);
        }

        .doc-modal-header {
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .doc-modal-body {
            padding: 16px;
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            flex: 1;
        }

        .doc-modal-body img {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 4px;
        }

        .doc-modal-footer {
            padding: 10px 16px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            background: #ffffff;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; font-size: 11px !important; }
            .show-container { padding: 0 !important; max-width: 100% !important; margin: 0 !important; }
            .voucher-header { box-shadow: none !important; border: 1px solid #cbd5e1 !important; padding: 14px 18px !important; }
            .app-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; page-break-inside: avoid !important; margin-bottom: 10px !important; }
            .voucher-table thead th { background: #e2e8f0 !important; color: #000 !important; }
            .costing-section.costing-hidden { display: none !important; }
            @page { margin: 8mm 10mm; }
        }
    </style>
</head>

<body>

    <!-- Document Modal -->
    <div class="doc-modal no-print" id="docModal" onclick="closeModalOnOverlay(event)">
        <div class="doc-modal-content">
            <div class="doc-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="mdi mdi-file-document-outline fs-18 text-primary"></i>
                    <strong id="docModalTitle">Document Preview</strong>
                </div>
                <button type="button" class="btn-action btn-action-secondary p-1" onclick="closeDocModal()">&times;</button>
            </div>
            <div class="doc-modal-body" id="docModalBody"></div>
            <div class="doc-modal-footer">
                <button type="button" class="btn-action btn-action-secondary" onclick="closeDocModal()">Close</button>
                <a href="#" id="docModalDownloadBtn" class="btn-action btn-action-primary" download>
                    <i class="mdi mdi-download"></i> Download Document
                </a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="show-container" id="printableBooking">

        <!-- Top Actions Bar -->
        <div class="top-action-bar no-print">
            <div class="brand-pill">
                <img src="{{ $isHajj ? asset('assets/images/logo/kgm.png') : asset('assets/images/logo/logo.png') }}"
                     alt="Logo" onerror="this.src='{{ asset('assets/images/logo-light.png') }}'">
                <span class="fw-bold text-dark fs-12">{{ $isHajj ? 'KGM Travel & Tours' : 'Dua Travels & Tours' }}</span>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('pilgrim.bulk-badges', $booking->id) }}" target="_blank" class="btn-action btn-action-secondary">
                    <i class="mdi mdi-card-account-details-outline"></i> Print Badges
                </a>
                <button type="button" class="btn-action btn-action-secondary" id="toggleCostBtn" onclick="toggleCostingVisibility()">
                    <i class="mdi mdi-eye-off-outline" id="toggleCostIcon"></i>
                    <span id="toggleCostText">Hide Costing</span>
                </button>
                <button type="button" class="btn-action btn-action-secondary" onclick="window.print()">
                    <i class="mdi mdi-printer"></i> Print Voucher
                </button>
                <button type="button" class="btn-action btn-action-primary" onclick="downloadBookingPdf()">
                    <i class="mdi mdi-download"></i> Download PDF
                </button>
                <a href="{{ route('booking.edit', $booking->id) }}" class="btn-action btn-action-secondary">
                    <i class="mdi mdi-square-edit-outline"></i> Edit
                </a>
                <a href="{{ route('booking.index') }}" class="btn-action btn-action-secondary">
                    <i class="mdi mdi-arrow-left"></i> Bookings List
                </a>
            </div>
        </div>

        <!-- Voucher Hero Banner -->
        <div class="voucher-header">
            <div class="voucher-header-grid">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge-tag" style="background: rgba(255,255,255,0.2);">
                            <i class="mdi mdi-kaaba me-1"></i> {{ strtoupper($pkgType) }} @if($booking->package_year) {{ $booking->package_year }} @endif
                        </span>
                        <span class="badge-tag badge-status-{{ $booking->status ?? 'confirmed' }}">
                            {{ ucfirst($booking->status ?? 'Confirmed') }}
                        </span>
                        @if($booking->voucher_number)
                            <span class="badge-tag" style="background: rgba(255,255,255,0.15);">
                                Voucher #: {{ $booking->voucher_number }}
                            </span>
                        @endif
                        <span class="badge-tag" style="background: rgba(201,168,76,0.3); color: #fde68a;">
                            Currency: {{ $currency }}
                        </span>
                    </div>

                    <h1 class="voucher-title">{{ $displayName }}</h1>
                    
                    <div style="opacity: 0.85; font-size: 11.5px;">
                        @if($booking->package_name)
                            <span><i class="mdi mdi-tag-outline me-1"></i>{{ $booking->package_name }}</span> · 
                        @endif
                        <span><i class="mdi mdi-account-group me-1"></i>{{ $booking->no_of_pax ?? 1 }} Passenger(s)</span>
                        @if($booking->booking_date)
                            · <span><i class="mdi mdi-calendar-check me-1"></i>Booking Date: {{ date('d M Y', strtotime($booking->booking_date)) }}</span>
                        @endif
                    </div>
                </div>

                <div class="text-end">
                    <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Total Booking Amount</div>
                    <div style="font-size: 24px; font-weight: 800; line-height: 1.1;">
                        {{ $currency }} {{ number_format($booking->total_amount, 0) }}
                    </div>
                    <div style="font-size: 11.5px; opacity: 0.9; margin-top: 3px;">
                        Received: <span style="color: #6ee7b7; font-weight: 700;">{{ $currency }} {{ number_format($booking->total_received, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4-Stat Box Row (Evenly distributed) -->
        <div class="stats-row">
            <div class="stat-box" style="border-left: 3px solid var(--primary);">
                <div class="stat-box-label">Total Amount</div>
                <div class="stat-box-val" style="color: var(--primary);">{{ $currency }} {{ number_format($booking->total_amount, 0) }}</div>
                <small class="text-muted fs-10">Total Services</small>
            </div>
            <div class="stat-box" style="border-left: 3px solid #10b981;">
                <div class="stat-box-label">Received</div>
                <div class="stat-box-val text-success">{{ $currency }} {{ number_format($booking->total_received, 0) }}</div>
                <small class="text-muted fs-10">{{ $percentPaid }}% Paid</small>
            </div>
            <div class="stat-box" style="border-left: 3px solid {{ $booking->balance > 0 ? '#ef4444' : '#10b981' }};">
                <div class="stat-box-label">Balance Due</div>
                <div class="stat-box-val {{ $booking->balance > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $currency }} {{ number_format($booking->balance, 0) }}
                </div>
                <small class="text-muted fs-10">{{ $booking->balance > 0 ? 'Pending' : 'Settled' }}</small>
            </div>
            <div class="stat-box" style="border-left: 3px solid #f59e0b;">
                <div class="stat-box-label">Base Pkg / Person</div>
                <div class="stat-box-val text-warning">{{ $currency }} {{ number_format($booking->package_cost, 0) }}</div>
                <small class="text-muted fs-10">{{ $booking->no_of_pax }} Pax</small>
            </div>
        </div>

        <!-- Client & Contact Overview Card -->
        <div class="app-card">
            <div class="app-card-head">
                <div class="head-title">
                    <i class="mdi mdi-account-box-outline"></i> Client & Lead Pilgrim Details
                </div>
                @if($booking->haji_id)
                    <span class="badge haji-badge px-2 py-1" style="background:#0f535e;color:#fff;border-radius:4px;">
                        Haji ID: {{ $booking->haji_id }}
                    </span>
                @endif
            </div>
            <div class="app-card-body">
                <div class="kv-grid">
                    <div class="kv-item">
                        <span class="kv-key">Client Name</span>
                        <span class="kv-val">{{ $displayName }}</span>
                    </div>
                    <div class="kv-item">
                        <span class="kv-key">Phone Number</span>
                        <span class="kv-val">{{ $booking->phone ?? ($booking->client->phone ?? '—') }}</span>
                    </div>
                    <div class="kv-item">
                        <span class="kv-key">Passport #</span>
                        <span class="kv-val">{{ $booking->passport_number ?? ($booking->client->passport_number ?? '—') }}</span>
                    </div>
                    <div class="kv-item">
                        <span class="kv-key">CNIC #</span>
                        <span class="kv-val">{{ $booking->cnic ?? ($booking->client->cnic ?? '—') }}</span>
                    </div>
                    <div class="kv-item">
                        <span class="kv-key">Care Of / Agent</span>
                        <span class="kv-val">{{ $booking->care_of ?? '—' }}</span>
                    </div>
                    <div class="kv-item">
                        <span class="kv-key">Emergency Contact</span>
                        <span class="kv-val">{{ $booking->emergency_phone ?? '—' }}</span>
                    </div>
                    <div class="kv-item">
                        <span class="kv-key">Room Number</span>
                        <span class="kv-val">{{ $booking->room_number ?? '—' }}</span>
                    </div>
                    <div class="kv-item">
                        <span class="kv-key">Nominee</span>
                        <span class="kv-val">{{ $booking->nominee_name ? $booking->nominee_name . ' (' . ($booking->nominee_relation ?? 'Nominee') . ')' : '—' }}</span>
                    </div>
                </div>

                {{-- Client Uploaded Documents --}}
                @if($booking->cnic_front || $booking->cnic_back || $booking->passport_photo || $booking->photo || $booking->medical_certificate)
                    <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                        <strong class="kv-key d-block w-100 mb-1">Attached Client Documents:</strong>
                        @if($booking->cnic_front)
                            @php $isPdf = Str::endsWith(strtolower($booking->cnic_front), '.pdf'); @endphp
                            <div class="doc-thumb-mini" onclick="openDocModal('{{ asset('storage/'.$booking->cnic_front) }}', '{{ $isPdf ? 'pdf' : 'image' }}', 'CNIC Front')">
                                <i class="mdi {{ $isPdf ? 'mdi-file-pdf-box text-danger' : 'mdi-file-image' }}"></i> CNIC Front
                            </div>
                        @endif
                        @if($booking->cnic_back)
                            @php $isPdf = Str::endsWith(strtolower($booking->cnic_back), '.pdf'); @endphp
                            <div class="doc-thumb-mini" onclick="openDocModal('{{ asset('storage/'.$booking->cnic_back) }}', '{{ $isPdf ? 'pdf' : 'image' }}', 'CNIC Back')">
                                <i class="mdi {{ $isPdf ? 'mdi-file-pdf-box text-danger' : 'mdi-file-image' }}"></i> CNIC Back
                            </div>
                        @endif
                        @if($booking->passport_photo)
                            @php $isPdf = Str::endsWith(strtolower($booking->passport_photo), '.pdf'); @endphp
                            <div class="doc-thumb-mini" onclick="openDocModal('{{ asset('storage/'.$booking->passport_photo) }}', '{{ $isPdf ? 'pdf' : 'image' }}', 'Passport Photo')">
                                <i class="mdi {{ $isPdf ? 'mdi-file-pdf-box text-danger' : 'mdi-file-image' }}"></i> Passport
                            </div>
                        @endif
                        @if($booking->photo)
                            @php $isPdf = Str::endsWith(strtolower($booking->photo), '.pdf'); @endphp
                            <div class="doc-thumb-mini" onclick="openDocModal('{{ asset('storage/'.$booking->photo) }}', '{{ $isPdf ? 'pdf' : 'image' }}', 'Haji Photo')">
                                <i class="mdi {{ $isPdf ? 'mdi-file-pdf-box text-danger' : 'mdi-file-image' }}"></i> Photo
                            </div>
                        @endif
                        @if($booking->medical_certificate)
                            @php $isPdf = Str::endsWith(strtolower($booking->medical_certificate), '.pdf'); @endphp
                            <div class="doc-thumb-mini" onclick="openDocModal('{{ asset('storage/'.$booking->medical_certificate) }}', '{{ $isPdf ? 'pdf' : 'image' }}', 'Medical Certificate')">
                                <i class="mdi {{ $isPdf ? 'mdi-file-pdf-box text-danger' : 'mdi-file-image' }}"></i> Medical Cert
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- 1. HOTEL ACCOMMODATION (FIRST AS REQUESTED) -->
        <div class="app-card">
            <div class="app-card-head">
                <div class="head-title">
                    <i class="mdi mdi-hotel"></i> Hotel Accommodation Details
                </div>
                <span class="badge bg-primary px-2 py-0 fs-11">{{ $booking->hotels->count() }} Hotel Stay(s)</span>
            </div>
            <div class="app-card-body p-0">
                @if($booking->hotels && $booking->hotels->count() > 0)
                    <table class="voucher-table">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Hotel Name</th>
                                <th>Room Type</th>
                                <th class="text-center">Room #</th>
                                <th class="text-center">Rooms</th>
                                <th class="text-center">Nights</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Supplier</th>
                                <th class="text-center">Voucher</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->hotels as $hotel)
                                <tr>
                                    <td>
                                        <span class="badge {{ strtolower($hotel->location ?? '') === 'makkah' ? 'bg-soft-warning text-warning' : 'bg-soft-success text-success' }} fw-bold text-uppercase fs-10">
                                            {{ $hotel->location ?? 'Hotel' }}
                                        </span>
                                    </td>
                                    <td><strong class="text-primary">{{ $hotel->hotel_name }}</strong></td>
                                    <td>{{ ucfirst($hotel->room_type ?? 'Double') }}</td>
                                    <td class="text-center fw-bold">{{ $hotel->room_number ?? '—' }}</td>
                                    <td class="text-center">{{ $hotel->no_of_rooms ?? 1 }}</td>
                                    <td class="text-center fw-bold">{{ $hotel->no_of_nights ?? 1 }}</td>
                                    <td>{{ $hotel->check_in ? date('d M Y', strtotime($hotel->check_in)) : '—' }}</td>
                                    <td>{{ $hotel->check_out ? date('d M Y', strtotime($hotel->check_out)) : '—' }}</td>
                                    <td>{{ $hotel->supplier->name ?? '—' }}</td>
                                    <td class="text-center">
                                        @if($hotel->hotel_voucher)
                                            @php
                                                $hPath = asset('storage/' . $hotel->hotel_voucher);
                                                $hPdf = Str::endsWith(strtolower($hotel->hotel_voucher), '.pdf');
                                            @endphp
                                            <button type="button" class="doc-thumb-mini" onclick="openDocModal('{{ $hPath }}', '{{ $hPdf ? 'pdf' : 'image' }}', 'Hotel Voucher — {{ $hotel->hotel_name }}')">
                                                <i class="mdi {{ $hPdf ? 'mdi-file-pdf-box text-danger' : 'mdi-file-document-outline' }}"></i> View
                                            </button>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-3 text-center text-muted fs-12">No hotel accommodations recorded.</div>
                @endif
            </div>
        </div>

        <!-- 2. TRANSPORTATION ROUTES (SEPARATE CARD AS REQUESTED) -->
        <div class="app-card">
            <div class="app-card-head">
                <div class="head-title">
                    <i class="mdi mdi-bus"></i> Transportation & Transfer Routes
                </div>
                <span class="badge bg-warning text-dark px-2 py-0 fs-11">{{ $booking->transports->count() }} Route(s)</span>
            </div>
            <div class="app-card-body p-0">
                @if($booking->transports && $booking->transports->count() > 0)
                    <table class="voucher-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;" class="text-center">#</th>
                                <th>Sector / Route</th>
                                <th>Transport Type</th>
                                <th>Transport Supplier</th>
                                <th>Special Notes</th>
                                <th class="text-center">Ticket / Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->transports as $t)
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                    <td><strong class="text-dark">{{ $t->route }}</strong></td>
                                    <td>
                                        <span class="badge bg-light text-dark fw-bold">
                                            {{ ucfirst(str_replace('_', ' ', $t->transport_type ?? 'Private Car')) }}
                                        </span>
                                    </td>
                                    <td>{{ $t->supplier->name ?? '—' }}</td>
                                    <td>{{ $t->notes ?? '—' }}</td>
                                    <td class="text-center">
                                        @if($t->transport_ticket)
                                            @php
                                                $tPath = asset('storage/' . $t->transport_ticket);
                                                $tPdf = Str::endsWith(strtolower($t->transport_ticket), '.pdf');
                                            @endphp
                                            <button type="button" class="doc-thumb-mini" onclick="openDocModal('{{ $tPath }}', '{{ $tPdf ? 'pdf' : 'image' }}', 'Transport Ticket — {{ $t->route }}')">
                                                <i class="mdi {{ $tPdf ? 'mdi-file-pdf-box text-danger' : 'mdi-ticket-confirmation-outline' }}"></i> View
                                            </button>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-3 text-center text-muted fs-12">No transport routes recorded.</div>
                @endif
            </div>
        </div>

        <!-- 3. FLIGHT SCHEDULE & TICKETS (SEPARATE CARD AS REQUESTED) -->
        <div class="app-card">
            <div class="app-card-head">
                <div class="head-title">
                    <i class="mdi mdi-airplane"></i> Flight Schedule & Air Tickets
                </div>
                @if($booking->departure_airline)
                    <span class="badge bg-light text-dark fw-bold">{{ $booking->departure_airline }}</span>
                @endif
            </div>
            <div class="app-card-body">
                @if($booking->departure_date || $booking->arrival_date || $booking->departure_flight)
                    <div class="flight-route-box">
                        <div class="flight-sector">
                            <div class="flight-sector-code">{{ $booking->departure_flight ?? 'DEP' }}</div>
                            <div class="flight-sector-time">{{ $booking->departure_date ? date('d M Y', strtotime($booking->departure_date)) : '—' }}</div>
                            @if($booking->departure_time)<div class="flight-sector-time">{{ $booking->departure_time }}</div>@endif
                        </div>
                        <div class="flight-sector-arrow">
                            <i class="mdi mdi-airplane-takeoff fs-18 text-primary"></i>
                            <div class="flight-sector-line"></div>
                            <span class="fs-10 text-muted">{{ $booking->departure_airline ?? 'Flight Sector' }}</span>
                        </div>
                        <div class="flight-sector">
                            <div class="flight-sector-code">{{ $booking->arrival_flight ?? 'ARR' }}</div>
                            <div class="flight-sector-time">{{ $booking->arrival_date ? date('d M Y', strtotime($booking->arrival_date)) : '—' }}</div>
                            @if($booking->arrival_time)<div class="flight-sector-time">{{ $booking->arrival_time }}</div>@endif
                        </div>
                    </div>
                @endif

                <div class="kv-grid">
                    <div class="kv-item">
                        <span class="kv-key">Departure Airline & Flight</span>
                        <span class="kv-val">{{ $booking->departure_airline ?? '—' }} {{ $booking->departure_flight ?? '' }}</span>
                    </div>
                    <div class="kv-item">
                        <span class="kv-key">Departure PNR / Ticket #</span>
                        <span class="kv-val"><strong>{{ $booking->departure_pnr ?? '—' }}</strong></span>
                    </div>
                    <div class="kv-item">
                        <span class="kv-key">Arrival Airline & Flight</span>
                        <span class="kv-val">{{ $booking->arrival_airline ?? '—' }} {{ $booking->arrival_flight ?? '' }}</span>
                    </div>
                    <div class="kv-item">
                        <span class="kv-key">Arrival PNR / Ticket #</span>
                        <span class="kv-val"><strong>{{ $booking->arrival_pnr ?? '—' }}</strong></span>
                    </div>
                    <div class="kv-item">
                        <span class="kv-key">Flight Supplier / Vendor</span>
                        <span class="kv-val">{{ $booking->flightSupplier->name ?? '—' }}</span>
                    </div>
                    @if($booking->flight_roe && $booking->flight_roe != 1)
                    <div class="kv-item">
                        <span class="kv-key">Exchange Rate (ROE)</span>
                        <span class="kv-val">{{ number_format($booking->flight_roe, 4) }}</span>
                    </div>
                    @endif
                    @if($booking->flight_attachment)
                    <div class="kv-item">
                        <span class="kv-key">Ticket Attachment</span>
                        <span class="kv-val">
                            @php
                                $fPath = asset('storage/' . $booking->flight_attachment);
                                $fPdf = Str::endsWith(strtolower($booking->flight_attachment), '.pdf');
                            @endphp
                            <button type="button" class="doc-thumb-mini" onclick="openDocModal('{{ $fPath }}', '{{ $fPdf ? 'pdf' : 'image' }}', 'Flight Ticket')">
                                <i class="mdi {{ $fPdf ? 'mdi-file-pdf-box text-danger' : 'mdi-file-document-outline' }}"></i> View Ticket
                            </button>
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 4. TRAVELLING PILGRIMS & PASSENGERS (SEPARATE CARD) -->
        @if($booking->persons && $booking->persons->count() > 0)
            <div class="app-card">
                <div class="app-card-head">
                    <div class="head-title">
                        <i class="mdi mdi-account-group"></i> Travelling Pilgrims & Passenger Details
                    </div>
                    <span class="badge bg-primary px-2 py-0 fs-11">{{ $booking->persons->count() }} Passenger(s)</span>
                </div>
                <div class="app-card-body p-0">
                    <table class="voucher-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;" class="text-center">#</th>
                                <th>Passenger Full Name & Haji ID</th>
                                <th>Passport & DOB</th>
                                <th>CNIC & Phone</th>
                                <th>City & Blood</th>
                                <th>Attached Documents</th>
                                <th>Nominee</th>
                                <th class="text-center no-print">Pass & Badge</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->persons as $person)
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                    <td>
                                        <strong class="text-dark">{{ $person->full_name }}</strong>
                                        @if($person->haji_id)
                                            <br><span class="badge haji-badge px-2 py-0 fs-10 mt-1" style="background:#0f535e;color:#fff;">
                                                <i class="mdi mdi-card-account-details-outline me-1"></i>{{ $person->haji_id }}
                                            </span>
                                        @endif
                                        @if($person->father_name)
                                            <br><small class="text-muted">S/O, D/O: {{ $person->father_name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $person->passport_number ?? '—' }}</strong>
                                        @if($person->dob)
                                            <br><small class="text-muted">{{ date('d M Y', strtotime($person->dob)) }}</small>
                                        @endif
                                        @if($person->gender)
                                            · <span class="badge bg-light text-dark fs-10">{{ ucfirst($person->gender) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span>{{ $person->cnic ?? '—' }}</span>
                                        @if($person->phone)
                                            <br><small class="text-muted"><i class="mdi mdi-phone fs-10"></i> {{ $person->phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span>{{ $person->city ?? '—' }}</span>
                                        @if($person->blood_group)
                                            <br><span class="badge bg-soft-danger text-danger fw-bold fs-10">{{ $person->blood_group }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($person->cnic_front)
                                                @php $isPdf = Str::endsWith(strtolower($person->cnic_front), '.pdf'); @endphp
                                                <button type="button" class="doc-thumb-mini" onclick="openDocModal('{{ asset('storage/'.$person->cnic_front) }}', '{{ $isPdf ? 'pdf' : 'image' }}', 'CNIC Front — {{ $person->full_name }}')">
                                                    CNIC F
                                                </button>
                                            @endif
                                            @if($person->cnic_back)
                                                @php $isPdf = Str::endsWith(strtolower($person->cnic_back), '.pdf'); @endphp
                                                <button type="button" class="doc-thumb-mini" onclick="openDocModal('{{ asset('storage/'.$person->cnic_back) }}', '{{ $isPdf ? 'pdf' : 'image' }}', 'CNIC Back — {{ $person->full_name }}')">
                                                    CNIC B
                                                </button>
                                            @endif
                                            @if($person->passport_photo)
                                                @php $isPdf = Str::endsWith(strtolower($person->passport_photo), '.pdf'); @endphp
                                                <button type="button" class="doc-thumb-mini" onclick="openDocModal('{{ asset('storage/'.$person->passport_photo) }}', '{{ $isPdf ? 'pdf' : 'image' }}', 'Passport — {{ $person->full_name }}')">
                                                    Pass
                                                </button>
                                            @endif
                                            @if($person->photo)
                                                @php $isPdf = Str::endsWith(strtolower($person->photo), '.pdf'); @endphp
                                                <button type="button" class="doc-thumb-mini" onclick="openDocModal('{{ asset('storage/'.$person->photo) }}', '{{ $isPdf ? 'pdf' : 'image' }}', 'Photo — {{ $person->full_name }}')">
                                                    Photo
                                                </button>
                                            @endif
                                            @if(!$person->cnic_front && !$person->cnic_back && !$person->passport_photo && !$person->photo)
                                                <span class="text-muted">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($person->nominee_name)
                                            <div class="fs-11">
                                                <strong>{{ $person->nominee_name }}</strong>
                                                @if($person->nominee_relation)<br><small class="text-muted">{{ $person->nominee_relation }}</small>@endif
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center no-print">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('pilgrim.badge', $person->id) }}" target="_blank" class="doc-thumb-mini" title="Print Lanyard Badge">
                                                <i class="mdi mdi-card-account-details-outline text-primary"></i> Badge
                                            </a>
                                            <a href="{{ route('pilgrim.epass', $person->id) }}" target="_blank" class="doc-thumb-mini" title="Live Mobile E-Pass">
                                                <i class="mdi mdi-qrcode text-success"></i> E-Pass
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- 5. VISA PROCESSING RECORDS (SEPARATE CARD) -->
        @if($booking->visas && $booking->visas->count() > 0)
            <div class="app-card">
                <div class="app-card-head">
                    <div class="head-title">
                        <i class="mdi mdi-passport"></i> Visa Records & Documentation
                    </div>
                    <span class="badge bg-secondary text-white px-2 py-0 fs-11">{{ $booking->visas->count() }} Visa(s)</span>
                </div>
                <div class="app-card-body p-0">
                    <table class="voucher-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;" class="text-center">#</th>
                                <th>Pilgrim Name</th>
                                <th>Passport Number</th>
                                <th>DOB</th>
                                <th>Visa Supplier / Company</th>
                                <th>Send To</th>
                                <th>Status</th>
                                <th class="text-center">Attachment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->visas as $visa)
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                    <td><strong class="text-dark">{{ $visa->given_name ?? '—' }}</strong></td>
                                    <td>{{ $visa->passport_number ?? '—' }}</td>
                                    <td>{{ $visa->date_of_birth ? date('d M Y', strtotime($visa->date_of_birth)) : '—' }}</td>
                                    <td>{{ $visa->supplier->name ?? ($visa->company ?? '—') }}</td>
                                    <td>{{ ucfirst($visa->send_to ?? 'Shirka') }}</td>
                                    <td>
                                        <span class="badge bg-soft-success text-success fw-bold">
                                            {{ ucfirst($visa->status ?? 'Approved') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($visa->visa_attachment)
                                            @php
                                                $vPath = asset('storage/' . $visa->visa_attachment);
                                                $vPdf = Str::endsWith(strtolower($visa->visa_attachment), '.pdf');
                                            @endphp
                                            <button type="button" class="doc-thumb-mini" onclick="openDocModal('{{ $vPath }}', '{{ $vPdf ? 'pdf' : 'image' }}', 'Visa Document — {{ $visa->given_name }}')">
                                                <i class="mdi {{ $vPdf ? 'mdi-file-pdf-box text-danger' : 'mdi-file-document-outline' }}"></i> View
                                            </button>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- 6. OFFICIAL FINANCIAL BREAKDOWN & PAYMENT LEDGER (SEPARATE CARD) -->
        <div class="app-card costing-section" id="costingCard">
            <div class="app-card-head">
                <div class="head-title">
                    <i class="mdi mdi-cash-multiple"></i> Financial Breakdown & Payment Ledger
                </div>
                <span class="badge bg-soft-primary text-primary fs-11">Official Financial Record</span>
            </div>
            <div class="app-card-body p-0">
                <table class="costing-table">
                    @if($booking->package_cost > 0)
                    <tr>
                        <td><strong>Package Base Cost</strong></td>
                        <td class="text-muted">{{ $currency }} {{ number_format($booking->package_cost, 0) }} × {{ $booking->no_of_pax ?? 1 }} Pax</td>
                        <td class="text-end fw-semibold">{{ $currency }} {{ number_format($booking->package_cost * ($booking->no_of_pax ?? 1), 0) }}</td>
                    </tr>
                    @endif

                    @if($booking->hotel_charges > 0)
                    <tr>
                        <td>Hotel Accommodation Total</td>
                        <td class="text-muted">All hotel stays</td>
                        <td class="text-end fw-semibold">{{ $currency }} {{ number_format($booking->hotel_charges, 0) }}</td>
                    </tr>
                    @endif

                    @if($booking->flight_charges > 0)
                    <tr>
                        <td>Flight Charges Total</td>
                        <td class="text-muted">Airlines & booking tickets</td>
                        <td class="text-end fw-semibold">{{ $currency }} {{ number_format($booking->flight_charges, 0) }}</td>
                    </tr>
                    @endif

                    @if($booking->transport_charges > 0)
                    <tr>
                        <td>Transportation Total</td>
                        <td class="text-muted">All designated routes</td>
                        <td class="text-end fw-semibold">{{ $currency }} {{ number_format($booking->transport_charges, 0) }}</td>
                    </tr>
                    @endif

                    @if($booking->visa_charges > 0)
                    <tr>
                        <td>Visa Processing Total</td>
                        <td class="text-muted">Consulate & Shirka fees</td>
                        <td class="text-end fw-semibold">{{ $currency }} {{ number_format($booking->visa_charges, 0) }}</td>
                    </tr>
                    @endif

                    @if($booking->additional_services_amount > 0)
                    <tr>
                        <td>Additional Services</td>
                        <td class="text-muted">{!! strip_tags($booking->additional_services_detail) !!}</td>
                        <td class="text-end fw-semibold">{{ $currency }} {{ number_format($booking->additional_services_amount, 0) }}</td>
                    </tr>
                    @endif

                    @if($booking->other_charges > 0)
                    <tr>
                        <td>Other / Qurbani Charges</td>
                        <td class="text-muted">Supplementary services</td>
                        <td class="text-end fw-semibold">{{ $currency }} {{ number_format($booking->other_charges, 0) }}</td>
                    </tr>
                    @endif

                    @if($booking->discount > 0)
                    <tr>
                        <td class="text-danger">Promotional Discount</td>
                        <td class="text-muted">Special markdown</td>
                        <td class="text-end text-danger fw-semibold">- {{ $currency }} {{ number_format($booking->discount, 0) }}</td>
                    </tr>
                    @endif

                    <tr class="total-row">
                        <td class="text-primary fs-13"><strong>Total Booking Payable</strong></td>
                        <td></td>
                        <td class="text-end text-primary fs-14"><strong>{{ $currency }} {{ number_format($booking->total_amount, 0) }}</strong></td>
                    </tr>

                    <tr>
                        <td class="text-success fw-semibold">Total Amount Received</td>
                        <td></td>
                        <td class="text-end text-success fw-bold">{{ $currency }} {{ number_format($booking->total_received, 0) }}</td>
                    </tr>

                    <tr class="total-row">
                        <td class="{{ $booking->balance > 0 ? 'text-danger' : 'text-success' }} fs-13">
                            <strong>Net Balance Due</strong>
                        </td>
                        <td></td>
                        <td class="text-end {{ $booking->balance > 0 ? 'text-danger' : 'text-success' }} fs-15 fw-bold">
                            {{ $currency }} {{ number_format($booking->balance, 0) }}
                        </td>
                    </tr>
                </table>

                <div class="costing-hidden-msg">
                    <i class="mdi mdi-eye-off-outline fs-24 text-secondary d-block mb-1"></i>
                    <strong>Costing Section Hidden</strong>
                    <p class="fs-11 text-muted mb-0">Financial figures are hidden for customer-facing voucher.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script>
        function openDocModal(url, type, title) {
            const modal = document.getElementById('docModal');
            const body = document.getElementById('docModalBody');
            const titleEl = document.getElementById('docModalTitle');
            const downloadBtn = document.getElementById('docModalDownloadBtn');

            titleEl.textContent = title || 'Document Preview';
            downloadBtn.href = url;

            const fname = (title || 'document').replace(/[^a-z0-9\-_. ]/gi, '').replace(/\s+/g, '-');
            const ext = url.split('.').pop().split('?')[0];
            downloadBtn.setAttribute('download', fname + '.' + ext);

            if (type === 'pdf') {
                body.innerHTML = `<embed src="${url}" type="application/pdf" style="width:100%;height:70vh;border-radius:6px;">`;
            } else {
                body.innerHTML = `<img src="${url}" alt="${title || 'Document'}">`;
            }

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeDocModal() {
            const modal = document.getElementById('docModal');
            modal.classList.remove('show');
            document.getElementById('docModalBody').innerHTML = '';
            document.body.style.overflow = '';
        }

        function closeModalOnOverlay(e) {
            if (e.target.id === 'docModal') {
                closeDocModal();
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeDocModal();
        });

        // Toggle Costing
        let costingVisible = true;
        function toggleCostingVisibility() {
            const card = document.getElementById('costingCard');
            const icon = document.getElementById('toggleCostIcon');
            const text = document.getElementById('toggleCostText');

            if (costingVisible) {
                card.classList.add('costing-hidden');
                icon.className = 'mdi mdi-eye-outline';
                text.textContent = 'Show Costing';
            } else {
                card.classList.remove('costing-hidden');
                icon.className = 'mdi mdi-eye-off-outline';
                text.textContent = 'Hide Costing';
            }
            costingVisible = !costingVisible;
        }

        // PDF Generation with A4 Page Optimization
        function downloadBookingPdf() {
            const element = document.getElementById('printableBooking');
            const noPrint = document.querySelectorAll('.no-print');
            noPrint.forEach(el => el.style.display = 'none');

            const clientName = '{{ addslashes($displayName) }}'.replace(/\s+/g, '-');
            const opt = {
                margin: [6, 8, 6, 8],
                filename: `Voucher_${clientName}_#{{ $booking->voucher_number ?? $booking->id }}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, logging: false },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                noPrint.forEach(el => el.style.display = '');
            }).catch(err => {
                noPrint.forEach(el => el.style.display = '');
                console.error(err);
            });
        }
    </script>
</body>

</html>
