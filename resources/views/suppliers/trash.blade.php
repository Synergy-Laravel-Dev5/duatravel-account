@extends('layout.master')
@section('title', 'Suppliers Trash')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Deleted Suppliers (Trash)</h4>
                    <a href="{{ route('supplier.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Suppliers
                    </a>
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
                                        <th style="width: 50px;">S:NO</th>
                                        <th>Supplier Name</th>
                                        <th>Company Name</th>
                                        <th>Service Type</th>
                                        <th>Phone</th>
                                        <th>Deleted At</th>
                                        <th style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($suppliers as $supplier)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $supplier->name }}</strong></td>
                                            <td>{{ $supplier->company_name ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-soft-info text-info text-capitalize">{{ $supplier->service_type ?? 'All' }}</span>
                                            </td>
                                            <td>{{ $supplier->phone ?? '-' }}</td>
                                            <td>{{ $supplier->deleted_at ? $supplier->deleted_at->format('d M Y, h:i A') : '-' }}</td>
                                            <td>
                                                <a href="{{ route('supplier.restore', $supplier->id) }}" class="btn btn-sm btn-success" title="Restore">
                                                    <i class="mdi mdi-restore"></i> Restore
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Trash is empty.</td>
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
