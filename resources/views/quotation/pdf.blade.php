<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>VIP Quotation - {{ $quotation->quotation_number }}</title>
    <style>
        @page {
            margin: 10mm 10mm 12mm 10mm;
            size: A4 portrait;
        }

        :root {
            --navy: #1b1f4b;
            --navy-light: #2c326e;
            --gold: #d9a441;
            --gold-dark: #b8860b;
            --gold-light: #fef8ee;
            --peach: #f3cfa0;
            --grey-light: #f8fafc;
            --border-color: #cbd5e1;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            line-height: 1.4;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* Top Action Bar for web preview / print */
        .no-print-bar {
            background: #1b1f4b;
            color: #d9a441;
            padding: 12px 24px;
            margin-bottom: 20px;
            text-align: right;
            border-radius: 6px;
            border-bottom: 3px solid #d9a441;
        }
        .no-print-bar button, .no-print-bar a {
            background: #d9a441;
            color: #1b1f4b;
            padding: 8px 18px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 800;
            border: none;
            cursor: pointer;
            display: inline-block;
            margin-left: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .no-print-bar a.btn-download {
            background: #059669;
            color: #ffffff;
        }

        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                background: #fff;
                padding: 0;
            }
        }

        /* Page Break Rules */
        .page-break {
            page-break-after: always;
        }
        .keep-together {
            page-break-inside: avoid !important;
        }
        tr {
            page-break-inside: avoid !important;
        }
        thead {
            display: table-header-group;
        }
        tfoot {
            display: table-footer-group;
        }

        /* Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .fw-semibold { font-weight: 600; }
        .text-navy { color: #1b1f4b; }
        .text-gold { color: #b8860b; }
        .text-success { color: #059669; }
        .text-danger { color: #dc2626; }
        .text-muted { color: #64748b; }
        .text-uppercase { text-transform: uppercase; }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        /* Header Banner (Luxury 2-Box Header) */
        .header-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: collapse;
        }

        .header-left {
            background-color: #1b1f4b;
            color: #ffffff;
            padding: 15px 18px;
            vertical-align: middle;
            border-radius: 4px 0 0 0;
        }

        .header-left .company-title {
            font-size: 20px;
            font-weight: 900;
            color: #d9a441;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0 0 3px 0;
            line-height: 1.1;
        }

        .header-left .company-subtitle {
            font-size: 8.5px;
            color: #cbd5e1;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .header-left .company-meta {
            font-size: 9px;
            color: #e2e8f0;
            line-height: 1.35;
        }

        .header-right {
            background-color: #d9a441;
            color: #1b1f4b;
            padding: 12px 16px;
            vertical-align: middle;
            text-align: right;
            border-radius: 0 4px 0 0;
            width: 42%;
        }

        .header-right .doc-heading {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1b1f4b;
            margin: 0 0 6px 0;
        }

        .header-right .quote-badge {
            background-color: #1b1f4b;
            color: #ffffff;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 800;
            border-radius: 3px;
            display: inline-block;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .header-right .meta-table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }

        .header-right .meta-table td {
            padding: 1px 0;
            font-size: 9.5px;
            color: #1b1f4b;
            font-weight: 600;
        }

        /* Gold Banner Strip */
        .title-strip {
            background-color: #1b1f4b;
            border-top: 2px solid #d9a441;
            border-bottom: 2px solid #d9a441;
            color: #d9a441;
            padding: 6px 15px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        /* Client Information Card */
        .client-info-box {
            background-color: #fdf8eb;
            border: 1px solid #e5d1a5;
            border-left: 5px solid #d9a441;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .client-info-box table {
            margin: 0;
        }

        .client-info-box td {
            padding: 2px 6px;
            font-size: 10px;
            vertical-align: top;
        }

        .info-label {
            color: #64748b;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .info-value {
            color: #0f172a;
            font-weight: bold;
            font-size: 10.5px;
        }

        /* Section Headings */
        .section-header {
            background-color: #1b1f4b;
            color: #ffffff;
            padding: 5px 10px;
            margin-top: 12px;
            margin-bottom: 0;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-left: 4px solid #d9a441;
            border-radius: 3px 3px 0 0;
            page-break-inside: avoid;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9.5px;
            border: 1px solid #cbd5e1;
        }

        .data-table th {
            background-color: #2c326e;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            letter-spacing: 0.4px;
            padding: 5px 7px;
            border: 1px solid #1b1f4b;
            text-align: left;
        }

        .data-table td {
            padding: 5px 7px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .data-table tr.highlight td {
            background-color: #fdf8eb;
        }

        .data-table tfoot td {
            font-weight: bold;
            background-color: #f1f5f9;
            border-top: 2px solid #1b1f4b;
            padding: 6px 7px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-navy { background-color: #1b1f4b; color: #ffffff; }
        .badge-gold { background-color: #fef3e2; color: #b8860b; border: 1px solid #d9a441; }
        .badge-success { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
        .badge-warning { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }

        /* Grand Pricing Banner (VIP Theme) */
        .pricing-banner {
            background: #1b1f4b;
            border: 2px solid #d9a441;
            border-radius: 4px;
            margin-top: 14px;
            margin-bottom: 12px;
            padding: 10px 14px;
            color: #ffffff;
            page-break-inside: avoid;
        }

        .pricing-banner table {
            margin: 0;
        }

        .pricing-banner td {
            color: #ffffff;
            padding: 2px 8px;
        }

        .grand-price-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #d9a441;
            font-weight: bold;
        }

        .grand-price-value {
            font-size: 16px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.1;
        }

        .grand-price-gold {
            font-size: 18px;
            font-weight: 900;
            color: #fde047;
            line-height: 1.1;
        }

        /* Content Boxes (Inclusions, Terms) */
        .content-card {
            border: 1px solid #cbd5e1;
            border-top: 3px solid #1b1f4b;
            background: #ffffff;
            padding: 8px 12px;
            border-radius: 0 0 4px 4px;
            margin-bottom: 10px;
            font-size: 9.5px;
            line-height: 1.45;
            page-break-inside: avoid;
        }

        .content-card ul, .content-card ol {
            margin: 3px 0;
            padding-left: 16px;
        }

        .content-card p {
            margin: 2px 0;
        }

        /* Signature Table */
        .signature-table {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 25%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 8px;
        }

        .signature-line {
            border-top: 1.5px dashed #1b1f4b;
            margin-top: 35px;
            padding-top: 4px;
            font-size: 9px;
            font-weight: bold;
            color: #1b1f4b;
            text-transform: uppercase;
        }

        .signature-sub {
            font-size: 8px;
            color: #64748b;
        }

        /* Footer */
        .footer-strip {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            border-bottom: 2px solid #1b1f4b;
            text-align: center;
            font-size: 8.5px;
            color: #64748b;
            padding: 6px;
            margin-top: 15px;
            page-break-inside: avoid;
        }

        /* Column Visibility Toggles */
        .col-cost {
            display: {{ !empty($showCost) ? 'table-cell' : 'none' }};
        }
        .col-selling {
            display: {{ !empty($showSelling) ? 'table-cell' : 'none' }};
        }
        .box-cost {
            display: {{ !empty($showCost) ? 'block' : 'none' }};
        }
        .box-selling {
            display: {{ !empty($showSelling) ? 'block' : 'none' }};
        }
        .cell-cost {
            display: {{ !empty($showCost) ? 'table-cell' : 'none' }};
        }
        .cell-selling {
            display: {{ !empty($showSelling) ? 'table-cell' : 'none' }};
        }
    </style>
@php
    $showCost = isset($showCost) ? (bool)$showCost : false;
    $showSelling = isset($showSelling) ? (bool)$showSelling : true;
    $isHajjPkg = (session('dashboard_package') == 'hajj');
    $logoFile = $isHajjPkg ? public_path('assets/images/logo/kgm.png') : public_path('assets/images/logo/logo.png');
    if (!file_exists($logoFile)) {
        $logoFile = public_path('assets/images/logo/logo.jpeg');
    }
    if (!file_exists($logoFile)) {
        $logoFile = public_path('assets/images/logo-light.png');
    }
    $logoBase64 = '';
    if (file_exists($logoFile)) {
        $ext = pathinfo($logoFile, PATHINFO_EXTENSION);
        $logoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoFile));
    }
@endphp
<body>

    @if (request()->has('print'))
        <div class="no-print-bar" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <span style="font-weight: 900; font-size: 14px; color: #d9a441; letter-spacing: 0.5px;">
                    <i class="mdi mdi-star"></i> OFFICIAL TRAVEL PROPOSAL &bull; QUOTE #{{ $quotation->quotation_number }}
                </span>
                <label style="color: #ffffff; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; margin: 0; background: rgba(255,255,255,0.12); padding: 5px 10px; border-radius: 4px;">
                    <input type="checkbox" id="chkShowSelling" {{ $showSelling ? 'checked' : '' }} onchange="toggleColumns()"> Show Selling Amount
                </label>
                <label style="color: #ffffff; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; margin: 0; background: rgba(255,255,255,0.12); padding: 5px 10px; border-radius: 4px;">
                    <input type="checkbox" id="chkShowCost" {{ $showCost ? 'checked' : '' }} onchange="toggleColumns()"> Show Cost Amount
                </label>
            </div>
            <div>
                <button onclick="window.print();">Print Quotation</button>
                <a href="{{ route('quotation.pdf', ['id' => $quotation->id, 'show_cost' => $showCost ? 1 : 0, 'show_selling' => $showSelling ? 1 : 0]) }}" id="btnDownloadPdf" class="btn-download">Download Official PDF</a>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 1. VIP HEADER BANNER (Navy & Gold Theme with Logo) -->
    <!-- ========================================================================= -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <table style="width: 100%; border: none; margin: 0; background: transparent;">
                    <tr>
                        @if($logoBase64)
                            <td style="width: 65px; vertical-align: middle; border: none; background: transparent; padding: 0 10px 0 0;">
                                <img src="{{ $logoBase64 }}" alt="Dua Travels Logo" style="height: 52px; max-width: 65px; object-fit: contain; border-radius: 4px; background: #ffffff; padding: 2px;">
                            </td>
                        @endif
                        <td style="vertical-align: middle; border: none; background: transparent; padding: 0;">
                            <div class="company-title">{{ $company->company_name ?? 'DUA TRAVELS & TOURS' }}</div>
                            <div class="company-subtitle">Premium Hajj, Umrah & Luxury Travel Solutions</div>
                            <div class="company-meta">
                                @if(optional($company)->addresses && $company->addresses->count() > 0)
                                    {{ $company->addresses->first()->address ?? '' }}
                                    @if($company->addresses->first()->city), {{ $company->addresses->first()->city }} @endif
                                    <br>
                                @endif
                                @if(optional($company)->contactNumbers && $company->contactNumbers->count() > 0)
                                    <strong>Phone / Tel:</strong> {{ $company->contactNumbers->pluck('number')->implode(' | ') }}
                                @endif
                                @if(optional($company)->emails && $company->emails->count() > 0)
                                    &nbsp;&bull;&nbsp; <strong>Email:</strong> {{ $company->emails->first()->email ?? '' }}
                                @endif
                                @if(optional($company)->licenses && $company->licenses->count() > 0)
                                    <br><strong>DTS License / IATA:</strong> {{ $company->licenses->first()->license_number ?? '-' }}
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="header-right">
                <div class="doc-heading">Travel Quotation</div>
                <div class="quote-badge">{{ $quotation->quotation_number }}</div>
                <table class="meta-table">
                    <tr>
                        <td style="text-align: left;">Date Issued:</td>
                        <td style="text-align: right;">{{ $quotation->created_at ? $quotation->created_at->format('d M Y') : date('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">Valid Until:</td>
                        <td style="text-align: right;">{{ $quotation->valid_until ? date('d M Y', strtotime($quotation->valid_until)) : date('d M Y', strtotime('+15 days')) }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">Status:</td>
                        <td style="text-align: right;">
                            <span class="badge {{ $quotation->status == 'confirmed' ? 'badge-success' : ($quotation->status == 'sent' ? 'badge-info' : 'badge-navy') }}">
                                {{ strtoupper($quotation->status ?? 'DRAFT') }}
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ========================================================================= -->
    <!-- 2. PROPOSAL TITLE RIBBON -->
    <!-- ========================================================================= -->
    <div class="title-strip">
        Proposal Title: {{ $quotation->quotation_title ?: 'Customized Umrah & Travel Itinerary Package' }}
    </div>

    <!-- ========================================================================= -->
    <!-- 3. CLIENT & GROUP TRAVELER OVERVIEW (Luxury Card) -->
    <!-- ========================================================================= -->
    <div class="client-info-box">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; border-right: 1px dashed #d9a441; padding-right: 12px;">
                    <div class="info-label">Client / Lead Information</div>
                    <div class="info-value" style="font-size: 12px; color: #1b1f4b;">
                        {{ $quotation->client_name ?: (optional($quotation->lead)->contact_person ?: 'Valued Client') }}
                    </div>
                    <div><strong>Phone / WhatsApp:</strong> {{ $quotation->client_phone ?: (optional($quotation->lead)->contact_phone ?: '-') }}</div>
                    <div><strong>Email Address:</strong> {{ $quotation->client_email ?: (optional($quotation->lead)->contact_email ?: '-') }}</div>
                    @if($quotation->lead_id)
                        <div><strong>Linked Lead Ref:</strong> Lead #{{ $quotation->lead_id }} @if($quotation->lead && $quotation->lead->source) (Source: {{ $quotation->lead->source }}) @endif</div>
                    @endif
                </td>
                <td style="width: 50%; padding-left: 12px;">
                    <div class="info-label">Group Overview & Specifications</div>
                    <div><strong>Total Passengers:</strong> <span class="fw-bold text-navy" style="font-size: 11px;">{{ $quotation->total_pax ?: 1 }} Person(s)</span></div>
                    <div><strong>Pax Composition:</strong> {{ $quotation->adults_count ?? 1 }} Adults, {{ $quotation->children_count ?? 0 }} Children, {{ $quotation->infants_count ?? 0 }} Infants</div>
                    @if($quotation->package)
                        <div><strong>Master Package:</strong> {{ $quotation->package->name }}</div>
                    @endif
                    <div><strong>Currency Base:</strong> PKR (Pakistani Rupee) &nbsp;|&nbsp; <strong>Generated:</strong> {{ date('d M Y, h:i A') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. ACCOMMODATION & HOTEL STAYS -->
    <!-- ========================================================================= -->
    @if($quotation->accommodations && $quotation->accommodations->count() > 0)
        <div class="section-header">1. Accommodations & Hotel Stays</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 10%;">City</th>
                    <th style="width: 22%;">Hotel Details</th>
                    <th style="width: 17%;">Check-In / Out</th>
                    <th style="width: 6%; text-align: center;">Nights</th>
                    <th style="width: 13%;">Room / View</th>
                    <th style="width: 10%;">Meal Plan</th>
                    <th style="width: 6%; text-align: center;">Rooms</th>
                    <th class="col-cost" style="width: 12%; text-align: right;">Cost (PKR)</th>
                    <th class="col-selling" style="width: 12%; text-align: right;">Selling (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $accTotalCost = 0;
                    $accTotalSelling = 0;
                @endphp
                @foreach($quotation->accommodations as $acc)
                    @php
                        $accTotalCost += $acc->cost_amount_pkr;
                        $accTotalSelling += $acc->selling_amount_pkr;
                    @endphp
                    <tr>
                        <td class="fw-bold text-navy">
                            <span class="badge badge-navy">{{ $acc->city }}</span>
                        </td>
                        <td>
                            <strong style="color: #1b1f4b;">{{ $acc->hotel_name ?: '-' }}</strong>
                            @if($acc->confirmation_number)
                                <br><small class="text-muted"><strong>Conf #:</strong> {{ $acc->confirmation_number }}</small>
                            @endif
                            @if($acc->per_night_rate > 0)
                                <br><small class="text-muted">Sale Rate/N: {{ $acc->selling_currency ?? ($acc->currency ?? 'PKR') }} {{ number_format($acc->per_night_rate) }} @if($acc->selling_exchange_rate > 1)(ROE: {{ $acc->selling_exchange_rate }})@endif</small>
                            @endif
                            @if($acc->cost_rate > 0)
                                <div class="box-cost"><small class="text-danger">Cost Rate/N: {{ $acc->cost_currency ?? 'PKR' }} {{ number_format($acc->cost_rate) }} @if($acc->cost_exchange_rate > 1)(ROE: {{ $acc->cost_exchange_rate }})@endif</small></div>
                            @endif
                        </td>
                        <td>
                            {{ $acc->check_in ? date('d M Y', strtotime($acc->check_in)) : '-' }} &rarr;
                            {{ $acc->check_out ? date('d M Y', strtotime($acc->check_out)) : '-' }}
                        </td>
                        <td class="text-center fw-bold">{{ $acc->number_of_nights ?: 0 }} N</td>
                        <td>
                            {{ $acc->room_type ?: 'Standard' }}
                            @if($acc->room_type === 'Sharing' && ($acc->no_of_beds || $acc->male_beds || $acc->female_beds))
                                <br><small class="badge badge-navy">Beds: {{ $acc->no_of_beds }} ({{ $acc->male_beds }}M / {{ $acc->female_beds }}F)</small>
                            @endif
                            @if($acc->room_view)
                                <br><small class="badge badge-gold">{{ $acc->room_view }}</small>
                            @endif
                        </td>
                        <td>{{ $acc->meal_plan ?: 'Room Only' }}</td>
                        <td class="text-center fw-bold">{{ $acc->no_of_rooms ?: 1 }}</td>
                        <td class="text-right fw-bold text-danger col-cost">
                            PKR {{ number_format($acc->cost_amount_pkr) }}
                        </td>
                        <td class="text-right fw-bold text-navy col-selling">
                            PKR {{ number_format($acc->selling_amount_pkr) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="text-right fw-bold text-uppercase">Total Accommodations:</td>
                    <td class="text-right fw-bold text-danger col-cost">PKR {{ number_format($accTotalCost) }}</td>
                    <td class="text-right fw-bold text-navy col-selling">PKR {{ number_format($accTotalSelling) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- ========================================================================= -->
    <!-- 5. SEPARATE MEAL PLANS -->
    <!-- ========================================================================= -->
    @if($quotation->mealPlans && $quotation->mealPlans->count() > 0)
        <div class="section-header">2. Separate Meal Plan Facilities</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Meal Type</th>
                    <th style="width: 14%;">City</th>
                    <th style="width: 24%;">Provider / Catering Details</th>
                    <th style="width: 7%; text-align: center;">Pax</th>
                    <th style="width: 7%; text-align: center;">Days</th>
                    <th style="width: 10%; text-align: right;">Rate/Day</th>
                    <th class="col-cost" style="width: 13%; text-align: right;">Cost (PKR)</th>
                    <th class="col-selling" style="width: 13%; text-align: right;">Selling (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $mealTotalCost = 0;
                    $mealTotalSelling = 0;
                @endphp
                @foreach($quotation->mealPlans as $meal)
                    @php
                        $mealTotalCost += $meal->total_cost_pkr;
                        $mealTotalSelling += $meal->total_sale_pkr;
                    @endphp
                    <tr>
                        <td class="fw-bold text-navy">{{ $meal->meal_type }}</td>
                        <td>{{ $meal->city ?: '-' }}</td>
                        <td>
                            {{ $meal->provider_name ?: 'Buffet / Hotel Catering' }}
                            @if($meal->remarks)
                                <br><small class="text-muted">{{ $meal->remarks }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $meal->pax }}</td>
                        <td class="text-center">{{ $meal->days }}</td>
                        <td class="text-right">{{ $meal->currency ?? 'PKR' }} {{ number_format($meal->selling_rate) }}</td>
                        <td class="text-right fw-bold text-danger col-cost">
                            PKR {{ number_format($meal->total_cost_pkr) }}
                        </td>
                        <td class="text-right fw-bold text-navy col-selling">
                            PKR {{ number_format($meal->total_sale_pkr) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right fw-bold text-uppercase">Total Meals:</td>
                    <td class="text-right fw-bold text-danger col-cost">PKR {{ number_format($mealTotalCost) }}</td>
                    <td class="text-right fw-bold text-navy col-selling">PKR {{ number_format($mealTotalSelling) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- ========================================================================= -->
    <!-- 6. GROUND TRANSFERS & TRANSPORTATION -->
    <!-- ========================================================================= -->
    @if($quotation->transfers && $quotation->transfers->count() > 0)
        <div class="section-header">3. Ground Transfers & Transport Sectors</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 32%;">Sector / Route Details</th>
                    <th style="width: 23%;">Vehicle Type</th>
                    <th style="width: 17%;">Transfer Date & Time</th>
                    <th style="width: 8%; text-align: center;">Vehicles</th>
                    <th class="col-cost" style="width: 13%; text-align: right;">Cost (PKR)</th>
                    <th class="col-selling" style="width: 13%; text-align: right;">Selling (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $transferTotalCost = 0;
                    $transferTotalSelling = 0;
                @endphp
                @foreach($quotation->transfers as $tr)
                    @php
                        $transferTotalCost += $tr->cost_amount_pkr;
                        $transferTotalSelling += $tr->selling_amount_pkr;
                    @endphp
                    <tr>
                        <td class="fw-bold text-navy">
                            {{ $tr->sector ?: '-' }}
                            @if($tr->confirmation_number)
                                <br><small class="text-muted"><strong>Booking / Conf #:</strong> {{ $tr->confirmation_number }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-gold">{{ $tr->vehicle_type ?: 'Standard Vehicle' }}</span>
                        </td>
                        <td>{{ $tr->transfer_date ?: '-' }}</td>
                        <td class="text-center fw-bold">{{ $tr->quantity ?: 1 }}</td>
                        <td class="text-right fw-bold text-danger col-cost">
                            PKR {{ number_format($tr->cost_amount_pkr) }}
                        </td>
                        <td class="text-right fw-bold text-navy col-selling">
                            PKR {{ number_format($tr->selling_amount_pkr) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right fw-bold text-uppercase">Total Ground Transfers:</td>
                    <td class="text-right fw-bold text-danger col-cost">PKR {{ number_format($transferTotalCost) }}</td>
                    <td class="text-right fw-bold text-navy col-selling">PKR {{ number_format($transferTotalSelling) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- ========================================================================= -->
    <!-- 7. SIGHTSEEING, TOURS & HOLY ZIYARAT -->
    <!-- ========================================================================= -->
    @if($quotation->tours && $quotation->tours->count() > 0)
        <div class="section-header">4. Sightseeing, Tours & Holy Ziyarat</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Tour / Ziyarat Name</th>
                    <th style="width: 13%;">City</th>
                    <th style="width: 23%;">Sector / Historic Sites</th>
                    <th style="width: 13%;">Tour Date</th>
                    <th style="width: 6%; text-align: center;">Qty</th>
                    <th class="col-cost" style="width: 13%; text-align: right;">Cost (PKR)</th>
                    <th class="col-selling" style="width: 13%; text-align: right;">Selling (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $tourTotalCost = 0;
                    $tourTotalSelling = 0;
                @endphp
                @foreach($quotation->tours as $tour)
                    @php
                        $tourTotalCost += $tour->cost_amount_pkr;
                        $tourTotalSelling += $tour->selling_amount_pkr;
                    @endphp
                    <tr>
                        <td class="fw-bold text-navy">
                            {{ $tour->tour_name ?: 'Holy Places Ziyarat' }}
                            <br>
                            <span class="badge {{ $tour->guide_included ? 'badge-success' : 'badge-navy' }}">
                                {{ $tour->guide_included ? 'Guide Included' : 'Self Guided' }}
                            </span>
                        </td>
                        <td>{{ $tour->city ?: '-' }}</td>
                        <td>{{ $tour->sector ?: 'All Historical Mazarat' }}</td>
                        <td>{{ $tour->tour_date ?: '-' }}</td>
                        <td class="text-center fw-bold">{{ $tour->quantity ?: 1 }}</td>
                        <td class="text-right fw-bold text-danger col-cost">
                            PKR {{ number_format($tour->cost_amount_pkr) }}
                        </td>
                        <td class="text-right fw-bold text-navy col-selling">
                            PKR {{ number_format($tour->selling_amount_pkr) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right fw-bold text-uppercase">Total Tours & Ziyarat:</td>
                    <td class="text-right fw-bold text-danger col-cost">PKR {{ number_format($tourTotalCost) }}</td>
                    <td class="text-right fw-bold text-navy col-selling">PKR {{ number_format($tourTotalSelling) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- ========================================================================= -->
    <!-- 8. DOMESTIC FLIGHTS / HARAMAIN HIGH SPEED TRAIN -->
    <!-- ========================================================================= -->
    @if($quotation->flightsTrains && $quotation->flightsTrains->count() > 0)
        <div class="section-header">5. Domestic Flights & Haramain High Speed Train</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Service Mode</th>
                    <th style="width: 25%;">From &rarr; To Location</th>
                    <th style="width: 17%;">Travel Date & Time</th>
                    <th style="width: 9%;">Class</th>
                    <th style="width: 5%; text-align: center;">Pax</th>
                    <th class="col-cost" style="width: 13%; text-align: right;">Cost (PKR)</th>
                    <th class="col-selling" style="width: 13%; text-align: right;">Selling (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $flightTotalCost = 0;
                    $flightTotalSelling = 0;
                @endphp
                @foreach($quotation->flightsTrains as $train)
                    @php
                        $flightTotalCost += $train->cost_amount_pkr;
                        $flightTotalSelling += $train->selling_amount_pkr;
                    @endphp
                    <tr>
                        <td class="fw-bold text-navy">
                            {{ $train->service_type ?: 'High Speed Train' }}
                            @if($train->ticket_number_pnr)
                                <br><small class="text-muted"><strong>PNR / Ticket:</strong> {{ $train->ticket_number_pnr }}</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $train->from_location ?: '-' }}</strong> &rarr; <strong>{{ $train->to_location ?: '-' }}</strong>
                        </td>
                        <td>{{ $train->travel_date ?: '-' }}</td>
                        <td><span class="badge badge-navy">{{ $train->class_type ?: 'Economy' }}</span></td>
                        <td class="text-center fw-bold">{{ $train->pax_count ?: 1 }}</td>
                        <td class="text-right fw-bold text-danger col-cost">
                            PKR {{ number_format($train->cost_amount_pkr) }}
                        </td>
                        <td class="text-right fw-bold text-navy col-selling">
                            PKR {{ number_format($train->selling_amount_pkr) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right fw-bold text-uppercase">Total Domestic Transport:</td>
                    <td class="text-right fw-bold text-danger col-cost">PKR {{ number_format($flightTotalCost) }}</td>
                    <td class="text-right fw-bold text-navy col-selling">PKR {{ number_format($flightTotalSelling) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- ========================================================================= -->
    <!-- 9. VISA PROCESSING & CHARGES -->
    <!-- ========================================================================= -->
    @if($quotation->visas && $quotation->visas->count() > 0)
        <div class="section-header">6. Visa Processing & Formalities</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 24%;">Passenger Name</th>
                    <th style="width: 24%;">Visa Category / Type</th>
                    <th style="width: 15%;">Pax Type & Gender</th>
                    <th style="width: 18%;">Passport Number</th>
                    <th class="col-cost" style="width: 13%; text-align: right;">Cost (PKR)</th>
                    <th class="col-selling" style="width: 13%; text-align: right;">Selling (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $visaTotalCost = 0;
                    $visaTotalSelling = 0;
                @endphp
                @foreach($quotation->visas as $visa)
                    @php
                        $visaTotalCost += $visa->cost_amount_pkr;
                        $visaTotalSelling += $visa->selling_amount_pkr;
                    @endphp
                    <tr>
                        <td class="fw-bold text-navy">{{ $visa->passenger_name ?: '-' }}</td>
                        <td>
                            {{ $visa->visa_type ?: 'Umrah Tourist E-Visa' }}
                            @if($visa->country) <br><small class="text-muted">{{ $visa->country }}</small> @endif
                        </td>
                        <td>
                            <span class="badge badge-gold">{{ $visa->passenger_type ?: 'Adult' }}</span>
                            ({{ $visa->gender ?: 'Male' }})
                        </td>
                        <td><code>{{ $visa->passport_number ?: '-' }}</code></td>
                        <td class="text-right fw-bold text-danger col-cost">
                            PKR {{ number_format($visa->cost_amount_pkr) }}
                        </td>
                        <td class="text-right fw-bold text-navy col-selling">
                            PKR {{ number_format($visa->selling_amount_pkr) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right fw-bold text-uppercase">Total Visa Processing:</td>
                    <td class="text-right fw-bold text-danger col-cost">PKR {{ number_format($visaTotalCost) }}</td>
                    <td class="text-right fw-bold text-navy col-selling">PKR {{ number_format($visaTotalSelling) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- ========================================================================= -->
    <!-- 9.5. PASSENGER SERVICE FEE (PSF) -->
    <!-- ========================================================================= -->
    @if($quotation->psf_cost_pkr > 0 || $quotation->psf_selling_pkr > 0 || $quotation->psf_description)
        <div class="section-header">7. PSF</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="col-cost" style="width: 25%; text-align: right;">Cost Amount (PKR)</th>
                    <th class="col-selling" style="width: 25%; text-align: right;">Selling Price (PKR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold text-navy">{{ $quotation->psf_description ?: 'PSF' }}</td>
                    <td class="text-right fw-bold text-danger col-cost">PKR {{ number_format($quotation->psf_cost_pkr) }}</td>
                    <td class="text-right fw-bold text-navy col-selling">PKR {{ number_format($quotation->psf_selling_pkr) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-right fw-bold text-uppercase">Total PSF:</td>
                    <td class="text-right fw-bold text-danger col-cost">PKR {{ number_format($quotation->psf_cost_pkr) }}</td>
                    <td class="text-right fw-bold text-navy col-selling">PKR {{ number_format($quotation->psf_selling_pkr) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- ========================================================================= -->
    <!-- 10. GRAND FINANCIAL SUMMARY BANNER (VIP Royal Look) -->
    <!-- ========================================================================= -->
    <div class="pricing-banner">
        <table style="width: 100%;">
            <tr>
                <td style="width: 25%; vertical-align: middle;">
                    <div class="grand-price-label">Total Group Size:</div>
                    <div class="grand-price-value">{{ $quotation->total_pax ?: 1 }} Person(s)</div>
                    <div style="font-size: 8.5px; color: #cbd5e1; margin-top: 2px;">{{ $quotation->adults_count ?? 1 }} Adults, {{ $quotation->children_count ?? 0 }} Children, {{ $quotation->infants_count ?? 0 }} Infants</div>
                </td>
                <td class="cell-cost" style="width: 25%; vertical-align: middle; border-left: 1px dashed #d9a441; padding-left: 10px;">
                    <div class="grand-price-label" style="color: #f87171;">Total Cost (Purchase):</div>
                    <div class="grand-price-value" style="color: #fca5a5;">
                        PKR {{ number_format($quotation->total_cost_pkr) }}
                    </div>
                    @if($quotation->total_supplier_amount > 0)
                        <div style="font-size: 8.5px; color: #fde68a; margin-top: 2px;">Supplier Payable: PKR {{ number_format($quotation->total_supplier_amount) }}</div>
                    @endif
                    <div style="font-size: 8.5px; color: #cbd5e1; margin-top: 2px;">Est. Profit: PKR {{ number_format($quotation->total_profit_pkr) }} ({{ $quotation->margin_percentage }}%)</div>
                </td>
                <td class="cell-selling" style="width: 25%; vertical-align: middle; text-align: center; border-left: 1px dashed #d9a441; border-right: 1px dashed #d9a441; padding: 0 10px;">
                    <div class="grand-price-label">Price Per Person (Per Pax):</div>
                    <div class="grand-price-value" style="color: #67e8f9;">
                        PKR {{ number_format($quotation->per_pax_sale_pkr ?: ($quotation->total_pax > 0 ? $quotation->total_sale_pkr / $quotation->total_pax : $quotation->total_sale_pkr)) }}
                    </div>
                    <div style="font-size: 8.5px; color: #cbd5e1; margin-top: 2px;">All taxes & service charges included</div>
                </td>
                <td class="cell-selling" style="width: 25%; vertical-align: middle; text-align: right; padding-left: 10px;">
                    <div class="grand-price-label" style="color: #fde047;">Net Quotation Amount (PKR):</div>
                    <div class="grand-price-gold">
                        PKR {{ number_format($quotation->total_sale_pkr) }}
                    </div>
                    <div style="font-size: 8.5px; color: #cbd5e1; margin-top: 2px;">Total Payable Amount</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ========================================================================= -->
    <!-- 11. PACKAGE INCLUSIONS & EXCLUSIONS -->
    <!-- ========================================================================= -->
    @if($quotation->inclusions || $quotation->exclusions)
        <div class="keep-together" style="margin-top: 10px;">
            <table style="width: 100%; margin-bottom: 0;">
                <tr>
                    @if($quotation->inclusions)
                        <td style="width: {{ $quotation->exclusions ? '50%' : '100%' }}; vertical-align: top; padding-right: {{ $quotation->exclusions ? '5px' : '0' }};">
                            <div class="section-header" style="background-color: #059669; border-left-color: #34d399;">Package Inclusions</div>
                            <div class="content-card" style="border-top-color: #059669;">
                                {!! $quotation->inclusions !!}
                            </div>
                        </td>
                    @endif
                    @if($quotation->exclusions)
                        <td style="width: {{ $quotation->inclusions ? '50%' : '100%' }}; vertical-align: top; padding-left: {{ $quotation->inclusions ? '5px' : '0' }};">
                            <div class="section-header" style="background-color: #dc2626; border-left-color: #f87171;">Package Exclusions</div>
                            <div class="content-card" style="border-top-color: #dc2626;">
                                {!! $quotation->exclusions !!}
                            </div>
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 12. TERMS & SPECIAL CONDITIONS -->
    <!-- ========================================================================= -->
    @if($quotation->terms_and_conditions)
        <div class="keep-together" style="margin-top: 8px;">
            <div class="section-header">Terms, Payment Policies & Conditions</div>
            <div class="content-card">
                {!! $quotation->terms_and_conditions !!}
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 13. SPECIAL NOTES / REMARKS (if any) -->
    <!-- ========================================================================= -->
    @if($quotation->notes)
        <div class="keep-together" style="margin-top: 8px;">
            <div class="section-header" style="background-color: #b8860b;">Special Notes & Remarks</div>
            <div class="content-card" style="border-top-color: #b8860b;">
                {!! nl2br(e($quotation->notes)) !!}
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 14. AUTHORIZATION, ACCEPTANCE & OFFICIAL STAMP -->
    <!-- ========================================================================= -->
    <div class="keep-together">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line">Prepared By</div>
                    <div class="signature-sub">Travel Consultant</div>
                </td>
                <td>
                    <div class="signature-line">Approved By</div>
                    <div class="signature-sub">Operations Department</div>
                </td>
                <td>
                    <div class="signature-line">Client Acceptance</div>
                    <div class="signature-sub">Sign & Date</div>
                </td>
                <td>
                    <div class="signature-line">Official Seal</div>
                    <div class="signature-sub">{{ $company->company_name ?? 'Dua Travels' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ========================================================================= -->
    <!-- 15. OFFICIAL FOOTER NOTE -->
    <!-- ========================================================================= -->
    <div class="footer-strip">
        This document is an official computerized quotation proposal prepared by <strong>{{ $company->company_name ?? 'Dua Travels & Tours' }}</strong>. 
        Hotel rates, transport and flight fares remain subject to availability and currency exchange fluctuations at the final time of booking. 
        Generated on <strong>{{ date('d M Y, h:i A') }}</strong>.
    </div>

    <script>
        function toggleColumns() {
            var showCost = document.getElementById('chkShowCost')?.checked;
            var showSelling = document.getElementById('chkShowSelling')?.checked;

            document.querySelectorAll('.col-cost').forEach(function(el) {
                el.style.display = showCost ? 'table-cell' : 'none';
            });
            document.querySelectorAll('.box-cost').forEach(function(el) {
                el.style.display = showCost ? 'block' : 'none';
            });
            document.querySelectorAll('.cell-cost').forEach(function(el) {
                el.style.display = showCost ? 'table-cell' : 'none';
            });

            document.querySelectorAll('.col-selling').forEach(function(el) {
                el.style.display = showSelling ? 'table-cell' : 'none';
            });
            document.querySelectorAll('.box-selling').forEach(function(el) {
                el.style.display = showSelling ? 'block' : 'none';
            });
            document.querySelectorAll('.cell-selling').forEach(function(el) {
                el.style.display = showSelling ? 'table-cell' : 'none';
            });

            var dlBtn = document.getElementById('btnDownloadPdf');
            if (dlBtn) {
                var baseHref = "{{ route('quotation.pdf', $quotation->id) }}";
                dlBtn.href = baseHref + "?show_cost=" + (showCost ? 1 : 0) + "&show_selling=" + (showSelling ? 1 : 0);
            }
        }
    </script>
</body>
</html>
