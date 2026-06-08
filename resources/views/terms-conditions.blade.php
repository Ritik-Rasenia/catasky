@extends('layouts.frontend')

@section('title', 'Terms & Conditions - Catasky')

@section('content')
<!-- Custom Modern Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<div class="policy-page-wrapper">
    <div class="container py-5 mt-4">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4 animate-fade-in">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="breadcrumb-link"><i class="bi bi-house-door me-1"></i> Home</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Terms & Conditions</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="text-center mb-5 animate-fade-in">
            <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold mb-3 small-text header-badge">
                <i class="bi bi-file-earmark-text-fill me-1"></i> Platform Guidelines & Agreements
            </div>
            <h1 class="display-4 fw-extrabold text-gradient mb-3" style="letter-spacing: -1.5px; font-family: 'Outfit', sans-serif;">Terms & Conditions</h1>
            <p class="text-secondary mx-auto subtitle-text" style="max-width: 600px; font-size: 1.1rem; line-height: 1.6;">
                Rules, guidelines, and terms of service that govern your use of the Catasky platform.
            </p>
        </div>

        <!-- Main Content -->
        <div class="row justify-content-center">
            <div class="col-lg-10 col-12 animate-fade-in">
                <div class="premium-glass-card p-4 p-md-5 border rounded-4 position-relative overflow-hidden mb-5">
                    <div class="card-bg-glow"></div>
                    <div class="position-relative z-index-1 text-dark" style="line-height: 1.8; font-size: 0.95rem;">
                        <p class="text-muted small">Last Updated: June 6, 2026</p>

                        <p>These Terms & Conditions ("Terms") govern your access to and use of the <strong>Catasky</strong> web application and SaaS cataloging platform ("Platform") operated by <strong>Sarthak</strong>. By creating an account, subscribing to our plans, or sharing catalogs via Catasky, you agree to be bound by these Terms.</p>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">1. Account Registration & Storefront Activation</h4>
                        <ul>
                            <li>You must provide accurate, current, and complete details during registration. You are solely responsible for maintaining the confidentiality of your account credentials.</li>
                            <li>Subscriber storefronts (e.g. `catasky.com/store/your-slug`) and sharing features are subject to manual approval to ensure authenticity and prevent spam. Sarthak reserves the absolute right to approve, reject, or suspend storefront activations.</li>
                        </ul>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">2. Service Usage & Catalog Sharing</h4>
                        <ul>
                            <li>Catasky permits subscribers to compile product catalogs, customize parameters (e.g. MRP, offer prices, notes), and output PDF catalogs or sharing links.</li>
                            <li>You are solely responsible for the products, pricing, and visual assets uploaded to your account. You warrant that you own or hold appropriate licenses for all images and catalogs hosted on the platform.</li>
                            <li>You agree not to use the platform to share prohibited items, counterfeit goods, or offensive material. Violating usage guidelines will result in immediate termination of service without refund.</li>
                        </ul>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">3. Subscription Fees & Payment Processing</h4>
                        <ul>
                            <li>Access to premium features (unlimited products, PDF generation, watermark removal, custom domains) requires an active paid subscription plan.</li>
                            <li>Payments are processed securely via our integration partners (such as Razorpay). Fees are billed in advance on a recurring monthly or yearly billing cycle.</li>
                            <li>We reserve the right to modify subscription pricing with a 30-day prior notice. Continued use of the service constitutes agreement to the updated fees.</li>
                        </ul>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">4. Cancellation & Refund Policy</h4>
                        <p>Our cancellation terms depend on the billing cycle selected during checkout:</p>
                        <ul>
                            <li><strong>Monthly Plans:</strong> Cancellation takes effect at the end of the current billing month. Monthly subscription payments are **non-refundable**.</li>
                            <li><strong>Yearly Plans:</strong> We calculate refunds on a **pro-rata basis**. If you cancel early, we charge for the whole months utilized (rounded up) and issue a refund for the remaining unused months. For example, cancelling after 4.5 months leaves 7.5 months. We round up used time to 5 months and issue a refund for the remaining <strong>7 months</strong>.</li>
                        </ul>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">5. Intellectual Property Rights</h4>
                        <p>All catalog layout templates, PDF compilers, analytics dashboards, software structures, and designs on the Catasky platform are the exclusive intellectual property of Sarthak and our licensors. You are granted a limited, non-exclusive, non-transferable license to utilize the platform for commercial cataloging and business-to-business dispatches during your subscription period.</p>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">6. Limitation of Liability</h4>
                        <p>The Catasky platform is provided on an "as is" and "as available" basis. Sarthak and its operators shall not be liable for any indirect, incidental, special, or consequential damages arising from service downtimes, payment gateway delays, custom domain verification issues, or loss of catalog traffic logs.</p>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">7. Amendments to Terms</h4>
                        <p>We may update these Terms from time to time. When amendments are published, we will update the "Last Updated" date at the top of this page. Your continued use of the platform indicates your acceptance of the revised terms.</p>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">8. Corporate Office & Support Address</h4>
                        <p>For inquiries regarding terms agreement or compliance, please reach out to Sarthak:</p>

                        <div class="bg-light-soft p-4 rounded-3 border-start border-primary border-4 mt-3">
                            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-building text-primary me-1"></i> Corporate Headquarters</h6>
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

    .bg-primary-soft {
        background-color: rgba(79, 70, 229, 0.08) !important;
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
        background: radial-gradient(circle, rgba(79, 70, 229, 0.05) 0%, transparent 70%);
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
