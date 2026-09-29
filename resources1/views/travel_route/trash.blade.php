@extends('layout.master')
@section('title', 'Trashed Travel Routes')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Trashed Travel Routes</h4>
                    <a href="{{ route('travel-route.index') }}" class="btn btn-secondary btn-sm">Back to Travel Routes</a>
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
                                        <th>Deleted At</th>
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
                                                    {{ $tr->vehicle->vehicle_type ?? '' }} {{ $tr->vehicle->brand_name ?? '' }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $tr->deleted_at ? $tr->deleted_at->format('Y-m-d H:i') : '—' }}</td>
                                            <td>
                                                @can('travel_route_restore')
                                                    <a href="{{ route('travel-route.restore', $tr->id) }}"
                                                        class="btn btn-sm btn-outline-success" title="Restore">
                                                        <i class="mdi mdi-restore"></i> Restore
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No trashed travel routes found.</td>
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
