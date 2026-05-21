@extends('admin.layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Admin Profile</h3>
            <p class="text-muted mb-0">Manage your personal information and password</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Update Profile Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4 text-center">
                            @if($user->profile_image)
                                <img src="{{ asset('uploads/profile/'.$user->profile_image) }}" width="120" height="120" class="rounded-circle object-fit-cover shadow-sm mb-3">
                            @else
                                <img src="{{ asset('uploads/logo.png') }}" width="120" height="120" class="rounded-circle object-fit-cover shadow-sm mb-3" alt="Default Profile">
                            @endif
                            <input type="file" name="profile_image" class="form-control form-control-sm @error('profile_image') is-invalid @enderror" accept="image/png, image/jpeg, image/jpg, image/webp">
                            @error('profile_image') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <button class="btn btn-primary px-4">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Update Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.password') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="6">
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @else
                                <small class="text-muted">Minimum 6 characters.</small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                        </div>

                        <button class="btn btn-primary px-4">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
