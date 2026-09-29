@php
    $forPdf = $forPdf ?? false;
    $accentColor = $package->color ?? '#000000';
@endphp

<style>
    .brochure-accent-title {
        color: {{ $accentColor }} !important;
        border-left-color: {{ $accentColor }} !important;
    }

    .brochure-accent-bg {
        background: {{ $accentColor }} !important;
    }
</style>

@if ($forPdf)
    <style>
        /* Wide multi-column tables (Accommodation, Flights, Trains, Pricing)
           were using white-space:nowrap headers with no fixed widths, so the
           total row width went past the PDF page edge and got cut off.
           table-layout:fixed + wrapping text forces every table to fit
           within the page width instead. */
        .brochure-table {
            table-layout: fixed;
            width: 100%;
        }

        .brochure-table th,
        .brochure-table td {
            white-space: normal !important;
            word-break: break-word;
            font-size: 10px;
            padding: 6px 5px;
        }

        .pricing-table thead th {
            text-align: center;
        }

        /* Keep a section heading glued to the content that follows it so a
           title never gets stranded alone at the bottom of a page while its
           table starts fresh on the next one. This only prevents a split at
           that exact point - it does not insert any new page breaks. */
        .section-title {
            page-break-after: avoid;
            break-after: avoid;
        }

        .table-responsive,
        .info-table,
        .pricing-table,
        .roe-table {
            page-break-before: avoid;
            break-before: avoid;
        }

        .pricing-keep-together {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .roe-table {
            table-layout: auto;
            width: 100%;
            margin-top: 15px;
        }

        .roe-table th,
        .roe-table td {
            white-space: nowrap !important;
            word-break: normal !important;
            font-size: 8px;
            padding: 5px 3px;
        }
    </style>
@endif

{{-- ====================== BROCHURE CARD ====================== --}}
<div class="package-brochure" id="package-brochure">

    {{-- ---------- HEADER BANNER ---------- --}}
    <div class="brochure-header" style="background: {{ $accentColor }};">
            @php
                $isPkgHajj = ($package->hajj_duration || ($package->medina_arrival ?? false) || session('dashboard_package', 'hajj') === 'hajj');
                $pkgLogoRel = $isPkgHajj ? 'assets/images/logo/kgm.png' : 'assets/images/logo/logo.png';
            @endphp
            <tr>
                <td class="header-logo-cell">
                    <img src="{{ $forPdf ? public_path($pkgLogoRel) : asset($pkgLogoRel) }}"
                        alt="Company Logo" class="brochure-logo">
                </td>
                <td class="header-title-cell">
                    <h1 class="brochure-title">{{ $package->name }}</h1>
                    <p class="brochure-sub">
                        {{ $package->category_zone }}
                        @if ($package->nearby)
                            &middot; {{ $package->nearby }}
                        @endif
                    </p>
                </td>
                <td class="header-badge-cell">
                    <div class="brochure-badge">PKG CODE<br><strong>{{ $package->code ?? '-' }}</strong>
                    </div>
                    <div class="brochure-badge brochure-badge-days">
                        {{ $package->days ?? '-' }} Days Package
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ---------- COMPANY NAME (own table, separate from the tag pills below) ---------- --}}
    @if ($package->company)
        <div class="brochure-company-bar">
            <table class="company-table">
                <tr>
                    <td class="company-table-cell">{{ strtoupper($package->company->company_name) }}</td>
                </tr>
            </table>
        </div>
    @endif

    <div class="brochure-tags">
        @if ($package->travel_route)
            <span
                class="tag">{{ \App\Enums\TravelRoute::options()[$package->travel_route] ?? $package->travel_route }}</span>
        @endif
        <span class="tag">{{ $package->year }}</span>
        <span class="tag">Medina Arrival:
            {{ $package->medina_arrival == 'before_hajj' ? 'Before Hajj' : 'After Hajj' }}</span>
        <span class="tag">Hajj Duration: {{ ucfirst($package->hajj_duration) }}</span>
    </div>

    @if ($package->image)
        <div class="brochure-cover">
            <img src="{{ $forPdf ? public_path('assets/images/packages/' . $package->image) : asset('assets/images/packages/' . $package->image) }}"
                alt="{{ $package->name }}">
        </div>
    @endif

    {{-- ---------- MAKTAB INFO ---------- --}}
    <div class="brochure-section">
        <h3 class="section-title brochure-accent-title">Maktab Information</h3>
        <table class="info-table">
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
    </div>

    {{-- ---------- ACCOMMODATION ---------- --}}
    @if ($package->accommodations && $package->accommodations->count())
        <div class="brochure-section">
            <h3 class="section-title brochure-accent-title">Accommodation</h3>
            <div class="table-responsive">
                <table class="brochure-table">
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
                                <td>{{ \App\Enums\SaudiStarRating::options()[$acc->saudi_star_rating] ?? $acc->saudi_star_rating }}
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
                                            <strong>Group Ziarat:</strong> {{ $acc->group_ziarat }} &nbsp;
                                        @endif
                                        @if ($acc->religious_lectures)
                                            <strong>Religious Lectures:</strong>
                                            {{ $acc->religious_lectures }} &nbsp;
                                        @endif
                                        @if ($acc->distribution)
                                            <strong>Distribution:</strong> {{ $acc->distribution }} &nbsp;
                                        @endif
                                        @if ($acc->camp)
                                            <strong>Camp:</strong> {{ $acc->camp }} &nbsp;
                                        @endif
                                        @if ($acc->arafat)
                                            <strong>Arafat:</strong> {{ $acc->arafat }} &nbsp;
                                        @endif
                                        @if ($acc->shuttle)
                                            <strong>Shuttle:</strong> {{ $acc->shuttle }} &nbsp;
                                        @endif
                                        @if ($acc->bedding)
                                            <strong>Bedding:</strong> {{ $acc->bedding }} &nbsp;
                                        @endif
                                        @if ($acc->sharing)
                                            <strong>Sharing:</strong> {{ $acc->sharing }}
                                            ({{ $acc->sharing_type }})
                                            &nbsp;
                                        @endif
                                        @if ($acc->note)
                                            <strong>Note:</strong> {{ $acc->note }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ---------- ROOM TYPE & PRICING ---------- --}}
    <div class="brochure-section">
        <h3 class="section-title brochure-accent-title">Room Type</h3>
        <table class="info-table">
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
    </div>

    <div class="brochure-section pricing-keep-together">
        <h3 class="section-title brochure-accent-title">Package Pricing</h3>
        <div class="table-responsive">
            <table class="brochure-table pricing-table">
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

        <table class="info-table roe-table mt-3">
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
    <div class="brochure-section">
        <h3 class="section-title brochure-accent-title">Transport</h3>
        <table class="info-table">
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
            <h5 class="mt-4 mb-2">Flights</h5>
            <div class="table-responsive">
                <table class="brochure-table">
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
            <h5 class="mt-4 mb-2">Trains</h5>
            <div class="table-responsive">
                <table class="brochure-table">
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
    </div>

    {{-- ---------- TRAINING/GIFTS + ITINERARY + TERMS, ONE ROW ---------- --}}
    @php
        $hasTraining =
            ($package->trainingSessions && $package->trainingSessions->count()) ||
            ($package->giveaways && $package->giveaways->count());
        $hasItineraryText = $package->itinerary && $package->itinerary->description;
        $hasTerms = $package->terms && $package->terms->content;
    @endphp
    @if ($hasTraining || $hasItineraryText || $hasTerms)
        <div class="brochure-section">
            <table class="triple-col-table">
                <tr>
                    <td class="triple-col-cell">
                        @if ($hasTraining)
                            <h3 class="section-title brochure-accent-title">Training / Gifts</h3>

                            @if ($package->trainingSessions && $package->trainingSessions->count())
                                <h5 class="mb-2">Training Sessions</h5>
                                <ul class="brochure-list">
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
                                <h5 class="mb-2 mt-3">Giveaways</h5>
                                <ul class="brochure-list">
                                    @foreach ($package->giveaways as $g)
                                        <li>{{ $g->code }} - {{ $g->name }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        @endif
                    </td>
                    <td class="triple-col-cell">
                        @if ($hasItineraryText)
                            <h3 class="section-title brochure-accent-title">Itinerary</h3>
                            <div class="brochure-text">{!! nl2br(e($package->itinerary->description)) !!}</div>
                        @endif
                    </td>
                    <td class="triple-col-cell">
                        @if ($hasTerms)
                            <h3 class="section-title brochure-accent-title">Terms & Condition</h3>
                            <div class="brochure-text">{!! nl2br(e($package->terms->content)) !!}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif

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
        <div class="brochure-section">
            <h3 class="section-title brochure-accent-title">Itinerary Images</h3>
            @php
                $activeItineraryImages = collect($itineraryImages)
                    ->filter(fn($label, $field) => !empty($itineraryModel->{$field}))
                    ->chunk(3);
            @endphp
            <table class="image-grid-table">
                @foreach ($activeItineraryImages as $chunk)
                    <tr>
                        @foreach ($chunk as $field => $label)
                            <td class="image-grid-cell">
                                <div class="itinerary-image-card">
                                    <img src="{{ $forPdf ? public_path('assets/images/packages/itinerary/' . $itineraryModel->{$field}) : asset('assets/images/packages/itinerary/' . $itineraryModel->{$field}) }}"
                                        alt="{{ $label }}">
                                    <p>{{ $label }}</p>
                                </div>
                            </td>
                        @endforeach
                        @for ($i = $chunk->count(); $i < 3; $i++)
                            <td class="image-grid-cell"></td>
                        @endfor
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <div class="brochure-footer">
        <p>Generated by {{ $package->company->company_name ?? config('app.name') }} &middot;
            {{ now()->format('d M, Y') }}</p>
    </div>

</div>
{{-- ====================== /BROCHURE CARD ====================== --}}

<style>
    .package-brochure {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .brochure-header {
        padding: 20px 25px;
        color: #fff;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    .header-table td {
        vertical-align: middle;
        padding: 0;
    }

    .header-logo-cell {
        width: 120px;
    }

    .header-title-cell {
        text-align: center;
    }

    .header-badge-cell {
        width: 190px;
        text-align: right;
    }

    .brochure-logo {
        height: 55px;
        background: #fff;
        padding: 6px 10px;
        border-radius: 6px;
    }

    .brochure-title {
        font-size: 24px;
        font-weight: 700;
        margin: 0;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .brochure-sub {
        margin: 4px 0 0;
        font-size: 13px;
        opacity: 0.9;
    }

    .brochure-badge {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 11px;
        text-align: center;
        margin-bottom: 8px;
    }

    .brochure-badge-days {
        background: #fff;
        color: #000;
        font-weight: 700;
        margin-bottom: 0;
    }

    /* ---- Company name bar: its own table, visually distinct from the
       rounded tag pills that follow it ---- */
    .brochure-company-bar {
        padding: 12px 25px;
        background: #fff;
        border-bottom: 1px solid #eee;
    }

    .company-table {
        width: 100%;
        border-collapse: collapse;
    }

    .company-table-cell {
        padding: 8px 14px;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: {{ $accentColor }};
        background: #f8f9fa;
        border: 1px solid #eee;
        border-left: 4px solid {{ $accentColor }};
        border-radius: 4px;
    }

    .brochure-tags {
        padding: 14px 25px;
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
        line-height: 2.4;
    }

    .brochure-tags .tag {
        display: inline-block;
        background: #eef1f5;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 12px;
        font-weight: 600;
        color: #333;
        margin-right: 8px;
    }

    .brochure-cover {
        max-height: none;
        text-align: center;
    }

    .brochure-cover img {
        width: auto;
        height: 150px;
        max-width: 100%;
        object-fit: initial;
        margin-top: 10px;
    }

    .brochure-section {
        padding: 22px 25px;
        border-bottom: 1px solid #f0f0f0;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
        color: #333;
        margin-bottom: 14px;
        border-left: 4px solid #333;
        padding-left: 10px;
    }

    .info-table,
    .brochure-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    /* reduced fixed width + no-wrap so labels don't leave a big empty gap on the left */
    .info-table th {
        background: #f8f9fa;
        text-align: left;
        padding: 8px 12px;
        width: 1%;
        white-space: nowrap;
        font-weight: 600;
        border: 1px solid #eee;
    }

    .info-table td {
        padding: 8px 12px;
        border: 1px solid #eee;
    }

    /* ROE table: five pairs, one row, evenly spaced */
    .roe-table th,
    .roe-table td {
        width: 10%;
        white-space: nowrap;
        text-align: center;
    }

    .brochure-table thead th {
        background: {{ $accentColor }};
        color: #fff;
        padding: 10px 12px;
        text-align: left;
        white-space: nowrap;
    }

    .brochure-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #eee;
    }

    .brochure-table .note-row td {
        background: #fffaf0;
        font-size: 12px;
        color: #555;
    }

    /* ---- Package Pricing table redesign ---- */
    .pricing-table {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #eee;
    }

    .pricing-table thead th {
        text-align: center;
        padding: 10px 8px;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    .pricing-table tbody td {
        text-align: center;
        padding: 10px 8px;
        font-weight: 600;
        color: #333;
    }

    .pricing-table tbody td.pricing-currency {
        text-align: center;
        color: {{ $accentColor }};
        font-weight: 700;
    }

    .pricing-table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .pricing-table tbody tr:hover {
        background: #f1f3f5;
    }

    .triple-col-table {
        width: 100%;
        border-collapse: collapse;
    }

    .triple-col-cell {
        width: 33.33%;
        vertical-align: top;
        padding: 0 16px;
        border-left: 1px solid #eee;
    }

    .triple-col-cell:first-child {
        padding-left: 0;
        border-left: none;
    }

    .triple-col-cell:last-child {
        padding-right: 0;
    }

    .brochure-list {
        margin: 0;
        padding-left: 20px;
    }

    .brochure-list li {
        margin-bottom: 6px;
        font-size: 13px;
    }

    .brochure-text {
        font-size: 13px;
        line-height: 1.7;
        color: #444;
        white-space: pre-line;
    }

    .image-grid-table {
        width: 100%;
        border-collapse: collapse;
    }

    .image-grid-cell {
        width: 33.33%;
        padding: 6px;
        vertical-align: top;
    }

    .itinerary-image-card {
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
        text-align: center;
        /* background: #f8f9fa; */
    }

    .itinerary-image-card img {
        width: 100%;
        height: 180px;
        object-fit: contain;
        object-position: center;
        display: block;
        /* background: #f8f9fa; */
        padding: 6px;
        box-sizing: border-box;
    }

    .itinerary-image-card p {
        margin: 8px 0;
        font-size: 12px;
        font-weight: 600;
        padding: 0 6px;
    }

    .brochure-footer {
        padding: 14px 25px;
        text-align: center;
        font-size: 11px;
        color: #999;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .package-brochure {
            box-shadow: none;
        }
    }
</style>
