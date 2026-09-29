@extends('layout.master')
@section('title', 'Vehicle Details')
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
    </style>
    <div class="content-page cs-page">
        <div class="content">
            <div class="container-fluid">
                <div class="cs-wrap" style="margin-top: 20px; width: 100%;">
                    <div class="cs-card">
                        <div class="cs-profile-body">
                            <div class="cs-actions">
                                @can('vehicle_edit')
                                    <a href="{{ route('vehicle.edit', $vehicle->id) }}" class="cs-btn warning">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                @endcan
                                <a href="{{ route('vehicle.index') }}" class="cs-btn">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="cs-card">
                        <div class="cs-card-head">
                            <span class="cs-title">
                                <span class="cs-ico"><i class="fa fa-info-circle"></i></span>
                                Vehicle Details
                            </span>
                        </div>
                        <div class="cs-card-body">
                            <div class="cs-row">
                                <label>Vehicle Type</label>
                                <div class="val">{{ $vehicle->vehicle_type ?? '—' }}</div>
                            </div>
                            <div class="cs-row">
                                <label>Brand / Model Name</label>
                                <div class="val">{{ $vehicle->brand_name ?? '—' }}</div>
                            </div>
                            <div class="cs-row">
                                <label>Model Year</label>
                                <div class="val">{{ $vehicle->model_year ?? '—' }}</div>
                            </div>
                            <div class="cs-row">
                                <label>Plate Number</label>
                                <div class="val">{{ $vehicle->plate_number ?? '—' }}</div>
                            </div>
                            <div class="cs-row">
                                <label>Supplier Name</label>
                                <div class="val">{{ $vehicle->supplier_name ?? '—' }}</div>
                            </div>
                            <div class="cs-row">
                                <label>Status</label>
                                <div class="val">
                                    <span class="cs-badge {{ $vehicle->status === 'active' ? 'active' : 'inactive' }}">
                                        {{ ucfirst($vehicle->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
