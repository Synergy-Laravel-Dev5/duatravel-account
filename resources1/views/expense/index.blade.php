@extends('layout.master')

@section('title', 'Expenses')

@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Expense Heads</h4>
                    <div class="d-flex gap-2">
                        @can('expense_create')
                        <a href="{{ route('expense.create') }}" class="btn btn-primary btn-sm">
                            + Add Expense Head
                        </a>
                        @endcan
                        @can('expense_trash_view')
                        <a href="{{ route('expense.trash') }}" class="btn btn-danger btn-sm">
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
                                        <th>Expense Name</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenses as $expense)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $expense->name }}</strong></td>
                                          
                                            <td>{{ $expense->created_at->format('d M, Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    @can('expense_edit')
                                                    <a href="{{ route('expense.edit', $expense->id) }}"
                                                        class="btn btn-sm btn-outline-success" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    @endcan
                                                    @can('expense_trash')
                                                    <form action="{{ route('expense.delete', $expense->id) }}"
                                                        method="POST" onsubmit="return confirm('Move to trash?')">
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
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No expense heads found.
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
