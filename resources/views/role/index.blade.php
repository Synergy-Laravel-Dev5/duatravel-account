@extends('layout.master')

@section('title', 'Roles')
@section('header-title', 'Roles')

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

                                <h5 class="mb-0">Roles List</h5>

                                <div class="d-flex gap-2">

                                    @can('role_trash_view')
                                        <a href="{{ route('role.trash') }}"
                                            class="btn btn-danger btn-sm d-flex align-items-center gap-1">
                                            <i class="material-icons-outlined"></i>
                                            Trash <span>{{ $trashrole ?? 0 }}</span>
                                        </a>
                                    @endcan

                                    @can('role_create')
                                        <a href="{{ route('role.create') }}"
                                            class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                                            <i class="material-icons-outlined"></i>
                                            Add Role
                                        </a>
                                    @endcan

                                </div>

                            </div>

                            <!-- BODY -->
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table class="table align-middle table-nowrap" id="customerTable">

                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            @foreach ($roles as $role)
                                                <tr>

                                                    <td>
                                                        <strong>{{ ucfirst($role->name) }}</strong>
                                                    </td>

                                                    <td>
                                                        <div class="d-flex gap-1">
                                                            @can('role_edit')
                                                                <a href="{{ route('role.edit', $role->id) }}"
                                                                    class="btn btn-sm btn-outline-success" title="Edit">
                                                                    <i class="mdi mdi-pencil"></i>
                                                                </a>
                                                            @endcan
                                                            @can('role_trash')
                                                                <form action="{{ route('role.delete', $role->id) }}"
                                                                    method="POST" onsubmit="return confirm('Move to trash?')">
                                                                    @csrf @method('DELETE')
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
