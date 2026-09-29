@extends('layout.master')
@section('title', 'Travel Routes')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Travel Routes</h4>
                    <div class="d-flex gap-2">
                        @can('travel_route_create')
                            <a href="{{ route('travel-route.create') }}" class="btn btn-primary btn-sm">
                                + Add Travel Route
                            </a>
                        @endcan
                        @can('travel_route_trash_view')
                            <a href="{{ route('travel-route.trash') }}" class="btn btn-danger btn-sm">
                                🗑 Trash <span class="badge bg-white text-danger ms-1">{{ $trashCount }}</span>
                            </a>
                        @endcan
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Route Name</th>
                                        <th>Vehicle</th>
                                        <th>Arrival Date & Time</th>
                                        <th>Sharing Type</th>
                                        <th>Included Routes</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($travelRoutes as $tr)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $tr->name }}</strong></td>
                                            <td>
                                                @if($tr->vehicle)
                                                    {{ $tr->vehicle->vehicle_type ?? '' }} {{ $tr->vehicle->brand_name ?? '' }} ({{ $tr->vehicle->plate_number ?? '' }})
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                {{ $tr->arrival_date ? \Carbon\Carbon::parse($tr->arrival_date)->format('d M Y') : '—' }}
                                                @if($tr->arrival_time)
                                                    <small class="text-muted">({{ \Carbon\Carbon::parse($tr->arrival_time)->format('h:i A') }})</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $tr->sharing_type ?? '—' }}</span>
                                            </td>
                                            <td>
                                                @forelse($tr->routes as $r)
                                                    <span class="badge bg-info text-dark me-1 mb-1">
                                                        {{ $r->formatted_route }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted fs-12">No routes assigned</span>
                                                @endforelse
                                            </td>
                                            <td>
                                                <span class="badge {{ $tr->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($tr->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('travel-route.show', $tr->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Show">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    @can('travel_route_edit')
                                                        <a href="{{ route('travel-route.edit', $tr->id) }}"
                                                            class="btn btn-sm btn-outline-success" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('travel_route_trash')
                                                        <form action="{{ route('travel-route.delete', $tr->id) }}"
                                                            method="POST" onsubmit="return confirm('Move to trash?')">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                                <i class="mdi mdi-delete"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
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
