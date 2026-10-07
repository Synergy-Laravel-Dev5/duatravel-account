@extends('layout.master')
@section('title', 'Room Types')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fs-18 fw-semibold m-0">Room Types</h4>
                        <p class="text-muted small mb-0">Manage accommodation room types (Single, Double, Triple, Quad, Sharing, Suites, etc.)</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('room-type.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus-circle me-1"></i> Add Room Type
                        </a>
                        <a href="{{ route('room-type.trash') }}" class="btn btn-danger btn-sm">
                            <i class="mdi mdi-delete-outline me-1"></i> Trash <span class="badge bg-white text-danger ms-1">{{ $trashCount }}</span>
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover table-bordered dt-responsive nowrap align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px">#</th>
                                        <th>Room Type Name</th>
                                        <th>Code</th>
                                        <th class="text-center">Capacity (Beds/Pax)</th>
                                        <th>Description</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width:120px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roomTypes as $rt)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong class="text-dark">{{ $rt->name }}</strong>
                                            </td>
                                            <td>
                                                <code class="text-primary fw-semibold">{{ $rt->code ?? '—' }}</code>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info-subtle text-info border border-info px-2 py-1">
                                                    <i class="mdi mdi-bed me-1"></i>{{ $rt->capacity }} {{ $rt->capacity > 1 ? 'Persons' : 'Person' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $rt->description ?? '—' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $rt->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($rt->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="{{ route('room-type.edit', $rt->id) }}"
                                                        class="btn btn-sm btn-outline-success" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('room-type.delete', $rt->id) }}" method="POST"
                                                        onsubmit="return confirm('Move this room type to trash?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No Room Types found. Click "+ Add Room Type" to create one.</td>
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
