@extends('layout.master')

@section('title', 'Client Ledger (Dr / Cr) Filter')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="row justify-content-center">
                    <div class="col-lg-8 col-12">
                        <div class="card shadow-sm border-0 mt-3">
                            <div class="card-body p-4">

                                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                                    <div>
                                        <h4 class="fw-bold m-0 text-dark">
                                            <i class="mdi mdi-book-open-page-variant text-primary me-2"></i> Client Ledger Filter (Dr / Cr)
                                        </h4>
                                        <p class="text-muted fs-12 mb-0">Generate double-entry Statement of Account with Debit, Credit & Running Balance.</p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('reports.hub', ['tab' => 'client']) }}" class="btn btn-primary btn-sm">
                                            <i class="mdi mdi-layers-outline me-1"></i> All-In-One Reports Hub
                                        </a>
                                        <a href="{{ route('reports.transactions') }}" class="btn btn-outline-dark btn-sm">
                                            <i class="mdi mdi-receipt-text-outline me-1"></i> All Transactions Report
                                        </a>
                                    </div>
                                </div>

                                <form action="{{ route('transaction.ledger.view') }}" method="GET">
                                    <div class="row g-3">

                                        <div class="col-12">
                                            <label class="form-label fw-bold">Select Client <span class="text-danger">*</span></label>
                                            <select name="client_id" class="form-select form-select-lg fs-14" required>
                                                <option value="">-- Choose Client --</option>
                                                <option value="all">🌟 All Clients (Consolidated Ledger)</option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->id }}">
                                                        {{ $client->name }} {{ $client->company_name ? ' (' . $client->company_name . ')' : '' }} {{ $client->phone ? ' — ' . $client->phone : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- From Date -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">From Date <small class="text-muted">(Optional)</small></label>
                                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                                        </div>

                                        <!-- To Date -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">To Date <small class="text-muted">(Optional)</small></label>
                                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                                        </div>

                                    </div>

                                    <div class="mt-4 pt-2 border-top d-flex justify-content-between align-items-center">
                                        <a href="{{ route('transaction.index') }}" class="btn btn-light px-3">
                                            <i class="mdi mdi-arrow-left me-1"></i> Back
                                        </a>
                                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                            <i class="mdi mdi-file-document-check me-1"></i> Generate Ledger Statement
                                        </button>
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
