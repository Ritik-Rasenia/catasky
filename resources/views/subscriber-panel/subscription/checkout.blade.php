@extends('subscriber-panel.layouts.app')

@section('title', 'Secure Checkout')

@section('content')
<div class="vp-page-header">
    <div>
        <h1 class="vp-page-title">Secure Checkout</h1>
        <div class="vp-breadcrumb">
            <a href="{{ route('subscriber.dashboard') }}">Dashboard</a> &nbsp;/&nbsp; <a href="{{ route('subscriber.subscription.index') }}">Subscription</a> &nbsp;/&nbsp; <a href="{{ route('subscriber.subscription.plans') }}">Plans</a> &nbsp;/&nbsp; <span>Checkout</span>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Secure Multi-Gateway Payment Card --}}
    <div class="col-lg-7">
        <div class="vp-card">
            <div class="vp-card-header d-flex align-items-center justify-content-between">
                <h5 class="vp-card-title"><i class="bi bi-shield-lock-fill text-success me-2"></i>Select Payment Method</h5>
                <span class="badge bg-warning-soft text-warning fw-bold px-3 py-1.5" style="font-size:0.75rem; border-radius:30px;"><i class="bi bi-gear-fill me-1"></i> Sandbox Mode</span>
            </div>
            
            <div class="vp-card-body p-0">
                {{-- Payment Gateway Tabs Header --}}
                <ul class="nav nav-tabs border-bottom" id="paymentTabs" role="tablist" style="background: rgba(0,0,0,0.02);">
                    <li class="nav-item" role="presentation" style="flex: 1;">
                        <button class="nav-link active w-100 py-3 text-center border-0 fw-bold d-flex align-items-center justify-content-center gap-2" id="razorpay-tab" data-bs-toggle="tab" data-bs-target="#razorpay-content" type="button" role="tab" style="color: var(--text-primary); border-bottom: 3px solid #1D6FEB !important; border-radius: 0;">
                            <img src="https://razorpay.com/favicon.png" style="width: 18px; height: 18px; object-fit: contain;" alt="Razorpay">
                            Razorpay
                        </button>
                    </li>
                    <li class="nav-item" role="presentation" style="flex: 1;">
                        <button class="nav-link w-100 py-3 text-center border-0 fw-bold d-flex align-items-center justify-content-center gap-2" id="stripe-tab" data-bs-toggle="tab" data-bs-target="#stripe-content" type="button" role="tab" style="color: var(--text-muted); border-bottom: 3px solid transparent !important; border-radius: 0;">
                            <i class="bi bi-stripe text-primary" style="font-size:1.1rem;"></i>
                            Stripe
                        </button>
                    </li>
                    <li class="nav-item" role="presentation" style="flex: 1;">
                        <button class="nav-link w-100 py-3 text-center border-0 fw-bold d-flex align-items-center justify-content-center gap-2" id="paypal-tab" data-bs-toggle="tab" data-bs-target="#paypal-content" type="button" role="tab" style="color: var(--text-muted); border-bottom: 3px solid transparent !important; border-radius: 0;">
                            <i class="bi bi-paypal text-primary" style="font-size:1rem;"></i>
                            PayPal
                        </button>
                    </li>
                </ul>

                {{-- Payment Forms Content --}}
                <div class="tab-content p-4" id="paymentTabsContent">
                    
                    {{-- RAZORPAY TAB --}}
                    <div class="tab-pane fade show active" id="razorpay-content" role="tabpanel">
                        <div class="text-center py-4">
                            <div class="mx-auto mb-3" style="width: 70px; height: 70px; background: rgba(29, 111, 235, 0.08); border-radius: 50%; display:flex; align-items:center; justify-content:center;">
                                <i class="bi bi-credit-card-2-front-fill text-primary" style="font-size:2rem;"></i>
                            </div>
                            <h5 class="fw-bold mb-2 text-dark">Instant Checkout via Razorpay</h5>
                            <p class="text-muted small mx-auto" style="max-width: 400px; line-height: 1.6;">
                                Pay securely using Credit/Debit Cards, UPI, Netbanking, or Wallets. Razorpay offers frictionless sandbox testing for instant subscription activation.
                            </p>

                            <div class="p-3 bg-light rounded-3 mb-4 text-start border d-flex gap-3">
                                <i class="bi bi-info-circle-fill text-primary fs-4 mt-1"></i>
                                <div style="font-size: 0.8rem; color:#475569;">
                                    <strong>Sandbox Simulator Instructions:</strong> Click the button below to invoke the simulated Razorpay.js overlay widget. You can choose to trigger success or failure to test Catasky's SaaS approval gates.
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary py-3 px-5 fw-bold text-white " id="btn-razorpay-trigger" style="background:#1D6FEB; border:none; border-radius:10px;">
                                <i class="bi bi-lightning-charge-fill me-2"></i> Pay ₹{{ number_format($plan->price, 2) }} with Razorpay
                            </button>
                        </div>

                        {{-- Hidden Form for Razorpay Submit --}}
                        <form action="{{ route('subscriber.subscription.pay', $plan->id) }}" method="POST" id="razorpay-hidden-form">
                            @csrf
                            <input type="hidden" name="gateway" value="razorpay">
                            <input type="hidden" name="gateway_payment_id" id="razorpay-payment-id">
                            <input type="hidden" name="gateway_order_id" id="razorpay-order-id">
                        </form>
                    </div>

                    {{-- STRIPE TAB --}}
                    <div class="tab-pane fade" id="stripe-content" role="tabpanel">
                        <form action="{{ route('subscriber.subscription.pay', $plan->id) }}" method="POST" id="stripe-checkout-form">
                            @csrf
                            <input type="hidden" name="gateway" value="stripe">
                            
                            <div class="vp-form-group mb-3">
                                <label class="vp-label">Cardholder Name</label>
                                <input type="text" name="card_name" placeholder="John Doe" class="vp-input" value="{{ auth()->user()->name }}" required>
                            </div>

                            <div class="vp-form-group mb-3">
                                <label class="vp-label">Card Number</label>
                                <div class="position-relative">
                                    <input type="text" name="card_number" id="stripe_card_input" placeholder="4242 4242 4242 4242" class="vp-input" maxlength="19" required>
                                    <i class="bi bi-credit-card position-absolute" style="right: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size:1.1rem;"></i>
                                </div>
                                <span class="text-muted" style="font-size:0.75rem;">Stripe Sandbox: Use any valid expiry and CVV. Try <strong>4242 4242 4242 4242</strong></span>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="vp-form-group mb-4">
                                        <label class="vp-label">Expiration Date</label>
                                        <input type="text" placeholder="MM / YY" class="vp-input" maxlength="7" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="vp-form-group mb-4">
                                        <label class="vp-label">CVV / CVC Code</label>
                                        <input type="password" placeholder="•••" class="vp-input" maxlength="4" required>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-subscriber w-100 justify-content-center py-3 fs-6">
                                <i class="bi bi-shield-lock-fill me-1"></i> Pay ₹{{ number_format($plan->price, 2) }} via Stripe Sandbox
                            </button>
                        </form>
                    </div>

                    {{-- PAYPAL TAB --}}
                    <div class="tab-pane fade" id="paypal-content" role="tabpanel">
                        <form action="{{ route('subscriber.subscription.pay', $plan->id) }}" method="POST" id="paypal-checkout-form">
                            @csrf
                            <input type="hidden" name="gateway" value="paypal">

                            <div class="text-center py-3 mb-4">
                                <div class="mx-auto mb-3" style="width: 50px; height: 50px; background: rgba(0, 121, 193, 0.08); border-radius: 50%; display:flex; align-items:center; justify-content:center;">
                                    <i class="bi bi-paypal text-primary" style="font-size:1.5rem;"></i>
                                </div>
                                <h6 class="fw-bold text-dark">Simulate PayPal Express Checkout</h6>
                                <p class="text-muted small mx-auto" style="max-width: 320px;">
                                    Connect your simulated PayPal sandbox account or checkout instantly using guest debit cards.
                                </p>
                            </div>

                            <div class="vp-form-group mb-4">
                                <label class="vp-label">PayPal Sandbox Email Address</label>
                                <input type="email" name="paypal_email" placeholder="buyer-sandbox@catasky.com" class="vp-input" value="{{ auth()->user()->email }}" required>
                                <span class="text-muted" style="font-size:0.75rem;">Enter any dummy email for sandbox payment logs.</span>
                            </div>

                            <button type="submit" class="btn btn-warning w-100 justify-content-center py-3 fs-6 fw-bold text-dark border-0" style="background:#FFC439; border-radius:8px;">
                                <i class="bi bi-paypal me-2"></i> Pay with PayPal Sandbox
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Order Summary Panel --}}
    <div class="col-lg-5">
        <div class="vp-card">
            <div class="vp-card-header">
                <h5 class="vp-card-title">Order Summary</h5>
            </div>
            
            <div class="vp-card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif; font-size:1.05rem; color:var(--text-primary);">{{ $plan->name }} Plan</h6>
                        <span class="badge bg-primary-soft text-primary" style="font-size:0.72rem;">{{ $plan->duration_days }} Days Validity</span>
                    </div>
                    <span class="fw-bold text-dark fs-5">₹{{ number_format($plan->price, 2) }}</span>
                </div>
                
                <hr class="my-3" style="border-color:#E2E8F0;">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted" style="font-size:0.85rem;">Plan Price</span>
                    <span class="text-dark fw-bold" style="font-size:0.85rem;">₹{{ number_format($plan->price, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted" style="font-size:0.85rem;">Taxes & Admin Fees</span>
                    <span class="text-success fw-bold" style="font-size:0.85rem;">₹0.00</span>
                </div>

                <hr class="my-3" style="border-color:#E2E8F0;">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold fs-5" style="color:var(--text-primary);">Total Amount Due</span>
                    <span class="fw-bold fs-4 text-primary" style="font-family:'Outfit',sans-serif;">₹{{ number_format($plan->price, 2) }}</span>
                </div>

                <div class="p-3 rounded-3 bg-light" style="font-size:0.82rem; color:#475569; border: 1px solid #E2E8F0;">
                    <div class="fw-bold mb-2 text-dark" style="font-size:0.85rem;"><i class="bi bi-check2-circle text-success me-1"></i> Included Plan Features:</div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-shield-check text-success"></i>
                        <span>Upload up to <strong>{{ $plan->product_limit == -1 ? 'Unlimited' : $plan->product_limit }}</strong> Products</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-shield-check text-success"></i>
                        <span>Custom store: <strong>{{ $plan->slug === 'enterprise' ? 'Custom Domain' : 'Subdomain/Path' }}</strong></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success"></i>
                        <span>White-label: <strong>{{ $plan->slug === 'enterprise' ? 'Yes' : 'No' }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Razorpay Simulation Overlay Modal --}}
<div class="modal fade" id="razorpaySimModal" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(8px); background: rgba(0, 0, 0, 0.4);">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; background:#111b2d; color:#ffffff; font-family:'Outfit', sans-serif;">
            
            {{-- Modal Header --}}
            <div class="p-4 border-bottom border-secondary d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <img src="https://razorpay.com/favicon.png" style="width: 22px; height: 22px; object-fit: contain;">
                    <span style="font-weight: 800; font-size: 1.1rem; letter-spacing: -0.5px; color:#4fa3ff;">Razorpay Sandbox Checkout</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4">
                <div style="background: rgba(255, 255, 255, 0.04); border-radius: 12px; padding: 16px; margin-bottom: 24px; border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Merchant</span>
                        <span class="fw-bold small text-white">Catasky B2B Platform</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Customer</span>
                        <span class="fw-bold small text-white">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary small">Amount</span>
                        <span class="fw-bold text-success">₹{{ number_format($plan->price, 2) }}</span>
                    </div>
                </div>

                <div class="text-center mb-4">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 32px; height: 32px;"></div>
                    <p class="small text-secondary mb-0">Awaiting payment verification challenge from Sandbox Simulator...</p>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex flex-column gap-2">
                    <button type="button" class="btn btn-success py-2.5 fw-bold" id="btn-simulate-success" style="border-radius:10px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Simulate Payment Success
                    </button>
                    <button type="button" class="btn btn-danger py-2.5 fw-bold" id="btn-simulate-fail" style="border-radius:10px;">
                        <i class="bi bi-x-circle-fill me-1"></i> Simulate Payment Failure
                    </button>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="p-3 bg-dark-soft text-center text-muted" style="font-size: 0.68rem; border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                <i class="bi bi-shield-fill-check text-info me-1"></i> Secure Test Environment SSL 256-bit encryption
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab highlight listener
        const tabs = document.querySelectorAll('#paymentTabs button');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                tabs.forEach(t => {
                    t.style.borderBottom = '3px solid transparent';
                    t.style.color = 'var(--text-muted)';
                });
                event.target.style.borderBottom = '3px solid #1D6FEB';
                event.target.style.color = 'var(--text-primary)';
            });
        });

        // Razorpay Modal Integration
        const triggerBtn = document.getElementById('btn-razorpay-trigger');
        const modalEl = document.getElementById('razorpaySimModal');
        const modal = new bootstrap.Modal(modalEl);
        
        triggerBtn.addEventListener('click', function() {
            modal.show();
        });

        // Success Simulation
        document.getElementById('btn-simulate-success').addEventListener('click', function() {
            // Populate simulated fields
            document.getElementById('razorpay-payment-id').value = 'pay_rzp_test_' + Math.random().toString(36).substr(2, 9);
            document.getElementById('razorpay-order-id').value = 'order_rzp_test_' + Math.random().toString(36).substr(2, 9);
            
            modal.hide();
            
            // Show alert
            alertService.successAlert('Payment Successful', '🎉 Razorpay payment verification successfully matched. Submitting registration.');
            
            // Submit form
            document.getElementById('razorpay-hidden-form').submit();
        });

        // Failure Simulation
        document.getElementById('btn-simulate-fail').addEventListener('click', function() {
            modal.hide();
            alertService.errorAlert('Payment Failed', '❌ Razorpay transaction rejected (Simulated Payment Failure). please try again.');
        });

        // Stripe Card Input Formatter
        const cardInput = document.getElementById('stripe_card_input');
        if (cardInput) {
            cardInput.addEventListener('input', function (e) {
                let target = e.target;
                let position = target.selectionEnd;
                let length = target.value.length;
                let digits = target.value.replace(/\D/g, '');
                let formatted = '';
                
                for (let i = 0; i < digits.length; i++) {
                    if (i > 0 && i % 4 === 0) formatted += ' ';
                    formatted += digits[i];
                }
                target.value = formatted;
                if (position < length) {
                    target.setSelectionRange(position, position);
                }
            });
        }
    });
</script>

<style>
    .nav-tabs .nav-link:hover {
        background-color: transparent !important;
        border-color: transparent !important;
    }
    .nav-tabs .nav-link.active {
        background-color: transparent !important;
    }
    .bg-warning-soft {
        background-color: rgba(245, 158, 11, 0.08) !important;
    }
    .bg-primary-soft {
        background-color: rgba(29, 111, 235, 0.08) !important;
    }
    .bg-dark-soft {
        background: rgba(0, 0, 0, 0.15);
    }
</style>
@endsection
