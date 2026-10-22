@extends('layout.master')
@section('title', 'Lead Trash')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">🗑 Lead Trash</h4>
                    <a href="{{ route('lead.index') }}" class="btn btn-secondary btn-sm">← Back to Leads</a>
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
                                        <th>Contact Person</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Company</th>
                                        <th>Package</th>
                                        <th>Deleted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leads as $lead)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $lead->contact_person ?? '—' }}</strong>
                                                @if ($lead->client)
                                                    <span class="badge bg-soft-info text-info ms-1">Converted Client</span>
                                                @endif
                                            </td>
                                            <td>{{ $lead->phone ?? '—' }}</td>
                                            <td>{{ $lead->email ?? '—' }}</td>
                                            <td>{{ $lead->company->company_name ?? '—' }}</td>
                                            <td>
                                                @if ($lead->package)
                                                    <span class="badge bg-soft-primary text-primary">{{ $lead->package->name }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $lead->deleted_at ? $lead->deleted_at->format('d M Y, h:i A') : '—' }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('lead.restore', $lead->id) }}"
                                                        class="btn btn-sm btn-outline-success" title="Restore Lead">
                                                        <i class="mdi mdi-restore me-1"></i> Restore
                                                    </a>
                                                    <form action="{{ route('lead.force-delete', $lead->id) }}" method="POST"
                                                        onsubmit="return confirm('Are you sure you want to permanently delete this lead? This action cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Permanently Delete">
                                                            <i class="mdi mdi-delete-forever me-1"></i> Delete Forever
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Trash is empty.</td>
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
