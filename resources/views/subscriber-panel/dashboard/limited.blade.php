@extends('subscriber-panel.layouts.app')

@section('title', 'Setup Workspace')
@section('page-title', 'Workspace Setup')
@section('breadcrumb')
    <div style="font-size:0.85rem;color:var(--text-muted);font-weight:500;">
        Welcome to CATASKY onboarding workspace, <strong>{{ $user->name }}</strong>!
    </div>
@endsection

@section('content')
@php
    $storeStatus = $profile->store_status ?? 'draft';
    $complianceStatus = $profile->status ?? 'pending';
@endphp

{{-- Premium Onboarding Progress Timeline --}}
<div class="card border-0  rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.05) 0%, rgba(124, 58, 237, 0.03) 100%); border: 1px solid var(--border-color) !important;">
    <h5 class="fw-bold mb-3 brand-font"><i class="bi bi-rocket-takeoff-fill text-primary me-2"></i>B2B Subscriber Activation Sequence</h5>
    <div class="row g-3 text-center">
        <!-- Step 1: Verification -->
        <div class="col-md-3 col-6">
            <div class="p-3 rounded-3" style="background:rgba(16,185,129,0.06); border:1px solid rgba(16,185,129,0.18);">
                <div class="fs-4 text-success mb-1"><i class="bi bi-shield-fill-check"></i></div>
                <div class="fw-bold small text-dark">Step 1: Verified</div>
                <div class="smaller text-muted">Email OTP Completed</div>
            </div>
        </div>
        <!-- Step 2: Account Approved -->
        <div class="col-md-3 col-6">
            <div class="p-3 rounded-3" style="background:rgba(16,185,129,0.06); border:1px solid rgba(16,185,129,0.18);">
                <div class="fs-4 text-success mb-1"><i class="bi bi-patch-check-fill"></i></div>
                <div class="fw-bold small text-dark">Step 2: Active</div>
                <div class="smaller text-muted">B2B Compliance Approved</div>
            </div>
        </div>
        <!-- Step 3: Store Configuration -->
        <div class="col-md-3 col-6">
            <div class="p-3 rounded-3" style="
                @if($storeStatus === 'live') background:rgba(16,185,129,0.06); border:1px solid rgba(16,185,129,0.18);
                @elseif($storeStatus === 'pending') background:rgba(245,158,11,0.06); border:1px solid rgba(245,158,11,0.18);
                @else background:var(--surface-muted); border:1px solid var(--border-color); @endif
            ">
                <div class="fs-4 mb-1 
                    @if($storeStatus === 'live') text-success @elseif($storeStatus === 'pending') text-warning @else text-muted @endif">
                    <i class="bi @if($storeStatus === 'live') bi-shop-window @else bi-shop @endif"></i>
                </div>
                <div class="fw-bold small text-dark">Step 3: Store Setup</div>
                <div class="smaller text-muted">
                    @if($storeStatus === 'live') Approved &amp; Live @elseif($storeStatus === 'pending') Awaiting Review @else Branding &amp; Documents @endif
                </div>
            </div>
        </div>
        <!-- Step 4: Subscription & Activation -->
        <div class="col-md-3 col-6">
            <div class="p-3 rounded-3" style="background:var(--surface-muted); border:1px solid var(--border-color); opacity: 0.65;">
                <div class="fs-4 text-muted mb-1"><i class="bi bi-credit-card-2-front-fill"></i></div>
                <div class="fw-bold small text-dark">Step 4: Subscription</div>
                <div class="smaller text-muted">Unlock Full Dashboard</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Stage 1: Configure Store --}}
    @if($storeStatus === 'draft' || $storeStatus === 'rejected')
    <div class="col-xl-8 col-12 mx-auto">
        <div class="card border-0  rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h4 class="fw-bold mb-1 text-dark brand-font">Configure Your B2B Store &amp; Branding 🎨</h4>
                <p class="text-muted small">Set up your catalog branding and upload business proof to request store activation.</p>
            </div>
            
            <div class="card-body p-4 pt-2">
                @if($storeStatus === 'rejected')
                <div class="alert alert-danger d-flex align-items-center gap-3 mb-4 rounded-3 border-0 py-2 px-3 small" style="background:rgba(239,68,68,0.06);color:#ef4444;">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>
                        <strong>Store Review Rejected:</strong> {{ $profile->suspension_reason ?? 'Your profile did not meet standard catalog compliance rules. Please review and update details below.' }}
                    </div>
                </div>
                @endif

                <form action="{{ route('subscriber.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-secondary small" for="name">Your Full Name *</label>
                            <input type="text" name="name" id="name" class="form-control rounded-3" value="{{ old('name', $user->name) }}" required>
                        </div>
                        
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-secondary small" for="company_name">Company Name *</label>
                            <input type="text" name="company_name" id="company_name" class="form-control rounded-3" value="{{ old('company_name', $profile->company_name) }}" required>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-secondary small" for="phone">Phone Number</label>
                            <input type="tel" name="phone" id="phone" class="form-control rounded-3" value="{{ old('phone', $profile->phone) }}">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-secondary small" for="whatsapp_number">WhatsApp Number</label>
                            <input type="tel" name="whatsapp_number" id="whatsapp_number" class="form-control rounded-3" value="{{ old('whatsapp_number', $profile->whatsapp_number) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small" for="website">Company Website</label>
                            <input type="url" name="website" id="website" class="form-control rounded-3" placeholder="https://example.com" value="{{ old('website', $profile->website) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small" for="gst_number">GSTIN / Compliance Number *</label>
                            <input type="text" name="gst_number" id="gst_number" class="form-control rounded-3" placeholder="e.g. 22AAAAA0000A1Z5" value="{{ old('gst_number', $profile->gst_number) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small" for="address">Corporate Address</label>
                            <textarea name="address" id="address" class="form-control rounded-3" rows="2">{{ old('address', $profile->address) }}</textarea>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-secondary small" for="city">City</label>
                            <input type="text" name="city" id="city" class="form-control rounded-3" value="{{ old('city', $profile->city) }}">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-secondary small" for="state">State</label>
                            <input type="text" name="state" id="state" class="form-control rounded-3" value="{{ old('state', $profile->state) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small" for="logo">Store Logo / Branding Banner</label>
                            <input type="file" name="logo" id="logo" class="form-control rounded-3">
                            <span class="smaller text-muted d-block mt-1">Recommended: PNG or WEBP format. Maximum size: 2MB.</span>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-secondary small" for="primary_color">Primary Brand Color</label>
                            <div class="d-flex gap-2">
                                <input type="color" name="primary_color" id="primary_color" class="form-control form-control-color rounded-3 p-1" style="width:50px;height:38px;" value="{{ old('primary_color', $profile->primary_color ?? '#4F46E5') }}">
                                <input type="text" class="form-control rounded-3" id="primary_hex" value="{{ old('primary_color', $profile->primary_color ?? '#4F46E5') }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-secondary small" for="secondary_color">Secondary Brand Color</label>
                            <div class="d-flex gap-2">
                                <input type="color" name="secondary_color" id="secondary_color" class="form-control form-control-color rounded-3 p-1" style="width:50px;height:38px;" value="{{ old('secondary_color', $profile->secondary_color ?? '#7C3AED') }}">
                                <input type="text" class="form-control rounded-3" id="secondary_hex" value="{{ old('secondary_color', $profile->secondary_color ?? '#7C3AED') }}" readonly>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary small" for="bio">Corporate Bio / Description</label>
                            <textarea name="bio" id="bio" class="form-control rounded-3" rows="3" placeholder="Tell your customers about your team and catalog offerings.">{{ old('bio', $profile->bio) }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-bold text-white  mt-4" style="background:var(--primary-color); border:none;">
                        <i class="bi bi-shop-window me-2"></i> Submit Store Setup Request
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Stage 2: Store Awaiting Approval --}}
    @elseif($storeStatus === 'pending')
    <div class="col-xl-8 col-12 mx-auto">
        <div class="card border-0  rounded-4 p-5 text-center">
            <div class="radar-spinner mb-4">
                <div class="radar-circle"></div>
                <div class="radar-circle"></div>
                <div class="radar-circle"></div>
                <div class="radar-center">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            
            <h3 class="fw-bold mb-2 text-dark brand-font">Store Review In Progress ⚙️</h3>
            <p class="text-muted small mx-auto" style="max-width:480px;">
                You have successfully configured and submitted your catalog brand profile and compliance documents. Super Admin is currently reviewing your store logo, setup, and category layout.
            </p>

            <div class="p-3 bg-light rounded-4 text-start border d-inline-block mx-auto mb-4" style="max-width:420px; width:100%;">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-card-list me-1 text-primary"></i>Submitted Profile Summary</h6>
                <div class="small mb-1 text-muted"><strong>Brand Name:</strong> {{ $profile->company_name }}</div>
                @if($profile->gst_number)
                <div class="small mb-1 text-muted"><strong>GSTIN / Compliance:</strong> {{ $profile->gst_number }}</div>
                @endif
                <div class="small mb-1 text-muted"><strong>Subdomain Path:</strong> <span class="text-primary fw-semibold">/store/{{ $profile->company_slug }}</span></div>
                <div class="small text-muted"><strong>Branding Scheme:</strong> 
                    <span class="badge" style="background:{{ $profile->primary_color }}; color:white;">{{ $profile->primary_color }}</span> 
                    <span class="badge" style="background:{{ $profile->secondary_color }}; color:white;">{{ $profile->secondary_color }}</span>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('subscriber.profile.edit') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-bold">
                    <i class="bi bi-pencil me-1"></i> Edit Submitted Setup
                </a>
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-4 fw-semibold" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Check Review Status
                </button>
            </div>
        </div>
    </div>
    @endif
</div>



@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const primaryCol = document.getElementById('primary_color');
    const primaryHex = document.getElementById('primary_hex');
    const secondaryCol = document.getElementById('secondary_color');
    const secondaryHex = document.getElementById('secondary_hex');

    if (primaryCol && primaryHex) {
        primaryCol.addEventListener('input', function(e) {
            primaryHex.value = e.target.value.toUpperCase();
        });
    }

    if (secondaryCol && secondaryHex) {
        secondaryCol.addEventListener('input', function(e) {
            secondaryHex.value = e.target.value.toUpperCase();
        });
    }
});
</script>
@endpush
@endsection
