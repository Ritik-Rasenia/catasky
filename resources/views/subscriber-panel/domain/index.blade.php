@extends('subscriber-panel.layouts.app')

@section('title', 'Custom Domain Routing')

@section('content')
<div class="vp-page-header">
    <div>
        <h1 class="vp-page-title">Custom Domain Routing</h1>
        <div class="vp-breadcrumb">
            <a href="{{ route('subscriber.dashboard') }}">Dashboard</a> &nbsp;/&nbsp; <a href="{{ route('subscriber.subscription.index') }}">Billing</a> &nbsp;/&nbsp; <span>Custom Domain</span>
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

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius:12px; background:#FEE2E2; color:#991B1B;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(!$isEnterprise)
{{-- Premium Upgrade Prompt for Non-Enterprise Users --}}
<div class="vp-card overflow-hidden relative" style="border: 1px solid rgba(255, 255, 255, 0.08); background: rgba(30, 27, 75, 0.45); backdrop-filter: blur(16px);">
    <div class="p-5 text-center">
        <div class="mb-4 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 20px; background: linear-gradient(135deg, #4F46E5, #7C3AED); box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);">
            <i class="bi bi-globe fs-1 text-white animate-pulse"></i>
        </div>
        
        <h2 class="fw-bold mb-3" style="font-family: 'Outfit', sans-serif; background: linear-gradient(to right, #FFFFFF, #CBD5E1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Enterprise White-Label Custom Domain Routing
        </h2>
        
        <p class="text-muted mx-auto mb-4" style="max-width: 600px; font-size: 0.95rem; line-height: 1.6;">
            Host your B2B digital storefront and catalogs directly under your own brand's custom domain (e.g. <strong class="text-light">catalog.mybrand.com</strong>). Completely eliminate CataSky references for a seamless enterprise experience.
        </p>

        <div class="row justify-content-center text-start g-3 mb-5 mx-auto" style="max-width: 750px;">
            <div class="col-md-4">
                <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                    <h6 class="fw-bold text-light mb-1.5"><i class="bi bi-patch-check-fill text-primary me-2"></i>Brand Ownership</h6>
                    <span class="text-muted" style="font-size:0.75rem;">Build trust by keeping visitors entirely on your official company domains.</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                    <h6 class="fw-bold text-light mb-1.5"><i class="bi bi-shield-fill-check text-primary me-2"></i>Auto SSL Security</h6>
                    <span class="text-muted" style="font-size:0.75rem;">Let us provision fully managed Let's Encrypt SSL certificates automatically.</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                    <h6 class="fw-bold text-light mb-1.5"><i class="bi bi-eye-slash-fill text-primary me-2"></i>100% White-Label</h6>
                    <span class="text-muted" style="font-size:0.75rem;">Mask all branding and footers, creating a fully dedicated customer portal.</span>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-center gap-3">
            <a href="{{ route('subscriber.subscription.plans') }}" class="btn-subscriber py-3 px-4 fs-6 justify-content-center" style="background: linear-gradient(135deg, #4F46E5, #7C3AED);">
                <i class="bi bi-gem me-1.5"></i> Upgrade to Enterprise Plan
            </a>
            <a href="{{ route('subscriber.subscription.index') }}" class="btn btn-outline-secondary py-3 px-4 fs-6" style="border-radius: 12px; border-color: rgba(255,255,255,0.15); color: white;">
                View Current Billing
            </a>
        </div>
    </div>
</div>
@else
{{-- Custom Domain Setup page for Enterprise users --}}
<div class="row g-4">
    <div class="col-lg-5">
        <div class="vp-card">
            <div class="vp-card-header">
                <h5 class="vp-card-title"><i class="bi bi-plus-circle-fill text-success me-2"></i>Map Custom Domain</h5>
            </div>
            
            <div class="vp-card-body">
                @if($domains->count() >= 1)
                    <div class="p-3 rounded-3 bg-light border text-center mb-2" style="border-style:dashed !important;">
                        <i class="bi bi-info-circle text-primary fs-3 mb-2 d-block"></i>
                        <span class="text-dark fw-bold mb-1 d-block" style="font-size:0.9rem;">Maximum Limit Reached</span>
                        <span class="text-muted d-block" style="font-size:0.78rem;">Your Enterprise subscription plan supports 1 active custom domain route mapping. Release your current domain to configure a new one.</span>
                    </div>
                @else
                    <form action="{{ route('subscriber.domain.store') }}" method="POST">
                        @csrf
                        
                        <div class="vp-form-group">
                            <label class="vp-label">Domain Name</label>
                            <input type="text" name="domain" placeholder="catalog.mybrand.com" class="vp-input @error('domain') is-invalid @enderror" value="{{ old('domain') }}" required>
                            @error('domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <span class="text-muted" style="font-size:0.75rem; display:block; margin-top:6px;">Do not include http://, https://, or paths. Use lowercase.</span>
                        </div>

                        <div class="p-3 rounded-3 mb-4" style="background: rgba(79, 70, 229, 0.05); border: 1px solid rgba(79, 70, 229, 0.15); font-size: 0.8rem; line-height: 1.5; color: #E2E8F0;">
                            <strong class="text-light d-block mb-1"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>Before Adding:</strong>
                            Make sure you own this domain and have access to configure DNS records with your registrar (e.g. GoDaddy, Namecheap, Cloudflare).
                        </div>

                        <button type="submit" class="btn-subscriber w-100 justify-content-center py-2.5">
                            <i class="bi bi-globe2 me-1.5"></i> Request Custom Domain Route
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="vp-card">
            <div class="vp-card-header d-flex align-items-center justify-content-between">
                <h5 class="vp-card-title"><i class="bi bi-hdd-network-fill text-primary me-2"></i>Active Domains</h5>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1" style="border-radius:20px; font-size:0.75rem;">Enterprise Enabled</span>
            </div>
            
            <div class="vp-card-body p-0">
                @if($domains->isEmpty())
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-hdd-network fs-1 mb-3 text-secondary d-block"></i>
                        <span>No custom domains configured yet. Add your brand domain on the left to begin setup.</span>
                    </div>
                @else
                    @foreach($domains as $dom)
                        <div class="p-4 border-bottom border-secondary-subtle">
                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1 text-light" style="font-family:'Outfit',sans-serif;">{{ $dom->domain }}</h5>
                                    <span class="text-muted" style="font-size:0.75rem;">Added on: {{ $dom->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if($dom->status === 'active')
                                        <span class="badge bg-success text-white px-2.5 py-1" style="border-radius:10px; font-size:0.75rem;"><i class="bi bi-check-circle me-1"></i> Active Routing</span>
                                    @elseif($dom->status === 'approved')
                                        <span class="badge bg-info text-white px-2.5 py-1" style="border-radius:10px; font-size:0.75rem;"><i class="bi bi-shield-check me-1"></i> DNS Verified</span>
                                    @elseif($dom->status === 'rejected')
                                        <span class="badge bg-danger text-white px-2.5 py-1" style="border-radius:10px; font-size:0.75rem;"><i class="bi bi-x-circle me-1"></i> Blocked</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2.5 py-1" style="border-radius:10px; font-size:0.75rem;"><i class="bi bi-clock-history me-1"></i> Pending Verification</span>
                                    @endif
                                    
                                    @if($dom->ssl_status === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="border-radius:8px; font-size:0.7rem;"><i class="bi bi-lock-fill"></i> SSL Active</span>
                                    @else
                                        <span class="badge bg-secondary text-white px-2 py-0.5" style="border-radius:8px; font-size:0.7rem;"><i class="bi bi-lock"></i> SSL Pending</span>
                                    @endif
                                </div>
                            </div>

                            {{-- DNS Configuration Instructions Box --}}
                            <div class="p-3.5 rounded-3 mb-4" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); font-size:0.8rem;">
                                <h6 class="fw-bold text-light mb-2.5" style="font-size:0.85rem;"><i class="bi bi-gear-wide-connected me-1.5 text-primary"></i>DNS Record Requirements</h6>
                                <p class="text-muted mb-3" style="line-height:1.5;">To verify ownership and establish custom path routing, please configure the following records inside your domain DNS panel:</p>
                                
                                <div class="table-responsive">
                                    <table class="table table-dark table-borderless align-middle mb-0" style="--bs-table-bg: transparent; font-size: 0.78rem;">
                                        <thead>
                                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.08); color: var(--text-muted);">
                                                <th class="ps-0 py-2">Type</th>
                                                <th class="py-2">Host / Key</th>
                                                <th class="py-2">Value / Points To</th>
                                                <th class="pe-0 py-2 text-end">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.04);">
                                                <td class="ps-0 py-2.5 text-success fw-bold">TXT</td>
                                                <td class="py-2.5 font-monospace text-light">{{ $dom->dns_txt_key }}</td>
                                                <td class="py-2.5 font-monospace text-light">{{ Str::limit($dom->dns_txt_value, 28) }}</td>
                                                <td class="pe-0 py-2.5 text-end">
                                                    @if($dom->dns_verified)
                                                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> verified</span>
                                                    @else
                                                        <span class="text-warning"><i class="bi bi-exclamation-circle-fill"></i> unverified</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-0 py-2.5 text-info fw-bold">CNAME</td>
                                                <td class="py-2.5 font-monospace text-light">{{ parse_url($dom->domain, PHP_URL_HOST) ?: $dom->domain }}</td>
                                                <td class="py-2.5 font-monospace text-light">{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'catasky.com' }}</td>
                                                <td class="pe-0 py-2.5 text-end">
                                                    @if($dom->status === 'active')
                                                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> routing active</span>
                                                    @else
                                                        <span class="text-muted"><i class="bi bi-arrow-repeat spin"></i> listening</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            @if(!$dom->dns_verified)
                                <div class="d-flex align-items-center gap-2">
                                    <button class="btn-subscriber btn-sm px-3.5 py-2" id="verify-dns-btn" data-id="{{ $dom->id }}">
                                        <i class="bi bi-shield-check me-1"></i> Verify CNAME & TXT DNS Records
                                    </button>
                                    <div class="spinner-border spinner-border-sm text-primary d-none" id="verify-spinner" role="status"></div>
                                </div>
                            @else
                                <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15); font-size:0.8rem; color:#A7F3D0;">
                                    <i class="bi bi-shield-check-fill text-success fs-5"></i>
                                    <span>Your custom domain is verified! The Super Admin will review your SSL provisioning and activate global custom routing shortly.</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const verifyBtn = document.getElementById('verify-dns-btn');
    const spinner = document.getElementById('verify-spinner');
    
    if (verifyBtn) {
        verifyBtn.addEventListener('click', function() {
            const domainId = this.getAttribute('data-id');
            
            verifyBtn.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            
            // Post verify request to simulator endpoint
            fetch("{{ route('subscriber.domain.verify') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ domain_id: domainId })
            })
            .then(response => response.json())
            .then(data => {
                if (spinner) spinner.classList.add('d-none');
                
                if (data.success) {
                    window.alertService.successAlert('Domain verified', data.message).then(() => {
                        window.location.reload();
                    });
                } else {
                    window.alertService.errorAlert('DNS verification failed', 'Please wait and try again.');
                    verifyBtn.disabled = false;
                }
            })
            .catch(error => {
                if (spinner) spinner.classList.add('d-none');
                verifyBtn.disabled = false;
                console.error('Error:', error);
                window.alertService.errorAlert('Verification error', 'An error occurred during DNS verification.');
            });
        });
    }
});
</script>
@endsection
