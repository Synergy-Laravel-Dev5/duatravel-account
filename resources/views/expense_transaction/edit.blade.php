@extends('layout.master')

@section('title', 'Edit Expense Transaction')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container">

                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Edit Expense Transaction</h4>
                    <a href="{{ route('expense.transaction.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-header">
                                <h5 class="mb-0">Update Transaction</h5>
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

                                <form action="{{ route('expense.transaction.update', $transaction->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">

                                        <!-- EXPENSE HEAD -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Expense Head <span
                                                        class="text-danger">*</span></label>
                                                <select name="expense_id" class="form-control" required>
                                                    @foreach ($expenses as $expense)
                                                        <option value="{{ $expense->id }}"
                                                            {{ $transaction->expense_id == $expense->id ? 'selected' : '' }}>
                                                            {{ $expense->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <!-- HAJJ / UMRAH TYPE -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                                <select name="hajj_umrah" class="form-control" required>
                                                    <option value="hajj"
                                                        {{ $transaction->hajj_umrah == 'hajj' ? 'selected' : '' }}>Hajj
                                                    </option>
                                                    <option value="umrah"
                                                        {{ $transaction->hajj_umrah == 'umrah' ? 'selected' : '' }}>Umrah
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- YEAR -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Year <span class="text-danger">*</span></label>
                                                <input type="number" name="year"
                                                    value="{{ old('year', $transaction->year) }}" class="form-control"
                                                    min="2000" max="2100" required>
                                            </div>
                                        </div>

                                        <!-- AMOUNT -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                                <input type="number" name="amount"
                                                    value="{{ old('amount', $transaction->amount) }}" class="form-control"
                                                    required>
                                            </div>
                                        </div>

                                        <!-- PAYMENT TYPE -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Payment Type <span
                                                        class="text-danger">*</span></label>
                                                <select name="payment_type" class="form-control" required>
                                                    <option value="cash"
                                                        {{ $transaction->payment_type == 'cash' ? 'selected' : '' }}>Cash
                                                    </option>
                                                    <option value="bank"
                                                        {{ $transaction->payment_type == 'bank' ? 'selected' : '' }}>Bank
                                                    </option>
                                                    <option value="online"
                                                        {{ $transaction->payment_type == 'online' ? 'selected' : '' }}>
                                                        Online
                                                    </option>
                                                    <option value="ewallet"
                                                        {{ $transaction->payment_type == 'ewallet' ? 'selected' : '' }}>
                                                        E-Wallet
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- CURRENCY -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Currency <span
                                                        class="text-danger">*</span></label>
                                                <select name="currency" id="currency_select" class="form-control" required>
                                                    <option value="PKR"
                                                        {{ old('currency', $transaction->currency) == 'PKR' ? 'selected' : '' }}>
                                                        PKR</option>
                                                    <option value="SAR"
                                                        {{ old('currency', $transaction->currency) == 'SAR' ? 'selected' : '' }}>
                                                        SAR</option>
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
                                                    id="exchange_rate_input"
                                                    value="{{ old('exchange_rate', $transaction->exchange_rate) }}"
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
                                                    value="{{ old('date', $transaction->date) }}" class="form-control"
                                                    required>
                                            </div>
                                        </div>

                                        <!-- REFERENCE -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Reference No</label>
                                                <input type="text" name="reference_no"
                                                    value="{{ old('reference_no', $transaction->reference_no) }}"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <!-- PROOF -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Proof (Slip / Screenshot)</label>
                                                <input type="file" name="proof" class="form-control">
                                                @if ($transaction->proof)
                                                    <small class="text-muted d-block mt-1">
                                                        Current Proof:
                                                        <a href="{{ asset('assets/images/expense_proofs/' . $transaction->proof) }}"
                                                            target="_blank">View File</a>
                                                    </small>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- DESCRIPTION -->
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="3">{{ old('description', $transaction->description) }}</textarea>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-primary px-4">Update Transaction</button>
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
