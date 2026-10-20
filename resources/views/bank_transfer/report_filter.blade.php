@extends('layout.master')

@section('title', 'Bank Transfer Report Filter')

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
                                        <i class="mdi mdi-bank text-primary me-2"></i> Bank Transaction & Statement Reports Filter
                                    </h4>
                                    <p class="text-muted fs-13 mb-0 mt-1">Filter and generate comprehensive bank statements, deposits, payments, and inter-bank transfers.</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('bank-transfer.index') }}" class="btn btn-secondary btn-sm">
                                        <i class="mdi mdi-arrow-left me-1"></i> Back to Transfers
                                    </a>
                                </div>
                            </div>

                            <form action="{{ route('bank-transfer.report') }}" method="GET">
                                <div class="row g-3">

                                    {{-- Single Bank Filter (Either From or To) --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold fs-13">
                                            <i class="mdi mdi-bank me-1 text-primary"></i> Bank (All Activity)
                                        </label>
                                        <select name="bank_id" class="form-select form-select-sm" id="bank_id_select">
                                            <option value="">-- All Banks (Any Activity) --</option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank->id }}" {{ request('bank_id') == $bank->id ? 'selected' : '' }}>
                                                    {{ $bank->name }} {{ !empty($bank->account_number) ? '(' . $bank->account_number . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted fs-11">Shows all transfers sent or received by this bank</small>
                                    </div>

                                    {{-- Specific Sender Bank --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold fs-13">
                                            <i class="mdi mdi-arrow-up-circle me-1 text-danger"></i> Source Bank (Transferred From)
                                        </label>
                                        <select name="from_bank_id" class="form-select form-select-sm">
                                            <option value="">-- Any Source Bank --</option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank->id }}" {{ request('from_bank_id') == $bank->id ? 'selected' : '' }}>
                                                    {{ $bank->name }} {{ !empty($bank->account_number) ? '(' . $bank->account_number . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted fs-11">Filter by originating bank account</small>
                                    </div>

                                    {{-- Specific Destination Bank --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold fs-13">
                                            <i class="mdi mdi-arrow-down-circle me-1 text-success"></i> Destination Bank (Transferred To)
                                        </label>
                                        <select name="to_bank_id" class="form-select form-select-sm">
                                            <option value="">-- Any Destination Bank --</option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank->id }}" {{ request('to_bank_id') == $bank->id ? 'selected' : '' }}>
                                                    {{ $bank->name }} {{ !empty($bank->account_number) ? '(' . $bank->account_number . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted fs-11">Filter by destination bank account</small>
                                    </div>

                                    {{-- From Date --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold fs-13">From Date</label>
                                        <input type="date" name="from_date" class="form-control form-control-sm"
                                            value="{{ request('from_date') }}">
                                    </div>

                                    {{-- To Date --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold fs-13">To Date</label>
                                        <input type="date" name="to_date" class="form-control form-control-sm"
                                            value="{{ request('to_date') }}">
                                    </div>

                                    {{-- Min Amount --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold fs-13">Min Amount (PKR)</label>
                                        <input type="number" step="0.01" name="min_amount" class="form-control form-control-sm"
                                            placeholder="Min PKR" value="{{ request('min_amount') }}">
                                    </div>

                                    {{-- Max Amount --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold fs-13">Max Amount (PKR)</label>
                                        <input type="number" step="0.01" name="max_amount" class="form-control form-control-sm"
                                            placeholder="Max PKR" value="{{ request('max_amount') }}">
                                    </div>

                                    {{-- Activity Type Filter --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-13">
                                            <i class="mdi mdi-swap-vertical text-info me-1"></i> Activity Type
                                        </label>
                                        <select name="activity_type" class="form-select form-select-sm">
                                            <option value="all" {{ request('activity_type') == 'all' ? 'selected' : '' }}>-- All Activity (Deposits, Withdrawals, Transfers) --</option>
                                            <option value="deposit" {{ request('activity_type') == 'deposit' ? 'selected' : '' }}>Deposits Only (Client Receipts / Cr)</option>
                                            <option value="withdrawal" {{ request('activity_type') == 'withdrawal' ? 'selected' : '' }}>Withdrawals Only (Supplier/Expense Payments / Dr)</option>
                                            <option value="transfer" {{ request('activity_type') == 'transfer' ? 'selected' : '' }}>Internal Bank Transfers Only</option>
                                        </select>
                                    </div>

                                    {{-- Search Description --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-13">Search Description / Reference Keyword</label>
                                        <input type="text" name="search" class="form-control form-control-sm"
                                            placeholder="Search by note, cheque number, payee, reference, etc." value="{{ request('search') }}">
                                    </div>

                                    {{-- Quick Date Presets --}}
                                    <div class="col-12 mt-2">
                                        <div class="p-3 bg-light rounded-2 border d-flex flex-wrap align-items-center gap-2">
                                            <span class="fs-12 fw-bold text-muted me-2"><i class="mdi mdi-clock-fast me-1"></i> Quick Presets:</span>
                                            <button type="button" class="btn btn-outline-secondary btn-xs preset-btn" data-days="0">Today</button>
                                            <button type="button" class="btn btn-outline-secondary btn-xs preset-btn" data-days="7">Last 7 Days</button>
                                            <button type="button" class="btn btn-outline-secondary btn-xs preset-btn" data-days="30">Last 30 Days</button>
                                            <button type="button" class="btn btn-outline-secondary btn-xs preset-btn" data-type="this_month">This Month</button>
                                            <button type="button" class="btn btn-outline-secondary btn-xs preset-btn" data-type="this_year">This Year</button>
                                            <button type="button" class="btn btn-outline-secondary btn-xs preset-btn" data-type="all">Clear Dates</button>
                                        </div>
                                    </div>

                                    {{-- Submit Buttons --}}
                                    <div class="col-12 text-end mt-4 pt-2 border-top">
                                        <a href="{{ route('bank-transfer.report') }}" class="btn btn-outline-dark btn-sm px-3 me-2">
                                            <i class="mdi mdi-eye me-1"></i> View All Transfers
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                                            <i class="mdi mdi-filter me-1"></i> Generate Report
                                        </button>
                                    </div>

                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const fromInput = document.querySelector('input[name="from_date"]');
            const toInput = document.querySelector('input[name="to_date"]');
            const now = new Date();
            const formatDate = (d) => d.toISOString().split('T')[0];

            if (this.dataset.days !== undefined) {
                const days = parseInt(this.dataset.days);
                const from = new Date();
                from.setDate(now.getDate() - days);
                fromInput.value = formatDate(from);
                toInput.value = formatDate(now);
            } else if (this.dataset.type === 'this_month') {
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                fromInput.value = formatDate(firstDay);
                toInput.value = formatDate(now);
            } else if (this.dataset.type === 'this_year') {
                const firstDayYear = new Date(now.getFullYear(), 0, 1);
                fromInput.value = formatDate(firstDayYear);
                toInput.value = formatDate(now);
            } else if (this.dataset.type === 'all') {
                fromInput.value = '';
                toInput.value = '';
            }
        });
    });
</script>
@endsection
