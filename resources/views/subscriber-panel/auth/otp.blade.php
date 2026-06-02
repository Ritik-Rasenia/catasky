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
    <title>Verify Your Account | {{ $siteTitle }}</title>
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
            align-items: center;
            justify-content: center;
            background: #0F172A;
            overflow-x: hidden;
            position: relative;
            color: white;
        }

        /* Ambient Glowing Background Orbs */
        .ambient-orb-1 {
            position: absolute;
            top: -10%; left: -10%;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.18) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .ambient-orb-2 {
            position: absolute;
            bottom: -10%; right: -10%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        /* Ambient grid */
        .grid-overlay {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.015) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
            z-index: 0;
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            max-width: 520px;
            width: 100%;
            padding: 44px 36px;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            margin-bottom: 28px;
        }
        .brand-logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 800; font-size: 1.2rem;
            box-shadow: 0 8px 20px rgba(79,70,229,0.3);
        }
        .brand-logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1.45rem; font-weight: 800; color: white;
            letter-spacing: -0.5px;
        }

        .glass-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.6rem;
            margin-bottom: 8px;
            color: #FFFFFF;
        }

        .glass-subtitle {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin-bottom: 24px;
            line-height: 1.5;
        }

        /* Premium Simulation Toast */
        .simulated-notification {
            background: rgba(16, 185, 129, 0.05);
            border: 1px dashed rgba(16, 185, 129, 0.25);
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
        }
        .simulated-notification .icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(16, 185, 129, 0.12);
            display: flex; align-items: center; justify-content: center;
            color: #34D399; font-size: 1.2rem; flex-shrink: 0;
        }
        .simulated-notification .msg {
            font-size: 0.76rem; color: rgba(255,255,255,0.7);
            line-height: 1.4;
        }

        /* Segmented OTP Input Grid */
        .otp-inputs-wrapper {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 28px;
        }
        .otp-input-field {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.04);
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            color: white;
            font-size: 1.6rem;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            text-align: center;
            outline: none;
            transition: all 0.25s ease;
        }
        .otp-input-field:focus {
            border-color: rgba(79,70,229,0.8);
            background: rgba(79,70,229,0.06);
            box-shadow: 0 0 0 4px rgba(79,70,229,0.15);
        }

        .btn-verify {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white; border: none; border-radius: 12px;
            font-size: 0.95rem; font-weight: 700; font-family: 'Poppins', sans-serif;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(79,70,229,0.3);
            transition: all 0.3s ease;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-bottom: 18px;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(79,70,229,0.4);
        }

        .btn-resend {
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.45);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.2s;
            text-decoration: underline;
        }
        .btn-resend:hover {
            color: #818CF8;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            font-size: 0.82rem;
            text-decoration: none;
            margin-top: 18px;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: white;
        }
    </style>
</head>
<body>

    <div class="ambient-orb-1"></div>
    <div class="ambient-orb-2"></div>
    <div class="grid-overlay"></div>

    <div class="glass-card">
        <!-- Logo -->
        <a href="{{ url('/') }}" class="brand-logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" style="max-height:40px;max-width:140px;object-fit:contain;">
            @else
                <div class="brand-logo-icon">Catasky</div>
            @endif
        </a>

        <h1 class="glass-title">Verify Your Email 🔒</h1>
        <p class="glass-subtitle">Enter the 6-digit compliance code sent to your registered address <strong>{{ $email }}</strong> to verify business identity.</p>

        @if ($errors->any())
        <div class="alert alert-danger border-0 text-start small mb-4 py-2 px-3 rounded-3" style="background:rgba(239,68,68,0.08);color:#FCA5A5;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
        </div>
        @endif

        @if (session('success'))
        <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:12px;padding:12px 16px;color:#4ADE80;font-size:0.85rem;margin-bottom:24px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <form action="{{ route('subscriber.verify-otp.submit') }}" method="POST" id="otp-form">
            @csrf
            
            <!-- Hidden field for consolidated OTP -->
            <input type="hidden" name="otp" id="consolidated-otp" required>

            <!-- Segmented Inputs -->
            <div class="otp-inputs-wrapper">
                <input type="text" class="otp-input-field" maxlength="1" pattern="[0-9]" inputmode="numeric" required autofocus>
                <input type="text" class="otp-input-field" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-input-field" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-input-field" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-input-field" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-input-field" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            </div>

            <button type="submit" class="btn-verify" id="verify-btn">
                <i class="bi bi-patch-check-fill"></i>
                Verify &amp; Set Pending Approval
            </button>
        </form>

        <div class="text-center">
            <form action="{{ route('subscriber.resend-otp') }}" method="POST" id="resend-form" style="display:none;">
                @csrf
            </form>
            <button type="button" class="btn-resend" onclick="document.getElementById('resend-form').submit()">
                <i class="bi bi-arrow-clockwise me-1"></i>Resend Code
            </button>
        </div>

        <a href="{{ route('subscriber.register') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to Registration
        </a>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.otp-input-field');
    const form = document.getElementById('otp-form');
    const consolidatedInput = document.getElementById('consolidated-otp');

    // Handle Input sequence auto-focus
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            // allow only numbers
            if (e.target.value && !/^[0-9]$/.test(e.target.value)) {
                e.target.value = '';
                return;
            }

            if (e.target.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }

            consolidateOtp();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace') {
                if (!e.target.value && index > 0) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                } else {
                    e.target.value = '';
                }
                consolidateOtp();
            }
        });

        // Paste support
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = e.clipboardData.getData('text').trim();
            if (/^[0-9]{6}$/.test(text)) {
                inputs.forEach((inp, idx) => {
                    inp.value = text[idx];
                });
                consolidateOtp();
                document.getElementById('verify-btn').focus();
            }
        });
    });

    function consolidateOtp() {
        let code = '';
        inputs.forEach(inp => code += inp.value);
        consolidatedInput.value = code;
    }

    form.addEventListener('submit', function (e) {
        consolidateOtp();
        if (consolidatedInput.value.length !== 6) {
            e.preventDefault();
            alert('Please enter all 6 digits of the verification code.');
            return;
        }

        const btn = document.getElementById('verify-btn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Verifying...';
        btn.disabled = true;
    });
});

function resendOtp() {
    window.location.reload();
}
</script>
</body>
</html>
