@extends('layout.master')

@section('title', 'Deleted Roles')
@section('header-title', 'Deleted Roles')

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
                                <h5 class="mb-0">Deleted Roles</h5>
                            </div>

                            <!-- BODY -->
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table id="example2" class="table table-bordered table-striped">

                                        <thead>
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

                                                    <td class="text-center">

                                                        <a href="{{ route('role.restore', $role->id) }}"
                                                            class="btn btn-sm btn-success"
                                                            onclick="return confirm('Restore this role?')">
                                                            Restore
                                                        </a>

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
