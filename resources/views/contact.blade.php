@extends('layouts.frontend')

@section('title', 'Contact Corporate Support - Catasky')

@section('content')
<div class="container py-5 mt-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4 animate-fade-in">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-secondary"><i class="bi bi-house-door-fill me-1"></i> Home</a></li>
            <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Contact Us</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="text-center mb-5 animate-fade-in">
        <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold mb-3 small-text">
            <i class="bi bi-shield-check"></i> Verified Dispatch Office
        </div>
        <h1 class="display-4 fw-bold text-dark" style="letter-spacing: -1.5px;">Get in Touch</h1>
        <p class="text-secondary mx-auto" style="max-width: 600px; font-size: 1.05rem;">
            Have queries regarding apparel pricing, dynamic custom logos, or B2B volume orders? Contact our dispatch office directly.
        </p>
    </div>

    <!-- Main Grid -->
    <div class="row g-4 justify-content-center">
        <!-- Contact Details Card Column -->
        <div class="col-lg-5 col-12 order-2 order-lg-1 animate-fade-in">
            <div class="premium-card p-4 p-md-5 h-100 bg-white border rounded-4 d-flex flex-column justify-content-between" style="box-shadow: var(--shadow-sm);">
                <div>
                    <h3 class="fw-bold mb-4 text-dark" style="letter-spacing: -0.5px;">Corporate Headquarters</h3>
                    
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="icon-box bg-primary-soft rounded-3 p-3 text-primary">
                            <i class="bi bi-geo-alt-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Office Address</h6>
                            <p class="text-secondary small mb-0">Sector 62, Noida, Uttar Pradesh, 201301, India</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="icon-box bg-success-soft rounded-3 p-3 text-success">
                            <i class="bi bi-telephone-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Phone Helpline</h6>
                            <p class="mb-0"><a href="tel:+91919871376205" class="text-decoration-none fw-bold text-secondary hover-primary-text">+91 91987 137 6205</a></p>
                            <span class="text-secondary small-text">Mon - Sat, 9:00 AM - 6:00 PM IST</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="icon-box bg-info-soft rounded-3 p-3 text-info">
                            <i class="bi bi-envelope-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Email Dispatch</h6>
                            <p class="mb-0"><a href="mailto:support@catasky.com" class="text-decoration-none fw-bold text-secondary hover-primary-text">support@catasky.com</a></p>
                            <span class="text-secondary small-text">B2B proposals: sales@catasky.com</span>
                        </div>
                    </div>
                </div>

                <div class="bg-light p-3 rounded-3 border-start border-primary border-4 mt-4">
                    <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-clock-fill text-primary"></i> Fast Turnaround Guarantee</h6>
                    <p class="text-secondary small mb-0">Our sales engineering team typically replies to catalog and sample requests within 2 hours during active business shifts.</p>
                </div>
            </div>
        </div>

        <!-- Glassmorphic Interactive Form Card Column -->
        <div class="col-lg-7 col-12 order-1 order-lg-2 animate-fade-in">
            <div class="premium-card p-4 p-md-5 bg-white border rounded-4" style="box-shadow: var(--shadow-md);">
                <h3 class="fw-bold mb-2 text-dark" style="letter-spacing: -0.5px;">Log an Inquiry</h3>
                <p class="text-secondary small mb-4">Provide your procurement specs below to automatically sync with our sales dashboard.</p>

                <!-- Success Alert -->
                @if(session('success'))
                    <div class="alert alert-success border-0 rounded-3 p-3 mb-4 shadow-sm animate-fade-in d-flex align-items-center gap-3">
                        <i class="bi bi-check-circle-fill text-success fs-3"></i>
                        <div>
                            <h6 class="fw-bold mb-0 text-success">Submission Received!</h6>
                            <small class="text-secondary">{{ session('success') }}</small>
                        </div>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="alert alert-danger border-0 rounded-3 p-3 mb-4 shadow-sm animate-fade-in">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('enquiry.submit') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Full Name *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border border-end-0 text-secondary"><i class="bi bi-person-fill"></i></span>
                                <input type="text" name="name" class="form-control border border-start-0 ps-0" placeholder="John Doe" value="{{ old('name') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Corporate Email *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border border-end-0 text-secondary"><i class="bi bi-envelope-fill"></i></span>
                                <input type="email" name="email" class="form-control border border-start-0 ps-0" placeholder="john@company.com" value="{{ old('email') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Phone Number *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border border-end-0 text-secondary"><i class="bi bi-telephone-fill"></i></span>
                                <input type="text" name="phone" class="form-control border border-start-0 ps-0" placeholder="+91 98765 43210" value="{{ old('phone') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Inquiry Subject</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border border-end-0 text-secondary"><i class="bi bi-chat-left-dots-fill"></i></span>
                                <input type="text" name="subject" class="form-control border border-start-0 ps-0" placeholder="Bulk Catalogue Pricing" value="{{ old('subject') }}">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Detailed Specifications *</label>
                            <textarea name="message" class="form-control border p-3" rows="5" placeholder="Specify order quantity, brand preferences, target customization logos, or sizing rules..." required>{{ old('message') }}</textarea>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-premium btn-premium-primary w-100 py-3 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-send-fill text-white"></i> Send Corporate Enquiry
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft {
        background-color: rgba(79, 70, 229, 0.08) !important;
    }
    .bg-success-soft {
        background-color: rgba(16, 185, 129, 0.08) !important;
    }
    .bg-info-soft {
        background-color: rgba(6, 182, 212, 0.08) !important;
    }
    .hover-primary-text {
        transition: color 0.2s ease;
    }
    .hover-primary-text:hover {
        color: var(--primary) !important;
    }
    .form-control:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.1) !important;
    }
    .input-group-text {
        border-color: #dee2e6;
    }
</style>
@endsection
