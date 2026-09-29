@extends('layout.master')
@section('title', 'Airline Trash')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Airline Trash</h4>
                    <a href="{{ route('airline.index') }}" class="btn btn-secondary btn-sm">← Back</a>
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
                                        <th>Logo</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Deleted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($airlines as $airline)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if ($airline->logo)
                                                    <img src="{{ asset('storage/' . $airline->logo) }}" alt="Logo" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <span class="badge bg-light text-muted">No Logo</span>
                                                @endif
                                            </td>
                                            <td><strong>{{ $airline->name }}</strong></td>
                                            <td>{{ $airline->code }}</td>
                                            <td>{{ $airline->deleted_at->format('d M Y, h:i A') }}</td>
                                            <td>
                                                @can('airline_restore')
                                                    <a href="{{ route('airline.restore', $airline->id) }}"
                                                        class="btn btn-sm btn-outline-success">
                                                        <i class="mdi mdi-restore me-1"></i> Restore
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Trash is empty.</td>
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
