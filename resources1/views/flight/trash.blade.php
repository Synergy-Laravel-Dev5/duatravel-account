@extends('layout.master')
@section('title', 'Flight Trash')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Flight Trash</h4>
                    <a href="{{ route('flight.index') }}" class="btn btn-secondary btn-sm">← Back</a>
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
                                        <th>Airline</th>
                                        <th>Name</th>
                                        <th>Outbound Flight #</th>
                                        <th>Deleted At</th>
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
                                            <td>{{ $flight->deleted_at->format('d M Y, h:i A') }}</td>
                                            <td>
                                                @can('flight_restore')
                                                    <a href="{{ route('flight.restore', $flight->id) }}"
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
