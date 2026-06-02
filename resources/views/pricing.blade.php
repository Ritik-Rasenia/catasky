@extends('layouts.frontend')

@section('title', 'SaaS Subscription Pricing Plans - Catasky')

@section('content')
<div class="container py-5 mt-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4 animate-fade-in">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-secondary"><i class="bi bi-house-door-fill me-1"></i> Home</a></li>
            <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Pricing</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="text-center mb-5 animate-fade-in">
        <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold mb-3 small-text">
            <i class="bi bi-shield-check"></i> Simple, Transparent Pricing
        </div>
        <h1 class="display-4 fw-bold text-dark" style="letter-spacing: -1.5px;">Plans That Grow With Your Business</h1>
        <p class="text-secondary mx-auto" style="max-width: 600px; font-size: 1.05rem;">
            Select a plan to start showcasing your products beautifully, sharing instantly, and closing B2B sales faster.
        </p>
    </div>

    <!-- Pricing Switcher -->
    <div class="d-flex justify-content-center mb-5 animate-fade-in">
        <div class="pricing-toggle-wrap" style="background:#F1F5F9; border-radius:100px; padding:4px; display:inline-flex; align-items:center;">
            <button class="pricing-toggle-btn active btn py-2 px-4 rounded-pill fw-bold" id="monthly-btn" style="font-size:0.9rem; border:none; background:#1D6FEB; color:white;">Monthly Billing</button>
            <button class="pricing-toggle-btn btn py-2 px-4 rounded-pill text-secondary fw-bold" id="annual-btn" style="font-size:0.9rem; border:none; background:transparent;">Annual Billing (Save 30%)</button>
        </div>
    </div>

    <!-- Pricing Cards Grid -->
    <div class="row g-4 justify-content-center align-items-stretch animate-fade-in">

        @foreach($plans as $plan)
        @php
            $isTrial = $plan->is_trial;
            $isPopular = $plan->slug === 'business';
            $btnClass = $isTrial
                ? 'btn btn-success py-3 rounded-3 w-100 fw-bold mt-auto'
                : ($isPopular
                    ? 'btn btn-primary py-3 rounded-3 w-100 fw-bold mt-auto text-white'
                    : 'btn btn-outline-primary py-3 rounded-3 w-100 fw-bold mt-auto');
            $btnStyle = $isPopular && !$isTrial ? 'background:#1D6FEB; border:none;' : '';
            if ($isTrial) { $btnStyle = 'background:#10B981; border:none; color:white;'; }
            if ($plan->slug === 'enterprise') { $btnClass = 'btn btn-dark py-3 rounded-3 w-100 fw-bold mt-auto'; $btnStyle = ''; }
        @endphp

        <div class="col-lg-3 col-md-6 d-flex">
            <div class="premium-card p-4 p-md-4 w-100 bg-white border rounded-4 d-flex flex-column justify-content-between position-relative
                {{ $isPopular ? 'border-primary border-2' : '' }}"
                style="box-shadow: {{ $isPopular ? 'var(--shadow-md)' : '' }}; transition: transform 0.3s; border-radius: 20px;"
                onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='none'">

                @if($isTrial)
                    <div class="position-absolute top-0 end-0 text-white px-3 py-1 rounded-bottom-start fw-bold small-text"
                        style="background:#10B981; border-top-right-radius:10px; border-bottom-left-radius:12px; font-size:0.75rem;">
                        🎁 FREE TRIAL
                    </div>
                @elseif($isPopular)
                    <div class="position-absolute top-0 end-0 bg-primary text-white px-3 py-1 rounded-bottom-start fw-bold small-text"
                        style="border-top-right-radius:10px; border-bottom-left-radius:12px; font-size:0.75rem;">
                        ★ RECOMMENDED
                    </div>
                @endif

                <div>
                    <h3 class="fw-bold mb-2 {{ $isPopular ? 'text-primary' : 'text-dark' }}" style="font-family:'Outfit',sans-serif;">
                        {{ $plan->name }}
                    </h3>
                    <p class="text-secondary small mb-4">{{ $plan->description }}</p>

                    <div class="mb-4">
                        @if($isTrial)
                            <span class="fs-2 fw-bold text-success">Free</span>
                            <span class="text-secondary"> / {{ $plan->duration_days }} Days</span>
                        @elseif($plan->price > 0)
                            <span class="fs-2 fw-bold text-dark plan-price" data-monthly="₹{{ number_format($plan->price, 0) }}" data-annual="₹{{ number_format($plan->price * 0.7, 0) }}">₹{{ number_format($plan->price, 0) }}</span>
                            <span class="text-secondary plan-period"> / month</span>
                        @else
                            <span class="fs-2 fw-bold text-dark">Free</span>
                        @endif
                    </div>

                    <hr class="my-4" style="border-color:#E2E8F0;">

                    <ul class="list-unstyled d-flex flex-column gap-2 mb-4 text-start" style="font-size:0.875rem; color:#475569;">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-0.5"></i>
                            <span>Product Limit: <strong>{{ $plan->product_limit == -1 ? 'Unlimited' : $plan->product_limit }} Products</strong></span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-0.5"></i>
                            <span>Attributes Limit: <strong>{{ $plan->attribute_limit == -1 ? 'Unlimited' : $plan->attribute_limit }} Fields</strong></span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-0.5"></i>
                            <span>Share Links: <strong>{{ $plan->share_link_limit == -1 ? 'Unlimited' : $plan->share_link_limit }} Links</strong></span>
                        </li>
                        
                        <li class="d-flex align-items-start gap-2 {{ $plan->pdf_sharing ? '' : 'text-muted text-decoration-line-through' }}">
                            <i class="bi {{ $plan->pdf_sharing ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} mt-0.5"></i>
                            <span>Premium PDF Catalog Generation</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 {{ $plan->image_sharing ? '' : 'text-muted text-decoration-line-through' }}">
                            <i class="bi {{ $plan->image_sharing ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} mt-0.5"></i>
                            <span>Visual Assets Showcase Gallery</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 {{ $plan->custom_branding ? '' : 'text-muted text-decoration-line-through' }}">
                            <i class="bi {{ $plan->custom_branding ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} mt-0.5"></i>
                            <span>Custom Brand Logos & Colors</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 {{ $plan->watermark_removal ? '' : 'text-muted text-decoration-line-through' }}">
                            <i class="bi {{ $plan->watermark_removal ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} mt-0.5"></i>
                            <span>Watermark Customization & Removal</span>
                        </li>
                    </ul>
                </div>

                <a href="{{ route('subscriber.register') }}?plan={{ $plan->slug }}"
                   class="{{ $btnClass }} mt-auto" style="{{ $btnStyle }}">
                    {{ $isTrial ? 'Start Free Trial' : ($plan->price > 0 ? 'Subscribe Now' : 'Get Started') }}
                </a>
            </div>
        </div>
        @endforeach

    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const monthlyBtn = document.getElementById('monthly-btn');
        const annualBtn = document.getElementById('annual-btn');
        const prices = document.querySelectorAll('.plan-price');
        const periods = document.querySelectorAll('.plan-period');

        monthlyBtn.addEventListener('click', function() {
            monthlyBtn.classList.add('active');
            monthlyBtn.style.background = '#1D6FEB';
            monthlyBtn.style.color = 'white';
            
            annualBtn.classList.remove('active');
            annualBtn.style.background = 'transparent';
            annualBtn.style.color = '#6C757D';
            
            prices.forEach(function(priceEl) {
                priceEl.textContent = priceEl.getAttribute('data-monthly');
            });
            
            periods.forEach(function(periodEl) {
                periodEl.textContent = ' / month';
            });
        });

        annualBtn.addEventListener('click', function() {
            annualBtn.classList.add('active');
            annualBtn.style.background = '#1D6FEB';
            annualBtn.style.color = 'white';
            
            monthlyBtn.classList.remove('active');
            monthlyBtn.style.background = 'transparent';
            monthlyBtn.style.color = '#6C757D';
            
            prices.forEach(function(priceEl) {
                priceEl.textContent = priceEl.getAttribute('data-annual');
            });
            
            periods.forEach(function(periodEl) {
                periodEl.textContent = ' / month (billed annually)';
            });
        });
    });
</script>

<style>
    .bg-primary-soft {
        background-color: rgba(29, 111, 235, 0.08) !important;
    }
</style>
@endsection
