@extends('layout.master')

@section('title', 'Hajj & Umrah Season Management')

@section('content')
<style>
    .season-tab .nav-link {
        font-weight: 600;
        color: #64748b;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 10px 20px;
        font-size: 14px;
    }
    .season-tab .nav-link.active {
        color: #0f535e;
        border-bottom-color: #0f535e;
        background: transparent;
    }
    .badge-hajj {
        background-color: #0f535e;
        color: #ffffff;
    }
    .badge-umrah {
        background-color: #d84816;
        color: #ffffff;
    }
    .active-season-banner {
        background: linear-gradient(135deg, rgba(15, 83, 94, 0.08), rgba(216, 72, 22, 0.08));
        border: 1px solid rgba(15, 83, 94, 0.2);
        border-radius: 12px;
        padding: 16px 20px;
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Title & Top Actions -->
            <div class="py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h4 class="fs-18 fw-semibold m-0 text-dark">
                        <i class="mdi mdi-calendar-sync me-1 text-primary"></i> Hajj & Umrah Seasons
                    </h4>
                    <p class="text-muted fs-13 mb-0">
                        Manage financial and operational seasons. The entire system (Bookings, Accounts, Reports, Cash Book) runs according to the selected season.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('season.create') }}" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-plus-circle me-1"></i> Add Season
                    </a>
                    <a href="{{ route('season.trash') }}" class="btn btn-outline-danger btn-sm">
                        <i class="mdi mdi-delete-outline me-1"></i> Trash
                        @if($trashCount > 0)
                            <span class="badge bg-danger text-white ms-1">{{ $trashCount }}</span>
                        @endif
                    </a>
                </div>
            </div>

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Current Active Season in System Session Banner -->
            @php
                $currSessionId = session('selected_season_id');
                $currPkg = session('dashboard_package', 'hajj');
                $currYr  = session('dashboard_year', date('Y'));
            @endphp
            <div class="active-season-banner mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div style="font-size: 28px;">
                        @if($currPkg === 'hajj')
                            🕋
                        @else
                            🕌
                        @endif
                    </div>
                    <div>
                        <div class="fs-12 text-uppercase fw-semibold text-muted">Currently Active System Season</div>
                        <div class="fs-16 fw-bold text-dark">
                            {{ session('selected_season_name', ($activeSeason ? $activeSeason->name : strtoupper($currPkg) . ' ' . $currYr)) }}
                            <span class="badge {{ $currPkg === 'hajj' ? 'badge-hajj' : 'badge-umrah' }} ms-1">
                                {{ strtoupper($currPkg) }}
                            </span>
                            <span class="badge bg-secondary ms-1">{{ $currYr }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-muted fs-12">
                    <i class="mdi mdi-information-outline me-1"></i>
                    All Bookings, Expenses, Cash Book, Groups and Reports currently filter by this season.
                </div>
            </div>

            <!-- Filter Tabs & Status Buttons -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 border-bottom pb-2">
                <ul class="nav nav-pills gap-1">
                    <li class="nav-item">
                        <a class="nav-link btn-sm {{ ($type ?? 'all') === 'all' && ($status ?? 'all') === 'all' ? 'active' : '' }}" href="{{ route('season.index') }}">
                            <i class="mdi mdi-view-list me-1"></i> All Seasons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-sm {{ ($status ?? '') === 'active' ? 'active bg-success text-white' : '' }}" href="{{ route('season.index', ['status' => 'active', 'type' => $type ?? 'all']) }}">
                            <i class="mdi mdi-check-circle me-1"></i> Active Seasons ({{ $activeCount ?? 0 }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-sm {{ ($status ?? '') === 'inactive' ? 'active bg-danger text-white' : '' }}" href="{{ route('season.index', ['status' => 'inactive', 'type' => $type ?? 'all']) }}">
                            <i class="mdi mdi-close-circle me-1"></i> Inactive Seasons ({{ $inactiveCount ?? 0 }})
                        </a>
                    </li>
                </ul>

                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('season.index', ['type' => 'all', 'status' => $status ?? 'all']) }}" class="btn btn-outline-secondary {{ ($type ?? 'all') === 'all' ? 'active' : '' }}">
                        All Types
                    </a>
                    <a href="{{ route('season.index', ['type' => 'hajj', 'status' => $status ?? 'all']) }}" class="btn btn-outline-secondary {{ ($type ?? '') === 'hajj' ? 'active' : '' }}">
                        🕋 Hajj
                    </a>
                    <a href="{{ route('season.index', ['type' => 'umrah', 'status' => $status ?? 'all']) }}" class="btn btn-outline-secondary {{ ($type ?? '') === 'umrah' ? 'active' : '' }}">
                        🕌 Umrah
                    </a>
                </div>
            </div>

            <!-- Seasons Table Card -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Season Name</th>
                                    <th>Type</th>
                                    <th>Year</th>
                                    <th>Hijri Year</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Default Active</th>
                                    <th>System Session</th>
                                    <th class="text-end" style="width: 220px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($seasons as $season)
                                    @php
                                        $isCurrentInSession = ($currSessionId == $season->id) || (!$currSessionId && $currPkg == $season->season_type && $currYr == $season->year);
                                    @endphp
                                    <tr class="{{ $isCurrentInSession ? 'table-success bg-opacity-25' : '' }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-bold fs-14 text-dark">{{ $season->name }}</div>
                                            @if($season->notes)
                                                <small class="text-muted">{{ Str::limit($season->notes, 40) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($season->season_type === 'hajj')
                                                <span class="badge badge-hajj px-2 py-1">
                                                    🕋 HAJJ
                                                </span>
                                            @else
                                                <span class="badge badge-umrah px-2 py-1">
                                                    🕌 UMRAH
                                                </span>
                                            @endif
                                        </td>
                                        <td><strong>{{ $season->year }}</strong></td>
                                        <td>
                                            {{ $season->hijri_year ?? '—' }}
                                        </td>
                                        <td>
                                            @if($season->start_date && $season->end_date)
                                                <small class="text-muted d-block">
                                                    {{ $season->start_date->format('d M Y') }} - {{ $season->end_date->format('d M Y') }}
                                                </small>
                                            @elseif($season->start_date)
                                                <small class="text-muted d-block">From {{ $season->start_date->format('d M Y') }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($season->status === 'active')
                                                <a href="{{ route('season.toggle-status', $season->id) }}" 
                                                   class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center shadow-sm"
                                                   title="Status is ACTIVE. Click to set INACTIVE"
                                                   onclick="return confirm('Change status of \'{{ $season->name }}\' to INACTIVE?')">
                                                    <i class="mdi mdi-check-circle me-1"></i> Active
                                                </a>
                                            @else
                                                <a href="{{ route('season.toggle-status', $season->id) }}" 
                                                   class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center shadow-sm"
                                                   title="Status is INACTIVE. Click to set ACTIVE"
                                                   onclick="return confirm('Change status of \'{{ $season->name }}\' to ACTIVE?')">
                                                    <i class="mdi mdi-close-circle me-1"></i> Inactive
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if($season->is_active)
                                                <span class="badge bg-success text-white" title="Default active season for {{ strtoupper($season->season_type) }}">
                                                    <i class="mdi mdi-star"></i> Default Active
                                                </span>
                                            @else
                                                <a href="{{ route('season.set-active', $season->id) }}" class="btn btn-xs btn-outline-secondary" title="Make default active for this type" onclick="return confirm('Set {{ $season->name }} as the default active season?')">
                                                    Set Default
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if($isCurrentInSession)
                                                <span class="badge bg-primary text-white">
                                                    <i class="mdi mdi-check-circle-outline"></i> Running Now
                                                </span>
                                            @else
                                                <a href="{{ route('season.switch', $season->id) }}" class="btn btn-sm btn-outline-primary" title="Switch entire system to this season">
                                                    <i class="mdi mdi-swap-horizontal"></i> Switch Here
                                                </a>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <a href="{{ route('season.edit', $season->id) }}" class="btn btn-sm btn-outline-success" title="Edit Season">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <form action="{{ route('season.delete', $season->id) }}" method="POST" onsubmit="return confirm('Move season \'{{ $season->name }}\' to trash?')" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Move to Trash">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-calendar-remove fs-24 d-block mb-1"></i>
                                            No seasons found. Click <strong>+ Add Season</strong> to create your first season.
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
</div>
@endsection
