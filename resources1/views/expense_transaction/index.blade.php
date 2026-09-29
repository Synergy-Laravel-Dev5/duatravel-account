@extends('layout.master')

@section('title', 'Expense Transactions')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- TOP BAR -->
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Expense Transactions</h4>

                    <div class="d-flex gap-2">
                        @can('expense_transaction_create')
                            <a href="{{ route('expense.transaction.create') }}" class="btn btn-primary btn-sm">
                                + Add Transaction
                            </a>
                        @endcan
                    </div>
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
                                        <th>Expense</th>
                                        <th>Amount</th>
                                        <th>Currency</th>
                                        <th>Payment</th>
                                        <th>Type</th>
                                        <th>Year</th>
                                        <th>Date</th>
                                        <th>Proof</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @if ($transactions->count() > 0)

                                        @foreach ($transactions as $t)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>

                                                <!-- Expense -->
                                                <td>
                                                    <strong>{{ $t->expense->name ?? '—' }}</strong>
                                                </td>

                                                <!-- Amount -->
                                                <td>{{ number_format($t->amount, 2) }}</td>

                                                <!-- Currency -->
                                                <td>
                                                    <span class="badge"
                                                        style="background:{{ $t->currency === 'SAR' ? '#be123c' : '#0e7490' }}">
                                                        {{ $t->currency }}
                                                    </span>
                                                </td>

                                                <!-- Payment -->
                                                <td>
                                                    @php
                                                        $payments = [
                                                            'cash' => 'bg-success',
                                                            'bank' => 'bg-primary',
                                                            'online' => 'bg-info',
                                                            'ewallet' => 'bg-warning',
                                                        ];
                                                    @endphp

                                                    <span class="badge {{ $payments[$t->payment_type] ?? 'bg-secondary' }}">
                                                        {{ ucfirst($t->payment_type) }}
                                                    </span>
                                                </td>

                                                <!-- Type -->
                                                <td>
                                                    {{ $t->hajj_umrah }}
                                                </td>

                                                <!-- Year -->
                                                <td>
                                                    {{ $t->year }}
                                                </td>

                                                <!-- Date -->
                                                <td>
                                                    {{ \Carbon\Carbon::parse($t->date)->format('d M, Y') }}
                                                </td>

                                                <!-- Proof -->
                                                <td>
                                                    @if ($t->proof)
                                                        <a href="{{ asset('assets/images/expense_proofs/' . $t->proof) }}"
                                                            target="_blank" class="btn btn-sm btn-outline-info">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>

                                                <!-- ACTION -->
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        @can('expense_transaction_edit')
                                                            <a href="{{ route('expense.transaction.edit', $t->id) }}"
                                                                class="btn btn-sm btn-outline-success" title="Edit">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </a>
                                                        @endcan
                                                        @can('expense_transaction_trash')
                                                            <form action="{{ route('expense.transaction.delete', $t->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Delete this transaction?')">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                                    <i class="mdi mdi-delete"></i>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>

                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                No expense transactions found.
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
