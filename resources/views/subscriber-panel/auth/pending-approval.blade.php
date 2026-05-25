<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Under Review | CataSky</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
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

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark-bg);
            background-image: 
                radial-gradient(at 10% 20%, rgba(79, 70, 229, 0.15) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(124, 58, 237, 0.12) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(20, 18, 43, 0.9) 0px, transparent 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Ambient grid */
        .grid-overlay {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, 0.015) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255, 255, 255, 0.015) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: 0;
            pointer-events: none;
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            box-shadow: 0 30px 70px rgba(0,0,0,0.5);
            max-width: 550px;
            width: 100%;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            opacity: 0.8;
        }

        .radar-spinner {
            position: relative;
            width: 90px;
            height: 90px;
            margin: 0 auto 30px;
        }

        .radar-circle {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px solid var(--primary);
            border-radius: 50%;
            opacity: 0;
            animation: pulse-ring 2.5s cubic-bezier(0.215, 0.610, 0.355, 1) infinite;
        }

        .radar-circle:nth-child(2) {
            animation-delay: 0.8s;
            border-color: var(--secondary);
        }

        .radar-circle:nth-child(3) {
            animation-delay: 1.6s;
        }

        .radar-center {
            position: absolute;
            width: 50px;
            height: 50px;
            left: 20px;
            top: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 0 20px var(--primary-glow);
            z-index: 2;
        }

        .glass-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 12px;
            background: linear-gradient(to right, #FFF, #E2E8F0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .glass-subtitle {
            font-size: 0.95rem;
            color: #818CF8;
            font-weight: 600;
            margin-bottom: 24px;
            letter-spacing: 0.5px;
        }

        .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
            margin: 24px 0;
        }

        .text-body-p {
            font-size: 0.88rem;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            color: #FFFFFF;
            font-size: 0.88rem;
            font-weight: 600;
            padding: 10px 24px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }
            50% {
                opacity: 0.6;
            }
            100% {
                transform: scale(1.3);
                opacity: 0;
            }
        }
    </style>
</head>
<body>

<div class="grid-overlay"></div>

<div class="glass-card">
    <div class="radar-spinner">
        <div class="radar-circle"></div>
        <div class="radar-circle"></div>
        <div class="radar-circle"></div>
        <div class="radar-center">
            <i class="bi bi-clock-history"></i>
        </div>
    </div>
    
    <h1 class="glass-title">Your account is under admin review</h1>
    <div class="glass-subtitle">STATUS: PENDING COMPLIANCE APPROVAL</div>
    
    <p class="text-body-p">
        We have received your plan selection and successful registration/payment. Our administration is currently conducting a standard B2B compliance review of your store profile.
    </p>
    
    <div class="divider"></div>
    
    <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 30px;">
        <i class="bi bi-shield-check text-success"></i> Accounts are usually reviewed and activated within 1-2 hours during business schedules. You will receive full dashboard access immediately once activated.
    </p>
    
    <form action="{{ route('subscriber.logout') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn-logout">
            <i class="bi bi-box-arrow-left"></i> Logout Account
        </button>
    </form>
</div>

</body>
</html>
