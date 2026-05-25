@extends('subscriber-panel.layouts.app')

@section('title', 'Subscription Plans')

@section('content')
<div class="vp-page-header">
    <div>
        <h1 class="vp-page-title">Subscription Pricing Plans</h1>
        <div class="vp-breadcrumb">
            <a href="{{ route('subscriber.dashboard') }}">Dashboard</a> &nbsp;/&nbsp; <a href="{{ route('subscriber.subscription.index') }}">Subscription</a> &nbsp;/&nbsp; <span>Plans</span>
        </div>
    </div>
</div>

<div class="text-center py-4">
    <h2 style="font-family:'Outfit',sans-serif; font-weight:800; color:var(--text-primary);">Supercharge Your CataSky Catalog sharing</h2>
    <p class="text-muted mx-auto" style="max-width:550px; font-size:0.95rem;">Unlock custom specs grids, white-label pdf templates, image assets grids, WhatsApp doubletick integrations, and detailed visual catalog analytics.</p>
</div>

<div class="row g-4 justify-content-center mt-2">
    @foreach($plans as $plan)
        @php
            $isCurrent = $currentSubscription && $currentSubscription->subscription_plan_id === $plan->id;
            $isPopular = $plan->slug === 'professional' || $plan->slug === 'enterprise';
        @endphp
        
        <div class="col-md-6 col-lg-4">
            <div class="vp-card h-100 position-relative" style="{{ $isPopular ? 'border-color:var(--subscriber-primary); box-shadow:var(--shadow-lg); transform: translateY(-4px);' : '' }}">
                
                @if($isPopular)
                    <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill text-white" style="background: linear-gradient(135deg, var(--subscriber-primary), var(--subscriber-secondary)); padding: 6px 16px; font-weight:700; font-size:0.7rem; letter-spacing:0.05em; text-transform:uppercase;">
                        RECOMMENDED
                    </span>
                @endif

                <div class="vp-card-body p-4 text-center d-flex flex-column h-100">
                    <h3 class="fw-bold mt-2" style="font-family:'Outfit',sans-serif; font-weight:800; color:var(--text-primary);">{{ $plan->name }}</h3>
                    <p class="text-muted mt-1" style="font-size:0.8rem; min-height: 38px;">{{ $plan->description }}</p>
                    
                    <div class="my-4 py-3 border-top border-bottom">
                        <span class="fs-1 fw-bold text-dark" style="font-family:'Outfit',sans-serif;">
                            {{ $plan->price > 0 ? '₹' . number_format($plan->price, 0) : 'Free' }}
                        </span>
                        @if($plan->price > 0)
                            <span class="text-muted">/ {{ $plan->duration_days }} days</span>
                        @else
                            <span class="text-muted">Forever</span>
                        @endif
                    </div>

                    <ul class="list-unstyled text-start mb-4 flex-grow-1" style="font-size:0.875rem; color:#475569;">
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-0.5"></i>
                            <span>Product Limit: <strong>{{ $plan->product_limit == -1 ? 'Unlimited' : $plan->product_limit }} Products</strong></span>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-0.5"></i>
                            <span>Attributes Limit: <strong>{{ $plan->attribute_limit == -1 ? 'Unlimited' : $plan->attribute_limit }} Fields</strong></span>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-0.5"></i>
                            <span>Share Links: <strong>{{ $plan->share_link_limit == -1 ? 'Unlimited' : $plan->share_link_limit }} Links</strong></span>
                        </li>
                        
                        <li class="mb-3 d-flex align-items-start gap-2 {{ $plan->pdf_sharing ? '' : 'text-muted text-decoration-line-through' }}">
                            <i class="bi {{ $plan->pdf_sharing ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} mt-0.5"></i>
                            <span>Premium PDF Catalog Generation</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2 {{ $plan->image_sharing ? '' : 'text-muted text-decoration-line-through' }}">
                            <i class="bi {{ $plan->image_sharing ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} mt-0.5"></i>
                            <span>Visual Assets Showcase Gallery</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2 {{ $plan->custom_branding ? '' : 'text-muted text-decoration-line-through' }}">
                            <i class="bi {{ $plan->custom_branding ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} mt-0.5"></i>
                            <span>Custom Brand Logos & Colors</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2 {{ $plan->watermark_removal ? '' : 'text-muted text-decoration-line-through' }}">
                            <i class="bi {{ $plan->watermark_removal ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} mt-0.5"></i>
                            <span>Watermark Customization & Removal</span>
                        </li>
                    </ul>

                    @if($isCurrent)
                        <button class="btn btn-secondary w-100 py-2.5 fw-bold" style="border-radius: 10px;" disabled>
                            <i class="bi bi-check2-all me-1"></i> Current Active Plan
                        </button>
                    @else
                        <a href="{{ route('subscriber.subscription.checkout', $plan->id) }}" class="btn-subscriber w-100 justify-content-center py-2.5 fs-6" style="{{ $isPopular ? 'background: linear-gradient(135deg, var(--subscriber-primary), var(--subscriber-secondary));' : 'background:#0F172A;' }}">
                            {{ $plan->price > 0 ? 'Subscribe Now' : 'Choose Plan' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
