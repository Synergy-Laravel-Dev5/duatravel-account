@extends('layout.master')
@section('title', 'Leads')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Leads</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('lead.create') }}" class="btn btn-primary btn-sm">
                            + Add Lead
                        </a>
                        <a href="{{ route('lead.trash') }}" class="btn btn-danger btn-sm">
                            🗑 Trash
                            <span class="badge bg-white text-danger ms-1">
                                {{ $trashCount ?? 0 }}
                            </span>
                        </a>

                    </div>
                </div>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                        </button>
                    </div>
                @endif
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>S:NO</th>
                                        <th>Contact Person</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Company</th>
                                        <th>Assigned User</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leads as $lead)
                                        <tr>
                                            <td>
                                                {{ $loop->iteration }}
                                            </td>
                                            <td>
                                                <strong>
                                                    {{ $lead->contact_person ?? '-' }}
                                                </strong>
                                                @if ($lead->client)
                                                    <span class="badge bg-soft-success text-success fs-11 mt-1 d-block" style="width: fit-content;">
                                                        <i class="mdi mdi-account-check me-1"></i> Client #{{ $lead->client->id }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $lead->phone ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $lead->email ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $lead->company->company_name ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $lead->user->name ?? '-' }}
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('lead.show', $lead->id) }}"
                                                        class="btn btn-sm btn-outline-info" title="View">

                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('lead.edit', $lead->id) }}"
                                                        class="btn btn-sm btn-outline-success" title="Edit">

                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('lead.delete', $lead->id) }}" method="POST"
                                                        onsubmit="return confirm('Move to trash?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" title="Delete">

                                                            <i class="mdi mdi-delete"></i>

                                                        </button>
                                                    </form>
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
