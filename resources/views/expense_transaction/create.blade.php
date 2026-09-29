@extends('layout.master')

@section('title', 'Add Expense Transaction')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container">

                <!-- TOP BAR -->
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Add New Expense Transaction</h4>
                    <a href="{{ route('expense.transaction.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-header">
                                <h5 class="mb-0">Transaction Information</h5>
                            </div>

                            <div class="card-body">

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form action="{{ route('expense.transaction.store') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <div class="row">

                                        <!-- EXPENSE HEAD -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Expense Head <span
                                                        class="text-danger">*</span></label>
                                                <select name="expense_id" class="form-control" required>
                                                    <option value="">Select Expense</option>
                                                    @foreach ($expenses as $expense)
                                                        <option value="{{ $expense->id }}"
                                                            {{ old('expense_id') == $expense->id ? 'selected' : '' }}>
                                                            {{ $expense->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('expense_id')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- HAJJ / UMRAH TYPE -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                                <select name="hajj_umrah" class="form-control" required>
                                                    <option value="">Select Type</option>
                                                    <option value="hajj"
                                                        {{ old('hajj_umrah') == 'hajj' ? 'selected' : '' }}>Hajj</option>
                                                    <option value="umrah"
                                                        {{ old('hajj_umrah') == 'umrah' ? 'selected' : '' }}>Umrah</option>
                                                </select>
                                                @error('hajj_umrah')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- YEAR -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Year <span class="text-danger">*</span></label>
                                                <input type="number" name="year" value="{{ old('year', date('Y')) }}"
                                                    class="form-control" placeholder="e.g. 2025" min="2000"
                                                    max="2100" required>
                                                @error('year')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- AMOUNT -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                                <input type="number" name="amount" value="{{ old('amount') }}"
                                                    class="form-control" placeholder="Enter amount" required>
                                                @error('amount')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- PAYMENT TYPE -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Payment Type <span
                                                        class="text-danger">*</span></label>
                                                <select name="payment_type" class="form-control" required>
                                                    <option value="">Select Payment Type</option>
                                                    <option value="cash"
                                                        {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                                                    <option value="bank"
                                                        {{ old('payment_type') == 'bank' ? 'selected' : '' }}>Bank</option>
                                                    <option value="online"
                                                        {{ old('payment_type') == 'online' ? 'selected' : '' }}>Online
                                                    </option>
                                                    <option value="ewallet"
                                                        {{ old('payment_type') == 'ewallet' ? 'selected' : '' }}>E-Wallet
                                                    </option>
                                                </select>
                                                @error('payment_type')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- CURRENCY -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Currency <span
                                                        class="text-danger">*</span></label>
                                                <select name="currency" id="currency_select" class="form-control" required>
                                                    <option value="">Select Currency</option>
                                                    <option value="PKR"
                                                        {{ old('currency', 'PKR') == 'PKR' ? 'selected' : '' }}>PKR
                                                    </option>
                                                    <option value="SAR"
                                                        {{ old('currency') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                                </select>
                                                @error('currency')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- EXCHANGE RATE -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Exchange Rate <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" step="0.01" name="exchange_rate"
                                                    id="exchange_rate_input" value="{{ old('exchange_rate', 1) }}"
                                                    class="form-control" placeholder="Enter SAR rate" required>
                                                @error('exchange_rate')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- DATE -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                                <input type="date" name="date"
                                                    value="{{ old('date', date('Y-m-d')) }}" class="form-control"
                                                    required>
                                                @error('date')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- REFERENCE -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Reference No</label>
                                                <input type="text" name="reference_no"
                                                    value="{{ old('reference_no') }}" class="form-control"
                                                    placeholder="Enter reference">
                                            </div>
                                        </div>

                                        <!-- PROOF -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Proof (Slip / Screenshot)</label>
                                                <input type="file" name="proof" class="form-control">
                                            </div>
                                        </div>

                                        <!-- DESCRIPTION -->
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="3" placeholder="Optional details">{{ old('description') }}</textarea>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary px-4">Save Transaction</button>
                                        <a href="{{ route('expense.transaction.index') }}"
                                            class="btn btn-secondary">Cancel</a>
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
        document.addEventListener('DOMContentLoaded', function() {
            const currencySelect = document.getElementById('currency_select');
            const rateInput = document.getElementById('exchange_rate_input');

            function toggleRateField() {
                if (currencySelect.value === 'SAR') {
                    rateInput.removeAttribute('readonly');
                    if (rateInput.value == 1) {
                        rateInput.value = '';
                    }
                } else {
                    rateInput.value = 1;
                    rateInput.setAttribute('readonly', true);
                }
            }

            currencySelect.addEventListener('change', toggleRateField);
            toggleRateField();
        });
    </script>

@endsection
