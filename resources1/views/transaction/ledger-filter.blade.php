@extends('layout.master')

@section('title', 'Client Ledger Filter')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-4">

                                <h4 class="fw-bold mb-4">Client Ledger Filter</h4>

                                <form action="{{ route('transaction.ledger.view') }}" method="POST">
                                    @csrf

                                    <div class="row">

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">Select Client <span
                                                    class="text-danger">*</span></label>
                                            <select name="client_id" class="form-select" required>
                                                <option value="">-- Select Client --</option>
                                                <option value="all">All Clients</option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- From Date -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-medium">From Date</label>
                                            <input type="date" name="from_date" class="form-control"
                                                value="{{ old('from_date') }}">
                                        </div>

                                        <!-- To Date -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-medium">To Date</label>
                                            <input type="date" name="to_date" class="form-control"
                                                value="{{ old('to_date') }}">
                                        </div>

                                    </div>

                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary px-5">Generate Ledger</button>
                                        <a href="{{ route('transaction.index') }}" class="btn btn-secondary px-4">Back</a>
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
