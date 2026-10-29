@extends('layout.master')
@section('title', 'Clients')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h4 class="fs-18 fw-semibold m-0">Clients / Vendors</h4>
                        <span class="text-muted fs-12">
                            Showing {{ ($filterPkg ?? 'hajj') === 'all' ? 'All' : ucfirst($filterPkg ?? 'hajj') }} Clients
                            (Current Dashboard Season: <strong class="text-uppercase text-dark">{{ $sessionPkg ?? session('dashboard_package', 'hajj') }}</strong>)
                        </span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('client.index', ['package_type' => ($sessionPkg ?? session('dashboard_package', 'hajj'))]) }}"
                               class="btn {{ ($filterPkg ?? $sessionPkg) === ($sessionPkg ?? session('dashboard_package', 'hajj')) && ($filterPkg ?? '') !== 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                {{ ucfirst($sessionPkg ?? session('dashboard_package', 'hajj')) }} Only
                            </a>
                            <a href="{{ route('client.index', ['package_type' => (($sessionPkg ?? session('dashboard_package', 'hajj')) === 'hajj' ? 'umrah' : 'hajj')]) }}"
                               class="btn {{ ($filterPkg ?? '') === (($sessionPkg ?? session('dashboard_package', 'hajj')) === 'hajj' ? 'umrah' : 'hajj') ? 'btn-primary' : 'btn-outline-secondary' }}">
                                {{ ($sessionPkg ?? session('dashboard_package', 'hajj')) === 'hajj' ? 'Umrah' : 'Hajj' }} Only
                            </a>
                            <a href="{{ route('client.index', ['package_type' => 'all']) }}"
                               class="btn {{ ($filterPkg ?? '') === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                All Clients
                            </a>
                        </div>
                        @can('client_create')
                            <a href="{{ route('client.create') }}" class="btn btn-primary btn-sm">
                                + Add Client
                            </a>
                        @endcan
                        @can('client_trash_view')
                            <a href="{{ route('client.trash') }}" class="btn btn-danger btn-sm">
                                🗑 Trash <span class="badge bg-white text-danger ms-1">{{ $trashCount }}</span>
                            </a>
                        @endcan
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Package</th>
                                        <th>Passport #</th>
                                        <th>CNIC</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($clients as $client)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $client->name }}</strong>
                                                <span class="badge {{ ($client->package_type ?? 'hajj') === 'hajj' ? 'bg-warning text-dark' : 'bg-info text-white' }} fs-10 text-uppercase ms-1">{{ $client->package_type ?? 'hajj' }}</span>
                                                @if ($client->lead_id)
                                                    <a href="{{ route('lead.show', $client->lead_id) }}" class="badge bg-soft-info text-info d-block mt-1" style="font-size: 11px; width: fit-content;">
                                                        <i class="mdi mdi-link-variant me-1"></i> Lead #{{ $client->lead_id }}
                                                    </a>
                                                @endif
                                            </td>
                                            <td>{{ $client->company_name ?? '—' }}</td>
                                            <td>
                                                @if ($client->package)
                                                    <span class="fw-semibold">{{ $client->package->name }}</span>
                                                    <small class="text-muted d-block">({{ $client->package->code ?? $client->package->package_number }})</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <td>
                                                {{ $client->passport_number ?? '—' }}
                                                @if ($client->passport_document)
                                                    <a href="{{ $client->passport_document_url }}" target="_blank" class="badge bg-soft-info text-info d-block mt-1" style="font-size: 10px; width: fit-content;" title="View Passport Document">
                                                        <i class="mdi mdi-file-document-outline me-1"></i> Passport Doc
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $client->cnic ?? '—' }}
                                                @if ($client->cnic_document)
                                                    <a href="{{ $client->cnic_document_url }}" target="_blank" class="badge bg-soft-success text-success d-block mt-1" style="font-size: 10px; width: fit-content;" title="View CNIC Document">
                                                        <i class="mdi mdi-card-account-details-outline me-1"></i> CNIC Doc
                                                    </a>
                                                @endif
                                            </td>
                                            <td>{{ $client->phone ?? '—' }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $client->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($client->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1">
                                                    <a href="{{ route('transaction.ledger.view') }}?client_id={{ $client->id }}"
                                                        class="btn btn-sm btn-info text-white" title="Generate Client Report / Statement">
                                                        <i class="mdi mdi-file-chart me-1"></i> Report
                                                    </a>
                                                    @can('client_edit')
                                                        <a href="{{ route('client.edit', $client->id) }}"
                                                            class="btn btn-sm btn-outline-success" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('client_trash')
                                                        <form action="{{ route('client.delete', $client->id) }}" method="POST"
                                                            onsubmit="return confirm('Move to trash?')">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                                <i class="mdi mdi-delete"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
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
