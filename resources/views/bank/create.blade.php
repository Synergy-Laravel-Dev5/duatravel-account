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

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Opening Balance (PKR)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white fw-bold">PKR</span>
                                        <input type="number" step="0.01" name="opening_balance_pkr" id="opening_balance_pkr" class="form-control fw-bold" placeholder="0.00" value="{{ old('opening_balance_pkr', 0) }}">
                                    </div>
                                    <small class="text-muted fs-11">Bank opening balance is maintained in PKR only.</small>
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
@endsection
