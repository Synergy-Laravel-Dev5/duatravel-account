@extends('layout.master')
@section('title', 'Add Client')
@section('header-title', 'Add Client')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container">
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Add New Client</h4>
                    <a href="{{ route('client.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Client Information</h5>
                            </div>
                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $e)
                                                <li>{{ $e }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <form action="{{ route('client.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="package_type" value="{{ session('dashboard_package', 'hajj') }}">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Full Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="name" value="{{ old('name') }}"
                                                    class="form-control" placeholder="Enter full name">
                                                @error('name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Company Name</label>
                                                <input type="text" name="company_name" value="{{ old('company_name') }}"
                                                    class="form-control" placeholder="Enter company name">
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Package</label>
                                            <select name="package_id" class="form-select">
                                                <option value="">Select Package</option>

                                                @foreach ($packages as $package)
                                                    <option value="{{ $package->id }}"
                                                        {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                                        {{ $package->name }} ({{ $package->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Phone Number</label>
                                                <input type="text" name="phone" value="{{ old('phone') }}"
                                                    class="form-control" placeholder="+92 300 0000000">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Passport Number</label>
                                                <input type="text" name="passport_number"
                                                    value="{{ old('passport_number') }}" class="form-control"
                                                    placeholder="AA1234567">
                                                @error('passport_number')
                                                    <div class="text-danger fs-12 mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label d-flex justify-content-between">
                                                    <span>Passport Document</span>
                                                    <small class="text-muted">PDF, JPG, PNG (Max: 10MB)</small>
                                                </label>
                                                <input type="file" name="passport_document" class="form-control @error('passport_document') is-invalid @enderror" accept=".pdf,image/*">
                                                @error('passport_document')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">CNIC Number</label>
                                                <input type="text" name="cnic" value="{{ old('cnic') }}"
                                                    class="form-control" placeholder="XXXXX-XXXXXXX-X">
                                                @error('cnic')
                                                    <div class="text-danger fs-12 mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label d-flex justify-content-between">
                                                    <span>CNIC Document</span>
                                                    <small class="text-muted">PDF, JPG, PNG (Max: 10MB)</small>
                                                </label>
                                                <input type="file" name="cnic_document" class="form-control @error('cnic_document') is-invalid @enderror" accept=".pdf,image/*">
                                                @error('cnic_document')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email Address</label>
                                                <input type="email" name="email" value="{{ old('email') }}"
                                                    class="form-control" placeholder="client@example.com">
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="active"
                                                        {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active
                                                    </option>
                                                    <option value="inactive"
                                                        {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive
                                                    </option>
                                                </select>
                                                @error('status')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Opening Balances (Multi-Currency: PKR and SAR) --}}
                                        <div class="col-12">
                                            <div class="p-3 border rounded bg-light mb-3">
                                                <h6 class="text-primary fw-bold mb-2">
                                                    <i class="mdi mdi-cash-multiple me-1"></i> Client Opening Balance (Multi-Currency)
                                                </h6>
                                                <p class="text-muted small mb-3">Enter opening balance in PKR and conversion ROE. The SAR equivalent will auto-calculate based on ROE.</p>
                                                <div class="row g-3 align-items-end">
                                                    <div class="col-md-5">
                                                        <label class="form-label fw-semibold">Opening Balance (PKR)</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white">PKR</span>
                                                            <input type="number" step="0.01" name="opening_balance_pkr" id="opening_balance_pkr" class="form-control" value="{{ old('opening_balance_pkr', '0.00') }}" placeholder="0.00">
                                                            <select name="opening_balance_pkr_type" id="opening_balance_pkr_type" class="form-select" style="max-width: 140px;">
                                                                <option value="debit" {{ old('opening_balance_pkr_type', 'debit') == 'debit' ? 'selected' : '' }}>Debit (Receivable)</option>
                                                                <option value="credit" {{ old('opening_balance_pkr_type') == 'credit' ? 'selected' : '' }}>Credit (Advance)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fw-semibold">Rate of Exchange (ROE)</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white"><i class="mdi mdi-swap-horizontal"></i></span>
                                                            <input type="number" step="0.0001" name="opening_balance_roe" id="opening_balance_roe" class="form-control text-center fw-bold" value="{{ old('opening_balance_roe', '75.00') }}" placeholder="75.00">
                                                        </div>
                                                        <small class="text-muted d-block mt-1">1 SAR = X PKR</small>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="form-label fw-semibold">Opening Balance (SAR)</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white">SAR</span>
                                                            <input type="number" step="0.01" name="opening_balance_sar" id="opening_balance_sar" class="form-control" value="{{ old('opening_balance_sar', '0.00') }}" placeholder="0.00">
                                                            <select name="opening_balance_sar_type" id="opening_balance_sar_type" class="form-select" style="max-width: 140px;">
                                                                <option value="debit" {{ old('opening_balance_sar_type', 'debit') == 'debit' ? 'selected' : '' }}>Debit (Receivable)</option>
                                                                <option value="credit" {{ old('opening_balance_sar_type') == 'credit' ? 'selected' : '' }}>Credit (Advance)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary px-4">
                                                Save Client
                                            </button>
                                            <a href="{{ route('client.index') }}" class="btn btn-secondary">
                                                Cancel
                                            </a>
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
        document.addEventListener('DOMContentLoaded', function () {
            const pkrInput = document.getElementById('opening_balance_pkr');
            const roeInput = document.getElementById('opening_balance_roe');
            const sarInput = document.getElementById('opening_balance_sar');
            const pkrType = document.getElementById('opening_balance_pkr_type');
            const sarType = document.getElementById('opening_balance_sar_type');

            function calculateSarFromPkr() {
                const pkr = parseFloat(pkrInput.value) || 0;
                const roe = parseFloat(roeInput.value) || 0;
                if (roe > 1) {
                    sarInput.value = (pkr / roe).toFixed(2);
                    if (pkrType && sarType) {
                        sarType.value = pkrType.value;
                    }
                } else {
                    sarInput.value = '0.00';
                }
            }

            if (pkrInput && roeInput && sarInput) {
                pkrInput.addEventListener('input', calculateSarFromPkr);
                roeInput.addEventListener('input', calculateSarFromPkr);
                if (pkrType && sarType) {
                    pkrType.addEventListener('change', function () {
                        sarType.value = pkrType.value;
                    });
                }
            }
        });
    </script>
@endsection
