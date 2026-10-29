@extends('layout.master')
@section('title', 'Edit Client')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container">
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Edit Client</h4>
                    <a href="{{ route('client.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="mdi mdi-account-edit me-2 text-primary"></i>
                                    Update Client Information
                                </h5>
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
                                <form action="{{ route('client.update', $client->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="package_type" value="{{ old('package_type', $client->package_type ?? session('dashboard_package', 'hajj')) }}">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Full Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="name"
                                                    value="{{ old('name', $client->name) }}" class="form-control"
                                                    placeholder="Enter full name" required>
                                                @error('name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Company Name</label>
                                                <input type="text" name="company_name"
                                                    value="{{ old('company_name', $client->company_name) }}"
                                                    class="form-control" placeholder="Enter company name">
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Package</label>
                                            <select name="package_id" class="form-select">
                                                <option value="">Select Package</option>

                                                @foreach ($packages as $package)
                                                    <option value="{{ $package->id }}"
                                                        {{ old('package_id', $client->package_id) == $package->id ? 'selected' : '' }}>
                                                        {{ $package->name }} ({{ $package->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Phone Number</label>
                                                <input type="text" name="phone"
                                                    value="{{ old('phone', $client->phone) }}" class="form-control"
                                                    placeholder="+92 300 0000000">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Passport Number</label>
                                                <input type="text" name="passport_number"
                                                    value="{{ old('passport_number', $client->passport_number) }}"
                                                    class="form-control" placeholder="AA1234567">
                                                @error('passport_number')
                                                    <div class="text-danger fs-12 mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label mb-0">Passport Document</label>
                                                    @if ($client->passport_document)
                                                        <a href="{{ $client->passport_document_url }}" target="_blank" class="badge bg-success-subtle text-success border border-success text-decoration-none">
                                                            <i class="mdi mdi-eye me-1"></i> View Current File
                                                        </a>
                                                    @else
                                                        <small class="text-muted">No file uploaded</small>
                                                    @endif
                                                </div>
                                                <input type="file" name="passport_document" class="form-control @error('passport_document') is-invalid @enderror" accept=".pdf,image/*">
                                                <small class="text-muted fs-11">Upload new file to replace existing (PDF, JPG, PNG, Max 10MB)</small>
                                                @error('passport_document')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">CNIC Number</label>
                                                <input type="text" name="cnic"
                                                    value="{{ old('cnic', $client->cnic) }}" class="form-control"
                                                    placeholder="XXXXX-XXXXXXX-X">
                                                @error('cnic')
                                                    <div class="text-danger fs-12 mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label mb-0">CNIC Document</label>
                                                    @if ($client->cnic_document)
                                                        <a href="{{ $client->cnic_document_url }}" target="_blank" class="badge bg-success-subtle text-success border border-success text-decoration-none">
                                                            <i class="mdi mdi-eye me-1"></i> View Current File
                                                        </a>
                                                    @else
                                                        <small class="text-muted">No file uploaded</small>
                                                    @endif
                                                </div>
                                                <input type="file" name="cnic_document" class="form-control @error('cnic_document') is-invalid @enderror" accept=".pdf,image/*">
                                                <small class="text-muted fs-11">Upload new file to replace existing (PDF, JPG, PNG, Max 10MB)</small>
                                                @error('cnic_document')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email Address</label>
                                                <input type="email" name="email"
                                                    value="{{ old('email', $client->email) }}" class="form-control"
                                                    placeholder="client@example.com">
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="active"
                                                        {{ old('status', $client->status) == 'active' ? 'selected' : '' }}>
                                                        Active</option>
                                                    <option value="inactive"
                                                        {{ old('status', $client->status) == 'inactive' ? 'selected' : '' }}>
                                                        Inactive</option>
                                                </select>
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
                                                            <input type="number" step="0.01" name="opening_balance_pkr" id="opening_balance_pkr" class="form-control" value="{{ old('opening_balance_pkr', $client->opening_balance_pkr ?? '0.00') }}" placeholder="0.00">
                                                            <select name="opening_balance_pkr_type" id="opening_balance_pkr_type" class="form-select" style="max-width: 140px;">
                                                                <option value="debit" {{ old('opening_balance_pkr_type', $client->opening_balance_pkr_type ?? 'debit') == 'debit' ? 'selected' : '' }}>Debit (Receivable)</option>
                                                                <option value="credit" {{ old('opening_balance_pkr_type', $client->opening_balance_pkr_type ?? 'debit') == 'credit' ? 'selected' : '' }}>Credit (Advance)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fw-semibold">Rate of Exchange (ROE)</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white"><i class="mdi mdi-swap-horizontal"></i></span>
                                                            <input type="number" step="0.0001" name="opening_balance_roe" id="opening_balance_roe" class="form-control text-center fw-bold" value="{{ old('opening_balance_roe', $client->opening_balance_roe ?? '75.00') }}" placeholder="75.00">
                                                        </div>
                                                        <small class="text-muted d-block mt-1">1 SAR = X PKR</small>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="form-label fw-semibold">Opening Balance (SAR)</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white">SAR</span>
                                                            <input type="number" step="0.01" name="opening_balance_sar" id="opening_balance_sar" class="form-control" value="{{ old('opening_balance_sar', (($client->opening_balance_roe ?? 0) <= 1 ? '0.00' : ($client->opening_balance_sar ?? '0.00'))) }}" placeholder="0.00">
                                                            <select name="opening_balance_sar_type" id="opening_balance_sar_type" class="form-select" style="max-width: 140px;">
                                                                <option value="debit" {{ old('opening_balance_sar_type', $client->opening_balance_sar_type ?? 'debit') == 'debit' ? 'selected' : '' }}>Debit (Receivable)</option>
                                                                <option value="credit" {{ old('opening_balance_sar_type', $client->opening_balance_sar_type ?? 'debit') == 'credit' ? 'selected' : '' }}>Credit (Advance)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-12 text-end">
                                            <a href="{{ route('client.index') }}" class="btn btn-secondary">
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-success px-4">
                                                <i class="mdi mdi-content-save me-1"></i> Update Client
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
