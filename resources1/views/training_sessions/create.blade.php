@extends('layout.master')

@section('title', 'Create Training Session')

@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Training Session / <strong>Create</strong></h4>
                    <a href="{{ route('training-session.index') }}" class="btn btn-secondary btn-sm">
                        Back to List
                    </a>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="m-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('training-session.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label"><b>Session Name <span class="text-danger">*</span></b></label>
                                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter session name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="session_date" class="form-label"><b>Session Date</b></label>
                                        <input type="date" id="session_date" name="session_date" class="form-control" value="{{ old('session_date') }}">
                                        @error('session_date')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="session_time" class="form-label"><b>Session Time</b></label>
                                        <input type="time" id="session_time" name="session_time" class="form-control" value="{{ old('session_time') }}">
                                        @error('session_time')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="venue" class="form-label"><b>Venue / Location</b></label>
                                        <input type="text" id="venue" name="venue" class="form-control" placeholder="Enter venue or online link" value="{{ old('venue') }}">
                                        @error('venue')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label"><b>Description / Notes</b></label>
                                        <textarea id="description" name="description" rows="4" class="form-control" placeholder="Enter session description or notes...">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Save Session</button>
                                <a href="{{ route('training-session.index') }}" class="btn btn-light ms-1">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
