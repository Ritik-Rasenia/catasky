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
        padding: 6px 14px;
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
    .status-blue {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    .checklist-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-radius: 10px;
        background: #f8fafc;
        margin-bottom: 8px;
        border: 1px solid #f1f5f9;
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
                $statusLabel = strtoupper($domain->status ?: 'Pending DNS Setup');
                $statusClass = 'status-pending';
                if ($domain->status === 'Active' || $domain->status === 'active_routing') {
                    $statusLabel = 'Active';
                    $statusClass = 'status-verified';
                } elseif ($domain->status === 'Rejected' || $domain->status === 'suspended') {
                    $statusLabel = 'Rejected';
                    $statusClass = 'status-suspended';
                } elseif ($domain->status === 'DNS Verified' || $domain->status === 'Verified') {
                    $statusLabel = 'DNS Verified';
                    $statusClass = 'status-blue';
                }
                
                $profile = $domain->user->subscriberProfile ?? null;
            @endphp
            
            <div>
                <span class="status-badge {{ $statusClass }} fs-6 py-2 px-3">
                    @if($statusLabel === 'Active' || $statusLabel === 'DNS Verified' || $statusLabel === 'Verified')
                        <i class="bi bi-check-circle-fill"></i>
                    @elseif($statusLabel === 'Rejected')
                        <i class="bi bi-x-circle-fill"></i>
                    @else
                        <i class="bi bi-hourglass-split"></i>
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
            {{-- Activation Checklist --}}
            <div class="saas-card p-4 animate-fade-in">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-4"><i class="bi bi-card-checklist text-primary me-2"></i>Activation Checklist</h5>
                
                <div class="checklist-item">
                    <span class="fw-semibold text-secondary">TXT Record Ownership Verified</span>
                    @if($domain->dns_txt_verified)
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-check-lg me-1"></i> YES</span>
                    @else
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-x-lg me-1"></i> NO</span>
                    @endif
                </div>

                <div class="checklist-item">
                    <span class="fw-semibold text-secondary">A Record Server IP Pointed (159.89.172.11)</span>
                    @if($domain->dns_a_verified)
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-check-lg me-1"></i> YES</span>
                    @else
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-x-lg me-1"></i> NO</span>
                    @endif
                </div>

                <div class="checklist-item">
                    <span class="fw-semibold text-secondary">CNAME Record Subdomain Connected (domain.catasky.com)</span>
                    @if($domain->dns_cname_verified)
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-check-lg me-1"></i> YES</span>
                    @else
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-x-lg me-1"></i> NO</span>
                    @endif
                </div>

                <div class="checklist-item">
                    <span class="fw-semibold text-secondary">SSL Certificate Configuration Active</span>
                    @if($domain->ssl_status === 'SSL Active' || $domain->ssl_status === 'active')
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-check-lg me-1"></i> YES</span>
                    @else
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-x-lg me-1"></i> NO ({{ $domain->ssl_status ?: 'Pending' }})</span>
                    @endif
                </div>

                <div class="checklist-item">
                    <span class="fw-semibold text-secondary">Super Admin Authorized</span>
                    @if($domain->admin_approved)
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-check-lg me-1"></i> YES</span>
                    @else
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-x-lg me-1"></i> NO</span>
                    @endif
                </div>

                <div class="mt-4 p-3 rounded-3 text-secondary border border-secondary border-opacity-10 d-flex gap-2" style="background-color: #f8fafc; font-size:0.82rem; line-height:1.6;">
                    <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                    <span>Domain activation is fully automated. The domain status changes to **Active** and routes storefront traffic only when **all 5 criteria** above are successfully checked. Super Admins cannot bypass DNS or SSL requirements.</span>
                </div>
            </div>

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
                        <span class="info-label">TXT Host</span>
                        <div class="p-2 rounded bg-light border font-monospace text-dark" style="font-size:0.8rem;">@</div>
                    </div>
                    <div class="col-md-6">
                        <span class="info-label">TXT Token Value</span>
                        <div class="p-2 rounded bg-light border font-monospace text-dark" style="font-size:0.8rem;" title="{{ $domain->dns_txt_value }}">{{ $domain->dns_txt_value }}</div>
                    </div>
                    @if($domain->status === 'Rejected')
                        <div class="col-12">
                            <span class="info-label">Rejection Reason</span>
                            <div class="p-3 rounded bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 fw-semibold" style="font-size:0.9rem;">
                                {{ $domain->rejection_reason ?: 'No reason provided.' }}
                            </div>
                        </div>
                    @endif
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

            {{-- Verification Audit Logs --}}
            <div class="saas-card p-4 animate-fade-in">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-4"><i class="bi bi-journal-text text-primary me-2"></i>Verification Audit Logs</h5>
                <div class="table-responsive rounded-3 border" style="border-color: #f1f5f9 !important;">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead>
                            <tr class="table-light text-secondary small fw-bold" style="background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">
                                <th class="ps-3 py-2.5">TIMESTAMP</th>
                                <th class="py-2.5">ACTION</th>
                                <th class="py-2.5">STATUS</th>
                                <th class="pe-3 py-2.5">MESSAGE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($domain->logs as $log)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td class="ps-3 py-3 font-monospace text-secondary" style="white-space: nowrap;">
                                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="py-3 fw-bold text-dark text-uppercase" style="font-size:0.75rem;">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </td>
                                    <td class="py-3">
                                        @if($log->status === 'success')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2.5 py-1 fw-bold" style="font-size: 0.7rem; border-radius: 6px;">SUCCESS</span>
                                        @elseif($log->status === 'failed')
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-2.5 py-1 fw-bold" style="font-size: 0.7rem; border-radius: 6px;">FAILED</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20 px-2.5 py-1 fw-bold" style="font-size: 0.7rem; border-radius: 6px;">INFO</span>
                                        @endif
                                    </td>
                                    <td class="pe-3 py-3 text-secondary text-wrap" style="max-width: 300px;">
                                        {{ $log->message }}
                                        @if($log->details)
                                            <div class="mt-1 font-monospace small bg-light p-2 border rounded text-dark" style="font-size: 0.72rem; overflow-x: auto; max-width: 100%;">
                                                {{ json_encode($log->details) }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No verification logs recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column: Admin Actions block --}}
        <div class="col-lg-4">
            <div class="saas-card p-4 animate-fade-in position-sticky" style="top: 100px;">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-4"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Admin Actions</h5>
                
                <div class="d-grid gap-3">
                    {{-- Verify DNS records --}}
                    <form action="{{ route('admin.saas.domains.verify', $domain->id) }}" method="POST" class="w-100">
                        @csrf
                        <button type="submit" class="btn btn-light border py-2.5 w-100 fw-bold text-start px-3" title="Verify DNS Records Ownership and Setup">
                            <i class="bi bi-arrow-repeat text-success me-2 fs-5"></i> Check & Verify DNS
                        </button>
                    </form>

                    {{-- Approve Domain --}}
                    @if(!$domain->admin_approved)
                        <form action="{{ route('admin.saas.domains.approve', $domain->id) }}" method="POST" class="w-100">
                            @csrf
                            <button type="submit" class="btn btn-success py-2.5 w-100 fw-bold text-start px-3 text-white" title="Approve mapping request">
                                <i class="bi bi-patch-check-fill me-2 fs-5"></i> Approve Domain
                            </button>
                        </form>
                    @endif

                    {{-- Reject Domain Button --}}
                    @if($domain->status !== 'Rejected')
                        <button type="button" class="btn btn-danger py-2.5 w-100 fw-bold text-start px-3 text-white" data-bs-toggle="modal" data-bs-target="#rejectDomainModal" title="Reject mapping request with reason">
                            <i class="bi bi-x-octagon me-2 fs-5"></i> Reject Domain
                        </button>
                    @endif

                    {{-- Suspend Domain --}}
                    @if($domain->status === 'Active' || $domain->status === 'Verified')
                        <form action="{{ route('admin.saas.domains.suspend', $domain->id) }}" method="POST" class="w-100" onsubmit="return confirm('Are you sure you want to suspend this custom domain? This will reset all verification flags.');">
                            @csrf
                            <button type="submit" class="btn btn-warning py-2.5 w-100 fw-bold text-start px-3 text-dark" title="Suspend routing and reset validation status">
                                <i class="bi bi-slash-circle me-2 fs-5"></i> Suspend Domain
                            </button>
                        </form>
                    @endif

                    {{-- Regenerate SSL --}}
                    @if($domain->status === 'Active')
                        <form action="{{ route('admin.saas.domains.regenerate-ssl', $domain->id) }}" method="POST" class="w-100">
                            @csrf
                            <button type="submit" class="btn btn-info py-2.5 w-100 fw-bold text-start px-3 text-white" title="Regenerate SSL certificate validation and extend expiration">
                                <i class="bi bi-shield-lock me-2 fs-5"></i> Regenerate SSL
                            </button>
                        </form>
                    @endif

                    {{-- Delete Domain --}}
                    <form action="{{ route('admin.saas.domains.destroy', $domain->id) }}" method="POST" class="w-100" onsubmit="return confirm('Are you sure you want to delete this custom domain mapping record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger py-2.5 w-100 fw-bold text-start px-3" title="Completely delete domain record">
                            <i class="bi bi-trash3 me-2 fs-5"></i> Delete Record
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

{{-- Reject Domain Modal --}}
<div class="modal fade" id="rejectDomainModal" tabindex="-1" aria-labelledby="rejectDomainModalLabel" aria-hidden="true" style="font-family:'Outfit',sans-serif;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="rejectDomainModalLabel"><i class="bi bi-exclamation-octagon text-danger me-2"></i>Reject Custom Domain Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.saas.domains.reject', $domain->id) }}" method="POST">
                @csrf
                <div class="modal-body px-4">
                    <p class="text-secondary small mb-3">Provide a clear explanation for rejecting the mapping request. The subscriber will view this reason in their Domain settings dashboard.</p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label fw-semibold text-secondary small">Rejection Reason</label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="4" class="form-control" placeholder="e.g. DNS TXT ownership challenge record could not be found or has incorrect value." required style="border-radius:10px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn btn-danger text-white fw-semibold" style="border-radius:10px;">Submit Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
