<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Pilgrim Badges — Booking #{{ $booking->voucher_number ?? $booking->id }} ({{ $booking->persons->count() }} Badges)</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.2.96/css/materialdesignicons.min.css">

    @php
        $pkgType = strtolower($booking->package_type ?? 'umrah');
        $isHajj = ($pkgType === 'hajj');
        $appName = $isHajj ? 'KGM TRAVEL & TOURS' : 'DUA TRAVELS & TOURS';
        $agencyPhone = '+92 300 1234567';
    @endphp

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 20px;
        }

        .no-print-bar {
            max-width: 1000px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .btn-print {
            background: #0f535e;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-print:hover {
            background: #09343b;
        }

        .btn-secondary {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background: #f8fafc;
        }

        /* ── Sheet Container (A4 2-Column Grid) ── */
        .badges-grid {
            max-width: 190mm;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 86mm);
            gap: 12mm 14mm;
            justify-content: center;
        }

        /* ── Standard Lanyard Badge Dimensions (86mm x 126mm) ── */
        .badge-card {
            width: 86mm;
            height: 126mm;
            background: #ffffff;
            border-radius: 10px;
            border: 1px dashed #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        /* Lanyard Hole Guide */
        .lanyard-hole {
            width: 14mm;
            height: 3.5mm;
            border: 1px dashed #94a3b8;
            border-radius: 4px;
            position: absolute;
            top: 2.5mm;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            background: rgba(255,255,255,0.4);
        }

        /* Header */
        .badge-header {
            background: linear-gradient(135deg, #09343b 0%, #0f535e 100%);
            color: #ffffff;
            text-align: center;
            padding: 8mm 3mm 3mm;
            position: relative;
        }

        .badge-header::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2.5px;
            background: linear-gradient(90deg, #c9a84c, #fef08a, #c9a84c);
        }

        .badge-brand-title {
            font-size: 10.5px;
            font-weight: 900;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .badge-sub-title {
            font-size: 8px;
            font-weight: 700;
            color: #fde047;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-top: 1px;
        }

        /* Body Section */
        .badge-body {
            padding: 3mm 4mm;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Profile Row */
        .profile-row {
            display: flex;
            gap: 3mm;
            align-items: center;
            margin-bottom: 2mm;
        }

        .photo-box {
            width: 25mm;
            height: 30mm;
            border-radius: 6px;
            border: 1.5px solid #0f535e;
            object-fit: cover;
            background: #e2e8f0;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            color: #0f535e;
        }

        .profile-meta {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .haji-pill {
            background: #0f535e;
            color: #ffffff;
            font-size: 8px;
            font-weight: 800;
            padding: 1.5px 5px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 2px;
            align-self: flex-start;
        }

        .pax-name {
            font-size: 11.5px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.15;
            margin-bottom: 2px;
        }

        .pax-kv {
            font-size: 8px;
            color: #475569;
            line-height: 1.35;
        }

        .pax-kv strong {
            color: #0f172a;
        }

        /* Hotel Box */
        .hotel-summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 2mm;
            margin-bottom: 2mm;
        }

        .hotel-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8px;
            padding: 1px 0;
            border-bottom: 1px dashed #e2e8f0;
        }

        .hotel-row:last-child {
            border-bottom: none;
        }

        .hotel-name-trunc {
            max-width: 48mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 700;
            color: #0f172a;
        }

        .room-badge {
            background: #e6f3f5;
            color: #0f535e;
            font-weight: 800;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            white-space: nowrap;
        }

        /* QR + Emergency Row */
        .qr-emergency-row {
            display: flex;
            gap: 2mm;
            align-items: center;
            background: #fdfdfd;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 1.5mm 2mm;
        }

        .qr-img {
            width: 16mm;
            height: 16mm;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            flex-shrink: 0;
        }

        .qr-text-col {
            flex: 1;
            font-size: 7.5px;
            color: #475569;
            line-height: 1.25;
        }

        .qr-text-col strong {
            font-size: 8px;
            color: #0f535e;
            display: block;
            margin-bottom: 1px;
            text-transform: uppercase;
        }

        .emergency-pill {
            background: #fee2e2;
            color: #b91c1c;
            font-weight: 800;
            padding: 1px 4px;
            border-radius: 3px;
            display: inline-block;
            margin-top: 1.5px;
            font-size: 7px;
        }

        /* Footer */
        .badge-footer {
            background: #09343b;
            color: #cbd5e1;
            text-align: center;
            padding: 1.5mm 3mm;
            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        @media print {
            body {
                background: none;
                padding: 5mm;
                margin: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .badges-grid {
                max-width: 100%;
                gap: 8mm 10mm;
            }
            .badge-card {
                box-shadow: none;
                border: 1px dashed #94a3b8;
                page-break-inside: avoid;
            }
            @page {
                size: A4 portrait;
                margin: 10mm 8mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong style="font-size: 15px; color: #0f535e;">
                <i class="mdi mdi-account-multiple-check"></i> Bulk Badges Sheet
            </strong>
            <div style="font-size: 12px; color: #64748b;">
                Voucher #: {{ $booking->voucher_number ?? $booking->id }} · {{ $booking->client->name ?? 'Client' }} ({{ $booking->persons->count() }} Badges)
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn-print" onclick="window.print()">
                <i class="mdi mdi-printer"></i> Print All Badges (A4 Sheet)
            </button>
            <a href="{{ route('booking.show', $booking->id) }}" class="btn-print btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Back to Booking
            </a>
        </div>
    </div>

    <!-- ── 2x2 Printable Badges Grid ── -->
    <div class="badges-grid">
        @foreach($booking->persons as $person)
            @php
                $hajiId = $person->haji_id ?? ($booking->haji_id ?? ('DT-'.str_pad($booking->id, 5, '0', STR_PAD_LEFT)));
                $epassUrl = url('/pilgrim/e-pass/' . $person->id);
                $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($epassUrl);
                
                $pilgrimPhoto = null;
                if (!empty($person->photo)) {
                    $pilgrimPhoto = asset('storage/' . $person->photo);
                } elseif (!empty($person->passport_photo)) {
                    $pilgrimPhoto = asset('storage/' . $person->passport_photo);
                } elseif (!empty($booking->photo)) {
                    $pilgrimPhoto = asset('storage/' . $booking->photo);
                }
            @endphp
            <div class="badge-card">
                <!-- Hole Punch Guide -->
                <div class="lanyard-hole"></div>

                <!-- Header -->
                <div class="badge-header">
                    <div class="badge-brand-title">{{ $appName }}</div>
                    <div class="badge-sub-title">
                        <i class="mdi mdi-kaaba"></i> {{ strtoupper($pkgType) }} PILGRIM PASS · {{ $booking->package_year ?? date('Y') }}
                    </div>
                </div>

                <!-- Body -->
                <div class="badge-body">
                    <!-- Profile Row -->
                    <div class="profile-row">
                        @if($pilgrimPhoto)
                            <img src="{{ $pilgrimPhoto }}" alt="{{ $person->full_name }}" class="photo-box">
                        @else
                            <div class="photo-box">
                                {{ strtoupper(substr($person->full_name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="profile-meta">
                            <div class="haji-pill">
                                HAJI ID: {{ $hajiId }}
                            </div>
                            <div class="pax-name">{{ $person->full_name }}</div>
                            @if($person->father_name)
                                <div class="pax-kv">S/O: <strong>{{ $person->father_name }}</strong></div>
                            @endif
                            <div class="pax-kv">Pass: <strong>{{ $person->passport_number ?? ($booking->passport_number ?? '—') }}</strong></div>
                            <div class="pax-kv">Blood: <strong style="color:#dc2626;">{{ $person->blood_group ?? '—' }}</strong> · {{ ucfirst($person->gender ?? 'Male') }}</div>
                            <div class="pax-kv">City: <strong>{{ $person->city ?? 'Pakistan' }}</strong></div>
                        </div>
                    </div>

                    <!-- Accommodation Box -->
                    <div class="hotel-summary-box">
                        <div class="hotel-row">
                            <span style="color:#92400e; font-weight:800;"><i class="mdi mdi-kaaba"></i> MAKKAH:</span>
                            <span class="hotel-name-trunc">{{ $makkahHotel->hotel_name ?? 'Designated Hotel' }}</span>
                            <span class="room-badge">Room: {{ $makkahHotel->room_number ?? ($person->room_number ?? ($booking->room_number ?? '—')) }}</span>
                        </div>
                        @if($madinahHotel)
                        <div class="hotel-row">
                            <span style="color:#166534; font-weight:800;"><i class="mdi mdi-mosque"></i> MADINAH:</span>
                            <span class="hotel-name-trunc">{{ $madinahHotel->hotel_name ?? 'Designated Hotel' }}</span>
                            <span class="room-badge">Room: {{ $madinahHotel->room_number ?? ($person->room_number ?? ($booking->room_number ?? '—')) }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- QR Code & Emergency Help -->
                    <div class="qr-emergency-row">
                        <img src="{{ $qrCodeUrl }}" alt="Scan E-Pass" class="qr-img">
                        <div class="qr-text-col">
                            <strong>Scan for Live Pass</strong>
                            <div>Live Itinerary & Hotel Navigation</div>
                            <div class="emergency-pill">
                                <i class="mdi mdi-phone-alert"></i> {{ $booking->emergency_phone ?? $agencyPhone }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="badge-footer">
                    24/7 SUPPORT · VOUCHER #{{ $booking->voucher_number ?? $booking->id }}
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
