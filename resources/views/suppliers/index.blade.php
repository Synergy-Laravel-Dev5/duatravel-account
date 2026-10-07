@extends('layout.master')
@section('title', 'Suppliers')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Suppliers Management</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('supplier.ledger.filter') }}" class="btn btn-outline-info btn-sm">
                            <i class="mdi mdi-book-open-variant me-1"></i> Supplier Ledgers
                        </a>
                        <a href="{{ route('supplier.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus-circle me-1"></i> + Add Supplier
                        </a>
                        <a href="{{ route('supplier.trash') }}" class="btn btn-danger btn-sm">
                            <i class="mdi mdi-trash-can-outline me-1"></i> Trash <span class="badge bg-white text-danger ms-1">{{ $trashCount ?? 0 }}</span>
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">S:NO</th>
                                        <th>Supplier Info</th>
                                        <th>Service Type</th>
                                        <th>Contact / Phone</th>
                                        <th>City / Country</th>
                                        <th class="text-end">Total Payable</th>
                                        <th class="text-end">Total Paid</th>
                                        <th class="text-end">Balance Due</th>
                                        <th>Status</th>
                                        <th style="width: 140px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($suppliers as $supplier)
                                        @php
                                            $payable = $supplier->total_payable;
                                            $paid = $supplier->total_paid;
                                            $bal = $supplier->balance_due;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $supplier->name }}</strong>
                                                @if($supplier->company_name)
                                                    <div class="small text-muted">{{ $supplier->company_name }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-info text-info text-capitalize">{{ $supplier->service_type ?? 'All' }}</span>
                                            </td>
                                            <td>
                                                <div>{{ $supplier->contact_person ?? '—' }}</div>
                                                <div class="small text-muted">{{ $supplier->phone ?? '' }}</div>
                                            </td>
                                            <td>{{ $supplier->city ?? '' }}{{ $supplier->city && $supplier->country ? ', ' : '' }}{{ $supplier->country ?? '' }}</td>
                                            <td class="text-end fw-semibold text-dark">{{ number_format($payable, 2) }}</td>
                                            <td class="text-end fw-semibold text-success">{{ number_format($paid, 2) }}</td>
                                            <td class="text-end fw-bold {{ $bal > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($bal, 2) }}
                                            </td>
                                            <td>
                                                @if ($supplier->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="{{ route('supplier.ledger.show', $supplier->id) }}" class="btn btn-sm btn-primary" title="View Supplier Ledger">
                                                        <i class="mdi mdi-book-open-variant"></i>
                                                    </a>
                                                    <a href="{{ route('expense.transaction.create') }}?payee_type=supplier&supplier_id={{ $supplier->id }}" class="btn btn-sm btn-success" title="Pay Supplier">
                                                        <i class="mdi mdi-cash-multiple"></i>
                                                    </a>
                                                    <a href="{{ route('supplier.edit', $supplier->id) }}" class="btn btn-sm btn-info" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('supplier.delete', $supplier->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this supplier?')" title="Delete">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4 text-muted">No suppliers found. Click "+ Add Supplier" to create one.</td>
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
