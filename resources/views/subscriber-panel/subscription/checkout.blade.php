@extends('subscriber-panel.layouts.app')

@section('title', 'Secure Checkout')

@section('content')
@php
    $rzpKey = config('services.razorpay.key');
    $isTestMode = str_starts_with($rzpKey, 'rzp_test');
    $isNotConfigured = empty($rzpKey) || $rzpKey === 'rzp_test_change_me' || empty(config('services.razorpay.secret')) || config('services.razorpay.secret') === 'change_me_secret';
    $hasGst = !$plan->is_trial;

    // Prorata vs full price
    $baseAmount  = isset($prorata) && $prorata ? $prorata['prorata_amount'] : $plan->price;
    $gstAmount   = $hasGst ? round($baseAmount * 0.18, 2) : 0;
    $totalAmount = $baseAmount + $gstAmount;
@endphp


{{-- Global Error / Success Messages --}}
@if(session('error'))
<div class="alert alert-danger border-0 shadow-sm d-flex gap-3 align-items-center p-3 mb-4 rounded-3" style="background: rgba(239, 68, 68, 0.08); border-left: 4px solid #EF4444 !important;">
    <i class="bi bi-x-circle-fill text-danger fs-4"></i>
    <div class="fw-semibold text-danger small">{{ session('error') }}</div>
</div>
@endif

@if($isNotConfigured)
<div class="alert alert-warning border-0 shadow-sm d-flex gap-3 align-items-center p-4 mb-4 rounded-3" style="background: rgba(245, 158, 11, 0.08); border-left: 4px solid #F59E0B !important;">
    <i class="bi bi-exclamation-triangle-fill text-warning fs-3"></i>
    <div>
        <h6 class="fw-bold mb-1 text-warning" style="font-size:0.9rem;">Razorpay Gateway Setup Required</h6>
        <p class="mb-0 text-muted small" style="line-height: 1.5;">You are currently running with default placeholder credentials. Please replace <strong>`RAZORPAY_KEY`</strong> and <strong>`RAZORPAY_SECRET`</strong> in your <strong>`.env`</strong> file with your valid Razorpay Dashboard API credentials to complete transactions.</p>
    </div>
</div>
@endif

{{-- Prorata Upgrade Banner --}}
@if(isset($isUpgrade) && $isUpgrade && isset($prorata) && $prorata)
<div class="alert border-0 shadow-sm d-flex gap-3 align-items-start p-4 mb-4 rounded-3" style="background: rgba(79, 70, 229, 0.06); border-left: 4px solid #4F46E5 !important;">
    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:rgba(79,70,229,0.12);">
        <i class="bi bi-arrow-up-circle-fill text-primary fs-5"></i>
    </div>
    <div class="flex-grow-1">
        <h6 class="fw-bold mb-1" style="font-size:0.9rem;color:#3730A3;">⚡ Prorata Upgrade Billing Applied</h6>
        <p class="mb-2 text-muted small">You have an active subscription with <strong>{{ $prorata['remaining_days'] }}</strong> days remaining. Your unused credit of <strong class="text-success">₹{{ number_format($prorata['unused_credit'], 2) }}</strong> has been deducted from the new plan price.</p>
        <div class="d-flex flex-wrap gap-3 mt-1">
            <div class="px-3 py-2 rounded-3 small" style="background:#f1f5f9;font-size:0.78rem;">
                <span class="text-muted">New Plan Full Price</span><br>
                <strong class="text-dark">₹{{ number_format($prorata['new_plan_price'], 2) }}</strong>
            </div>
            <div class="px-3 py-2 rounded-3 small text-center" style="background:#f0fdf4;font-size:0.78rem;">
                <span class="text-success">Unused Credit</span><br>
                <strong class="text-success">− ₹{{ number_format($prorata['unused_credit'], 2) }}</strong>
            </div>
            <div class="px-3 py-2 rounded-3 small" style="background:rgba(79,70,229,0.08);font-size:0.78rem;">
                <span class="text-primary fw-bold">You Pay Today</span><br>
                <strong class="text-primary">₹{{ number_format($totalAmount, 2) }}</strong>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-4 position-relative">
    {{-- Secure Payment Form Card --}}
    <div class="col-lg-7">
        <div class="vp-card position-relative overflow-hidden" id="checkout-container" style="border-radius:16px; border: 1px solid #E2E8F0; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05);">
            
            {{-- Blur Loader Overlay during API validation/signature verification --}}
            <div id="payment-overlay" class="position-absolute w-100 h-100 top-0 start-0 d-none flex-column align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.9); z-index: 1050; border-radius: 16px; backdrop-filter: blur(6px);">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem; border-width: 0.25em;"></div>
                <h6 class="fw-bold text-dark mb-1" id="overlay-title">Initiating Secure Gateway...</h6>
                <p class="text-muted small mb-0" id="overlay-desc">Connecting with Razorpay. Please do not close or refresh this tab.</p>
            </div>

            <div class="vp-card-header d-flex align-items-center justify-content-between p-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <img src="https://razorpay.com/favicon.png" style="width: 22px; height: 22px; object-fit: contain;" alt="Razorpay">
                    <h5 class="vp-card-title m-0 fw-bold" style="font-family:'Outfit',sans-serif; color:var(--text-primary);">Secure Payment Gateway</h5>
                </div>
                
                @if(!$isNotConfigured)
                    @if($isTestMode)
                        <span class="badge bg-warning-soft text-warning fw-bold px-3 py-1.5 border border-warning" style="font-size:0.75rem; border-radius:30px; background-color: rgba(245, 158, 11, 0.08) !important;">
                            <i class="bi bi-shield-slash me-1"></i> Sandbox / Test Mode
                        </span>
                    @else
                        <span class="badge bg-success-soft text-success fw-bold px-3 py-1.5 border border-success" style="font-size:0.75rem; border-radius:30px; background-color: rgba(16, 185, 129, 0.08) !important;">
                            <i class="bi bi-shield-check-fill me-1"></i> Live Production Mode
                        </span>
                    @endif
                @endif
            </div>
            
            <div class="vp-card-body p-4">
                
                @if($plan->is_trial)
                {{-- Trial Intro Hero --}}
                <div class="p-4 rounded-4 mb-4 text-center" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.03) 0%, rgba(16, 185, 129, 0.07) 100%); border: 1px dashed rgba(16, 185, 129, 0.2);">
                    <div class="mx-auto mb-3" style="width: 56px; height: 56px; background: #ffffff; border-radius: 50%; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.1);">
                        <i class="bi bi-gift-fill text-success" style="font-size:1.6rem;"></i>
                    </div>
                    <h6 class="fw-bold mb-1 text-dark" style="font-size:1rem; font-family:'Outfit', sans-serif;">Complimentary Trial Plan</h6>
                    <p class="text-muted small mx-auto mb-0" style="max-width: 440px; line-height: 1.5;">
                        You have selected the free trial option. Absolutely no payment or credit cards are required to begin. Activate below to launch your catalog instantly!
                    </p>
                </div>
                @elseif(isset($isUpgrade) && $isUpgrade)
                {{-- Upgrade Intro Hero --}}
                <div class="p-4 rounded-4 mb-4 text-center" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.03) 0%, rgba(79, 70, 229, 0.07) 100%); border: 1px dashed rgba(79, 70, 229, 0.2);">
                    <div class="mx-auto mb-3" style="width: 56px; height: 56px; background: #ffffff; border-radius: 50%; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.1);">
                        <i class="bi bi-arrow-up-circle-fill text-primary" style="font-size:1.6rem;"></i>
                    </div>
                    <h6 class="fw-bold mb-1 text-dark" style="font-size:1rem; font-family:'Outfit', sans-serif;">Plan Upgrade — Prorata Billing</h6>
                    <p class="text-muted small mx-auto mb-0" style="max-width: 440px; line-height: 1.5;">
                        You are upgrading your subscription. Only the price difference (adjusted for unused days) is charged today. You get the full new plan duration.
                    </p>
                </div>
                @else
                {{-- Razorpay Intro Hero --}}
                <div class="p-4 rounded-4 mb-4 text-center" style="background: linear-gradient(135deg, rgba(29, 111, 235, 0.03) 0%, rgba(29, 111, 235, 0.07) 100%); border: 1px dashed rgba(29, 111, 235, 0.2);">
                    <div class="mx-auto mb-3" style="width: 56px; height: 56px; background: #ffffff; border-radius: 50%; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 10px rgba(29, 111, 235, 0.1);">
                        <i class="bi bi-shield-lock-fill text-primary" style="font-size:1.6rem;"></i>
                    </div>
                    <h6 class="fw-bold mb-1 text-dark" style="font-size:1rem; font-family:'Outfit', sans-serif;">Unified UPI &amp; Card Payments</h6>
                    <p class="text-muted small mx-auto mb-0" style="max-width: 440px; line-height: 1.5;">
                        Complete your purchase seamlessly using UPI (GPay, PhonePe), Cards (Visa, Mastercard, RuPay), Netbanking, or digital Wallets via 100% secure PCI-DSS compliant processing.
                    </p>
                </div>
                @endif

                {{-- Billing Prefilled Form Info --}}
                <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2" style="font-size:0.9rem; font-family:'Outfit',sans-serif;">
                    <i class="bi bi-person-bounding-box text-primary"></i> Subscriber Billing Information
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="vp-form-group">
                            <label class="vp-label text-muted small mb-1">Subscriber Name</label>
                            <input type="text" class="vp-input w-100 bg-light text-muted" style="border-color:#E2E8F0; opacity: 0.8;" value="{{ auth()->user()->name }}" readonly disabled>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="vp-form-group">
                            <label class="vp-label text-muted small mb-1">Email Address</label>
                            <input type="email" class="vp-input w-100 bg-light text-muted" style="border-color:#E2E8F0; opacity: 0.8;" value="{{ auth()->user()->email }}" readonly disabled>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="vp-form-group">
                            <label class="vp-label text-muted small mb-1">Phone Contact</label>
                            <input type="text" class="vp-input w-100 bg-light text-muted" style="border-color:#E2E8F0; opacity: 0.8;" value="{{ auth()->user()->subscriberProfile->phone ?? 'Not Provided' }}" readonly disabled>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="vp-form-group">
                            <label class="vp-label text-muted small mb-1">Company / Store Name</label>
                            <input type="text" class="vp-input w-100 bg-light text-muted" style="border-color:#E2E8F0; opacity: 0.8;" value="{{ auth()->user()->subscriberProfile->company_name ?? 'Individual Subscriber' }}" readonly disabled>
                        </div>
                    </div>
                </div>

                {{-- Payment Trust Badge logos --}}
                <div class="border-top pt-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Accepted Payment Instruments</span>
                        <span class="text-success small fw-semibold d-flex align-items-center gap-1"><i class="bi bi-patch-check-fill"></i> Secure SSL</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center justify-content-center" style="gap: 16px;">
                        <!-- Visa Badge -->
                        <div class="payment-badge border d-flex align-items-center justify-content-center px-3 py-2.5 bg-white shadow-sm" style="cursor: default; border-color: #E2E8F0; border-radius: 10px; width: 62px; height: 38px;">
                            <svg viewBox="0 0 24 15" style="width: 32px; height: auto; fill: #1A1F71;" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.7 0h-2.2L6.1 9.4 5.3 1.9C5.1 0.9 4.3 0 3.2 0H0v.4C2 0.9 3.5 1.6 4.3 2.9l2.7 10.6h2.4L13.1 0h-2.4zm8.9 4.9c0-1.8-2.6-1.9-2.6-2.7 0-.3.3-.5.9-.5 1.4 0 2.5.3 3.2.6l.4-1.8C20.8.2 19.5 0 18 0c-2.3 0-3.9 1.2-3.9 2.9 0 2.3 3.2 2.4 3.2 3.7 0 .4-.4.6-1 .6-1.7 0-2.6-.4-3.4-.8l-.4 1.8c.8.4 2.3.7 3.8.7 2.3.1 4-1.1 4-3.1zm4.4-4.9H22c-.6 0-1.1.4-1.3.9L17.5 13.5h2.4l.5-1.3h2.9l.3 1.3h2.1L24 0zM21 9.9l1-.4.6-2.4-.2 2.8H21z"/>
                            </svg>
                        </div>
                        <!-- Mastercard Badge -->
                        <div class="payment-badge border d-flex align-items-center justify-content-center px-3 py-2.5 bg-white shadow-sm" style="cursor: default; border-color: #E2E8F0; border-radius: 10px; width: 62px; height: 38px;">
                            <svg viewBox="0 0 24 18" style="width: 28px; height: auto;" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="7.5" cy="9" r="7.5" fill="#EB001B"/>
                                <circle cx="16.5" cy="9" r="7.5" fill="#FF5F00" fill-opacity="0.85"/>
                            </svg>
                        </div>
                        <!-- UPI Badge -->
                        <div class="payment-badge border d-flex align-items-center justify-content-center px-3 py-2.5 bg-white shadow-sm" style="cursor: default; border-color: #E2E8F0; border-radius: 10px; width: 62px; height: 38px;">
                            <span class="badge bg-primary-soft text-primary px-1.5 py-0.5 rounded fw-extrabold" style="font-size:0.65rem; font-family:'Outfit',sans-serif; background-color: rgba(29, 111, 235, 0.08) !important; letter-spacing: 0.5px;">UPI</span>
                        </div>
                        <!-- Netbanking Badge -->
                        <div class="payment-badge border d-flex align-items-center justify-content-center px-3 py-2.5 bg-white shadow-sm" style="cursor: default; border-color: #E2E8F0; border-radius: 10px; width: 62px; height: 38px;">
                            <i class="bi bi-bank text-secondary fs-6"></i>
                        </div>
                    </div>
                </div>

                {{-- Action Trigger Button --}}
                <div class="mt-4">
                    @if($plan->is_trial)
                    <form action="{{ route('subscriber.subscription.pay', $plan->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="gateway" value="free_trial">
                        <button type="submit" class="btn btn-success w-100 py-3.5 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2" style="background:#10B981; border:none; border-radius:12px; font-size:1.05rem; font-family:'Outfit', sans-serif; transition:all 0.3s;">
                            <i class="bi bi-gift-fill"></i>
                            Activate Free Trial • {{ $plan->trial_days }} Days
                        </button>
                    </form>
                    @else
                    <button type="button" class="btn btn-primary w-100 py-3.5 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2" id="btn-razorpay-trigger" style="background:#1D6FEB; border:none; border-radius:12px; font-size:1.05rem; font-family:'Outfit', sans-serif; transition:all 0.3s; transform:translateY(0);" {{ $isNotConfigured ? 'disabled' : '' }}>
                        @if(isset($isUpgrade) && $isUpgrade)
                            <i class="bi bi-arrow-up-circle-fill"></i>
                            <span id="btn-text">Upgrade Plan — Pay ₹{{ number_format($totalAmount, 2) }} Today</span>
                        @else
                            <i class="bi bi-shield-lock-fill"></i>
                            <span id="btn-text">Proceed to Secure Payment • ₹{{ number_format($totalAmount, 2) }}</span>
                        @endif
                    </button>
                    
                    @if($isNotConfigured)
                    <p class="text-danger small text-center mt-2 mb-0 fw-semibold"><i class="bi bi-exclamation-triangle"></i> Configure .env keys to enable the payment button.</p>
                    @else
                    <div class="text-center mt-2.5">
                        @if($isTestMode)
                            <small class="text-warning fw-semibold d-inline-flex align-items-center gap-1.5 px-3 py-1 rounded-pill" style="background: rgba(245, 158, 11, 0.06); font-size: 0.8rem; border: 1px solid rgba(245, 158, 11, 0.15);">
                                <span class="spinner-grow spinner-grow-sm text-warning" role="status" style="width:0.55rem; height:0.55rem;"></span>
                                Sandbox Active: <code>{{ substr($rzpKey, 0, 12) }}...</code> (Test Key)
                            </small>
                        @else
                            <small class="text-success fw-semibold d-inline-flex align-items-center gap-1.5 px-3 py-1 rounded-pill" style="background: rgba(16, 185, 129, 0.06); font-size: 0.8rem; border: 1px solid rgba(16, 185, 129, 0.15);">
                                <span class="position-relative d-flex" style="width: 8px; height: 8px;">
                                  <span class="animate-ping position-absolute inline-flex h-full w-full rounded-full bg-success opacity-75" style="animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;"></span>
                                  <span class="position-relative inline-flex rounded-full h-2 w-2 bg-success"></span>
                                </span>
                                Live mode active: Production key configured
                            </small>
                        @endif
                    </div>
                    @endif
                    @endif
                </div>

                {{-- Hidden Form for Razorpay Submit (using relative action to avoid port redirects) --}}
                <form action="{{ route('subscriber.subscription.razorpay.verify', $plan->id, false) }}" method="POST" id="razorpay-hidden-form">
                    @csrf
                    <input type="hidden" name="razorpay_payment_id" id="razorpay-payment-id">
                    <input type="hidden" name="razorpay_order_id" id="razorpay-order-id">
                    <input type="hidden" name="razorpay_signature" id="razorpay-signature">
                </form>
            </div>
        </div>
    </div>

    {{-- Order Summary Panel --}}
    <div class="col-lg-5">
        <div class="vp-card" style="border-radius:16px; border: 1px solid #E2E8F0; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05);">
            <div class="vp-card-header p-4 border-bottom">
                <h5 class="vp-card-title m-0 fw-bold" style="font-family:'Outfit',sans-serif; color:var(--text-primary);">Order Summary</h5>
            </div>
            
            <div class="vp-card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h6 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif; font-size:1.1rem; color:var(--text-primary);">{{ $plan->name }} Plan</h6>
                        <span class="badge bg-primary-soft text-primary" style="font-size:0.75rem; background-color: rgba(29, 111, 235, 0.08) !important; border-radius: 4px; padding: 4px 8px;">
                            <i class="bi bi-calendar3 me-1"></i> {{ $plan->duration_days }} Days Validity
                        </span>
                    </div>
                    <span class="fw-bold text-dark fs-4" style="font-family:'Outfit',sans-serif;">₹{{ number_format($plan->price, 2) }}</span>
                </div>
                
                <hr class="my-4" style="border-color:#E2E8F0;">

                {{-- Prorata Breakdown Lines --}}
                @if(isset($prorata) && $prorata)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted" style="font-size:0.88rem;">New Plan Full Price</span>
                        <span class="text-dark fw-bold" style="font-size:0.88rem;">₹{{ number_format($prorata['new_plan_price'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted" style="font-size:0.88rem;">
                            Prorata Credit
                            <span class="badge bg-success rounded-pill ms-1" style="font-size:0.65rem;">{{ $prorata['remaining_days'] }} days unused</span>
                        </span>
                        <span class="text-success fw-bold" style="font-size:0.88rem;">− ₹{{ number_format($prorata['unused_credit'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-dark fw-semibold" style="font-size:0.88rem;">Upgrade Amount</span>
                        <span class="text-dark fw-bold" style="font-size:0.88rem;">₹{{ number_format($prorata['prorata_amount'], 2) }}</span>
                    </div>
                @else
                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                        <span class="text-muted" style="font-size:0.88rem;">Plan Subscription Fee</span>
                        <span class="text-dark fw-bold" style="font-size:0.88rem;">₹{{ number_format($plan->price, 2) }}</span>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted" style="font-size:0.88rem;">GST (18%)</span>
                    <span class="text-{{ $hasGst ? 'danger' : 'success' }} fw-bold" style="font-size:0.88rem;">₹{{ number_format($gstAmount, 2) }}</span>
                </div>

                <hr class="my-4" style="border-color:#E2E8F0;">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold fs-5" style="color:var(--text-primary); font-family:'Outfit',sans-serif;">
                        {{ (isset($isUpgrade) && $isUpgrade) ? 'Upgrade Amount Due' : 'Total Amount Due' }}
                    </span>
                    <span class="fw-bold fs-3 text-primary" style="font-family:'Outfit',sans-serif;">₹{{ number_format($totalAmount, 2) }}</span>
                </div>

                {{-- Prorata saving callout --}}
                @if(isset($prorata) && $prorata && $prorata['unused_credit'] > 0)
                <div class="p-3 rounded-3 mb-4 d-flex align-items-center gap-2" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <i class="bi bi-piggy-bank-fill text-success fs-5"></i>
                    <div>
                        <div class="fw-bold text-success small">You saved ₹{{ number_format($prorata['unused_credit'], 2) }}!</div>
                        <div class="text-muted" style="font-size:0.75rem;">Prorata credit from your remaining {{ $prorata['remaining_days'] }} days was applied.</div>
                    </div>
                </div>
                @endif

                <div class="p-4 rounded-4" style="font-size:0.82rem; color:#475569; border: 1px solid #E2E8F0; background-color: rgba(0,0,0,0.01);">
                    <div class="fw-bold mb-3 text-dark" style="font-size:0.88rem; font-family:'Outfit',sans-serif;"><i class="bi bi-check2-circle text-success me-1"></i> Included Plan Privileges:</div>
                    <div class="d-flex align-items-center gap-2.5 mb-2.5">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span>Upload up to <strong>{{ $plan->product_limit == -1 ? 'Unlimited' : $plan->product_limit }}</strong> Catalog Products</span>
                    </div>
                    <div class="d-flex align-items-center gap-2.5 mb-2.5">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span>Store Access: <strong>{{ $plan->slug === 'enterprise' ? 'Custom Domain' : 'Subdomain Path' }}</strong></span>
                    </div>
                    <div class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span>White-labeled storefronts: <strong>{{ $plan->slug === 'enterprise' ? 'Yes' : 'No' }}</strong></span>
                    </div>
                </div>

                <div class="text-center mt-4 text-muted small d-flex justify-content-center align-items-center gap-2">
                    <i class="bi bi-lock-fill text-muted"></i>
                    <span>Secure SSL 256-bit Encrypted Transaction</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Razorpay Checkout script --}}
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const triggerBtn = document.getElementById('btn-razorpay-trigger');
        const btnText = document.getElementById('btn-text');
        const overlay = document.getElementById('payment-overlay');
        const overlayTitle = document.getElementById('overlay-title');
        const overlayDesc = document.getElementById('overlay-desc');

        if (!triggerBtn) return;

        @php
            $isUpgradeJs = isset($isUpgrade) && $isUpgrade ? 'true' : 'false';
            $btnLabel = (isset($isUpgrade) && $isUpgrade)
                ? "Upgrade Plan — Pay ₹" . number_format($totalAmount, 2) . " Today"
                : "Proceed to Secure Payment • ₹" . number_format($totalAmount, 2);
        @endphp

        triggerBtn.addEventListener('click', async function() {
            triggerBtn.disabled = true;
            btnText.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Initializing Gateway...`;
            
            overlay.classList.remove('d-none');
            overlay.style.display = 'flex';
            overlayTitle.innerText = "Initiating Secure Gateway...";
            overlayDesc.innerText = "Connecting with Razorpay servers. Please do not refresh.";

            try {
                const response = await fetch("{{ route('subscriber.subscription.razorpay.order', $plan->id, false) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Server failed to initialize transaction.');
                }

                // Handle prorata-free upgrade (credit covers full cost)
                if (data.prorata_free) {
                    overlayTitle.innerText = "Processing Prorata Upgrade...";
                    overlayDesc.innerText = "Your unused credit covers the full upgrade cost. Activating now...";
                    // Submit the free upgrade form
                    const freeForm = document.createElement('form');
                    freeForm.method = 'POST';
                    freeForm.action = "{{ route('subscriber.subscription.pay', $plan->id, false) }}";
                    freeForm.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="gateway" value="prorata_free">`;
                    document.body.appendChild(freeForm);
                    freeForm.submit();
                    return;
                }

                overlay.classList.add('d-none');

                const isUpgrade = {{ $isUpgradeJs }};
                const options = {
                    "key": data.key,
                    "amount": data.amount,
                    "currency": data.currency,
                    "name": "Catasky B2B Platform",
                    "description": isUpgrade ? "Upgrade to {{ $plan->name }} Plan (Prorata)" : "{{ $plan->name }} Subscription Plan",
                    "image": "https://razorpay.com/favicon.png",
                    "order_id": data.id,
                    "handler": function (rzpResponse) {
                        overlay.classList.remove('d-none');
                        overlay.style.display = 'flex';
                        overlayTitle.innerText = "Verifying Signature...";
                        overlayDesc.innerText = "Confirming security hashes and activating subscription. Please wait.";

                        document.getElementById('razorpay-payment-id').value = rzpResponse.razorpay_payment_id;
                        document.getElementById('razorpay-order-id').value = rzpResponse.razorpay_order_id;
                        document.getElementById('razorpay-signature').value = rzpResponse.razorpay_signature;
                        document.getElementById('razorpay-hidden-form').submit();
                    },
                    "prefill": {
                        "name": data.user.name,
                        "email": data.user.email,
                        "contact": data.user.phone
                    },
                    "theme": { "color": "#1D6FEB" },
                    "modal": {
                        "ondismiss": function() {
                            triggerBtn.disabled = false;
                            btnText.innerHTML = `{{ $isUpgradeJs === 'true' ? '<i class="bi bi-arrow-up-circle-fill"></i> Upgrade Plan — Pay ₹' . number_format($totalAmount, 2) . ' Today' : '<i class="bi bi-shield-lock-fill"></i> Proceed to Secure Payment • ₹' . number_format($totalAmount, 2) }}`;
                            overlay.classList.add('d-none');
                            if (typeof alertService !== 'undefined') {
                                alertService.errorAlert('Cancelled', 'Payment was closed. You can retry anytime.');
                            } else {
                                alert('Payment was cancelled by the user.');
                            }
                        }
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();

            } catch (error) {
                console.error(error);
                triggerBtn.disabled = false;
                btnText.innerHTML = `{{ $isUpgradeJs === 'true' ? 'Upgrade Plan — Pay ₹' . number_format($totalAmount, 2) . ' Today' : '<i class="bi bi-shield-lock-fill"></i> Proceed to Secure Payment • ₹' . number_format($plan->price, 2) }}`;
                overlay.classList.add('d-none');

                if (typeof alertService !== 'undefined') {
                    alertService.errorAlert('Initialization Failed', error.message);
                } else {
                    alert('Error: ' + error.message);
                }
            }
        });
    });
</script>

<style>
    .bg-primary-soft {
        background-color: rgba(29, 111, 235, 0.08) !important;
    }
    .bg-warning-soft {
        background-color: rgba(245, 158, 11, 0.08) !important;
    }
    .bg-success-soft {
        background-color: rgba(16, 185, 129, 0.08) !important;
    }
    
    #btn-razorpay-trigger:hover {
        background: #1557bf !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(29, 111, 235, 0.2) !important;
    }
    
    #btn-razorpay-trigger:active {
        transform: translateY(0);
    }

    .payment-badge {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1.5px solid #F1F5F9 !important;
    }
    .payment-badge:hover {
        transform: translateY(-2px);
        border-color: #1D6FEB !important;
        background-color: rgba(29, 111, 235, 0.02) !important;
        box-shadow: 0 6px 15px -3px rgba(29, 111, 235, 0.12) !important;
    }

    @keyframes ping {
        75%, 100% {
            transform: scale(2);
            opacity: 0;
        }
    }
</style>
@endsection
