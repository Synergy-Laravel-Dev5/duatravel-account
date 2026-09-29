@extends('layout.master')
@section('title', 'Clients')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Clients / Vendors</h4>
                    <div class="d-flex gap-2">
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
                                        <th>Type</th>
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
                                                @php
                                                    $types = [
                                                        'client' => 'bg-primary',
                                                        'vendor' => 'bg-info',
                                                        'customer' => 'bg-secondary',
                                                    ];
                                                @endphp
                                                <span class="badge {{ $types[$client->type] ?? 'bg-secondary' }}">
                                                    {{ ucfirst($client->type) }}
                                                </span>
                                            </td>
                                            <td>{{ $client->passport_number ?? '—' }}</td>
                                            <td>{{ $client->cnic ?? '—' }}</td>
                                            <td>{{ $client->phone ?? '—' }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $client->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($client->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
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
