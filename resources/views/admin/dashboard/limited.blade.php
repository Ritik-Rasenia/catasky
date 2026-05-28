@extends('admin.layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-12 text-center d-flex justify-content-center">
            <div class="card border-0 shadow-lg p-5 rounded-5" style="max-width: 600px; background: var(--surface-color); border: 1px solid var(--border-color) !important;">
                
                <!-- Animated SVG Shield Lock -->
                <div class="mb-4 d-flex justify-content-center">
                    <div class="shield-wrapper position-relative bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" fill="currentColor" class="bi bi-shield-check text-primary shield-icon" viewBox="0 0 16 16">
                            <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a1 1 0 0 0 .588 0 3.613 3.613 0 0 0 .294-.118c.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.03 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                            <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </div>
                </div>

                <h2 class="fw-extrabold brand-font text-dark mb-3">Welcome, {{ auth()->user()->name }}!</h2>
                <p class="lead text-muted mb-4 small" style="line-height: 1.6;">
                    You have successfully authenticated and entered the administrative workspace. <br>
                    However, your account currently has <strong>limited access</strong> to the analytics dashboard.
                </p>
                
                <div class="row g-3 justify-content-center mb-4">
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-4" style="background: var(--surface-muted);">
                            <i class="bi bi-shield-lock text-primary mb-2 fs-4"></i>
                            <h6 class="fw-bold mb-1 text-dark">Active Profile</h6>
                            <p class="small text-muted mb-0">{{ auth()->user()->roles->pluck('name')->first() ?? 'No Role Assigned' }}</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-4" style="background: var(--surface-muted);">
                            <i class="bi bi-clock text-info mb-2 fs-4"></i>
                            <h6 class="fw-bold mb-1 text-dark">Current Session</h6>
                            <p class="small text-muted mb-0">{{ now()->format('M d, H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning border-0 rounded-4 mb-4 small d-flex align-items-center gap-2 text-start">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                    <div>If you believe you require additional granular modules or permission node authorizations, please contact your system administrator.</div>
                </div>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary rounded-pill px-4  fw-bold" style="background: var(--primary-color); border:none;">
                        <i class="bi bi-person-gear me-2"></i>Update Profile
                    </a>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger rounded-pill px-4 fw-bold">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
