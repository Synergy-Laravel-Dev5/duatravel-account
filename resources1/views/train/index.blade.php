@extends('layout.master')
@section('title', 'Trains')
@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Trains</h4>
                    <div class="d-flex gap-2">
                        @can('train_create')
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTrainModal">
                                + Add Train
                            </button>
                        @endcan
                        @can('train_trash_view')
                            <a href="{{ route('train.trash') }}" class="btn btn-danger btn-sm">
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

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
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
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($trains as $train)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $train->code ?? '—' }}</td>
                                            <td><strong>{{ $train->name ?? '—' }}</strong></td>
                                            <td>
                                                <span class="badge {{ $train->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($train->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    @can('train_edit')
                                                        <button type="button" class="btn btn-sm btn-outline-success edit-train-btn" 
                                                            data-id="{{ $train->id }}"
                                                            data-name="{{ $train->name }}"
                                                            data-code="{{ $train->code }}"
                                                            data-status="{{ $train->status }}"
                                                            title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                    @endcan
                                                    @can('train_trash')
                                                        <form action="{{ route('train.delete', $train->id) }}" method="POST"
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

    <!-- CREATE TRAIN MODAL -->
    @can('train_create')
    <div class="modal fade" id="createTrainModal" tabindex="-1" aria-labelledby="createTrainModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTrainModalLabel">Add New Train</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('train.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Train Code</label>
                            <input type="text" name="code" class="form-control" placeholder="Enter Train Code..." value="{{ old('code') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Train Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Train Name..." value="{{ old('name') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Train</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <!-- EDIT TRAIN MODAL -->
    @can('train_edit')
    <div class="modal fade" id="editTrainModal" tabindex="-1" aria-labelledby="editTrainModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTrainModalLabel">Edit Train</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST" id="editTrainForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Train Code</label>
                            <input type="text" name="code" id="edit_code" class="form-control" placeholder="Enter Train Code...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Train Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" placeholder="Enter Train Name...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Update Train</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

     <script>
        const trainUpdateBaseUrl = "{{ route('train.update', ':id') }}";

        document.addEventListener('DOMContentLoaded', function () {
            const editModal = new bootstrap.Modal(document.getElementById('editTrainModal'));
            const editForm = document.getElementById('editTrainForm');
            const editName = document.getElementById('edit_name');
            const editCode = document.getElementById('edit_code');
            const editStatus = document.getElementById('edit_status');

            document.querySelectorAll('.edit-train-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const code = this.getAttribute('data-code');
                    const status = this.getAttribute('data-status');

                    editForm.action = trainUpdateBaseUrl.replace(':id', id);

                    editName.value = name;
                    editCode.value = code;
                    editStatus.value = status;

                    editModal.show();
                });
            });
        });
    </script>

@endsection
