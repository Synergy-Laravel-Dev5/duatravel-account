@extends('layout.master')

@section('title', 'Expense Transaction Report')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-4">

                                <h4 class="fw-bold mb-4">Expense Transaction Report</h4>

                                <form action="{{ route('expense.transaction.report') }}" method="POST">
                                    @csrf

                                    <div class="row">

                                        {{-- Package Type --}}
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-medium">Package Type <span
                                                    class="text-danger">*</span></label>
                                            <select name="hajj_umrah" class="form-select" required>
                                                <option value="">-- Select Package --</option>
                                                <option value="hajj" {{ old('hajj_umrah') == 'hajj' ? 'selected' : '' }}>
                                                    Hajj</option>
                                                <option value="umrah" {{ old('hajj_umrah') == 'umrah' ? 'selected' : '' }}>
                                                    Umrah</option>
                                            </select>
                                            @error('hajj_umrah')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Year --}}
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-medium">Year <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="year" class="form-control"
                                                placeholder="e.g. 2025" min="2000" max="2100"
                                                value="{{ old('year', \Carbon\Carbon::now()->year) }}" required>
                                            @error('year')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Expense Category --}}
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-medium">Expense Category</label>
                                            <select name="expense_id" class="form-select">
                                                <option value="">-- All Categories --</option>
                                                @foreach ($expenses as $expense)
                                                    <option value="{{ $expense->id }}"
                                                        {{ old('expense_id') == $expense->id ? 'selected' : '' }}>
                                                        {{ $expense->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Payment Method --}}
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-medium">Payment Method</label>
                                            <select name="payment_type" class="form-select">
                                                <option value="">-- All Methods --</option>
                                                <option value="cash"
                                                    {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                                                <option value="bank"
                                                    {{ old('payment_type') == 'bank' ? 'selected' : '' }}>Bank Transfer
                                                </option>
                                                <option value="online"
                                                    {{ old('payment_type') == 'online' ? 'selected' : '' }}>Online
                                                </option>
                                                <option value="ewallet"
                                                    {{ old('payment_type') == 'ewallet' ? 'selected' : '' }}>E-Wallet
                                                </option>
                                            </select>
                                        </div>

                                        {{-- Currency --}}
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-medium">Currency</label>
                                            <select name="currency" class="form-select">
                                                <option value="">-- All Currencies --</option>
                                                <option value="PKR" {{ old('currency') == 'PKR' ? 'selected' : '' }}>PKR
                                                </option>
                                                <option value="SAR" {{ old('currency') == 'SAR' ? 'selected' : '' }}>SAR
                                                </option>
                                            </select>
                                            @error('currency')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- From Date --}}
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-medium">From Date</label>
                                            <input type="date" name="from_date" class="form-control"
                                                value="{{ old('from_date') }}">
                                        </div>

                                        {{-- To Date --}}
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-medium">To Date</label>
                                            <input type="date" name="to_date" class="form-control"
                                                value="{{ old('to_date') }}">
                                        </div>

                                    </div>

                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary px-5">Generate Report</button>
                                        <a href="{{ route('expense.transaction.index') }}"
                                            class="btn btn-secondary px-4">Back</a>
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
