@extends('layout.master')
@section('title', 'Accounts')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fs-18 fw-semibold m-0">Account Management</h4>
                        <p class="text-muted fs-13 mb-0">Manage Cash, Bank, General, Loan Assets (Receivables), and Loan Liabilities (Payables).</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('account.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus-circle me-1"></i> + Add Account
                        </a>
                    </div>
                </div>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">S:NO</th>
                                        <th>Account Title</th>
                                        <th>Account Type</th>
                                        <th>Linked Entity / Party</th>
                                        <th class="text-end">Opening (PKR)</th>
                                        <th class="text-end">Opening (SAR)</th>
                                        <th style="width: 80px;">Status</th>
                                        <th style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($accounts as $account)
                                        @php
                                            $entityName = $account->entity_name;
                                            $eType = $account->entity_type;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>
                                                <strong class="text-dark">{{ $account->account_name }}</strong>
                                                @if(!empty($account->notes))
                                                    <small class="text-muted d-block fs-11">{{ Str::limit($account->notes, 60) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($account->account_type === 'cash')
                                                    <span class="badge bg-soft-success text-success border border-success border-opacity-25 fs-12">
                                                        <i class="mdi mdi-cash me-1"></i> Cash Account
                                                    </span>
                                                @elseif($account->account_type === 'loan_asset')
                                                    <span class="badge bg-soft-primary text-primary border border-primary border-opacity-25 fs-12">
                                                        <i class="mdi mdi-arrow-down-bold-circle me-1"></i> Loan Asset (Receivable)
                                                    </span>
                                                @elseif($account->account_type === 'loan_liability')
                                                    <span class="badge bg-soft-warning text-warning border border-warning border-opacity-25 fs-12">
                                                        <i class="mdi mdi-arrow-up-bold-circle me-1"></i> Loan Liability (Payable)
                                                    </span>
                                                @elseif($account->account_type === 'bank')
                                                    <span class="badge bg-soft-info text-info border border-info border-opacity-25 fs-12">
                                                        <i class="mdi mdi-bank me-1"></i> Bank Account
                                                    </span>
                                                @else
                                                    <span class="badge bg-soft-secondary text-secondary border border-secondary border-opacity-25 fs-12">
                                                        <i class="mdi mdi-folder me-1"></i> General
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($eType === 'staff' && $entityName)
                                                    <span class="badge bg-soft-success text-success fs-12">
                                                        <i class="mdi mdi-account-tie me-1"></i> Staff: {{ $entityName }}
                                                    </span>
                                                @elseif($eType === 'client' && $entityName)
                                                    <span class="badge bg-soft-info text-info fs-12">
                                                        <i class="mdi mdi-account me-1"></i> Client: {{ $entityName }}
                                                    </span>
                                                @elseif($eType === 'supplier' && $entityName)
                                                    <span class="badge bg-soft-primary text-primary fs-12">
                                                        <i class="mdi mdi-truck-delivery me-1"></i> Supplier: {{ $entityName }}
                                                    </span>
                                                @elseif($eType === 'bank' && $entityName)
                                                    <span class="badge bg-soft-dark text-dark fs-12">
                                                        <i class="mdi mdi-bank me-1"></i> Bank: {{ $entityName }}
                                                    </span>
                                                @else
                                                    <span class="text-muted fs-12">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold text-danger">
                                                PKR {{ number_format($account->opening_balance_pkr ?? 0, 2) }}
                                            </td>
                                            <td class="text-end fw-semibold text-success">
                                                SAR {{ number_format($account->opening_balance_sar ?? 0, 2) }}
                                            </td>
                                            <td class="text-center">
                                                @if ($account->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('account.edit', $account->id) }}" class="btn btn-sm btn-info" title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <form action="{{ route('account.destroy', $account->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this account?')" title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">No accounts found. Click "+ Add Account" to create one.</td>
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
