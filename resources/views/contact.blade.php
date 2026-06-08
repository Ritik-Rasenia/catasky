@extends('layouts.frontend')

@section('title', 'B2B Corporate Support & Dispatch Inquiries - Catasky')

@section('content')
<!-- Custom Modern Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<div class="contact-page-wrapper">
    <div class="container py-5 mt-4">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4 animate-fade-in">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="breadcrumb-link"><i class="bi bi-house-door me-1"></i> Home</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Contact Us</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="text-center mb-5 animate-fade-in">
            <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold mb-3 small-text header-badge ">
                <i class="bi bi-shield-check-fill me-1"></i> Verified Dispatch Office
            </div>
            <h1 class="display-4 fw-extrabold text-gradient mb-3" style="letter-spacing: -1.5px; font-family: 'Outfit', sans-serif;">Get in Touch</h1>
            <p class="text-secondary mx-auto subtitle-text" style="max-width: 600px; font-size: 1.1rem; line-height: 1.6;">
                Have questions regarding apparel pricing, dynamic custom logos, or B2B volume orders? Our corporate dispatch office is ready to assist you.
            </p>
        </div>

        <!-- Main Grid -->
        <div class="row g-4 justify-content-center">
            <!-- Contact Details Card Column -->
            <div class="col-lg-5 col-12 order-2 order-lg-1 animate-fade-in">
                <div class="premium-glass-card p-4 p-md-5 h-100 d-flex flex-column justify-content-between border rounded-4 position-relative overflow-hidden">
                    <div class="card-bg-glow"></div>
                    <div class="position-relative z-index-1">
                        <h3 class="fw-bold mb-4 text-dark card-heading" style="letter-spacing: -0.5px; font-family: 'Outfit', sans-serif;">Corporate Headquarters</h3>
                        
                        <div class="info-item-box d-flex align-items-start gap-3 mb-4">
                            <div class="icon-box bg-primary-soft rounded-3 p-3 text-primary ">
                                <i class="bi bi-geo-alt-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Office Address</h6>
                                <p class="text-secondary small mb-0">Sector 62, Noida, Uttar Pradesh, 201301, India</p>
                            </div>
                        </div>

                        <div class="info-item-box d-flex align-items-start gap-3 mb-4">
                            <div class="icon-box bg-success-soft rounded-3 p-3 text-success ">
                                <i class="bi bi-telephone-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Phone Helpline</h6>
                                <p class="mb-0"><a href="tel:+91919871376205" class="text-decoration-none fw-bold text-secondary hover-primary-text">+91 91987 137 6205</a></p>
                                <span class="text-secondary small-text">Mon - Sat, 9:00 AM - 6:00 PM IST</span>
                            </div>
                        </div>

                        <div class="info-item-box d-flex align-items-start gap-3 mb-4">
                            <div class="icon-box bg-info-soft rounded-3 p-3 text-info ">
                                <i class="bi bi-envelope-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Email Dispatch</h6>
                                <p class="mb-0"><a href="mailto:support@catasky.com" class="text-decoration-none fw-bold text-secondary hover-primary-text">support@catasky.com</a></p>
                                <span class="text-secondary small-text">B2B proposals: sales@catasky.com</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light-soft p-4 rounded-3 border-start border-primary border-4 mt-4 position-relative z-index-1 ">
                        <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Fast Turnaround Guarantee</h6>
                        <p class="text-secondary small mb-0">Our sales engineering team typically replies to catalog and sample requests within 2 hours during active business shifts.</p>
                    </div>
                </div>
            </div>

            <!-- Glassmorphic Interactive Form Card Column -->
            <div class="col-lg-7 col-12 order-1 order-lg-2 animate-fade-in">
                <div class="premium-form-card p-4 p-md-5 border rounded-4 bg-white shadow-lg position-relative overflow-hidden">
                    <div class="form-bg-glow"></div>
                    <div class="position-relative z-index-1">
                        <h3 class="fw-bold mb-2 text-dark card-heading" style="letter-spacing: -0.5px; font-family: 'Outfit', sans-serif;">Log an Inquiry</h3>
                        <p class="text-secondary small mb-4">Provide your procurement specs below to automatically sync with our sales dashboard.</p>

                        <!-- Success Alert -->
                        @if(session('success'))
                            <div class="alert alert-success border-0 rounded-4 p-4 mb-4  animate-fade-in d-flex align-items-center gap-3">
                                <div class="alert-icon-wrapper bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                    <i class="bi bi-check-lg fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-success">Submission Received!</h6>
                                    <small class="text-secondary-soft" style="font-size:0.85rem;">{{ session('success') }}</small>
                                </div>
                            </div>
                        @endif

                        <!-- Validation Errors -->
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 rounded-4 p-4 mb-4  animate-fade-in">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                                    <h6 class="fw-bold mb-0 text-danger">Please resolve the following errors:</h6>
                                </div>
                                <ul class="mb-0 small ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li class="text-secondary-soft mb-1">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('enquiry.submit') }}" method="POST" id="corporate-enquiry-form">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label class="form-label-premium">Full Name *</label>
                                    <div class="input-group-premium">
                                        <span class="input-icon"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" name="name" class="form-control-premium" placeholder="John Doe" value="{{ old('name') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label class="form-label-premium">Corporate Email *</label>
                                    <div class="input-group-premium">
                                        <span class="input-icon"><i class="bi bi-envelope-fill"></i></span>
                                        <input type="email" name="email" class="form-control-premium" placeholder="john@company.com" value="{{ old('email') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label class="form-label-premium">Phone Number *</label>
                                    <div class="input-group-premium">
                                        <span class="input-icon"><i class="bi bi-telephone-fill"></i></span>
                                        <input type="text" name="phone" class="form-control-premium" placeholder="+91 98765 43210" value="{{ old('phone') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label class="form-label-premium">Inquiry Subject</label>
                                    <div class="input-group-premium">
                                        <span class="input-icon"><i class="bi bi-chat-left-dots-fill"></i></span>
                                        <input type="text" name="subject" class="form-control-premium" placeholder="Bulk Catalog Pricing" value="{{ old('subject') }}">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label-premium">Detailed Specifications *</label>
                                    <textarea name="message" class="form-control-premium-textarea" rows="5" placeholder="Specify order quantity, brand preferences, target customization logos, or sizing rules..." required>{{ old('message') }}</textarea>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-premium-send w-100 py-3 rounded-3 shadow-md d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-send-fill text-white"></i> Send Corporate Enquiry
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Map Section -->
        <div class="row mt-5 pt-3 animate-fade-in">
            <div class="col-12">
                <div class="premium-glass-card p-2 border rounded-4 shadow-lg overflow-hidden position-relative" style="min-height: 400px;">
                    <div class="card-bg-glow"></div>
                    <div class="map-container rounded-3 position-relative overflow-hidden border" style="height: 400px; width: 100%;">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3502.404847953289!2d77.37059737528859!3d28.617621075673024!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce561c28c8951%3A0x63cd2d580f4f9f!2sSector%2062%2C%20Noida%2C%20Uttar%20Pradesh!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin" 
                            width="100%" 
                            height="100%" 
                            style="border:0; filter: grayscale(0.2) contrast(1.1) brightness(0.95);" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        <div class="map-overlay">
                            <span class="badge bg-dark rounded-pill py-2 px-3"><i class="bi bi-map-fill me-1"></i> Noida Dispatch Office</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium B2B Fonts */
    .contact-page-wrapper {
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
    .bg-success-soft {
        background-color: rgba(16, 185, 129, 0.08) !important;
    }
    .bg-info-soft {
        background-color: rgba(6, 182, 212, 0.08) !important;
    }
    .bg-light-soft {
        background-color: rgba(248, 250, 252, 0.8) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .hover-primary-text {
        transition: color 0.2s ease;
        color: #475569 !important;
    }
    .hover-primary-text:hover {
        color: #4F46E5 !important;
    }

    /* Premium Glassmorphic Cards */
    .premium-glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        box-shadow: 0 10px 30px -10px rgba(79, 70, 229, 0.05);
        transition: all 0.3s ease;
    }
    .premium-glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -15px rgba(79, 70, 229, 0.1);
        border-color: rgba(79, 70, 229, 0.2) !important;
    }

    .premium-form-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    .premium-form-card:hover {
        box-shadow: 0 25px 50px -15px rgba(79, 70, 229, 0.08);
        border-color: rgba(79, 70, 229, 0.15) !important;
    }

    .card-bg-glow {
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(79, 70, 229, 0.08) 0%, transparent 70%);
        pointer-events: none;
    }

    .form-bg-glow {
        position: absolute;
        bottom: -50px;
        left: -50px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.06) 0%, transparent 70%);
        pointer-events: none;
    }

    /* Form Fields */
    .form-label-premium {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: #64748B;
        margin-bottom: 6px;
        display: block;
    }

    .input-group-premium {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        border-radius: 10px;
        border: 1.5px solid #E2E8F0;
        background-color: #F8FAFC;
        transition: all 0.2s ease;
    }
    .input-group-premium:focus-within {
        border-color: #4F46E5;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .input-icon {
        padding-left: 14px;
        color: #94A3B8;
        display: flex;
        align-items: center;
        font-size: 1rem;
    }

    .form-control-premium {
        width: 100%;
        padding: 12px 14px 12px 10px;
        border: none;
        background: transparent;
        font-size: 0.9rem;
        color: #1E293B;
        font-weight: 500;
        outline: none;
    }
    .form-control-premium::placeholder {
        color: #94A3B8;
    }

    .form-control-premium-textarea {
        width: 100%;
        padding: 14px;
        border: 1.5px solid #E2E8F0;
        border-radius: 10px;
        background-color: #F8FAFC;
        font-size: 0.9rem;
        color: #1E293B;
        font-weight: 500;
        outline: none;
        transition: all 0.2s ease;
    }
    .form-control-premium-textarea:focus {
        border-color: #4F46E5;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }
    .form-control-premium-textarea::placeholder {
        color: #94A3B8;
    }

    /* Send Button */
    .btn-premium-send {
        background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);
        border: none;
        color: white;
        font-weight: 600;
        font-size: 0.95rem;
        letter-spacing: -0.01em;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    .btn-premium-send:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
        background: linear-gradient(135deg, #4338CA 0%, #2E2984 100%);
    }
    .btn-premium-send:active {
        transform: translateY(1px);
    }

    /* Map Overlay */
    .map-overlay {
        position: absolute;
        top: 15px;
        left: 15px;
        pointer-events: none;
        z-index: 10;
    }
    .map-overlay .badge {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        font-size: 0.8rem;
        font-weight: 600;
        background-color: rgba(30, 27, 75, 0.95) !important;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    /* Animations */
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

    @media (max-width: 768px) {
        .display-4 {
            font-size: 2.5rem;
        }
    }
</style>
@endsection
