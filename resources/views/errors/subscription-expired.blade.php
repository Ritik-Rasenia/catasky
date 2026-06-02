<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired | CataSky</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
        }
        .container {
            max-width: 560px;
            width: 100%;
            text-align: center;
            position: relative;
        }
        .container::before {
            content: '';
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%);
            pointer-events: none;
            z-index: 1;
        }
        .glass-card {
            background: rgba(30, 27, 75, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }
        .icon-wrapper {
            width: 90px;
            height: 90px;
            border-radius: 28px;
            background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%);
            box-shadow: 0 10px 30px rgba(244, 63, 94, 0.4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
            animation: pulse 2s infinite ease-in-out;
        }
        .icon-wrapper i {
            font-size: 2.5rem;
            color: #ffffff;
        }
        h1 {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 16px;
            background: linear-gradient(to right, #ffffff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p.subtitle {
            color: #94a3b8;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .info-box {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 32px;
            text-align: left;
        }
        .info-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #f43f5e;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .info-desc {
            color: #e2e8f0;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .info-desc a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        .info-desc a:hover {
            color: #818cf8;
            text-decoration: underline;
        }
        .btn-renew {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            padding: 16px 28px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.35);
            transition: all 0.25s ease;
            margin-bottom: 24px;
        }
        .btn-renew:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.45);
        }
        .footer-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            font-size: 0.85rem;
        }
        .footer-links a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .footer-links a:hover {
            color: #94a3b8;
        }
        .separator {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #475569;
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(244, 63, 94, 0.4);
            }
            50% {
                transform: scale(1.04);
                box-shadow: 0 10px 40px rgba(244, 63, 94, 0.6);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(244, 63, 94, 0.4);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <div class="icon-wrapper">
                <i class="bi bi-credit-card-2-front-fill"></i>
            </div>
            
            <h1>Subscription Expired</h1>
            
            <p class="subtitle">
                The Enterprise subscription plan for <strong>{{ $company_name }}</strong> has expired. Custom domain routing has been disabled.
            </p>

            <div class="info-box">
                <div class="info-title">
                    <i class="bi bi-info-circle-fill"></i> System Notification
                </div>
                <div class="info-desc">
                    To reactivate your custom domain immediately, please renew your Enterprise plan. 
                    Your catalog is still safely preserved and can be viewed at: 
                    <a href="{{ $fallback_url }}" target="_blank">Default Fallback URL <i class="bi bi-box-arrow-up-right" style="font-size: 0.75rem;"></i></a>
                </div>
            </div>

            <a href="/subscriber/login" class="btn-renew">
                <i class="bi bi-arrow-right-circle-fill"></i> Sign In & Renew Subscription
            </a>

            <div class="footer-links">
                <a href="/">CataSky Portal</a>
                <div class="separator"></div>
                <a href="/contact-us">Contact Corporate Support</a>
            </div>
        </div>
    </div>
</body>
</html>
