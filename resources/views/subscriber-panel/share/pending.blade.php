<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog Under Review | Catasky</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #4F46E5;
            --secondary: #7C3AED;
            --bg: #0B0F19;
            --card-bg: rgba(22, 28, 45, 0.7);
            --border: rgba(255, 255, 255, 0.08);
            --text: #F3F4F6;
            --text-muted: #9CA3AF;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.15) 0, transparent 50%),
                radial-gradient(at 100% 100%, rgba(124, 58, 237, 0.15) 0, transparent 50%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
            max-width: 550px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .logo-wrap {
            margin-bottom: 24px;
        }

        .review-icon {
            font-size: 4rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            animation: pulse 2s infinite;
        }

        .glass-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 12px;
            background: linear-gradient(to right, #FFF, #D1D5DB);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 20px 0;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>

<div class="glass-card">
    <div class="logo-wrap">
        <i class="bi bi-shield-lock review-icon"></i>
    </div>
    
    <h1 class="glass-title">Catalog Under Review</h1>
    <p class="text-muted">
        This digital catalog page is currently pending administrative review.
    </p>
    
    <div class="divider"></div>
    
    <p style="font-size: 0.9rem; line-height: 1.6; color: var(--text-muted);">
        Under B2B SaaS security guidelines, newly generated store pages, catalog share links, and product listings require review and approval from our compliance department before going live.
    </p>
    
    <p class="mt-4 mb-0" style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">
        <i class="bi bi-info-circle-fill"></i> Once approved, this share link will activate instantly.
    </p>
</div>

</body>
</html>
