@extends('layouts.frontend')

@section('title', ($profile->company_name ?? 'Store') . ' — Contact Us')

@section('content')

{{-- Premium Fonts --}}
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<div class="store-contact-wrapper">
    <div class="container py-5 mt-4">

        {{-- Breadcrumbs --}}
        <nav aria-label="breadcrumb" class="mb-4 animate-fade-in">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('subscriber_store', $profile->company_slug) }}" class="breadcrumb-link">
                        <i class="bi bi-shop me-1"></i> {{ $profile->company_name }}
                    </a>
                </li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Contact Us</li>
            </ol>
        </nav>

        {{-- Page Header --}}
        <div class="text-center mb-5 animate-fade-in">
            {{-- Store Logo --}}
            @if($profile->logo)
                <div class="mb-4">
                    <img src="{{ asset('uploads/subscriber-logos/' . $profile->logo) }}"
                         alt="{{ $profile->company_name }}"
                         style="max-height: 72px; max-width: 240px; object-fit: contain;">
                </div>
            @else
                <div class="mx-auto mb-4 d-flex align-items-center justify-content-center fw-bold text-white"
                     style="width:72px;height:72px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:20px;font-size:2rem;font-family:'Outfit',sans-serif;">
                    {{ strtoupper(substr($profile->company_name, 0, 1)) }}
                </div>
            @endif

            <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold mb-3 small header-badge">
                <i class="bi bi-verified-fill me-1"></i> Verified Store Contact
            </div>
            <h1 class="display-5 fw-extrabold text-gradient mb-2" style="letter-spacing: -1px; font-family: 'Outfit', sans-serif;">
                Get in Touch
            </h1>
            <p class="text-secondary mx-auto" style="max-width: 560px; font-size: 1rem; line-height: 1.6;">
                Reach out to <strong>{{ $profile->company_name }}</strong> for product enquiries, pricing, bulk orders, or general support.
            </p>
        </div>

        {{-- Main Grid --}}
        <div class="row g-4 justify-content-center">

            {{-- Contact Details Card --}}
            <div class="col-lg-5 col-12 order-2 order-lg-1 animate-fade-in">
                <div class="premium-glass-card p-4 p-md-5 h-100 d-flex flex-column justify-content-between border rounded-4 position-relative overflow-hidden">
                    <div class="card-bg-glow"></div>
                    <div class="position-relative z-index-1">

                        <h3 class="fw-bold mb-4 text-dark card-heading" style="letter-spacing: -0.5px; font-family: 'Outfit', sans-serif;">
                            {{ $profile->company_name }}
                        </h3>

                        {{-- Address --}}
                        @if($profile->address || $profile->city || $profile->state)
                        <div class="info-item-box d-flex align-items-start gap-3 mb-4">
                            <div class="icon-box bg-primary-soft rounded-3 p-3 text-primary flex-shrink-0">
                                <i class="bi bi-geo-alt-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Business Address</h6>
                                <p class="text-secondary small mb-0">
                                    {{ $profile->address }}
                                    @if($profile->city), {{ $profile->city }}@endif
                                    @if($profile->state), {{ $profile->state }}@endif
                                    @if($profile->pincode) — {{ $profile->pincode }}@endif
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Phone --}}
                        @if($profile->phone || $profile->whatsapp_number)
                        <div class="info-item-box d-flex align-items-start gap-3 mb-4">
                            <div class="icon-box bg-success-soft rounded-3 p-3 text-success flex-shrink-0">
                                <i class="bi bi-telephone-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Phone / WhatsApp</h6>
                                @if($profile->phone)
                                    <p class="mb-1">
                                        <a href="tel:{{ $profile->phone }}" class="text-decoration-none fw-bold text-secondary hover-primary-text">
                                            {{ $profile->phone }}
                                        </a>
                                    </p>
                                @endif
                                @if($profile->whatsapp_number && $profile->whatsapp_number !== $profile->phone)
                                    <p class="mb-0">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile->whatsapp_number) }}" target="_blank" rel="noopener" class="text-decoration-none fw-bold text-success hover-primary-text d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-whatsapp"></i> {{ $profile->whatsapp_number }}
                                        </a>
                                    </p>
                                @elseif($profile->whatsapp_number)
                                    <p class="mb-0">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile->whatsapp_number) }}" target="_blank" rel="noopener" class="text-decoration-none fw-semibold text-success d-inline-flex align-items-center gap-1 small">
                                            <i class="bi bi-whatsapp"></i> Chat on WhatsApp
                                        </a>
                                    </p>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Email --}}
                        @if($profile->email_for_inquiries || ($profile->user && $profile->user->email))
                        <div class="info-item-box d-flex align-items-start gap-3 mb-4">
                            <div class="icon-box bg-info-soft rounded-3 p-3 text-info flex-shrink-0">
                                <i class="bi bi-envelope-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Email Address</h6>
                                <p class="mb-0">
                                    <a href="mailto:{{ $profile->email_for_inquiries ?? $profile->user->email }}" class="text-decoration-none fw-bold text-secondary hover-primary-text">
                                        {{ $profile->email_for_inquiries ?? $profile->user->email }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Website --}}
                        @if($profile->website)
                        <div class="info-item-box d-flex align-items-start gap-3 mb-4">
                            <div class="icon-box bg-warning-soft rounded-3 p-3 text-warning flex-shrink-0">
                                <i class="bi bi-globe2 fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Website</h6>
                                <p class="mb-0">
                                    <a href="{{ $profile->website }}" target="_blank" rel="noopener" class="text-decoration-none fw-bold text-secondary hover-primary-text">
                                        {{ $profile->website }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Social Links --}}
                        @php
                            $socialLinks = [
                                'facebook'  => ['icon' => 'bi-facebook',   'color' => '#1877F2', 'label' => 'Facebook'],
                                'instagram' => ['icon' => 'bi-instagram',  'color' => '#E1306C', 'label' => 'Instagram'],
                                'twitter'   => ['icon' => 'bi-twitter-x',  'color' => '#000000', 'label' => 'Twitter/X'],
                                'linkedin'  => ['icon' => 'bi-linkedin',   'color' => '#0A66C2', 'label' => 'LinkedIn'],
                                'youtube'   => ['icon' => 'bi-youtube',    'color' => '#FF0000', 'label' => 'YouTube'],
                            ];
                            $hasSocials = false;
                            foreach ($socialLinks as $key => $info) {
                                try {
                                    if (!empty($profile->{$key})) { $hasSocials = true; break; }
                                } catch (\Throwable $e) {}
                            }
                        @endphp

                        @if($hasSocials)
                        <div class="mt-2">
                            <h6 class="fw-bold mb-3 text-dark" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Follow Us</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($socialLinks as $key => $info)
                                    @php
                                        try { $val = $profile->{$key} ?? null; } catch (\Throwable $e) { $val = null; }
                                    @endphp
                                    @if(!empty($val))
                                        <a href="{{ $val }}" target="_blank" rel="noopener"
                                           class="social-link-btn d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 text-decoration-none fw-semibold"
                                           style="background: {{ $info['color'] }}12; color: {{ $info['color'] }}; border: 1px solid {{ $info['color'] }}28; font-size: 0.82rem; transition: all 0.2s;"
                                           title="{{ $info['label'] }}">
                                            <i class="bi {{ $info['icon'] }}"></i>
                                            {{ $info['label'] }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Quick Action Buttons --}}
                    <div class="mt-4 position-relative z-index-1">
                        @if($profile->whatsapp_number)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile->whatsapp_number) }}?text=Hello, I am interested in your catalog at {{ route('subscriber_store', $profile->company_slug) }}"
                               target="_blank" rel="noopener"
                               class="btn w-100 py-2 fw-bold text-white mb-2 d-flex align-items-center justify-content-center gap-2"
                               style="background:#25D366; border:none; border-radius:10px; font-size:0.9rem;">
                                <i class="bi bi-whatsapp"></i> Chat on WhatsApp
                            </a>
                        @endif
                        @if($profile->email_for_inquiries)
                            <a href="mailto:{{ $profile->email_for_inquiries }}"
                               class="btn btn-outline-primary w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2"
                               style="border-radius:10px; font-size:0.9rem;">
                                <i class="bi bi-envelope-fill"></i> Send an Email
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Enquiry Form Card --}}
            <div class="col-lg-7 col-12 order-1 order-lg-2 animate-fade-in">
                <div class="premium-form-card p-4 p-md-5 border rounded-4 bg-white shadow-lg position-relative overflow-hidden">
                    <div class="form-bg-glow"></div>
                    <div class="position-relative z-index-1">

                        <h3 class="fw-bold mb-2 text-dark card-heading" style="letter-spacing: -0.5px; font-family: 'Outfit', sans-serif;">Send an Enquiry</h3>
                        <p class="text-secondary small mb-4">Fill in your details and we'll get back to you as soon as possible.</p>

                        {{-- Success --}}
                        @if(session('success'))
                            <div class="alert alert-success border-0 rounded-4 p-4 mb-4 animate-fade-in d-flex align-items-center gap-3">
                                <div class="alert-icon-wrapper bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;flex-shrink:0;">
                                    <i class="bi bi-check-lg fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-success">Enquiry Sent!</h6>
                                    <small class="text-secondary">{{ session('success') }}</small>
                                </div>
                            </div>
                        @endif

                        {{-- Validation Errors --}}
                        @if($errors->any())
                            <div class="alert alert-danger border-0 rounded-4 p-4 mb-4 animate-fade-in">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                                    <h6 class="fw-bold mb-0 text-danger">Please fix the following:</h6>
                                </div>
                                <ul class="mb-0 small ps-3">
                                    @foreach($errors->all() as $error)
                                        <li class="text-secondary mb-1">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('enquiry.submit') }}" method="POST" id="store-enquiry-form">
                            @csrf
                            <input type="hidden" name="company_slug" value="{{ $profile->company_slug }}">

                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label class="form-label-premium">Full Name *</label>
                                    <div class="input-group-premium">
                                        <span class="input-icon"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" name="name" class="form-control-premium" placeholder="Your Name" value="{{ old('name') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label class="form-label-premium">Email *</label>
                                    <div class="input-group-premium">
                                        <span class="input-icon"><i class="bi bi-envelope-fill"></i></span>
                                        <input type="email" name="email" class="form-control-premium" placeholder="you@company.com" value="{{ old('email') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label class="form-label-premium">Phone *</label>
                                    <div class="input-group-premium">
                                        <span class="input-icon"><i class="bi bi-telephone-fill"></i></span>
                                        <input type="text" name="phone" class="form-control-premium" placeholder="+91 98765 43210" value="{{ old('phone') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label class="form-label-premium">Subject</label>
                                    <div class="input-group-premium">
                                        <span class="input-icon"><i class="bi bi-chat-left-dots-fill"></i></span>
                                        <input type="text" name="subject" class="form-control-premium" placeholder="Product Inquiry" value="{{ old('subject') }}">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label-premium">Message *</label>
                                    <textarea name="message" class="form-control-premium-textarea" rows="5" placeholder="Tell us about your requirements, bulk order quantity, or any questions..." required>{{ old('message') }}</textarea>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-premium-send w-100 py-3 rounded-3 shadow-md d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-send-fill text-white"></i> Send Enquiry to {{ $profile->company_name }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Back to Store CTA --}}
        <div class="text-center mt-5 animate-fade-in">
            <a href="{{ route('subscriber_store', $profile->company_slug) }}" class="btn btn-outline-primary px-5 py-3 rounded-pill fw-bold">
                <i class="bi bi-arrow-left me-2"></i> Back to {{ $profile->company_name }} Catalog
            </a>
        </div>

    </div>
</div>

<style>
    .store-contact-wrapper {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #FAFCFF;
        background-image: radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.03) 0px, transparent 50%),
                          radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.03) 0px, transparent 50%);
    }

    .fw-extrabold { font-weight: 800; }

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
    .breadcrumb-link:hover { color: #4F46E5; }

    .bg-primary-soft  { background-color: rgba(79, 70, 229, 0.08) !important; }
    .bg-success-soft  { background-color: rgba(16, 185, 129, 0.08) !important; }
    .bg-info-soft     { background-color: rgba(6, 182, 212, 0.08) !important; }
    .bg-warning-soft  { background-color: rgba(245, 158, 11, 0.08) !important; }

    .hover-primary-text { transition: color 0.2s ease; color: #475569 !important; }
    .hover-primary-text:hover { color: #4F46E5 !important; }

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
        position: absolute; top: -50px; right: -50px;
        width: 150px; height: 150px; border-radius: 50%;
        background: radial-gradient(circle, rgba(79, 70, 229, 0.08) 0%, transparent 70%);
        pointer-events: none;
    }
    .form-bg-glow {
        position: absolute; bottom: -50px; left: -50px;
        width: 180px; height: 180px; border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.06) 0%, transparent 70%);
        pointer-events: none;
    }

    .form-label-premium {
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; color: #64748B; margin-bottom: 6px; display: block;
    }

    .input-group-premium {
        position: relative; display: flex; align-items: center;
        width: 100%; border-radius: 10px; border: 1.5px solid #E2E8F0;
        background-color: #F8FAFC; transition: all 0.2s ease;
    }
    .input-group-premium:focus-within {
        border-color: #4F46E5; background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .input-icon {
        padding-left: 14px; color: #94A3B8;
        display: flex; align-items: center; font-size: 1rem;
    }

    .form-control-premium {
        width: 100%; padding: 12px 14px 12px 10px;
        border: none; background: transparent; font-size: 0.9rem;
        color: #1E293B; font-weight: 500; outline: none;
    }
    .form-control-premium::placeholder { color: #94A3B8; }

    .form-control-premium-textarea {
        width: 100%; padding: 14px; border: 1.5px solid #E2E8F0;
        border-radius: 10px; background-color: #F8FAFC; font-size: 0.9rem;
        color: #1E293B; font-weight: 500; outline: none; transition: all 0.2s ease;
    }
    .form-control-premium-textarea:focus {
        border-color: #4F46E5; background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }
    .form-control-premium-textarea::placeholder { color: #94A3B8; }

    .btn-premium-send {
        background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);
        border: none; color: white; font-weight: 600; font-size: 0.95rem;
        letter-spacing: -0.01em; transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    .btn-premium-send:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
        background: linear-gradient(135deg, #4338CA 0%, #2E2984 100%);
    }
    .btn-premium-send:active { transform: translateY(1px); }

    .social-link-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }

    .animate-fade-in { animation: fadeInUp 0.6s ease forwards; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .display-5 { font-size: 2rem; }
    }
</style>
@endsection
