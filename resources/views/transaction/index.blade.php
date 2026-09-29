@extends('layout.master')

@section('title', 'Transactions')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- TOP BAR -->
                <div class="py-4 d-flex align-items-center justify-content-between border-bottom mb-4">
                    <h4 class="fs-22 fw-bold m-0">All Transactions</h4>
                    <div class="d-flex gap-2">
                        @if ($trashCount > 0)
                            @can('client_Transactions_trash_view')
                                <a href="{{ route('transaction.trash') }}" class="btn btn-outline-danger btn-sm">
                                    <i class="mdi mdi-delete-restore me-1"></i> Trash
                                    <span class="badge bg-danger">{{ $trashCount }}</span>
                                </a>
                            @endif
                        @endcan
                        @can('client_Transactions_create')
                            <a href="{{ route('transaction.create') }}" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus me-1"></i> Add Transaction
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

                <!-- FILTER CARD -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body py-3">
                        <form method="GET" action="{{ route('transaction.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-medium">Client</label>
                                <select name="client_id" class="form-select">
                                    <option value="">-- All Clients --</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-medium">Company</label>
                                <select name="company_id" class="form-select">
                                    <option value="">-- All Companies --</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->company_name ?? $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-medium">Payment Type</label>
                                <select name="payment_type" class="form-select">
                                    <option value="">-- All Types --</option>
                                    <option value="cash" {{ request('payment_type') == 'cash' ? 'selected' : '' }}>Cash
                                    </option>
                                    <option value="bank_transfer"
                                        {{ request('payment_type') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer
                                    </option>
                                    <option value="cheque" {{ request('payment_type') == 'cheque' ? 'selected' : '' }}>
                                        Cheque</option>
                                    <option value="online" {{ request('payment_type') == 'online' ? 'selected' : '' }}>
                                        Online</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-medium">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">-- All Status --</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>
                                        Confirmed</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                        Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2 align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                                <a href="{{ route('transaction.index') }}"
                                    class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Client / Company</th>
                                        {{-- <th>Booking</th> --}}
                                        <th>Payment Type</th>
                                        <th class="text-end">Amount</th>
                                        <th>Date</th>
                                        <th>Ref No</th>
                                        <th>Proof</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($transactions as $t)
                                        <tr>
                                            <td class="ps-4">{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $t->client->name ?? ($t->company->name ?? '—') }}</strong>
                                                @if (!$t->client && $t->company)
                                                    <span class="badge bg-secondary ms-1">Company</span>
                                                @endif
                                            </td>
                                            {{-- <td>
                                                @if ($t->booking)
                                                    <a
                                                        href="{{ route('booking.show', $t->booking->id) }}">#{{ $t->booking->id }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td> --}}
                                            <td>
                                                <span
                                                    class="badge {{ ['cash' => 'bg-success', 'bank_transfer' => 'bg-primary', 'cheque' => 'bg-warning', 'online' => 'bg-info'][$t->payment_type] ?? 'bg-secondary' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $t->payment_type)) }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold text-success">PKR
                                                {{ number_format($t->amount, 0) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($t->payment_date)->format('d M, Y') }}</td>
                                            <td>{{ $t->reference_number ?? '—' }}</td>
                                            <td>
                                                @if ($t->proof_of_payment)
                                                    <a href="{{ asset('assets/images/transaction_proofs/' . $t->proof_of_payment) }}"
                                                        target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge {{ ['confirmed' => 'bg-success', 'pending' => 'bg-warning', 'rejected' => 'bg-danger'][$t->status] ?? 'bg-secondary' }}">
                                                    {{ ucfirst($t->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    @can('client_Transactions_show')
                                                        <a href="{{ route('transaction.show', $t->id) }}"
                                                            class="btn btn-sm btn-outline-info"><i class="mdi mdi-eye"></i></a>
                                                    @endcan
                                                    @can('client_Transactions_edit')
                                                        <a href="{{ route('transaction.edit', $t->id) }}"
                                                            class="btn btn-sm btn-outline-success"><i
                                                                class="mdi mdi-pencil"></i></a>
                                                    @endcan
                                                    <a href="{{ route('transaction.invoice', $t->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Invoice"
                                                        target="_blank">
                                                        <i class="mdi mdi-file-document-outline"></i>
                                                    </a>
                                                    @can('client_Transactions_trash')
                                                        <form action="{{ route('transaction.destroy', $t->id) }}"
                                                            method="POST" onsubmit="return confirm('Move to trash?')">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-danger">
                                                                <i class="mdi mdi-delete"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-5 text-muted">No transactions found.
                                            </td>
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
