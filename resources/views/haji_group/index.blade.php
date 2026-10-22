@extends('layout.master')
@section('title', 'Haji Pilgrim Group Management')

@section('content')
<style>
    .group-nav-tabs .nav-link {
        font-weight: 600;
        color: #64748b;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 10px 18px;
        font-size: 14px;
    }
    .group-nav-tabs .nav-link.active {
        color: #001030;
        border-bottom-color: #001030;
        background: transparent;
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Title & Actions -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-1 text-primary">
                        <i class="mdi mdi-account-group me-1"></i> Haji Pilgrim Group Management
                    </h4>
                    <p class="text-muted fs-13 mb-0">
                        Manage separate groups for Flights, Buses/Vehicles, Hotel Rooms, and Travel Routes.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    @if(($groupType ?? 'all') === 'flight')
                        <a href="{{ route('haji-group.create', ['type' => 'flight']) }}" class="btn btn-info btn-sm">
                            <i class="mdi mdi-airplane me-1"></i> + Create Flight Group
                        </a>
                    @elseif(($groupType ?? 'all') === 'bus')
                        <a href="{{ route('haji-group.create', ['type' => 'bus']) }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-bus me-1"></i> + Create Bus Group
                        </a>
                    @elseif(($groupType ?? 'all') === 'hotel')
                        <a href="{{ route('haji-group.create', ['type' => 'hotel']) }}" class="btn btn-success btn-sm">
                            <i class="mdi mdi-hotel me-1"></i> + Create Hotel Group
                        </a>
                    @elseif(($groupType ?? 'all') === 'route')
                        <a href="{{ route('haji-group.create', ['type' => 'route']) }}" class="btn btn-warning btn-sm text-dark">
                            <i class="mdi mdi-map-marker-path me-1"></i> + Create Route Group
                        </a>
                    @else
                        <a href="{{ route('haji-group.create', ['type' => 'flight']) }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus-circle me-1"></i> + Create New Group
                        </a>
                    @endif
                </div>
            </div>

            <!-- Flash Message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show fs-13" role="alert">
                    <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show fs-13" role="alert">
                    <i class="mdi mdi-information me-1"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- CATEGORY NAV TABS (Flight, Bus, Hotel, Route, All) -->
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-white p-0 border-bottom">
                    <ul class="nav group-nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link {{ ($groupType ?? 'all') === 'flight' ? 'active' : '' }}" href="{{ route('haji-group.index', ['type' => 'flight']) }}">
                                <i class="mdi mdi-airplane me-1 text-info"></i> 1. Flight Groups
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($groupType ?? 'all') === 'bus' ? 'active' : '' }}" href="{{ route('haji-group.index', ['type' => 'bus']) }}">
                                <i class="mdi mdi-bus me-1 text-primary"></i> 2. Bus / Vehicle Groups
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($groupType ?? 'all') === 'hotel' ? 'active' : '' }}" href="{{ route('haji-group.index', ['type' => 'hotel']) }}">
                                <i class="mdi mdi-hotel me-1 text-success"></i> 3. Hotel Rooming Groups
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($groupType ?? 'all') === 'route' ? 'active' : '' }}" href="{{ route('haji-group.index', ['type' => 'route']) }}">
                                <i class="mdi mdi-map-marker-path me-1 text-warning"></i> 4. Travel Route Groups
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($groupType ?? 'all') === 'all' ? 'active' : '' }}" href="{{ route('haji-group.index', ['type' => 'all']) }}">
                                <i class="mdi mdi-format-list-bulleted me-1 text-secondary"></i> All Combined Groups
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('haji-group.index') }}">
                        <input type="hidden" name="type" value="{{ $groupType ?? 'all' }}">
                        <div class="row g-2">
                            <div class="col-md-9">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Search by group name, leader name, bus name, or PNR..."
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3 d-flex gap-1">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="mdi mdi-magnify me-1"></i> Search
                                </button>
                                <a href="{{ route('haji-group.index', ['type' => $groupType ?? 'all']) }}" class="btn btn-light btn-sm">
                                    <i class="mdi mdi-refresh"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Groups Table Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle fs-13 mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Group Name & Type</th>
                                    <th>Group Leader</th>
                                    <th>Pilgrims Count</th>
                                    @if(($groupType ?? 'all') === 'flight' || ($groupType ?? 'all') === 'all')
                                        <th>Flight / PNR</th>
                                    @endif
                                    @if(($groupType ?? 'all') === 'bus' || ($groupType ?? 'all') === 'all')
                                        <th>Bus / Vehicle</th>
                                    @endif
                                    @if(($groupType ?? 'all') === 'hotel' || ($groupType ?? 'all') === 'all')
                                        <th>Hotel Accommodation</th>
                                    @endif
                                    @if(($groupType ?? 'all') === 'route' || ($groupType ?? 'all') === 'all')
                                        <th>Travel Route</th>
                                    @endif
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groups as $idx => $g)
                                    @php
                                        $typeBadge = 'bg-primary';
                                        if ($g->group_type === 'flight') $typeBadge = 'bg-info';
                                        elseif ($g->group_type === 'bus') $typeBadge = 'bg-primary';
                                        elseif ($g->group_type === 'hotel') $typeBadge = 'bg-success';
                                        elseif ($g->group_type === 'route') $typeBadge = 'bg-warning text-dark';
                                    @endphp
                                    <tr>
                                        <td>{{ $groups->firstItem() + $idx }}</td>
                                        <td>
                                            <a href="{{ route('haji-group.show', $g->id) }}" class="fw-bold text-primary">
                                                {{ $g->group_name }}
                                            </a>
                                            <br>
                                            <span class="badge {{ $typeBadge }} fs-10 text-uppercase">
                                                {{ $g->group_type ?: 'General' }} Group
                                            </span>
                                        </td>
                                        <td>
                                            @if($g->leader_name)
                                                <strong>{{ $g->leader_name }}</strong>
                                                @if($g->leader_phone)
                                                    <br><small class="text-muted"><i class="mdi mdi-phone"></i> {{ $g->leader_phone }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary fs-12 px-2 py-1">
                                                <i class="mdi mdi-account-multiple me-1"></i> {{ $g->persons->count() }} Pilgrims
                                            </span>
                                            @if($g->bus_capacity && ($g->group_type === 'bus' || $g->vehicle_id))
                                                <br><small class="text-muted">Cap: {{ $g->bus_capacity }} seats</small>
                                            @endif
                                        </td>
                                        @if(($groupType ?? 'all') === 'flight' || ($groupType ?? 'all') === 'all')
                                            <td>
                                                @if($g->pnr || $g->flight_number || $g->airline)
                                                    <span class="fw-semibold text-dark"><i class="mdi mdi-airplane me-1 text-info"></i> {{ $g->airline->name ?? '' }} {{ $g->flight_number }}</span>
                                                    @if($g->pnr)
                                                        <br><small class="text-muted">PNR: <strong>{{ $g->pnr }}</strong></small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-light text-muted fs-11">Unassigned</span>
                                                @endif
                                            </td>
                                        @endif
                                        @if(($groupType ?? 'all') === 'bus' || ($groupType ?? 'all') === 'all')
                                            <td>
                                                @if($g->vehicle || $g->bus_name)
                                                    <span class="fw-semibold text-dark"><i class="mdi mdi-bus me-1 text-primary"></i> {{ $g->bus_name ?: ($g->vehicle->title ?? 'Assigned Bus') }}</span>
                                                @else
                                                    <span class="badge bg-light text-muted fs-11">Unassigned</span>
                                                @endif
                                            </td>
                                        @endif
                                        @if(($groupType ?? 'all') === 'hotel' || ($groupType ?? 'all') === 'all')
                                            <td>
                                                @if($g->hotel)
                                                    <span class="fw-semibold text-dark"><i class="mdi mdi-hotel me-1 text-success"></i> {{ $g->hotel->name }}</span>
                                                    @if($g->room_type)
                                                        <br><small class="text-muted">{{ $g->room_type }}</small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-light text-muted fs-11">Unassigned</span>
                                                @endif
                                            </td>
                                        @endif
                                        @if(($groupType ?? 'all') === 'route' || ($groupType ?? 'all') === 'all')
                                            <td>
                                                @if($g->travelRoute || $g->route)
                                                    <span class="fw-semibold text-dark"><i class="mdi mdi-map-marker-path me-1 text-warning"></i> {{ $g->travelRoute->title ?? ($g->route->title ?? 'Assigned Route') }}</span>
                                                @else
                                                    <span class="badge bg-light text-muted fs-11">Unassigned</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            <span class="badge {{ $g->status === 'active' ? 'bg-success' : 'bg-secondary' }} fs-10 text-uppercase">
                                                {{ $g->status }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('haji-group.show', $g->id) }}" class="btn btn-outline-info" title="View & Allocate">
                                                    <i class="mdi mdi-eye"></i> View
                                                </a>
                                                <a href="{{ route('haji-group.edit', $g->id) }}" class="btn btn-outline-primary" title="Edit Group">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <form action="{{ route('haji-group.delete', $g->id) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this group?');" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete Group">
                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-account-group-outline fs-24 d-block mb-1"></i>
                                            No {{ $groupType !== 'all' ? ucfirst($groupType) : '' }} groups created yet. Click "+ Create Group" to add a new group.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($groups->hasPages())
                    <div class="card-footer bg-white py-2">
                        {{ $groups->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
