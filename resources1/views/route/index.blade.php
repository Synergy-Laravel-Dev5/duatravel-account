@extends('layout.master')
@section('title', 'Routes')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Routes</h4>
                    <div class="d-flex gap-2">
                        @can('route_create')
                            <a href="{{ route('route.create') }}" class="btn btn-primary btn-sm">
                                + Add Route
                            </a>
                        @endcan
                        @can('route_trash_view')
                            <a href="{{ route('route.trash') }}" class="btn btn-danger btn-sm">
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
                                        <th>Start Place</th>
                                        <th>End Place</th>
                                        <th>Route Path</th>
                                        <th>Status</th>
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
                                            <td>
                                                <span class="badge {{ $route->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($route->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    @can('route_edit')
                                                        <a href="{{ route('route.edit', $route->id) }}"
                                                            class="btn btn-sm btn-outline-success" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('route_trash')
                                                        <form action="{{ route('route.delete', $route->id) }}"
                                                            method="POST" onsubmit="return confirm('Move to trash?')">
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
