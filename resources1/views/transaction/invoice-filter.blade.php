@extends('layout.master')

@section('title', 'Invoice Filter')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-4">

                                <h4 class="fw-bold mb-4">Invoice Filter</h4>

                                {{-- Step 1: pick a client (auto-submits via GET so the
                                     transaction dropdown can load that client's records) --}}
                                <form action="{{ route('transaction.invoice.filter') }}" method="GET">
                                    <div class="row">

                                        {{-- Client --}}
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label fw-medium">Client <span
                                                    class="text-danger">*</span></label>
                                            <select name="client_id" class="form-select" onchange="this.form.submit()"
                                                required>
                                                <option value="">-- Select Client --</option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->id }}"
                                                        {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                                        {{ $client->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-5 mb-3">
                                            <label class="form-label fw-medium">Transaction <span
                                                    class="text-danger">*</span></label>
                                            <select name="transaction_id" id="transaction_id" class="form-select"
                                                {{ $transactions->isEmpty() ? 'disabled' : '' }} required>
                                                @if ($transactions->isEmpty())
                                                    <option value="">-- Select a client first --</option>
                                                @else
                                                    <option value="">-- Select Transaction --</option>
                                                    @foreach ($transactions as $transaction)
                                                        <option value="{{ $transaction->id }}">
                                                            #{{ str_pad($transaction->id, 4, '0', STR_PAD_LEFT) }} —
                                                            {{ \Carbon\Carbon::parse($transaction->payment_date)->format('d/m/Y') }}
                                                            — {{ number_format($transaction->amount, 2) }}
                                                            ({{ ucfirst($transaction->status) }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="col-md-2 mb-3 d-flex align-items-end">
                                            <button type="button" onclick="goToInvoice()" class="btn btn-primary w-100"
                                                {{ $transactions->isEmpty() ? 'disabled' : '' }}>
                                                View Invoice
                                            </button>
                                        </div>

                                    </div>
                                </form>

                                <div class="mt-3">
                                    <a href="{{ route('transaction.index') }}" class="btn btn-secondary px-4">Back</a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function goToInvoice() {
            const select = document.getElementById('transaction_id');
            const transactionId = select.value;

            if (!transactionId) {
                alert('Please select a transaction first.');
                return;
            }

            // Build the invoice URL using the route helper's base, then append the id.
            const baseUrl = "{{ route('transaction.invoice', ['id' => 'TX_ID']) }}";
            window.location.href = baseUrl.replace('TX_ID', transactionId);
        }
    </script>

@endsection
