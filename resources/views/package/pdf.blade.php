<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $package->name }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=Poppins:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    @php
        $forPdf = $forPdf ?? false;
        $accentColor = $package->color ?? '#d9a441';

        $darken = function ($hex, $percent) {
            $hex = ltrim($hex ?: '#d9a441', '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            if (strlen($hex) !== 6) {
                $hex = 'd9a441';
            }
            $r = max(0, round(hexdec(substr($hex, 0, 2)) * (1 - $percent)));
            $g = max(0, round(hexdec(substr($hex, 2, 2)) * (1 - $percent)));
            $b = max(0, round(hexdec(substr($hex, 4, 2)) * (1 - $percent)));
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        };
        $goldDark = $darken($accentColor, 0.22);
    @endphp

    <style>
        :root {
            --navy: #1b1f4b;
            --gold: {{ $accentColor }};
            --gold-dark: {{ $goldDark }};
            --peach: #f3cfa0;
            --peach-light: #f8e4c3;
            --grey-row: #e9e9e9;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f0f0;
            color: var(--navy);
        }

        .sheet {
            max-width: 1100px;
            margin: 20px auto;
            background: #fff;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0, 0, 0, .15);
        }

        /* ---------- HEADER (bolder / bigger, matching the premium brochure look) ---------- */
        .header-banner {
            display: flex;
            align-items: stretch;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .header-left {
            background: var(--navy);
            color: var(--gold);
            padding: 27px 25px;
            flex: 2;
            min-width: 260px;
        }

        .header-left h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 900;
            font-size: 2.6rem;
            margin: 0;
            letter-spacing: 1px;
            line-height: 1;
            text-transform: uppercase;
        }

        .header-left .subtitle {
            color: #fff;
            font-weight: 600;
            font-size: .95rem;
            letter-spacing: 1px;
            margin-top: 6px;
        }

        .header-right {
            background: linear-gradient(135deg, var(--gold-dark), var(--gold) 40%, var(--gold-dark));
            color: var(--navy);
            flex: 1.6;
            min-width: 260px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 12px 15px;
            flex-wrap: wrap;
        }

        .pkg-code-box {
            text-align: center;
            font-weight: 800;
            line-height: 1.1;
        }

        .pkg-code-box .lbl {
            font-size: .7rem;
            letter-spacing: 1px;
        }

        .pkg-code-box .code {
            font-size: 1.3rem;
            background: var(--navy);
            color: #fff;
            padding: 2px 10px;
            border-radius: 3px;
            display: inline-block;
            margin-top: 3px;
        }

        .header-right .days {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-weight: 700;
            font-size: 2.3rem;
            color: var(--navy);
            text-align: center;
        }

        .company-strip {
            background: var(--navy);
            color: var(--gold);
            text-align: center;
            font-weight: 800;
            letter-spacing: 1px;
            font-size: .95rem;
            padding: 8px;
            margin-bottom: 14px;
            border-radius: 2px;
        }

        .tags-row {
            display: flex;
            gap: 8px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .tag-badge {
            background: var(--navy);
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .5px;
            padding: 4px 10px;
            border-radius: 3px;
        }

        .cover-frame {
            width: 100%;
            max-height: 320px;
            overflow: hidden;
            border: 3px solid var(--gold);
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .cover-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ---------- SECTION TITLES ---------- */
        .section-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 900;
            font-size: 1.15rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--navy);
            border-left: 5px solid var(--gold);
            padding-left: 10px;
            margin: 22px 0 12px;
        }

        /* ---------- WIDE / LISTING TABLES ---------- */
        .pkg-table {
            border-collapse: collapse;
            width: 100%;
            font-size: .82rem;
            margin-bottom: 8px;
        }

        .pkg-table th,
        .pkg-table td {
            border: 1px solid #333;
            text-align: center;
            vertical-align: middle;
            padding: 6px 8px;
        }

        .pkg-table thead th {
            background: var(--navy);
            color: #fff;
            font-weight: 700;
        }

        .pkg-table tbody tr:nth-child(odd) {
            background: var(--grey-row);
        }

        .pkg-table tbody tr:nth-child(even) {
            background: #fff;
        }

        .pkg-table .note-row td {
            background: var(--navy);
            color: #fff;
            font-size: .72rem;
            text-align: left;
            font-weight: 400;
        }

        .pkg-table .note-row b {
            background: #fff;
            color: var(--navy);
            padding: 1px 6px;
            border-radius: 2px;
            margin-right: 4px;
        }

        .stars {
            color: var(--gold-dark);
            letter-spacing: 1px;
        }

        /* ---------- LABEL / VALUE (PAIR) TABLES ---------- */
        .room-table {
            border-collapse: collapse;
            width: 100%;
            font-size: .82rem;
            margin-bottom: 14px;
        }

        .room-table th,
        .room-table td {
            border: 1px solid #333;
            padding: 8px 10px;
        }

        .room-table th {
            background: var(--navy);
            color: #fff;
            font-weight: 800;
            text-align: left;
            white-space: nowrap;
            width: 1%;
        }

        .room-table td {
            text-align: left;
        }

        .room-table tbody tr:nth-child(odd) td {
            background: var(--grey-row);
        }

        /* ---------- PRICING ---------- */
        .pricing-table thead th {
            background: var(--navy);
        }

        .pricing-table td.pricing-currency {
            background: var(--peach) !important;
            color: var(--navy);
            font-weight: 800;
        }

        .price-caption {
            text-align: right;
            font-weight: 700;
            font-size: .85rem;
            margin: 4px 0 20px;
            font-style: italic;
        }

        /* ---------- NOTES / TERMS COLUMNS ---------- */
        .icon-box {
            border: 2px solid var(--gold);
            border-radius: 15px;
            padding: 18px;
            height: 100%;
        }

        .notes-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 900;
            font-size: 1.05rem;
            letter-spacing: 1px;
            margin-bottom: 10px;
            color: var(--navy);
        }

        .notes-list {
            font-size: .8rem;
            line-height: 1.55;
            padding-left: 18px;
        }

        .notes-list li {
            margin-bottom: 8px;
        }

        .brochure-text {
            font-size: .8rem;
            line-height: 1.6;
            color: #333;
            white-space: pre-line;
        }

        /* ---------- IMAGE GRID ---------- */
        .image-grid-card {
            border: 2px solid var(--gold);
            border-radius: 10px;
            overflow: hidden;
            text-align: center;
            margin-bottom: 15px;
        }

        .image-grid-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .image-grid-card p {
            margin: 0;
            padding: 8px;
            background: var(--navy);
            color: var(--gold);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .5px;
        }

        /* ---------- APPLICANT SIGN (matches the brochure's sign-off line) ---------- */
        .applicant-sign {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            margin-top: 25px;
            color: var(--navy);
        }

        .sign-line {
            border-bottom: 2px solid #000;
            display: inline-block;
            width: 300px;
            margin-left: 15px;
        }

        /* ---------- FOOTER (bolder, wider letter-spacing like the brochure) ---------- */
        .page-footer {
            background: var(--navy);
            color: #fff;
            text-align: center;
            padding: 8px;
            font-weight: 700;
            letter-spacing: 2px;
            font-size: .75rem;
            margin-top: 20px;
            border-radius: 2px;
        }

        @if ($forPdf)
            /* Wide tables were overflowing the PDF page edge with nowrap headers
               and no fixed widths - fixed layout + wrapping keeps every table
               inside the printable area. */
            .pkg-table,
            .room-table {
                table-layout: fixed;
                width: 100%;
            }

            .pkg-table th,
            .pkg-table td,
            .room-table th,
            .room-table td {
                white-space: normal !important;
                word-break: break-word;
                font-size: 9px;
                padding: 5px 4px;
            }

            /* Keep a section heading glued to the content that follows it. */
            .section-title {
                page-break-after: avoid;
                break-after: avoid;
            }

            .table-responsive,
            .room-table,
            .pkg-table {
                page-break-before: avoid;
                break-before: avoid;
            }

            /* Package Pricing block always moves together as one unit. */
            .pricing-keep-together {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .icon-box {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        @endif
    </style>
</head>

<body>
    <div class="sheet">

        {{-- ---------- HEADER BANNER ---------- --}}
        <div class="header-banner">
            <div class="header-left">
                <h1>{{ $package->name }}</h1>
                <div class="subtitle">
                    {{ $package->category_zone }}
                    @if ($package->nearby)
                        &middot; {{ $package->nearby }}
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="pkg-code-box">
                    <div class="lbl">PKG CODE</div>
                    <div class="code">{{ $package->code ?? '-' }}</div>
                </div>
                <div class="days">{{ $package->days ?? '-' }} Days Package</div>
            </div>
        </div>

        /* {{-- ---------- COMPANY NAME ---------- --}} */
        /* @if ($package->company)
            <div class="company-strip">{{ strtoupper($package->company->company_name) }}</div>
        @endif */

        {{-- ---------- TAGS ---------- --}}
        <div class="tags-row">
            @if ($package->travel_route)
                <span
                    class="tag-badge">{{ \App\Enums\TravelRoute::options()[$package->travel_route] ?? $package->travel_route }}</span>
            @endif
            <span class="tag-badge">{{ $package->year }}</span>
            <span class="tag-badge">Medina Arrival:
                {{ $package->medina_arrival == 'before_hajj' ? 'Before Hajj' : 'After Hajj' }}</span>
            <span class="tag-badge">Hajj Duration: {{ ucfirst($package->hajj_duration) }}</span>
        </div>

        {{-- ---------- COVER IMAGE ---------- --}}
        @if ($package->image)
            <div class="cover-frame">
                <img src="{{ $forPdf ? public_path('storage/' . $package->image) : asset('storage/' . $package->image) }}"
                    alt="{{ $package->name }}">
            </div>
        @endif

        {{-- ---------- MAKTAB INFO ---------- --}}
        <div class="section-title">Maktab Information</div>
        <table class="room-table">
            <tr>
                <th>Maktab</th>
                <td>{{ $package->maktab }}</td>
                <th>Maktab Number</th>
                <td>{{ $package->maktab_number }}</td>
            </tr>
            <tr>
                <th>Maktab Address</th>
                <td colspan="3">{{ $package->maktabAddress->maktab_address ?? '-' }}</td>
            </tr>
            <tr>
                <th>Office Address</th>
                <td colspan="3">{{ $package->maktabAddress->office_address ?? '-' }}</td>
            </tr>
        </table>

        {{-- ---------- ACCOMMODATION ---------- --}}
        @if ($package->accommodations && $package->accommodations->count())
            <div class="section-title">Accommodation</div>
            <div class="table-responsive">
                <table class="pkg-table">
                    <thead>
                        <tr>
                            <th>Place</th>
                            <th>Type</th>
                            <th>Star Rating</th>
                            <th>Hotel</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Days / Nights</th>
                            <th>Food Package</th>
                            <th>Distance (m)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($package->accommodations as $acc)
                            <tr>
                                <td>{{ \App\Enums\Place::options()[$acc->place] ?? $acc->place }}</td>
                                <td>{{ \App\Enums\AccommodationType::options()[$acc->accommodation_type] ?? $acc->accommodation_type }}
                                </td>
                                <td class="stars">
                                    {{ \App\Enums\SaudiStarRating::options()[$acc->saudi_star_rating] ?? $acc->saudi_star_rating }}
                                </td>
                                <td>{{ \App\Enums\Hotel::options()[$acc->hotel] ?? $acc->hotel }}</td>
                                <td>{{ $acc->check_in ? \Carbon\Carbon::parse($acc->check_in)->format('d M, Y') : '-' }}
                                </td>
                                <td>{{ $acc->check_out ? \Carbon\Carbon::parse($acc->check_out)->format('d M, Y') : '-' }}
                                </td>
                                <td>{{ $acc->days }} / {{ $acc->nights }}</td>
                                <td>{{ \App\Enums\FoodPackage::options()[$acc->food_package] ?? $acc->food_package }}
                                </td>
                                <td>{{ $acc->distance ?? '-' }}</td>
                            </tr>
                            @if (
                                $acc->group_ziarat ||
                                    $acc->religious_lectures ||
                                    $acc->distribution ||
                                    $acc->camp ||
                                    $acc->arafat ||
                                    $acc->shuttle ||
                                    $acc->bedding ||
                                    $acc->sharing ||
                                    $acc->note)
                                <tr class="note-row">
                                    <td colspan="9">
                                        @if ($acc->group_ziarat)
                                            <b>Group Ziarat:</b> {{ $acc->group_ziarat }} &nbsp;
                                        @endif
                                        @if ($acc->religious_lectures)
                                            <b>Religious Lectures:</b> {{ $acc->religious_lectures }} &nbsp;
                                        @endif
                                        @if ($acc->distribution)
                                            <b>Distribution:</b> {{ $acc->distribution }} &nbsp;
                                        @endif
                                        @if ($acc->camp)
                                            <b>Camp:</b> {{ $acc->camp }} &nbsp;
                                        @endif
                                        @if ($acc->arafat)
                                            <b>Arafat:</b> {{ $acc->arafat }} &nbsp;
                                        @endif
                                        @if ($acc->shuttle)
                                            <b>Shuttle:</b> {{ $acc->shuttle }} &nbsp;
                                        @endif
                                        @if ($acc->bedding)
                                            <b>Bedding:</b> {{ $acc->bedding }} &nbsp;
                                        @endif
                                        @if ($acc->sharing)
                                            <b>Sharing:</b> {{ $acc->sharing }} ({{ $acc->sharing_type }}) &nbsp;
                                        @endif
                                        @if ($acc->note)
                                            <b>Note:</b> {{ $acc->note }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ---------- ROOM TYPE ---------- --}}
        <div class="section-title">Room Type</div>
        <table class="room-table">
            <tr>
                <th>Room Type</th>
                <td>{{ $package->room_type ?? '-' }}</td>
                <th>Azizia Room Type</th>
                <td>{{ $package->azizia_room_type ?? '-' }}</td>
            </tr>
            <tr>
                <th>Makkah Type</th>
                <td>{{ $package->makkah_type ?? '-' }}</td>
                <th>Medinah Type</th>
                <td>{{ $package->medinah_type ?? '-' }}</td>
            </tr>
            <tr>
                <th>Azizia Type</th>
                <td>{{ $package->azizia_type ?? '-' }}</td>
                <th>Mina Type</th>
                <td>{{ $package->mina_type ?? '-' }}</td>
            </tr>
        </table>

        {{-- ---------- PRICING ---------- --}}
        <div class="pricing-keep-together">
            <div class="section-title">Package Pricing</div>
            <div class="table-responsive">
                <table class="pkg-table pricing-table">
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th>Adult</th>
                            <th>Child</th>
                            <th>Infant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (['pkr' => 'PKR', 'sar' => 'SAR', 'usd' => 'USD', 'eur' => 'EUR', 'gbp' => 'GBP', 'aed' => 'AED'] as $cur => $label)
                            <tr>
                                <td class="pricing-currency">{{ $label }}</td>
                                <td>{{ number_format($package->{"adult_{$cur}"} ?? 0, 2) }}</td>
                                <td>{{ number_format($package->{"child_{$cur}"} ?? 0, 2) }}</td>
                                <td>{{ number_format($package->{"infant_{$cur}"} ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="price-caption">"Book Early, Prices and Packages Subject to Change."</div>

            <table class="room-table">
                <tr>
                    <th>PKR ROE</th>
                    <td>{{ $package->pkr_roe }}</td>
                    <th>USD ROE</th>
                    <td>{{ $package->usd_roe }}</td>
                    <th>GBP ROE</th>
                    <td>{{ $package->gbp_roe }}</td>
                    <th>EURO ROE</th>
                    <td>{{ $package->euro_roe }}</td>
                    <th>AED ROE</th>
                    <td>{{ $package->aed_roe }}</td>
                </tr>
            </table>
        </div>

        {{-- ---------- TRANSPORT ---------- --}}
        <div class="section-title">Transport</div>
        <table class="room-table">
            <tr>
                <th>Route</th>
                <td>{{ $package->transport->route ?? '-' }}</td>
                <th>Type</th>
                <td>{{ $package->transport->type ?? '-' }}</td>
            </tr>
            <tr>
                <th>Arrival Date & Time</th>
                <td>
                    @if(isset($package->transport->arrival_date))
                        {{ \Carbon\Carbon::parse($package->transport->arrival_date)->format('d M, Y') }}
                        @if(!empty($package->transport->arrival_time))
                            ({{ \Carbon\Carbon::parse($package->transport->arrival_time)->format('h:i A') }})
                        @endif
                    @else
                        {{ $package->transport->arrival ?? '-' }}
                    @endif
                </td>
                <th>Vehicle</th>
                <td>{{ $package->transport->vehicle ?? '-' }}</td>
            </tr>
        </table>

        @if ($package->transportFlights && $package->transportFlights->count())
            <div class="section-title" style="font-size: 1rem; margin-top: 14px;">Flights</div>
            <div class="table-responsive">
                <table class="pkg-table">
                    <thead>
                        <tr>
                            <th>Airline</th>
                            <th>Flight No.</th>
                            <th>Class</th>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>PNR</th>
                            <th>Ticket (SAR)</th>
                            <th>Preferred</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($package->transportFlights as $f)
                            <tr>
                                <td>{{ $f->airline }}</td>
                                <td>{{ $f->flight_no }}</td>
                                <td>{{ $f->flight_class }}</td>
                                <td>{{ $f->origin }}</td>
                                <td>{{ $f->destination }}</td>
                                <td>{{ $f->departure_date ? \Carbon\Carbon::parse($f->departure_date)->format('d M, Y') : '-' }}
                                    {{ $f->departure_time }}</td>
                                <td>{{ $f->arrival_date ? \Carbon\Carbon::parse($f->arrival_date)->format('d M, Y') : '-' }}
                                    {{ $f->arrival_time }}</td>
                                <td>{{ $f->pnr_no }}</td>
                                <td>{{ number_format($f->ticket_amount ?? 0, 2) }}</td>
                                <td>{{ $f->is_preferred ? 'Yes' : 'No' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($package->transportTrains && $package->transportTrains->count())
            <div class="section-title" style="font-size: 1rem; margin-top: 14px;">Trains</div>
            <div class="table-responsive">
                <table class="pkg-table">
                    <thead>
                        <tr>
                            <th>Railway</th>
                            <th>Train No.</th>
                            <th>Class</th>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>PNR</th>
                            <th>Ticket (SAR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($package->transportTrains as $t)
                            <tr>
                                <td>{{ $t->railway }}</td>
                                <td>{{ $t->train_no }}</td>
                                <td>{{ $t->train_class }}</td>
                                <td>{{ $t->origin }}</td>
                                <td>{{ $t->destination }}</td>
                                <td>{{ $t->departure_date ? \Carbon\Carbon::parse($t->departure_date)->format('d M, Y') : '-' }}
                                    {{ $t->departure_time }}</td>
                                <td>{{ $t->arrival_date ? \Carbon\Carbon::parse($t->arrival_date)->format('d M, Y') : '-' }}
                                    {{ $t->arrival_time }}</td>
                                <td>{{ $t->pnr_no }}</td>
                                <td>{{ number_format($t->ticket_amount ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ---------- TRAINING/GIFTS + ITINERARY + TERMS ---------- --}}
        @php
            $hasTraining =
                ($package->trainingSessions && $package->trainingSessions->count()) ||
                ($package->giveaways && $package->giveaways->count());
            $hasItineraryText = $package->itinerary && $package->itinerary->description;
            $hasTerms = $package->terms && $package->terms->content;
        @endphp
        @if ($hasTraining || $hasItineraryText || $hasTerms)
            <div class="row mt-4">
                <div class="col-lg-4 mb-3">
                    @if ($hasTraining)
                        <div class="icon-box">
                            <div class="notes-title">Training / Gifts</div>

                            @if ($package->trainingSessions && $package->trainingSessions->count())
                                <div class="fw-bold mb-1" style="font-size:.82rem;">Training Sessions</div>
                                <ul class="notes-list">
                                    @foreach ($package->trainingSessions as $session)
                                        <li>
                                            {{ $session->name }}
                                            @if ($session->session_date)
                                                ({{ \Carbon\Carbon::parse($session->session_date)->format('d M, Y') }}
                                                @if ($session->session_time)
                                                    at
                                                    {{ \Carbon\Carbon::parse($session->session_time)->format('h:i A') }}
                                                @endif)
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($package->giveaways && $package->giveaways->count())
                                <div class="fw-bold mb-1 mt-2" style="font-size:.82rem;">Giveaways</div>
                                <ul class="notes-list">
                                    @foreach ($package->giveaways as $g)
                                        <li>{{ $g->code }} - {{ $g->name }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="col-lg-4 mb-3">
                    @if ($hasItineraryText)
                        <div class="icon-box">
                            <div class="notes-title">Itinerary</div>
                            <div class="brochure-text">{!! nl2br(e($package->itinerary->description)) !!}</div>
                        </div>
                    @endif
                </div>
                <div class="col-lg-4 mb-3">
                    @if ($hasTerms)
                        <div class="icon-box">
                            <div class="notes-title">Terms &amp; Condition</div>
                            <div class="brochure-text">{!! nl2br(e($package->terms->content)) !!}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ---------- ITINERARY IMAGES ---------- --}}
        @php
            $itineraryImages = [
                'mina_image' => 'MINA',
                'arafat_image' => 'ARAFAT',
                'muzdalifah_image' => 'MUZDALIFAH',
                'makkah_mina_rami_day_one_image' => 'MAKKAH / MINA RAMI - DAY ONE',
                'mina_rami_day_two_image' => 'MINA RAMI - DAY TWO',
                'mina_makkah_rami_day_three_image' => 'MINA / MAKKAH RAMI - DAY THREE',
            ];
            $itineraryModel = $package->itinerary;
            $hasItineraryImages = $itineraryModel
                ? collect($itineraryImages)->keys()->contains(fn($f) => !empty($itineraryModel->{$f}))
                : false;
        @endphp
        @if ($hasItineraryImages)
            <div class="section-title">Itinerary Images</div>
            <div class="row">
                @foreach ($itineraryImages as $field => $label)
                    @if (!empty($itineraryModel->{$field}))
                        <div class="col-4">
                            <div class="image-grid-card">
                                <img src="{{ $forPdf ? public_path('storage/' . $itineraryModel->{$field}) : asset('storage/' . $itineraryModel->{$field}) }}"
                                    alt="{{ $label }}">
                                <p>{{ $label }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- ---------- APPLICANT SIGN ---------- --}}
        <div class="applicant-sign">Applicant Sign: <span class="sign-line"></span></div>

        {{-- ---------- FOOTER ---------- --}}
        <div class="page-footer">
            Generated by {{ $package->company->company_name ?? config('app.name') }} &middot;
            {{ now()->format('d M, Y') }}
        </div>

    </div>
</body>

</html>
