@extends('layout.master')
@section('title', 'Room Types Trash')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fs-18 fw-semibold m-0">Room Types - Trash</h4>
                        <p class="text-muted small mb-0">Restore or permanently manage deleted room types</p>
                    </div>
                    <a href="{{ route('room-type.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Room Types
                    </a>
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
                            <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px">#</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th class="text-center">Capacity</th>
                                        <th>Deleted At</th>
                                        <th class="text-center" style="width:100px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roomTypes as $rt)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $rt->name }}</strong></td>
                                            <td><code>{{ $rt->code ?? '—' }}</code></td>
                                            <td class="text-center">{{ $rt->capacity }} Pax</td>
                                            <td>{{ $rt->deleted_at ? $rt->deleted_at->format('d M Y, h:i A') : '—' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('room-type.restore', $rt->id) }}"
                                                    class="btn btn-sm btn-success" title="Restore"
                                                    onclick="return confirm('Restore this room type?')">
                                                    <i class="mdi mdi-restore me-1"></i> Restore
                                                </a>
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
