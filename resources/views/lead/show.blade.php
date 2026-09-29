@extends('layout.master')
@section('title', 'Lead Details')
@section('content')
    @php
        $statusColors = [
            'pending' => 'warning',
            'done' => 'success',
            'need further follow up' => 'info',
        ];
    @endphp
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                    <h4 class="fs-18 fw-semibold">Lead Details</h4>
                </div>
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="card mb-4">
                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-4">
                                <h6 class="text-muted">Contact Person</h6>
                                <p class="fw-semibold fs-15">{{ $lead->contact_person ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <h6 class="text-muted">Phone</h6>
                                <p class="fw-semibold fs-15">{{ $lead->phone ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <h6 class="text-muted">Email</h6>
                                <p class="fw-semibold fs-15">{{ $lead->email ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <h6 class="text-muted">Company</h6>
                                <p class="fw-semibold fs-15">{{ $lead->company->company_name ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <h6 class="text-muted">Package</h6>
                                <p class="fw-semibold fs-15">{{ $lead->package->package_number ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <h6 class="text-muted">Number Of Person</h6>
                                <p class="fw-semibold fs-15">{{ $lead->number_of_pax ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <h6 class="text-muted">Lead Source</h6>
                                <p class="fw-semibold fs-15">{{ $lead->source ? ucfirst($lead->source) : '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <h6 class="text-muted">Medium Of Contact</h6>
                                <p class="fw-semibold fs-15">
                                    {{ $lead->medium_of_contact ? ucfirst($lead->medium_of_contact) : '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <h6 class="text-muted">Assigned User</h6>
                                <p class="fw-semibold fs-15">{{ $lead->user->name ?? '-' }}</p>
                            </div>

                            <div class="col-12">
                                <h6 class="text-muted">Description</h6>
                                <div class="border rounded p-3">
                                    {!! nl2br(e($lead->description ?? '-')) !!}
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <div>
                            @if (!$lead->client)
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#convertToClientModal">
                                    <i class="mdi mdi-account-convert me-1"></i> Convert to Client
                                </button>
                            @else
                                <span class="badge bg-success fs-13">
                                    <i class="mdi mdi-check-circle me-1"></i> Converted to Client #{{ $lead->client->id }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('lead.edit', $lead->id) }}" class="btn btn-primary">Edit</a>
                            <a href="{{ route('lead.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>

                {{-- Tabs for Client, Follow-ups & Quotations --}}
                <div class="card">
                    <div class="card-header bg-white pb-0 border-bottom-0">
                        <ul class="nav nav-tabs card-header-tabs" id="leadTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-semibold" id="client-tab" data-bs-toggle="tab"
                                    data-bs-target="#client-pane" type="button" role="tab" aria-controls="client-pane"
                                    aria-selected="true">
                                    <i class="mdi mdi-account-check me-1"></i> Client Details
                                    @if ($lead->client)
                                        <span class="badge bg-success rounded-pill ms-1">Converted</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill ms-1">Not Converted</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold" id="followups-tab" data-bs-toggle="tab"
                                    data-bs-target="#followups" type="button" role="tab" aria-controls="followups"
                                    aria-selected="false">
                                    <i class="mdi mdi-calendar-clock me-1"></i> Follow-ups
                                    <span class="badge bg-primary rounded-pill ms-1">{{ $lead->followUps->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold" id="quotations-tab" data-bs-toggle="tab"
                                    data-bs-target="#quotations" type="button" role="tab" aria-controls="quotations"
                                    aria-selected="false">
                                    <i class="mdi mdi-file-document-outline me-1"></i> Quotations
                                    <span
                                        class="badge bg-secondary rounded-pill ms-1">{{ ($lead->quotations ? $lead->quotations->count() : 0) + ($lead->package ? 1 : 0) }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="leadTabContent">
                            {{-- ──────── TAB 1: CLIENT DETAILS / CONVERT ──────── --}}
                            <div class="tab-pane fade show active" id="client-pane" role="tabpanel"
                                aria-labelledby="client-tab">
                                @if ($lead->client)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="mb-0 text-success"><i class="mdi mdi-check-decagram me-1"></i>
                                                Converted Client Record</h5>
                                            <small class="text-muted">This lead is registered as an active client in the
                                                directory.</small>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('client.edit', $lead->client->id) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="mdi mdi-pencil me-1"></i> Edit Client
                                            </a>
                                            <a href="{{ route('client.index') }}"
                                                class="btn btn-outline-secondary btn-sm">
                                                <i class="mdi mdi-account-group me-1"></i> All Clients
                                            </a>
                                        </div>
                                    </div>

                                    <div class="card border border-success bg-light bg-opacity-25 shadow-none mb-3">
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <span class="text-muted fs-13 d-block">Client ID:</span>
                                                    <strong class="fs-15 text-primary">#{{ $lead->client->id }}</strong>
                                                </div>
                                                <div class="col-md-3">
                                                    <span class="text-muted fs-13 d-block">Client Name:</span>
                                                    <strong class="fs-15">{{ $lead->client->name }}</strong>
                                                </div>
                                                <div class="col-md-3">
                                                    <span class="text-muted fs-13 d-block">Phone:</span>
                                                    <span>{{ $lead->client->phone ?? '-' }}</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <span class="text-muted fs-13 d-block">Email:</span>
                                                    <span>{{ $lead->client->email ?? ($lead->email ?? '-') }}</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <span class="text-muted fs-13 d-block">Company:</span>
                                                    <span>{{ $lead->client->company_name ?? ($lead->company->company_name ?? '-') }}</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <span class="text-muted fs-13 d-block">Linked Package:</span>
                                                    @if ($lead->client->package || $lead->package)
                                                        <span class="badge bg-soft-primary text-primary fs-12">
                                                            {{ ($lead->client->package ?? $lead->package)->name ?? ($lead->client->package ?? $lead->package)->package_number }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">No Package Linked</span>
                                                    @endif
                                                </div>
                                                <div class="col-md-3">
                                                    <span class="text-muted fs-13 d-block">Client Type:</span>
                                                    <span class="badge bg-info">{{ ucfirst($lead->client->type) }}</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <span class="text-muted fs-13 d-block">Status:</span>
                                                    <span
                                                        class="badge bg-{{ $lead->client->status == 'active' ? 'success' : 'danger' }}">
                                                        {{ ucfirst($lead->client->status) }}
                                                    </span>
                                                </div>
                                                <div class="col-md-6">
                                                    <span class="text-muted fs-13 d-block">Passport Number:</span>
                                                    <span>{{ $lead->client->passport_number ?? 'Not Added' }}</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <span class="text-muted fs-13 d-block">CNIC:</span>
                                                    <span>{{ $lead->client->cnic ?? 'Not Added' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-5 border rounded bg-light bg-opacity-50">
                                        <div class="avatar-lg bg-soft-success text-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                            style="width: 70px; height: 70px; margin: 0 auto; background: #e8f5e9;">
                                            <i class="mdi mdi-account-arrow-right text-success"
                                                style="font-size: 36px;"></i>
                                        </div>
                                        <h4 class="fw-bold mb-1">Convert Lead to Client</h4>
                                        <p class="text-muted fs-14 mb-4 mx-auto" style="max-width: 520px;">
                                            Ready to onboard this prospect? Converting this lead will register
                                            <strong>{{ $lead->contact_person ?? 'this contact' }}</strong> as an active
                                            Client in the directory with all contact info.
                                        </p>
                                        <button type="button" class="btn btn-success px-4 py-2 fw-bold fs-14"
                                            data-bs-toggle="modal" data-bs-target="#convertToClientModal">
                                            <i class="mdi mdi-account-check me-1"></i> Convert to Client
                                        </button>
                                    </div>
                                @endif
                            </div>

                            {{-- ──────── TAB 2: FOLLOW-UPS ──────── --}}
                            <div class="tab-pane fade" id="followups" role="tabpanel" aria-labelledby="followups-tab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0" style="color: #005f73">Follow-ups</h5>
                                    <button data-bs-toggle="modal" data-bs-target="#followUpModal-{{ $lead->id }}"
                                        class="btn btn-success btn-sm">
                                        <i class="mdi mdi-plus-circle-outline me-1"></i> Create Follow-up
                                    </button>
                                </div>

                                {{-- Create Follow-up Modal --}}
                                <div class="modal fade" id="followUpModal-{{ $lead->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('followUp.store', $lead->id) }}">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Create Follow-up for
                                                        {{ $lead->contact_person ?? 'Lead' }}</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Subject</label>
                                                        <input type="text" name="subject" class="form-control"
                                                            required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Follow-up Date</label>
                                                        <input type="text" name="create_date"
                                                            class="form-control flatpickr-create-date" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason</label>
                                                        <input type="text" name="reason" class="form-control"
                                                            placeholder="Enter reason">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Create
                                                        Follow-up</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- Follow-ups Accordion --}}
                                <div class="accordion" id="followUpAccordion">
                                    @forelse ($lead->followUps as $followUp)
                                        <div class="accordion-item mb-2">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse{{ $followUp->id }}">
                                                    <span class="fw-semibold">{{ $followUp->subject }}</span>
                                                    <span class="text-muted ms-2 fs-13">
                                                        ({{ $followUp->create_date ? \Carbon\Carbon::parse($followUp->create_date)->format('d M Y, h:i A') : '-' }})
                                                    </span>
                                                    <span
                                                        class="badge bg-{{ $statusColors[$followUp->status] ?? 'secondary' }} ms-auto me-3">
                                                        {{ ucfirst($followUp->status) }}
                                                    </span>
                                                </button>
                                            </h2>
                                            <div id="collapse{{ $followUp->id }}" class="accordion-collapse collapse"
                                                data-bs-parent="#followUpAccordion">
                                                <div class="accordion-body">
                                                    <table class="table table-bordered mb-0">
                                                        <tr>
                                                            <th style="width: 25%;">Subject</th>
                                                            <td>{{ $followUp->subject }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Reason</th>
                                                            <td>{{ $followUp->reason ?? '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Status</th>
                                                            <td>
                                                                <span
                                                                    class="badge bg-{{ $statusColors[$followUp->status] ?? 'secondary' }}">
                                                                    {{ ucfirst($followUp->status) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Remarks</th>
                                                            <td>{{ $followUp->remarks ?? '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Take Date</th>
                                                            <td>{{ $followUp->taken_at ? \Carbon\Carbon::parse($followUp->taken_at)->format('d M Y, h:i A') : '-' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Due Date</th>
                                                            <td>{{ $followUp->due_date ? \Carbon\Carbon::parse($followUp->due_date)->format('d M Y, h:i A') : '-' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Action</th>
                                                            <td>
                                                                @if ($followUp->status != 'done')
                                                                    <button type="button" class="btn btn-success btn-sm"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#takeModal{{ $followUp->id }}">Take</button>
                                                                @else
                                                                    <span class="badge bg-primary text-white">Taken</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Take Follow-up Modal --}}
                                        <div class="modal fade" id="takeModal{{ $followUp->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST"
                                                    action="{{ route('followUp.take', $followUp->id) }}">
                                                    @csrf
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Take Follow-up</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Remarks</label>
                                                                <textarea name="remarks" class="form-control" rows="3"></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Select Status</label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="" selected disabled>Select Status
                                                                    </option>
                                                                    @foreach (\App\Models\FollowUp::getStatuses() as $key => $value)
                                                                        <option value="{{ $key }}">
                                                                            {{ $value }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Take Date</label>
                                                                <input type="text" name="taken_at"
                                                                    class="form-control flatpickr-taken-at"
                                                                    value="{{ \Carbon\Carbon::now('Asia/Karachi')->format('d M Y, h:i A') }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Due Date</label>
                                                                <input type="text" name="due_date"
                                                                    class="form-control flatpickr-due-date">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Submit</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4">
                                            <i class="mdi mdi-calendar-blank-outline text-muted"
                                                style="font-size: 42px;"></i>
                                            <p class="text-muted mt-2 mb-0">No follow-ups available for this lead.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- ──────── TAB 2: QUOTATIONS ──────── --}}
                            <div class="tab-pane fade" id="quotations" role="tabpanel" aria-labelledby="quotations-tab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0" style="color: #005f73">Quotations</h5>
                                    <a href="{{ route('quotation.create', ['lead_id' => $lead->id]) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="mdi mdi-plus-circle-outline me-1"></i> Create Quotation
                                    </a>
                                </div>

                                @if ($lead->quotations && $lead->quotations->count() > 0)
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Quote #</th>
                                                    <th>Title</th>
                                                    <th>Pax</th>
                                                    <th>Total Cost</th>
                                                    <th>Total Selling</th>
                                                    <th>Profit</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th class="text-center" style="width: 100px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($lead->quotations as $q)
                                                    <tr>
                                                        <td class="fw-bold text-primary">{{ $q->quotation_number }}</td>
                                                        <td>{{ $q->quotation_title }}</td>
                                                        <td><span class="badge bg-light text-dark">{{ $q->total_pax }}
                                                                Pax</span></td>
                                                        <td class="text-danger fw-semibold">PKR
                                                            {{ number_format($q->total_cost_pkr) }}</td>
                                                        <td class="text-success fw-semibold">PKR
                                                            {{ number_format($q->total_sale_pkr) }}</td>
                                                        <td class="text-primary fw-bold">PKR
                                                            {{ number_format($q->total_profit_pkr) }}</td>
                                                        <td>
                                                            <span
                                                                class="badge bg-{{ $q->status == 'confirmed' ? 'success' : 'warning' }}">{{ ucfirst($q->status) }}</span>
                                                        </td>
                                                        <td>{{ $q->created_at->format('d M Y') }}</td>
                                                        <td class="text-center">
                                                            <div class="d-inline-flex gap-1 align-items-center justify-content-center">
                                                                <a href="{{ route('quotation.show', $q->id) }}"
                                                                    class="btn btn-sm btn-outline-info" title="View Proposal">
                                                                    <i class="mdi mdi-eye"></i>
                                                                </a>
                                                                <a href="{{ route('quotation.edit', $q->id) }}"
                                                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                                                    <i class="mdi mdi-pencil"></i>
                                                                </a>
                                                                <a href="{{ route('quotation.pdf', $q->id) }}"
                                                                    class="btn btn-sm btn-outline-danger" title="Download PDF" download>
                                                                    <i class="mdi mdi-file-pdf-box"></i>
                                                                </a>
                                                                <a href="{{ route('quotation.pdf', ['id' => $q->id, 'print' => 1]) }}"
                                                                    target="_blank" class="btn btn-sm btn-outline-dark" title="Print / Web View">
                                                                    <i class="mdi mdi-printer"></i>
                                                                </a>
                                                                <form action="{{ route('quotation.delete', $q->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this quotation?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                                        <i class="mdi mdi-trash-can"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                @if ($lead->package)
                                    <h6 class="fs-13 fw-semibold text-muted mb-2">Assigned Master Package:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Package #</th>
                                                    <th>Package Name</th>
                                                    <th>Category / Zone</th>
                                                    <th>Days</th>
                                                    <th>Pax Requested</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold">
                                                        {{ $lead->package->package_number ?? 'PKG-' . $lead->package->id }}
                                                    </td>
                                                    <td>{{ $lead->package->name ?? '-' }}</td>
                                                    <td>{{ $lead->package->category_zone ?? '-' }}</td>
                                                    <td>{{ $lead->package->days ? $lead->package->days . ' Days' : '-' }}
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info text-white fs-12">
                                                            {{ $lead->number_of_pax ?? 1 }} Pax
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('package.show', $lead->package->id) }}"
                                                            class="btn btn-sm btn-info text-white">
                                                            <i class="mdi mdi-eye-outline me-1"></i> View Package
                                                        </a>
                                                        <a href="{{ route('package.pdf', $lead->package->id) }}"
                                                            target="_blank" class="btn btn-sm btn-primary">
                                                            <i class="mdi mdi-file-pdf-box me-1"></i> Brochure / Quotation
                                                            PDF
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                @elseif(!$lead->quotations || $lead->quotations->count() == 0)
                                    <div class="text-center py-4">
                                        <i class="mdi mdi-file-document-outline text-muted" style="font-size: 42px;"></i>
                                        <p class="text-muted mt-2 mb-2">No quotation has been created for this lead yet.
                                        </p>
                                        <a href="{{ route('quotation.create', ['lead_id' => $lead->id]) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-plus-circle-outline me-1"></i> Create First Quotation
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Convert to Client Confirmation Modal --}}
                <div class="modal fade" id="convertToClientModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title text-white"><i class="mdi mdi-account-convert me-1"></i> Confirm
                                    Conversion to Client</h5>
                                <button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('lead.convert-to-client', $lead->id) }}" method="POST">
                                @csrf
                                <div class="modal-body p-4">
                                    <div class="text-center mb-3">
                                        <i class="mdi mdi-help-circle-outline text-success" style="font-size: 52px;"></i>
                                        <h5 class="mt-2 fw-semibold">Are you sure you want to convert this lead into a
                                            client?</h5>
                                        <p class="text-muted fs-13">A new client profile will be created in the client
                                            directory with the details below.</p>
                                    </div>
                                    <div class="card border shadow-none bg-light p-3 mb-0">
                                        <table class="table table-sm table-borderless mb-0 fs-13">
                                            <tr>
                                                <th style="width: 40%;">Contact Person:</th>
                                                <td class="fw-semibold">{{ $lead->contact_person ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Phone:</th>
                                                <td>{{ $lead->phone ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td>{{ $lead->email ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Company:</th>
                                                <td>{{ $lead->company->company_name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Assigned Package:</th>
                                                <td>
                                                    @if ($lead->package)
                                                        <span
                                                            class="badge bg-soft-primary text-primary">{{ $lead->package->name ?? $lead->package->package_number }}</span>
                                                    @else
                                                        <span class="text-muted">None (Can be linked later in edit
                                                            lead)</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success fw-bold">
                                        <i class="mdi mdi-check me-1"></i> Yes, Convert to Client
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr(".flatpickr-create-date", {
            enableTime: true,
            dateFormat: "d M Y, h:i K",
            defaultDate: "{{ \Carbon\Carbon::now('Asia/Karachi')->format('d M Y, h:i K') }}",
            time_24hr: false
        });
        document.querySelectorAll('.flatpickr-taken-at').forEach(function(el) {
            flatpickr(el, {
                enableTime: true,
                dateFormat: "d M Y, h:i K",
                time_24hr: false
            });
        });
        document.querySelectorAll('.flatpickr-due-date').forEach(function(el) {
            flatpickr(el, {
                enableTime: true,
                dateFormat: "d M Y, h:i K",
                time_24hr: false
            });
        });
    </script>
@endsection
