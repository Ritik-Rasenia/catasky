@extends('admin.layouts.app')

@section('title', 'Custom Domain Management —')

@push('css')
<style>
    /* Premium glassmorphic styles */
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
    .status-approved {
        background: rgba(99, 102, 241, 0.1);
        color: #6366F1;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }
    .status-rejected {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .dns-txt-box {
        background: #F8FAFC;
        border: 1px dashed #CBD5E1;
        border-radius: 8px;
        padding: 6px 12px;
        font-family: monospace;
        font-size: 0.78rem;
        color: #334155;
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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
            <h2 class="fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">Domain Management</h2>
            <p class="text-white-50 mb-0" style="max-width: 600px;">
                Review White-Label custom domain mapping requests for Enterprise subscribers, inspect DNS values, and issue automated SSL certs.
            </p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="saas-card mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.saas.domains.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-8 col-lg-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by domain or company name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3 d-grid">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill flex-grow-1 px-4"><i class="bi bi-funnel-fill me-2"></i>Filter</button>
                        @if(request()->filled('search'))
                            <a href="{{ route('admin.saas.domains.index') }}" class="btn btn-light rounded-pill"><i class="bi bi-x-lg"></i></a>
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
                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Custom Domain</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Subscriber Store</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">TXT Verification Record</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">DNS Status</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">SSL Status</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Status</th>
                            <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($domains as $domain)
                        @php
                            $profile = $domain->user->subscriberProfile ?? null;
                            $status = $domain->status;
                            $sslStatus = $domain->ssl_status;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded bg-primary bg-opacity-10 text-primary p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                        <i class="bi bi-globe2"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $domain->domain }}</div>
                                        <a href="http://{{ $domain->domain }}" target="_blank" class="text-muted small text-decoration-none">
                                            <i class="bi bi-link-45deg"></i>Visit Domain
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($profile)
                                    <div class="fw-bold text-dark">{{ $profile->company_name }}</div>
                                    <div class="text-muted small">Owner: {{ $domain->user->name }}</div>
                                @else
                                    <span class="text-muted small">No profile</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small fw-semibold text-muted text-uppercase" style="font-size: 0.65rem; width: 40px;">Host:</span>
                                        <div class="dns-txt-box" title="{{ $domain->dns_txt_key }}">{{ $domain->dns_txt_key }}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small fw-semibold text-muted text-uppercase" style="font-size: 0.65rem; width: 40px;">Value:</span>
                                        <div class="dns-txt-box" title="{{ $domain->dns_txt_value }}">{{ $domain->dns_txt_value }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($domain->dns_verified)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 border border-success border-opacity-20">
                                        <i class="bi bi-shield-check me-1"></i> Verified
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 border border-warning border-opacity-20">
                                        <i class="bi bi-shield-exclamation me-1"></i> Unverified
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($sslStatus === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 border border-success border-opacity-20">
                                        <i class="bi bi-lock-fill me-1"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 border border-secondary border-opacity-20">
                                        <i class="bi bi-hourglass-split me-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge status-{{ $status }}">
                                    @if($status === 'active')
                                        Active
                                    @elseif($status === 'approved')
                                        Verified
                                    @elseif($status === 'rejected')
                                        Rejected
                                    @else
                                        Pending
                                    @endif
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- DNS verification simulation --}}
                                    <form action="{{ route('admin.saas.domains.verify', $domain->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light border" title="Simulate DNS Lookup Check">
                                            <i class="bi bi-arrow-repeat text-primary"></i> DNS Check
                                        </button>
                                    </form>

                                    @if($status !== 'active')
                                        <form action="{{ route('admin.saas.domains.approve', $domain->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Activate Domain Routing">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($status !== 'rejected')
                                        <form action="{{ route('admin.saas.domains.reject', $domain->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject / Deactivate">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-globe text-muted" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                                No custom domain mapping requests found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($domains->hasPages())
                <div class="d-flex justify-content-between align-items-center p-4 border-top">
                    <div class="text-muted small">
                        Showing {{ $domains->firstItem() }} to {{ $domains->lastItem() }} of {{ $domains->total() }} domains
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
