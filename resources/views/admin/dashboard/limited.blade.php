@extends('admin.layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-12 text-center">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    
                    <h2 class="fw-bold text-dark mb-3">Welcome, {{ auth()->user()->name }}!</h2>
                    <p class="lead text-muted mb-4">
                        You have successfully logged into the administration panel. <br>
                        However, your account currently has <strong>limited access</strong> to the analytics dashboard.
                    </p>
                    
                    <div class="row g-3 justify-content-center mb-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded-4 bg-light">
                                <i class="fa-solid fa-user-shield text-primary mb-2 fs-4"></i>
                                <h6 class="fw-bold mb-1">Your Role</h6>
                                <p class="small text-muted mb-0">{{ auth()->user()->roles->pluck('name')->first() ?? 'No Role' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-4 bg-light">
                                <i class="fa-solid fa-clock text-info mb-2 fs-4"></i>
                                <h6 class="fw-bold mb-1">Last Login</h6>
                                <p class="small text-muted mb-0">{{ now()->format('M d, H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 rounded-4 mb-4">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        If you believe you should have access to more features, please contact your system administrator.
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="fa-solid fa-user-gear me-2"></i>Update Profile
                        </a>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger rounded-pill px-4">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
