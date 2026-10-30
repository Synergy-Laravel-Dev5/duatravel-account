@extends('layout.master')

@section('title', 'Transaction Reports')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mt-3">
                            <div class="card-body p-4">

                                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                                    <div>
                                        <h4 class="fw-bold m-0 text-dark">
                                            <i class="mdi mdi-file-chart text-danger me-1"></i> Transaction & Expense Reports Filter
                                        </h4>
                                        <p class="text-muted fs-13 mb-0 mt-1">Generate multi-criteria reports for expenses, suppliers, clients, and staff transactions.</p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('reports.hub', ['tab' => 'expense']) }}" class="btn btn-primary btn-sm">
                                            <i class="mdi mdi-layers-outline me-1"></i> All-In-One Reports Hub
                                        </a>
                                        <a href="{{ route('expense.transaction.index') }}" class="btn btn-secondary btn-sm">
                                            <i class="mdi mdi-arrow-left me-1"></i> Back to Transactions
                                        </a>
                                    </div>
                                </div>

                                <form action="{{ route('expense.transaction.report') }}" method="POST">
                                    @csrf

                                    <div class="row g-3">

                                        {{-- Package Type --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Package Type</label>
                                            <select name="hajj_umrah" class="form-select form-select-sm">
                                                <option value="all" {{ old('hajj_umrah', session('dashboard_package', 'all')) == 'all' ? 'selected' : '' }}>-- All Seasons (Hajj & Umrah) --</option>
                                                <option value="umrah" {{ old('hajj_umrah', session('dashboard_package')) == 'umrah' ? 'selected' : '' }}>Umrah</option>
                                                <option value="hajj" {{ old('hajj_umrah', session('dashboard_package')) == 'hajj' ? 'selected' : '' }}>Hajj</option>
                                            </select>
                                            @error('hajj_umrah')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Year --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Year</label>
                                            <input type="number" name="year" class="form-control form-control-sm"
                                                placeholder="e.g. 2026 (Leave blank for all)" min="2000" max="2100"
                                                value="{{ old('year', session('dashboard_year', \Carbon\Carbon::now()->year)) }}">
                                            @error('year')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Payee / Transaction Type --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Category / Payee Type</label>
                                            <select name="payee_type" class="form-select form-select-sm">
                                                <option value="">-- All Categories --</option>
                                                <option value="supplier" {{ old('payee_type') == 'supplier' ? 'selected' : '' }}>Supplier Payment</option>
                                                <option value="client" {{ old('payee_type') == 'client' ? 'selected' : '' }}>Client Transaction</option>
                                                <option value="staff" {{ old('payee_type') == 'staff' ? 'selected' : '' }}>Staff Salary / Advance</option>
                                                <option value="expense" {{ old('payee_type') == 'expense' ? 'selected' : '' }}>Expense Head</option>
                                                <option value="self" {{ old('payee_type') == 'self' ? 'selected' : '' }}>Self / Internal Transfer</option>
                                            </select>
                                        </div>

                                        {{-- Bank --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Bank Account</label>
                                            <select name="bank_id" class="form-select form-select-sm">
                                                <option value="">-- All Banks --</option>
                                                @foreach ($banks as $bank)
                                                    <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                                                        {{ $bank->name }}{{ !empty($bank->account_name) ? ' (' . $bank->account_name . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Supplier --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Supplier</label>
                                            <select name="supplier_id" class="form-select form-select-sm">
                                                <option value="">-- All Suppliers --</option>
                                                @foreach ($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                        {{ $supplier->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Client --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Client</label>
                                            <select name="client_id" class="form-select form-select-sm">
                                                <option value="">-- All Clients --</option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                                        {{ $client->client_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Expense Category / Head --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Expense Head</label>
                                            <select name="expense_id" class="form-select form-select-sm">
                                                <option value="">-- All Expense Heads --</option>
                                                @foreach ($expenses as $expense)
                                                    <option value="{{ $expense->id }}" {{ old('expense_id') == $expense->id ? 'selected' : '' }}>
                                                        {{ $expense->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Staff Member --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Staff Member</label>
                                            <select name="staff_id" class="form-select form-select-sm">
                                                <option value="">-- All Staff Members --</option>
                                                @foreach ($staffMembers as $st)
                                                    <option value="{{ $st->id }}" {{ old('staff_id') == $st->id ? 'selected' : '' }}>
                                                        {{ $st->first_name }} {{ $st->last_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Payment Method --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Payment Method</label>
                                            <select name="payment_type" class="form-select form-select-sm">
                                                <option value="">-- All Methods --</option>
                                                <option value="bank" {{ old('payment_type') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                                <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                                                <option value="cheque" {{ old('payment_type') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                                <option value="online" {{ old('payment_type') == 'online' ? 'selected' : '' }}>Online / UPI</option>
                                            </select>
                                        </div>

                                        {{-- Currency --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Currency</label>
                                            <select name="currency" class="form-select form-select-sm">
                                                <option value="">-- All Currencies --</option>
                                                <option value="PKR" {{ old('currency') == 'PKR' ? 'selected' : '' }}>PKR</option>
                                                <option value="SAR" {{ old('currency') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                            </select>
                                        </div>

                                        {{-- From Date --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">From Date</label>
                                            <input type="date" name="from_date" class="form-control form-control-sm"
                                                value="{{ old('from_date') }}">
                                        </div>

                                        {{-- To Date --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">To Date</label>
                                            <input type="date" name="to_date" class="form-control form-control-sm"
                                                value="{{ old('to_date') }}">
                                        </div>

                                    </div>

                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary px-5">
                                            <i class="mdi mdi-filter me-1"></i> Generate Report
                                        </button>
                                        <a href="{{ route('expense.transaction.index') }}" class="btn btn-secondary px-4">
                                            Cancel
                                        </a>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
