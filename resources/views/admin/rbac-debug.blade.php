@extends('admin.layouts.app')

@section('title', 'RBAC Security Audit Console')

@section('content')
<style>
    .rbac-gradient-bg {
        background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%);
        border-radius: 24px;
        position: relative;
        overflow: hidden;
    }
    .rbac-gradient-bg::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(168,85,247,0.15) 0%, rgba(0,0,0,0) 70%);
        top: -100px;
        right: -100px;
        pointer-events: none;
    }
    .rbac-glass-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(16px);
        border-radius: 20px;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    }
    .rbac-glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        border-color: rgba(255, 255, 255, 0.15);
    }
    .badge-capsule {
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.72rem;
        letter-spacing: 0.5px;
        padding: 5px 14px;
    }
    .security-status-glow-green {
        box-shadow: 0 0 15px rgba(34,197,94,0.3);
    }
    .security-status-glow-red {
        box-shadow: 0 0 15px rgba(239,68,68,0.3);
    }
    .module-card {
        transition: all 0.3s ease;
    }
    .module-card:hover {
        transform: scale(1.02);
    }
</style>

<div class="container-fluid py-4">
    {{-- Hero Section --}}
    <div class="rbac-gradient-bg p-5 mb-4 text-white shadow-lg">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-primary bg-opacity-25 text-info badge-capsule mb-3 border border-info border-opacity-25">
                    <i class="fa-solid fa-shield-halved me-1"></i> SECURITY AUDIT CONSOLE
                </span>
                <h2 class="fw-bold display-6 mb-2" style="font-family: 'Outfit', sans-serif;">RBAC & Access Control Debugger</h2>
                <p class="text-white-50 fs-6 mb-0">Inspect user authorization parameters, verify active role mappings, check module access states, and clear Spatie cache in real-time.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="{{ route('route-clear') }}" target="_blank" class="btn btn-outline-light rounded-pill px-4 py-2 border-opacity-25 hover-shadow">
                    <i class="fa-solid fa-arrows-rotate me-2"></i>Clear System Cache
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card rbac-glass-card p-4 border-0 h-100 shadow-sm" style="background: linear-gradient(135deg, rgba(99,102,241,0.05) 0%, rgba(0,0,0,0) 100%); border: 1px solid rgba(99,102,241,0.1) !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Total System Roles</h6>
                        <h2 class="fw-extrabold text-dark mb-0 display-6">{{ $totalRoles }}</h2>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                        <i class="fa-solid fa-users-gear fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card rbac-glass-card p-4 border-0 h-100 shadow-sm" style="background: linear-gradient(135deg, rgba(168,85,247,0.05) 0%, rgba(0,0,0,0) 100%); border: 1px solid rgba(168,85,247,0.1) !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Total Permissions</h6>
                        <h2 class="fw-extrabold text-dark mb-0 display-6">{{ $totalPermissions }}</h2>
                    </div>
                    <div class="rounded-circle bg-purple bg-opacity-10 p-3 text-purple" style="color: #a855f7;">
                        <i class="fa-solid fa-key fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card rbac-glass-card p-4 border-0 h-100 shadow-sm" style="background: linear-gradient(135deg, rgba(236,72,153,0.05) 0%, rgba(0,0,0,0) 100%); border: 1px solid rgba(236,72,153,0.1) !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Audited User Base</h6>
                        <h2 class="fw-extrabold text-dark mb-0 display-6">{{ $totalUsers }}</h2>
                    </div>
                    <div class="rounded-circle bg-pink bg-opacity-10 p-3 text-pink" style="color: #ec4899;">
                        <i class="fa-solid fa-circle-user fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- User Profile Overview --}}
    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-id-card-clip text-primary me-2"></i>Current User Context</h5>
        <div class="row align-items-center">
            <div class="col-md-4 mb-3 mb-md-0 border-end">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 shadow" style="width:60px; height:60px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">{{ $user->name }}</h6>
                        <p class="text-muted smaller mb-0">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="mb-2">
                    <span class="text-muted small fw-bold me-2">Assigned Roles:</span>
                    @forelse($roles as $role)
                        <span class="badge bg-primary bg-opacity-10 text-primary badge-capsule border border-primary border-opacity-15 me-1">
                            {{ $role }}
                        </span>
                    @empty
                        <span class="text-muted small">None</span>
                    @endforelse
                </div>
                <div>
                    <span class="text-muted small fw-bold me-2">Highest Privilege Bypass:</span>
                    @if($user->hasRole('Super Admin'))
                        <span class="badge bg-success badge-capsule security-status-glow-green"><i class="fa-solid fa-circle-check me-1"></i> ACTIVE (SUPER ADMIN BYPASS ON)</span>
                    @else
                        <span class="badge bg-secondary badge-capsule"><i class="fa-solid fa-lock me-1"></i> INACTIVE (RESTRICTED TO ROLES & PERMISSIONS)</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Audit Tabs --}}
    <ul class="nav nav-pills mb-4 gap-2" id="rbacTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-bold shadow-sm" id="modules-tab" data-bs-toggle="pill" data-bs-target="#modules" type="button" role="tab"><i class="fa-solid fa-table-columns me-2"></i>Module Security Mappings</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold shadow-sm" id="permissions-tab" data-bs-toggle="pill" data-bs-target="#permissions" type="button" role="tab"><i class="fa-solid fa-key me-2"></i>Loaded Permissions Registry ({{ $allPermissions->count() }})</button>
        </li>
    </ul>

    <div class="tab-content" id="rbacTabContent">
        {{-- Modules Tab --}}
        <div class="tab-pane fade show active" id="modules" role="tabpanel">
            <div class="row">
                {{-- Accessible Modules --}}
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 rounded-4 shadow-sm h-100 p-4" style="border-top: 4px solid #22c55e !important;">
                        <h5 class="fw-bold text-success mb-3 d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-circle-check me-2"></i>Accessible Modules</span>
                            <span class="badge bg-success bg-opacity-10 text-success badge-capsule">{{ count($accessibleModules) }} Accessible</span>
                        </h5>
                        <p class="text-muted smaller mb-4">The logged-in user's role mappings grant direct or parent module access to these sections:</p>
                        
                        <div class="d-flex flex-column gap-3">
                            @forelse($accessibleModules as $mod)
                                <div class="card border-0 bg-light bg-opacity-50 p-3 rounded-4 module-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold text-dark mb-0">{{ $mod['name'] }}</h6>
                                        <span class="badge bg-light text-muted small border px-2 py-1 rounded">{{ $mod['category'] }}</span>
                                    </div>
                                    <p class="text-muted small mb-2">{{ $mod['description'] }}</p>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="smaller text-muted"><i class="fa-solid fa-key me-1 text-primary"></i> Required Permission: <code>{{ $mod['permission'] }}</code></span>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small fw-bold border border-success border-opacity-15"><i class="fa-solid fa-shield me-1"></i>Allowed</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted">No modules accessible under this context.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Restricted Modules --}}
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 rounded-4 shadow-sm h-100 p-4" style="border-top: 4px solid #ef4444 !important;">
                        <h5 class="fw-bold text-danger mb-3 d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-circle-xmark me-2"></i>Restricted Modules</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger badge-capsule">{{ count($restrictedModules) }} Restricted</span>
                        </h5>
                        <p class="text-muted smaller mb-4">Access is locked for these modules due to missing permissions in the user's role mapping:</p>

                        <div class="d-flex flex-column gap-3">
                            @forelse($restrictedModules as $mod)
                                <div class="card border-0 bg-light bg-opacity-50 p-3 rounded-4 module-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold text-dark mb-0" style="opacity: 0.7;">{{ $mod['name'] }}</h6>
                                        <span class="badge bg-light text-muted small border px-2 py-1 rounded">{{ $mod['category'] }}</span>
                                    </div>
                                    <p class="text-muted small mb-2" style="opacity: 0.7;">{{ $mod['description'] }}</p>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="smaller text-muted"><i class="fa-solid fa-key me-1 text-danger"></i> Required Permission: <code>{{ $mod['permission'] }}</code></span>
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small fw-bold border border-danger border-opacity-15"><i class="fa-solid fa-lock me-1"></i>Locked</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-shield-heart text-success display-6 mb-3"></i>
                                    <p class="mb-0 text-success fw-bold">Maximum Access! No modules are restricted for this user.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Permissions Tab --}}
        <div class="tab-pane fade" id="permissions" role="tabpanel">
            <div class="card border-0 rounded-4 shadow-sm p-4">
                <h5 class="fw-bold text-dark mb-4"><i class="fa-solid fa-unlock-keyhole text-primary me-2"></i>Loaded Database Permissions Audit</h5>
                
                <div class="row g-4">
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-shield-check text-primary me-2"></i>Directly Assigned Permissions ({{ $directPermissions->count() }})</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($directPermissions as $perm)
                                <code class="text-primary fw-medium px-2 py-1 bg-primary bg-opacity-10 rounded border border-primary border-opacity-10">{{ $perm }}</code>
                            @empty
                                <span class="text-muted small py-2">No direct permissions assigned. Role-based permissions only.</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-shield-halved text-purple me-2" style="color: #a855f7;"></i>Inherited Via Role Mappings ({{ $rolePermissions->count() }})</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($rolePermissions as $perm)
                                <code class="text-purple fw-medium px-2 py-1 bg-purple bg-opacity-10 rounded border border-purple border-opacity-10" style="color: #a855f7; background-color: rgba(168,85,247,0.1); border-color: rgba(168,85,247,0.1) !important;">{{ $perm }}</code>
                            @empty
                                <span class="text-muted small py-2">No inherited permissions found.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
