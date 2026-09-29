@extends('layout.master')
@section('title', 'Hotels')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Hotels</h4>
                    <div class="d-flex gap-2">
                        @can('hotel_create')
                            <a href="{{ route('hotel.create') }}" class="btn btn-primary btn-sm">
                                + Add Hotel
                            </a>
                        @endcan
                        @can('hotel_trash_view')
                            <a href="{{ route('hotel.trash') }}" class="btn btn-danger btn-sm">
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
                                        <th>Hotel Number</th>
                                        <th>Place</th>
                                        <th>Type</th>
                                        <th>Category</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($hotels as $hotel)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if ($hotel->logo)
                                                    <img src="{{ asset('storage/' . $hotel->logo) }}" alt="Logo" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <span class="badge bg-light text-muted">No Logo</span>
                                                @endif
                                            </td>
                                            <td><strong>{{ $hotel->name }}</strong></td>
                                            <td>{{ $hotel->code }}</td>
                                            <td>{{ $hotel->hotel_number }}</td>
                                            <td>{{ $hotel->place->value ?? $hotel->place }}</td>
                                            <td>{{ $hotel->accommodation_type->value ?? $hotel->accommodation_type }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    {{ $hotel->accommodation_category->value ?? $hotel->accommodation_category }}
                                                </span>
                                            </td>
                                            <td>{{ $hotel->contact ?? '—' }}</td>
                                            <td>
                                                <span class="badge {{ $hotel->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($hotel->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('hotel.show', $hotel->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Show">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    @can('hotel_edit')
                                                        <a href="{{ route('hotel.edit', $hotel->id) }}"
                                                            class="btn btn-sm btn-outline-success" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('hotel_trash')
                                                        <form action="{{ route('hotel.delete', $hotel->id) }}" method="POST"
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
