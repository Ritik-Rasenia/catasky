<!DOCTYPE html>
<html lang="en" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access | Catasky</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-glow: rgba(79, 70, 229, 0.15);
            --bg: #0f172a;
            --surface: rgba(30, 41, 59, 0.7);
            --border: rgba(255, 255, 255, 0.08);
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --radius: 24px;
        }

        html[data-theme="light"] {
            --bg: #f8fafc;
            --surface: rgba(255, 255, 255, 0.85);
            --border: rgba(0, 0, 0, 0.06);
            --text: #0f172a;
            --text-muted: #64748b;
            --primary-glow: rgba(79, 70, 229, 0.08);
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        /* Abstract glowing background shapes for high premium feel */
        .glow-shape {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--primary-glow) 0%, rgba(0,0,0,0) 70%);
            z-index: 1;
            filter: blur(40px);
            pointer-events: none;
        }
        .shape-1 { top: 10%; left: 10%; }
        .shape-2 { bottom: 10%; right: 10%; }

        .error-card {
            max-width: 580px;
            width: 100%;
            background: var(--surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            padding: 48px 40px;
            text-align: center;
            z-index: 2;
            transform: translateY(0);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .error-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(79, 70, 229, 0.1);
            border-color: rgba(79, 70, 229, 0.3);
        }

        .icon-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.15) 0%, rgba(239, 68, 68, 0.02) 80%);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: pulseRed 2s infinite;
        }

        .icon-container i {
            font-size: 2.5rem;
            color: #ef4444;
            animation: float 3s ease-in-out infinite;
        }

        .badge-role {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 800;
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            padding: 6px 16px;
            border-radius: 100px;
            display: inline-block;
            margin-bottom: 20px;
            border: 1px solid rgba(79, 70, 229, 0.15);
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 14px;
            background: linear-gradient(135deg, var(--text) 30%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .error-code {
            font-size: 5.5rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 10px;
            letter-spacing: -2px;
            opacity: 0.15;
            background: linear-gradient(180deg, var(--text) 0%, rgba(0,0,0,0) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.lead-text {
            font-family: 'Poppins', sans-serif;
            font-size: 1.05rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 35px;
            padding: 0 10px;
        }

        .btn-action-group {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn-primary-custom {
            background: var(--primary);
            color: #ffffff;
            font-weight: 600;
            padding: 14px 28px;
            border-radius: 100px;
            border: none;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.25);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary-custom:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(79, 70, 229, 0.35);
            color: #ffffff;
        }

        .btn-secondary-custom {
            background: transparent;
            color: var(--text);
            font-weight: 600;
            padding: 14px 28px;
            border-radius: 100px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-secondary-custom:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--text-muted);
            color: var(--text);
            transform: translateY(-2px);
        }

        html[data-theme="light"] .btn-secondary-custom:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        @keyframes cardAppear {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulseRed {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>
    <div class="glow-shape shape-1"></div>
    <div class="glow-shape shape-2"></div>

    <div class="error-card">
        <div class="error-code">403</div>
        <div class="icon-container">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        
        @if(isset($role))
            <span class="badge-role">{{ $role }} Active</span>
        @else
            <span class="badge-role">System Security</span>
        @endif
        
        <h1>Unauthorized Access</h1>
        <p class="lead-text">
            You do not have the required permissions to access this module or execute this action. If you believe this is an error, please contact your system administrator.
        </p>

        <div class="btn-action-group">
            <a href="{{ route('dashboard') }}" class="btn btn-primary-custom">
                <i class="bi bi-grid-fill"></i>
                Return to Dashboard
            </a>
            
            <form action="{{ auth()->user() && auth()->user()->isSubscriber() ? route('subscriber.logout') : route('admin.logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-secondary-custom">
                    <i class="bi bi-box-arrow-right"></i>
                    Sign Out
                </button>
            </form>
        </div>
    </div>

    <script>
        // Synchronise local theme settings
        (function() {
            const savedTheme = localStorage.getItem('catasky-theme');
            if (savedTheme === 'dark' || savedTheme === 'light') {
                document.documentElement.setAttribute('data-theme', savedTheme);
            }
        })();
    </script>
</body>
</html>
