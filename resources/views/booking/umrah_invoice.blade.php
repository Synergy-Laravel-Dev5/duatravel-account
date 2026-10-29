<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Umrah Invoice - {{ $booking->voucher_number ?: ('DTT-' . $booking->id) }}</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">

    <style>
        body {
            background: #eef2f5;
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            margin: 0;
            padding: 20px 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .invoice-sheet {
            background: #fff;
            max-width: 920px;
            margin: 0 auto;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-radius: 4px;
            position: relative;
        }

        /* Top Action Bar */
        .action-toolbar {
            max-width: 920px;
            margin: 0 auto 15px auto;
            background: #fff;
            padding: 12px 20px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        /* Header Layout */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .header-logo-box {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-logo-box img {
            height: 60px;
            object-fit: contain;
        }

        .company-info-block {
            text-align: right;
            font-size: 11.5px;
            line-height: 1.45;
            color: #333;
        }

        .company-info-block h2 {
            font-size: 24px;
            font-weight: 800;
            color: #e65100;
            margin: 0 0 2px 0;
            letter-spacing: 0.2px;
        }

        /* Title */
        .invoice-title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            color: #e65100;
            margin: 12px 0 10px 0;
            letter-spacing: 0.5px;
        }

        /* Meta Box (4-segment banner) */
        .meta-banner {
            display: grid;
            grid-template-columns: 1.1fr 1fr 1.5fr 1.6fr;
            border: 1px solid #f2994a;
            border-radius: 4px;
            background: #fffaf4;
            margin-bottom: 12px;
            overflow: hidden;
            font-size: 11.5px;
        }

        .meta-segment {
            padding: 6px 10px;
            border-right: 1px solid #f2994a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta-segment:last-child {
            border-right: none;
        }

        .meta-segment .meta-lbl {
            color: #d35400;
            font-weight: 700;
            margin-right: 4px;
        }

        .meta-segment .meta-val {
            color: #222;
            font-weight: 600;
        }

        /* Tables & Typography */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .inv-table th,
        .inv-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: middle;
        }

        .inv-table thead th {
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }

        /* Section Bars with icons */
        .sec-bar {
            display: flex;
            align-items: center;
            font-size: 13.5px;
            font-weight: 800;
            color: #e65100;
            margin: 10px 0 4px 0;
            letter-spacing: 0.3px;
        }

        .sec-bar i {
            font-size: 16px;
            margin-right: 6px;
        }

        /* Table header color themes */
        .thead-guest th {
            background: #fffaf4;
            color: #d35400;
        }

        .thead-flight th {
            background: #f1f5f9;
            color: #1e3a8a;
        }

        .thead-service th {
            background: #fffaf4;
            color: #d35400;
        }

        .summary-sub-bar {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-top: none;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-top: -10px;
            margin-bottom: 10px;
        }

        /* Bottom Price Section */
        .bottom-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 10px;
            gap: 20px;
        }

        .bottom-pkg-name {
            font-size: 12.5px;
            font-weight: 800;
            color: #111;
            margin-top: 5px;
        }

        .price-box-table {
            width: 320px;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-left: auto;
            border: 1px solid #f2994a;
        }

        .price-box-table thead th {
            background: #fffaf4;
            color: #d35400;
            font-weight: 800;
            padding: 5px 8px;
            text-align: left;
            border-bottom: 1px solid #f2994a;
        }

        .price-box-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .price-box-table tr:last-child td {
            border-bottom: none;
        }

        .price-box-table td.amt-cell {
            text-align: right;
            font-weight: 700;
            color: #d35400;
        }

        /* Footer */
        .inv-footer {
            margin-top: 25px;
            font-size: 10.5px;
            color: #64748b;
            line-height: 1.4;
        }

        /* Print Mode */
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-sheet {
                box-shadow: none !important;
                padding: 10px 15px !important;
                max-width: 100% !important;
                border: none !important;
            }
            .meta-banner {
                background: #fffaf4 !important;
                border: 1px solid #f2994a !important;
            }
            .thead-guest th, .thead-service th, .price-box-table thead th {
                background: #fffaf4 !important;
                color: #d35400 !important;
            }
            .thead-flight th {
                background: #f1f5f9 !important;
                color: #1e3a8a !important;
            }
        }
    </style>
</head>
<body>

    @php
        // Currency & ROE Calculations
        $effectiveRoe = (float)($booking->flight_sale_roe > 1 ? $booking->flight_sale_roe : ($booking->flight_roe > 1 ? $booking->flight_roe : ($booking->roe > 1 ? $booking->roe : 75.0)));
        if ($effectiveRoe <= 1) $effectiveRoe = 75.0;

        // Pax Counts
        $adultsCount = (int)($booking->adults_count ?: ($booking->persons->where('pax_type', 'adult')->count() ?: ($booking->no_of_pax ?: 1)));
        $childrenCount = (int)($booking->children_count ?: $booking->persons->where('pax_type', 'child')->count());
        $infantsCount = (int)($booking->infants_count ?: $booking->persons->where('pax_type', 'infant')->count());
        $totalPax = $adultsCount + $childrenCount + $infantsCount;
        if ($totalPax <= 0) $totalPax = 1;

        // If specific person is selected
        if ($selectedPerson) {
            $guestList = collect([$selectedPerson]);
            $isSinglePax = true;
            $displayAdults = ($selectedPerson->pax_type === 'adult' || empty($selectedPerson->pax_type)) ? 1 : 0;
            $displayChildren = ($selectedPerson->pax_type === 'child') ? 1 : 0;
            $displayInfants = ($selectedPerson->pax_type === 'infant') ? 1 : 0;
            $displayTotalPax = 1;
        } else {
            $guestList = $booking->persons->count() > 0 ? $booking->persons : collect([
                (object)[
                    'id' => 0,
                    'given_name' => $booking->given_name ?: ($booking->client->name ?? 'Guest Passenger'),
                    'surname' => $booking->sur_name ?: '',
                    'full_name' => $booking->client->name ?? 'Guest Passenger',
                    'passport_number' => $booking->passport_number ?: '-',
                    'dob' => null,
                    'gender' => 'Male',
                    'pax_type' => 'adult',
                ]
            ]);
            $isSinglePax = false;
            $displayAdults = $adultsCount;
            $displayChildren = $childrenCount;
            $displayInfants = $infantsCount;
            $displayTotalPax = $totalPax;
        }

        // Hotels Calculation (Dynamic SAR & PKR)
        $hotelSarTotal = 0.00;
        $hotelPkrTotal = 0.00;
        $totalNights = 0;
        foreach ($booking->hotels as $h) {
            $cur = strtoupper($h->currency ?: 'SAR');
            $hNights = (int)($h->no_of_nights ?: 1);
            $hRooms = (int)($h->no_of_rooms ?: 1);
            $totalNights += $hNights;

            $hRate = (float)($h->selling_per_night > 0 ? $h->selling_per_night : ($h->cost_per_night > 0 ? $h->cost_per_night : 0));
            $hRoe = (float)($h->sale_roe > 1 ? $h->sale_roe : ($h->cost_roe > 1 ? $h->cost_roe : $effectiveRoe));

            if ($cur === 'SAR') {
                $hSarRate = $hRate;
                $hSarAmount = round($hRate * $hNights * $hRooms, 2);
                $hPkrRate = round($hRate * $hRoe, 2);
                $hPkrAmount = (float)($h->selling_amount > 0 ? $h->selling_amount : round($hSarAmount * $hRoe, 2));
            } else {
                $hPkrRate = $hRate;
                $hPkrAmount = (float)($h->selling_amount > 0 ? $h->selling_amount : round($hRate * $hNights * $hRooms, 2));
                $hSarRate = ($hRoe > 1) ? round($hRate / $hRoe, 2) : 0;
                $hSarAmount = ($hRoe > 1) ? round($hPkrAmount / $hRoe, 2) : 0;
            }

            $h->calc_sar_rate = $hSarRate;
            $h->calc_pkr_rate = $hPkrRate;
            $h->calc_sar_amount = $hSarAmount;
            $h->calc_pkr_amount = $hPkrAmount;

            $hotelSarTotal += $hSarAmount;
            $hotelPkrTotal += $hPkrAmount;
        }

        // Transports Calculation (Dynamic SAR & PKR)
        $transportSarTotal = 0.00;
        $transportPkrTotal = 0.00;
        foreach ($booking->transports as $tr) {
            $cur = strtoupper($tr->currency ?: 'SAR');
            $tQty = (int)($tr->quantity ?: 1);
            $tRate = (float)($tr->selling_rate > 0 ? $tr->selling_rate : ($tr->cost_rate > 0 ? $tr->cost_rate : 0));
            $tRoe = (float)($tr->sale_roe > 1 ? $tr->sale_roe : ($tr->cost_roe > 1 ? $tr->cost_roe : $effectiveRoe));

            if ($cur === 'SAR') {
                $tSarRate = $tRate;
                $tSarAmount = round($tRate * $tQty, 2);
                $tPkrRate = round($tRate * $tRoe, 2);
                $tPkrAmount = (float)($tr->selling_amount > 0 ? $tr->selling_amount : round($tSarAmount * $tRoe, 2));
            } else {
                $tPkrRate = $tRate;
                $tPkrAmount = (float)($tr->selling_amount > 0 ? $tr->selling_amount : round($tRate * $tQty, 2));
                $tSarRate = ($tRoe > 1) ? round($tRate / $tRoe, 2) : 0;
                $tSarAmount = ($tRoe > 1) ? round($tPkrAmount / $tRoe, 2) : 0;
            }

            $tr->calc_sar_rate = $tSarRate;
            $tr->calc_pkr_rate = $tPkrRate;
            $tr->calc_sar_amount = $tSarAmount;
            $tr->calc_pkr_amount = $tPkrAmount;

            $transportSarTotal += $tSarAmount;
            $transportPkrTotal += $tPkrAmount;
        }

        // Package Price Summary (100% Dynamic)
        $pkgWithoutTicketSar = $hotelSarTotal + $transportSarTotal;
        $netTotalPkr = (float)($booking->total_amount > 0 ? $booking->total_amount : ($pkgWithoutTicketSar * $effectiveRoe));
        if ($pkgWithoutTicketSar <= 0 && $netTotalPkr > 0 && $effectiveRoe > 0) {
            $pkgWithoutTicketSar = round($netTotalPkr / $effectiveRoe, 2);
        }

        // If single person is filtered, adjust amounts proportionately
        if ($selectedPerson && $totalPax > 1) {
            $ratio = 1 / $totalPax;
            $hotelSarTotal = round($hotelSarTotal * $ratio, 2);
            $transportSarTotal = round($transportSarTotal * $ratio, 2);
            $pkgWithoutTicketSar = round($pkgWithoutTicketSar * $ratio, 2);
            $netTotalPkr = round($netTotalPkr * $ratio, 2);
        }

        // Agent / Client Name
        $clientDisplayName = $booking->client->name ?? ($booking->company->name ?? 'Guest Client');
        $agentRefNo = $booking->client_id ? ('DC-' . $booking->client_id) : ($booking->company_id ? ('CMP-' . $booking->company_id) : ('BK-' . $booking->id));
        $hvNumber = $booking->voucher_number ?: ('DTT-' . $booking->id);
        $dateFormatted = $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d-m-Y') : ($booking->created_at ? $booking->created_at->format('d-m-Y') : date('d-m-Y'));

        // Company Details
        $companyName = $booking->company->name ?? 'Dua Travels and Tours';
        $companyAddress = $booking->company->address ?? 'Office # 01 Plot # A-14 Block - 4 Saadabad CHS, Gulistan-e-Johar Karachi';
        $companyPhone = $booking->company->phone ?? '021-34774262 / 021-34774263';
        $companyEmail = $booking->company->email ?? 'dua.travel2012@gmail.com';
    @endphp

    <!-- ACTION TOOLBAR (Hidden in Print) -->
    <div class="action-toolbar no-print">
        <div class="row g-2 align-items-center">
            
            <!-- Person Filter Dropdown -->
            <div class="col-md-3 col-sm-6">
                <label class="form-label fs-11 fw-bold text-muted mb-1">
                    <i class="mdi mdi-account-filter text-primary"></i> Guest / Pilgrim:
                </label>
                <select id="personSelect" class="form-select form-select-sm">
                    <option value="all" {{ !$selectedPerson ? 'selected' : '' }}>
                        All Guests ({{ $totalPax }} Pax)
                    </option>
                    @foreach($booking->persons as $p)
                        <option value="{{ $p->id }}" {{ ($selectedPerson && $selectedPerson->id == $p->id) ? 'selected' : '' }}>
                            {{ $p->given_name ?: ($p->full_name ?: 'Guest #' . $p->id) }} {{ $p->surname ?: '' }} ({{ $p->pax_type ?: 'Adult' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Rates Visibility Toggle -->
            <div class="col-md-3 col-sm-6">
                <label class="form-label fs-11 fw-bold text-muted mb-1">
                    <i class="mdi mdi-eye-outline text-primary"></i> Rates / Cost Display:
                </label>
                <div class="btn-group btn-group-sm w-100" role="group">
                    <button type="button" class="btn btn-outline-primary rate-toggle-btn {{ $showRates == 0 ? 'active' : '' }}" data-rate="0">
                        Without Rates
                    </button>
                    <button type="button" class="btn btn-outline-primary rate-toggle-btn {{ $showRates == 1 ? 'active' : '' }}" data-rate="1">
                        With Rates
                    </button>
                </div>
            </div>

            <!-- Currency Toggle -->
            <div class="col-md-3 col-sm-6">
                <label class="form-label fs-11 fw-bold text-muted mb-1">
                    <i class="mdi mdi-currency-usd text-primary"></i> Currency Mode:
                </label>
                <div class="btn-group btn-group-sm w-100" role="group">
                    <button type="button" class="btn btn-outline-secondary curr-toggle-btn {{ $currencyMode === 'mix' ? 'active' : '' }}" data-curr="mix">
                        SAR + PKR Mix
                    </button>
                    <button type="button" class="btn btn-outline-secondary curr-toggle-btn {{ $currencyMode === 'pkr' ? 'active' : '' }}" data-curr="pkr">
                        Just PKR
                    </button>
                    <button type="button" class="btn btn-outline-secondary curr-toggle-btn {{ $currencyMode === 'sar' ? 'active' : '' }}" data-curr="sar">
                        Just SAR
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-md-3 col-sm-6 text-end d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-primary btn-sm px-3" onclick="window.print()">
                    <i class="mdi mdi-printer me-1"></i> Print Invoice
                </button>
                <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-outline-secondary btn-sm">
                    Back
                </a>
            </div>

        </div>
    </div>

    <!-- PRINTABLE INVOICE SHEET -->
    <div class="invoice-sheet" id="invoiceSheet">

        <!-- HEADER -->
        <div class="invoice-header">
            <div class="header-logo-box">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" onerror="this.src='https://placehold.co/120x60/f26522/white?text=DUA+TRAVELS'">
            </div>
            <div class="company-info-block">
                <h2>{{ $companyName }}</h2>
                <div>{{ $companyAddress }}</div>
                <div>Phone: {{ $companyPhone }}</div>
                <div>Email: {{ $companyEmail }}</div>
            </div>
        </div>

        <!-- TITLE -->
        <div class="invoice-title">Umrah Invoice</div>

        <!-- 4-SEGMENT META BANNER -->
        <div class="meta-banner">
            <div class="meta-segment">
                <span class="meta-lbl">HV #:</span>
                <span class="meta-val">{{ $hvNumber }}</span>
            </div>
            <div class="meta-segment">
                <span class="meta-lbl">Agent Ref:</span>
                <span class="meta-val">{{ $agentRefNo }}</span>
            </div>
            <div class="meta-segment">
                <span class="meta-lbl">Date:</span>
                <span class="meta-val">{{ $dateFormatted }}</span>
            </div>
            <div class="meta-segment">
                <span class="meta-lbl">Client Name:</span>
                <span class="meta-val">{{ $clientDisplayName }}</span>
            </div>
        </div>

        <!-- GUEST TABLE -->
        <table class="inv-table">
            <thead class="thead-guest">
                <tr>
                    <th style="width: 45px;">Sr #</th>
                    <th style="text-align: left; padding-left: 10px;">Guest Name</th>
                    <th style="width: 140px;">Passport #</th>
                    <th style="width: 110px;">D.O.B</th>
                    <th style="width: 120px;">Visa #</th>
                    <th style="width: 110px;">Date</th>
                    <th class="rate-col {{ $showRates ? '' : 'd-none' }}" style="width: 80px;">Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guestList as $g)
                    @php
                        $gName = trim(($g->given_name ?? '') . ' ' . ($g->surname ?? ''));
                        if (empty($gName)) $gName = $g->full_name ?? ($booking->client->name ?? 'Guest Passenger');
                        $paxTypeStr = strtoupper($g->pax_type ?? 'Adult');
                        $genderStr = ucfirst($g->gender ?? 'Male');
                        $isHead = ($loop->first && !$selectedPerson) || !empty($g->is_head);

                        // Match visa from booking->visas by passport number if available
                        $matchedVisa = null;
                        if (!empty($g->passport_number)) {
                            $matchedVisa = $booking->visas->firstWhere('passport_number', $g->passport_number);
                        }
                        $visaNo = $matchedVisa->voucher_no ?? ($matchedVisa->visa_attachment ?? ($g->visa_number ?? '-'));
                        $visaDate = $matchedVisa && $matchedVisa->booking_date ? \Carbon\Carbon::parse($matchedVisa->booking_date)->format('d-m-Y') : '-';
                        $visaRate = 0.00;
                        if ($matchedVisa && $matchedVisa->selling_rate > 0) {
                            $visaRate = ($currencyMode === 'pkr') ? round($matchedVisa->selling_rate * ($matchedVisa->sale_roe ?: $effectiveRoe), 2) : (float)$matchedVisa->selling_rate;
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td style="text-align: left; padding-left: 10px; font-weight: 700;">
                            {{ strtoupper($gName) }} ({{ substr($paxTypeStr, 0, 1) }}) ({{ $genderStr }}) {{ $isHead ? '(Head)' : '' }}
                        </td>
                        <td class="text-center fw-semibold">{{ $g->passport_number ?: '-' }}</td>
                        <td class="text-center">{{ !empty($g->dob) ? \Carbon\Carbon::parse($g->dob)->format('d-m-Y') : '-' }}</td>
                        <td class="text-center">{{ $visaNo }}</td>
                        <td class="text-center">{{ $visaDate }}</td>
                        <td class="text-center rate-col {{ $showRates ? '' : 'd-none' }}">{{ number_format($visaRate, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- PAX SUMMARY BAR -->
        <div class="summary-sub-bar">
            <span>Adult: {{ $displayAdults }}</span>
            <span>Child: {{ $displayChildren }}</span>
            <span>Infant: {{ $displayInfants }}</span>
            <span>Total Pax: {{ $displayTotalPax }}</span>
        </div>

        <!-- FLIGHT DETAIL SECTION (Dynamic) -->
        @php
            $hasDepFlight = !empty($booking->departure_flight) || !empty($booking->departure_date) || !empty($booking->departure_airline);
            $hasArrFlight = !empty($booking->arrival_flight) || !empty($booking->arrival_date) || !empty($booking->arrival_airline);
        @endphp
        <div class="sec-bar">
            <i class="mdi mdi-airplane"></i> Flight Detail
        </div>
        <table class="inv-table">
            <thead class="thead-flight">
                <tr>
                    <th style="width: 130px;">Flight #</th>
                    <th>Sector</th>
                    <th style="width: 140px;">Dep.Date</th>
                    <th style="width: 110px;">Dep.Time</th>
                    <th style="width: 140px;">Arr.Date</th>
                    <th style="width: 110px;">Arr.Time</th>
                </tr>
            </thead>
            <tbody>
                @if($hasDepFlight)
                    <tr>
                        <td class="text-center fw-bold">{{ $booking->departure_flight ?: ($booking->departure_airline ?: '-') }}</td>
                        <td class="text-center">{{ $booking->departure_sector ?: '-' }}</td>
                        <td class="text-center">{{ $booking->departure_date ? \Carbon\Carbon::parse($booking->departure_date)->format('d-m-Y') : '-' }}</td>
                        <td class="text-center">{{ $booking->departure_time ? date('H:i', strtotime($booking->departure_time)) : ($booking->departure_time ?: '-') }}</td>
                        <td class="text-center">{{ $booking->departure_date ? \Carbon\Carbon::parse($booking->departure_date)->format('d-m-Y') : '-' }}</td>
                        <td class="text-center">-</td>
                    </tr>
                @endif
                @if($hasArrFlight)
                    <tr>
                        <td class="text-center fw-bold">{{ $booking->arrival_flight ?: ($booking->arrival_airline ?: '-') }}</td>
                        <td class="text-center">{{ $booking->arrival_sector ?: '-' }}</td>
                        <td class="text-center">{{ $booking->arrival_date ? \Carbon\Carbon::parse($booking->arrival_date)->format('d-m-Y') : '-' }}</td>
                        <td class="text-center">{{ $booking->arrival_time ? date('H:i', strtotime($booking->arrival_time)) : ($booking->arrival_time ?: '-') }}</td>
                        <td class="text-center">{{ $booking->arrival_date ? \Carbon\Carbon::parse($booking->arrival_date)->format('d-m-Y') : '-' }}</td>
                        <td class="text-center">-</td>
                    </tr>
                @endif
                @if(!$hasDepFlight && !$hasArrFlight)
                    <tr>
                        <td colspan="6" class="text-center text-muted py-2">No Flight Details Recorded</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- HOTEL | ACCOMODATION SECTION (Dynamic) -->
        <div class="sec-bar">
            <i class="mdi mdi-bed"></i> Hotel | Accomodation
        </div>
        <table class="inv-table">
            <thead class="thead-service">
                <tr>
                    <th style="text-align: left; padding-left: 10px;">Hotel Name</th>
                    <th style="width: 100px;">City</th>
                    <th style="width: 95px;">Check In</th>
                    <th style="width: 95px;">Check Out</th>
                    <th style="width: 55px;">Nights</th>
                    <th style="width: 100px;">Room Type</th>
                    <th style="width: 45px;">QTY</th>
                    <th style="width: 80px;">Meal Plan</th>
                    <th class="rate-col {{ $showRates ? '' : 'd-none' }}" style="width: 75px;">Rate</th>
                    <th class="rate-col {{ $showRates ? '' : 'd-none' }}" style="width: 85px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($booking->hotels as $h)
                    @php
                        $hRateVal = ($currencyMode === 'pkr') ? $h->calc_pkr_rate : $h->calc_sar_rate;
                        $hAmtVal = ($currencyMode === 'pkr') ? $h->calc_pkr_amount : $h->calc_sar_amount;
                    @endphp
                    <tr>
                        <td style="text-align: left; padding-left: 10px; font-weight: 700;">
                            {{ strtoupper($h->hotel_name) }}
                        </td>
                        <td class="text-center">{{ strtoupper($h->location ?: '-') }}</td>
                        <td class="text-center">{{ $h->check_in ? \Carbon\Carbon::parse($h->check_in)->format('d-m-Y') : '-' }}</td>
                        <td class="text-center">{{ $h->check_out ? \Carbon\Carbon::parse($h->check_out)->format('d-m-Y') : '-' }}</td>
                        <td class="text-center">{{ $h->no_of_nights ?: 1 }}</td>
                        <td class="text-center">{{ strtoupper($h->room_type ?: '-') }}</td>
                        <td class="text-center">{{ $h->no_of_rooms ?: 1 }}</td>
                        <td class="text-center">{{ strtoupper($h->meal_plan ?: 'Room Only') }}</td>
                        <td class="text-center rate-col {{ $showRates ? '' : 'd-none' }}">{{ number_format($hRateVal, 2) }}</td>
                        <td class="text-center rate-col {{ $showRates ? '' : 'd-none' }} fw-bold">{{ number_format($hAmtVal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-2">No Hotel / Accommodation Details Recorded</td>
                    </tr>
                @endforelse
                @if($booking->hotels->isNotEmpty())
                    <tr style="background: #fafafa; font-weight: 700;">
                        <td colspan="4" class="text-end pe-2">Total</td>
                        <td class="text-center">{{ $totalNights }}</td>
                        <td colspan="3"></td>
                        <td class="rate-col {{ $showRates ? '' : 'd-none' }}"></td>
                        <td class="text-center rate-col {{ $showRates ? '' : 'd-none' }} fw-bold text-primary">
                            {{ number_format(($currencyMode === 'pkr') ? $hotelPkrTotal : $hotelSarTotal, 2) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- TRANSPORTATION SECTION (Dynamic) -->
        <div class="sec-bar">
            <i class="mdi mdi-bus"></i> Transportation
        </div>
        <table class="inv-table">
            <thead class="thead-service">
                <tr>
                    <th style="text-align: left; padding-left: 10px;">Sector / Route</th>
                    <th style="width: 75px;">Vehicle</th>
                    <th style="width: 85px;">Type</th>
                    <th style="width: 65px;">Quantity</th>
                    <th style="width: 95px;">Date</th>
                    <th style="width: 85px;">Pickup Time</th>
                    <th style="width: 120px;">Pickup From</th>
                    <th style="width: 120px;">Destination</th>
                    <th class="rate-col {{ $showRates ? '' : 'd-none' }}" style="width: 65px;">Rate</th>
                    <th class="rate-col {{ $showRates ? '' : 'd-none' }}" style="width: 75px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($booking->transports as $t)
                    @php
                        $tRateVal = ($currencyMode === 'pkr') ? $t->calc_pkr_rate : $t->calc_sar_rate;
                        $tAmtVal = ($currencyMode === 'pkr') ? $t->calc_pkr_amount : $t->calc_sar_amount;
                        $routeStr = $t->route ?: ($t->pickup_from && $t->destination ? ($t->pickup_from . ' - ' . $t->destination) : ($t->transport_type ?: '-'));
                    @endphp
                    <tr>
                        <td style="text-align: left; padding-left: 10px; font-weight: 700;">
                            {{ strtoupper($routeStr) }}
                        </td>
                        <td class="text-center">{{ strtoupper($t->vehicle ?: ($t->transport_type ?: '-')) }}</td>
                        <td class="text-center">{{ strtoupper($t->transport_type ?: ($t->status ?: '-')) }}</td>
                        <td class="text-center">{{ $t->quantity ?: 1 }}</td>
                        <td class="text-center">{{ $t->travel_date ? \Carbon\Carbon::parse($t->travel_date)->format('d-m-Y') : ($t->booking_date ? \Carbon\Carbon::parse($t->booking_date)->format('d-m-Y') : '-') }}</td>
                        <td class="text-center">{{ $t->travel_time ? date('H:i', strtotime($t->travel_time)) : ($t->travel_time ?: '-') }}</td>
                        <td class="text-center">{{ strtoupper($t->pickup_from ?: '-') }}</td>
                        <td class="text-center">{{ strtoupper($t->destination ?: '-') }}</td>
                        <td class="text-center rate-col {{ $showRates ? '' : 'd-none' }}">{{ number_format($tRateVal, 2) }}</td>
                        <td class="text-center rate-col {{ $showRates ? '' : 'd-none' }} fw-bold">{{ number_format($tAmtVal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-2">No Transportation Services Recorded</td>
                    </tr>
                @endforelse
                @if($booking->transports->isNotEmpty())
                    <tr style="background: #fafafa; font-weight: 700;">
                        <td colspan="8" class="text-end pe-2">Total</td>
                        <td class="rate-col {{ $showRates ? '' : 'd-none' }}"></td>
                        <td class="text-center rate-col {{ $showRates ? '' : 'd-none' }} fw-bold text-primary">
                            {{ number_format(($currencyMode === 'pkr') ? $transportPkrTotal : $transportSarTotal, 2) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- BOTTOM SUMMARY SECTION (Dynamic) -->
        <div class="bottom-section">
            <div class="bottom-pkg-name">
                Package Name: {{ $booking->package_name ?: ($booking->package->name ?? ('Umrah Package #' . $booking->id)) }}
            </div>

            <!-- PACKAGE PRICE BOX (100% Dynamic) -->
            <table class="price-box-table">
                <thead>
                    <tr>
                        <th colspan="2">Package Price</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Itemized rows (only visible in "with rates" mode) -->
                    <tr class="rate-itemized-row {{ $showRates ? '' : 'd-none' }}">
                        <td>Hotel Amount</td>
                        <td class="amt-cell" id="sumHotelAmount">{{ number_format(($currencyMode === 'pkr') ? $hotelPkrTotal : $hotelSarTotal, 2) }}</td>
                    </tr>
                    <tr class="rate-itemized-row {{ $showRates ? '' : 'd-none' }}">
                        <td>Transport Amount</td>
                        <td class="amt-cell" id="sumTransportAmount">{{ number_format(($currencyMode === 'pkr') ? $transportPkrTotal : $transportSarTotal, 2) }}</td>
                    </tr>

                    <!-- Package Without Ticket (SAR) (Hidden in Just PKR mode) -->
                    <tr class="curr-sar-row {{ $currencyMode === 'pkr' ? 'd-none' : '' }}">
                        <td>Package Without Ticket (SAR)</td>
                        <td class="amt-cell" id="sumSarAmount">{{ number_format($pkgWithoutTicketSar, 2) }}</td>
                    </tr>

                    <!-- Net Total (PKR) (Hidden in Just SAR mode) -->
                    <tr class="curr-pkr-row {{ $currencyMode === 'sar' ? 'd-none' : '' }}">
                        <td style="font-weight: 700;">Net Total (PKR)</td>
                        <td class="amt-cell" style="font-size: 13px; font-weight: 800;" id="sumPkrAmount">
                            {{ number_format($netTotalPkr, 2) }}
                        </td>
                    </tr>

                    @if($booking->total_received > 0)
                        <tr>
                            <td>Total Paid / Received</td>
                            <td class="amt-cell text-success">PKR {{ number_format($booking->total_received, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Balance Due</td>
                            <td class="amt-cell text-danger">PKR {{ number_format($booking->balance ?? ($netTotalPkr - $booking->total_received), 2) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- FOOTER -->
        <div class="inv-footer">
            <div>{{ $companyName }} — Umrah Service Invoice</div>
            <div>Generated on {{ date('d-m-Y H:i:s') }}</div>
        </div>

    </div>

    <!-- SCRIPT FOR DYNAMIC TOGGLES & INTERACTIVITY -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const personSelect = document.getElementById('personSelect');
            const rateButtons = document.querySelectorAll('.rate-toggle-btn');
            const currButtons = document.querySelectorAll('.curr-toggle-btn');

            let currentShowRates = {{ $showRates }};
            let currentCurrMode = "{{ $currencyMode }}";

            // Rates Toggle
            rateButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    rateButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentShowRates = parseInt(this.getAttribute('data-rate'));
                    updateDisplay();
                });
            });

            // Currency Toggle
            currButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    currButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentCurrMode = this.getAttribute('data-curr');
                    updateDisplay();
                });
            });

            // Person Selector Change -> Reload page with selected person parameter
            if (personSelect) {
                personSelect.addEventListener('change', function() {
                    const personId = this.value;
                    const url = new URL(window.location.href);
                    url.searchParams.set('person_id', personId);
                    url.searchParams.set('show_rates', currentShowRates);
                    url.searchParams.set('currency_mode', currentCurrMode);
                    window.location.href = url.toString();
                });
            }

            function updateDisplay() {
                // Toggle Rate columns
                const rateCols = document.querySelectorAll('.rate-col');
                const rateItemizedRows = document.querySelectorAll('.rate-itemized-row');
                if (currentShowRates === 1) {
                    rateCols.forEach(el => el.classList.remove('d-none'));
                    rateItemizedRows.forEach(el => el.classList.remove('d-none'));
                } else {
                    rateCols.forEach(el => el.classList.add('d-none'));
                    rateItemizedRows.forEach(el => el.classList.add('d-none'));
                }

                // Currency rows
                const currSarRow = document.querySelector('.curr-sar-row');
                const currPkrRow = document.querySelector('.curr-pkr-row');

                if (currentCurrMode === 'mix') {
                    if (currSarRow) currSarRow.classList.remove('d-none');
                    if (currPkrRow) currPkrRow.classList.remove('d-none');
                } else if (currentCurrMode === 'pkr') {
                    if (currSarRow) currSarRow.classList.add('d-none');
                    if (currPkrRow) currPkrRow.classList.remove('d-none');
                } else if (currentCurrMode === 'sar') {
                    if (currSarRow) currSarRow.classList.remove('d-none');
                    if (currPkrRow) currPkrRow.classList.add('d-none');
                }
            }
        });
    </script>
</body>
</html>
