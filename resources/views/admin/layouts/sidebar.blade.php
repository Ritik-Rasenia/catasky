<div id="sidebar" style="height: 100vh; overflow-y: auto; overflow-x: hidden;">
    @php $setting = \App\Models\Setting::first(); @endphp

    {{-- Sidebar Header / Logo --}}
    <div class="sidebar-header" style="justify-content: center; flex-direction: column; gap: 4px; padding: 20px 16px;">
        @if($setting && $setting->logo)
            <img src="{{ asset('uploads/settings/' . $setting->logo) }}" alt="Logo" style="max-height: 32px; max-width: 100%; object-fit: contain;">
        @else
            <div class="sidebar-logo" style="font-family:'Outfit',sans-serif; font-size:1.3rem; font-weight:800; letter-spacing:-0.5px;">Catasky.</div>
        @endif
        <div style="font-size:0.58rem; color:rgba(255,255,255,0.3); letter-spacing:1.4px; text-transform:uppercase; font-weight:700; margin-top:2px;">Admin Console</div>
    </div>

    <div class="py-2">

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        {{-- ── Catalogue Section ── --}}
        <div class="px-4 pt-4 pb-2">
            <small style="font-size:0.62rem; letter-spacing:1.4px; text-transform:uppercase; font-weight:700; color:rgba(255,255,255,0.3);">Catalogue</small>
        </div>

        @can('view-brands')
        <a href="{{ route('admin.brands.index') }}" class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
            <i class="bi bi-patch-check-fill"></i>
            <span>Brands</span>
        </a>
        @endcan

        @can('view-categories')
        <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="bi bi-layers-fill"></i>
            <span>Categories</span>
        </a>
        @endcan

        @can('view-categories')
        <a href="{{ route('admin.subcategories.index') }}" class="nav-link {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}">
            <i class="bi bi-list-nested"></i>
            <span>Subcategories</span>
        </a>
        @endcan

        @can('view-products')
        <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam-fill"></i>
            <span>Products</span>
        </a>
        @endcan

        {{-- ── Sharing & Analytics Section ── --}}
        <div class="px-4 pt-4 pb-2">
            <small style="font-size:0.62rem; letter-spacing:1.4px; text-transform:uppercase; font-weight:700; color:rgba(255,255,255,0.3);">Sharing & Analytics</small>
        </div>

        @can('view-enquiries')
        <a href="{{ route('admin.enquiries.index') }}" class="nav-link {{ request()->routeIs('admin.enquiries.*') ? 'active' : '' }}">
            <i class="bi bi-chat-left-dots-fill"></i>
            <span>Enquiries</span>
            @php $unreadCount = \App\Models\Enquiry::where('is_read', false)->count(); @endphp
            @if($unreadCount > 0)
                <span class="badge rounded-pill ms-auto" style="background:#EF4444; color:white; font-size:0.62rem; padding:2px 7px; font-weight:800;">{{ $unreadCount }}</span>
            @endif
        </a>
        @endcan

        <a href="{{ route('admin.tracking.analytics') }}" class="nav-link {{ request()->routeIs('admin.tracking.analytics') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i>
            <span>Analytics</span>
        </a>

        <a href="{{ route('admin.newsletters.index') }}" class="nav-link {{ request()->routeIs('admin.newsletters.*') ? 'active' : '' }}">
            <i class="bi bi-envelope-paper-fill"></i>
            <span>Newsletter</span>
        </a>

        {{-- ── Access Control Section ── --}}
        <div class="px-4 pt-4 pb-2">
            <small style="font-size:0.62rem; letter-spacing:1.4px; text-transform:uppercase; font-weight:700; color:rgba(255,255,255,0.3);">Access Control</small>
        </div>

        @can('view-users')
        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i>
            <span>Users</span>
        </a>
        @endcan

        @can('view-roles')
        <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Roles</span>
        </a>
        @endcan

        @can('view-permissions')
        <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
            <i class="bi bi-key-fill"></i>
            <span>Permissions</span>
        </a>
        @endcan

        {{-- ── System Section ── --}}
        <div class="px-4 pt-4 pb-2">
            <small style="font-size:0.62rem; letter-spacing:1.4px; text-transform:uppercase; font-weight:700; color:rgba(255,255,255,0.3);">System</small>
        </div>

        <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear-fill"></i>
            <span>General Settings</span>
        </a>

        <a href="{{ route('admin.profile.edit') }}" class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-circle"></i>
            <span>My Profile</span>
        </a>

        {{-- Catalogue preview link --}}
        <a href="{{ route('catalogue') }}" target="_blank" class="nav-link">
            <i class="bi bi-box-arrow-up-right" style="color:#4ADE80;"></i>
            <span>View Catalogue</span>
        </a>

        {{-- Sign Out --}}
        <div class="px-4 mt-4 mb-4">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" style="width:100%; padding:10px 16px; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.12); border-radius:12px; color:#FC8181; font-size:0.82rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.15)'" onmouseout="this.style.background='rgba(239,68,68,0.08)'">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>

    </div>
</div>
