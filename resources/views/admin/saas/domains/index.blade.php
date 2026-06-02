@extends('admin.layouts.app')

@section('title', 'Custom Domains Management')

@push('css')
<style>
    /* Premium glassmorphic & high-fidelity styles */
    .saas-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
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
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        transition: all 0.25s ease;
    }
    .saas-card:hover {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.05);
    }
    .table-hover tbody tr:hover {
        background-color: rgba(79, 70, 229, 0.02);
    }
    .badge-status {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 100px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 1px solid transparent;
    }
    .badge-status-verified {
        background: #ecfdf5;
        color: #10b981;
        border-color: #d1fae5;
    }
    .badge-status-pending {
        background: #fffbeb;
        color: #f59e0b;
        border-color: #fef3c7;
    }
    .badge-status-suspended {
        background: #fef2f2;
        color: #ef4444;
        border-color: #fee2e2;
    }
    .record-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 0.8rem;
    }
    .btn-white {
        background: #fff;
        border: 1px solid #e2e8f0;
    }
    .btn-white:hover {
        background: #f8fafc;
    }
</style>
@endpush

@section('content')
<div class="container-fluid" style="font-family:'Outfit',sans-serif;">
    {{-- Header --}}
    <div class="saas-header animate-fade-in">
        <div style="position: relative; z-index: 2;">
            <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255, 255, 255, 0.5); margin-bottom: 8px;">
                CATASKY SAAS CORE
            </div>
            <h2 class="fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif; font-size: 2.25rem;">Domain Management</h2>
            <p class="text-white-50 mb-0" style="max-width: 650px; font-size: 0.95rem; line-height: 1.6;">
                Review White-Label custom domain mapping requests for Enterprise subscribers, inspect DNS values, and issue automated SSL certs.
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 mb-4" role="alert" style="border-radius:12px; background:#DCFCE7; color:#15803d; font-size:0.9rem;">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Filter Card --}}
    <div class="saas-card mb-4 animate-fade-in">
        <div class="card-body p-4">
            <form action="{{ route('admin.saas.domains.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-6 col-lg-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 12px 0 0 12px;"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by domain or company name..." value="{{ request('search') }}" style="border-radius:0 12px 12px 0; font-size: 0.95rem; padding-top: 10px; padding-bottom: 10px;">
                    </div>
                </div>
                
                <div class="col-md-3 col-lg-2">
                    <select name="status" class="form-select border" style="border-radius:12px; font-size: 0.95rem; padding-top: 10px; padding-bottom: 10px;">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                <div class="col-md-3 col-lg-2 d-grid">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn text-white w-100 fw-bold d-flex align-items-center justify-content-center gap-1.5" style="background:#4F46E5; border:none; border-radius:12px; padding-top: 10px; padding-bottom: 10px;">
                            <i class="bi bi-funnel-fill"></i> Filter
                        </button>
                        @if(request()->filled('search') || request()->filled('status'))
                            <a href="{{ route('admin.saas.domains.index') }}" class="btn btn-light d-flex align-items-center justify-content-center" style="border-radius:12px; border: 1px solid #cbd5e1;"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table Content --}}
    <div class="saas-card animate-fade-in">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">
                            <th class="ps-4 py-3 text-uppercase small fw-bold text-secondary border-0" style="width:15%;">Store Name</th>
                            <th class="py-3 text-uppercase small fw-bold text-secondary border-0" style="width:15%;">Subscriber Name</th>
                            <th class="py-3 text-uppercase small fw-bold text-secondary border-0" style="width:20%;">Domain Name</th>
                            <th class="py-3 text-uppercase small fw-bold text-secondary border-0 text-center" style="width:10%;">Plan</th>
                            <th class="py-3 text-uppercase small fw-bold text-secondary border-0 text-center" style="width:10%;">DNS Status</th>
                            <th class="py-3 text-uppercase small fw-bold text-secondary border-0 text-center" style="width:10%;">SSL Status</th>
                            <th class="py-3 text-uppercase small fw-bold text-secondary border-0 text-center" style="width:10%;">Routing Status</th>
                            <th class="py-3 text-uppercase small fw-bold text-secondary border-0 text-center" style="width:10%;">Created Date</th>
                            <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-secondary border-0" style="width:10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($domains as $domain)
                        @php
                            $profile = $domain->user->subscriberProfile ?? null;
                            $sub = $domain->user->activeSubscription();
                            $planName = $sub && $sub->plan ? $sub->plan->name : 'None';
                            
                            // Map Status
                            $statusLabel = 'PENDING DNS';
                            $statusClass = 'badge-status-pending';
                            if ($domain->status === 'suspended') {
                                $statusLabel = 'SUSPENDED';
                                $statusClass = 'badge-status-suspended';
                            } elseif ($domain->status === 'active_routing') {
                                $statusLabel = 'ACTIVE ROUTING';
                                $statusClass = 'badge-status-verified';
                            } elseif ($domain->status === 'dns_verified') {
                                $statusLabel = 'DNS VERIFIED';
                                $statusClass = 'badge-status-verified';
                            } elseif ($domain->status === 'ssl_provisioning') {
                                $statusLabel = 'SSL PROVISIONING';
                                $statusClass = 'badge-status-pending';
                            }
                        @endphp
                        <tr>
                            {{-- Store Name --}}
                            <td class="ps-4 py-3.5">
                                <strong class="text-dark d-block" style="font-size: 0.95rem;">{{ $profile ? $profile->company_name : 'No Store Profile' }}</strong>
                            </td>

                            {{-- Subscriber Name --}}
                            <td class="py-3.5">
                                <span class="text-secondary fw-semibold">{{ $domain->user->name }}</span>
                                <span class="text-muted d-block small" style="font-size:0.75rem;">{{ $domain->user->email }}</span>
                            </td>

                            {{-- Domain Name --}}
                            <td class="py-3.5">
                                <a href="https://{{ $domain->domain }}" target="_blank" class="text-primary fw-bold text-decoration-none small d-inline-flex align-items-center gap-1">
                                    {{ $domain->domain }} <i class="bi bi-box-arrow-up-right" style="font-size:0.75rem;"></i>
                                </a>
                            </td>

                            {{-- Plan --}}
                            <td class="py-3.5 text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-2.5 py-1 fw-bold" style="font-size: 0.72rem; border-radius: 6px;">
                                    {{ $planName }}
                                </span>
                            </td>
                            
                            {{-- DNS Status --}}
                            <td class="py-3.5 text-center">
                                @if($domain->dns_verified)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2 py-1 small rounded-pill fw-bold" style="font-size: 0.7rem;">
                                        VERIFIED
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 px-2 py-1 small rounded-pill fw-bold" style="font-size: 0.7rem;">
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            
                            {{-- SSL Status --}}
                            <td class="py-3.5 text-center">
                                @if($domain->ssl_status === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2 py-1 small rounded-pill fw-bold" style="font-size: 0.7rem;">
                                        ACTIVE
                                    </span>
                                @elseif($domain->ssl_status === 'provisioning')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 px-2 py-1 small rounded-pill fw-bold" style="font-size: 0.7rem;">
                                        PROVISIONING
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20 px-2 py-1 small rounded-pill fw-bold" style="font-size: 0.7rem;">
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            
                            {{-- Routing Status --}}
                            <td class="py-3.5 text-center">
                                <span class="badge-status {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            {{-- Created Date --}}
                            <td class="py-3.5 text-center text-secondary small fw-semibold">
                                {{ $domain->created_at->format('M d, Y') }}
                            </td>
                            
                            {{-- Actions --}}
                            <td class="text-end pe-4 py-3.5" style="white-space: nowrap;">
                                <div class="d-inline-flex justify-content-end align-items-center">
                                    <div class="btn-group rounded-3 overflow-hidden">
                                        {{-- View Details --}}
                                        <a href="{{ route('admin.saas.domains.show', $domain->id) }}" class="btn btn-white btn-sm px-3" title="View Domain Details">
                                            <i class="fa-solid fa-eye text-primary"></i>
                                        </a>
 
                                        {{-- DNS Check / Verify --}}
                                        @if(!$domain->dns_verified)
                                            <form action="{{ route('admin.saas.domains.verify', $domain->id) }}" method="POST" class="d-inline m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-white btn-sm px-3" title="Verify DNS Records">
                                                    <i class="fa-solid fa-arrows-rotate text-success"></i>
                                                </button>
                                            </form>
                                        @endif
 
                                        {{-- Activate --}}
                                        @if($domain->status !== 'active_routing')
                                            <form action="{{ route('admin.saas.domains.approve', $domain->id) }}" method="POST" class="d-inline m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-white btn-sm px-3" title="Activate Domain Routing">
                                                    <i class="fa-solid fa-circle-play text-success"></i>
                                                </button>
                                            </form>
                                        @endif
 
                                        {{-- Suspend --}}
                                        @if($domain->status === 'active_routing' || $domain->status === 'dns_verified')
                                            <form action="{{ route('admin.saas.domains.suspend', $domain->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to suspend this custom domain?');">
                                                @csrf
                                                <button type="submit" class="btn btn-white btn-sm px-3" title="Suspend Domain Routing">
                                                    <i class="fa-solid fa-circle-pause text-warning"></i>
                                                </button>
                                            </form>
                                        @endif
 
                                        {{-- Delete --}}
                                        <form action="{{ route('admin.saas.domains.destroy', $domain->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this custom domain mapping record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-white btn-sm px-3" title="Remove Mapping">
                                                <i class="fa-solid fa-trash-can text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-globe text-muted" style="font-size:2.5rem; display:block; margin-bottom:12px; opacity:0.4;"></i>
                                No custom domain mapping records matched your filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
 
            @if($domains->hasPages())
                <div class="d-flex justify-content-between align-items-center p-4 border-top">
                    <div class="text-muted small">
                        Showing {{ $domains->firstItem() }} to {{ $domains->lastItem() }} of {{ $domains->total() }} custom domains
                    </div>
                    <div>
                        {{ $domains->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
