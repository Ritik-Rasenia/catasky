@extends('layouts.frontend')

@section('title', 'Refund & Cancellation Policy - Catasky')

@section('content')
<!-- Custom Modern Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<div class="policy-page-wrapper">
    <div class="container py-5 mt-4">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4 animate-fade-in">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="breadcrumb-link"><i class="bi bi-house-door me-1"></i> Home</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Refund Policy</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="text-center mb-5 animate-fade-in">
            <div class="badge bg-success-soft text-success px-3 py-2 rounded-pill fw-bold mb-3 small-text header-badge">
                <i class="bi bi-cash-stack me-1"></i> Transparent Cancellation
            </div>
            <h1 class="display-4 fw-extrabold text-gradient mb-3" style="letter-spacing: -1.5px; font-family: 'Outfit', sans-serif;">Refund & Cancellation Policy</h1>
            <p class="text-secondary mx-auto subtitle-text" style="max-width: 600px; font-size: 1.1rem; line-height: 1.6;">
                Understand your billing cycles, cancel at any time, and review pro-rata refund calculations.
            </p>
        </div>

        <!-- Main Content -->
        <div class="row justify-content-center">
            <div class="col-lg-10 col-12 animate-fade-in">
                <div class="premium-glass-card p-4 p-md-5 border rounded-4 position-relative overflow-hidden mb-5">
                    <div class="card-bg-glow"></div>
                    <div class="position-relative z-index-1 text-dark" style="line-height: 1.8; font-size: 0.95rem;">
                        <p class="text-muted small">Last Updated: June 6, 2026</p>

                        <p>At <strong>Catasky</strong>, operated by <strong>Sarthak</strong>, we strive to provide a premium B2B cataloging and sales enablement platform. If you find that our service does not meet your commercial requirements, we offer direct subscription cancellation options governed by the terms below.</p>

                        <h4 class="fw-bold mt-4 mb-3 text-danger border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">1. Monthly Subscription Plan</h4>
                        <div class="alert alert-warning border-0 rounded-3 p-3.5 mb-3" style="background: rgba(245, 158, 11, 0.08);">
                            <h6 class="fw-bold text-warning-emphasis mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Strictly Non-Refundable</h6>
                            <p class="mb-0 small text-secondary">Our Monthly Plans are charged on a monthly recurring basis and are <strong>strictly non-refundable</strong>. Upon cancellation, your storefront and cataloging capabilities will remain active until the end of your current paid billing period, and no future payments will be charged.</p>
                        </div>

                        <h4 class="fw-bold mt-4 mb-3 text-success border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">2. Yearly Subscription Plan (Pro-Rata Refund)</h4>
                        <p>Our Yearly Plans represent a 12-month commitment but offer the flexibility of a **pro-rata refund** in the event of early termination. If you cancel your yearly plan before the completion of the 12-month cycle, we will issue a refund for the unused whole months of your subscription.</p>
                        
                        <div class="bg-light-soft p-4 rounded-3 border-start border-success border-4 mt-3 mb-4">
                            <h6 class="fw-bold text-success mb-2"><i class="bi bi-calculator me-1"></i> How Pro-Rata Refund is Calculated:</h6>
                            <p class="mb-2 text-secondary">When calculating the refund, we round up the used billing period to the nearest whole month and refund the remaining unused months of the 12-month plan.</p>
                            <p class="mb-0 text-dark"><strong>Example Scenario:</strong></p>
                            <ul>
                                <li>You subscribe to a 12-month yearly plan.</li>
                                <li>After <strong>4.5 months</strong>, you decide to cancel your subscription.</li>
                                <li>The used time is rounded up to 5 months.</li>
                                <li>You are issued a refund for the remaining <strong>7 months</strong> of your yearly plan.</li>
                            </ul>
                        </div>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">3. Free Trial Period</h4>
                        <p>We offer a free trial period for specific plans (such as our 7-day trial). You will not be charged if you cancel your subscription before the trial period concludes. Once the trial expires and shifts to a monthly or yearly plan, the standard refund terms outlined in Sections 1 and 2 apply.</p>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">4. Abuse of Refund Policy</h4>
                        <p>Refunds are intended to protect clients from purchasing plans they cannot utilize. Sarthak reserves the right to deny refund requests to subscribers who exhibit a pattern of subscribing and cancelling to obtain pro-rata refunds repeatedly.</p>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">5. Initiating a Refund Request</h4>
                        <p>To request a cancellation and claim a pro-rata refund for your yearly plan, please contact Sarthak's support desk using the coordinates below:</p>

                        <div class="bg-light-soft p-4 rounded-3 border-start border-primary border-4 mt-3">
                            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-chat-dots-fill text-primary me-1"></i> Support Desk Contact</h6>
                            <p class="mb-1 text-secondary"><strong>Operator:</strong> Sarthak</p>
                            <p class="mb-1 text-secondary"><strong>Email:</strong> support@catasky.com / sales@catasky.com</p>
                            <p class="mb-0 text-secondary"><strong>Phone:</strong> +91 91987 137 6205</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .policy-page-wrapper {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #FAFCFF;
        background-image: radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.03) 0px, transparent 50%),
                          radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.03) 0px, transparent 50%);
    }

    .fw-extrabold {
        font-weight: 800;
    }

    .text-gradient {
        background: linear-gradient(135deg, #1E1B4B 30%, #4F46E5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .breadcrumb-link {
        color: #64748B;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .breadcrumb-link:hover {
        color: #4F46E5;
    }

    .bg-success-soft {
        background-color: rgba(16, 185, 129, 0.08) !important;
    }
    
    .bg-light-soft {
        background-color: rgba(248, 250, 252, 0.8) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .premium-glass-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        box-shadow: 0 10px 30px -10px rgba(79, 70, 229, 0.05);
    }

    .card-bg-glow {
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
        pointer-events: none;
    }

    .animate-fade-in {
        animation: fadeInUp 0.6s ease forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection
