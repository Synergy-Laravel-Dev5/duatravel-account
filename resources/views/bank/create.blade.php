@extends('layout.master')
@section('title', 'Add Bank')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Add Bank</h4>
                    <a href="{{ route('bank.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('bank.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Account / Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Meezan Bank / Main Cash Counter" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Type <span class="text-danger">*</span></label>
                                    <select name="account_type" class="form-select" required>
                                        <option value="bank" {{ old('account_type', 'bank') == 'bank' ? 'selected' : '' }}>Bank Account</option>
                                        <option value="cash" {{ old('account_type') == 'cash' ? 'selected' : '' }}>Cash Account (Cash in Hand)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Account Title / Holder Name</label>
                                    <input type="text" name="account_name" class="form-control" placeholder="e.g. Dua Travels & Tours" value="{{ old('account_name') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Account Number / IBAN</label>
                                    <input type="text" name="account_number" class="form-control" placeholder="e.g. 01010101010101" value="{{ old('account_number') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Branch / Counter</label>
                                    <input type="text" name="branch" class="form-control" placeholder="e.g. Clifton Branch, Karachi" value="{{ old('branch') }}">
                                </div>

                                {{-- Multi-Currency Opening Balance (PKR & SAR) --}}
                                <div class="col-12">
                                    <div class="p-3 border rounded bg-light">
                                        <h6 class="text-primary fw-bold mb-2">
                                            <i class="mdi mdi-cash-multiple me-1"></i> Multi-Currency Opening Balance (PKR & SAR)
                                        </h6>
                                        <p class="text-muted small mb-3">Enter opening balance in PKR and conversion ROE. The SAR equivalent will auto-calculate based on ROE.</p>
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold">Opening Balance (PKR)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white">PKR</span>
                                                    <input type="number" step="0.01" name="opening_balance_pkr" id="opening_balance_pkr" class="form-control" placeholder="0.00" value="{{ old('opening_balance_pkr', 0) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Rate of Exchange (ROE)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white"><i class="mdi mdi-swap-horizontal"></i></span>
                                                    <input type="number" step="0.0001" name="opening_balance_roe" id="opening_balance_roe" class="form-control text-center fw-bold" placeholder="75.00" value="{{ old('opening_balance_roe', '75.00') }}">
                                                </div>
                                                <small class="text-muted d-block mt-1">1 SAR = X PKR</small>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold">Opening Balance (SAR)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white">SAR</span>
                                                    <input type="number" step="0.01" name="opening_balance_sar" id="opening_balance_sar" class="form-control" placeholder="0.00" value="{{ old('opening_balance_sar', 0) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save me-1"></i> Save Account</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pkrInput = document.getElementById('opening_balance_pkr');
            const roeInput = document.getElementById('opening_balance_roe');
            const sarInput = document.getElementById('opening_balance_sar');

            function calculateSarFromPkr() {
                const pkr = parseFloat(pkrInput.value) || 0;
                const roe = parseFloat(roeInput.value) || 75;
                if (roe > 0) {
                    sarInput.value = (pkr / roe).toFixed(2);
                }
            }

            if (pkrInput && roeInput && sarInput) {
                pkrInput.addEventListener('input', calculateSarFromPkr);
                roeInput.addEventListener('input', calculateSarFromPkr);
            }
        });
    </script>
@endsection
