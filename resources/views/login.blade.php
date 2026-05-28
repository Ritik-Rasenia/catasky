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
    <title>Sign In - {{ $siteTitle }}</title>
    <meta name="description" content="{{ Str::limit(strip_tags($globalSetting->site_description ?? 'Sign in to manage your B2B product catalogues.'), 160, '') }}">
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4F46E5;
            --secondary: #7C3AED;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0F172A;
            overflow: hidden;
        }

        /* ── Left Brand Panel ── */
        .brand-panel {
            width: 46%;
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

        /* decorative blobs */
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
            /* align-items: center; */
            justify-content: center;
            padding: 80px 40px;
            background: #0F172A;
            min-height: 100vh;
        }

        /* Prevent vertical scroll on standard screens */
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
                overflow: hidden;
            }
        }

        .form-box {
            width: 100%; max-width: 420px;
        }

        .form-header { margin-bottom: 20px; }
        .form-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.55rem; font-weight: 800; color: white; letter-spacing: -0.03em; margin-bottom: 4px;
        }
        .form-header p { font-size: 0.85rem; color: rgba(255,255,255,0.45); }
        .form-header a { color: #818CF8; text-decoration: none; font-weight: 600; }
        .form-header a:hover { color: #A5B4FC; }

        .form-floating-label {
            font-size: 0.72rem; font-weight: 600; letter-spacing: 0.3px;
            color: rgba(255,255,255,0.5); text-transform: uppercase; margin-bottom: 6px;
            display: block;
        }

        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            color: white;
            padding: 10px 14px;
            font-size: 0.88rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            outline: none;
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
        .invalid-feedback { color: #FCA5A5; font-size: 0.75rem; margin-top: 4px; }

        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.25); font-size: 0.95rem; pointer-events: none;
        }
        .input-wrap .form-input { padding-left: 40px; }
        .toggle-password {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.3); background: none; border: none; cursor: pointer;
            font-size: 0.95rem; transition: color 0.2s;
        }
        .toggle-password:hover { color: white; }

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
        }
        .btn-signin:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(79,70,229,0.45);
        }
        .btn-signin:active { transform: translateY(0); }

        .form-divider {
            display: flex; align-items: center; gap: 12px; margin: 16px 0;
        }
        .form-divider::before, .form-divider::after {
            content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.07);
        }
        .form-divider span { font-size: 0.75rem; color: rgba(255,255,255,0.3); }

        .form-footer {
            text-align: center; margin-top: 16px;
            font-size: 0.8rem; color: rgba(255,255,255,0.35);
        }
        .form-footer a { color: #818CF8; text-decoration: none; font-weight: 600; }
        .form-footer a:hover { color: #A5B4FC; }

        .forgot-link {
            color: #818CF8; text-decoration: none; font-size: 0.8rem; font-weight: 600;
        }
        .forgot-link:hover { color: #A5B4FC; }

        .remember-label {
            font-size: 0.8rem; color: rgba(255,255,255,0.5); cursor: pointer;
        }

        .form-check-input {
            background-color: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.15);
        }
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .error-alert {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 12px; padding: 10px 14px;
            color: #FCA5A5; font-size: 0.8rem; margin-bottom: 16px;
            display: flex; align-items: center; gap: 10px;
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
    </style>
</head>
<body>

    <!-- ── Brand Panel ── -->
    <div class="brand-panel">
        <div class="grid-dots"></div>

        <!-- Logo -->
        <a href="{{ url('/') }}" class="brand-logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" style="max-width:150px;object-fit:contain;margin:auto;">
            @else
                <div class="brand-logo-icon">C</div>
            @endif
           
        </a>

        <!-- Main Content -->
        <div class="brand-content">
            <h1 class="brand-headline">
                The World's Most<br>Premium <span style="background:linear-gradient(135deg,#818CF8,#A78BFA);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Catalogue Platform</span>
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

        <!-- Mini Stats Mockup -->
        <div class="mini-mockup">
            <div class="mini-mockup-label">Live Platform Stats</div>
            <div class="mini-stat">
                <span class="mini-stat-label">Catalogues Created</span>
                <span class="mini-stat-val">85,421</span>
            </div>
            <div class="mini-stat">
                <span class="mini-stat-label">PDFs Generated Today</span>
                <span class="mini-stat-val">1,248</span>
            </div>
            <div class="mini-stat">
                <span class="mini-stat-label">Active B2B Teams</span>
                <span class="mini-stat-val">2,400+</span>
            </div>
        </div>
    </div>

    <!-- ── Form Panel ── -->
    <div class="form-panel">
        <div class="form-box">

            <!-- Mobile Logo -->
            <div class="d-lg-none text-cente">
                <div style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" style="max-height:42px;max-width:150px;object-fit:contain;">
                    @else
                        <div style="width:38px;height:38px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1.1rem;">C</div>
                    @endif
                    <span style="font-family:'Outfit',sans-serif;font-size:1.4rem;font-weight:800;color:white;">{{ $siteTitle }}</span>
                </div>
            </div>

            <!-- Header -->
            <div class="form-header">
                <h2>Welcome back 👋</h2>
                <p>Sign in to your administration dashboard</p>
            </div>

            <!-- Error Alert -->
            @if ($errors->any())
            <div class="error-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            @if (session('success'))
            <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:12px;padding:12px 16px;color:#4ADE80;font-size:0.85rem;margin-bottom:24px;display:flex;align-items:center;gap:10px;">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login.submit') }}" method="POST" id="login-form">
                @csrf

                <div class="mb-3">
                    <label class="form-floating-label" for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope-fill input-icon"></i>
                        <input type="email" name="email" id="email"
                            class="form-input @error('email') is-invalid @enderror"
                            placeholder="you@company.com"
                            value="{{ old('email') }}" required autocomplete="email">
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-floating-label mb-0" for="password">Password</label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" name="password" id="password"
                            class="form-input @error('password') is-invalid @enderror"
                            placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="toggle-password" onclick="togglePassword()" id="toggle-pw-btn">
                            <i class="bi bi-eye-fill" id="pw-eye-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center gap-2 mb-3">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input" style="width:18px;height:18px;border-radius:5px;margin:0;">
                    <label for="remember" class="remember-label">Remember me for 30 days</label>
                </div>

                <button type="submit" class="btn-signin" id="signin-btn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Sign In to {{ $siteTitle }}
                </button>
            </form>

            <div class="form-divider"><span>or</span></div>

            <a href="{{ url('/') }}" style="display:flex;align-items:center;justify-content:center;gap:10px;padding:14px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:14px;color:rgba(255,255,255,0.6);font-size:0.9rem;font-weight:600;text-decoration:none;transition:all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.07)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                <i class="bi bi-arrow-left-circle-fill" style="color:#818CF8;"></i>
                Back to Homepage
            </a>

            <div class="form-footer">
                <span style="font-size:0.75rem;margin-top:6px;display:block;">
                    By signing in you agree to our <a href="#" style="color:rgba(255,255,255,0.35);">Terms</a> &amp; <a href="#" style="color:rgba(255,255,255,0.35);">Privacy Policy</a>
                </span>
            </div>

        </div>
    </div>

<script>
function togglePassword() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('pw-eye-icon');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.className = 'bi bi-eye-slash-fill';
    } else {
        pw.type = 'password';
        icon.className = 'bi bi-eye-fill';
    }
}

// Loading state on submit
document.getElementById('login-form').addEventListener('submit', function() {
    const btn = document.getElementById('signin-btn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Signing In...';
    btn.disabled = true;
});
</script>
</body>
</html>
