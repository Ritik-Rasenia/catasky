@extends('subscriber-panel.layouts.app')

@section('title', 'Branding & Profile Settings')

@section('content')
<div class="vp-page-header">
    <div>
        <h1 class="vp-page-title">Branding & Profile Settings</h1>
        <div class="vp-breadcrumb">
            <a href="{{ route('subscriber.dashboard') }}">Dashboard</a> &nbsp;/&nbsp; <span>Branding Settings</span>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius:12px; background:#DCFCE7; color:#15803d;">
    <i class="bi bi-check-circle-fill me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius:12px; background:#FEE2E2; color:#991B1B;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    {{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row g-4">
    {{-- Tabs Left Selector --}}
    <div class="col-md-3">
        <div class="vp-card p-2">
            <div class="nav flex-column nav-pills" id="settingsTabs" role="tablist" aria-orientation="vertical">
                <button class="nav-link active text-start d-flex align-items-center gap-2 py-2.5 px-3 mb-1" id="tab-profile" data-bs-toggle="pill" data-bs-target="#content-profile" type="button" role="tab" style="border-radius:10px; font-weight: 500;">
                    <i class="bi bi-shop fs-5"></i> Company Profile
                </button>
                <button class="nav-link text-start d-flex align-items-center gap-2 py-2.5 px-3 mb-1" id="tab-pdf" data-bs-toggle="pill" data-bs-target="#content-pdf" type="button" role="tab" style="border-radius:10px; font-weight: 500;">
                    <i class="bi bi-file-earmark-pdf fs-5"></i> PDF Catalog Template
                </button>
                <button class="nav-link text-start d-flex align-items-center gap-2 py-2.5 px-3" id="tab-security" data-bs-toggle="pill" data-bs-target="#content-security" type="button" role="tab" style="border-radius:10px; font-weight: 500;">
                    <i class="bi bi-shield-lock fs-5"></i> Account Security
                </button>
            </div>
        </div>
    </div>

    {{-- Tabs Content Panel --}}
    <div class="col-md-9">
        <div class="tab-content" id="settingsTabsContent">
            
            {{-- Tab 1: Company Profile --}}
            <div class="tab-pane fade show active" id="content-profile" role="tabpanel" aria-labelledby="tab-profile">
                <div class="vp-card">
                    <div class="vp-card-header">
                        <h5 class="vp-card-title">Company Profile & Branding Details</h5>
                    </div>
                    <div class="vp-card-body">
                        <form action="{{ route('subscriber.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            {{-- Logo Upload with Preview --}}
                            <div class="row align-items-center mb-4 pb-3 border-bottom">
                                <div class="col-sm-2 text-center text-sm-start">
                                    @if($profile->logo)
                                        <img id="logo-preview-img" src="{{ asset('uploads/subscriber-logos/' . $profile->logo) }}" alt="Logo" class="rounded-3 border object-cover" style="width: 80px; height: 80px; object-fit: cover;">
                                    @else
                                        <div id="logo-preview-placeholder" class="rounded-3 border bg-light d-flex align-items-center justify-content-center fw-bold fs-3 text-secondary" style="width: 80px; height: 80px;">
                                            {{ strtoupper(substr($profile->company_name ?: $user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-sm-10 mt-3 mt-sm-0">
                                    <label class="vp-label">Company Logo</label>
                                    <input type="file" name="logo" id="logo-file-input" class="form-control" style="border-radius:10px; font-size: 0.85rem;" accept="image/*">
                                    <span class="text-muted" style="font-size:0.75rem;">Recommended dimension: Square. Max size: 2MB. Format: PNG, JPG, WEBP.</span>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Full Name</label>
                                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="vp-input" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Company / Brand Name</label>
                                        <input type="text" name="company_name" value="{{ old('company_name', $profile->company_name) }}" class="vp-input" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Inquiry Email</label>
                                        <input type="email" name="email_for_inquiries" value="{{ old('email_for_inquiries', $profile->email_for_inquiries ?: $user->email) }}" class="vp-input">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Website URL</label>
                                        <input type="url" name="website" value="{{ old('website', $profile->website) }}" class="vp-input" placeholder="https://example.com">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Phone Number</label>
                                        <input type="text" name="phone" value="{{ old('phone', $profile->phone) }}" class="vp-input">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">WhatsApp Contact Number</label>
                                        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $profile->whatsapp_number) }}" class="vp-input">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Brand Primary Color</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" name="primary_color" value="{{ old('primary_color', $profile->primary_color ?: '#4F46E5') }}" class="form-control form-control-color border-0 p-0" style="width:45px; height:45px; border-radius:10px; cursor:pointer;">
                                            <input type="text" class="vp-input flex-grow-1" value="{{ $profile->primary_color ?: '#4F46E5' }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Brand Secondary Color</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" name="secondary_color" value="{{ old('secondary_color', $profile->secondary_color ?: '#7C3AED') }}" class="form-control form-control-color border-0 p-0" style="width:45px; height:45px; border-radius:10px; cursor:pointer;">
                                            <input type="text" class="vp-input flex-grow-1" value="{{ $profile->secondary_color ?: '#7C3AED' }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Company Bio / Description</label>
                                        <textarea name="bio" class="vp-textarea" rows="3" placeholder="Briefly describe your company or catalog collections...">{{ old('bio', $profile->bio) }}</textarea>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Business Address</label>
                                        <input type="text" name="address" value="{{ old('address', $profile->address) }}" class="vp-input">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="vp-form-group">
                                        <label class="vp-label">City</label>
                                        <input type="text" name="city" value="{{ old('city', $profile->city) }}" class="vp-input">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="vp-form-group">
                                        <label class="vp-label">State</label>
                                        <input type="text" name="state" value="{{ old('state', $profile->state) }}" class="vp-input">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Pincode</label>
                                        <input type="text" name="pincode" value="{{ old('pincode', $profile->pincode) }}" class="vp-input">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">GST / Tax Identification Number</label>
                                        <input type="text" name="gst_number" value="{{ old('gst_number', $profile->gst_number) }}" class="vp-input" placeholder="GSTIN/Tax ID">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Country</label>
                                        <input type="text" name="country" value="{{ old('country', $profile->country ?: 'India') }}" class="vp-input">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-subscriber mt-3">
                                <i class="bi bi-save-fill"></i> Save Branding Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tab 2: PDF Template Settings --}}
            <div class="tab-pane fade" id="content-pdf" role="tabpanel" aria-labelledby="tab-pdf">
                <div class="vp-card">
                    <div class="vp-card-header">
                        <h5 class="vp-card-title">PDF Catalog Export Settings</h5>
                    </div>
                    <div class="vp-card-body">
                        <form action="{{ route('subscriber.profile.pdf-template') }}" method="POST">
                            @csrf
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Catalog Template Name</label>
                                        <input type="text" name="template_name" value="{{ old('template_name', $pdfTemplate->name ?: 'Standard Catalog') }}" class="vp-input" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Header Title Text Override</label>
                                        <input type="text" name="header_text" value="{{ old('header_text', $pdfTemplate->header_text) }}" class="vp-input" placeholder="Leave empty for Company Name">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">PDF Primary Brand Color</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" name="brand_color" value="{{ old('brand_color', $pdfTemplate->brand_color ?: '#4F46E5') }}" class="form-control form-control-color border-0 p-0" style="width:45px; height:45px; border-radius:10px; cursor:pointer;">
                                            <input type="text" class="vp-input flex-grow-1" value="{{ $pdfTemplate->brand_color ?: '#4F46E5' }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">PDF Accent Color</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" name="accent_color" value="{{ old('accent_color', $pdfTemplate->accent_color ?: '#7C3AED') }}" class="form-control form-control-color border-0 p-0" style="width:45px; height:45px; border-radius:10px; cursor:pointer;">
                                            <input type="text" class="vp-input flex-grow-1" value="{{ $pdfTemplate->accent_color ?: '#7C3AED' }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Layout Mode</label>
                                        <select name="layout" class="vp-select">
                                            <option value="grid" {{ $pdfTemplate->layout === 'grid' ? 'selected' : '' }}>Grid Grid Layout (Recommended)</option>
                                            <option value="list" {{ $pdfTemplate->layout === 'list' ? 'selected' : '' }}>List Rows Sheet</option>
                                            <option value="detailed" {{ $pdfTemplate->layout === 'detailed' ? 'selected' : '' }}>Detailed Specifications Leaflet</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Paper Dimension Size</label>
                                        <select name="paper_size" class="vp-select">
                                            <option value="A4" {{ $pdfTemplate->paper_size === 'A4' ? 'selected' : '' }}>A4 Standard (Recommended)</option>
                                            <option value="A3" {{ $pdfTemplate->paper_size === 'A3' ? 'selected' : '' }}>A3 Extra Space</option>
                                            <option value="Letter" {{ $pdfTemplate->paper_size === 'Letter' ? 'selected' : '' }}>US Letter</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 my-3 border-top pt-3">
                                    <h6 class="fw-bold mb-3" style="font-size:0.85rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">PDF Visibility Toggles</h6>
                                </div>

                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-toggle">
                                            <input type="checkbox" name="show_logo" value="1" {{ $pdfTemplate->show_logo !== false ? 'checked' : '' }}>
                                            <span class="vp-toggle-label">Render Company Logo on PDF Headers</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-toggle">
                                            <input type="checkbox" name="show_qr_code" value="1" {{ $pdfTemplate->show_qr_code !== false ? 'checked' : '' }}>
                                            <span class="vp-toggle-label">Include Online Catalog Scan QR Code</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-toggle">
                                            <input type="checkbox" name="show_page_numbers" value="1" {{ $pdfTemplate->show_page_numbers !== false ? 'checked' : '' }}>
                                            <span class="vp-toggle-label">Show Page Number Indicators in Footer</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-toggle">
                                            <input type="checkbox" name="show_watermark" id="watermark-toggle" value="1" {{ $pdfTemplate->show_watermark ? 'checked' : '' }}>
                                            <span class="vp-toggle-label">Overlay Diagonal Page Watermark Text</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12" id="watermark-text-wrap" style="{{ $pdfTemplate->show_watermark ? '' : 'display:none;' }}">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Custom Watermark Text</label>
                                        <input type="text" name="watermark_text" value="{{ old('watermark_text', $pdfTemplate->watermark_text) }}" class="vp-input" placeholder="e.g. DRAFT or CONFIDENTIAL">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Footer Terms & Copyright Note</label>
                                        <input type="text" name="footer_text" value="{{ old('footer_text', $pdfTemplate->footer_text) }}" class="vp-input" placeholder="e.g. Terms & conditions apply. Generated via CataSky.">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-subscriber mt-3">
                                <i class="bi bi-save-fill"></i> Save PDF Template Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tab 3: Account Security --}}
            <div class="tab-pane fade" id="content-security" role="tabpanel" aria-labelledby="tab-security">
                <div class="vp-card">
                    <div class="vp-card-header">
                        <h5 class="vp-card-title">Update Password</h5>
                    </div>
                    <div class="vp-card-body">
                        <form action="{{ route('subscriber.profile.password') }}" method="POST">
                            @csrf
                            
                            <div class="vp-form-group">
                                <label class="vp-label">Current Password</label>
                                <input type="password" name="current_password" class="vp-input" required>
                            </div>

                            <div class="vp-form-group">
                                <label class="vp-label">New Password</label>
                                <input type="password" name="password" class="vp-input" placeholder="Min 8 characters" required>
                            </div>

                            <div class="vp-form-group">
                                <label class="vp-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="vp-input" required>
                            </div>

                            <button type="submit" class="btn-subscriber mt-3">
                                <i class="bi bi-shield-lock-fill"></i> Update Account Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Watermark text field
        const watermarkToggle = document.getElementById('watermark-toggle');
        const watermarkWrap = document.getElementById('watermark-text-wrap');
        
        if (watermarkToggle && watermarkWrap) {
            watermarkToggle.addEventListener('change', function() {
                if (this.checked) {
                    watermarkWrap.style.display = 'block';
                } else {
                    watermarkWrap.style.display = 'none';
                }
            });
        }

        // Live color pickers reading hex codes
        document.querySelectorAll('input[type="color"]').forEach(picker => {
            picker.addEventListener('input', function() {
                const textInput = this.nextElementSibling;
                if (textInput) {
                    textInput.value = this.value.toUpperCase();
                }
            });
        });

        // Logo Upload Preview
        const logoInput = document.getElementById('logo-file-input');
        const previewImg = document.getElementById('logo-preview-img');
        const previewPlaceholder = document.getElementById('logo-preview-placeholder');

        if (logoInput) {
            logoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (previewImg) {
                            previewImg.src = e.target.result;
                        } else if (previewPlaceholder) {
                            // Convert placeholder to image
                            const img = document.createElement('img');
                            img.id = 'logo-preview-img';
                            img.src = e.target.result;
                            img.alt = 'Logo';
                            img.className = 'rounded-3 border object-cover';
                            img.style.width = '80px';
                            img.style.height = '80px';
                            img.style.objectFit = 'cover';
                            previewPlaceholder.parentNode.replaceChild(img, previewPlaceholder);
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endsection
