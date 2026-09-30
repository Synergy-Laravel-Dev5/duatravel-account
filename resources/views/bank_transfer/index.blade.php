@extends('layout.master')
@section('title', 'Bank Transfers')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Bank Transfers</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('bank-transfer.create') }}" class="btn btn-primary btn-sm">
                            + New Transfer
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
                                        <th>Date</th>
                                        <th>From Bank</th>
                                        <th>To Bank</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transfers as $transfer)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ \Carbon\Carbon::parse($transfer->transfer_date)->format('d M Y') }}</td>
                                            <td><span class="badge bg-danger">From:</span> <strong>{{ $transfer->fromBank->name ?? 'Deleted' }}</strong></td>
                                            <td><span class="badge bg-success">To:</span> <strong>{{ $transfer->toBank->name ?? 'Deleted' }}</strong></td>
                                            <td><strong>{{ number_format($transfer->amount, 2) }}</strong></td>
                                            <td>{{ $transfer->description ?? '-' }}</td>
                                            <td>
                                                <form action="{{ route('bank-transfer.destroy', $transfer->id) }}" method="POST" class="d-inline">
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
                                            <td colspan="7" class="text-center">No transfers found.</td>
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
