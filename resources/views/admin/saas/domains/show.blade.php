@extends('admin.layouts.app')

@section('title', 'Custom Domain Details')

@push('css')
<style>
    .saas-header {
        background: linear-gradient(135deg, #020617 0%, #0F172A 50%, #1E1B4B 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        padding: 32px 36px;
        color: white;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
    }
    .saas-header::before {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 250px; height: 250px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .saas-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        transition: all 0.25s ease;
        margin-bottom: 24px;
    }
    .info-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748B;
        margin-bottom: 6px;
        display: block;
    }
    .info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1E293B;
    }
    .status-badge {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 100px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .status-verified {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    .status-pending {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    .status-suspended {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid" style="font-family:'Outfit',sans-serif;">
    {{-- Header --}}
    <div class="saas-header animate-fade-in">
        <div style="position: relative; z-index: 2;" class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255, 255, 255, 0.5); margin-bottom: 8px;">
                    <a href="{{ route('admin.saas.domains.index') }}" class="text-white-50 text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Back to Domains</a>
                </div>
                <h2 class="fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">Domain Details</h2>
                <p class="text-white-50 mb-0">
                    Review and verify details for domain <strong>{{ $domain->domain }}</strong>.
                </p>
            </div>
            
            @php
                // Map Status
                $statusLabel = 'Pending DNS';
                $statusClass = 'status-pending';
                if ($domain->status === 'suspended') {
                    $statusLabel = 'Suspended';
                    $statusClass = 'status-suspended';
                } elseif ($domain->status === 'active_routing') {
                    $statusLabel = 'Active Routing';
                    $statusClass = 'status-verified';
                } elseif ($domain->status === 'dns_verified') {
                    $statusLabel = 'DNS Verified';
                    $statusClass = 'status-verified';
                } elseif ($domain->status === 'ssl_provisioning') {
                    $statusLabel = 'SSL Provisioning';
                    $statusClass = 'status-pending';
                }
                
                $profile = $domain->user->subscriberProfile ?? null;
            @endphp
            
            <div>
                <span class="status-badge {{ $statusClass }} fs-6 py-2 px-3">
                    @if($statusLabel === 'Active Routing' || $statusLabel === 'DNS Verified')
                        <i class="bi bi-check-circle-fill"></i>
                    @elseif($statusLabel === 'Suspended')
                        <i class="bi bi-slash-circle-fill"></i>
                    @else
                        <i class="bi bi-clock-history"></i>
                    @endif
                    {{ $statusLabel }}
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 mb-4" role="alert" style="border-radius:12px; background:#DCFCE7; color:#15803d; font-size:0.9rem;">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        {{-- Left Column: Information blocks --}}
        <div class="col-lg-8">
            {{-- Domain Information --}}
            <div class="saas-card p-4 animate-fade-in">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-4"><i class="bi bi-globe2 text-primary me-2"></i>Domain Information</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <span class="info-label">Domain Name</span>
                        <div class="info-value text-primary fs-5">{{ $domain->domain }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">Created At</span>
                        <div class="info-value">{{ $domain->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">TXT Key</span>
                        <div class="p-2 rounded bg-light border font-monospace text-dark" style="font-size:0.8rem;">{{ $domain->dns_txt_key }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">TXT Expected Value</span>
                        <div class="p-2 rounded bg-light border font-monospace text-dark" style="font-size:0.8rem;" title="{{ $domain->dns_txt_value }}">{{ Str::limit($domain->dns_txt_value, 30) }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">DNS Verification Status</span>
                        <div class="info-value">
                            @if($domain->dns_verified)
                                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Verified</span>
                            @else
                                <span class="text-warning fw-bold"><i class="bi bi-exclamation-circle-fill me-1"></i> Pending Verification Check</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">SSL Provisioning</span>
                        <div class="info-value">
                            @if($domain->ssl_status === 'active')
                                <span class="text-success fw-bold"><i class="bi bi-lock-fill me-1"></i> Active</span>
                            @else
                                <span class="text-secondary fw-bold"><i class="bi bi-hourglass-split me-1"></i> Pending Provisioning</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Store Information --}}
            <div class="saas-card p-4 animate-fade-in">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-4"><i class="bi bi-shop text-primary me-2"></i>Store Information</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <span class="info-label">Store Name</span>
                        <div class="info-value">{{ $profile ? $profile->company_name : 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">Store Slug</span>
                        <div class="info-value text-muted">/store/{{ $profile ? $profile->company_slug : 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">Phone</span>
                        <div class="info-value">{{ $profile ? $profile->phone ?? 'N/A' : 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">Website</span>
                        <div class="info-value">{{ $profile ? $profile->website ?? 'N/A' : 'N/A' }}</div>
                    </div>
                </div>
            </div>

            {{-- Subscriber Information --}}
            <div class="saas-card p-4 animate-fade-in">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-4"><i class="bi bi-people text-primary me-2"></i>Subscriber Information</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <span class="info-label">Subscriber Name</span>
                        <div class="info-value">{{ $domain->user->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">Email Address</span>
                        <div class="info-value">{{ $domain->user->email }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">Subscription Plan</span>
                        <div class="info-value">
                            @php
                                $sub = $domain->user->activeSubscription();
                                $planName = $sub && $sub->plan ? $sub->plan->name : 'None';
                            @endphp
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-2.5 py-1" style="font-size:0.75rem; border-radius:6px;">
                                {{ $planName }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">Active Subscription Range</span>
                        <div class="info-value text-muted" style="font-size:0.85rem;">
                            @if($sub)
                                From {{ $sub->starts_at ? $sub->starts_at->format('M d, Y') : 'N/A' }} to {{ $sub->ends_at ? $sub->ends_at->format('M d, Y') : 'Unlimited' }}
                            @else
                                No active subscription
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Admin Actions block --}}
        <div class="col-lg-4">
            <div class="saas-card p-4 animate-fade-in position-sticky" style="top: 100px;">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-4"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Admin Actions</h5>
                
                <div class="d-grid gap-3">
                    {{-- Verify Domain --}}
                    <form action="{{ route('admin.saas.domains.verify', $domain->id) }}" method="POST" class="w-100">
                        @csrf
                        <button type="submit" class="btn btn-light border py-2.5 w-100 fw-bold text-start px-3" title="Simulate CNAME & TXT DNS Records Verification">
                            <i class="bi bi-shield-check text-success me-2 fs-5"></i> Verify Domain
                        </button>
                    </form>

                    {{-- Activate Domain --}}
                    @if($domain->status !== 'active_routing')
                        <form action="{{ route('admin.saas.domains.approve', $domain->id) }}" method="POST" class="w-100">
                            @csrf
                            <button type="submit" class="btn btn-success py-2.5 w-100 fw-bold text-start px-3 text-white" title="Approve custom routing mapping">
                                <i class="bi bi-play-fill me-2 fs-5"></i> Activate Domain
                            </button>
                        </form>
                    @endif

                    {{-- Suspend Domain --}}
                    @if($domain->status === 'active_routing' || $domain->status === 'dns_verified')
                        <form action="{{ route('admin.saas.domains.suspend', $domain->id) }}" method="POST" class="w-100" onsubmit="return confirm('Are you sure you want to suspend this custom domain?');">
                            @csrf
                            <button type="submit" class="btn btn-warning py-2.5 w-100 fw-bold text-start px-3 text-dark" title="Deactivate custom domain routing">
                                <i class="bi bi-pause-fill me-2 fs-5"></i> Suspend Domain
                            </button>
                        </form>
                    @endif

                    {{-- Delete Domain --}}
                    <form action="{{ route('admin.saas.domains.destroy', $domain->id) }}" method="POST" class="w-100" onsubmit="return confirm('Are you sure you want to delete this custom domain mapping record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger py-2.5 w-100 fw-bold text-start px-3 text-white" title="Completely remove domain mapping record">
                            <i class="bi bi-trash3 me-2 fs-5"></i> Delete Domain
                        </button>
                    </form>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary-subtle">
                    <span class="text-muted small d-block mb-1">Store Front Preview:</span>
                    <a href="https://{{ $domain->domain }}" target="_blank" class="text-primary text-decoration-none fw-semibold" style="font-size:0.85rem;">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open Storefront Website
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
