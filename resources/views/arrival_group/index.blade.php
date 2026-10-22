@extends('layout.master')

@section('title', 'Arrival Groups')
@section('header-title', 'Arrival Groups')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- PAGE TITLE -->
                <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 fw-semibold m-0">
                            <i class="mdi mdi-airplane-landing text-success me-1"></i> Arrival Groups
                        </h4>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <!-- HEADER -->
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Arrival Groups Listing</h5>
                                <a href="{{ route('arrival-group.create') }}" class="btn btn-success btn-sm d-flex gap-1 align-items-center">
                                    <i class="mdi mdi-plus"></i> Add Arrival Group
                                </a>
                            </div>

                            <!-- BODY -->
                            <div class="card-body">

                                <form method="GET" action="{{ route('arrival-group.index') }}" class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by Group Name, Flight No, PNR..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-sm btn-primary">Search</button>
                                        @if(request('search'))
                                            <a href="{{ route('arrival-group.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
                                        @endif
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table class="table table-bordered dt-responsive nowrap align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>S:NO</th>
                                                <th>Group Name</th>
                                                <th>Airline / Flight</th>
                                                <th>Flight Date & Time</th>
                                                <th>PNR</th>
                                                <th>Route (From - To)</th>
                                                <th>Passengers</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($groups as $group)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <strong class="text-success fs-14">{{ $group->group_name }}</strong>
                                                        <span class="badge bg-success ms-1" style="font-size: 10px;">Arrival</span>
                                                    </td>
                                                    <td>
                                                        {{ $group->airline->name ?? 'N/A' }}
                                                        @if($group->flight_number)
                                                            <br><span class="badge bg-info text-dark">{{ $group->flight_number }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $group->flight_date ? $group->flight_date->format('d M Y') : 'N/A' }}
                                                        @if($group->flight_time)
                                                            <br><small class="text-muted"><i class="mdi mdi-clock-outline"></i> {{ $group->flight_time }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($group->pnr)
                                                            <span class="badge bg-dark fw-bold">{{ $group->pnr }}</span>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $group->departure_city ?? '—' }} <i class="mdi mdi-arrow-right text-muted"></i> {{ $group->arrival_city ?? '—' }}
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary rounded-pill px-3 py-1 fs-12">
                                                            <i class="mdi mdi-account-group me-1"></i> {{ $group->persons->count() }} Pax
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-1">
                                                            <a href="{{ route('arrival-group.show', $group->id) }}" class="btn btn-sm btn-outline-primary" title="View VIP Sheet">
                                                                <i class="mdi mdi-eye"></i>
                                                            </a>
                                                            <a href="{{ route('arrival-group.edit', $group->id) }}" class="btn btn-sm btn-outline-success" title="Edit Group">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </a>
                                                            <form action="{{ route('arrival-group.delete', $group->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Arrival Group?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                                    <i class="mdi mdi-delete"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4 text-muted">
                                                        No Arrival Groups found. Click <strong>Add Arrival Group</strong> to create one.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    {{ $groups->links() }}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
