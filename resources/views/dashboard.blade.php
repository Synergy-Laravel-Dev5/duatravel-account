@extends('layout.master')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');

    :root {
        --gold: #0f535e;
        --gold-light: #187280;
        --gold-subtle: #e6f3f5;
        --emerald: #0f535e;
        --emerald-light: #187280;
        --emerald-subtle: #e6f3f5;
        --navy: #0f535e;
        --navy-light: #187280;
        --cream: #FDFAF4;
        --text-dark: #1C1C1E;
        --text-muted: #6B7280;
        --border: #EDE8DC;
        --card-shadow: 0 2px 16px rgba(15, 83, 94, .08);
        --card-shadow-hover: 0 8px 32px rgba(15, 83, 94, .14);

        /* Dynamic accent based on package */
        --accent: #0f535e;
        --accent-light: #187280;
        --accent-subtle: #e6f3f5;
    }

    body {
        font-family: 'DM Sans', sans-serif;
        background-color: var(--cream) !important;
    }

    .page-title-font {
        font-family: 'Playfair Display', serif;
        color: var(--navy);
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    .page-subtitle {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 400;
    }

    /* ── Package Toggle Tabs ── */
    .pkg-tabs {
        display: inline-flex;
        background: #F0EBE0;
        border-radius: 12px;
        padding: 4px;
        gap: 4px;
    }

    .pkg-tab {
        padding: 6px 18px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: var(--text-muted);
        transition: all .2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .pkg-tab.active-hajj {
        background: linear-gradient(135deg, #0f535e, #187280);
        color: #fff;
        box-shadow: 0 2px 8px rgba(15, 83, 94, .35);
    }

    .pkg-tab.active-umrah {
        background: linear-gradient(135deg, #D84816, #e65c2b);
        color: #fff;
        box-shadow: 0 2px 8px rgba(216, 72, 22, .30);
    }

    /* ── Year Dropdown ── */
    .year-select {
        font-size: 13px;
        font-weight: 600;
        color: var(--navy);
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 6px 14px;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%236B7280' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 30px;
        outline: none;
    }

    .year-select:focus {
        border-color: var(--accent);
    }

    /* ── Stat Cards ── */
    .stat-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: var(--card-shadow);
        padding: 20px;
        transition: transform .25s ease, box-shadow .25s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        border-radius: 16px 16px 0 0;
    }

    .stat-card.gold::before {
        background: linear-gradient(90deg, #0f535e, #187280);
    }

    .stat-card.emerald::before {
        background: linear-gradient(90deg, #D84816, #e65c2b);
    }

    .stat-card.navy::before {
        background: linear-gradient(90deg, #0f535e, #187280);
    }

    .stat-card.teal::before {
        background: linear-gradient(90deg, #D84816, #e65c2b);
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-shadow-hover);
    }

    .stat-icon-wrap {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-wrap.gold {
        background: linear-gradient(135deg, #0f535e, #187280);
    }

    .stat-icon-wrap.emerald {
        background: linear-gradient(135deg, #D84816, #e65c2b);
    }

    .stat-icon-wrap.navy {
        background: linear-gradient(135deg, #0f535e, #187280);
    }

    .stat-icon-wrap.teal {
        background: linear-gradient(135deg, #D84816, #e65c2b);
    }

    .stat-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 6px;
    }

    .stat-value {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-variant-numeric: tabular-nums;
        font-size: 26px;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1;
        margin-bottom: 8px;
    }

    .badge-trend {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 20px;
    }

    .badge-trend.up {
        background: #EDFBF4;
        color: #1A6B4A;
    }

    .badge-trend.down {
        background: #FEF0F0;
        color: #D94040;
    }

    /* ── Panel Cards ── */
    .panel-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: var(--card-shadow);
    }

    .panel-card .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
    }

    .panel-card .panel-header h5 {
        font-family: 'Playfair Display', serif;
        font-size: 15px;
        font-weight: 600;
        color: var(--navy);
        margin: 0;
    }

    .panel-card .panel-body {
        padding: 18px 20px;
    }

    .panel-card .panel-body.p-0 {
        padding: 0;
    }

    /* ── Table ── */
    .bookings-table th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border) !important;
        padding: 10px 14px;
        background: #FAFAF8;
    }

    .bookings-table td {
        font-size: 13px;
        color: var(--text-dark);
        padding: 11px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #F4F0E8 !important;
    }

    .bookings-table tr:last-child td {
        border-bottom: none !important;
    }

    .bookings-table tr:hover td {
        background: var(--gold-subtle);
    }

    .pkg-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 20px;
        letter-spacing: .3px;
    }

    .pkg-badge.hajj {
        background: #FBF5E6;
        color: #9A6F1A;
        border: 1px solid #E8D4A0;
    }

    .pkg-badge.umrah {
        background: #E8F5EE;
        color: #1A6B4A;
        border: 1px solid #A8D5BC;
    }

    .pkg-badge.other {
        background: #EEF2FF;
        color: #3D5CCC;
        border: 1px solid #BCC8F5;
    }

    .status-dot {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-dot::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .status-dot.confirmed::before {
        background: #1A6B4A;
    }

    .status-dot.pending::before {
        background: #C9A84C;
    }

    .status-dot.cancelled::before {
        background: #D94040;
    }

    /* ── Booking-for tag (client / company) ── */
    .for-tag {
        display: inline-block;
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .3px;
        padding: 1px 6px;
        border-radius: 8px;
        margin-left: 4px;
        vertical-align: middle;
    }

    .for-tag.company {
        background: #EEF2FF;
        color: #3D5CCC;
    }

    .for-tag.client {
        background: #FFF8E8;
        color: #9A6F1A;
    }

    /* ── Distribution Bars ── */
    .dest-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #F4F0E8;
    }

    .dest-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .dest-item:first-child {
        padding-top: 0;
    }

    .dest-flag {
        font-size: 20px;
        width: 32px;
        text-align: center;
    }

    .dest-bar-bg {
        flex: 1;
        height: 5px;
        background: #EDE8DC;
        border-radius: 4px;
        overflow: hidden;
    }

    .dest-bar-fill {
        height: 100%;
        border-radius: 4px;
        background: linear-gradient(90deg, var(--gold), var(--gold-light));
    }

    /* ── Quick Stats ── */
    .qs-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 0;
        border-bottom: 1px solid #F4F0E8;
    }

    .qs-row:first-child {
        padding-top: 0;
    }

    .qs-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .qs-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .qs-label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .qs-value {
        font-size: 14.5px;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.1;
    }

    /* ── Animations ── */
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .anim {
        animation: fadeUp .45s ease both;
    }

    .anim-1 {
        animation-delay: .05s;
    }

    .anim-2 {
        animation-delay: .10s;
    }

    .anim-3 {
        animation-delay: .15s;
    }

    .anim-4 {
        animation-delay: .20s;
    }

    .anim-9 {
        animation-delay: .45s;
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            {{-- ════ Page Header ════ --}}
            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column gap-3 mb-2 flex-wrap">

                {{-- Title --}}
                <div class="flex-grow-1">
                    <h4 class="page-title-font mb-1 d-flex align-items-center gap-2">
                        @if ($package === 'hajj')
                            <svg width="28" height="28" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <ellipse cx="32" cy="56" rx="20" ry="4.5" fill="#000000" fill-opacity="0.25" />
                                <polygon points="16,18 32,10 48,18 32,26" fill="#475569" />
                                <polygon points="16,18 32,26 32,50 16,42" fill="#1E293B" />
                                <polygon points="32,26 48,18 48,42 32,50" fill="#0F172A" />
                                <polygon points="16,23 32,31 32,34.5 16,26.5" fill="#F59E0B" />
                                <polygon points="32,31 48,23 48,26.5 32,34.5" fill="#F59E0B" />
                                <polygon points="36,36 41,33.5 41,45.5 36,48" fill="#F59E0B" stroke="#B45309" stroke-width="0.5" />
                            </svg>
                        @else
                            <svg width="28" height="28" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <ellipse cx="32" cy="56" rx="22" ry="4.5" fill="#000000" fill-opacity="0.2" />
                                <path d="M12 40H52V52H12V40Z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="0.5" />
                                <path d="M20 40H44V34H20V40Z" fill="#FFFFFF" stroke="#94A3B8" stroke-width="0.5" />
                                <path d="M20 34C20 22 32 16 32 16C32 16 44 22 44 34H20Z" fill="#10B981" />
                                <path d="M32 9V16" stroke="#F59E0B" stroke-width="1.8" stroke-linecap="round" />
                                <circle cx="32" cy="12" r="1.5" fill="#F59E0B" />
                                <path d="M46 52H50V24H46V52Z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="0.5" />
                                <path d="M47 21C47 17 48 15 48 15C48 15 49 17 49 21H47Z" fill="#10B981" />
                            </svg>
                        @endif
                        <span>{{ ucfirst($package) }} Dashboard</span>
                    </h4>
                    <p class="page-subtitle mb-0">{{ $package === 'hajj' ? 'KGM' : 'Dua Travels & Tours' }} &mdash; {{ $year }}</p>
                </div>

                {{-- Package Toggle --}}
                <div class="pkg-tabs">
                    <a href="{{ route('dashboard', ['package' => 'hajj', 'year' => $year]) }}"
                        class="pkg-tab {{ $package === 'hajj' ? 'active-hajj' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 64 64" fill="none" style="vertical-align:-3px; margin-right:4px;">
                            <polygon points="16,18 32,10 48,18 32,26" fill="#94A3B8" />
                            <polygon points="16,18 32,26 32,50 16,42" fill="#1E293B" />
                            <polygon points="32,26 48,18 48,42 32,50" fill="#0F172A" />
                            <polygon points="16,23 32,31 32,34.5 16,26.5" fill="#F59E0B" />
                            <polygon points="32,31 48,23 48,26.5 32,34.5" fill="#F59E0B" />
                            <polygon points="36,36 41,33.5 41,45.5 36,48" fill="#F59E0B" stroke="#B45309" stroke-width="0.5" />
                        </svg>
                        Hajj
                    </a>
                    <a href="{{ route('dashboard', ['package' => 'umrah', 'year' => $year]) }}"
                        class="pkg-tab {{ $package === 'umrah' ? 'active-umrah' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 64 64" fill="none" style="vertical-align:-3px; margin-right:4px;">
                            <path d="M12 40H52V52H12V40Z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="0.5" />
                            <path d="M20 40H44V34H20V40Z" fill="#FFFFFF" stroke="#94A3B8" stroke-width="0.5" />
                            <path d="M20 34C20 22 32 16 32 16C32 16 44 22 44 34H20Z" fill="#10B981" />
                            <path d="M32 9V16" stroke="#F59E0B" stroke-width="1.8" stroke-linecap="round" />
                            <circle cx="32" cy="12" r="1.5" fill="#F59E0B" />
                            <path d="M46 52H50V24H46V52Z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="0.5" />
                            <path d="M47 21C47 17 48 15 48 15C48 15 49 17 49 21H47Z" fill="#10B981" />
                        </svg>
                        Umrah
                    </a>
                </div>

                {{-- Year Dropdown --}}
                <form method="GET" action="{{ route('dashboard') }}" id="yearForm">
                    <input type="hidden" name="package" value="{{ $package }}">
                    <select name="year" class="year-select" onchange="document.getElementById('yearForm').submit()">
                        @foreach ($availableYears as $yr)
                            <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>
                                {{ $yr }}</option>
                        @endforeach
                    </select>
                </form>

                {{-- Date Badge --}}
                <span class="badge rounded-pill px-3 py-2"
                    style="background:var(--gold-subtle);color:var(--gold);border:1px solid #E0C97A;font-size:12px;font-weight:600;">
                    <i class="mdi mdi-calendar-month me-1"></i> {{ now()->format('d M Y') }}
                </span>

                {{-- Back to Welcome --}}
                <a href="{{ route('welcome') }}"
                    style="font-size:12px;font-weight:600;color:var(--text-muted);text-decoration:none;">
                    <i class="mdi mdi-arrow-left me-1"></i> Switch Package
                </a>
            </div>

            {{-- ════ ROW 1 — FINANCIAL PERFORMANCE (REVENUE, DIRECT COSTS, OVERHEADS, PROFIT) ════ --}}
            <div class="row g-3 mb-3">

                {{-- Total Revenue --}}
                <div class="col-6 col-xl-3 col-md-6 anim anim-1">
                    <div class="stat-card emerald">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon-wrap emerald">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                    <path fill="#fff" d="M7 15h2c0 1.08 1.37 2 3 2s3-.92 3-2c0-1.1-1.04-1.5-3.24-2.03C9.64 12.44 7 11.78 7 9c0-1.79 1.47-3.31 3.5-3.82V3h3v2.18C15.53 5.69 17 7.21 17 9h-2c0-1.08-1.37-2-3-2s-3 .92-3 2c0 1.1 1.04 1.5 3.24 2.03C14.36 11.56 17 12.22 17 15c0 1.79-1.47 3.31-3.5 3.82V21h-3v-2.18C8.47 18.31 7 16.79 7 15" />
                                </svg>
                            </div>
                            <p class="stat-label mb-0">{{ ucfirst($package) }} Revenue</p>
                        </div>
                        <div class="stat-value">{{ number_format($totalRevenue) }}</div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                            <span class="badge-trend {{ $revenueTrend >= 0 ? 'up' : 'down' }}">
                                <i class="mdi mdi-trending-{{ $revenueTrend >= 0 ? 'up' : 'down' }}"></i>
                                {{ abs($revenueTrend) }}%
                            </span>
                            <span style="font-size:11.5px;color:var(--text-muted);">Last 7 days</span>
                        </div>
                    </div>
                </div>

                {{-- Direct Booking Costs --}}
                <div class="col-6 col-xl-3 col-md-6 anim anim-2">
                    <div class="stat-card gold">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon-wrap gold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                    <path fill="#fff" d="M19 7h-8v6h8V7m2-2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2m-2 2H5v8h14V7M9 9H7v2h2V9z" />
                                </svg>
                            </div>
                            <p class="stat-label mb-0">Direct Costs</p>
                        </div>
                        <div class="stat-value">{{ number_format($totalDirectCosts) }}</div>
                        <div class="text-truncate" style="font-size:11.5px;color:var(--text-muted);" title="Hotels, Flights, Visas & Transport">
                            Hotels, Flights, Visas & Trans
                        </div>
                    </div>
                </div>

                {{-- Overhead Expenses --}}
                <div class="col-6 col-xl-3 col-md-6 anim anim-3">
                    <div class="stat-card" style="border-color:var(--border);">
                        <div style="position:absolute;top:0;left:0;right:0;height:3px;border-radius:16px 16px 0 0;background:linear-gradient(90deg,#0f535e,#187280);"></div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#0f535e,#187280);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                    <path fill="#fff" d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2m7 3a1 1 0 0 0-1 1v.27A3 3 0 0 0 9 10a3 3 0 0 0 3 3a1 1 0 0 1 0 2a1 1 0 0 1-1-1H9a3 3 0 0 0 2 2.72V17a1 1 0 0 0 2 0v-.27A3 3 0 0 0 15 14a3 3 0 0 0-3-3a1 1 0 0 1 0-2a1 1 0 0 1 1 1h2a3 3 0 0 0-2-2.72V6a1 1 0 0 0-1-1z" />
                                </svg>
                            </div>
                            <p class="stat-label mb-0">Overhead Expenses</p>
                        </div>
                        <div class="stat-value">{{ number_format($totalOverheadExpenses) }}</div>
                        <div style="font-size:11.5px;color:var(--text-muted);">
                            PKR {{ number_format($totalExpensesPkr) }} @if($totalExpensesSar > 0) | SAR {{ number_format($totalExpensesSar) }} @endif
                        </div>
                    </div>
                </div>

                {{-- Profit / Loss --}}
                <div class="col-6 col-xl-3 col-md-6 anim anim-4">
                    <div class="stat-card" style="border-color:var(--border);">
                        <div style="position:absolute;top:0;left:0;right:0;height:3px;border-radius:16px 16px 0 0;background:linear-gradient(90deg,{{ $isProfit ? '#10B981,#059669' : '#EF4444,#DC2626' }});"></div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon-wrap" style="background:linear-gradient(135deg,{{ $isProfit ? '#0f535e,#187280' : '#D94040,#b91c1c' }});">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                    <path fill="#fff" d="{{ $isProfit ? 'M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z' : 'M16 18l2.29-2.29-4.88-4.88-4 4L2 7.41 3.41 6l6 6 4-4 6.3 6.29L22 12v6z' }}" />
                                </svg>
                            </div>
                            <p class="stat-label mb-0">{{ $isProfit ? 'Net Profit' : 'Net Loss' }}</p>
                        </div>
                        <div class="stat-value" style="color:{{ $isProfit ? '#0f535e' : '#D94040' }};">
                            {{ $isProfit ? '' : '-' }}{{ number_format(abs($profit)) }}
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                            <span class="badge-trend {{ $isProfit ? 'up' : 'down' }}">
                                <i class="mdi mdi-{{ $isProfit ? 'check-circle' : 'alert-circle' }}"></i>
                                {{ $isProfit ? 'Profitable' : 'Loss' }}
                            </span>
                            <span style="font-size:11.5px;color:var(--text-muted);">Gross: {{ number_format($grossProfit) }}</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ════ ROW 2 — VOLUME & CASH FLOW STATS ════ --}}
            <div class="row g-3 mb-3">

                {{-- Total Received (Payments) --}}
                <div class="col-6 col-xl-3 col-md-6 anim anim-1">
                    <div class="stat-card" style="border-color:var(--border);">
                        <div style="position:absolute;top:0;left:0;right:0;height:3px;border-radius:16px 16px 0 0;background:linear-gradient(90deg,#0f535e,#187280);"></div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#0f535e,#187280);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                    <path fill="#fff" d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2m0 14H4v-6h16zm0-10H4V6h16z" />
                                </svg>
                            </div>
                            <p class="stat-label mb-0">Total Received</p>
                        </div>
                        <div class="stat-value">{{ number_format($totalReceived) }}</div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                            <span class="badge-trend {{ $receivedTrend >= 0 ? 'up' : 'down' }}">
                                <i class="mdi mdi-trending-{{ $receivedTrend >= 0 ? 'up' : 'down' }}"></i>
                                {{ abs($receivedTrend) }}%
                            </span>
                            <span style="font-size:11.5px;color:var(--text-muted);">Collections</span>
                        </div>
                    </div>
                </div>

                {{-- Total Bookings --}}
                <div class="col-6 col-xl-3 col-md-6 anim anim-2">
                    <div class="stat-card emerald">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon-wrap emerald">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                    <path fill="#fff" d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2m0 16H5V10h14zm0-12H5V6h14z" />
                                </svg>
                            </div>
                            <p class="stat-label mb-0">{{ ucfirst($package) }} Bookings</p>
                        </div>
                        <div class="stat-value">{{ number_format($totalBookings) }}</div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                            <span class="badge-trend {{ $bookingsTrend >= 0 ? 'up' : 'down' }}">
                                <i class="mdi mdi-trending-{{ $bookingsTrend >= 0 ? 'up' : 'down' }}"></i>
                                {{ abs($bookingsTrend) }}%
                            </span>
                            <span style="font-size:11.5px;color:var(--text-muted);">Year {{ $year }}</span>
                        </div>
                    </div>
                </div>

                {{-- Total Companies --}}
                <div class="col-6 col-xl-3 col-md-6 anim anim-3">
                    <div class="stat-card" style="border-color:var(--border);">
                        <div style="position:absolute;top:0;left:0;right:0;height:3px;border-radius:16px 16px 0 0;background:linear-gradient(90deg,#0f535e,#187280);"></div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#0f535e,#187280);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                    <path fill="#fff" d="M12 7a4 4 0 1 1 0 8a4 4 0 0 1 0-8m0 10c4.42 0 8 1.79 8 4v2H4v-2c0-2.21 3.58-4 8-4" />
                                </svg>
                            </div>
                            <p class="stat-label mb-0">Companies ({{ $year }})</p>
                        </div>
                        <div class="stat-value">{{ number_format($totalCompanies) }}</div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                            <span class="badge-trend {{ $companiesTrend >= 0 ? 'up' : 'down' }}">
                                <i class="mdi mdi-trending-{{ $companiesTrend >= 0 ? 'up' : 'down' }}"></i>
                                {{ abs($companiesTrend) }}%
                            </span>
                            <span style="font-size:11.5px;color:var(--text-muted);">Partner B2B</span>
                        </div>
                    </div>
                </div>

                {{-- Total Users --}}
                <div class="col-6 col-xl-3 col-md-6 anim anim-4">
                    <div class="stat-card gold">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon-wrap gold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                    <path fill="#fff" d="M12 4a4 4 0 0 1 4 4a4 4 0 0 1-4 4a4 4 0 0 1-4-4a4 4 0 0 1 4-4m0 10c4.42 0 8 1.79 8 4v2H4v-2c0-2.21 3.58-4 8-4" />
                                </svg>
                            </div>
                            <p class="stat-label mb-0">System Users</p>
                        </div>
                        <div class="stat-value">{{ number_format($totalUsers) }}</div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                            <span class="badge-trend {{ $usersTrend >= 0 ? 'up' : 'down' }}">
                                <i class="mdi mdi-trending-{{ $usersTrend >= 0 ? 'up' : 'down' }}"></i>
                                {{ abs($usersTrend) }}%
                            </span>
                            <span style="font-size:11.5px;color:var(--text-muted);">Active Staff</span>
                        </div>
                    </div>
                </div>

            </div>
            {{-- End Row 2 --}}

            {{-- ════ ROW 3 — CHART ════ --}}
            <div class="row g-3 mb-3">

                {{-- Monthly Bar Chart --}}
                <div class="col-12 anim anim-9">
                    <div class="panel-card">
                        <div class="panel-header">
                            <h5>{{ ucfirst($package) }} Bookings — {{ $year }}</h5>
                            <span style="font-size:12px;color:var(--text-muted);">Monthly breakdown</span>
                        </div>
                        <div class="panel-body">
                            <div id="bookings-chart" style="min-height:280px;"></div>
                        </div>
                    </div>
                </div>

            </div>{{-- End Row 3 --}}

            {{-- ════ OPERATIONAL REMINDERS & ALERTS (Check-in, Option Date, Flight Departure & Arrival) ════ --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-2 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="fs-14 fw-bold mb-0 text-dark">
                            <i class="mdi mdi-bell-ring text-warning me-1"></i> Operational Reminders & Alerts
                        </h5>
                        <small class="text-muted">Live tracking for upcoming hotel check-ins, expiring option dates, and flight schedules.</small>
                    </div>
                    <ul class="nav nav-pills card-header-pills fs-12 fw-semibold" id="reminderTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-1 px-3" data-bs-toggle="pill" data-bs-target="#tab-hotel-checkins" type="button">
                                🏨 Check-ins <span class="badge bg-primary ms-1">{{ $hotelCheckinReminders->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#tab-option-dates" type="button">
                                ⏳ Option Dates <span class="badge bg-warning text-dark ms-1">{{ $hotelOptionDateReminders->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#tab-flight-departures" type="button">
                                🛫 Departures <span class="badge bg-info ms-1">{{ $flightDepartureReminders->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#tab-flight-arrivals" type="button">
                                🛬 Arrivals <span class="badge bg-success ms-1">{{ $flightArrivalReminders->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="reminderTabContent">

                        {{-- TAB 1: Hotel Check-ins --}}
                        <div class="tab-pane fade show active" id="tab-hotel-checkins">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 fs-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Hotel & City</th>
                                            <th>Client / Guest</th>
                                            <th>Check-In Date</th>
                                            <th>Check-Out Date</th>
                                            <th>Nights & Rooms</th>
                                            <th>Voucher</th>
                                            <th class="text-center">Status Alert</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hotelCheckinReminders as $hcr)
                                            @php
                                                $ci = \Carbon\Carbon::parse($hcr->check_in);
                                                $daysDiff = \Carbon\Carbon::today()->diffInDays($ci, false);
                                                $alertBadge = match(true) {
                                                    $daysDiff < 0 => '<span class="badge bg-danger">Overdue Check-in</span>',
                                                    $daysDiff == 0 => '<span class="badge bg-success"><i class="mdi mdi-bell-ring"></i> Today</span>',
                                                    $daysDiff == 1 => '<span class="badge bg-warning text-dark">Tomorrow</span>',
                                                    default => '<span class="badge bg-soft-primary text-primary">In ' . $daysDiff . ' Days</span>'
                                                };
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong class="text-dark">{{ $hcr->hotel_name }}</strong>
                                                    <span class="badge bg-light text-muted border ms-1">{{ strtoupper($hcr->location ?? 'Hotel') }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $hcr->booking->client->name ?? ($hcr->booking->given_name ?? '—') }}</strong>
                                                    @if($hcr->booking && $hcr->booking->client && $hcr->booking->client->phone)
                                                        <small class="text-muted d-block">{{ $hcr->booking->client->phone }}</small>
                                                    @endif
                                                </td>
                                                <td><span class="fw-bold text-primary">{{ $ci->format('d M, Y') }}</span></td>
                                                <td>{{ $hcr->check_out ? \Carbon\Carbon::parse($hcr->check_out)->format('d M, Y') : '—' }}</td>
                                                <td>{{ $hcr->no_of_nights ?: 1 }} Nights &bull; {{ $hcr->no_of_rooms ?: 1 }} Room(s)</td>
                                                <td><span class="font-monospace text-muted">{{ $hcr->voucher_no ?: ('BK-' . $hcr->booking_id) }}</span></td>
                                                <td class="text-center">{!! $alertBadge !!}</td>
                                                <td class="text-center">
                                                    @if($hcr->booking_id)
                                                        <a href="{{ route('booking.edit', $hcr->booking_id) }}" class="btn btn-xs btn-outline-primary py-0 px-2" title="View Booking">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-3 text-muted">
                                                    No hotel check-ins scheduled for the next 7 days.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB 2: Hotel Option Dates --}}
                        <div class="tab-pane fade" id="tab-option-dates">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 fs-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Hotel & City</th>
                                            <th>Supplier / Vendor</th>
                                            <th>Client / Booking</th>
                                            <th>Option Cut-Off Date</th>
                                            <th>Check-In</th>
                                            <th class="text-center">Expiry Alert</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hotelOptionDateReminders as $odr)
                                            @php
                                                $od = \Carbon\Carbon::parse($odr->option_date);
                                                $daysDiff = \Carbon\Carbon::today()->diffInDays($od, false);
                                                $optBadge = match(true) {
                                                    $daysDiff < 0 => '<span class="badge bg-danger"><i class="mdi mdi-alert-circle"></i> Expired / Overdue</span>',
                                                    $daysDiff == 0 => '<span class="badge bg-warning text-dark"><i class="mdi mdi-alert"></i> Expires Today!</span>',
                                                    $daysDiff == 1 => '<span class="badge bg-info text-white">Expires Tomorrow</span>',
                                                    default => '<span class="badge bg-soft-warning text-dark">Expires in ' . $daysDiff . ' Days</span>'
                                                };
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong class="text-dark">{{ $odr->hotel_name }}</strong>
                                                    <span class="badge bg-light text-muted border ms-1">{{ strtoupper($odr->location ?? 'Hotel') }}</span>
                                                </td>
                                                <td>{{ $odr->supplier->name ?? '—' }}</td>
                                                <td>
                                                    <strong>{{ $odr->booking->client->name ?? '—' }}</strong>
                                                    <small class="text-muted d-block">Booking #{{ $odr->booking_id }}</small>
                                                </td>
                                                <td><span class="fw-bold text-danger">{{ $od->format('d M, Y') }}</span></td>
                                                <td>{{ $odr->check_in ? \Carbon\Carbon::parse($odr->check_in)->format('d M, Y') : '—' }}</td>
                                                <td class="text-center">{!! $optBadge !!}</td>
                                                <td class="text-center">
                                                    @if($odr->booking_id)
                                                        <a href="{{ route('booking.edit', $odr->booking_id) }}" class="btn btn-xs btn-outline-warning py-0 px-2" title="Manage Booking">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3 text-muted">
                                                    No hotel option date cut-offs pending.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB 3: Flight Departures --}}
                        <div class="tab-pane fade" id="tab-flight-departures">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 fs-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Flight # & Airline</th>
                                            <th>Client / Passengers</th>
                                            <th>Departure Date</th>
                                            <th>Departure Time</th>
                                            <th>PNR / Ticket</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($flightDepartureReminders as $fdr)
                                            @php
                                                $depD = \Carbon\Carbon::parse($fdr->departure_date);
                                                $daysDiff = \Carbon\Carbon::today()->diffInDays($depD, false);
                                                $depBadge = match(true) {
                                                    $daysDiff < 0 => '<span class="badge bg-secondary">Departed</span>',
                                                    $daysDiff == 0 => '<span class="badge bg-success"><i class="mdi mdi-airplane-takeoff"></i> Departing Today</span>',
                                                    $daysDiff == 1 => '<span class="badge bg-info">Departing Tomorrow</span>',
                                                    default => '<span class="badge bg-soft-primary text-primary">In ' . $daysDiff . ' Days</span>'
                                                };
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong class="text-dark">{{ $fdr->departure_flight ?: 'Flight' }}</strong>
                                                    <span class="text-muted d-block small">{{ $fdr->departure_airline ?? '—' }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $fdr->client->name ?? '—' }}</strong>
                                                    <span class="badge bg-light text-dark border ms-1">{{ $fdr->no_of_pax ?: 1 }} Pax</span>
                                                </td>
                                                <td><span class="fw-bold text-primary">{{ $depD->format('d M, Y') }}</span></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border font-monospace">
                                                        <i class="mdi mdi-clock-outline me-1"></i> {{ $fdr->departure_time ?: '—' }}
                                                    </span>
                                                </td>
                                                <td><span class="font-monospace text-primary fw-bold">{{ $fdr->departure_pnr ?: '—' }}</span></td>
                                                <td class="text-center">{!! $depBadge !!}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('booking.edit', $fdr->id) }}" class="btn btn-xs btn-outline-primary py-0 px-2" title="View Booking">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3 text-muted">
                                                    No flight departures scheduled for the next 3 days.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB 4: Flight Arrivals --}}
                        <div class="tab-pane fade" id="tab-flight-arrivals">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 fs-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Flight # & Airline</th>
                                            <th>Client / Passengers</th>
                                            <th>Arrival Date</th>
                                            <th>Arrival Time</th>
                                            <th>PNR</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($flightArrivalReminders as $far)
                                            @php
                                                $arrD = \Carbon\Carbon::parse($far->arrival_date);
                                                $daysDiff = \Carbon\Carbon::today()->diffInDays($arrD, false);
                                                $arrBadge = match(true) {
                                                    $daysDiff < 0 => '<span class="badge bg-secondary">Arrived</span>',
                                                    $daysDiff == 0 => '<span class="badge bg-success"><i class="mdi mdi-airplane-landing"></i> Arriving Today</span>',
                                                    $daysDiff == 1 => '<span class="badge bg-info">Arriving Tomorrow</span>',
                                                    default => '<span class="badge bg-soft-primary text-primary">In ' . $daysDiff . ' Days</span>'
                                                };
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong class="text-dark">{{ $far->arrival_flight ?: ($far->departure_flight ?: 'Flight') }}</strong>
                                                    <span class="text-muted d-block small">{{ $far->arrival_airline ?: ($far->departure_airline ?? '—') }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $far->client->name ?? '—' }}</strong>
                                                    <span class="badge bg-light text-dark border ms-1">{{ $far->no_of_pax ?: 1 }} Pax</span>
                                                </td>
                                                <td><span class="fw-bold text-success">{{ $arrD->format('d M, Y') }}</span></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border font-monospace">
                                                        <i class="mdi mdi-clock-outline me-1"></i> {{ $far->arrival_time ?: '—' }}
                                                    </span>
                                                </td>
                                                <td><span class="font-monospace text-primary fw-bold">{{ $far->arrival_pnr ?: ($far->departure_pnr ?: '—') }}</span></td>
                                                <td class="text-center">{!! $arrBadge !!}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('booking.edit', $far->id) }}" class="btn btn-xs btn-outline-success py-0 px-2" title="View Booking">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3 text-muted">
                                                    No flight arrivals scheduled for the next 3 days.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ════ ROW 4 — TABLE + SIDE PANELS ════ --}}
            <div class="row g-3 mb-4">

                {{-- Recent Bookings Table --}}
                <div class="col-md-12 col-xl-8">
                    <div class="panel-card">
                        <div class="panel-header">
                            <h5>Recent {{ ucfirst($package) }} Bookings</h5>
                            <a href="{{ route('booking.index') }}"
                                style="font-size:12.5px;font-weight:600;color:var(--gold);text-decoration:none;">
                                View All <i class="mdi mdi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="panel-body p-0">
                            <div class="table-responsive">
                                <table class="table bookings-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Package</th>
                                            <th>Pax</th>
                                            <th>Amount</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentBookings as $booking)
                                            @php
                                                // ── FIX: booking_for ke hisab se client ya company
                                                // ka naam/email dikhao, warna company bookings
                                                // mein "N/A" show hota tha ──
                                                $isCompanyBooking = $booking->booking_for === 'company';

                                                $clientName = $isCompanyBooking
                                                    ? optional($booking->company)->name ?? 'N/A'
                                                    : optional($booking->client)->name ?? 'N/A';

                                                $clientEmail = $isCompanyBooking
                                                    ? optional($booking->company)->email ?? ''
                                                    : optional($booking->client)->email ?? '';

                                                $words = explode(' ', trim($clientName));
                                                $initials = strtoupper(
                                                    substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1),
                                                );
                                                $gradMap = [
                                                    'hajj' => 'var(--gold), var(--gold-light)',
                                                    'umrah' => 'var(--emerald), #4CAF82',
                                                    'other' => '#3D5CCC, #6B7FF0',
                                                ];
                                                $grad =
                                                    $gradMap[$booking->package_type] ??
                                                    'var(--navy), var(--navy-light)';
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div
                                                            style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,{{ $grad }});display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:11px;flex-shrink:0;">
                                                            {{ $initials }}
                                                        </div>
                                                        <div>
                                                            <div style="font-weight:600;font-size:13px;">
                                                                {{ $clientName }}
                                                                @if ($isCompanyBooking)
                                                                    <span class="for-tag company">CO</span>
                                                                @else
                                                                    <span class="for-tag client">CL</span>
                                                                @endif
                                                            </div>
                                                            <div style="font-size:11.5px;color:var(--text-muted);">
                                                                {{ $clientEmail }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="pkg-badge {{ $booking->package_type }}">
                                                        {{ ucfirst($booking->package_type) }}
                                                    </span>
                                                </td>
                                                <td style="font-size:13px;">{{ $booking->no_of_pax ?? '—' }}</td>
                                                <td style="font-weight:600;">
                                                    {{ number_format($booking->total_amount) }}</td>
                                                <td>
                                                    @if (($booking->balance ?? 0) <= 0)
                                                        <span class="status-dot confirmed">Paid</span>
                                                    @else
                                                        <span style="font-size:12px;color:#D94040;font-weight:600;">
                                                            {{ number_format($booking->balance) }}
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4"
                                                    style="color:var(--text-muted);font-size:13px;">
                                                    No {{ $package }} bookings found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Side: Quick Stats --}}
                <div class="col-md-12 col-xl-4">
                    <div class="panel-card">
                        <div class="panel-header">
                            <h5>Quick Stats — {{ ucfirst($package) }}</h5>
                        </div>
                        <div class="panel-body">
                            <div class="qs-row">
                                <div class="qs-icon" style="background:#FBF5E6;">✈️</div>
                                <div>
                                    <div class="qs-label">With Flight Charges</div>
                                    <div class="qs-value">{{ number_format($flightsThisMonth) }} this month</div>
                                </div>
                            </div>
                            <div class="qs-row">
                                <div class="qs-icon" style="background:#E8F5EE;">🏨</div>
                                <div>
                                    <div class="qs-label">With Hotel Charges</div>
                                    <div class="qs-value">{{ number_format($hotelsThisMonth) }} this month</div>
                                </div>
                            </div>
                            <div class="qs-row">
                                <div class="qs-icon" style="background:#EEF2FF;">📄</div>
                                <div>
                                    <div class="qs-label">With Visa Charges</div>
                                    <div class="qs-value">{{ number_format($visasApproved) }} total</div>
                                </div>
                            </div>
                            <div class="qs-row">
                                <div class="qs-icon" style="background:#FFF8E8;">👥</div>
                                <div>
                                    <div class="qs-label">Total {{ ucfirst($package) }} Bookings</div>
                                    <div class="qs-value">{{ number_format($totalBookings) }} total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-xl-12">
                    <div class="panel-card">
                        <div class="panel-header">
                            <h5>Agent wise Sales Report</h5>
                            <a href="{{ route('booking.index') }}"
                                style="font-size:12.5px;font-weight:600;color:var(--gold);text-decoration:none;">
                                View All <i class="mdi mdi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="panel-body p-0">
                            <div class="table-responsive">
                                <table class="table bookings-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Agent</th>
                                            <th>Hajj</th>
                                            <th>Umrah</th>
                                            <th>Other</th>
                                            <th>Total Bookings</th>
                                            <th>Total Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- <tr>
                                            <td style="font-weight:600;font-size:13px;">Ahmed Raza</td>
                                            <td>5</td>
                                            <td>8</td>
                                            <td>2</td>
                                            <td style="font-weight:600;">15</td>
                                            <td style="font-weight:600;">1,250,000</td>
                                        </tr> --}}
                                        {{-- <tr>
                                            <td style="font-weight:600;font-size:13px;">Bilal Khan</td>
                                            <td>3</td>
                                            <td>12</td>
                                            <td>1</td>
                                            <td style="font-weight:600;">16</td>
                                            <td style="font-weight:600;">980,500</td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight:600;font-size:13px;">Sara Malik</td>
                                            <td>7</td>
                                            <td>4</td>
                                            <td>3</td>
                                            <td style="font-weight:600;">14</td>
                                            <td style="font-weight:600;">1,540,750</td>
                                        </tr> --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- End Row 4 --}}

        </div>{{-- container-fluid --}}
    </div>{{-- content --}}
</div>
{{-- content-page --}}

{{-- ApexCharts --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isHajj = '{{ $package }}' === 'hajj';
        const barColor = '#0f535e';
        const barLight = '#187280';

        new ApexCharts(document.querySelector("#bookings-chart"), {
            series: [{
                name: '{{ ucfirst($package) }} Bookings',
                data: {!! json_encode($monthlyData) !!}
            }],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: {
                    show: false
                },
                fontFamily: 'DM Sans, sans-serif'
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '48%'
                }
            },
            colors: [barColor],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    gradientToColors: [barLight],
                    stops: [0, 100]
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct',
                    'Nov', 'Dec'
                ],
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#9CA3AF',
                        fontSize: '11px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#9CA3AF',
                        fontSize: '11px'
                    }
                }
            },
            grid: {
                borderColor: '#F4F0E8',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: false
                    }
                }
            },
            tooltip: {
                theme: 'light',
                style: {
                    fontFamily: 'DM Sans, sans-serif'
                }
            }
        }).render();
    });
</script>
@endsection
