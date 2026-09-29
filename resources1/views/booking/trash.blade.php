@extends('layout.master')
@section('title', 'Booking Trash')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Booking Trash</h4>
                    <a href="{{ route('booking.index') }}" class="btn btn-secondary btn-sm">← Back</a>
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
                                        <th>Client</th>
                                        <th>Passenger</th>
                                        <th>Package</th>
                                        <th>Total</th>
                                        <th>Deleted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bookings as $booking)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $booking->client->name ?? 'N/A' }}</td>
                                            <td>{{ $booking->given_name }} {{ $booking->sur_name }}</td>
                                            <td>
                                                @php $badges=['umrah'=>'bg-success','hajj'=>'bg-warning text-dark','other'=>'bg-secondary']; @endphp
                                                <span class="badge {{ $badges[$booking->package_type] ?? 'bg-secondary' }}">
                                                    {{ ucfirst($booking->package_type) }}
                                                </span>
                                            </td>
                                            <td>PKR {{ number_format($booking->total_amount, 0) }}</td>
                                            <td>{{ $booking->deleted_at->format('d M Y, h:i A') }}</td>
                                            <td>
                                                <a href="{{ route('booking.restore', $booking->id) }}"
                                                    class="btn btn-sm btn-outline-success">
                                                    <i class="mdi mdi-restore me-1"></i> Restore
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Trash is empty.</td>
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
