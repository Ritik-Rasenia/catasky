<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $globalSetting = \App\Models\Setting::first();
        $siteTitle = $globalSetting->site_title ?? 'Catasky';
        $faviconUrl = ($globalSetting && $globalSetting->favicon) ? asset('uploads/settings/' . $globalSetting->favicon) : asset('uploads/fav.png');
        $logoUrl = ($globalSetting && $globalSetting->logo) ? asset('uploads/settings/' . $globalSetting->logo) : null;
    @endphp
    <title>Subscriber Registration | {{ $siteTitle }}</title>
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4F46E5;
            --primary-glow: rgba(79, 70, 229, 0.4);
            --secondary: #7C3AED;
            --secondary-glow: rgba(124, 58, 237, 0.4);
            --dark-bg: #0B091A;
            --card-bg: rgba(20, 18, 43, 0.45);
            --text-main: #FFFFFF;
            --text-muted: rgba(255, 255, 255, 0.55);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0F172A;
            overflow-x: hidden;
        }

        /* ── Left Brand Panel ── */
        .brand-panel {
            width: 44%;
            min-height: 100vh;
            background: linear-gradient(150deg, #1E1B4B 0%, #2D1B69 50%, #0F172A 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 52px;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        /* Ambient Glowing Orbs */
        .brand-panel::before {
            content: '';
            position: absolute;
            top: -100px; left: -100px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(79,70,229,0.25) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -120px; right: -80px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(124,58,237,0.2) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Grid dots decoration */
        .grid-dots {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            position: relative;
            z-index: 2;
        }
        .brand-logo-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 800; font-size: 1.3rem;
            box-shadow: 0 8px 20px rgba(79,70,229,0.4);
        }
        .brand-logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem; font-weight: 800; color: white;
            letter-spacing: -0.5px;
        }
        .brand-logo-sub {
            font-size: 0.6rem; color: rgba(255,255,255,0.4);
            letter-spacing: 1.5px; text-transform: uppercase;
            font-weight: 600; margin-top: -2px;
        }

        .brand-content {
            position: relative; z-index: 2; flex: 1;
            display: flex; flex-direction: column; justify-content: center;
            padding: 40px 0;
        }

        .brand-headline {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            font-weight: 800; color: white; line-height: 1.15;
            letter-spacing: -0.03em; margin-bottom: 20px;
        }

        .brand-sub {
            font-size: 0.95rem; color: rgba(255,255,255,0.55);
            line-height: 1.7; max-width: 360px; margin-bottom: 40px;
        }

        .feature-list { display: flex; flex-direction: column; gap: 16px; }

        .feature-item {
            display: flex; align-items: center; gap: 14px;
        }
        .feature-dot {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 1.1rem;
        }
        .feature-item p { font-size: 0.875rem; color: rgba(255,255,255,0.8); font-weight: 500; margin: 0; }
        .feature-item span { font-size: 0.72rem; color: rgba(255,255,255,0.4); }

        /* Mockup card on brand panel */
        .mini-mockup {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px; padding: 20px;
            position: relative; z-index: 2;
        }
        .mini-mockup-label {
            font-size: 0.68rem; color: rgba(255,255,255,0.35);
            text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 12px;
        }
        .mini-stat {
            background: rgba(255,255,255,0.05); border-radius: 12px;
            padding: 10px 14px; margin-bottom: 8px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .mini-stat:last-child { margin-bottom: 0; }
        .mini-stat-label { font-size: 0.75rem; color: rgba(255,255,255,0.5); }
        .mini-stat-val { font-size: 0.875rem; font-weight: 700; color: white; }

        /* ── Right Form Panel ── */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 40px;
            background: #0F172A;
            min-height: 100vh;
            overflow-y: auto;
        }

        /* Prevent vertical scroll on standard screens, center elements beautifully */
        @media (min-height: 620px) and (min-width: 992px) {
            body {
                height: 100vh;
                overflow: hidden;
            }
            .brand-panel {
                height: 100vh;
            }
            .form-panel {
                height: 100vh;
                overflow-y: auto; /* allows scroll internally inside the pane if overflow occurs */
            }
        }

        .form-box {
            width: 100%; max-width: 580px;
            padding: 20px 0;
        }

        .form-header { margin-bottom: 16px; }
        .form-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.55rem; font-weight: 800; color: white; letter-spacing: -0.03em; margin-bottom: 4px;
        }
        .form-header p { font-size: 0.85rem; color: rgba(255,255,255,0.45); }
        .form-header a { color: #818CF8; text-decoration: none; font-weight: 600; }
        .form-header a:hover { color: #A5B4FC; }

        .form-floating-label {
            font-size: 0.72rem; font-weight: 600; letter-spacing: 0.3px;
            color: rgba(255,255,255,0.5); text-transform: uppercase; margin-bottom: 4px;
            display: block;
        }

        /* Compact field styling for two columns */
        .form-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            color: white;
            padding: 8px 12px;
            font-size: 0.84rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }
        select.form-input {
            background-color: #111827 !important;
            color: white !important;
        }
        select.form-input option {
            background-color: #111827;
            color: white;
        }
        .form-input::placeholder { color: rgba(255,255,255,0.2); }
        .form-input:focus {
            border-color: rgba(79,70,229,0.6);
            background: rgba(79,70,229,0.06);
            box-shadow: 0 0 0 4px rgba(79,70,229,0.12);
        }
        .form-input.is-invalid {
            border-color: rgba(239,68,68,0.6);
            background: rgba(239,68,68,0.04);
        }
        .invalid-feedback { color: #FCA5A5; font-size: 0.72rem; margin-top: 4px; }

        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.25); font-size: 0.95rem; pointer-events: none;
        }
        .input-wrap .form-input { padding-left: 40px; }

        /* Section Dividers */
        .section-divider {
            font-size: 0.68rem;
            font-weight: 800;
            color: #818CF8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            padding-bottom: 4px;
            margin: 14px 0 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.06);
        }

        /* 14-Day Free Trial Badge */
        .trial-badge {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.04), rgba(5, 150, 105, 0.04));
            border: 1.5px dashed rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            padding: 8px 12px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .trial-badge .trial-icon { font-size: 1.15rem; }
        .trial-badge .trial-title { font-weight: 700; color: #4ADE80; font-size: 0.8rem; }
        .trial-badge .trial-text { color: var(--text-muted); font-size: 0.72rem; }

        .btn-signin {
            width: 100%;
            padding: 11px;
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white; border: none; border-radius: 12px;
            font-size: 0.95rem; font-weight: 700; font-family: 'Poppins', sans-serif;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(79,70,229,0.35);
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-top: 14px;
        }
        .btn-signin:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(79,70,229,0.45);
        }
        .btn-signin:active { transform: translateY(0); }

        .form-divider {
            display: flex; align-items: center; gap: 12px; margin: 12px 0;
        }
        .form-divider::before, .form-divider::after {
            content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.07);
        }
        .form-divider span { font-size: 0.75rem; color: rgba(255,255,255,0.3); }

        .form-footer {
            text-align: center; margin-top: 12px;
            font-size: 0.8rem; color: rgba(255,255,255,0.35);
        }
        .form-footer a { color: #818CF8; text-decoration: none; font-weight: 600; }
        .form-footer a:hover { color: #A5B4FC; }

        .error-alert {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 12px; padding: 10px 14px;
            color: #FCA5A5; font-size: 0.8rem; margin-bottom: 16px;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .brand-panel { display: none; }
            .form-panel { padding: 32px 24px; }
            body { background: #0F172A; }
        }
        @media (max-width: 480px) {
            .form-panel { padding: 24px 20px; }
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.4);
            font-size: 0.95rem;
            cursor: pointer;
            z-index: 10;
            transition: color 0.2s ease;
        }
        .password-toggle:hover {
            color: #FFFFFF;
        }
        .input-wrap-password .form-input {
            padding-right: 42px;
        }
    </style>
</head>
<body>

    <!-- ── Brand Panel ── -->
    <div class="brand-panel">
        <div class="grid-dots"></div>

        <!-- Logo -->
        <a href="{{ url('/') }}" class="brand-logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" style="max-height:150px;max-width:150px;object-fit:contain;margin:auto;">
            @else
                <div class="brand-logo-icon">Catasky</div>
            @endif
          
        </a>

        <!-- Main Content -->
        <div class="brand-content">
            <h1 class="brand-headline">
                The World's Most<br>Premium <span style="background:linear-gradient(135deg,#818CF8,#A78BFA);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Catalogue</span>
            </h1>
            <p class="brand-sub">
                Manage B2B products. Create stunning PDF catalogues. Share them instantly via WhatsApp. All in one place.
            </p>

            <div class="feature-list">
                @foreach([
                    ['icon'=>'bi-file-earmark-pdf-fill','color'=>'rgba(79,70,229,0.15)','icolor'=>'#818CF8','title'=>'PDF Catalogue Generation','sub'=>'Multi-page branded PDF in seconds'],
                    ['icon'=>'bi-whatsapp','color'=>'rgba(37,211,102,0.12)','icolor'=>'#4ADE80','title'=>'WhatsApp Sharing','sub'=>'Direct product cards to buyers'],
                    ['icon'=>'bi-bar-chart-line-fill','color'=>'rgba(6,182,212,0.12)','icolor'=>'#22D3EE','title'=>'Analytics Dashboard','sub'=>'Track views, engagement & more'],
                ] as $f)
                <div class="feature-item">
                    <div class="feature-dot" style="background:{{ $f['color'] }};">
                        <i class="bi {{ $f['icon'] }}" style="color:{{ $f['icolor'] }};"></i>
                    </div>
                    <div>
                        <p>{{ $f['title'] }}</p>
                        <span>{{ $f['sub'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

      
      
    </div>

    <!-- ── Form Panel ── -->
    <div class="form-panel">
        <div class="form-box">

            <!-- Mobile Logo -->
            <div class="d-lg-none text-center mb-4">
                <div style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" style="max-height:42px;max-width:150px;object-fit:contain;">
                    @else
                        <div style="width:38px;height:38px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1.1rem;">Catasky</div>
                    @endif
                </div>
            </div>

            <!-- Header -->
            <div class="form-header">
                <h2>Create Your Account 🚀</h2>
                <p>Start sharing premium catalogs</p>
            </div>

            <!-- 14-Day Free Trial Badge -->
            

            <!-- Error Alert -->
            @if ($errors->any())
            <div class="error-alert">
                <div class="d-flex align-items-center gap-2 mb-1 fw-bold">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Please correct the errors:</span>
                </div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Form -->
            <form action="{{ route('subscriber.register.submit') }}" method="POST" id="register-form">
                @csrf
                <input type="hidden" name="selected_plan" value="{{ $selectedPlan ?? 'business' }}">

                <div class="section-divider">
                    <i class="bi bi-person-badge-fill me-1"></i> Personal details
                </div>

                <div class="row g-2">
                    <div class="col-md-6 col-12">
                        <div class="mb-2">
                            <label class="form-floating-label" for="name">Full Name *</label>
                            <div class="input-wrap">
                                <i class="bi bi-person input-icon"></i>
                                <input type="text" name="name" id="name"
                                    class="form-input @error('name') is-invalid @enderror"
                                    placeholder="John Doe"
                                    value="{{ old('name') }}" required autocomplete="name">
                            </div>
                            @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="mb-2">
                            <label class="form-floating-label" for="reg-email">Email Address *</label>
                            <div class="input-wrap">
                                <i class="bi bi-envelope-fill input-icon"></i>
                                <input type="email" name="email" id="reg-email"
                                    class="form-input @error('email') is-invalid @enderror"
                                    placeholder="you@company.com"
                                    value="{{ old('email') }}" required autocomplete="email">
                            </div>
                            @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="mb-2">
                            <label class="form-floating-label" for="reg-password">Password *</label>
                            <div class="input-wrap input-wrap-password">
                                <i class="bi bi-lock-fill input-icon"></i>
                                <input type="password" name="password" id="reg-password"
                                    class="form-input @error('password') is-invalid @enderror"
                                    placeholder="8–12 characters" required autocomplete="new-password">
                                <i class="bi bi-eye-slash-fill password-toggle" id="toggle-password"></i>
                            </div>
                            <div id="password-strength-container" class="mt-2 d-none">
                                <div class="progress" style="height: 5px; background: rgba(255,255,255,0.1); border-radius: 4px; margin-bottom: 6px;">
                                    <div id="strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div id="strength-text" class="small fw-semibold text-muted mb-2" style="font-size: 0.72rem;">Password Strength: <span id="strength-label" class="text-danger">Too Weak</span></div>
                                <div class="d-flex flex-wrap gap-2" style="font-size: 0.7rem;">
                                    <span id="rule-length" class="text-danger d-flex align-items-center gap-1"><i class="bi bi-x-circle-fill"></i> 8–12 characters required</span>
                                </div>
                            </div>
                            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="mb-2">
                            <label class="form-floating-label" for="reg-password-confirm">Confirm Password *</label>
                            <div class="input-wrap input-wrap-password">
                                <i class="bi bi-lock-fill input-icon"></i>
                                <input type="password" name="password_confirmation" id="reg-password-confirm"
                                    class="form-input" placeholder="Confirm password" required autocomplete="new-password">
                                <i class="bi bi-eye-slash-fill password-toggle" id="toggle-password-confirm"></i>
                            </div>
                            <div id="password-match-container" class="mt-2 d-none" style="font-size: 0.72rem;">
                                <span id="match-status" class="text-danger d-flex align-items-center gap-1"><i class="bi bi-x-circle-fill"></i> Passwords do not match</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-divider">
                    <i class="bi bi-building-fill me-1"></i> Company Details
                </div>

                <div class="row g-2">
                    <div class="col-md-6 col-12">
                        <div class="mb-2">
                            <label class="form-floating-label" for="company_name">Company Name *</label>
                            <div class="input-wrap">
                                <i class="bi bi-building input-icon"></i>
                                <input type="text" name="company_name" id="company_name"
                                    class="form-input @error('company_name') is-invalid @enderror"
                                    placeholder="Acme Corp"
                                    value="{{ old('company_name') }}" required>
                            </div>
                            @error('company_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="mb-2">
                            <label class="form-floating-label" for="phone">Phone Number</label>
                            <div class="input-wrap">
                                <i class="bi bi-telephone-fill input-icon"></i>
                                <input type="tel" name="phone" id="phone" class="form-input"
                                    placeholder="Phone" value="{{ old('phone') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="mb-2">
                            <label class="form-floating-label" for="whatsapp_number">WhatsApp</label>
                            <div class="input-wrap">
                                <i class="bi bi-whatsapp input-icon"></i>
                                <input type="tel" name="whatsapp_number" id="whatsapp_number" class="form-input"
                                    placeholder="WhatsApp" value="{{ old('whatsapp_number') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="mb-2">
                            <label class="form-floating-label" for="has_gst">GST Registered? *</label>
                            <div class="input-wrap">
                                <i class="bi bi-percent input-icon"></i>
                                <select name="has_gst" id="has_gst" class="form-input @error('has_gst') is-invalid @enderror" required>
                                    <option value="no" {{ old('has_gst') === 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ old('has_gst') === 'yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            @error('has_gst') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-12" id="gst_number_wrapper" style="display: none;">
                        <div class="mb-2">
                            <label class="form-floating-label" for="gst_number">GST Number *</label>
                            <div class="input-wrap">
                                <i class="bi bi-receipt-cutoff input-icon"></i>
                                <input type="text" name="gst_number" id="gst_number"
                                    class="form-input @error('gst_number') is-invalid @enderror"
                                    placeholder="e.g. 07ABOPN8619H1Z5"
                                    value="{{ old('gst_number') }}">
                            </div>
                            @error('gst_number') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-signin" id="register-btn">
                    <i class="bi bi-rocket-takeoff-fill"></i>
                    Create Account
                </button>
            </form>

            <div class="form-divider"><span>or</span></div>

            <div class="text-center mb-3">
                <span style="font-size:0.85rem;color:rgba(255,255,255,0.45);">Already have an account?</span> 
                <a href="{{ route('subscriber.login') }}" class="forgot-link ms-1">Sign in →</a>
            </div>

            <a href="{{ url('/') }}" style="display:flex;align-items:center;justify-content:center;gap:10px;padding:14px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:14px;color:rgba(255,255,255,0.6);font-size:0.9rem;font-weight:600;text-decoration:none;transition:all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.07)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                <i class="bi bi-arrow-left-circle-fill" style="color:#818CF8;"></i>
                Back to Homepage
            </a>

        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('reg-password');
    const confirmInput = document.getElementById('reg-password-confirm');
    const strengthContainer = document.getElementById('password-strength-container');
    const strengthBar = document.getElementById('strength-bar');
    const strengthLabel = document.getElementById('strength-label');
    
    const ruleLength = document.getElementById('rule-length');
    
    const matchContainer = document.getElementById('password-match-container');
    const matchStatus = document.getElementById('match-status');
    const registerForm = document.getElementById('register-form');
    const registerBtn = document.getElementById('register-btn');
    
    // Toggle Password Visibility
    const togglePassword = document.getElementById('toggle-password');
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('bi-eye-fill');
        this.classList.toggle('bi-eye-slash-fill');
    });

    const toggleConfirmPassword = document.getElementById('toggle-password-confirm');
    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmInput.setAttribute('type', type);
        this.classList.toggle('bi-eye-fill');
        this.classList.toggle('bi-eye-slash-fill');
    });

    // Password Real-time Strength Checker (length 8–12 only)
    passwordInput.addEventListener('input', function() {
        const val = this.value;
        if (val.length > 0) {
            strengthContainer.classList.remove('d-none');
        } else {
            strengthContainer.classList.add('d-none');
        }
        
        // Only length rule matters
        const isLengthValid = val.length >= 8 && val.length <= 12;
        
        updateRuleIndicator(ruleLength, isLengthValid);
        
        // Calculate score
        let score = 0;
        if (val.length >= 4) score += 25;
        if (val.length >= 8) score += 50;
        if (val.length >= 10) score += 25;
        if (val.length > 12) score = 10; // Over max
        
        strengthBar.style.width = score + '%';
        
        if (val.length > 12) {
            strengthBar.style.backgroundColor = '#EF4444';
            strengthLabel.textContent = 'Too Long (max 12)';
            strengthLabel.className = 'text-danger';
        } else if (val.length >= 8 && val.length <= 12) {
            strengthBar.style.backgroundColor = '#10B981';
            strengthLabel.textContent = 'Valid';
            strengthLabel.className = 'text-success fw-bold';
        } else if (val.length >= 4) {
            strengthBar.style.backgroundColor = '#F59E0B';
            strengthLabel.textContent = 'Too Short';
            strengthLabel.className = 'text-warning';
        } else {
            strengthBar.style.backgroundColor = '#EF4444';
            strengthLabel.textContent = 'Too Weak';
            strengthLabel.className = 'text-danger';
        }
        
        checkPasswordMatch();
    });

    function updateRuleIndicator(element, isValid) {
        if (isValid) {
            element.classList.remove('text-danger');
            element.classList.add('text-success');
            element.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + element.textContent.trim().substring(2);
        } else {
            element.classList.remove('text-success');
            element.classList.add('text-danger');
            element.innerHTML = '<i class="bi bi-x-circle-fill"></i> ' + element.textContent.trim().substring(2);
        }
    }

    // Password Match Checker
    confirmInput.addEventListener('input', checkPasswordMatch);

    function checkPasswordMatch() {
        const pVal = passwordInput.value;
        const cVal = confirmInput.value;
        
        if (cVal.length > 0) {
            matchContainer.classList.remove('d-none');
            if (pVal === cVal) {
                matchStatus.classList.remove('text-danger');
                matchStatus.classList.add('text-success');
                matchStatus.innerHTML = '<i class="bi bi-check-circle-fill"></i> Passwords match';
            } else {
                matchStatus.classList.remove('text-success');
                matchStatus.classList.add('text-danger');
                matchStatus.innerHTML = '<i class="bi bi-x-circle-fill"></i> Passwords do not match';
            }
        } else {
            matchContainer.classList.add('d-none');
        }
    }

    // GST fields toggle logic
    const hasGstSelect = document.getElementById('has_gst');
    const gstNumberWrapper = document.getElementById('gst_number_wrapper');
    const gstNumberInput = document.getElementById('gst_number');

    function toggleGstFields() {
        if (hasGstSelect.value === 'yes') {
            gstNumberWrapper.style.display = 'block';
            gstNumberInput.required = true;
        } else {
            gstNumberWrapper.style.display = 'none';
            gstNumberInput.required = false;
            gstNumberInput.value = '';
        }
    }

    if (hasGstSelect) {
        hasGstSelect.addEventListener('change', toggleGstFields);
        toggleGstFields();
    }

    // Form submit — only check length (8–12) and match
    registerForm.addEventListener('submit', function(e) {
        const val = passwordInput.value;
        const pVal = passwordInput.value;
        const cVal = confirmInput.value;
        
        if (!val) {
            e.preventDefault();
            alert('Password is required.');
            return false;
        }
        
        if (val.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters.');
            return false;
        }
        
        if (val.length > 12) {
            e.preventDefault();
            alert('Password cannot exceed 12 characters.');
            return false;
        }
        
        if (pVal !== cVal) {
            e.preventDefault();
            alert('Passwords do not match. Please verify your confirmation password.');
            return false;
        }

        // Show loading state
        registerBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Creating Account...';
        registerBtn.disabled = true;
    });
});
</script>
</body>
</html>
