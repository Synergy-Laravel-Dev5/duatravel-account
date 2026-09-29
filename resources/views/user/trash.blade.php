@extends('layout.master')

@section('title', 'Deleted Users')
@section('header-title', 'Deleted Users')

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

                            <!-- HEADER -->
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Deleted Users</h5>
                            </div>

                            <!-- BODY -->
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table id="example2" class="table table-bordered table-striped">

                                        <thead>
                                            <tr>
                                                <th>S:NO</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($users as $user)
                                                <tr>

                                                    <td>{{ $loop->iteration }}</td>

                                                    <td>
                                                        <strong>{{ $user->name ?? 'N/A' }}</strong>
                                                    </td>

                                                    <td>{{ $user->email ?? 'N/A' }}</td>

                                                    <td>
                                                        @if ($user->roles->isNotEmpty())
                                                            @foreach ($user->roles as $role)
                                                                <span class="badge bg-primary">
                                                                    {{ ucfirst($role->name) }}
                                                                </span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if ($user->status == 'active')
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-danger">Inactive</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @can('user_restore')
                                                            <a href="{{ route('user.restore', $user->id) }}"
                                                                class="btn btn-sm btn-success"
                                                                onclick="return confirm('Restore this user?')">
                                                                Restore
                                                            </a>
                                                        @endcan
                                                    </td>

                                                </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
