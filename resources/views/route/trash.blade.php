@extends('layout.master')
@section('title', 'Trashed Routes')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Trashed Routes</h4>
                    <a href="{{ route('route.index') }}" class="btn btn-secondary btn-sm">Back to Routes</a>
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
                                        <th>Start Place</th>
                                        <th>End Place</th>
                                        <th>Route Path</th>
                                        <th>Deleted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($routes as $route)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $route->start_place }}</strong></td>
                                            <td><strong>{{ $route->end_place }}</strong></td>
                                            <td>
                                                <span class="badge bg-info text-dark fs-13">
                                                    {{ $route->formatted_route }}
                                                </span>
                                            </td>
                                            <td>{{ $route->deleted_at ? $route->deleted_at->format('Y-m-d H:i') : '—' }}</td>
                                            <td>
                                                @can('route_restore')
                                                    <a href="{{ route('route.restore', $route->id) }}"
                                                        class="btn btn-sm btn-outline-success" title="Restore">
                                                        <i class="mdi mdi-restore"></i> Restore
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No trashed routes found.</td>
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
