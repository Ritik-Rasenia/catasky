<nav id="top-navbar" class="glass-effect px-4 py-2 border-bottom d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <div class="d-none d-md-block">
            <h5 class="mb-0 fw-bold brand-font text-dark">
                @yield('page-title', 'Analytical Panel')
            </h5>
        </div>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <!-- Live Frontend Link -->
        <a href="{{ route('home') }}" target="_blank" class="btn btn-white btn-sm rounded-pill px-3 shadow-sm d-none d-lg-flex align-items-center gap-2 me-2 border text-muted">
            <i class="bi bi-box-arrow-up-right"></i> View Site
        </a>



        @php
            $unreadEnquiries = \App\Models\Enquiry::where('is_read', false)->latest()->take(5)->get();
            $unreadCount = \App\Models\Enquiry::where('is_read', false)->count();
            $currentUser = auth()->user();
            $currentRole = $currentUser?->roles?->pluck('name')->first() ?? 'User';
        @endphp
        
        <!-- Notifications with pulse badge -->
        <div class="dropdown">
            <button class="btn btn-light position-relative rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" id="notifDropdown" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white pulse-badge" style="font-size: 0.6rem; padding: 4px 6px;">
                        {{ $unreadCount }}
                    </span>
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-0 rounded-4 overflow-hidden" aria-labelledby="notifDropdown" style="width: 320px;">
                <li class="p-3 bg-light d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold">Inquiries</h6>
                    @if($unreadCount > 0)
                        <span class="badge bg-primary rounded-pill">{{ $unreadCount }} New</span>
                    @endif
                </li>
                <div class="overflow-auto" style="max-height: 350px;">
                    @forelse($unreadEnquiries as $notif)
                    <li>
                        <a class="dropdown-item p-3 border-bottom d-flex align-items-start gap-3" href="{{ route('admin.enquiries.show', $notif->id) }}">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 flex-shrink-0" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark text-truncate small">{{ $notif->name }}</div>
                                <div class="text-muted text-truncate extra-small">{{ Str::limit($notif->message, 40) }}</div>
                                <div class="text-muted mt-1 extra-small opacity-50">{{ $notif->created_at->diffForHumans() }}</div>
                            </div>
                        </a>
                    </li>
                    @empty
                    <li class="p-5 text-center text-muted">
                        <i class="bi bi-bell-slash mb-2 d-block opacity-25 fs-1"></i>
                        <span class="small">No new inquiries</span>
                    </li>
                    @endforelse
                </div>
                <li>
                    <a class="dropdown-item py-3 text-center text-primary fw-bold small border-top" href="{{ route('admin.enquiries.index') }}">
                        View All
                    </a>
                </li>
            </ul>
        </div>

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
                <li><a class="dropdown-item rounded-3 py-2" href="{{ route('admin.settings.index') }}"><i class="bi bi-gear me-2"></i> Settings</a></li>
                <li><a class="dropdown-item rounded-3 py-2" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
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
    .pulse-badge {
        box-shadow: 0 0 0 rgba(239, 68, 68, 0.4);
        animation: pulse-animation 1.8s infinite;
    }
    @keyframes pulse-animation {
        0% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        }
        70% {
            box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const theme = document.documentElement.getAttribute('data-theme') || 'light';
        const toggle = document.getElementById('themeToggle');
        if (toggle) {
            toggle.innerHTML = theme === 'dark'
                ? '<i class="bi bi-sun-fill"></i>'
                : '<i class="bi bi-moon-stars-fill"></i>';
        }
    });
</script>
