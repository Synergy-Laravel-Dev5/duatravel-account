@extends('layout.master')

@section('title', 'Edit User')
@section('header-title', 'Edit User')

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
                                <h5 class="mb-0">Edit User</h5>
                            </div>

                            <div class="card-body">

                                <form action="{{ route('user.update', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">

                                        <!-- NAME -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" value="{{ $user->name }}"
                                                    class="form-control" placeholder="Enter user name">
                                            </div>
                                        </div>

                                        <!-- EMAIL -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" value="{{ $user->email }}"
                                                    class="form-control" placeholder="Enter email">
                                            </div>
                                        </div>

                                        <!-- STATUS -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-control">
                                                    <option disabled>Select status</option>
                                                    <option value="active"
                                                        {{ $user->status == 'active' ? 'selected' : '' }}>
                                                        Active
                                                    </option>
                                                    <option value="inactive"
                                                        {{ $user->status == 'inactive' ? 'selected' : '' }}>
                                                        Inactive
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- ROLES -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Assign Roles</label>

                                                <select name="roles[]" id="roles" class="form-control select2" multiple>

                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->name }}"
                                                            {{ $user->roles->contains('name', $role->name) ? 'selected' : '' }}>
                                                            {{ ucfirst($role->name) }}
                                                        </option>
                                                    @endforeach

                                                </select>

                                            </div>
                                        </div>

                                    </div>

                                    <!-- BUTTON -->
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary px-4">
                                                Update User
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

    <script>
        $(document).ready(function() {
            $('#roles').select2({
                placeholder: "Select Role(s)"
            });
        });
    </script>

@endsection
