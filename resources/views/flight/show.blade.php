@extends('layout.master')
@section('title', 'Flight Details')
@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .cs-page {
            background: #f4f6f9;
        }

        .cs-wrap {
            max-width: 100%;
            margin: 0 auto;
        }

        .cs-card {
            background: #fff;
            border: 1px solid #eef1f5;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, .04);
            margin-bottom: 22px;
            overflow: hidden;
        }

        .cs-card-head {
            padding: 14px 20px;
            border-bottom: 1px solid #f1f3f6;
            font-weight: 600;
            font-size: 14.5px;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            background: #fafbfc;
        }

        .cs-card-head .cs-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cs-card-head .cs-ico {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        .cs-card-body {
            padding: 20px;
        }

        .cs-profile-body {
            padding: 28px;
            text-align: center;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #fff;
        }

        .cs-logo-box {
            width: 110px;
            height: 110px;
            border-radius: 16px;
            background: rgba(255, 255, 255, .08);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0 auto 14px;
            border: 1px dashed rgba(255, 255, 255, .25);
        }

        .cs-logo-box i {
            font-size: 40px;
            color: #cbd5e1;
        }

        .cs-profile-name {
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 4px;
        }

        .cs-profile-meta {
            color: #cbd5e1;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .cs-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .cs-badge.active {
            background: #dcfce7;
            color: #15803d;
        }

        .cs-badge.inactive {
            background: #f1f5f9;
            color: #64748b;
        }

        .cs-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 18px;
        }

        .cs-btn {
            border-radius: 8px;
            font-weight: 500;
            font-size: 13px;
            padding: 8px 18px;
            border: 1px solid rgba(255, 255, 255, .25);
            color: #fff;
            background: rgba(255, 255, 255, .08);
            transition: .2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .cs-btn:hover {
            background: rgba(255, 255, 255, .18);
            color: #fff;
        }

        .cs-btn.warning {
            background: #f59e0b;
            border-color: #f59e0b;
        }

        .cs-btn.warning:hover {
            background: #d97706;
        }

        .cs-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 11px 0;
            border-bottom: 1px solid #f1f3f6;
        }

        .cs-row:last-child {
            border-bottom: none;
        }

        .cs-row label {
            font-size: 12.5px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
            flex: 0 0 auto;
        }

        .cs-row .val {
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
            text-align: right;
            word-break: break-word;
        }

        .cs-row .val.empty {
            color: #cbd5e1;
            font-weight: 400;
        }
    </style>

    <div class="content-page cs-page">
        <div class="content">
            <div class="container-fluid">
                <div class="cs-wrap" style="margin-top: 20px; width: 100%;">

                    {{-- Profile Header Card --}}
                    <div class="cs-card">
                        <div class="cs-profile-body">
                            <div class="cs-profile-name">{{ $flight->name ?? 'No Name' }}</div>
                            <div class="cs-profile-meta">Airline: {{ $flight->airline->name ?? '—' }}</div>
                            <span class="cs-badge {{ $flight->status === 'active' ? 'active' : 'inactive' }}">
                                {{ ucfirst($flight->status) }}
                            </span>
                            <div class="cs-actions">
                                @can('flight_edit')
                                    <a href="{{ route('flight.edit', $flight->id) }}" class="cs-btn warning">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                @endcan
                                <a href="{{ route('flight.index') }}" class="cs-btn">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Details Card --}}
                    <div class="cs-card">
                        <div class="cs-card-head">
                            <span class="cs-title">
                                <span class="cs-ico"><i class="fa fa-info-circle"></i></span>
                                Flight Details
                            </span>
                        </div>
                        <div class="cs-card-body">
                            <div class="cs-row">
                                <label>Airline</label>
                                <div class="val">{{ $flight->airline->name ?? '—' }}</div>
                            </div>
                            <div class="cs-row">
                                <label>Outbound Flight #</label>
                                <div class="val">{{ $flight->outbound_flight_no ?? '—' }}</div>
                            </div>
                            <div class="cs-row">
                                <label>Outbound Departure</label>
                                <div class="val">
                                    {{ $flight->outbound_departure ? $flight->outbound_departure->format('d M Y, h:i A') : '—' }}
                                </div>
                            </div>
                            <div class="cs-row">
                                <label>Outbound Arrival</label>
                                <div class="val">
                                    {{ $flight->outbound_arrival ? $flight->outbound_arrival->format('d M Y, h:i A') : '—' }}
                                </div>
                            </div>
                            <div class="cs-row">
                                <label>Inbound Flight #</label>
                                <div class="val">{{ $flight->inbound_flight_no ?? '—' }}</div>
                            </div>
                            <div class="cs-row">
                                <label>Inbound Departure</label>
                                <div class="val">
                                    {{ $flight->inbound_departure ? $flight->inbound_departure->format('d M Y, h:i A') : '—' }}
                                </div>
                            </div>
                            <div class="cs-row">
                                <label>Inbound Arrival</label>
                                <div class="val">
                                    {{ $flight->inbound_arrival ? $flight->inbound_arrival->format('d M Y, h:i A') : '—' }}
                                </div>
                            </div>
                            <div class="cs-row">
                                <label>Economy Seats Available</label>
                                <div class="val">{{ $flight->economy_seats ?? '0' }}</div>
                            </div>
                            <div class="cs-row">
                                <label>Business Seats Available</label>
                                <div class="val">{{ $flight->business_seats ?? '0' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Sectors Card --}}
                    <div class="cs-card">
                        <div class="cs-card-head">
                            <span class="cs-title">
                                <span class="cs-ico"><i class="fa fa-map-marker-alt"></i></span>
                                Sectors
                            </span>
                        </div>
                        <div class="cs-card-body p-0">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Flight No</th>
                                        <th>Type</th>
                                        <th>Destination</th>
                                        <th>Departure</th>
                                        <th>Arrival</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($flight->sectors as $sector)
                                        <tr>
                                            <td class="ps-3"><strong>{{ $sector->flight_no ?? '—' }}</strong></td>
                                            <td>{{ $sector->type ?? '—' }}</td>
                                            <td>{{ $sector->destination ?? '—' }}</td>
                                            <td>{{ $sector->departure ? $sector->departure->format('d M Y, h:i A') : '—' }}
                                            </td>
                                            <td>{{ $sector->arrival ? $sector->arrival->format('d M Y, h:i A') : '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-3 text-muted">No sectors added.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- PNRs Card --}}
                    <div class="cs-card">
                        <div class="cs-card-head">
                            <span class="cs-title">
                                <span class="cs-ico"><i class="fa fa-ticket-alt"></i></span>
                                PNR details
                            </span>
                        </div>
                        <div class="cs-card-body p-0">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">PNR Type</th>
                                        <th>PNR Name</th>
                                        <th>Capacity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($flight->pnrs as $pnr)
                                        <tr>
                                            <td class="ps-3">
                                                <span
                                                    class="badge {{ $pnr->pnr_type == 'Economy' ? 'bg-primary' : 'bg-success' }}">
                                                    {{ $pnr->pnr_type ?? '—' }}
                                                </span>
                                            </td>
                                            <td><strong>{{ $pnr->pnr_name ?? '—' }}</strong></td>
                                            <td>{{ $pnr->capacity ?? '0' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-3 text-muted">No PNRs added.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
