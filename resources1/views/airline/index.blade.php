@extends('layout.master')
@section('title', 'Airlines')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Airlines</h4>
                    <div class="d-flex gap-2">
                        @can('airline_create')
                            <a href="{{ route('airline.create') }}" class="btn btn-primary btn-sm">
                                + Add Airline
                            </a>
                        @endcan
                        @can('airline_trash_view')
                            <a href="{{ route('airline.trash') }}" class="btn btn-danger btn-sm">
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
                                        <th>S:NO</th>
                                        <th>Logo</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>IATA Code</th>
                                        <th>ICAO Code</th>
                                        <th>Country</th>
                                        <th>Call Sign</th>
                                        <th>FF Number</th>
                                        <th>Status</th>
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
                                            <td>{{ $airline->iata_code ?? '—' }}</td>
                                            <td>{{ $airline->icao_code ?? '—' }}</td>
                                            <td>{{ $airline->country ?? '—' }}</td>
                                            <td>{{ $airline->call_sign ?? '—' }}</td>
                                            <td>{{ $airline->ffnumber ?? '—' }}</td>
                                            <td>
                                                <span class="badge {{ $airline->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($airline->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('airline.show', $airline->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Show">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    @can('airline_edit')
                                                        <a href="{{ route('airline.edit', $airline->id) }}"
                                                            class="btn btn-sm btn-outline-success" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('airline_trash')
                                                        <form action="{{ route('airline.delete', $airline->id) }}" method="POST"
                                                            onsubmit="return confirm('Move to trash?')">
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
