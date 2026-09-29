@extends('layout.master')

@section('title', 'Bookings')
@section('header-title', 'Bookings')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container">

                <!-- PAGE TITLE -->
                <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 fw-semibold m-0">Dashboard</h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <!-- HEADER -->
                            <div class="card-header d-flex justify-content-between align-items-center">

                                <h5 class="mb-0">Booking List</h5>

                                <div class="d-flex gap-2">

                                    @can('booking_create')
                                        <a href="{{ route('booking.create') }}"
                                            class="btn btn-primary btn-sm d-flex gap-1 align-items-center">
                                            Add Booking
                                        </a>
                                    @endcan
                                    @can('booking_trash_view')
                                        <a href="{{ route('booking.trash') }}"
                                            class="btn btn-danger btn-sm d-flex gap-1 align-items-center">
                                            Trash <span>{{ $trashCount ?? 0 }}</span>
                                        </a>
                                    @endcan
                                </div>

                            </div>

                            <!-- BODY -->
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle">

                                        <thead class="table-light">
                                            <tr>
                                                <th>S:NO</th>
                                                <th>Client / Company</th>
                                                <th>Package Type</th>
                                                <th>No of Pax</th>
                                                <th>Total Amount</th>
                                                <th>Balance</th>
                                                <th>Year</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($bookings as $booking)
                                                <tr>

                                                    <td>{{ $loop->iteration }}</td>

                                                    <td>
                                                        <strong>
                                                            {{ $booking->booking_for === 'company' ? $booking->company->name ?? 'N/A' : $booking->client->name ?? 'N/A' }}
                                                        </strong>
                                                        @if ($booking->booking_for === 'company')
                                                            <span class="badge bg-secondary ms-1"
                                                                style="font-size:10px;">Company</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <span class="badge bg-primary">
                                                            {{ ucfirst($booking->package_type) }}
                                                        </span>
                                                    </td>

                                                    <td>{{ $booking->no_of_pax ?? 0 }}</td>

                                                    <td>
                                                        {{ number_format($booking->total_amount) }}
                                                    </td>

                                                    <td>
                                                        <span class="badge bg-warning text-dark">
                                                            {{ number_format($booking->balance) }}
                                                        </span>
                                                    </td>

                                                    <td>{{ $booking->package_year ?? 'N/A' }}</td>

                                                    <td>
                                                        <div class="d-flex gap-2">

                                                            <!-- VIEW -->
                                                            @can('booking_show')
                                                                <a href="{{ route('booking.show', $booking->id) }}"
                                                                    class="btn btn-sm btn-outline-primary">
                                                                    <i class="mdi mdi-eye"></i>
                                                                </a>
                                                            @endcan

                                                            <!-- EDIT -->
                                                            @can('booking_edit')
                                                                <a href="{{ route('booking.edit', $booking->id) }}"
                                                                    class="btn btn-sm btn-outline-success">
                                                                    <i class="mdi mdi-pencil"></i>
                                                                </a>
                                                            @endcan

                                                            <a href="{{ route('booking.voucher', $booking->id) }}"
                                                                target="_blank" class="btn btn-sm btn-outline-warning"
                                                                title="Hotel Voucher">
                                                                <i class="mdi mdi-file-document-outline"></i>
                                                            </a>


                                                            <!-- DELETE -->
                                                            @can('booking_trash')
                                                                <form action="{{ route('booking.delete', $booking->id) }}"
                                                                    method="POST" onsubmit="return confirm('Are you sure?');">
                                                                    @csrf
                                                                    @method('DELETE')

                                                                    <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger">
                                                                        <i class="mdi mdi-delete"></i>
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                            <!-- AGREEENT -->
                                                            {{-- <a target="_blank"
                                                            href="{{ \Illuminate\Support\Facades\URL::signedRoute('booking.agreement', ['booking' => $booking->id]) }}"
                                                            class="btn btn-sm btn-outline-success"
                                                            title="View Agreement">
                                                                <i class="mdi mdi-file-document-outline"></i>
                                                            </a> --}}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
