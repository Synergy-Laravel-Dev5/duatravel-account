@extends('layout.master')

@section('title', 'Edit Transaction')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- TOP BAR -->
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Edit Transaction</h4>
                    <a href="{{ route('transaction.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back
                    </a>
                </div>

                <!-- ERRORS -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">

                        <form action="{{ route('transaction.update', $transaction->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">

                                @php
                                    $currentBookingFor = old(
                                        'booking_for',
                                        $transaction->company_id ? 'company' : 'client',
                                    );
                                @endphp

                                <!-- Booking For -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Booking For <span
                                            class="text-danger">*</span></label>
                                    <select name="booking_for" id="booking_for"
                                        class="form-select @error('booking_for') is-invalid @enderror" required>
                                        <option value="client" {{ $currentBookingFor == 'client' ? 'selected' : '' }}>
                                            Client</option>
                                        <option value="company" {{ $currentBookingFor == 'company' ? 'selected' : '' }}>
                                            Company</option>
                                    </select>
                                    @error('booking_for')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Client -->
                                <div class="col-md-6 mb-3" id="client_wrapper">
                                    <label class="form-label fw-medium">Client <span class="text-danger">*</span></label>
                                    <select name="client_id" id="client_id"
                                        class="form-select @error('client_id') is-invalid @enderror">
                                        <option value="">-- Select Client --</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}"
                                                {{ old('client_id', $transaction->client_id) == $client->id ? 'selected' : '' }}>
                                                {{ $client->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Company -->
                                <div class="col-md-6 mb-3 d-none" id="company_wrapper">
                                    <label class="form-label fw-medium">Company <span class="text-danger">*</span></label>
                                    <select name="company_id" id="company_id"
                                        class="form-select @error('company_id') is-invalid @enderror">
                                        <option value="">-- Select Company --</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ old('company_id', $transaction->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->company_name ?? $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Booking Selector: NOW VISIBLE, user must manually pick which booking this payment belongs to -->
                                <div class="col-md-6 mb-3" id="booking_wrapper">
                                    <label class="form-label fw-medium">
                                        Select Booking <span class="text-danger">*</span>
                                        <small class="text-muted d-block">Choose the specific booking this payment is
                                            for</small>
                                    </label>
                                    <select name="booking_id" id="booking_id"
                                        class="form-select @error('booking_id') is-invalid @enderror" required>
                                        <option value="">-- Select Client/Company first --</option>
                                    </select>
                                    @error('booking_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Hidden source list of all bookings, used by JS to build the visible dropdown above -->
                                <div style="display:none;">
                                    <select id="all_bookings_source">
                                        @foreach ($bookings as $booking)
                                            <option value="{{ $booking->id }}" data-client-id="{{ $booking->client_id }}"
                                                data-company-id="{{ $booking->company_id }}"
                                                data-total="{{ $booking->total_amount }}"
                                                data-balance="{{ $booking->balance }}"
                                                data-package="{{ $booking->package_type }} {{ $booking->package_year }}">
                                                #{{ $booking->id }} — {{ $booking->package_type }}
                                                {{ $booking->package_year }} | Total:
                                                {{ number_format($booking->total_amount) }} | Balance:
                                                {{ number_format($booking->balance) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Payment Type -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Payment Type <span
                                            class="text-danger">*</span></label>
                                    <select name="payment_type"
                                        class="form-select @error('payment_type') is-invalid @enderror" required>
                                        <option value="">-- Select --</option>
                                        <option value="cash"
                                            {{ old('payment_type', $transaction->payment_type) == 'cash' ? 'selected' : '' }}>
                                            Cash</option>
                                        <option value="bank_transfer"
                                            {{ old('payment_type', $transaction->payment_type) == 'bank_transfer' ? 'selected' : '' }}>
                                            Bank Transfer</option>
                                        <option value="cheque"
                                            {{ old('payment_type', $transaction->payment_type) == 'cheque' ? 'selected' : '' }}>
                                            Cheque</option>
                                        <option value="online"
                                            {{ old('payment_type', $transaction->payment_type) == 'online' ? 'selected' : '' }}>
                                            Online</option>
                                    </select>
                                    @error('payment_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Amount -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Amount (PKR) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="amount" value="{{ old('amount', $transaction->amount) }}"
                                        class="form-control @error('amount') is-invalid @enderror" placeholder="0"
                                        min="1" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Payment Date -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Payment Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="payment_date"
                                        value="{{ old('payment_date', \Carbon\Carbon::parse($transaction->payment_date)->format('Y-m-d')) }}"
                                        class="form-control @error('payment_date') is-invalid @enderror" required>
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Reference Number -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Reference / Cheque No</label>
                                    <input type="text" name="reference_number"
                                        value="{{ old('reference_number', $transaction->reference_number) }}"
                                        class="form-control @error('reference_number') is-invalid @enderror"
                                        placeholder="TXN-123 / Cheque No">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Proof of Payment -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">
                                        Proof of Payment
                                        <small class="text-muted">(JPG, PNG, PDF — Max 2MB)</small>
                                    </label>

                                    @if ($transaction->proof_of_payment)
                                        <div class="mb-2">
                                            <small class="text-muted">Current file: </small>
                                            @php $ext = pathinfo($transaction->proof_of_payment, PATHINFO_EXTENSION); @endphp
                                            @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                                                <a href="{{ asset('assets/images/transaction_proofs/' . $transaction->proof_of_payment) }}"
                                                    target="_blank">
                                                    <img src="{{ asset('assets/images/transaction_proofs/' . $transaction->proof_of_payment) }}"
                                                        height="50" class="rounded border">
                                                </a>
                                            @else
                                                <a href="{{ asset('assets/images/transaction_proofs/' . $transaction->proof_of_payment) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-secondary">
                                                    <i class="mdi mdi-file-pdf-box me-1"></i> View PDF
                                                </a>
                                            @endif
                                        </div>
                                    @endif

                                    <input type="file" name="proof_of_payment"
                                        class="form-control @error('proof_of_payment') is-invalid @enderror"
                                        accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="text-muted">Uploading a new file will replace the current one</small>
                                    @error('proof_of_payment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror"
                                        required>
                                        <option value="confirmed"
                                            {{ old('status', $transaction->status) == 'confirmed' ? 'selected' : '' }}>
                                            Confirmed</option>
                                        <option value="pending"
                                            {{ old('status', $transaction->status) == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="rejected"
                                            {{ old('status', $transaction->status) == 'rejected' ? 'selected' : '' }}>
                                            Rejected</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Notes -->
                                <div class="col-12 mb-4">
                                    <label class="form-label fw-medium">Notes</label>
                                    <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                        placeholder="Optional remarks...">{{ old('notes', $transaction->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-1"></i> Update Transaction
                                </button>
                                <a href="{{ route('transaction.index') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bookingForSelect = document.getElementById('booking_for');
            const clientWrapper = document.getElementById('client_wrapper');
            const companyWrapper = document.getElementById('company_wrapper');
            const clientSelect = document.getElementById('client_id');
            const companySelect = document.getElementById('company_id');
            const bookingSelect = document.getElementById('booking_id');
            const sourceSelect = document.getElementById('all_bookings_source');

            // Build a plain JS array of all bookings from the hidden source select
            const allBookings = Array.from(sourceSelect.options).map(opt => ({
                value: opt.value,
                clientId: opt.dataset.clientId,
                companyId: opt.dataset.companyId,
                label: opt.textContent,
            }));

            // The booking already saved on this transaction (or the old() value if validation failed)
            const existingBookingId = "{{ old('booking_id', $transaction->booking_id) }}";

            // Rebuild the visible booking dropdown based on selected client/company.
            // preserveExisting=true keeps the transaction's current booking selected on first page load,
            // instead of forcing the user to re-pick it every time they open Edit.
            function filterBookings(type, id, preserveExisting) {
                bookingSelect.innerHTML = '';

                if (!id) {
                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = '-- Select Client/Company first --';
                    bookingSelect.appendChild(placeholder);
                    return;
                }

                const matches = allBookings.filter(b =>
                    type === 'company' ? b.companyId === id : b.clientId === id
                );

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = matches.length ?
                    '-- Select Booking --' :
                    '-- No booking found for this selection --';
                bookingSelect.appendChild(placeholder);

                matches.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.value;
                    opt.textContent = b.label;
                    bookingSelect.appendChild(opt);
                });

                if (preserveExisting && existingBookingId && matches.some(b => b.value === existingBookingId)) {
                    bookingSelect.value = existingBookingId;
                }
            }

            function toggleFields(preserveExisting) {
                if (bookingForSelect.value === 'company') {
                    clientWrapper.classList.add('d-none');
                    companyWrapper.classList.remove('d-none');
                    clientSelect.removeAttribute('required');
                    companySelect.setAttribute('required', 'required');
                    filterBookings('company', companySelect.value, preserveExisting);
                } else {
                    companyWrapper.classList.add('d-none');
                    clientWrapper.classList.remove('d-none');
                    companySelect.removeAttribute('required');
                    clientSelect.setAttribute('required', 'required');
                    filterBookings('client', clientSelect.value, preserveExisting);
                }
            }

            bookingForSelect.addEventListener('change', function() {
                toggleFields(false);
            });
            clientSelect.addEventListener('change', function() {
                filterBookings('client', this.value, false);
            });
            companySelect.addEventListener('change', function() {
                filterBookings('company', this.value, false);
            });

            toggleFields(true);
        });
    </script>

@endsection
