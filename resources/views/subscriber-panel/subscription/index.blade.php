@extends('subscriber-panel.layouts.app')

@section('title', 'Subscription & Billing')

@section('content')
<div class="vp-page-header">
    <div>
        <h1 class="vp-page-title">Subscription & Billing</h1>
        <div class="vp-breadcrumb">
            <a href="{{ route('subscriber.dashboard') }}">Dashboard</a> &nbsp;/&nbsp; <span>Subscription</span>
        </div>
    </div>
    <div class="d-flex gap-2">
        @if($activeSubscription)
            <a href="{{ route('subscriber.subscription.checkout', $activeSubscription->plan) }}" class="btn btn-outline-primary" style="border-radius:10px;">
                <i class="bi bi-arrow-clockwise me-1"></i> Renew
            </a>
        @endif
        <a href="{{ route('subscriber.subscription.plans') }}" class="btn-subscriber">
            <i class="bi bi-award-fill"></i> Upgrade Plan
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0  mb-4" role="alert" style="border-radius:12px; background:#DCFCE7; color:#15803d;">
    <i class="bi bi-check-circle-fill me-2"></i>
    {!! session('success') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row g-4">
    {{-- Active Subscription details --}}
    <div class="col-lg-7">
        <div class="vp-card h-100">
            <div class="vp-card-header">
                <h5 class="vp-card-title">Current Plan Summary</h5>
                @if($activeSubscription)
                    <span class="badge {{ $activeSubscription->status === 'trial' ? 'badge-trial' : 'badge-active' }}" style="padding: 6px 12px; border-radius: 20px; font-weight:600; font-size: 0.75rem;">
                        {{ strtoupper($activeSubscription->status) }}
                    </span>
                @else
                    <span class="badge badge-inactive" style="padding: 6px 12px; border-radius: 20px; font-weight:600; font-size: 0.75rem;">
                        NO ACTIVE PLAN
                    </span>
                @endif
            </div>
            
            <div class="vp-card-body">
                @if($activeSubscription)
                    @php 
                        $plan = $activeSubscription->plan;
                        $daysLeft = $activeSubscription->daysRemaining();
                    @endphp
                    
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="p-3 rounded-4 text-white" style="background: linear-gradient(135deg, var(--subscriber-primary), var(--subscriber-secondary)); width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; font-family: 'Outfit', sans-serif;">
                            {{ substr($plan->name ?? 'F', 0, 1) }}
                        </div>
                        <div>
                            <h4 style="font-family:'Outfit',sans-serif; font-weight:800; margin:0; color:var(--text-primary);">{{ $plan->name ?? 'Free Trial' }}</h4>
                            <p class="text-muted mb-0" style="font-size:0.85rem;">{{ $plan->description ?? 'Standard subscriber subscription' }}</p>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="text-muted" style="font-size: 0.75rem; font-weight:600; text-transform:uppercase;">Price & Term</div>
                                <div class="fs-5 fw-bold text-dark mt-1">
                                    {{ $plan->price > 0 ? '₹' . number_format($plan->price, 2) : 'Free' }}
                                    <span style="font-size: 0.78rem; font-weight:normal; color:var(--text-muted);">/ {{ $plan->duration_days }} Days</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="text-muted" style="font-size: 0.75rem; font-weight:600; text-transform:uppercase;">Time Remaining</div>
                                <div class="fs-5 fw-bold text-dark mt-1">
                                    {{ $daysLeft }} Days Left
                                    <span style="font-size: 0.78rem; font-weight:normal; color:var(--text-muted);">(Expires {{ $activeSubscription->ends_at ? $activeSubscription->ends_at->format('d M Y') : ($activeSubscription->trial_ends_at ? $activeSubscription->trial_ends_at->format('d M Y') : 'N/A') }})</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Feature checkboxes checklist --}}
                    <h6 class="mb-3 fw-bold" style="font-family:'Outfit',sans-serif; font-size:0.9rem; letter-spacing:0.04em; color:var(--text-muted); text-transform:uppercase;">Features Mapped to Plan</h6>
                    
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 py-1">
                                <i class="bi {{ $plan->pdf_sharing ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} fs-5"></i>
                                <span style="font-size:0.875rem;">Premium PDF Sharing</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 py-1">
                                <i class="bi {{ $plan->image_sharing ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} fs-5"></i>
                                <span style="font-size:0.875rem;">Interactive Image Galleries</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 py-1">
                                <i class="bi {{ $plan->custom_branding ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} fs-5"></i>
                                <span style="font-size:0.875rem;">Custom Color Themes & Logo</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 py-1">
                                <i class="bi {{ $plan->watermark_removal ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} fs-5"></i>
                                <span style="font-size:0.875rem;">Branded PDF Watermarks</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="empty-state py-4">
                        <div class="empty-state-icon text-danger"><i class="bi bi-exclamation-octagon"></i></div>
                        <div class="empty-state-title">No Active Subscription</div>
                        <p class="empty-state-text">Select a pricing plan below to unlock custom product sharing, high-fidelity PDFs, and QR-coded digital catalogs.</p>
                        <a href="{{ route('subscriber.subscription.plans') }}" class="btn-subscriber">Choose Plan</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Plan Quota & Usage Meters --}}
    <div class="col-lg-5">
        <div class="vp-card h-100">
            <div class="vp-card-header">
                <h5 class="vp-card-title">Resource Quota Usage</h5>
            </div>
            
            <div class="vp-card-body">
                @if($activeSubscription)
                    @php
                        $plan = $activeSubscription->plan;
                        $prodCount = auth()->user()->subscriberProducts()->count();
                        $prodLimit = $plan->product_limit;
                        $prodPct = $prodLimit > 0 ? min(100, round(($prodCount / $prodLimit) * 100)) : 0;

                        $attrCount = auth()->user()->attributes()->count();
                        $attrLimit = $plan->attribute_limit;
                        $attrPct = $attrLimit > 0 ? min(100, round(($attrCount / $attrLimit) * 100)) : 0;

                        $shareCount = auth()->user()->shareLinks()->count();
                        $shareLimit = $plan->share_link_limit;
                        $sharePct = $shareLimit > 0 ? min(100, round(($shareCount / $shareLimit) * 100)) : 0;
                    @endphp

                    {{-- Products Usage meter --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold" style="font-size:0.85rem;">Products Created</span>
                            <span style="font-size:0.82rem; font-weight:600; color:var(--text-muted);">
                                {{ $prodCount }} / {{ $prodLimit == -1 ? 'Unlimited' : $prodLimit }}
                            </span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 20px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $prodLimit == -1 ? 0 : $prodPct }}%; background: linear-gradient(90deg, var(--subscriber-primary), var(--subscriber-secondary)); border-radius: 20px;" aria-valuenow="{{ $prodPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    {{-- Custom Attributes Usage meter --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold" style="font-size:0.85rem;">Attributes Configured</span>
                            <span style="font-size:0.82rem; font-weight:600; color:var(--text-muted);">
                                {{ $attrCount }} / {{ $attrLimit == -1 ? 'Unlimited' : $attrLimit }}
                            </span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $attrLimit == -1 ? 0 : $attrPct }}%; border-radius: 20px;" aria-valuenow="{{ $attrPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    {{-- Share links Usage meter --}}
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold" style="font-size:0.85rem;">Share Links Generated</span>
                            <span style="font-size:0.82rem; font-weight:600; color:var(--text-muted);">
                                {{ $shareCount }} / {{ $shareLimit == -1 ? 'Unlimited' : $shareLimit }}
                            </span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 20px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $shareLimit == -1 ? 0 : $sharePct }}%; border-radius: 20px;" aria-valuenow="{{ $sharePct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-graph-up fs-1 mb-2 d-block"></i>
                        <span>No limits or stats to display. Activate a plan first.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Invoice history --}}
<div class="vp-card mt-4">
    <div class="vp-card-header">
        <h5 class="vp-card-title">Billing & Invoice History</h5>
    </div>
    
    {{-- Recent payments --}}
    <div class="vp-card mt-4">
        <div class="vp-card-header">
            <h5 class="vp-card-title">Recent Payments</h5>
        </div>
        <div class="vp-card-body p-0">
            @if($payments && $payments->count() > 0)
                <div class="table-responsive">
                    <table class="vp-table mb-0 table-nowrap">
                        <thead>
                            <tr>
                                <th>Transaction</th>
                                <th>Gateway</th>
                                <th>Date</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $p)
                            <tr>
                                <td>{{ $p->transaction_id }}</td>
                                <td>{{ ucfirst($p->gateway) }}</td>
                                <td>{{ $p->paid_at ? $p->paid_at->format('d M Y') : '-' }}</td>
                                <td class="fw-bold">₹{{ number_format($p->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state py-5">
                    <div class="empty-state-icon"><i class="bi bi-wallet"></i></div>
                    <div class="empty-state-title">No Payments Found</div>
                    <p class="empty-state-text">Payments will be listed here after transactions are completed.</p>
                </div>
            @endif
        </div>
    </div>
    <div class="vp-card-body p-0">
        @if($invoices->count() > 0)
            <div class="table-responsive">
                <table class="vp-table mb-0 table-nowrap">
                    <thead>
                        <tr>
                            <th>Invoice Number</th>
                            <th>Description</th>
                            <th>Billing Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                        <tr>
                            <td class="fw-bold text-dark">#{{ $inv->invoice_number }}</td>
                            <td>
                                {{ $inv->subscription?->plan?->name ?? 'Upgrade' }} Plan 
                                <span class="text-muted" style="font-size:0.75rem;">(txn: {{ $inv->payment?->transaction_id ?? 'N/A' }})</span>
                            </td>
                            <td>{{ $inv->paid_date ? $inv->paid_date->format('d M Y') : 'N/A' }}</td>
                            <td class="fw-bold">₹{{ number_format($inv->total, 2) }}</td>
                            <td>
                                <span class="badge badge-active" style="padding:4px 8px; border-radius: 20px;">Paid</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('subscriber.subscription.invoice', $inv->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Print Invoice
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state py-5">
                <div class="empty-state-icon"><i class="bi bi-receipt"></i></div>
                <div class="empty-state-title">No Invoices Found</div>
                <p class="empty-state-text">Your billing statements and subscription receipt PDFs will appear here once payments are executed.</p>
            </div>
        @endif
    </div>
</div>
@endsection
