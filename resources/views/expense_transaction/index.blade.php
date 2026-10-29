@extends('layout.master')

@section('title', 'Transactions')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- TOP BAR -->
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fs-18 fw-semibold m-0 text-dark">
                            <i class="mdi mdi-cash-multiple text-primary me-1"></i> Transactions
                        </h4>
                        <p class="text-muted fs-13 mb-0">List of all recorded supplier payments, client receipts, staff salaries, and expenses.</p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('expense.transaction.report.filter') }}" class="btn btn-outline-info btn-sm">
                            <i class="mdi mdi-file-chart me-1"></i> Transaction Reports
                        </a>
                        @can('expense_transaction_create')
                            <a href="{{ route('expense.transaction.create') }}" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus-circle me-1"></i> + Add Transaction
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- SUCCESS MESSAGE -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show fs-13" role="alert">
                        <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- TABLE -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">

                            <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle fs-13">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Reference #</th>
                                        <th>Category / Payee</th>
                                        <th>Bank / Contra A/C</th>
                                        <th>Amount</th>
                                        <th>Type & Payment</th>
                                        <th>Proof</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @if ($transactions->count() > 0)

                                        @foreach ($transactions as $t)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>

                                                <!-- Date -->
                                                <td>
                                                    {{ $t->date ? \Carbon\Carbon::parse($t->date)->format('d M, Y') : '—' }}
                                                    <div class="text-muted fs-11">{{ ucfirst($t->hajj_umrah) }} {{ $t->year }}</div>
                                                </td>

                                                <!-- Reference -->
                                                <td>
                                                    <span class="badge bg-soft-primary text-primary fw-semibold fs-11">
                                                        {{ $t->reference_no ?? ('DT-EXP-' . str_pad($t->id, 5, '0', STR_PAD_LEFT)) }}
                                                    </span>
                                                </td>

                                                <!-- Category / Payee -->
                                                <td>
                                                    @if($t->payee_type === 'supplier' || $t->supplier)
                                                        <span class="badge bg-soft-primary text-primary">
                                                            <i class="mdi mdi-truck-delivery me-1"></i> {{ $t->supplier->name ?? 'Supplier' }}
                                                            @if($t->supplier && $t->supplier->company_name) ({{ $t->supplier->company_name }}) @endif
                                                        </span>
                                                        <small class="text-muted d-block fs-11">Supplier ({{ strtoupper($t->supplier->service_type ?? 'Vendor') }})</small>
                                                    @elseif($t->payee_type === 'client' || $t->client)
                                                        <span class="badge bg-soft-info text-info">
                                                            <i class="mdi mdi-account me-1"></i> {{ $t->client->name ?? 'Client' }}
                                                        </span>
                                                        <small class="text-muted d-block fs-11">Client ({{ $t->client->phone ?? $t->client->cnic ?? '' }})</small>
                                                    @elseif($t->payee_type === 'staff' || $t->staff)
                                                        <span class="badge bg-soft-success text-success">
                                                            <i class="mdi mdi-account-tie me-1"></i> {{ $t->staff->first_name ?? '' }} {{ $t->staff->last_name ?? '' }}
                                                        </span>
                                                        <small class="text-muted d-block fs-11">Staff ({{ $t->staff->designation->name ?? 'Employee' }})</small>
                                                    @elseif($t->payee_type === 'company' || $t->company)
                                                        <span class="badge bg-soft-warning text-warning">
                                                            <i class="mdi mdi-domain me-1"></i> {{ $t->company->name ?? $t->company->company_name ?? 'Company' }}
                                                        </span>
                                                        <small class="text-muted d-block fs-11">Company / HGO</small>
                                                    @elseif($t->payee_type === 'expense' || $t->expense)
                                                        <span class="badge bg-soft-secondary text-secondary">
                                                            <i class="mdi mdi-office-building me-1"></i> {{ $t->expense->name ?? 'Office Expense' }}
                                                        </span>
                                                        <small class="text-muted d-block fs-11">Expense Head</small>
                                                    @elseif($t->payee_type === 'jv')
                                                        <span class="badge" style="background-color: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe;">
                                                            <i class="mdi mdi-book-open-page-variant me-1"></i> JV Voucher
                                                        </span>
                                                        @php
                                                            $contraParty = null;
                                                            if (!empty($t->reference_no)) {
                                                                if ($t->client_id && !$t->supplier_id) {
                                                                    $sib = \App\Models\ExpenseTransaction::where('payee_type', 'jv')
                                                                        ->where('reference_no', $t->reference_no)
                                                                        ->whereNotNull('supplier_id')
                                                                        ->with('supplier')
                                                                        ->first();
                                                                    if ($sib && $sib->supplier) {
                                                                        $contraParty = '⇄ ' . $sib->supplier->name . ' (Supplier)';
                                                                    }
                                                                } elseif ($t->supplier_id && !$t->client_id) {
                                                                    $sib = \App\Models\ExpenseTransaction::where('payee_type', 'jv')
                                                                        ->where('reference_no', $t->reference_no)
                                                                        ->whereNotNull('client_id')
                                                                        ->with('client')
                                                                        ->first();
                                                                    if ($sib && $sib->client) {
                                                                        $contraParty = '⇄ ' . $sib->client->name . ' (Client)';
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        @if($t->client && $t->supplier)
                                                            <small class="text-muted d-block fs-11">
                                                                <strong>Client:</strong> {{ $t->client->name }} ⇄ <strong>Supplier:</strong> {{ $t->supplier->name }}
                                                            </small>
                                                        @elseif($t->client)
                                                            <small class="text-muted d-block fs-11">
                                                                Party: {{ $t->client->name }} (Client) {{ $contraParty ? ' ' . $contraParty : '' }}
                                                            </small>
                                                        @elseif($t->supplier)
                                                            <small class="text-muted d-block fs-11">
                                                                Party: {{ $t->supplier->name }} (Supplier) {{ $contraParty ? ' ' . $contraParty : '' }}
                                                            </small>
                                                        @else
                                                            <small class="text-muted d-block fs-11">Journal Adjustment</small>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-soft-dark text-dark">
                                                            <i class="mdi mdi-account-circle me-1"></i> Self / Transfer
                                                        </span>
                                                    @endif
                                                </td>

                                                <!-- Bank / Contra A/C -->
                                                <td>
                                                    @if($t->bank)
                                                        <strong class="text-dark">{{ $t->bank->name }}</strong>
                                                        @php
                                                            $hasAcctName = !empty($t->bank->account_name) && trim($t->bank->account_name) !== '' && $t->bank->account_name !== '0';
                                                            $hasAcctNum = !empty($t->bank->account_number) && trim($t->bank->account_number) !== '' && $t->bank->account_number !== '0';
                                                        @endphp
                                                        @if($hasAcctName || $hasAcctNum)
                                                            <div class="text-muted fs-11">
                                                                {{ $hasAcctName ? $t->bank->account_name : '' }}
                                                                {{ $hasAcctNum ? '(' . $t->bank->account_number . ')' : '' }}
                                                            </div>
                                                        @endif
                                                    @elseif($t->account)
                                                        <strong class="text-dark">{{ $t->account->account_name }}</strong>
                                                    @elseif(empty($t->payment_type) || strtolower($t->payment_type) === 'cash' || empty($t->bank_id))
                                                        <span class="badge bg-soft-success text-success border border-success border-opacity-25 px-2 py-1">
                                                            <i class="mdi mdi-cash me-1"></i> Cash in Hand
                                                        </span>
                                                    @elseif(strtolower($t->payment_type) === 'cheque')
                                                        <span class="badge bg-soft-warning text-dark border">
                                                            <i class="mdi mdi-checkbook me-1"></i> Cheque Clearance
                                                        </span>
                                                    @else
                                                        <span class="badge bg-soft-secondary text-secondary">
                                                            {{ ucwords(str_replace('_', ' ', $t->payment_type)) }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <!-- Amount -->
                                                <td>
                                                    <strong class="text-dark fs-14">{{ number_format($t->amount, 2) }}</strong>
                                                    <span class="badge"
                                                        style="background:{{ $t->currency === 'SAR' ? '#be123c' : ($t->currency === 'USD' ? '#16a34a' : '#0e7490') }}">
                                                        {{ $t->currency }}
                                                    </span>
                                                </td>

                                                <!-- Type & Payment -->
                                                <td>
                                                    @php
                                                        $payments = [
                                                            'cash'    => 'bg-success',
                                                            'bank'    => 'bg-primary',
                                                            'cheque'  => 'bg-warning text-dark',
                                                            'online'  => 'bg-info',
                                                            'ewallet' => 'bg-secondary',
                                                        ];
                                                    @endphp

                                                    <span class="badge {{ $payments[$t->payment_type] ?? 'bg-secondary' }}">
                                                        {{ ucfirst($t->payment_type) }}
                                                    </span>
                                                    @if($t->transaction_type)
                                                        <span class="badge {{ $t->transaction_type === 'credit' ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} ms-1 fs-10 text-uppercase">
                                                            {{ $t->transaction_type }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <!-- Proof -->
                                                <td>
                                                    @if ($t->proof)
                                                        <a href="{{ asset('assets/images/expense_proofs/' . $t->proof) }}"
                                                            target="_blank" class="btn btn-xs btn-outline-info" title="View Proof">
                                                            <i class="mdi mdi-eye"></i> View
                                                        </a>
                                                    @else
                                                        <span class="text-muted">—</span>
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
                                            <td colspan="9" class="text-center text-muted py-4">
                                                No transactions found.
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
