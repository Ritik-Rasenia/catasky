@extends('admin.layouts.app')

@section('title', 'Subscriber Management —')

@push('css')
<style>
    /* Premium glassmorphic styles */
    .saas-header {
        background: linear-gradient(135deg, #1E1B4B 0%, #311042 50%, #1E1B4B 100%);
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
    }
    .saas-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.06);
    }
    .table-hover tbody tr:hover {
        background-color: rgba(99, 102, 241, 0.03);
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
    .status-active {
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
    .plan-badge {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .plan-starter {
        background: rgba(99, 102, 241, 0.1);
        color: #6366F1;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }
    .plan-business {
        background: rgba(236, 72, 153, 0.1);
        color: #EC4899;
        border: 1px solid rgba(236, 72, 153, 0.2);
    }
    .plan-enterprise {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    .plan-none {
        background: rgba(148, 163, 184, 0.1);
        color: #94A3B8;
        border: 1px solid rgba(148, 163, 184, 0.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="saas-header">
        <div style="position: relative; z-index: 2;">
            <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255, 255, 255, 0.5); margin-bottom: 8px;">
                Catasky SaaS Core
            </div>
            <h2 class="fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">Subscriber Management</h2>
            <p class="text-white-50 mb-0" style="max-width: 600px;">
                Monitor subscriber registrations, assign billing tiers, view store metrics, and manage system access permissions.
            </p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="saas-card mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.saas.subscribers.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-8 col-lg-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by name, email, or company name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3 d-grid">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill flex-grow-1 px-4"><i class="bi bi-funnel-fill me-2"></i>Filter</button>
                        @if(request()->filled('search'))
                            <a href="{{ route('admin.saas.subscribers.index') }}" class="btn btn-light rounded-pill"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Grid Content --}}
    <div class="saas-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Subscriber Details</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Company Info</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Active Plan</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Status</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Registered</th>
                            <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscribers as $sub)
                        @php
                            $profile = $sub->subscriberProfile;
                            $subscription = $sub->subscription;
                            $plan = $subscription ? $subscription->plan : null;
                            $planSlug = $plan ? $plan->slug : 'none';
                            $status = $profile ? $profile->status : 'pending';
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-3" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 700;">
                                        {{ strtoupper(substr($sub->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $sub->name }}</div>
                                        <div class="text-muted small">{{ $sub->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($profile)
                                    <div class="fw-bold text-dark">{{ $profile->company_name }}</div>
                                    @if($profile->company_slug)
                                        <a href="{{ route('store.catalog', $profile->company_slug) }}" target="_blank" class="text-primary small text-decoration-none">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>/store/{{ $profile->company_slug }}
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted small">No profile built</span>
                                @endif
                            </td>
                            <td>
                                <span class="plan-badge plan-{{ $planSlug }}">
                                    {{ $plan ? $plan->name : 'No Active Plan' }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $status }}">
                                    @if($status === 'active')
                                        <i class="bi bi-patch-check-fill"></i> Approved
                                    @elseif($status === 'suspended')
                                        <i class="bi bi-slash-circle-fill"></i> Suspended
                                    @else
                                        <i class="bi bi-clock-history"></i> Pending Review
                                    @endif
                                </span>
                                @if($status === 'suspended' && $profile->suspension_reason)
                                    <div class="text-muted small mt-1" style="font-size:0.65rem;" title="{{ $profile->suspension_reason }}">
                                        Reason: {{ Str::limit($profile->suspension_reason, 30) }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-muted small">
                                {{ $sub->created_at->format('M d, Y') }}
                                <div class="text-muted" style="font-size:0.7rem;">{{ $sub->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.subscribers.show', $sub->id) }}" class="btn btn-sm btn-light border" title="View Full Profile">
                                        <i class="bi bi-eye text-primary"></i>
                                    </a>
                                    
                                    @if($status === 'suspended')
                                        <form action="{{ route('admin.saas.subscribers.unsuspend', $sub->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border btn-unsuspend" title="Unsuspend Account">
                                                <i class="bi bi-unlock-fill text-success"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-light border btn-suspend-modal" data-bs-toggle="modal" data-bs-target="#suspendModal{{ $sub->id }}" title="Suspend Account">
                                            <i class="bi bi-shield-slash text-danger"></i>
                                        </button>
                                    @endif
                                </div>

                                {{-- Suspend Modal --}}
                                <div class="modal fade" id="suspendModal{{ $sub->id }}" tabindex="-1" aria-labelledby="suspendModalLabel{{ $sub->id }}" aria-hidden="true" style="backdrop-filter: blur(8px);">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4 shadow-lg">
                                            <div class="modal-header border-0 bg-danger text-white rounded-top-4 p-4">
                                                <h5 class="modal-title fw-bold" id="suspendModalLabel{{ $sub->id }}">
                                                    <i class="bi bi-shield-slash-fill me-2"></i>Suspend Subscriber Account
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.saas.subscribers.suspend', $sub->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4 text-start">
                                                    <p class="text-muted mb-3">
                                                        You are about to suspend <strong>{{ $sub->name }}</strong> ({{ $profile->company_name ?? 'Subscriber' }}). 
                                                        This will immediately lock them out of the Subscriber Panel and disable all public pages / store share links.
                                                    </p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-uppercase text-muted">Reason for Suspension</label>
                                                        <textarea class="form-control" name="reason" rows="3" required placeholder="Specify violation details, unpaid invoices, or terms of service breech..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 p-4 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-4">Suspend Access</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people text-muted" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                                No B2B subscribers found matching search criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscribers->hasPages())
                <div class="d-flex justify-content-between align-items-center p-4 border-top">
                    <div class="text-muted small">
                        Showing {{ $subscribers->firstItem() }} to {{ $subscribers->lastItem() }} of {{ $subscribers->total() }} subscribers
                    </div>
                    <div>
                        {{ $subscribers->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
