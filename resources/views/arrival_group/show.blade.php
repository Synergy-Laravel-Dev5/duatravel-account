<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIP Arrival Group — {{ $group->group_name }} | {{ config('app.name', 'Dua Travel') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.2.96/css/materialdesignicons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Outfit', sans-serif; background: #f4f6f9; color: #1c1c1e; padding: 20px; line-height: 1.5; }
        .sheet-box { max-width: 1000px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e2e8f0; }
        .sheet-head { background: linear-gradient(135deg, #10b981 0%, #047857 100%); color: #fff; padding: 24px 32px; display: flex; justify-content: space-between; align-items: center; }
        .sheet-title h1 { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .sheet-title p { font-size: 13px; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; }
        .sheet-logo img { height: 55px; filter: brightness(1.2); }
        .meta-strip { background: #ecfdf5; border-bottom: 2px solid #a7f3d0; padding: 18px 32px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; }
        .meta-card .meta-label { font-size: 10px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 1px; }
        .meta-card .meta-val { font-size: 16px; font-weight: 700; color: #064e3b; margin-top: 2px; }
        .sheet-body { padding: 28px 32px; }
        .section-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #047857; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; }
        .pax-table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13px; }
        .pax-table th { background: #f8fafc; color: #334155; text-align: left; padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #cbd5e1; }
        .pax-table td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .pax-table tr:hover td { background: #f1f5f9; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .badge-dark { background: #1e293b; color: #fff; }
        .badge-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .btn-print { background: #047857; color: #fff; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; text-decoration: none; }
        .btn-print:hover { background: #065f46; }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet-box { box-shadow: none; border: none; width: 100%; max-width: 100%; }
            .no-print { display: none !important; }
            @page { margin: 10mm; }
        }
    </style>
</head>

<body>

    <div class="sheet-box">

        <div class="no-print" style="padding: 14px 32px; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <a href="{{ route('arrival-group.index') }}" style="color: #64748b; text-decoration: none; font-size: 13px; font-weight: 600;">
                &larr; Back to Arrival Groups
            </a>
            <button class="btn-print" onclick="window.print()">
                <i class="mdi mdi-printer"></i> Print VIP Arrival Sheet
            </button>
        </div>

        <div class="sheet-head">
            <div class="sheet-title">
                <p><i class="mdi mdi-airplane-landing me-1"></i> VIP Airport Handling Manifest</p>
                <h1>{{ $group->group_name }}</h1>
            </div>
            <div class="sheet-logo">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
            </div>
        </div>

        <div class="meta-strip">
            <div class="meta-card">
                <div class="meta-label">Airline</div>
                <div class="meta-val">{{ $group->airline->name ?? 'N/A' }}</div>
            </div>
            <div class="meta-card">
                <div class="meta-label">Flight Number</div>
                <div class="meta-val">{{ $group->flight_number ?? 'N/A' }}</div>
            </div>
            <div class="meta-card">
                <div class="meta-label">Arrival Date & Time</div>
                <div class="meta-val">
                    {{ $group->flight_date ? $group->flight_date->format('d M Y') : 'N/A' }}
                    @if($group->flight_time) · {{ $group->flight_time }} @endif
                </div>
            </div>
            <div class="meta-card">
                <div class="meta-label">PNR Number</div>
                <div class="meta-val">{{ $group->pnr ?? 'N/A' }}</div>
            </div>
            <div class="meta-card">
                <div class="meta-label">Sector (From - To)</div>
                <div class="meta-val">{{ $group->departure_city ?? '—' }} &rarr; {{ $group->arrival_city ?? '—' }}</div>
            </div>
            <div class="meta-card">
                <div class="meta-label">Total Pilgrims</div>
                <div class="meta-val">{{ $group->persons->count() }} Pax</div>
            </div>
        </div>

        <div class="sheet-body">

            @if($group->notes)
                <div style="background: #fffbe0; border-left: 4px solid #f59e0b; padding: 10px 14px; margin-bottom: 20px; border-radius: 4px; font-size: 13px;">
                    <strong>Group Notes:</strong> {{ $group->notes }}
                </div>
            @endif

            <div class="section-label">
                <span><i class="mdi mdi-account-group me-1"></i> Passengers Manifest ({{ $group->persons->count() }} Pilgrims)</span>
                <span class="badge badge-success">Arrival Handling</span>
            </div>

            <table class="pax-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Passenger Name</th>
                        <th>CNIC / ID Number</th>
                        <th>Passport Number</th>
                        <th>Contact Phone</th>
                        <th>Booking No & Company</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($group->persons as $index => $person)
                        <tr>
                            <td><strong>{{ $index + 1 }}</strong></td>
                            <td>
                                <strong style="font-size: 14px; color: #0f172a;">{{ $person->full_name }}</strong>
                                @if($person->city) <small style="display: block; color: #64748b;">City: {{ $person->city }}</small> @endif
                            </td>
                            <td>{{ $person->cnic ?? '—' }}</td>
                            <td><strong style="color: #047857;">{{ $person->passport_number ?? '—' }}</strong></td>
                            <td>{{ $person->phone ?? '—' }}</td>
                            <td>
                                <span class="badge badge-dark">{{ $person->booking->booking_number ?? ('BK-' . $person->booking_id) }}</span>
                                <small style="display: block; color: #475569; margin-top: 2px;">
                                    {{ $person->booking->company->name ?? $person->booking->company->company_name ?? $person->booking->client->name ?? 'Company' }}
                                </small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #94a3b8;">
                                No passengers assigned to this Arrival Group yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top: 40px; display: flex; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 20px; font-size: 12px; color: #64748b;">
                <div><strong>Printed Date:</strong> {{ date('d M Y, h:i A') }}</div>
                <div><strong>{{ config('app.name', 'Dua Travel') }} Hajj Management System</strong></div>
            </div>

        </div>

    </div>

</body>

</html>
