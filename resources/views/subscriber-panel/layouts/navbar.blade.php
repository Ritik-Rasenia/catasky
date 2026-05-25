<nav id="top-navbar" class="glass-effect px-4 py-2 border-bottom d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <div class="d-none d-md-block">
            <h5 class="mb-0 fw-bold brand-font text-dark">
                @yield('page-title', 'Subscriber Workspace')
            </h5>
        </div>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <!-- Live Frontend Link -->
        <a href="{{ route('home') }}" target="_blank" class="btn btn-white btn-sm rounded-pill px-3 shadow-sm d-none d-lg-flex align-items-center gap-2 me-2 border text-muted">
            <i class="bi bi-box-arrow-up-right"></i> View Site
        </a>

        @php
            $currentUser = auth()->user();
            $currentRole = $currentUser?->roles?->pluck('name')->first() ?? 'User';
        @endphp

        <!-- User Profile -->
        <div class="dropdown ms-2">
            <a href="#" class="d-flex align-items-center text-decoration-none" id="userDropdown" data-bs-toggle="dropdown">
                <div class="me-2 text-end d-none d-sm-block">
                    <div class="fw-bold text-dark small">{{ $currentUser->name }}</div>
                    <div class="text-muted extra-small">{{ $currentRole }}</div>
                </div>
                <div class="position-relative">
                    @if($currentUser->profile_image)
                        <img src="{{ asset('uploads/profile/'.$currentUser->profile_image) }}" alt="User" class="rounded-circle object-fit-cover shadow-sm border border-light" width="40" height="40">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($currentUser->name) }}&background=4f46e5&color=fff" alt="User" class="rounded-circle shadow-sm border border-light" width="40" height="40">
                    @endif
                    <span class="position-absolute bottom-0 end-0 bg-success border border-white border-2 rounded-circle" style="width: 12px; height: 12px;"></span>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 rounded-4 p-2" aria-labelledby="userDropdown" style="min-width: 200px;">
                <li><a class="dropdown-item rounded-3 py-2" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2 me-2"></i> Dashboard Overview</a></li>
                <li><a class="dropdown-item rounded-3 py-2" href="{{ route('subscriber.profile.edit') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('subscriber.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item rounded-3 py-2 text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    .extra-small { font-size: 0.75rem; }
    .glass-effect {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(12px);
    }
    html[data-theme="dark"] .glass-effect {
        background: rgba(17, 24, 39, 0.8) !important;
    }
</style>
