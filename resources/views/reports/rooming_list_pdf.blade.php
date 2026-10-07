<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Rooming List & Manifest Report</title>
    <style>
        @page {
            margin: 12mm 8mm 12mm 8mm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header-box {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #000;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 3px 0;
            color: #000;
        }
        .header-subtitle {
            font-size: 11px;
            font-weight: bold;
            color: #444;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header-meta {
            font-size: 9px;
            color: #555;
        }
        .kpi-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .kpi-table td {
            background-color: #f8fafc;
            border: 1px solid #ccc;
            padding: 4px 8px;
            font-size: 9px;
            text-align: center;
        }
        .kpi-table td strong {
            font-size: 11px;
            color: #000;
        }
        
        /* Official Manifest Table */
        .manifest-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }
        .manifest-table th {
            background-color: #f1f5f9;
            color: #000;
            font-size: 9.5px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 5px 3px;
            border: 1.5px solid #000;
            text-transform: uppercase;
        }
        .manifest-table td {
            padding: 4px 3px;
            vertical-align: middle;
            font-size: 9px;
            border: 1px solid #000;
        }
        .room-divider-top td {
            border-top: 2px solid #000 !important;
        }
        .room-typ-cell {
            font-weight: bold;
            font-size: 10px;
            text-align: center;
            text-transform: uppercase;
            border-left: 1.5px solid #000 !important;
            border-right: 1px solid #000 !important;
            background-color: #fff;
        }
        .room-no-cell {
            font-weight: 900;
            font-size: 14px;
            color: #c00000;
            text-align: center;
            border-left: 1px solid #000 !important;
            border-right: 1.5px solid #000 !important;
            background-color: #fff;
            letter-spacing: 0.5px;
        }
        .gender-male {
            color: #1e3a8a;
            font-weight: bold;
            text-align: center;
        }
        .gender-female {
            color: #9d174d;
            font-weight: bold;
            text-align: center;
        }
        .haji-photo {
            width: 38px;
            height: 44px;
            object-fit: cover;
            border-radius: 2px;
            border: 0.5px solid #666;
            display: block;
            margin: 0 auto;
        }
        .photo-placeholder {
            width: 36px;
            height: 42px;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            display: block;
            margin: 0 auto;
            line-height: 42px;
            text-align: center;
            color: #999;
            font-size: 8px;
        }
        tr {
            page-break-inside: avoid;
        }
        .page-num:after {
            content: counter(page);
        }
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            font-size: 8px;
            color: #777;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    <div class="header-box">
        <div class="header-title">Dua Travel & Tourism</div>
        <div class="header-subtitle">Official Rooming List & Manifest Report</div>
        <div class="header-meta">
            Generated on: <strong>{{ date('d M Y, h:i A') }}</strong>
            @if(!empty($hotelFilter)) | Hotel: <strong>{{ $hotelFilter }}</strong> @endif
            @if(!empty($locationFilter) && $locationFilter !== 'all') | Location: <strong>{{ strtoupper($locationFilter) }}</strong> @endif
            @if(!empty($roomTypeFilter) && $roomTypeFilter !== 'all') | Room Type: <strong>{{ strtoupper($roomTypeFilter) }}</strong> @endif
            @if(!empty($fromDate) || !empty($toDate)) | Dates: <strong>{{ $fromDate ?: 'Start' }} to {{ $toDate ?: 'End' }}</strong> @endif
        </div>
    </div>

    {{-- KPI Summary Bar --}}
    <table class="kpi-table">
        <tr>
            <td>Total Allocated Rooms: <strong>{{ number_format($totalRoomsInUse) }}</strong></td>
            <td>Total Pilgrims (Pax): <strong>{{ number_format($totalBedsOccupied) }}</strong></td>
            <td>Total Bed Capacity: <strong>{{ number_format($totalBedsCapacity) }}</strong></td>
            <td>Free / Available Beds: <strong>{{ number_format($totalBedsAvailable) }}</strong></td>
        </tr>
    </table>

    {{-- Official Manifest Table matching User PDF --}}
    <table class="manifest-table">
        <thead>
            <tr>
                <th style="width: 25px;">SR</th>
                <th style="width: 65px;">HAJJ ID</th>
                <th style="width: 55px;">HB</th>
                <th style="width: 75px;">PASSPORT</th>
                <th style="text-align: left; padding-left: 5px;">FULL NAME</th>
                <th style="width: 65px;">ROOM TYP</th>
                <th style="width: 65px;">ROOM NO</th>
                <th style="width: 50px;">Gender</th>
                <th style="width: 45px;">Picture</th>
            </tr>
        </thead>
        <tbody>
            @php $globalSr = 1; @endphp
            @forelse($groupedRooms as $room)
                @php
                    $occupants = $room['occupants'];
                    $totalOccupants = count($occupants);
                    $rowspan = max(1, $totalOccupants);
                    
                    $rawNum = trim($room['room_number']);
                    $isUnassigned = ($rawNum === 'PENDING' || $rawNum === 'UNASSIGNED' || empty($rawNum));
                    $roomDisplayNo = $isUnassigned ? 'PENDING' : (str_starts_with(strtoupper($rawNum), 'R') ? strtoupper($rawNum) : ('R' . $rawNum));
                    
                    $roomTypeDisplay = strtoupper($room['room_type']);
                    if ($isUnassigned) {
                        $roomTypeDisplay = 'PENDING ALLOCATION';
                    } elseif (in_array(strtolower($roomTypeDisplay), ['sharing', '6', 'six-bed', '6-bed'])) {
                        $roomTypeDisplay = 'SIX';
                    }

                    $loc = strtolower(trim($room['location'] ?? 'makkah'));
                    $locLabel = match($loc) {
                        'makkah'  => 'MAKKAH',
                        'azizia'  => 'AZIZIA',
                        'mina'    => 'MINA',
                        'arafat'  => 'ARAFAT',
                        'madinah' => 'MADINAH',
                        'unassigned' => 'UNASSIGNED',
                        default   => strtoupper($loc),
                    };
                @endphp

                @if($totalOccupants === 0)
                    <tr class="room-divider-top">
                        <td style="text-align: center; color: #777;">{{ $globalSr++ }}</td>
                        <td style="text-align: center; color: #777;">—</td>
                        <td style="text-align: center; color: #777;">—</td>
                        <td style="text-align: center; color: #777;">—</td>
                        <td style="color: #777; font-style: italic;">(Empty Room - {{ $room['total_capacity'] }} Beds Available)</td>
                        <td class="room-typ-cell">
                            <div style="font-size: 7.5px; color: #555;">{{ $locLabel }}</div>
                            <div>{{ $roomTypeDisplay }}</div>
                        </td>
                        <td class="room-no-cell">{{ $roomDisplayNo }}</td>
                        <td style="text-align: center; color: #777;">—</td>
                        <td style="text-align: center; color: #777;">—</td>
                    </tr>
                @else
                    @foreach($occupants as $oIdx => $occ)
                        @php
                            $photoBase64 = null;
                            if (!empty($occ['photo']) && file_exists(public_path($occ['photo']))) {
                                $ext = pathinfo(public_path($occ['photo']), PATHINFO_EXTENSION);
                                $imgData = @file_get_contents(public_path($occ['photo']));
                                if ($imgData) {
                                    $photoBase64 = 'data:image/' . ($ext ?: 'jpeg') . ';base64,' . base64_encode($imgData);
                                }
                            }
                            $isFemale = strtoupper($occ['gender'] ?? 'MALE') === 'FEMALE';
                        @endphp
                        <tr class="{{ $oIdx === 0 ? 'room-divider-top' : '' }}">
                            <td style="text-align: center; font-weight: bold;">{{ $globalSr++ }}</td>
                            <td style="text-align: center; font-weight: bold; text-transform: uppercase;">{{ !empty($occ['hajj_id']) ? $occ['hajj_id'] : '—' }}</td>
                            <td style="text-align: center; font-weight: bold; text-transform: uppercase;">{{ !empty($occ['hb_number']) ? $occ['hb_number'] : '—' }}</td>
                            <td style="text-align: center; font-weight: bold; text-transform: uppercase;">{{ $occ['passport'] }}</td>
                            <td style="text-align: left; padding-left: 5px; font-weight: bold; text-transform: uppercase; color: #000;">
                                <div>{{ $occ['name'] }}</div>
                                <div style="font-size: 7.5px; color: #0d6efd; font-weight: bold; margin-top: 1px;">
                                    Booked: {{ strtoupper($occ['booked_room_type'] ?? 'Quad') }}
                                </div>
                                <div style="font-size: 7px; color: #666; font-weight: normal;">
                                    {{ $occ['client_name'] }} ({{ $occ['booking_number'] }})
                                </div>
                            </td>

                            @if($oIdx === 0)
                                <td rowspan="{{ $rowspan }}" class="room-typ-cell">
                                    <div style="font-size: 7.5px; color: #444; font-weight: bold; margin-bottom: 2px;">{{ $locLabel }}</div>
                                    <div style="font-size: 10px; font-weight: bold;">{{ $roomTypeDisplay }}</div>
                                    <div style="font-size: 7.5px; color: #666;">({{ $room['total_capacity'] }} Beds)</div>
                                </td>
                                <td rowspan="{{ $rowspan }}" class="room-no-cell">
                                    <div>{{ $roomDisplayNo }}</div>
                                    <div style="font-size: 7.5px; color: #666; font-weight: normal;">{{ $room['occupied_beds'] }}/{{ $room['total_capacity'] }}</div>
                                </td>
                            @endif

                            <td class="{{ $isFemale ? 'gender-female' : 'gender-male' }}" style="font-size: 8.5px; text-transform: uppercase;">
                                {{ strtoupper($occ['gender']) }}
                            </td>

                            <td style="text-align: center; padding: 2px;">
                                @if($photoBase64)
                                    <img src="{{ $photoBase64 }}" class="haji-photo" alt="Photo">
                                @else
                                    <div class="photo-placeholder">—</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 25px; color: #777;">
                        No room allocation records found matching the criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
