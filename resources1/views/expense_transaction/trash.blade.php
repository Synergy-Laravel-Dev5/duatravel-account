@extends('layout.master')

@section('title', 'Trash Transactions')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="m-0">Trash Transactions</h4>
                    <a href="{{ route('expense.transaction.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>

                <div class="card">
                    <div class="card-body">

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Expense</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($transactions as $key => $t)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $t->expense->name ?? '-' }}</td>
                                        <td>{{ $t->amount }}</td>
                                        <td>{{ $t->date }}</td>
                                        <td>
                                            <a href="{{ route('expense.transaction.restore', $t->id) }}"
                                                class="btn btn-success btn-sm">
                                                Restore
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No trash data</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
