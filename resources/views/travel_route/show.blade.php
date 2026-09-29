@extends('layout.master')
@section('title', 'Travel Route Details')
@section('header-title', 'Travel Route Details')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container">
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Travel Route: {{ $travelRoute->name }}</h4>
                    <div>
                        @can('travel_route_edit')
                            <a href="{{ route('travel-route.edit', $travelRoute->id) }}" class="btn btn-success btn-sm">Edit</a>
                        @endcan
                        <a href="{{ route('travel-route.index') }}" class="btn btn-secondary btn-sm">Back</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Details</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered mb-0">
                                    <tr>
                                        <th style="width: 30%;">Route Name:</th>
                                        <td><strong>{{ $travelRoute->name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Vehicle:</th>
                                        <td>
                                            @if($travelRoute->vehicle)
                                                {{ $travelRoute->vehicle->vehicle_type ?? '' }} {{ $travelRoute->vehicle->brand_name ?? '' }} ({{ $travelRoute->vehicle->plate_number ?? '' }})
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Arrival Date & Time:</th>
                                        <td>
                                            {{ $travelRoute->arrival_date ? \Carbon\Carbon::parse($travelRoute->arrival_date)->format('d M Y') : '—' }}
                                            @if($travelRoute->arrival_time)
                                                ({{ \Carbon\Carbon::parse($travelRoute->arrival_time)->format('h:i A') }})
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Group Sharing:</th>
                                        <td><span class="badge bg-secondary">{{ $travelRoute->sharing_type ?? 'N/A' }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge {{ $travelRoute->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($travelRoute->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Included Routes</h5>
                            </div>
                            <div class="card-body">
                                @forelse($travelRoute->routes as $route)
                                    <div class="p-2 mb-2 bg-light rounded border border-info">
                                        <i class="mdi mdi-map-marker text-danger me-1"></i>
                                        <strong>{{ $route->formatted_route }}</strong>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No routes attached to this travel route.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
