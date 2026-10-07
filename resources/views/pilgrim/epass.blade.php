<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Live Pilgrim E-Pass | {{ $person->full_name ?? ($booking->client->name ?? 'Pilgrim') }}</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Amiri:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.2.96/css/materialdesignicons.min.css">
    
    @php
        $pkgType = strtolower($booking->package_type ?? 'umrah');
        $isHajj = ($pkgType === 'hajj');
        $appName = $isHajj ? 'KGM Travel & Tours' : 'Dua Travels & Tours';
        $agencyPhone = '+923001234567'; // Default agency helpline
        $pilgrimName = $person->full_name ?? ($booking->client->name ?? 'Pilgrim');
        $hajiId = $person->haji_id ?? ($booking->haji_id ?? ('DT-'.str_pad($booking->id, 5, '0', STR_PAD_LEFT)));
        $passport = $person->passport_number ?? ($booking->passport_number ?? '—');
        $roomNo = $person->room_number ?? ($booking->room_number ?? '—');
        $emergencyPhone = $booking->emergency_phone ?? ($booking->phone ?? '—');
        
        $pilgrimPhoto = null;
        if (!empty($person->photo)) {
            $pilgrimPhoto = asset('storage/' . $person->photo);
        } elseif (!empty($person->passport_photo)) {
            $pilgrimPhoto = asset('storage/' . $person->passport_photo);
        } elseif (!empty($booking->photo)) {
            $pilgrimPhoto = asset('storage/' . $booking->photo);
        }
    @endphp

    <style>
        :root {
            --primary: #0f535e;
            --primary-dark: #09343b;
            --primary-light: #e6f3f5;
            --secondary: #c9a84c;
            --secondary-light: #fef8eb;
            --bg-page: #f1f5f9;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            font-size: 13.5px;
            line-height: 1.5;
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .pass-wrapper {
            max-width: 520px;
            margin: 0 auto;
            background: #ffffff;
            min-height: 100vh;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            position: relative;
        }

        /* Top Bar */
        .pass-topbar {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 16px 20px 24px;
            color: #ffffff;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            position: relative;
            overflow: hidden;
        }

        .pass-topbar::after {
            content: "";
            position: absolute;
            right: -20px;
            top: -20px;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, rgba(201,168,76,0.2) 0%, transparent 70%);
            border-radius: 50%;
        }

        .brand-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .brand-title {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .badge-live {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #6ee7b7;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-pulse {
            width: 7px;
            height: 7px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 1.6s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Pilgrim Profile Banner */
        .pilgrim-card {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .pilgrim-avatar {
            width: 76px;
            height: 76px;
            border-radius: 18px;
            background: #ffffff;
            border: 3px solid var(--secondary);
            object-fit: cover;
            box-shadow: 0 6px 15px rgba(0,0,0,0.25);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: var(--primary);
            font-weight: 800;
        }

        .pilgrim-meta h2 {
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 4px;
        }

        .haji-id-tag {
            display: inline-block;
            background: var(--secondary);
            color: #000000;
            font-weight: 800;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 6px;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .pkg-tag {
            font-size: 11.5px;
            opacity: 0.85;
        }

        /* Content Sections */
        .pass-content {
            padding: 16px 18px;
        }

        /* Quick Action Buttons */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: -12px;
            margin-bottom: 20px;
        }

        .action-btn {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 8px;
            text-align: center;
            text-decoration: none;
            color: var(--text-dark);
            box-shadow: 0 3px 8px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            transition: all 0.2s;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
            color: var(--primary);
        }

        .action-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .icon-call { background: #dcfce7; color: #16a34a; }
        .icon-wa { background: #dcfce7; color: #25d366; }
        .icon-print { background: #e0f2fe; color: #0284c7; }

        /* Card Section */
        .section-box {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-title {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 14px;
        }

        .info-cell {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 10.5px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .info-val {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-dark);
            word-break: break-word;
        }

        /* Hotel Card */
        .hotel-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 10px;
            position: relative;
        }

        .hotel-item:last-child {
            margin-bottom: 0;
        }

        .hotel-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .tag-makkah { background: #fef3c7; color: #92400e; }
        .tag-madinah { background: #dcfce7; color: #166534; }

        .btn-maps {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            border-radius: 8px;
            color: #0284c7;
            font-size: 11.5px;
            font-weight: 700;
            text-decoration: none;
            margin-top: 10px;
            transition: all 0.15s;
        }

        .btn-maps:hover {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
        }

        /* Flight Box */
        .flight-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 8px;
        }

        .emergency-banner {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            border-radius: 14px;
            padding: 14px;
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .emergency-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #ef4444;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .pass-footer {
            text-align: center;
            padding: 20px;
            font-size: 11px;
            color: var(--text-muted);
            border-top: 1px solid var(--border);
            margin-top: 20px;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .pass-wrapper { box-shadow: none; max-width: 100%; }
            .action-grid, .btn-maps, .pass-footer button { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="pass-wrapper">
        <!-- Top Banner Header -->
        <div class="pass-topbar">
            <div class="brand-header">
                <div class="brand-title">
                    <i class="mdi mdi-kaaba" style="color: var(--secondary); font-size: 20px;"></i>
                    <span>{{ $appName }}</span>
                </div>
                <div class="badge-live">
                    <span class="badge-pulse"></span>
                    Verified E-Pass
                </div>
            </div>

            <div class="pilgrim-card">
                @if($pilgrimPhoto)
                    <img src="{{ $pilgrimPhoto }}" alt="{{ $pilgrimName }}" class="pilgrim-avatar">
                @else
                    <div class="pilgrim-avatar">
                        {{ strtoupper(substr($pilgrimName, 0, 1)) }}
                    </div>
                @endif
                <div class="pilgrim-meta">
                    <div class="haji-id-tag">
                        <i class="mdi mdi-identifier"></i> {{ $hajiId }}
                    </div>
                    <h2>{{ $pilgrimName }}</h2>
                    <div class="pkg-tag">
                        <i class="mdi mdi-mosque"></i> {{ ucfirst($pkgType) }} Package {{ $booking->package_year ?? date('Y') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="pass-content">
            <!-- Quick Action Grid -->
            <div class="action-grid">
                <a href="tel:{{ $agencyPhone }}" class="action-btn">
                    <div class="action-icon icon-call"><i class="mdi mdi-phone-in-talk"></i></div>
                    <span>Agency Hotline</span>
                </a>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $agencyPhone) }}?text={{ urlencode('Assalam-o-Alaikum, I am Pilgrim '.$pilgrimName.' (ID: '.$hajiId.'). I need assistance.') }}" target="_blank" class="action-btn">
                    <div class="action-icon icon-wa"><i class="mdi mdi-whatsapp"></i></div>
                    <span>WhatsApp Help</span>
                </a>
                <a href="javascript:window.print()" class="action-btn">
                    <div class="action-icon icon-print"><i class="mdi mdi-printer"></i></div>
                    <span>Print E-Pass</span>
                </a>
            </div>

            <!-- 1. Pilgrim Identity & Details -->
            <div class="section-box">
                <div class="section-header">
                    <span class="section-title"><i class="mdi mdi-account-details"></i> Pilgrim Identity</span>
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:4px;font-size:10px;font-weight:700;">
                        Active
                    </span>
                </div>
                <div class="info-grid">
                    <div class="info-cell">
                        <span class="info-label">Full Name</span>
                        <span class="info-val">{{ $pilgrimName }}</span>
                    </div>
                    @if($person && $person->father_name)
                    <div class="info-cell">
                        <span class="info-label">Father / S/O</span>
                        <span class="info-val">{{ $person->father_name }}</span>
                    </div>
                    @endif
                    <div class="info-cell">
                        <span class="info-label">Passport Number</span>
                        <span class="info-val" style="color:var(--primary); font-size:14px;">{{ $passport }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">CNIC / National ID</span>
                        <span class="info-val">{{ $person->cnic ?? ($booking->cnic ?? '—') }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">Blood Group</span>
                        <span class="info-val" style="color:#dc2626;">{{ $person->blood_group ?? '—' }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">Contact Mobile</span>
                        <span class="info-val">
                            @if($person && $person->phone)
                                <a href="tel:{{ $person->phone }}" style="color:var(--text-dark);text-decoration:none;">{{ $person->phone }}</a>
                            @else
                                {{ $booking->phone ?? '—' }}
                            @endif
                        </span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">City / Origin</span>
                        <span class="info-val">{{ $person->city ?? 'Pakistan' }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-label">Room Number</span>
                        <span class="info-val" style="font-weight:800;color:var(--primary);">{{ $roomNo }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. Hotel Accommodations & Google Maps Navigation -->
            <div class="section-box">
                <div class="section-header">
                    <span class="section-title"><i class="mdi mdi-office-building-marker"></i> Hotel Stays & Directions</span>
                    <span style="font-size:11px;color:var(--text-muted);">{{ $booking->hotels->count() }} Location(s)</span>
                </div>

                @if($booking->hotels && $booking->hotels->count() > 0)
                    @foreach($booking->hotels as $hotel)
                        @php
                            $isMakkah = str_contains(strtolower($hotel->location ?? ''), 'makkah') || str_contains(strtolower($hotel->hotel_name ?? ''), 'makkah');
                            $isMadinah = str_contains(strtolower($hotel->location ?? ''), 'madinah') || str_contains(strtolower($hotel->hotel_name ?? ''), 'madinah') || str_contains(strtolower($hotel->location ?? ''), 'medina');
                            $mapQuery = urlencode(($hotel->hotel_name ?? '') . ' ' . ($hotel->location ?? ($isMakkah ? 'Makkah' : 'Madinah')) . ' Saudi Arabia');
                        @endphp
                        <div class="hotel-item">
                            <span class="hotel-tag {{ $isMakkah ? 'tag-makkah' : ($isMadinah ? 'tag-madinah' : 'tag-makkah') }}">
                                <i class="mdi {{ $isMakkah ? 'mdi-kaaba' : 'mdi-mosque' }}"></i>
                                {{ $hotel->location ?: ($isMakkah ? 'Makkah Mukarramah' : ($isMadinah ? 'Madinah Munawwarah' : 'Hotel')) }}
                            </span>
                            
                            <h4 style="font-size: 14.5px; font-weight: 800; margin-bottom: 4px; color: var(--text-dark);">
                                {{ $hotel->hotel_name }}
                            </h4>

                            <div class="info-grid mt-2">
                                <div class="info-cell">
                                    <span class="info-label">Check-in</span>
                                    <span class="info-val fs-12">{{ $hotel->check_in ? date('d M Y', strtotime($hotel->check_in)) : '—' }}</span>
                                </div>
                                <div class="info-cell">
                                    <span class="info-label">Check-out</span>
                                    <span class="info-val fs-12">{{ $hotel->check_out ? date('d M Y', strtotime($hotel->check_out)) : '—' }}</span>
                                </div>
                                <div class="info-cell">
                                    <span class="info-label">Nights & Type</span>
                                    <span class="info-val fs-12">{{ $hotel->no_of_nights ?? 1 }} Nights · {{ ucfirst($hotel->room_type ?? 'Sharing') }}</span>
                                </div>
                                <div class="info-cell">
                                    <span class="info-label">Room #</span>
                                    <span class="info-val fs-12" style="color:var(--primary);font-weight:800;">
                                        {{ $hotel->room_number ?? $roomNo }}
                                    </span>
                                </div>
                            </div>

                            <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank" class="btn-maps">
                                <i class="mdi mdi-map-marker-radius"></i> Open in Google Maps
                            </a>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted fs-12 mb-0">No hotel accommodation details uploaded.</p>
                @endif
            </div>

            <!-- 3. Flight Itinerary -->
            <div class="section-box">
                <div class="section-header">
                    <span class="section-title"><i class="mdi mdi-airplane"></i> Flight Schedule</span>
                </div>

                <!-- Departure -->
                <div class="flight-item">
                    <div style="font-size:11px;font-weight:700;color:var(--primary);margin-bottom:4px;text-transform:uppercase;">
                        <i class="mdi mdi-airplane-takeoff"></i> Departure Flight
                    </div>
                    <div class="info-grid">
                        <div class="info-cell">
                            <span class="info-label">Airline & Flight #</span>
                            <span class="info-val">{{ $booking->departure_airline ?? '—' }} {{ $booking->departure_flight ?? '' }}</span>
                        </div>
                        <div class="info-cell">
                            <span class="info-label">PNR / Ticket</span>
                            <span class="info-val" style="font-weight:800;color:var(--primary);">{{ $booking->departure_pnr ?? '—' }}</span>
                        </div>
                        <div class="info-cell">
                            <span class="info-label">Departure Date & Time</span>
                            <span class="info-val">{{ $booking->departure_date ? date('d M Y', strtotime($booking->departure_date)) : '—' }} {{ $booking->departure_time ? '('.date('h:i A', strtotime($booking->departure_time)).')' : '' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Return / Arrival -->
                <div class="flight-item">
                    <div style="font-size:11px;font-weight:700;color:#0284c7;margin-bottom:4px;text-transform:uppercase;">
                        <i class="mdi mdi-airplane-landing"></i> Return Flight
                    </div>
                    <div class="info-grid">
                        <div class="info-cell">
                            <span class="info-label">Airline & Flight #</span>
                            <span class="info-val">{{ $booking->arrival_airline ?? '—' }} {{ $booking->arrival_flight ?? '' }}</span>
                        </div>
                        <div class="info-cell">
                            <span class="info-label">PNR / Ticket</span>
                            <span class="info-val" style="font-weight:800;color:#0284c7;">{{ $booking->arrival_pnr ?? '—' }}</span>
                        </div>
                        <div class="info-cell">
                            <span class="info-label">Return Date & Time</span>
                            <span class="info-val">{{ $booking->arrival_date ? date('d M Y', strtotime($booking->arrival_date)) : '—' }} {{ $booking->arrival_time ? '('.date('h:i A', strtotime($booking->arrival_time)).')' : '' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Transport Routes -->
            @if($booking->transports && $booking->transports->count() > 0)
            <div class="section-box">
                <div class="section-header">
                    <span class="section-title"><i class="mdi mdi-bus"></i> Transport & Transfers</span>
                </div>
                @foreach($booking->transports as $tr)
                    <div style="background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:6px;font-size:12.5px;">
                        <strong><i class="mdi mdi-routes me-1 text-primary"></i> {{ $tr->route }}</strong>
                        @if($tr->transport_type)<br><small class="text-muted">Type: {{ $tr->transport_type }}</small>@endif
                    </div>
                @endforeach
            </div>
            @endif

            <!-- 5. Emergency Contacts & Nominee -->
            <div class="emergency-banner">
                <div class="emergency-icon">
                    <i class="mdi mdi-phone-alert"></i>
                </div>
                <div>
                    <strong style="color: #991b1b; font-size: 13px;">Emergency Contact & Nominee:</strong>
                    <div style="font-size: 12px; color: #7f1d1d; margin-top: 2px;">
                        @if($person && $person->nominee_name)
                            <strong>{{ $person->nominee_name }}</strong> ({{ $person->nominee_relation ?? 'Nominee' }}): <a href="tel:{{ $person->nominee_mobile }}" style="color:#991b1b;font-weight:700;">{{ $person->nominee_mobile ?? '—' }}</a>
                        @elseif($booking->nominee_name)
                            <strong>{{ $booking->nominee_name }}</strong> ({{ $booking->nominee_relation ?? 'Nominee' }}): <a href="tel:{{ $booking->nominee_mobile }}" style="color:#991b1b;font-weight:700;">{{ $booking->nominee_mobile ?? '—' }}</a>
                        @else
                            Agency 24/7 Helpline: <a href="tel:{{ $emergencyPhone }}" style="color:#991b1b;font-weight:700;">{{ $emergencyPhone }}</a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Agency Disclaimer -->
            <div class="pass-footer">
                <p class="mb-1"><strong>{{ $appName }}</strong> · Official Haji / Umrah E-Pass System</p>
                <p class="mb-0 text-muted">Please keep this digital pass accessible on your mobile phone throughout your blessed journey.</p>
            </div>
        </div>
    </div>

</body>
</html>
