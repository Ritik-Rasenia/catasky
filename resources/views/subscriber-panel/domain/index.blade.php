@extends('subscriber-panel.layouts.app')

@section('title', 'Custom Domain Management')

@push('css')
<style>
    .premium-badge {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-radius: 100px;
        padding: 5px 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid transparent;
    }
    .badge-active {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border-color: rgba(16, 185, 129, 0.2);
    }
    .badge-pending {
        background-color: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
        border-color: rgba(245, 158, 11, 0.2);
    }
    .badge-suspended {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.2);
    }
    .stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        transition: all 0.25s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
    }
    .copy-btn {
        background: transparent;
        border: none;
        color: #64748b;
        padding: 4px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .copy-btn:hover {
        color: #4F46E5;
        background: #f1f5f9;
    }
    .font-monospace {
        font-family: 'Courier New', Courier, monospace !important;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<!-- Title & Breadcrumbs Card -->
<div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px; background: #ffffff; font-family: 'Outfit', sans-serif;">
    <h1 class="fw-bold mb-2" style="font-size: 2.25rem; color: #0f172a; letter-spacing: -0.5px;">Custom Domain Management</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0" style="font-size: 0.9rem;">
            <li class="breadcrumb-item"><a href="{{ route('subscriber.dashboard') }}" class="text-secondary text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item text-secondary">Account</li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Custom Domain</li>
        </ol>
    </nav>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 mb-4" role="alert" style="border-radius:12px; background:#DCFCE7; color:#15803d; font-family:'Outfit',sans-serif; font-size:0.9rem;">
    <i class="bi bi-check-circle-fill me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert" style="border-radius:12px; background:#FEE2E2; color:#991B1B; font-family:'Outfit',sans-serif; font-size:0.9rem;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@php
    $dom = $domains->first();
@endphp

<!-- Statistics Overview -->
<div class="row g-3 mb-4" style="font-family: 'Outfit', sans-serif;">
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #e0e7ff; color: #4F46E5;">
                <i class="bi bi-gem fs-4"></i>
            </div>
            <div>
                <span class="text-secondary small d-block mb-0.5">Current Plan</span>
                <strong class="text-dark fs-5">Enterprise Plan</strong>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #dcfce7; color: #10B981;">
                <i class="bi bi-globe fs-4"></i>
            </div>
            <div>
                <span class="text-secondary small d-block mb-0.5">Domain Limit</span>
                <strong class="text-dark fs-5">1 Active Domain</strong>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center gap-3">
             <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #dcfce7; color: #10B981;">
                <i class="bi bi-link-45deg fs-4"></i>
            </div>
            <div class="text-truncate">
                <span class="text-secondary small d-block mb-0.5">Default Store URL</span>
                @php
                $profile = auth()->user()->subscriberProfile;
                $visitUrl = $profile && $profile->company_slug ? route('store.public', $profile->company_slug) : route('home');
                
                @endphp
                <a href="{{ $visitUrl }}" target="_blank" class="text-primary fw-bold text-decoration-none text-truncate d-block" style="font-size:0.95rem;">
                    https://catasky.com/{{ $user->subscriberProfile->company_slug }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4" style="font-family: 'Outfit', sans-serif;">
    <!-- Left Column: Map Custom Domain -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #ffffff;">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-start">
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: #dcfce7; color: #15803d;">
                    <i class="bi bi-plus-circle-fill"></i>
                </div>
                <h5 class="card-title fw-bold text-dark mb-0" style="font-size: 1.1rem;">Map Custom Domain</h5>
            </div>
            
            <div class="card-body p-4">
                @if($dom)
                    {{-- Maximum Limit Reached Block --}}
                    <div class="d-flex flex-column align-items-center justify-content-center border border-primary border-opacity-10 rounded-4 p-4 text-center" style="background-color: #f8fafc; border-style: dashed !important; min-height: 250px;">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #e0e7ff; color: #4338ca;">
                            <i class="bi bi-info-circle fs-3"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 1rem;">Maximum Limit Reached</h6>
                        <p class="text-secondary small mb-0 px-2" style="line-height: 1.6;">
                            Your Enterprise subscription plan supports 1 active custom domain route mapping. Release your current domain to configure a new one.
                        </p>
                    </div>
                @else
                    {{-- Form to configure domain --}}
                    <form action="{{ route('subscriber.domain.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small text-uppercase mb-2">Domain Name</label>
                            <input type="text" name="domain" placeholder="www.mycompany.com" class="form-control py-3 px-3 @error('domain') is-invalid @enderror" value="{{ old('domain') }}" required style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; color: #0f172a; font-size: 0.95rem;">
                            @error('domain') <div class="invalid-feedback mt-2 small">{{ $message }}</div> @enderror
                            <span class="text-muted d-block mt-2 small" style="font-size: 0.75rem;">Input Example: www.mycompany.com (Use lowercase).</span>
                        </div>

                        <button type="submit" class="btn w-100 py-3 text-white fw-bold d-flex align-items-center justify-content-center gap-2" style="background: #4F46E5; border: none; border-radius: 12px; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);">
                            <i class="bi bi-cloud-arrow-up-fill fs-5"></i> Save Domain
                        </button>
                    </form>
                @endif

                {{-- Store URL Info Section --}}
                @if($dom)
                <div class="mt-4 pt-4 border-top" style="border-color: #f1f5f9 !important;">
                    <h6 class="fw-bold text-dark mb-3" style="font-size:0.9rem;"><i class="bi bi-link-45deg text-primary me-1"></i>Custom Domain URL</h6>
                    
                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1">Routed Storefront URL:</span>
                        <a href="https://{{ $dom->domain }}" target="_blank" class="text-success text-decoration-none fw-semibold d-inline-flex align-items-center gap-1" style="font-size:0.9rem;">
                            https://{{ $dom->domain }} <i class="bi bi-box-arrow-up-right" style="font-size:0.75rem;"></i>
                        </a>
                    </div>

                    @if($dom->status === 'pending_dns')
                        <div class="p-3 rounded-3 mt-3 text-warning border border-warning border-opacity-20 d-flex gap-2" style="background-color: #fffbeb; font-size:0.78rem; line-height:1.5;">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-6"></i>
                            <span>Your custom domain is not active until DNS verification is completed.</span>
                        </div>
                    @elseif($dom->status === 'suspended')
                        <div class="p-3 rounded-3 mt-3 text-danger border border-danger border-opacity-20 d-flex gap-2" style="background-color: #fef2f2; font-size:0.78rem; line-height:1.5;">
                            <i class="bi bi-exclamation-octagon-fill text-danger fs-6"></i>
                            <span>This custom domain mapping has been suspended. Please renew your Enterprise subscription to reactivate routing.</span>
                        </div>
                    @else
                        <div class="p-3 rounded-3 mt-3 text-success border border-success border-opacity-20 d-flex gap-2" style="background-color: #f0fdf4; font-size:0.78rem; line-height:1.5;">
                            <i class="bi bi-patch-check-fill text-success fs-6"></i>
                            <span>Your custom domain is active! Enterprise catalog visitors will be automatically routed here.</span>
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Active Domains -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #ffffff;">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: #e0e7ff; color: #4f46e5;">
                        <i class="bi bi-hdd-network-fill"></i>
                    </div>
                    <h5 class="card-title fw-bold text-dark mb-0" style="font-size: 1.1rem;">Active Domains</h5>
                </div>
                <span class="badge px-3 py-2 small fw-semibold" style="border-radius: 100px; background-color: #dcfce7; color: #15803d; font-size: 0.75rem;">Enterprise Enabled</span>
            </div>

            <div class="card-body p-4">
                @if(!$dom)
                    {{-- Configure Domain First Placeholder --}}
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-5" style="min-height: 350px;">
                        <div class="rounded-circle bg-light p-3 mb-3 text-secondary d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background-color: #f1f5f9;">
                            <i class="bi bi-globe fs-2"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Configure Domain First</h6>
                        <p class="text-secondary small mb-0 px-4" style="max-width: 450px; line-height: 1.6;">
                            Add your custom domain on the left to generate customized CNAME, A records and TXT verification parameters.
                        </p>
                    </div>
                @else
                    {{-- Domain Status Block --}}
                    <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h4 class="fw-extrabold text-dark mb-1" style="font-size: 1.45rem;">{{ $dom->domain }}</h4>
                            <span class="text-secondary small">Added on: {{ $dom->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        
                        <div class="d-flex align-items-center gap-2">
                            <!-- DNS Status badge -->
                            @if($dom->dns_verified)
                                <span class="premium-badge badge-active"><i class="bi bi-check-circle-fill"></i> DNS Verified</span>
                            @else
                                <span class="premium-badge badge-pending"><i class="bi bi-hourglass-split"></i> Pending DNS</span>
                            @endif

                            <!-- SSL Status badge -->
                            @if($dom->ssl_status === 'active')
                                <span class="premium-badge badge-active"><i class="bi bi-lock-fill"></i> SSL Active</span>
                            @elseif($dom->ssl_status === 'provisioning')
                                <span class="premium-badge badge-pending"><i class="bi bi-hourglass-split"></i> SSL Provisioning</span>
                            @else
                                <span class="premium-badge badge-pending"><i class="bi bi-shield-slash"></i> SSL Pending</span>
                            @endif

                            <!-- Routing Status badge -->
                            @if($dom->status === 'active_routing')
                                <span class="premium-badge badge-active"><i class="bi bi-check-circle-fill"></i> Active Routing</span>
                            @elseif($dom->status === 'suspended')
                                <span class="premium-badge badge-suspended"><i class="bi bi-slash-circle-fill"></i> Suspended</span>
                            @else
                                <span class="premium-badge badge-pending"><i class="bi bi-arrow-right-short"></i> Routing Pending</span>
                            @endif
                        </div>
                    </div>

                    {{-- DNS Instructions Header --}}
                    <div class="mb-3 d-flex align-items-center text-primary fw-semibold" style="font-size: 0.9rem;">
                        <i class="bi bi-gear-fill me-2" style="font-size:1.1rem;"></i>
                        <span>DNS Record Requirements</span>
                    </div>
                    
                    <p class="text-secondary small mb-4" style="line-height: 1.6;">
                        Configure the following records inside your domain registrar's DNS management panel (GoDaddy, Hostinger, Cloudflare etc.) to establish ownership and route storefront requests.
                    </p>

                    {{-- DNS Table --}}
                    <div class="table-responsive mb-4 rounded-3 border" style="border-color: #f1f5f9 !important;">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead>
                                <tr class="table-light text-secondary small fw-bold" style="background-color:#f8fafc; border-bottom: 1px solid #f1f5f9;">
                                    <th class="ps-3 py-2.5">TYPE</th>
                                    <th class="py-2.5" style="width: 25%;">HOST / KEY</th>
                                    <th class="py-2.5" style="width: 50%;">VALUE / POINTS TO</th>
                                    <th class="pe-3 py-2.5 text-end" style="width: 15%;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- TXT record --}}
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td class="ps-3 py-3 fw-bold text-dark">TXT</td>
                                    <td class="py-3 font-monospace text-secondary fw-semibold">
                                        <span>_catasky-challenge</span>
                                        <button class="copy-btn ms-1" id="copy-txt-key" onclick="copyValue('_catasky-challenge', 'copy-txt-key')" title="Copy Host Key"><i class="bi bi-clipboard"></i></button>
                                    </td>
                                    <td class="py-3 font-monospace text-secondary">
                                        <span title="{{ $dom->dns_txt_value }}">{{ Str::limit($dom->dns_txt_value, 20) }}</span>
                                        <button class="copy-btn ms-1" id="copy-txt-val" onclick="copyValue('{{ $dom->dns_txt_value }}', 'copy-txt-val')" title="Copy Value"><i class="bi bi-clipboard"></i></button>
                                    </td>
                                    <td class="pe-3 py-3 text-end">
                                        @if($dom->dns_verified)
                                            <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> verified</span>
                                        @else
                                            <span class="text-muted small">pending</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- CNAME --}}
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td class="ps-3 py-3 fw-bold text-dark">CNAME</td>
                                    <td class="py-3 font-monospace text-secondary fw-semibold">
                                        <span>www</span>
                                        <button class="copy-btn ms-1" id="copy-cname-key" onclick="copyValue('www', 'copy-cname-key')" title="Copy Host"><i class="bi bi-clipboard"></i></button>
                                    </td>
                                    <td class="py-3 font-monospace text-secondary">
                                        <span>app.catasky.com</span>
                                        <button class="copy-btn ms-1" id="copy-cname-val" onclick="copyValue('app.catasky.com', 'copy-cname-val')" title="Copy Destination"><i class="bi bi-clipboard"></i></button>
                                    </td>
                                    <td class="pe-3 py-3 text-end">
                                        @if($dom->dns_verified)
                                            <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> verified</span>
                                        @else
                                            <span class="text-muted small">pending</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- A Record --}}
                                <tr>
                                    <td class="ps-3 py-3 fw-bold text-dark">A Record</td>
                                    <td class="py-3 font-monospace text-secondary fw-semibold">
                                        <span>@</span>
                                        <button class="copy-btn ms-1" id="copy-a-key" onclick="copyValue('@', 'copy-a-key')" title="Copy Host"><i class="bi bi-clipboard"></i></button>
                                    </td>
                                    <td class="py-3 font-monospace text-secondary">
                                        <span>159.89.172.11</span>
                                        <button class="copy-btn ms-1" id="copy-a-val" onclick="copyValue('159.89.172.11', 'copy-a-val')" title="Copy IP Address"><i class="bi bi-clipboard"></i></button>
                                    </td>
                                    <td class="pe-3 py-3 text-end">
                                        @if($dom->dns_verified)
                                            <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> verified</span>
                                        @else
                                            <span class="text-muted small">pending</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Status Banner --}}
                    <div class="mb-4">
                        @if($dom->status === 'active_routing')
                            <div class="p-3.5 rounded-3 text-success border border-success border-opacity-20 d-flex align-items-start gap-2.5" style="background-color: #f0fdf4; font-size: 0.85rem; line-height: 1.6;">
                                <i class="bi bi-patch-check-fill fs-5 text-success"></i>
                                <div>
                                    <strong class="d-block mb-0.5" style="font-weight:700;">Your custom domain is active!</strong>
                                    CNAME and IP routing are connected. SSL certification is Active ✅. Traffic is routing directly to your store catalogue.
                                </div>
                            </div>
                        @elseif($dom->status === 'suspended')
                            <div class="p-3.5 rounded-3 text-danger border border-danger border-opacity-20 d-flex align-items-start gap-2.5" style="background-color: #fef2f2; font-size: 0.85rem; line-height: 1.6;">
                                <i class="bi bi-exclamation-octagon-fill fs-5 text-danger"></i>
                                <div>
                                    <strong class="d-block mb-0.5" style="font-weight:700;">Routing Suspended</strong>
                                    The custom domain has been disabled due to subscription expiration. Re-enable Enterprise plan to unlock routing.
                                </div>
                            </div>
                        @else
                            <div class="p-3.5 rounded-3 text-warning border border-warning border-opacity-20 d-flex align-items-start gap-2.5" style="background-color: #fffbeb; font-size: 0.85rem; line-height: 1.6;">
                                <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                                <div>
                                    <strong class="d-block mb-0.5" style="font-weight:700;">DNS Verification Required</strong>
                                    Complete the DNS settings in your domain host registrar, then click **Verify Domain** below to run automated ownership and SSL checks.
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Action buttons --}}
                    <div class="d-flex align-items-center justify-content-between gap-3 pt-3 border-top border-light-subtle">
                        @if($dom->status === 'pending_dns' || $dom->status === 'suspended')
                            <div class="d-flex align-items-center gap-2">
                                <button class="btn btn-success px-4 py-2.5 fw-bold d-flex align-items-center gap-1.5 shadow-sm" id="verify-dns-btn" data-id="{{ $dom->id }}" style="border-radius: 10px; font-size: 0.9rem; background-color: #10B981; border: none; color: #ffffff;">
                                    <i class="bi bi-shield-check-fill fs-5"></i> Verify Domain
                                </button>
                                <div class="spinner-border spinner-border-sm text-success d-none" id="verify-spinner" role="status"></div>
                            </div>
                        @else
                            <div class="d-flex align-items-center gap-1.5 text-success fw-semibold small">
                                <i class="bi bi-check-circle-fill fs-5"></i> Connected & Active
                            </div>
                        @endif

                        <form action="{{ route('subscriber.domain.destroy', $dom->id) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to remove this custom domain? This will immediately disable routing and revert your storefront to the fallback URL.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger px-4 py-2.5 fw-semibold d-flex align-items-center gap-1.5" style="border-radius: 10px; font-size: 0.9rem;">
                                <i class="bi bi-trash3-fill"></i> Remove Domain
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Clipboard Copy helper function
function copyValue(text, buttonId) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById(buttonId);
        if (btn) {
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
            setTimeout(() => {
                btn.innerHTML = originalIcon;
            }, 1500);
        }
    }).catch(err => {
        console.error('Failed to copy to clipboard: ', err);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const verifyBtn = document.getElementById('verify-dns-btn');
    const spinner = document.getElementById('verify-spinner');
    
    if (verifyBtn) {
        verifyBtn.addEventListener('click', function() {
            const domainId = this.getAttribute('data-id');
            
            verifyBtn.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            
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
                    window.alertService.successAlert('Domain Activated', data.message).then(() => {
                        window.location.reload();
                    });
                } else {
                    window.alertService.errorAlert('DNS Not Propagated', 'The DNS changes have not propagated yet. Please check your domain DNS settings and try again.');
                    verifyBtn.disabled = false;
                }
            })
            .catch(error => {
                if (spinner) spinner.classList.add('d-none');
                verifyBtn.disabled = false;
                console.error('Error:', error);
                window.alertService.errorAlert('DNS Verification Failed', 'Verification timed out or failed. Please check the entries in your DNS registrar dashboard.');
            });
        });
    }
});
</script>
@endsection
