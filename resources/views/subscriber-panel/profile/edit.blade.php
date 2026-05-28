@extends('subscriber-panel.layouts.app')

@section('title', 'Branding & Profile Settings')

@section('content')
<div class="vp-page-header">
    <div>
        <h1 class="vp-page-title">Branding & Profile Settings</h1>
        <div class="vp-breadcrumb">
            <a href="{{ route('subscriber.dashboard') }}">Dashboard</a> &nbsp;/&nbsp; <span>Profile Settings</span>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0  mb-4 animate-fade-in" role="alert" style="border-radius:16px; background:#DCFCE7; color:#15803d; padding:16px 20px;">
    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
    <strong>Success!</strong> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0  mb-4 animate-fade-in" role="alert" style="border-radius:16px; background:#FEE2E2; color:#991B1B; padding:16px 20px;">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <strong>Warning!</strong> {{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row g-4">
    {{-- Horizontal Tabs Selector --}}
    <div class="col-12">
        <div class="vp-card p-2 border-0  rounded-4 mb-2 bg-white">
            <div class="nav nav-pills d-flex flex-row justify-content-start gap-2 flex-wrap" id="settingsTabs" role="tablist">
                <button class="nav-link active d-flex align-items-center gap-2 py-2.5 px-4 border-0" id="tab-profile" data-bs-toggle="pill" data-bs-target="#content-profile" type="button" role="tab" style="border-radius:12px; font-weight: 600; transition:all 0.2s;">
                    <i class="bi bi-shop fs-5"></i> Business Profile
                </button>
                <button class="nav-link d-flex align-items-center gap-2 py-2.5 px-4 border-0" id="tab-personal" data-bs-toggle="pill" data-bs-target="#content-personal" type="button" role="tab" style="border-radius:12px; font-weight: 600; transition:all 0.2s;">
                    <i class="bi bi-person-circle fs-5"></i> Personal & Contacts
                </button>
                <button class="nav-link d-flex align-items-center gap-2 py-2.5 px-4 border-0" id="tab-subscription" data-bs-toggle="pill" data-bs-target="#content-subscription" type="button" role="tab" style="border-radius:12px; font-weight: 600; transition:all 0.2s;">
                    <i class="bi bi-credit-card-2-front-fill fs-5"></i> Subscription & Invoices
                </button>
                <button class="nav-link d-flex align-items-center gap-2 py-2.5 px-4 border-0" id="tab-security" data-bs-toggle="pill" data-bs-target="#content-security" type="button" role="tab" style="border-radius:12px; font-weight: 600; transition:all 0.2s;">
                    <i class="bi bi-shield-lock fs-5"></i> Account Security
                </button>
            </div>
        </div>
    </div>

    {{-- Tabs Content --}}
    <div class="col-12">
        <div class="tab-content" id="settingsTabsContent">
            
            {{-- Tab 1: Business Profile --}}
            <div class="tab-pane fade show active" id="content-profile" role="tabpanel" aria-labelledby="tab-profile">
                <div class="vp-card border-0  rounded-4">
                    <div class="vp-card-header bg-transparent py-3.5 px-4 border-bottom">
                        <h5 class="vp-card-title"><i class="bi bi-shop me-2 text-primary"></i>Business Information & Branding</h5>
                    </div>
                    <div class="vp-card-body p-4">
                        <form action="{{ route('subscriber.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            {{-- Logo Upload with Preview --}}
                            <div class="row align-items-center mb-4 pb-4 border-bottom g-3">
                                <div class="col-sm-2 text-center text-sm-start">
                                    @if($profile->logo)
                                        <img id="logo-preview-img" src="{{ asset('uploads/subscriber-logos/' . $profile->logo) }}" alt="Logo" class="rounded-4 border " style="width: 85px; height: 85px; object-fit: cover; border: 2.5px solid #fff;">
                                    @else
                                        <div id="logo-preview-placeholder" class="rounded-4 border bg-light d-flex align-items-center justify-content-center fw-bold fs-3 text-secondary " style="width: 85px; height: 85px;">
                                            {{ strtoupper(substr($profile->company_name ?: $user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-sm-10">
                                    <label class="vp-label mb-1">Company / Brand Logo</label>
                                    <input type="file" name="logo" id="logo-file-input" class="form-control rounded-3" style="font-size: 0.85rem;" accept="image/*">
                                    <span class="text-muted extra-small mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Square dimension is highly recommended. Max: 2MB (PNG, JPG, WEBP).</span>
                                </div>
                            </div>

                            <input type="hidden" name="name" value="{{ old('name', $user->name) }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" name="company_name" value="{{ old('company_name', $profile->company_name) }}" class="vp-input" required placeholder="Enter company name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">GST / Tax Identification Number</label>
                                        <input type="text" name="gst_number" value="{{ old('gst_number', $profile->gst_number) }}" class="vp-input" placeholder="e.g. GSTIN/Tax ID">
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
                                        <label class="vp-label">Inquiry Email</label>
                                        <input type="email" name="email_for_inquiries" value="{{ old('email_for_inquiries', $profile->email_for_inquiries ?: $user->email) }}" class="vp-input" placeholder="inquiries@company.com">
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
                                    <div class="vp-form-group mb-0">
                                        <label class="vp-label">Company Bio / Description</label>
                                        <textarea name="bio" class="vp-textarea" rows="3" placeholder="Briefly describe your company or catalog collections...">{{ old('bio', $profile->bio) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-subscriber mt-4">
                                <i class="bi bi-save-fill"></i> Save Business Information
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tab 2: Personal & Contact Info --}}
            <div class="tab-pane fade" id="content-personal" role="tabpanel" aria-labelledby="tab-personal">
                <div class="vp-card border-0  rounded-4">
                    <div class="vp-card-header bg-transparent py-3.5 px-4 border-bottom">
                        <h5 class="vp-card-title"><i class="bi bi-person-badge me-2 text-primary"></i>Personal & Contact Details</h5>
                    </div>
                    <div class="vp-card-body p-4">
                        <form action="{{ route('subscriber.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            {{-- User Avatar Upload --}}
                            <div class="row align-items-center mb-4 pb-4 border-bottom g-3">
                                <div class="col-sm-2 text-center text-sm-start">
                                    @if($user->profile_image)
                                        <img id="avatar-preview-img" src="{{ asset('uploads/profile/'.$user->profile_image) }}" alt="Avatar" class="rounded-circle border " style="width: 85px; height: 85px; object-fit: cover; border: 2.5px solid #fff;">
                                    @else
                                        <img id="avatar-preview-img" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4f46e5&color=fff" alt="Avatar" class="rounded-circle border " style="width: 85px; height: 85px; object-fit: cover; border: 2.5px solid #fff;">
                                    @endif
                                </div>
                                <div class="col-sm-10">
                                    <label class="vp-label mb-1">Profile Image / Avatar</label>
                                    <input type="file" name="profile_image" id="avatar-file-input" class="form-control rounded-3" style="font-size: 0.85rem;" accept="image/*">
                                    <span class="text-muted extra-small mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Format: PNG, JPG, WEBP. Max size: 2MB.</span>
                                </div>
                            </div>

                            <input type="hidden" name="company_name" value="{{ old('company_name', $profile->company_name) }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="vp-input" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">User Login Email</label>
                                        <input type="email" class="vp-input bg-light border-0" value="{{ $user->email }}" readonly disabled style="cursor: not-allowed;">
                                        <span class="text-muted extra-small mt-1 d-block"><i class="bi bi-lock me-1"></i>Login email cannot be modified.</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Helpline Phone Number</label>
                                        <input type="text" name="phone" value="{{ old('phone', $profile->phone) }}" class="vp-input" placeholder="e.g. +91 99999 99999">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">WhatsApp Contact Number</label>
                                        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $profile->whatsapp_number) }}" class="vp-input" placeholder="e.g. +91 99999 99999">
                                    </div>
                                </div>

                                <div class="col-12 my-2 border-top pt-3">
                                    <h6 class="fw-bold text-muted small text-uppercase mb-3" style="letter-spacing:0.04em;"><i class="bi bi-geo-alt me-1"></i>Business Address</h6>
                                </div>

                                <div class="col-12">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Street Address</label>
                                        <input type="text" name="address" value="{{ old('address', $profile->address) }}" class="vp-input" placeholder="Enter complete office street address">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="vp-form-group">
                                        <label class="vp-label">City</label>
                                        <input type="text" name="city" value="{{ old('city', $profile->city) }}" class="vp-input" placeholder="City">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="vp-form-group">
                                        <label class="vp-label">State</label>
                                        <input type="text" name="state" value="{{ old('state', $profile->state) }}" class="vp-input" placeholder="State">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Pincode</label>
                                        <input type="text" name="pincode" value="{{ old('pincode', $profile->pincode) }}" class="vp-input" placeholder="Pincode">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group mb-0">
                                        <label class="vp-label">Country</label>
                                        <input type="text" name="country" value="{{ old('country', $profile->country ?: 'India') }}" class="vp-input" placeholder="Country">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-subscriber mt-4">
                                <i class="bi bi-save-fill"></i> Save Personal Details
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tab 3: Subscription & Invoices --}}
            <div class="tab-pane fade" id="content-subscription" role="tabpanel" aria-labelledby="tab-subscription">
                <!-- Plan Summary -->
                <div class="vp-card border-0  rounded-4 mb-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: white; border: none;">
                    <div class="vp-card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <span class="badge rounded-pill bg-white bg-opacity-20 text-white mb-2 px-3 py-1 fw-bold" style="font-size:0.72rem;">Active Subscription Plan</span>
                                <h3 class="fw-bold mb-1" style="font-family:'Outfit', sans-serif;">
                                    {{ $subscription->plan?->name ?? 'Free Tier Active' }}
                                </h3>
                                <p class="opacity-75 small mb-0 mt-2">
                                    @if($subscription)
                                        Billing started: {{ $subscription->starts_at ? $subscription->starts_at->format('M d, Y') : 'N/A' }}
                                    @else
                                        You are currently on a limited storefront plan.
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                @if($subscription)
                                    <div class="bg-white bg-opacity-10 p-3 rounded-4 d-inline-block border" style="border-color: rgba(255,255,255,0.08) !important;">
                                        <div class="text-white-50 small text-uppercase fw-bold" style="font-size:0.65rem;">Remaining Period</div>
                                        <div class="h2 fw-bold text-warning mb-0 mt-0.5">{{ $subscription->daysRemaining() }} Days</div>
                                        <div class="text-white-50 extra-small mt-0.5">Renews: {{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y') : 'N/A' }}</div>
                                    </div>
                                @else
                                    <a href="{{ route('subscriber.subscription.plans') }}" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">Upgrade Now</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice History -->
                <div class="vp-card border-0  rounded-4">
                    <div class="vp-card-header bg-transparent py-3.5 px-4 border-bottom">
                        <h5 class="vp-card-title"><i class="bi bi-credit-card me-2 text-primary"></i>Invoices & Billing History</h5>
                    </div>
                    <div class="vp-card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 border-0 small text-muted">Invoice No.</th>
                                        <th class="border-0 small text-muted">Amount</th>
                                        <th class="border-0 small text-muted">Paid Date</th>
                                        <th class="border-0 small text-muted">Billing Cycle</th>
                                        <th class="border-0 small text-muted">Status</th>
                                        <th class="text-end pe-4 border-0 small text-muted">Receipt</th>
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
                                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-2.5 py-1 extra-small">Paid</span>
                                                @else
                                                    <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-2.5 py-1 extra-small">Unpaid</span>
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
                                                <i class="bi bi-credit-card fs-1 opacity-25"></i>
                                                <h6 class="fw-bold text-dark mt-2 mb-1">No Invoices Found</h6>
                                                <p class="text-muted extra-small mb-0">No payment receipts are currently registered in your log.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>



            {{-- Tab 5: Account Security --}}
            <div class="tab-pane fade" id="content-security" role="tabpanel" aria-labelledby="tab-security">
                <div class="vp-card border-0  rounded-4">
                    <div class="vp-card-header bg-transparent py-3.5 px-4 border-bottom">
                        <h5 class="vp-card-title"><i class="bi bi-shield-lock me-2 text-primary"></i>Update Account Password</h5>
                    </div>
                    <div class="vp-card-body p-4">
                        <form action="{{ route('subscriber.profile.password') }}" method="POST">
                            @csrf
                            
                            <div class="vp-form-group">
                                <label class="vp-label">Current Password</label>
                                <input type="password" name="current_password" class="vp-input" placeholder="Enter current login password" required>
                            </div>

                            <div class="vp-form-group">
                                <label class="vp-label">New Password</label>
                                <input type="password" name="password" class="vp-input" placeholder="Min 8 characters required" required>
                            </div>

                            <div class="vp-form-group">
                                <label class="vp-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="vp-input" placeholder="Confirm your new password" required>
                            </div>

                            <button type="submit" class="btn-subscriber mt-4">
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
                            const img = document.createElement('img');
                            img.id = 'logo-preview-img';
                            img.src = e.target.result;
                            img.alt = 'Logo';
                            img.className = 'rounded-4 border ';
                            img.style.width = '85px';
                            img.style.height = '85px';
                            img.style.objectFit = 'cover';
                            img.style.border = '2.5px solid #fff';
                            previewPlaceholder.parentNode.replaceChild(img, previewPlaceholder);
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        // Avatar Upload Preview
        const avatarInput = document.getElementById('avatar-file-input');
        const avatarImg = document.getElementById('avatar-preview-img');

        if (avatarInput && avatarImg) {
            avatarInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarImg.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endsection
