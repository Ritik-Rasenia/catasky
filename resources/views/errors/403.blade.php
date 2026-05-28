@extends('admin.layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-center" style="min-height: 70vh;">
        <div class="card border-0 shadow-lg text-center p-5 rounded-5" style="max-width: 580px; background: var(--surface-color); border: 1px solid var(--border-color) !important;">
            
            <!-- Animated SVG Padlock -->
            <div class="mb-4 d-flex justify-content-center">
                <div class="lock-wrapper position-relative bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" fill="currentColor" class="bi bi-shield-lock-fill text-danger lock-icon" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.268-2.9 1.277-1.02.975-1.924 2.18-2.64 3.005-.404.466-.734.902-.977 1.246-.244.346-.382.597-.382.721v3.472a12.8 12.8 0 0 0 2.254 7.222c1.298 1.84 2.872 3.119 4.095 3.738a1 1 0 0 0 .902 0c1.223-.619 2.797-1.898 4.095-3.738A12.8 12.8 0 0 0 13.9 8.722V5.25c0-.124-.138-.375-.382-.721a12.3 12.3 0 0 0-.978-1.246c-.715-.824-1.619-2.03-2.639-3.005C8.843.268 7.69 0 8 0zm0 5a1.5 1.5 0 0 0-1 2.5v1.5a.5.5 0 0 0 1 0V7.5A1.5 1.5 0 0 0 8 5z"/>
                    </svg>
                </div>
            </div>

            <h2 class="fw-extrabold brand-font mb-2 text-dark">Access Restricted</h2>
            <p class="text-muted mb-4 small" style="line-height: 1.6;">
                You do not have the required role clearances or granular permissions to access this administrative node. Your current authorization profile is restricted.
            </p>

            <!-- Role Badge -->
            <div class="p-3 mb-4 rounded-4 d-inline-flex align-items-center gap-2 border" style="background: var(--surface-muted);">
                <i class="bi bi-person-badge text-primary fs-5"></i>
                <div class="text-start">
                    <div class="smaller text-muted text-uppercase fw-bold">Active Role</div>
                    <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $role ?? 'User' }}</span>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4  fw-bold" style="background: var(--primary-color); border: none;">
                    <i class="bi bi-grid-1x2 me-2"></i>Go to Dashboard
                </a>
                <a href="mailto:{{ optional(App\Models\Setting::first())->admin_email ?? 'support@example.com' }}" class="btn btn-light rounded-pill px-4 border fw-bold text-muted">
                    <i class="bi bi-envelope me-2"></i>Contact Admin
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .lock-wrapper {
        animation: lock-pulse 2.2s infinite;
    }
    .lock-icon {
        animation: lock-wobble 4s ease infinite;
    }
    @keyframes lock-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
        }
        70% {
            box-shadow: 0 0 0 14px rgba(239, 68, 68, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }
    @keyframes lock-wobble {
        0%, 100% { transform: rotate(0deg); }
        92% { transform: rotate(0deg); }
        94% { transform: rotate(-8deg); }
        96% { transform: rotate(8deg); }
        98% { transform: rotate(-4deg); }
    }
    .fw-extrabold { font-weight: 800; }
    .smaller { font-size: 0.72rem; }
</style>
@endsection
