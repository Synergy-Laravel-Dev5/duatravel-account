@extends('layout.master')

@section('title', 'User Activities')
@section('header-title', 'User Activities')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container">

                <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 fw-semibold m-0">Dashboard</h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">User Activities List</h5>
                            </div>

                            <div class="card-body">

                                <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle w-100">

                                    <thead class="table-light">
                                        <tr>
                                            <th>S:NO</th>
                                            <th>User Name</th>
                                            <th>Activity Type</th>
                                            <th>Details</th>
                                            <th>IP Address</th>
                                            <th>Created At</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($userActivities as $user)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>

                                                <td>
                                                    <strong>{{ $user->user->name ?? 'System' }}</strong>
                                                </td>

                                                <td>
                                                    <span class="badge bg-success px-3 py-2">
                                                        {{ ucfirst(str_replace('_', ' ', $user->activity_type ?? 'N/A')) }}
                                                    </span>
                                                </td>

                                                <td>{{ $user->details ?? '-' }}</td>

                                                <td><code>{{ $user->ip_address ?? '-' }}</code></td>

                                                <td>{{ $user->created_at ? $user->created_at->format('d M Y h:i A') : '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No User Activities Found</td>
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
    </div>

@endsection
