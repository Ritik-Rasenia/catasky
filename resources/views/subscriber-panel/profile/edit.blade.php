@extends('subscriber-panel.layouts.app')

@section('title', 'Store Branding & B2B Profile Settings')

@push('css')
<style>
    :root {
        --primary-hsl: 243, 75%, 59%;
        --secondary-hsl: 262, 80%, 50%;
        --success-hsl: 142, 70%, 45%;
        --warning-hsl: 38, 92%, 50%;
        --danger-hsl: 0, 84%, 60%;
        
        --primary-theme: hsl(var(--primary-hsl));
        --secondary-theme: hsl(var(--secondary-hsl));
        --border-color: rgba(226, 232, 240, 0.8);
        --bg-light: #F8FAFC;
    }

    body {
        background-color: var(--bg-light);
        font-family: 'Inter', sans-serif;
    }

    .premium-page-header {
        background: linear-gradient(135deg, #1E1B4B 0%, #312E81 100%);
        border-radius: 20px;
        padding: 24px 30px;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(30, 27, 75, 0.15);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .premium-page-header::after {
        content: '';
        position: absolute;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
        right: -50px; top: -50px;
        pointer-events: none;
    }

    .premium-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .premium-card:hover {
        box-shadow: 0 12px 20px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
    }

    /* Modern Tabs Pills */
    .pill-tab-container {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 8px;
        display: flex;
        gap: 8px;
        overflow-x: auto;
    }

    .pill-tab-btn {
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 10px 20px;
        transition: all 0.25s ease;
        border: none;
        background: transparent;
        color: #64748B;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .pill-tab-btn:hover {
        color: var(--primary-theme);
        background: rgba(79, 70, 229, 0.05);
    }

    .pill-tab-btn.active {
        color: white;
        background: linear-gradient(135deg, var(--primary-theme), var(--secondary-theme));
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }

    /* Workflow Alert Cards */
    .workflow-card {
        border-radius: 16px;
        border: 1px solid transparent;
        padding: 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        position: relative;
    }

    .workflow-card-draft {
        background: hsl(38, 92%, 97%);
        border-color: hsl(38, 92%, 90%);
        color: hsl(38, 92%, 20%);
    }

    .workflow-card-pending {
        background: hsl(243, 75%, 97%);
        border-color: hsl(243, 75%, 90%);
        color: hsl(243, 75%, 25%);
    }

    .workflow-card-live {
        background: hsl(142, 70%, 96%);
        border-color: hsl(142, 70%, 88%);
        color: hsl(142, 70%, 15%);
    }

    .workflow-card-rejected {
        background: hsl(0, 84%, 97%);
        border-color: hsl(0, 84%, 90%);
        color: hsl(0, 84%, 25%);
    }

    .workflow-icon-badge {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    .workflow-card-draft .workflow-icon-badge { background: hsl(38, 92%, 50%); color: white; }
    .workflow-card-pending .workflow-icon-badge { background: hsl(243, 75%, 59%); color: white; }
    .workflow-card-live .workflow-icon-badge { background: hsl(142, 70%, 45%); color: white; }
    .workflow-card-rejected .workflow-icon-badge { background: hsl(0, 84%, 60%); color: white; }

    /* Form Fields */
    .form-control-premium {
        border: 1.5px solid #E2E8F0;
        border-radius: 12px;
        padding: 11px 16px;
        font-weight: 500;
        color: #1E293B;
        transition: all 0.2s ease;
        background: #F8FAFC;
    }

    .form-control-premium:focus {
        background: white;
        border-color: var(--primary-theme);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        outline: none;
    }

    /* Uploader Drag Blocks */
    .upload-zone-wrapper {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    @media(min-width: 768px) {
        .upload-zone-wrapper {
            grid-template-columns: 200px 1fr;
        }
    }

    .panoramic-banner-uploader {
        border: 2px dashed #CBD5E1;
        border-radius: 16px;
        background: #F8FAFC;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .panoramic-banner-uploader:hover {
        border-color: var(--primary-theme);
        background: rgba(79, 70, 229, 0.02);
    }

    .banner-preview-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0; left: 0;
        z-index: 1;
        opacity: 0.85;
    }

    .banner-preview-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.4);
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .panoramic-banner-uploader:hover .banner-preview-overlay {
        opacity: 1;
    }

    .logo-uploader-circle {
        width: 120px; height: 120px;
        border-radius: 20px;
        border: 2px dashed #CBD5E1;
        background: #F8FAFC;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
    }

    .logo-uploader-circle:hover {
        border-color: var(--primary-theme);
        background: rgba(79, 70, 229, 0.02);
    }

    .logo-preview-img {
        width: 100%; height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0; left: 0;
    }

    /* Premium Buttons */
    .btn-premium-action {
        background: linear-gradient(135deg, var(--primary-theme), var(--secondary-theme));
        color: white;
        font-weight: 600;
        border: none;
        border-radius: 12px;
        padding: 12px 28px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
    }

    .btn-premium-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
        color: white;
    }

    .btn-premium-action:active {
        transform: translateY(1px);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    
    {{-- Custom Status Badge --}}
        @php
            $storeStatus = $profile->store_status ?? 'draft';
        @endphp

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 mb-4 shadow-sm" role="alert" style="border-radius:16px; background:#DCFCE7; color:#15803d; padding:16px 20px;">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>
                <strong>Success!</strong> {{ session('success') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 mb-4 shadow-sm" role="alert" style="border-radius:16px; background:#FEE2E2; color:#991B1B; padding:16px 20px;">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                <strong>Verification failed:</strong> {{ $errors->first() }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4">
        {{-- Navigation Pills Tabs --}}
        <div class="col-12">
            <div class="pill-tab-container">
                <button class="pill-tab-btn active" id="tab-btn-store" data-bs-toggle="pill" data-bs-target="#panel-store" type="button" role="tab">
                    <i class="bi bi-shop fs-5"></i> Store Branding
                </button>
                <button class="pill-tab-btn" id="tab-btn-personal" data-bs-toggle="pill" data-bs-target="#panel-personal" type="button" role="tab">
                    <i class="bi bi-person-badge fs-5"></i> Company Profile
                </button>
                <button class="pill-tab-btn" id="tab-btn-subscription" data-bs-toggle="pill" data-bs-target="#panel-subscription" type="button" role="tab">
                    <i class="bi bi-credit-card fs-5"></i> Subscription & Billings
                </button>
                <button class="pill-tab-btn" id="tab-btn-security" data-bs-toggle="pill" data-bs-target="#panel-security" type="button" role="tab">
                    <i class="bi bi-shield-lock fs-5"></i> Account Security
                </button>
            </div>
        </div>

        {{-- Tab Panels --}}
        <div class="col-12">
            <div class="tab-content" id="pills-tabContent">
                
                {{-- Panel 1: Store Branding --}}
                <div class="tab-pane fade show active" id="panel-store" role="tabpanel">
                    
                    {{-- 1.1 Store Status Card Workflow --}}
                    @if($storeStatus === 'live')
                        <div class="workflow-card workflow-card-live shadow-sm">
                            <div class="workflow-icon-badge"><i class="bi bi-check-lg"></i></div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1">Your store is Live!</h5>
                                <p class="small opacity-90 mb-2">Congratulations, your store branding has been verified. Users can view your products and catalogs publicly.</p>
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <a href="{{ route('store.public', $profile->company_slug) }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold"><i class="bi bi-box-arrow-up-right me-1"></i> Visit Catalog Front</a>
                                    <div class="input-group input-group-sm" style="max-width:320px;">
                                        <input type="text" class="form-control bg-white border" readonly id="public-store-link" value="{{ route('store.public', $profile->company_slug) }}">
                                        <button class="btn btn-outline-success mx-3" type="button" onclick="copyLink()"><i class="bi bi-copy"></i> Copy</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($storeStatus === 'pending')
                        <div class="workflow-card workflow-card-pending shadow-sm">
                            <div class="workflow-icon-badge"><i class="bi bi-clock-history"></i></div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1">Store configuration pending approval</h5>
                                <p class="small opacity-90 mb-0">Our administrators are currently reviewing your store branding assets, logos, and company verification files. You will be notified immediately once it is active.</p>
                            </div>
                        </div>
                    @elseif($storeStatus === 'rejected')
                        <div class="workflow-card workflow-card-rejected shadow-sm">
                            <div class="workflow-icon-badge"><i class="bi bi-x-circle-fill"></i></div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1">Store configuration revision required</h5>
                                <p class="small opacity-90 mb-2"><strong>Reason for revision:</strong> {{ $profile->suspension_reason ?? 'The uploaded branding logo, Banner, or GST documentation was invalid.' }}</p>
                                <p class="small opacity-90 mb-3">Please fix the assets and resubmit your store layout for a compliance check.</p>
                                <form action="{{ route('subscriber.profile.update') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <input type="hidden" name="company_name" value="{{ $profile->company_name }}">
                                    <input type="hidden" name="submit_store" value="1">
                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3.5 py-2 fw-semibold"><i class="bi bi-arrow-clockwise me-1"></i> Re-submit Store Branding</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="workflow-card workflow-card-draft shadow-sm">
                            <div class="workflow-icon-badge"><i class="bi bi-pen-fill"></i></div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1">Your Store is in Draft Mode</h5>
                                <p class="small opacity-90 mb-2">Complete your branding, upload a panoramic banner and company logo, then submit the configuration to make your catalog public.</p>
                                <form action="{{ route('subscriber.profile.update') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <input type="hidden" name="company_name" value="{{ $profile->company_name }}">
                                    <input type="hidden" name="submit_store" value="1">
                                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3.5 py-2 fw-bold text-dark"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Submit Store For Approval</button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- 1.2 Store Branding Assets Form --}}
                    <div class="premium-card">
                        <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-palette-fill text-primary me-2"></i>Storefront Identity & Design</h5>
                            <span class="small text-muted fw-medium">* Required fields</span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('subscriber.profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="name" value="{{ old('name', $user->name) }}">
                                
                                {{-- Logo & Banner Uploaders --}}
                                <div class="mb-4 pb-4 border-bottom">
                                    <h6 class="fw-bold text-secondary mb-3 small text-uppercase" style="letter-spacing: 0.05em;"><i class="bi bi-images me-1.5"></i>Branding Assets</h6>
                                    
                                    <div class="upload-zone-wrapper">
                                        {{-- Logo Uploader --}}
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="logo-uploader-circle" onclick="document.getElementById('logo-input-element').click()">
                                                @if($profile->logo)
                                                    <img id="logo-preview-src" src="{{ asset('uploads/subscriber-logos/' . $profile->logo) }}" alt="Logo" class="logo-preview-img">
                                                @else
                                                    <img id="logo-preview-src" src="" alt="Logo" class="logo-preview-img" style="display:none;">
                                                @endif
                                                <div id="logo-upload-placeholder" class="text-center p-2 {{ $profile->logo ? 'd-none' : '' }}">
                                                    <i class="bi bi-camera-fill text-secondary fs-3"></i>
                                                    <span class="extra-small text-muted mt-1 d-block fw-bold">Logo</span>
                                                </div>
                                            </div>
                                            <input type="file" name="logo" id="logo-input-element" class="d-none" accept="image/*" onchange="previewBrandingLogo(event)">
                                            <span class="extra-small text-muted mt-2 text-center">Square Logo (1:1)</span>
                                        </div>

                                        {{-- Panoramic Banner Uploader --}}
                                        <div class="flex-grow-1">
                                            <div class="panoramic-banner-uploader" onclick="document.getElementById('banner-input-element').click()">
                                                @if($profile->banner)
                                                    <img id="banner-preview-src" src="{{ asset('uploads/subscriber-banners/' . $profile->banner) }}" alt="Banner" class="banner-preview-img">
                                                @else
                                                    <img id="banner-preview-src" src="" alt="Banner" class="banner-preview-img" style="display:none;">
                                                @endif
                                                <div id="banner-upload-placeholder" class="text-center p-3 {{ $profile->banner ? 'd-none' : '' }}" style="z-index: 2;">
                                                    <i class="bi bi-image-fill text-primary fs-3"></i>
                                                    <h6 class="fw-bold mb-1 mt-1" style="font-size:0.9rem;">Upload Storefront Banner</h6>
                                                    <span class="extra-small text-muted d-block">Panoramic ratio (e.g. 1200x400) is highly recommended. Max: 5MB</span>
                                                </div>
                                                <div class="banner-preview-overlay">
                                                    <i class="bi bi-pencil-square me-1"></i> Replace Banner
                                                </div>
                                            </div>
                                            <input type="file" name="banner" id="banner-input-element" class="d-none" accept="image/*" onchange="previewBrandingBanner(event)">
                                        </div>
                                    </div>
                                </div>

                                {{-- Text Information --}}
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Company / Store Name *</label>
                                            <input type="text" name="company_name" value="{{ old('company_name', $profile->company_name) }}" class="form-control form-control-premium" required placeholder="Enter company name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Tax ID / GST Number</label>
                                            <input type="text" name="gst_number" value="{{ old('gst_number', $profile->gst_number) }}" class="form-control form-control-premium" placeholder="e.g. GSTIN proof">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Primary Theme Color</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" name="primary_color" id="primary-picker" value="{{ old('primary_color', $profile->primary_color ?: '#4F46E5') }}" class="form-control form-control-color border-0 p-0" style="width:48px; height:48px; border-radius:12px; cursor:pointer;" oninput="syncColorInput(this)">
                                                <input type="text" id="primary-hex" class="form-control form-control-premium flex-grow-1" value="{{ $profile->primary_color ?: '#4F46E5' }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Secondary Theme Color</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" name="secondary_color" id="secondary-picker" value="{{ old('secondary_color', $profile->secondary_color ?: '#7C3AED') }}" class="form-control form-control-color border-0 p-0" style="width:48px; height:48px; border-radius:12px; cursor:pointer;" oninput="syncColorInput(this)">
                                                <input type="text" id="secondary-hex" class="form-control form-control-premium flex-grow-1" value="{{ $profile->secondary_color ?: '#7C3AED' }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Store Bio / Catchy Slogan</label>
                                            <textarea name="bio" class="form-control form-control-premium" rows="3" placeholder="Explain your core catalog collections to B2B corporate buyers...">{{ old('bio', $profile->bio) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-top pt-4 mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-premium-action">
                                        <i class="bi bi-save me-2"></i> Save Storefront Branding
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Panel 2: Company Profile --}}
                <div class="tab-pane fade" id="panel-personal" role="tabpanel">
                    <div class="premium-card">
                        <div class="card-header bg-transparent py-3 px-4 border-bottom">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge-fill text-primary me-2"></i>Personal & Office Credentials</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('subscriber.profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="company_name" value="{{ $profile->company_name }}">
                                
                                {{-- Avatar Upload --}}
                                <div class="row align-items-center mb-4 pb-4 border-bottom g-3">
                                    <div class="col-auto">
                                        @if($user->profile_image)
                                            <img id="avatar-preview-element" src="{{ asset('uploads/profile/'.$user->profile_image) }}" alt="Avatar" class="rounded-circle border" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                                        @else
                                            <img id="avatar-preview-element" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4f46e5&color=fff" alt="Avatar" class="rounded-circle border" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                                        @endif
                                    </div>
                                    <div class="col">
                                        <label class="form-label text-secondary fw-semibold small text-uppercase mb-1">User Avatar Profile</label>
                                        <input type="file" name="profile_image" class="form-control form-control-premium form-control-sm" accept="image/*" onchange="previewUserAvatar(event)" style="max-width:320px;">
                                        <span class="text-muted extra-small mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Format: PNG, JPG, WEBP. Max: 2MB.</span>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Full Representative Name *</label>
                                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control form-control-premium" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">B2B Login ID (Email)</label>
                                            <input type="email" class="form-control form-control-premium bg-light border-0" value="{{ $user->email }}" readonly disabled style="cursor: not-allowed;">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Primary Office Helpline</label>
                                            <input type="text" name="phone" value="{{ old('phone', $profile->phone) }}" class="form-control form-control-premium" placeholder="Helpline Phone">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Inquiry WhatsApp Contact</label>
                                            <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $profile->whatsapp_number) }}" class="form-control form-control-premium" placeholder="WhatsApp contact line">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Corporate Website</label>
                                            <input type="url" name="website" value="{{ old('website', $profile->website) }}" class="form-control form-control-premium" placeholder="https://website.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Inquiry Email Target</label>
                                            <input type="email" name="email_for_inquiries" value="{{ old('email_for_inquiries', $profile->email_for_inquiries ?: $user->email) }}" class="form-control form-control-premium" placeholder="Target corporate inbox">
                                        </div>
                                    </div>

                                    <div class="col-12 my-2 border-top pt-3">
                                        <h6 class="fw-bold text-secondary small text-uppercase mb-3" style="letter-spacing:0.05em;"><i class="bi bi-geo-alt-fill me-1"></i>Office Dispatch Address</h6>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Office Street Address</label>
                                            <input type="text" name="address" value="{{ old('address', $profile->address) }}" class="form-control form-control-premium" placeholder="Complete office location address">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">City</label>
                                            <input type="text" name="city" value="{{ old('city', $profile->city) }}" class="form-control form-control-premium" placeholder="City">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">State</label>
                                            <input type="text" name="state" value="{{ old('state', $profile->state) }}" class="form-control form-control-premium" placeholder="State">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Pincode</label>
                                            <input type="text" name="pincode" value="{{ old('pincode', $profile->pincode) }}" class="form-control form-control-premium" placeholder="Pincode">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary fw-semibold small text-uppercase">Country</label>
                                            <input type="text" name="country" value="{{ old('country', $profile->country ?: 'India') }}" class="form-control form-control-premium">
                                        </div>
                                    </div>
                                </div>

                                <div class="border-top pt-4 mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-premium-action">
                                        <i class="bi bi-save me-2"></i> Save Corporate Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Panel 3: Subscription & Billings --}}
                <div class="tab-pane fade" id="panel-subscription" role="tabpanel">
                    {{-- 3.1 Active Subscription Header Plan --}}
                    <div class="premium-card mb-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: white; border: none; box-shadow: 0 10px 20px rgba(30,27,75,0.2);">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <span class="badge rounded-pill bg-white bg-opacity-20 text-dark mb-2 px-3 py-1 fw-bold" style="font-size:0.72rem;">ACTIVE B2B LICENSE</span>
                                    <h3 class="fw-bold mb-1 text-white" style="font-family:'Outfit', sans-serif;">
                                        {{ $subscription->plan?->name ?? 'Free Access Plan' }}
                                    </h3>
                                    <p class="opacity-75 small mb-0 mt-2 text-white">
                                        @if($subscription)
                                            Billing cycle started: {{ $subscription->starts_at ? $subscription->starts_at->format('M d, Y') : 'N/A' }}
                                        @else
                                            You are currently on a limited trial storefront plan.
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    @if($subscription)
                                        <div class="bg-white bg-opacity-10 p-3 rounded-4 d-inline-block border" style="border-color: rgba(255,255,255,0.08) !important;">
                                            <div class="text-white-50 small text-uppercase fw-bold" style="font-size:0.65rem;">Days Remaining</div>
                                            <div class="h2 fw-bold text-warning mb-0 mt-0.5">{{ $subscription->daysRemaining() }} Days</div>
                                            <div class="text-white-50 extra-small mt-0.5">Renews on: {{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y') : 'N/A' }}</div>
                                        </div>
                                    @else
                                        <a href="{{ route('subscriber.subscription.plans') }}" class="btn btn-warning rounded-pill px-4 py-2.5 fw-bold text-dark shadow-sm">Upgrade License</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3.2 Invoice History Table --}}
                    <div class="premium-card">
                        <div class="card-header bg-transparent py-3.5 px-4 border-bottom">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt text-primary me-2"></i>Invoices & Billing Statements</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 border-0 small text-muted text-uppercase fw-bold">Invoice No.</th>
                                            <th class="border-0 small text-muted text-uppercase fw-bold">Grand Total</th>
                                            <th class="border-0 small text-muted text-uppercase fw-bold">Date Paid</th>
                                            <th class="border-0 small text-muted text-uppercase fw-bold">Billing Term</th>
                                            <th class="border-0 small text-muted text-uppercase fw-bold">Status</th>
                                            <th class="text-end pe-4 border-0 small text-muted text-uppercase fw-bold">Receipt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invoices as $inv)
                                            <tr>
                                                <td class="ps-4"><span class="fw-bold text-dark">{{ $inv->invoice_number }}</span></td>
                                                <td><span class="fw-bold text-dark">₹{{ number_format($inv->total, 2) }}</span></td>
                                                <td class="small text-muted">{{ $inv->paid_date ? $inv->paid_date->format('M d, Y') : '-' }}</td>
                                                <td class="small text-muted">{{ $inv->created_at->format('M Y') }}</td>
                                                <td>
                                                    @if($inv->status === 'paid')
                                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 extra-small fw-bold">Paid</span>
                                                    @else
                                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2.5 py-1 extra-small fw-bold">Unpaid</span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ route('subscriber.subscription.invoice', $inv->id) }}" class="btn btn-outline-primary btn-sm rounded-3 py-1 px-3 d-inline-flex align-items-center gap-1 extra-small fw-bold">
                                                        <i class="bi bi-file-pdf text-danger"></i> PDF Receipt
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted empty-state">
                                                    <i class="bi bi-credit-card-2-back fs-1 opacity-25"></i>
                                                    <h6 class="fw-bold text-dark mt-2 mb-1">No billing data found</h6>
                                                    <p class="text-muted extra-small mb-0">You have no paid invoices logged on this profile yet.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel 4: Account Security --}}
                <div class="tab-pane fade" id="panel-security" role="tabpanel">
                    <div class="premium-card" style="max-width: 900px;">
                        <div class="card-header bg-transparent py-3 px-4 border-bottom">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Change Login Password</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('subscriber.profile.password') }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label text-secondary fw-semibold small text-uppercase">Current Security Password</label>
                                    <input type="password" name="current_password" class="form-control form-control-premium" placeholder="Verify current login password" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-secondary fw-semibold small text-uppercase">New Account Password</label>
                                    <input type="password" name="password" class="form-control form-control-premium" placeholder="Alphanumeric (8-20 characters)" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-secondary fw-semibold small text-uppercase">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="form-control form-control-premium" placeholder="Confirm password choice" required>
                                </div>

                                <div class="border-top pt-4 mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-premium-action">
                                        <i class="bi bi-lock-fill me-2"></i> Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab buttons maps
        const tabBtnMap = {
            'store': 'tab-btn-store',
            'personal': 'tab-btn-personal',
            'subscription': 'tab-btn-subscription',
            'security': 'tab-btn-security'
        };
        
        // Find the reverse map to know tab name from button ID
        const btnTabMap = {};
        for (const [key, value] of Object.entries(tabBtnMap)) {
            btnTabMap[value] = key;
        }

        // Get URL parameter or fallback to localStorage, or default to 'store'
        const urlParams = new URLSearchParams(window.location.search);
        let activeTab = urlParams.get('tab');
        
        if (!activeTab) {
            activeTab = localStorage.getItem('catasky-active-profile-tab') || 'store';
        }

        // Validate that the active tab is one of the valid keys
        if (!tabBtnMap[activeTab]) {
            activeTab = 'store';
        }

        const targetBtnId = tabBtnMap[activeTab];
        if (targetBtnId) {
            const targetBtn = document.getElementById(targetBtnId);
            if (targetBtn) {
                // De-activate all
                document.querySelectorAll('.pill-tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelectorAll('.tab-pane').forEach(panel => {
                    panel.classList.remove('show', 'active');
                });

                // Activate target
                targetBtn.classList.add('active');
                const targetPanelId = targetBtn.getAttribute('data-bs-target');
                const targetPanel = document.querySelector(targetPanelId);
                if (targetPanel) {
                    targetPanel.classList.add('show', 'active');
                }

                // Update URL to match without reload
                const url = new URL(window.location);
                url.searchParams.set('tab', activeTab);
                window.history.replaceState({}, '', url);
                localStorage.setItem('catasky-active-profile-tab', activeTab);
            }
        }

        // Add event listeners for clicking tab buttons
        document.querySelectorAll('.pill-tab-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const tabName = btnTabMap[this.id];
                if (tabName) {
                    // Update localStorage
                    localStorage.setItem('catasky-active-profile-tab', tabName);
                    
                    // Update URL without page reload
                    const url = new URL(window.location);
                    url.searchParams.set('tab', tabName);
                    window.history.replaceState({}, '', url);

                    // De-activate all buttons and panels
                    document.querySelectorAll('.pill-tab-btn').forEach(b => {
                        b.classList.remove('active');
                    });
                    document.querySelectorAll('.tab-pane').forEach(p => {
                        p.classList.remove('show', 'active');
                    });

                    // Activate this button and target panel
                    this.classList.add('active');
                    const targetPanelId = this.getAttribute('data-bs-target');
                    const targetPanel = document.querySelector(targetPanelId);
                    if (targetPanel) {
                        targetPanel.classList.add('show', 'active');
                    }
                }
            });
        });
    });

    // Copy link helper
    function copyLink() {
        const copyText = document.getElementById("public-store-link");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        
        alert("Public Store Link copied to clipboard!");
    }

    // Hex sync with color picker
    function syncColorInput(picker) {
        const hexField = picker.nextElementSibling;
        if (hexField) {
            hexField.value = picker.value.toUpperCase();
        }
    }

    // Logo Preview helper
    function previewBrandingLogo(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const img = document.getElementById('logo-preview-src');
            const placeholder = document.getElementById('logo-upload-placeholder');
            
            img.src = reader.result;
            img.style.display = 'block';
            if (placeholder) {
                placeholder.classList.add('d-none');
            }
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Banner Preview helper
    function previewBrandingBanner(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const img = document.getElementById('banner-preview-src');
            const placeholder = document.getElementById('banner-upload-placeholder');
            
            img.src = reader.result;
            img.style.display = 'block';
            if (placeholder) {
                placeholder.classList.add('d-none');
            }
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // User Avatar Preview helper
    function previewUserAvatar(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const img = document.getElementById('avatar-preview-element');
            img.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endpush
