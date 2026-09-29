@extends('layout.master')
@section('title', 'Flights')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Flights</h4>
                    <div class="d-flex gap-2">
                        @can('flight_create')
                            <a href="{{ route('flight.create') }}" class="btn btn-primary btn-sm">
                                + Add Flight
                            </a>
                        @endcan
                        @can('flight_trash_view')
                            <a href="{{ route('flight.trash') }}" class="btn btn-danger btn-sm">
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
                                        <th>S:NO</th>
                                        <th>Airline</th>
                                        <th>Name</th>
                                        <th>Outbound Flight #</th>
                                        <th>Outbound Departure</th>
                                        <th>Inbound Flight #</th>
                                        <th>Inbound Departure</th>
                                        <th>Economy Seats</th>
                                        <th>Business Seats</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($flights as $flight)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $flight->airline_name ?? ($flight->airline->name ?? '—') }}</strong>
                                            </td>
                                            <td>{{ $flight->name ?? '—' }}</td>
                                            <td>{{ $flight->outbound_flight_no ?? '—' }}</td>
                                            <td>{{ $flight->outbound_departure ? $flight->outbound_departure->format('Y-m-d H:i') : '—' }}
                                            </td>
                                            <td>{{ $flight->inbound_flight_no ?? '—' }}</td>
                                            <td>{{ $flight->inbound_departure ? $flight->inbound_departure->format('Y-m-d H:i') : '—' }}
                                            </td>
                                            <td>{{ $flight->economy_seats ?? '0' }}</td>
                                            <td>{{ $flight->business_seats ?? '0' }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $flight->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($flight->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('flight.show', $flight->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Show">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    @can('flight_edit')
                                                        <a href="{{ route('flight.edit', $flight->id) }}"
                                                            class="btn btn-sm btn-outline-success" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('flight_trash')
                                                        <form action="{{ route('flight.delete', $flight->id) }}" method="POST"
                                                            onsubmit="return confirm('Move to trash?')">
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
