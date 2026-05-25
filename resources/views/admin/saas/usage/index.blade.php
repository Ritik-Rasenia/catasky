@extends('admin.layouts.app')

@section('title', 'Subscriber Usage Tracking —')

@push('css')
<style>
    /* Premium glassmorphic styles */
    .saas-header {
        background: linear-gradient(135deg, #1E1B4B 0%, #311042 50%, #1E293B 100%);
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
    
    /* Custom Usage Progress Bars */
    .usage-meter-wrap {
        background: #F1F5F9;
        height: 6px;
        border-radius: 100px;
        overflow: hidden;
        margin-top: 6px;
    }
    .usage-meter-bar {
        height: 100%;
        border-radius: 100px;
        transition: width 0.3s ease;
    }
    .usage-status-ok { background: #10B981; }
    .usage-status-warning { background: #F59E0B; }
    .usage-status-danger { background: #EF4444; }

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
            <h2 class="fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">Usage Tracking Grid</h2>
            <p class="text-white-50 mb-0" style="max-width: 600px;">
                Real-time subscriber quota compliance tracker. Monitor product limit usage and attribute counts to enforce tier compliance.
            </p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="saas-card mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.saas.usage.index') }}" method="GET" class="row g-3 align-items-center">
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
                            <a href="{{ route('admin.saas.usage.index') }}" class="btn btn-light rounded-pill"><i class="bi bi-x-lg"></i></a>
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
                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Subscriber Store</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Tier Plan</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Product Quota Tracker</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Attribute Limit</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Total Share Links</th>
                            <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted border-0">Compliance Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscribers as $sub)
                        @php
                            $profile = $sub->subscriberProfile;
                            $planName = $sub->plan_name;
                            $planSlug = $sub->subscription && $sub->subscription->plan ? $sub->subscription->plan->slug : 'none';
                            
                            $pCount = $sub->products_count;
                            $pLimit = $sub->products_limit;
                            
                            $pPct = 0;
                            $usageColorClass = 'usage-status-ok';
                            
                            if ($pLimit > 0) {
                                $pPct = round(($pCount / $pLimit) * 100);
                                if ($pPct >= 90) {
                                    $usageColorClass = 'usage-status-danger';
                                } elseif ($pPct >= 70) {
                                    $usageColorClass = 'usage-status-warning';
                                }
                            } elseif ($pLimit === -1 || $planSlug === 'business' || $planSlug === 'enterprise') {
                                // Unlimited logic
                                $pPct = 100;
                                $pLimitText = 'Unlimited';
                            } else {
                                $pPct = 0;
                            }
                        @endphp
                        <tr>
                            <td class="ps-4">
                                @if($profile)
                                    <div class="fw-bold text-dark">{{ $profile->company_name }}</div>
                                    <div class="text-muted small">Owner: {{ $sub->name }}</div>
                                @else
                                    <div class="fw-bold text-dark">{{ $sub->name }}</div>
                                    <div class="text-muted small">No profile built</div>
                                @endif
                            </td>
                            <td>
                                <span class="plan-badge plan-{{ $planSlug }}">
                                    {{ $planName }}
                                </span>
                            </td>
                            <td style="min-width: 200px;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    @if(isset($pLimitText) && $pLimitText === 'Unlimited')
                                        <span class="small fw-semibold text-dark">{{ $pCount }} Products</span>
                                        <span class="small text-muted fw-bold">Unlimited Limit</span>
                                    @else
                                        <span class="small fw-semibold text-dark">{{ $pCount }} of {{ $pLimit }} Used</span>
                                        <span class="small text-muted fw-bold">{{ $pPct }}%</span>
                                    @endif
                                </div>
                                <div class="usage-meter-wrap">
                                    @if(isset($pLimitText) && $pLimitText === 'Unlimited')
                                        <div class="usage-meter-bar usage-status-ok" style="width: 100%;"></div>
                                    @else
                                        <div class="usage-meter-bar {{ $usageColorClass }}" style="width: {{ min($pPct, 100) }}%;"></div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($sub->attributes_limit === -1 || $planSlug === 'business' || $planSlug === 'enterprise')
                                    <span class="badge bg-light text-success border fw-semibold rounded">Unlimited</span>
                                @else
                                    <span class="fw-semibold text-dark">{{ $sub->attributes_limit }} Attributes</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border fw-bold rounded-pill px-3">{{ $sub->shares_count }} Shares</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.subscribers.show', $sub->id) }}" class="btn btn-sm btn-light border rounded-pill px-3" title="Inspect Subscriber Quota Details">
                                    <i class="bi bi-gear-fill text-muted me-1"></i> Quota Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-bar-chart-line text-muted" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                                No subscriber compliance records found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscribers->hasPages())
                <div class="d-flex justify-content-between align-items-center p-4 border-top">
                    <div class="text-muted small">
                        Showing {{ $subscribers->firstItem() }} to {{ $subscribers->lastItem() }} of {{ $subscribers->total() }} subscriber quotas
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
