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
    <style>
        :root {
            --navy: {{ $package->color ?? '#1b1f4b' }};
            --gold: #d9a441;
            --gold-dark: #c8922e;
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

        .header-banner {
            display: flex;
            align-items: stretch;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .header-left {
            background: var(--navy);
            color: var(--gold);
            padding: 27px 25px;
            flex: 2;
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
            margin-top: 5px;
            text-transform: uppercase;
        }

        .header-right {
            background: linear-gradient(135deg, var(--gold-dark), var(--gold) 40%, #b9812a);
            color: var(--navy);
            flex: 1.6;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 10px 15px;
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
        }

        .header-right .days {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-weight: 700;
            font-size: 2.3rem;
            color: var(--navy);
        }

        .tags-row {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            width: 100%;
        }

        .tag-badge {
            background: var(--navy);
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .5px;
            padding: 4px 10px;
            border-radius: 3px;
            text-transform: uppercase;
        }

        .pkg-table {
            border-collapse: collapse;
            width: 100%;
            font-size: .85rem;
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

        .stars {
            color: var(--gold-dark);
            letter-spacing: 1px;
        }

        .note-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 18px 0;
            flex-wrap: wrap;
        }

        .ticket-note {
            font-weight: 700;
            font-size: 1rem;
        }

        .note-box {
            background: var(--navy);
            color: #fff;
            font-size: .78rem;
            padding: 8px 14px;
            border-radius: 2px;
            flex: 1;
            text-align: center;
        }

        .note-box b {
            background: #fff;
            color: var(--navy);
            padding: 2px 8px;
            margin-right: 8px;
            border-radius: 2px;
        }

        .room-table {
            border-collapse: collapse;
            width: 100%;
            font-size: .85rem;
            margin-bottom: 10px;
        }

        .room-table td,
        .room-table th {
            border: 1px solid #333;
            padding: 8px 10px;
            text-align: center;
        }

        .room-table .room-header {
            background: var(--navy);
            color: #fff;
            font-weight: 800;
        }

        .room-table tbody tr:nth-child(odd) td:not(.room-header) {
            background: var(--grey-row);
        }

        .price-caption {
            text-align: right;
            font-weight: 700;
            margin: 10px 0 20px;
        }

        .notes-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 900;
            font-size: 1.4rem;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .notes-list {
            font-size: .85rem;
            line-height: 1.5;
        }

        .notes-list li {
            margin-bottom: 10px;
        }

        .icon-box {
            border: 2px solid var(--gold);
            border-radius: 15px;
            padding: 20px;
        }

        .icon-item {
            text-align: center;
            margin-bottom: 15px;
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--navy);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-size: 1.5rem;
        }

        .icon-item .label {
            font-weight: 800;
            font-size: .8rem;
            letter-spacing: .5px;
        }

        .icon-item .desc {
            font-size: .68rem;
            color: #555;
        }

        .zone-badge {
            background: var(--navy);
            color: var(--gold);
            font-weight: 800;
            padding: 6px 20px;
            border-radius: 20px;
            display: inline-block;
            font-size: .9rem;
            letter-spacing: 1px;
        }

        .zone-badge small {
            display: block;
            color: #fff;
            font-size: .6rem;
            font-weight: 600;
        }

        .applicant-sign {
            font-weight: 800;
            font-size: 1.3rem;
            margin-top: 25px;
        }

        .sign-line {
            border-bottom: 2px solid #000;
            display: inline-block;
            width: 300px;
            margin-left: 15px;
        }

        .page-footer {
            background: var(--navy);
            color: #fff;
            text-align: center;
            padding: 6px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-top: 20px;
            border-radius: 2px;
        }
    </style>
</head>

<body>
    <div class="sheet">
        <!-- Banner Section -->
        <div class="header-banner">
            <div class="header-left">
                <h1>{{ $package->name }}</h1>
                <div class="subtitle">{{ $package->travel_route ?? 'Route Info' }}</div>
            </div>
            <div class="header-right">
                <div class="pkg-code-box">
                    <div class="lbl">PKG CODE</div>
                    <div class="code">{{ $package->code ?? 'N/A' }}</div>
                </div>
                <div class="days">{{ $package->days ?? '0' }} Days Package</div>
                <div class="tags-row justify-content-end">
                    <span class="tag-badge">{{ str_replace('_', ' ', $package->medina_arrival) }}</span>
                    <span class="tag-badge">{{ $package->hajj_duration }} HAJJ</span>
                </div>
            </div>
        </div>

        <!-- Main Accommodation Table -->
        <div class="table-responsive">
            <table class="pkg-table">
                <thead>
                    <tr>
                        <th>PLACE</th>
                        <th>ACCOMMODATION TYPE</th>
                        <th>HOTEL</th>
                        <th>RATING</th>
                        <th>FOOD PACKAGE</th>
                        <th>SHUTTLE</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($package->accommodations as $acc)
                        <tr>
                            <td>{{ $acc->place ?? '-' }}</td>
                            <td>{{ $acc->accommodation_type ?? '-' }}</td>
                            <td>{{ $acc->hotel ?? '-' }}</td>
                            <td>
                                @if (!empty($acc->saudi_star_rating))
                                    <span class="stars">
                                        @for ($i = 0; $i < intval($acc->saudi_star_rating); $i++)
                                            ★
                                        @endfor
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $acc->food_package ?? '-' }}</td>
                            <td>{{ $acc->shuttle ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No Accommodation details provided.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Note Strip -->
        <div class="note-strip">
            <div class="ticket-note">Mina Maktab: {{ $package->maktab }} ({{ $package->maktab_number }})</div>
            <div class="note-box">
                <b>INFO:</b> {{ $package->category_zone ?? 'Zone info' }} - {{ $package->nearby ?? 'Nearby info' }}
            </div>
        </div>

        <!-- Pricing Table -->
        <div class="table-responsive">
            <table class="room-table">
                <thead>
                    <tr style="background: var(--navy); color: #fff; font-weight: 800;">
                        <td>ROOM CATEGORY</td>
                        <td>ADULT PRICE</td>
                        <td>CHILD PRICE</td>
                        <td>INFANT PRICE</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="room-header">{{ $package->room_type ?? 'Standard Room' }} (SAR)</td>
                        <td>{{ $package->adult_sar ? 'SAR ' . number_format($package->adult_sar) . '/-' : 'NA' }}</td>
                        <td>{{ $package->child_sar ? 'SAR ' . number_format($package->child_sar) . '/-' : 'NA' }}</td>
                        <td>{{ $package->infant_sar ? 'SAR ' . number_format($package->infant_sar) . '/-' : 'NA' }}
                        </td>
                    </tr>
                    @if ($package->adult_pkr)
                        <tr>
                            <td class="room-header">{{ $package->room_type ?? 'Standard Room' }} (PKR)</td>
                            <td>{{ 'PKR ' . number_format($package->adult_pkr) . '/-' }}</td>
                            <td>{{ $package->child_pkr ? 'PKR ' . number_format($package->child_pkr) . '/-' : 'NA' }}
                            </td>
                            <td>{{ $package->infant_pkr ? 'PKR ' . number_format($package->infant_pkr) . '/-' : 'NA' }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="price-caption">"Book Early, Prices and Packages Subject to Change."</div>

        <!-- Lower Section (Icon Box + Notes) -->
        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="icon-box">
                    <div class="row">
                        <div class="col-6 icon-item">
                            <div class="text-center mb-3">
                                <span class="zone-badge">ZONE
                                    1<small>{{ $package->category_zone ?? 'MAKTAB' }}</small></span>
                            </div>
                            <div class="label">LOCATION</div>
                            <div class="desc">Nearby: {{ $package->nearby ?? 'N/A' }}</div>
                        </div>
                        <div class="col-6 icon-item">
                            <div class="icon-circle">🍽️</div>
                            <div class="label">MINA TYPE</div>
                            <div class="desc">{{ $package->mina_type ?? 'Standard' }}</div>
                        </div>
                        <div class="col-6 icon-item">
                            <div class="icon-circle">🏢</div>
                            <div class="label">AZIZIA TYPE</div>
                            <div class="desc">{{ $package->azizia_type ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12 icon-item">
                            <div class="icon-circle">Route</div>
                            <div class="label">TRANSPORT ROUTE</div>
                            <div class="desc">{{ $package->transport->transport_route ?? 'Route setup pending' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 mb-4">
                <div class="notes-title">ITINERARY &amp; TERMS:</div>
                <ul class="notes-list ps-3">
                    @if ($package->itinerary && !empty($package->itinerary->itinerary_description))
                        <li><b>Itinerary:</b><br>{!! nl2br(e($package->itinerary->itinerary_description)) !!}</li>
                    @endif
                    @if ($package->terms && !empty($package->terms->terms_content))
                        <li><b>Terms &amp; Conditions:</b><br>{!! nl2br(e($package->terms->terms_content)) !!}</li>
                    @endif
                    @if ($package->maktabAddress)
                        <li><b>Office Address:</b> {{ $package->maktabAddress->office_address }}</li>
                        <li><b>Maktab Address:</b> {{ $package->maktabAddress->maktab_address }}</li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="applicant-sign">Applicant Sign: <span class="sign-line"></span></div>
        <div class="page-footer">PAGE 01</div>
    </div>
</body>

</html>
