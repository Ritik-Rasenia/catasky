<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $globalSetting = \App\Models\Setting::first(); @endphp
    <title>Create Account — {{ $globalSetting->site_title ?? 'Catasky' }}</title>
    <meta name="description" content="Create your free Catasky account and start building professional B2B product catalogues.">

    @if($globalSetting && $globalSetting->favicon)
        <link rel="icon" href="{{ asset('uploads/settings/' . $globalSetting->favicon) }}">
    @else
        <link rel="icon" href="{{ asset('uploads/fav.png') }}">
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root { --primary: #4F46E5; --secondary: #7C3AED; }
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
            width: 42%;
            min-height: 100vh;
            background: linear-gradient(150deg, #1E1B4B 0%, #2D1B69 50%, #0F172A 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px 48px;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }
        .brand-panel::before {
            content: '';
            position: absolute; top: -100px; left: -100px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(79,70,229,0.22) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .brand-panel::after {
            content: '';
            position: absolute; bottom: -120px; right: -80px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(124,58,237,0.18) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .grid-dots {
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px);
            background-size: 30px 30px; pointer-events: none;
        }

        .brand-logo {
            display: flex; align-items: center; gap: 12px;
            text-decoration: none; position: relative; z-index: 2;
        }
        .brand-logo-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 800; font-size: 1.2rem;
            box-shadow: 0 8px 20px rgba(79,70,229,0.4);
        }
        .brand-logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem; font-weight: 800; color: white; letter-spacing: -0.5px;
        }
        .brand-logo-sub {
            font-size: 0.58rem; color: rgba(255,255,255,0.35);
            letter-spacing: 1.5px; text-transform: uppercase; font-weight: 600; margin-top: -2px;
        }

        .brand-content {
            position: relative; z-index: 2; flex: 1;
            display: flex; flex-direction: column; justify-content: center;
            padding: 32px 0;
        }
        .brand-headline {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(1.6rem, 2.8vw, 2.2rem);
            font-weight: 800; color: white; line-height: 1.18;
            letter-spacing: -0.03em; margin-bottom: 18px;
        }
        .brand-sub {
            font-size: 0.9rem; color: rgba(255,255,255,0.5);
            line-height: 1.7; max-width: 340px; margin-bottom: 36px;
        }

        /* Plan card on brand side */
        .plan-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px; padding: 24px;
            position: relative; z-index: 2;
        }
        .plan-card-title {
            font-size: 0.68rem; font-weight: 700; letter-spacing: 1.2px;
            text-transform: uppercase; color: rgba(255,255,255,0.4); margin-bottom: 14px;
        }
        .plan-item {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 10px; font-size: 0.82rem; color: rgba(255,255,255,0.75);
        }
        .plan-item:last-child { margin-bottom: 0; }
        .plan-check {
            width: 22px; height: 22px; border-radius: 50%;
            background: rgba(16,185,129,0.15); color: #4ADE80;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.65rem; flex-shrink: 0;
        }
        .plan-badge {
            display: inline-block;
            background: rgba(79,70,229,0.2); border: 1px solid rgba(79,70,229,0.3);
            color: #818CF8; font-size: 0.65rem; font-weight: 700;
            padding: 2px 10px; border-radius: 100px; margin-left: 6px;
        }

        /* ── Right Form Panel ── */
        .form-panel {
            flex: 1;
            display: flex; align-items: center; justify-content: center;
            padding: 40px 36px;
            background: #0F172A;
            min-height: 100vh;
            overflow-y: auto;
        }
        .form-box { width: 100%; max-width: 440px; }

        .form-header { margin-bottom: 32px; }
        .form-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.8rem; font-weight: 800; color: white;
            letter-spacing: -0.03em; margin-bottom: 6px;
        }
        .form-header p { font-size: 0.88rem; color: rgba(255,255,255,0.4); }
        .form-header a { color: #818CF8; text-decoration: none; font-weight: 600; }
        .form-header a:hover { color: #A5B4FC; }

        .form-floating-label {
            font-size: 0.72rem; font-weight: 600; letter-spacing: 0.3px;
            color: rgba(255,255,255,0.45); text-transform: uppercase;
            margin-bottom: 7px; display: block;
        }
        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px; color: white;
            padding: 13px 18px 13px 46px;
            font-size: 0.92rem; font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease; outline: none;
        }
        .form-input::placeholder { color: rgba(255,255,255,0.18); }
        .form-input:focus {
            border-color: rgba(79,70,229,0.55);
            background: rgba(79,70,229,0.05);
            box-shadow: 0 0 0 4px rgba(79,70,229,0.1);
        }
        .form-input.is-invalid { border-color: rgba(239,68,68,0.5); }
        .invalid-feedback { color: #FCA5A5; font-size: 0.75rem; margin-top: 5px; display: block; }

        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.22); font-size: 0.95rem; pointer-events: none;
        }
        .input-wrap .form-input { padding-left: 44px; }

        /* Password strength bar */
        .strength-bar {
            display: flex; gap: 4px; margin-top: 8px;
        }
        .strength-segment {
            flex: 1; height: 3px; border-radius: 3px;
            background: rgba(255,255,255,0.08);
            transition: background 0.3s ease;
        }

        .btn-register {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white; border: none; border-radius: 14px;
            font-size: 0.98rem; font-weight: 700; font-family: 'Poppins', sans-serif;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(79,70,229,0.3);
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 35px rgba(79,70,229,0.4);
        }
        .btn-register:active { transform: translateY(0); }

        .form-footer {
            text-align: center; margin-top: 24px;
            font-size: 0.82rem; color: rgba(255,255,255,0.32);
        }
        .form-footer a { color: #818CF8; text-decoration: none; font-weight: 600; }
        .form-footer a:hover { color: #A5B4FC; }

        .error-alert {
            background: rgba(239,68,68,0.07);
            border: 1px solid rgba(239,68,68,0.18);
            border-radius: 12px; padding: 11px 15px;
            color: #FCA5A5; font-size: 0.82rem; margin-bottom: 22px;
            display: flex; align-items: center; gap: 10px;
        }

        .terms-text {
            font-size: 0.76rem; color: rgba(255,255,255,0.28); line-height: 1.6; margin-top: 16px;
        }
        .terms-text a { color: rgba(129,140,248,0.7); text-decoration: none; }

        @media (max-width: 991px) {
            .brand-panel { display: none; }
            .form-panel { padding: 30px 22px; }
        }
        @media (max-width: 480px) {
            .form-panel { padding: 22px 18px; }
        }
    </style>
</head>
<body>

    <!-- ── Brand Panel ── -->
    <div class="brand-panel">
        <div class="grid-dots"></div>

        <a href="{{ url('/') }}" class="brand-logo">
            <div class="brand-logo-icon">C</div>
            <div>
                <div class="brand-logo-text">Catasky</div>
                <div class="brand-logo-sub">Smart Catalogue</div>
            </div>
        </a>

        <div class="brand-content">
            <h1 class="brand-headline">
                Start Building Your<br>
                <span style="background:linear-gradient(135deg,#818CF8,#A78BFA);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Digital Catalogue</span><br>
                For Free Today
            </h1>
            <p class="brand-sub">
                Join 2,400+ B2B sales teams who use Catasky to build professional product catalogues and close more deals.
            </p>

            <div class="plan-card">
                <div class="plan-card-title">🎁 Free Account Includes</div>
                @foreach([
                    ['Up to 50 Products', null],
                    ['5 PDF Exports/Month', null],
                    ['WhatsApp Image Sharing', null],
                    ['Basic Branding & Logo', null],
                    ['Analytics Dashboard', 'Pro Feature'],
                ] as $item)
                <div class="plan-item">
                    <div class="plan-check"><i class="bi bi-check-lg"></i></div>
                    <span>{{ $item[0] }}</span>
                    @if($item[1])
                        <span class="plan-badge">{{ $item[1] }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <div style="position:relative;z-index:2;font-size:0.72rem;color:rgba(255,255,255,0.25);text-align:center;">
            No credit card required · Cancel anytime · 14-day free trial
        </div>
    </div>

    <!-- ── Form Panel ── -->
    <div class="form-panel">
        <div class="form-box">

            <!-- Mobile Logo -->
            <div class="d-lg-none text-center mb-4">
                <div style="display:inline-flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1rem;">C</div>
                    <span style="font-family:'Outfit',sans-serif;font-size:1.3rem;font-weight:800;color:white;">Catasky</span>
                </div>
            </div>

            <div class="form-header">
                <h2>Create your account 🚀</h2>
                <p>Already have an account? <a href="{{ route('login') }}">Sign in here</a></p>
            </div>

            @if ($errors->any())
            <div class="error-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST" id="reg-form">
                @csrf

                <div class="mb-3">
                    <label class="form-floating-label" for="name">Full Name</label>
                    <div class="input-wrap">
                        <i class="bi bi-person-fill input-icon"></i>
                        <input type="text" name="name" id="name"
                            class="form-input @error('name') is-invalid @enderror"
                            placeholder="Your full name"
                            value="{{ old('name') }}" required autocomplete="name">
                    </div>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-floating-label" for="email">Work Email</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope-fill input-icon"></i>
                        <input type="email" name="email" id="email"
                            class="form-input @error('email') is-invalid @enderror"
                            placeholder="you@company.com"
                            value="{{ old('email') }}" required autocomplete="email">
                    </div>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-floating-label" for="password">Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" name="password" id="password"
                            class="form-input @error('password') is-invalid @enderror"
                            placeholder="8–12 characters"
                            required autocomplete="new-password"
                            oninput="checkStrength(this.value)">
                    </div>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <!-- Password strength bar -->
                    <div class="strength-bar" id="strength-bar">
                        <div class="strength-segment" id="seg-1"></div>
                        <div class="strength-segment" id="seg-2"></div>
                        <div class="strength-segment" id="seg-3"></div>
                        <div class="strength-segment" id="seg-4"></div>
                    </div>
                    <div style="font-size:0.7rem;color:rgba(255,255,255,0.25);margin-top:4px;" id="strength-label"></div>
                </div>

                <div class="mb-4">
                    <label class="form-floating-label" for="password_confirmation">Confirm Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-shield-lock-fill input-icon"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-input"
                            placeholder="Repeat password"
                            required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn-register" id="reg-btn">
                    <i class="bi bi-rocket-takeoff-fill"></i>
                    Create Free Account
                </button>

                <p class="terms-text">
                    By creating an account you agree to our
                    <a href="#">Terms of Service</a> and
                    <a href="#">Privacy Policy</a>.
                    We'll never share your info without permission.
                </p>
            </form>

            <div class="form-footer">
                Already have an account? <a href="{{ route('login') }}">Sign in</a>
            </div>

        </div>
    </div>

<script>
// Password strength checker (length 8–12 only)
function checkStrength(pw) {
    const segs = [
        document.getElementById('seg-1'),
        document.getElementById('seg-2'),
        document.getElementById('seg-3'),
        document.getElementById('seg-4'),
    ];
    const label = document.getElementById('strength-label');

    let score = 0;
    if (pw.length >= 4) score++;
    if (pw.length >= 8) score++;
    if (pw.length >= 10 && pw.length <= 12) score++;
    if (pw.length >= 8 && pw.length <= 12) score++;
    if (pw.length > 12) score = 1; // Over limit

    const colors = ['#EF4444','#F59E0B','#10B981','#4F46E5'];
    const labels = ['Too Short','Getting there','Almost there','Valid (8–12 chars)'];

    if (pw.length > 12) {
        segs.forEach(seg => seg.style.background = '#EF4444');
        label.textContent = 'Too Long (max 12)';
        label.style.color = '#EF4444';
        return;
    }

    segs.forEach((seg, i) => {
        seg.style.background = i < score ? colors[score - 1] : 'rgba(255,255,255,0.08)';
    });

    label.textContent = score > 0 ? labels[score - 1] : '';
    label.style.color = score > 0 ? colors[score - 1] : 'rgba(255,255,255,0.25)';
}

// Submit state and validation
document.getElementById('reg-form').addEventListener('submit', function(e) {
    const pw = document.getElementById('password').value;
    const pwConf = document.getElementById('password_confirmation').value;

    if (!pw) {
        e.preventDefault();
        alert('Password is required.');
        return false;
    }

    if (pw.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters.');
        return false;
    }

    if (pw.length > 12) {
        e.preventDefault();
        alert('Password cannot exceed 12 characters.');
        return false;
    }

    if (pw !== pwConf) {
        e.preventDefault();
        alert('Passwords do not match.');
        return false;
    }

    const btn = document.getElementById('reg-btn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Creating Account...';
    btn.disabled = true;
});
</script>
</body>
</html>
