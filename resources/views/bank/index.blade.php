@extends('layout.master')
@section('title', 'Banks')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Banks</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('bank.create') }}" class="btn btn-primary btn-sm">
                            + Add Bank
                        </a>
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
                                        <th>S:NO</th>
                                        <th>Account / Bank Name</th>
                                        <th>Type</th>
                                        <th>Account # / Branch</th>
                                        <th class="text-end">Opening (PKR)</th>
                                        <th class="text-end">Opening (SAR)</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($banks as $bank)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $bank->name }}</strong>
                                                @if($bank->account_name)
                                                    <small class="d-block text-muted">{{ $bank->account_name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if(($bank->account_type ?? 'bank') === 'cash')
                                                    <span class="badge bg-soft-success text-success border border-success border-opacity-25"><i class="mdi mdi-cash me-1"></i> Cash Account</span>
                                                @else
                                                    <span class="badge bg-soft-primary text-primary border border-primary border-opacity-25"><i class="mdi mdi-bank me-1"></i> Bank Account</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $bank->account_number ?? '—' }}
                                                @if($bank->branch)
                                                    <small class="d-block text-muted">{{ $bank->branch }}</small>
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold text-danger">
                                                PKR {{ number_format($bank->opening_balance_pkr ?? 0, 2) }}
                                            </td>
                                            <td class="text-end fw-semibold text-success">
                                                SAR {{ number_format($bank->opening_balance_sar ?? 0, 2) }}
                                            </td>
                                            <td>
                                                @if ($bank->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('bank.edit', $bank->id) }}" class="btn btn-sm btn-info" title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <form action="{{ route('bank.destroy', $bank->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No accounts found.</td>
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
