@extends('layout.master')

@section('title', 'Package Trash')

@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Package / <strong>Trash</strong></h4>
                    <a href="{{ route('package.index') }}" class="btn btn-secondary btn-sm">
                        Back to Packages
                    </a>
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
                            <table class="table table-bordered nowrap align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Package Name</th>
                                        <th>Deleted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($packages as $package)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $package->name }}</strong></td>
                                            <td>{{ $package->deleted_at->format('d M, Y') }}</td>
                                            <td>
                                                <a href="{{ route('package.restore', $package->id) }}"
                                                    class="btn btn-sm btn-outline-success" title="Restore">
                                                    <i class="mdi mdi-restore"></i> Restore
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                Trash is empty.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $packages->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
