@extends('layout.master')
@section('title', 'Client Trash')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Client Trash</h4>
                    <a href="{{ route('client.index') }}" class="btn btn-secondary btn-sm">← Back</a>
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
                                        <th>Type</th>
                                        <th>Phone</th>
                                        <th>Deleted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($clients as $client)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $client->name }}</strong></td>
                                            <td>{{ $client->company_name ?? '—' }}</td>
                                            <td>
                                                @php $types=['client'=>'bg-primary','vendor'=>'bg-info','customer'=>'bg-secondary']; @endphp
                                                <span
                                                    class="badge {{ $types[$client->type] ?? 'bg-secondary' }}">{{ ucfirst($client->type) }}</span>
                                            </td>
                                            <td>{{ $client->phone ?? '—' }}</td>
                                            <td>{{ $client->deleted_at->format('d M Y, h:i A') }}</td>
                                            <td>
                                                @can('client_restore')
                                                <a href="{{ route('client.restore', $client->id) }}"
                                                    class="btn btn-sm btn-outline-success">
                                                    <i class="mdi mdi-restore me-1"></i> Restore
                                                </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Trash is empty.</td>
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
