@extends('layouts.frontend')

@section('title', 'Privacy Policy - Catasky')

@section('content')
<!-- Custom Modern Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<div class="policy-page-wrapper">
    <div class="container py-5 mt-4">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4 animate-fade-in">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="breadcrumb-link"><i class="bi bi-house-door me-1"></i> Home</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Privacy Policy</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="text-center mb-5 animate-fade-in">
            <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold mb-3 small-text header-badge">
                <i class="bi bi-shield-lock-fill me-1"></i> Data Protection & Security
            </div>
            <h1 class="display-4 fw-extrabold text-gradient mb-3" style="letter-spacing: -1.5px; font-family: 'Outfit', sans-serif;">Privacy Policy</h1>
            <p class="text-secondary mx-auto subtitle-text" style="max-width: 600px; font-size: 1.1rem; line-height: 1.6;">
                How Catasky collects, processes, and protects your business catalog data and customer analytics.
            </p>
        </div>

        <!-- Main Content -->
        <div class="row justify-content-center">
            <div class="col-lg-10 col-12 animate-fade-in">
                <div class="premium-glass-card p-4 p-md-5 border rounded-4 position-relative overflow-hidden mb-5">
                    <div class="card-bg-glow"></div>
                    <div class="position-relative z-index-1 text-dark" style="line-height: 1.8; font-size: 0.95rem;">
                        <p class="text-muted small">Last Updated: June 6, 2026</p>
                        
                        <p>Welcome to <strong>Catasky</strong> ("we," "our," or "us"), a B2B sales acceleration and digital cataloging platform operated by <strong>Sarthak</strong>. We respect your privacy and are committed to protecting the personal and business data you share with us when using our services, website, or mobile integrations.</p>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">1. Information We Collect</h4>
                        <p>We collect information to provide a highly optimized, interactive, and personalized catalog experience for your business:</p>
                        <ul>
                            <li><strong>Account & Profile Information:</strong> When you register as a subscriber, we collect your name, corporate email address, business phone/WhatsApp number, company name, logo, physical address, and billing details.</li>
                            <li><strong>Catalog & Product Content:</strong> We store product names, specifications, descriptions, prices, custom attributes, and product images/gallery files uploaded to your Catasky account to build and serve your catalogs.</li>
                            <li><strong>Visitor Analytics & Engagement Data:</strong> We track visitor interactions on shared catalog links (heartbeats, page views, PDF downloads, product clicks, and session durations) to provide analytics reports in your sales dashboard.</li>
                            <li><strong>Device & Log Data:</strong> Technical logs, including IP addresses, browser types, operating systems, and device identifiers are collected for security, monitoring, and error-prevention purposes.</li>
                        </ul>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">2. How We Use Your Information</h4>
                        <p>We process collected data to run the platform and optimize your commercial dispatches:</p>
                        <ul>
                            <li>To provision, maintain, and secure your digital storefronts and catalog sharing capabilities.</li>
                            <li>To generate professional PDF catalogs and Visual Flyers with customized branding overlay (watermarks, notes).</li>
                            <li>To supply detailed real-time visitor tracking metrics (e.g. tracking when catalog items are opened by buyers).</li>
                            <li>To process billing, manage subscription renewals, and handle pro-rata credits or refunds.</li>
                            <li>To contact you with updates, security alerts, and system notifications.</li>
                        </ul>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">3. Data Sharing & Third-Party Services</h4>
                        <p>We do not sell your personal or business data. We share information only in the following scenarios:</p>
                        <ul>
                            <li><strong>Buyers & Recipients:</strong> When you share a catalog link, the product contents, pricing, and contact details you choose to publish are viewable by any recipient holding the link.</li>
                            <li><strong>Service Providers:</strong> We utilize third-party integrations (e.g., Razorpay for payment processing, DoubleTick for WhatsApp dispatches, and secure cloud storage) to deliver specific platform capabilities under strict data processing agreements.</li>
                            <li><strong>Legal Compliance:</strong> We may disclose data if required to do so by applicable laws or valid regulatory demands.</li>
                        </ul>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">4. Data Security</h4>
                        <p>We deploy robust industry-standard safeguards (SSL encryption, secure cloud storage, and automated firewall protections) to prevent unauthorized access, alteration, or disclosure of your account records. However, no transmission method over the Internet is 100% secure, and we cannot guarantee absolute absolute security.</p>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">5. Cookies and Web Tracking</h4>
                        <p>We use essential cookies and local browser storage to persist user selection parameters, verify active dashboard sessions, and maintain your preferred catalog settings. You can manage or disable cookies through your browser preferences, though certain interactive platform sections may become unavailable as a result.</p>

                        <h4 class="fw-bold mt-4 mb-3 text-dark border-bottom pb-2" style="font-family: 'Outfit', sans-serif;">6. Contact Details</h4>
                        <p>If you have any questions, concerns, or requests regarding this Privacy Policy, please contact our data compliance office managed by Sarthak:</p>
                        
                        <div class="bg-light-soft p-4 rounded-3 border-start border-primary border-4 mt-3">
                            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-envelope-fill text-primary me-1"></i> Privacy & Compliance Helpdesk</h6>
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
