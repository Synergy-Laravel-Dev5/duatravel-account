@extends('layout.master')

@section('title', 'Users')
@section('header-title', 'Users')

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

                                <h5 class="mb-0">Users List</h5>

                                <div class="d-flex gap-2">

                                    @can('user_trash_view')
                                        <a href="{{ route('user.trash') }}"
                                            class="btn btn-danger btn-sm d-flex gap-1 align-items-center">
                                            <i class="material-icons-outlined"></i>
                                            Trash <span>{{ $trashrole ?? 0 }}</span>
                                        </a>
                                    @endcan

                                    @can('user_create')
                                        <a href="{{ route('user.create') }}"
                                            class="btn btn-primary btn-sm d-flex gap-1 align-items-center">
                                            <i class="material-icons-outlined"></i>
                                            Add User
                                        </a>
                                    @endcan

                                </div>

                            </div>

                            <!-- BODY -->
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle">

                                        <thead class="table-light">
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
                                                        <div class="d-flex gap-2">

                                                            @can('user_edit')
                                                                <a href="{{ route('user.edit', $user->id) }}"
                                                                    class="btn btn-sm btn-outline-success">
                                                                    <i class="mdi mdi-pencil"></i>
                                                                </a>
                                                            @endcan

                                                            @can('user_trash')
                                                                <form action="{{ route('user.delete', $user->id) }}"
                                                                    method="POST" onsubmit="return confirm('Are you sure?');">
                                                                    @csrf
                                                                    @method('DELETE')

                                                                    <button class="btn btn-sm btn-outline-danger"
                                                                        title="Delete">
                                                                        <i class="mdi mdi-delete"></i>
                                                                    </button>
                                                                </form>
                                                            @endcan

                                                        </div>
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
