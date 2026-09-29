@extends('layout.master')
@section('title', 'Vehicle Trash')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Vehicle Trash</h4>
                    <a href="{{ route('vehicle.index') }}" class="btn btn-secondary btn-sm">← Back</a>
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
                                        <th>Vehicle Type</th>
                                        <th>Brand/Model Name</th>
                                        <th>Plate Number</th>
                                        <th>Deleted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vehicles as $vehicle)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $vehicle->vehicle_type ?? '—' }}</strong></td>
                                            <td>{{ $vehicle->brand_name ?? '—' }}</td>
                                            <td>{{ $vehicle->plate_number ?? '—' }}</td>
                                            <td>{{ $vehicle->deleted_at->format('d M Y, h:i A') }}</td>
                                            <td>
                                                @can('vehicle_restore')
                                                    <a href="{{ route('vehicle.restore', $vehicle->id) }}"
                                                        class="btn btn-sm btn-outline-success">
                                                        <i class="mdi mdi-restore me-1"></i> Restore
                                                    </a>
                                                @endcan
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
