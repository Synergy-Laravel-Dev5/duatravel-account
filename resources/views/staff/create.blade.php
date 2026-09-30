@extends('layout.master')
@section('title', 'Add Staff')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Add Staff</h4>
                    <a href="{{ route('staff.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('staff.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" id="department_id" class="form-select select2" required>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Designation <span class="text-danger">*</span></label>
                                    <select name="designation_id" id="designation_id" class="form-select select2" required>
                                         @foreach($designations as $desig)
                                            <option value="{{ $desig->id }}" data-dept="{{ $desig->department_id }}" {{ old('designation_id') == $desig->id ? 'selected' : '' }}>
                                                {{ $desig->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Salary</label>
                                    <input type="number" name="salary" class="form-control" value="{{ old('salary') }}" step="0.01">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Joining Date</label>
                                    <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Save Staff</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2();
        }
        
        // Filter designations by department
        var designations = $('#designation_id option').clone();
        
        $('#department_id').change(function() {
            var dept_id = $(this).val();
            $('#designation_id').empty();
            $('#designation_id').append('<option value="">Select Designation</option>');
            
            designations.each(function() {
                if ($(this).attr('data-dept') == dept_id || $(this).val() == "") {
                    $('#designation_id').append($(this).clone());
                }
            });
        });
        
        // Trigger change on load if department is selected (for old input)
        if($('#department_id').val() != '') {
            var selected_desig = "{{ old('designation_id') }}";
            $('#department_id').trigger('change');
            if(selected_desig) {
                $('#designation_id').val(selected_desig);
            }
        }
    });
</script>
@endpush
