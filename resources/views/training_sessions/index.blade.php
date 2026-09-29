@extends('layout.master')

@section('title', 'Training Sessions')

@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Training Sessions</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('training-session.create') }}" class="btn btn-primary btn-sm">
                            + Add Training Session
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
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
                                        <th>Session Name</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Venue</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($trainingSessions as $session)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $session->name }}</strong></td>
                                            <td>{{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->format('d M, Y') : '-' }}</td>
                                            <td>{{ $session->session_time ? \Carbon\Carbon::parse($session->session_time)->format('h:i A') : '-' }}</td>
                                            <td>{{ $session->venue ?? '-' }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($session->description, 50) ?? '-' }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('training-session.edit', $session->id) }}"
                                                        class="btn btn-sm btn-outline-success" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('training-session.delete', $session->id) }}"
                                                        method="POST" onsubmit="return confirm('Are you sure you want to delete this training session?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No training sessions found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $trainingSessions->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
