@extends('layout.master')
@section('title', 'Quotations')

@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                    <div>
                        <h4 class="fs-18 fw-semibold mb-1">Quotations Management</h4>
                        <p class="text-muted fs-13 mb-0">Create, manage, and track custom travel quotations, costs, selling prices, and profit margins.</p>
                    </div>
                    <div>
                        <a href="{{ route('quotation.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus-circle-outline me-1"></i> Create Quotation
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle-outline me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0 datatable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Quote #</th>
                                        <th>Title & Client</th>
                                        <th>Linked Lead</th>
                                        <th>Pax</th>
                                        <th>Total Cost (PKR)</th>
                                        <th>Total Sale (PKR)</th>
                                        <th>Profit (PKR)</th>
                                        <th>Status</th>
                                        <th>Created Date</th>
                                        <th class="text-center" style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($quotations as $quote)
                                        <tr>
                                            <td class="fw-bold text-primary">
                                                <a href="{{ route('quotation.show', $quote->id) }}" class="text-primary">
                                                    {{ $quote->quotation_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $quote->quotation_title ?? 'Custom Quotation' }}</div>
                                                <small class="text-muted">{{ $quote->client_name ?? (optional($quote->lead)->contact_person ?? '-') }}</small>
                                            </td>
                                            <td>
                                                @if ($quote->lead)
                                                    <a href="{{ route('lead.show', $quote->lead_id) }}" class="badge bg-soft-info text-info">
                                                        Lead #{{ $quote->lead_id }} ({{ $quote->lead->contact_person }})
                                                    </a>
                                                @else
                                                    <span class="text-muted fs-12">Direct</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark fs-12">{{ $quote->total_pax }} Pax</span>
                                            </td>
                                            <td class="fw-semibold text-danger">PKR {{ number_format($quote->total_cost_pkr) }}</td>
                                            <td class="fw-semibold text-success">PKR {{ number_format($quote->total_sale_pkr) }}</td>
                                            <td class="fw-bold text-primary">
                                                PKR {{ number_format($quote->total_profit_pkr) }}
                                                <small class="d-block text-muted">{{ $quote->margin_percentage }}%</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $quote->status == 'confirmed' ? 'success' : ($quote->status == 'sent' ? 'info' : 'warning') }}">
                                                    {{ ucfirst($quote->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $quote->created_at ? $quote->created_at->format('d M Y') : '-' }}</td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1 align-items-center justify-content-center">
                                                    <a href="{{ route('quotation.show', $quote->id) }}" class="btn btn-sm btn-outline-info" title="View Proposal">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('quotation.edit', $quote->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <a href="{{ route('quotation.pdf', $quote->id) }}" class="btn btn-sm btn-outline-danger" title="Download PDF" download>
                                                        <i class="mdi mdi-file-pdf-box"></i>
                                                    </a>
                                                    <a href="{{ route('quotation.pdf', ['id' => $quote->id, 'print' => 1]) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="Print / Web View">
                                                        <i class="mdi mdi-printer"></i>
                                                    </a>
                                                    <form action="{{ route('quotation.delete', $quote->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this quotation?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="mdi mdi-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4 text-muted">
                                                <i class="mdi mdi-file-document-outline" style="font-size: 38px;"></i>
                                                <p class="mt-2 mb-0">No quotations found. Click "Create Quotation" to get started.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
