@extends('layout.master')

@section('title', 'Trashed Transactions')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- TOP BAR -->
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Trashed Transactions</h4>
                    <a href="{{ route('transaction.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Transactions
                    </a>
                </div>

                <!-- SUCCESS MESSAGE -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- TABLE -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">

                            <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle">

                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Client</th>
                                        <th>Booking</th>
                                        <th>Payment Type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Deleted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @if ($transactions->count() > 0)
                                        @foreach ($transactions as $t)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>

                                                <td><strong>{{ $t->client->name ?? '—' }}</strong></td>

                                                <td>{{ $t->booking ? '#' . $t->booking->id : '—' }}</td>

                                                <td>
                                                    @php
                                                        $typeColors = [
                                                            'cash' => 'bg-success',
                                                            'bank_transfer' => 'bg-primary',
                                                            'cheque' => 'bg-warning',
                                                            'online' => 'bg-info',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge {{ $typeColors[$t->payment_type] ?? 'bg-secondary' }}">
                                                        {{ ucfirst(str_replace('_', ' ', $t->payment_type)) }}
                                                    </span>
                                                </td>

                                                <td><strong>PKR {{ number_format($t->amount, 0) }}</strong></td>

                                                <td>{{ \Carbon\Carbon::parse($t->payment_date)->format('d M, Y') }}
                                                </td>

                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'confirmed' => 'bg-success',
                                                            'pending' => 'bg-warning',
                                                            'rejected' => 'bg-danger',
                                                        ];
                                                    @endphp
                                                    <span class="badge {{ $statusColors[$t->status] ?? 'bg-secondary' }}">
                                                        {{ ucfirst($t->status) }}
                                                    </span>
                                                </td>

                                                <td>{{ $t->deleted_at->format('d M, Y') }}</td>

                                                <td>
                                                    <form action="{{ route('transaction.restore', $t->id) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        <button class="btn btn-sm btn-success">
                                                            <i class="mdi mdi-restore"></i> Restore
                                                        </button>
                                                    </form>
                                                </td>

                                            </tr>
                                        @endforeach
                                    @else 
                                        <tr>
                                            <td colspan="9" class="text-`center text-muted py-4">
                                                Trash is empty.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>

                            </table>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
