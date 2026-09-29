@extends('layout.master')
@section('title', 'Vehicles')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Vehicles</h4>
                    <div class="d-flex gap-2">
                        @can('vehicle_create')
                            <a href="{{ route('vehicle.create') }}" class="btn btn-primary btn-sm">
                                + Add Vehicle
                            </a>
                        @endcan
                        @can('vehicle_trash_view')
                            <a href="{{ route('vehicle.trash') }}" class="btn btn-danger btn-sm">
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
                                        <th>Vehicle Type</th>
                                        <th>Brand/Model Name</th>
                                        <th>Model Year</th>
                                        <th>Plate Number</th>
                                        <th>Supplier Name</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vehicles as $vehicle)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $vehicle->vehicle_type ?? '—' }}</strong></td>
                                            <td>{{ $vehicle->brand_name ?? '—' }}</td>
                                            <td>{{ $vehicle->model_year ?? '—' }}</td>
                                            <td>{{ $vehicle->plate_number ?? '—' }}</td>
                                            <td>{{ $vehicle->supplier_name ?? '—' }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $vehicle->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($vehicle->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('vehicle.show', $vehicle->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Show">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    @can('vehicle_edit')
                                                        <a href="{{ route('vehicle.edit', $vehicle->id) }}"
                                                            class="btn btn-sm btn-outline-success" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('vehicle_trash')
                                                        <form action="{{ route('vehicle.delete', $vehicle->id) }}"
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
