@extends('layout.master')

@section('title', 'Create Role')
@section('header-title', 'Create Role')

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
                            <div class="card-header">
                                <h5 class="mb-0">Create Role</h5>
                            </div>

                            <div class="card-body">

                                <form action="{{ route('role.store') }}" method="POST">
                                    @csrf

                                    <div class="row">

                                        <!-- ROLE NAME -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Role Name <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="name" class="form-control"
                                                    placeholder="Enter role name">
                                            </div>
                                        </div>

                                        <!-- SELECT ALL -->
                                        <div class="col-lg-6 d-flex align-items-end">
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="checkAll">
                                                <label class="form-check-label" for="checkAll">
                                                    Select All Permissions
                                                </label>
                                            </div>
                                        </div>

                                    </div>

                                    <hr>

                                    <!-- PERMISSIONS -->
                                    <div class="row">

                                        @php
                                            $groupedPermissions = $permissions->groupBy('group_name');
                                        @endphp

                                        @foreach ($groupedPermissions as $module => $perms)
                                            <div class="col-12 mt-3">
                                                <h5 class="text-primary text-uppercase">
                                                    {{ ucfirst(str_replace('_', ' ', $module)) }}
                                                </h5>
                                            </div>

                                            <div class="col-12 mb-2">
                                                <div class="row">

                                                    @foreach ($perms as $permission)
                                                        <div class="col-lg-4 col-md-6 mb-2">

                                                            <div class="form-check">
                                                                <input type="checkbox"
                                                                    class="form-check-input permission-checkbox"
                                                                    name="permissions[]" value="{{ $permission->id }}">

                                                                <label class="form-check-label">
                                                                    {{ ucfirst(str_replace('_', ' ', $permission->name)) }}
                                                                </label>
                                                            </div>

                                                        </div>
                                                    @endforeach

                                                </div>
                                            </div>

                                            <hr>
                                        @endforeach

                                    </div>

                                    <!-- SUBMIT -->
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary px-4">
                                                Submit
                                            </button>
                                        </div>
                                    </div>

                                </form>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- CHECK ALL SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('checkAll');
            const checkboxes = document.querySelectorAll('.permission-checkbox');

            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = checkAll.checked);
            });
        });
    </script>

@endsection
