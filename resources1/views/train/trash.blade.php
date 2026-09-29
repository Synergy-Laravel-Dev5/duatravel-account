@extends('layout.master')
@section('title', 'Train Trash')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Train Trash</h4>
                    <a href="{{ route('train.index') }}" class="btn btn-secondary btn-sm">← Back</a>
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
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Deleted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($trains as $train)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $train->code ?? '—' }}</td>
                                            <td><strong>{{ $train->name ?? '—' }}</strong></td>
                                            <td>
                                                <span
                                                    class="badge {{ $train->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($train->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $train->deleted_at->format('d M Y, h:i A') }}</td>
                                            <td>
                                                @can('train_restore')
                                                    <a href="{{ route('train.restore', $train->id) }}"
                                                        class="btn btn-sm btn-outline-success">
                                                        <i class="mdi mdi-restore me-1"></i> Restore
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Trash is empty.</td>
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
