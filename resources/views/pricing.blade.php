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
        <!-- Starter Plan -->
        <div class="col-lg-4 col-md-6 d-flex">
            <div class="premium-card p-4 p-md-5 w-100 bg-white border rounded-4 d-flex flex-column justify-content-between" style="box-shadow: var(--); transition: transform 0.3s; border-radius: 20px;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='none'">
                <div>
                    <h3 class="fw-bold text-dark mb-2" style="font-family:'Outfit',sans-serif;">Starter Plan</h3>
                    <p class="text-secondary small mb-4">Perfect for small teams and freelancers getting started with digital catalogs.</p>
                    
                    <div class="mb-4">
                        <span class="fs-2 fw-bold text-dark" id="starter-price-val">₹499</span>
                        <span class="text-secondary" id="starter-period">/ month</span>
                    </div>

                    <hr class="my-4" style="border-color:#E2E8F0;">

                    <ul class="list-unstyled d-flex flex-column gap-3 mb-4">
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="text-dark small fw-semibold">50 Products</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="text-dark small fw-semibold">Subdomain or path-based store</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="text-dark small fw-semibold">Basic sharing options</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 text-muted">
                            <i class="bi bi-x-circle-fill text-danger"></i>
                            <span class="small">Custom domain mapping</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 text-muted">
                            <i class="bi bi-x-circle-fill text-danger"></i>
                            <span class="small">White-label support</span>
                        </li>
                    </ul>
                </div>
                
                <a href="{{ route('subscriber.register') }}?plan=starter" class="btn btn-outline-primary py-3 rounded-3 w-100 fw-bold mt-auto" style="border-width:2px;">
                    Get Started with Starter
                </a>
            </div>
        </div>

        <!-- Business Plan -->
        <div class="col-lg-4 col-md-6 d-flex">
            <div class="premium-card p-4 p-md-5 w-100 bg-white border border-primary border-2 rounded-4 d-flex flex-column justify-content-between position-relative" style="box-shadow: var(--shadow-md); transition: transform 0.3s; border-radius: 20px;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='none'">
                <div class="position-absolute top-0 end-0 bg-primary text-white px-3 py-1 rounded-bottom-start fw-bold small-text" style="border-top-right-radius: 10px; border-bottom-left-radius: 12px; font-size:0.75rem;">
                    ★ RECOMMENDED
                </div>
                <div>
                    <h3 class="fw-bold text-primary mb-2" style="font-family:'Outfit',sans-serif;">Business Plan</h3>
                    <p class="text-secondary small mb-4">For growing B2B brands that need unlimited products and advanced analytics.</p>
                    
                    <div class="mb-4">
                        <span class="fs-2 fw-bold text-dark" id="business-price-val">₹1,299</span>
                        <span class="text-secondary" id="business-period">/ month</span>
                    </div>

                    <hr class="my-4" style="border-color:#E2E8F0;">

                    <ul class="list-unstyled d-flex flex-column gap-3 mb-4">
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="text-dark small fw-semibold">250 Products</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="text-dark small fw-semibold">Subdomain or path-based store</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="text-dark small fw-semibold">Advanced sharing & analytics</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 text-muted">
                            <i class="bi bi-x-circle-fill text-danger"></i>
                            <span class="small">Custom domain mapping</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 text-muted">
                            <i class="bi bi-x-circle-fill text-danger"></i>
                            <span class="small">White-label support</span>
                        </li>
                    </ul>
                </div>
                
                <a href="{{ route('subscriber.register') }}?plan=business" class="btn btn-primary py-3 rounded-3 w-100 fw-bold mt-auto text-white" style="background:#1D6FEB; border:none;">
                    Subscribe to Business
                </a>
            </div>
        </div>

        <!-- Enterprise Plan -->
        <div class="col-lg-4 col-md-6 d-flex">
            <div class="premium-card p-4 p-md-5 w-100 bg-white border rounded-4 d-flex flex-column justify-content-between" style="box-shadow: var(--); transition: transform 0.3s; border-radius: 20px;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='none'">
                <div>
                    <h3 class="fw-bold text-dark mb-2" style="font-family:'Outfit',sans-serif;">Enterprise Plan</h3>
                    <p class="text-secondary small mb-4">For large-scale B2B systems needing white-label and custom domains.</p>
                    
                    <div class="mb-4">
                        <span class="fs-2 fw-bold text-dark" id="enterprise-price-val">₹3,999</span>
                        <span class="text-secondary" id="enterprise-period">/ month</span>
                    </div>

                    <hr class="my-4" style="border-color:#E2E8F0;">

                    <ul class="list-unstyled d-flex flex-column gap-3 mb-4">
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="text-dark small fw-semibold">Unlimited Access</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="text-dark small fw-semibold">Custom Domain mapping support</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="text-dark small fw-semibold">White-label (Remove Catasky logo)</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="text-dark small fw-semibold">Dedicated account support</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="text-dark small fw-semibold">SLA-backed uptime guarantee</span>
                        </li>
                    </ul>
                </div>
                
                <a href="{{ route('subscriber.register') }}?plan=enterprise" class="btn btn-dark py-3 rounded-3 w-100 fw-bold mt-auto">
                    Get Enterprise Plan
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const monthlyBtn = document.getElementById('monthly-btn');
        const annualBtn = document.getElementById('annual-btn');
        
        const starterVal = document.getElementById('starter-price-val');
        const businessVal = document.getElementById('business-price-val');
        const enterpriseVal = document.getElementById('enterprise-price-val');
        
        const starterPeriod = document.getElementById('starter-period');
        const businessPeriod = document.getElementById('business-period');
        const enterprisePeriod = document.getElementById('enterprise-period');

        monthlyBtn.addEventListener('click', function() {
            monthlyBtn.classList.add('active');
            monthlyBtn.style.background = '#1D6FEB';
            monthlyBtn.style.color = 'white';
            
            annualBtn.classList.remove('active');
            annualBtn.style.background = 'transparent';
            annualBtn.style.color = '#6C757D';
            
            starterVal.textContent = '₹499';
            businessVal.textContent = '₹1,299';
            enterpriseVal.textContent = '₹3,999';
            
            starterPeriod.textContent = '/ month';
            businessPeriod.textContent = '/ month';
            enterprisePeriod.textContent = '/ month';
        });

        annualBtn.addEventListener('click', function() {
            annualBtn.classList.add('active');
            annualBtn.style.background = '#1D6FEB';
            annualBtn.style.color = 'white';
            
            monthlyBtn.classList.remove('active');
            monthlyBtn.style.background = 'transparent';
            monthlyBtn.style.color = '#6C757D';
            
            starterVal.textContent = '₹349';
            businessVal.textContent = '₹899';
            enterpriseVal.textContent = '₹2,799';
            
            starterPeriod.textContent = '/ month (billed annually)';
            businessPeriod.textContent = '/ month (billed annually)';
            enterprisePeriod.textContent = '/ month (billed annually)';
        });
    });
</script>

<style>
    .bg-primary-soft {
        background-color: rgba(29, 111, 235, 0.08) !important;
    }
</style>
@endsection
