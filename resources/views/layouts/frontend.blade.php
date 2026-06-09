<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    <meta name="user-id" content="{{ auth()->id() }}">

    @php
        $settings = \App\Models\Setting::first();
        $logoBase64 = isset($logoBase64) && !empty($logoBase64) ? $logoBase64 : '';
        if (empty($logoBase64) && $settings && $settings->logo) {
            $logoPath = public_path('uploads/settings/' . $settings->logo);
            if (file_exists($logoPath) && is_file($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = @file_get_contents($logoPath);
                if ($data !== false) {
                    $logoBase64 = 'data:image/' . ($type === 'svg' ? 'svg+xml' : $type) . ';base64,' . base64_encode($data);
                } else {
                    $logoBase64 = asset('uploads/settings/' . $settings->logo);
                }
            }
        }
        $siteTitle = $settings->site_title ?? 'Catasky';
        $siteDescription = $settings->site_description ?? 'Premium B2B catalog and product sharing platform.';
        $faviconUrl = ($settings && $settings->favicon) ? asset('uploads/settings/' . $settings->favicon) : asset('uploads/fav.png');
        $footerLogoUrl = ($settings && $settings->footer_logo) ? asset('uploads/settings/' . $settings->footer_logo) : $logoBase64;

        $isDemo = (isset($profile) && ($profile->company_slug === 'demo' || $profile->user_id == 3)) || request()->is('demo*');

        if (isset($isSubscriberStore) && $isSubscriberStore && isset($profile)) {
            $siteTitle = $profile->company_name; // Always use subscriber's company name for watermark
            if ($profile->logo && $profile->company_slug !== 'demo') {
                $footerLogoUrl = asset('uploads/subscriber-logos/' . $profile->logo);
            }
        }
    @endphp

    <title>@yield('title', $siteTitle . ' - Premium B2B Catalog')</title>
    <meta name="description" content="@yield('meta_description', Str::limit(strip_tags($siteDescription), 160, ''))">
    @if($settings && $settings->meta_keywords)
        <meta name="keywords" content="{{ $settings->meta_keywords }}">
    @endif
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @if(isset($isSubscriberStore) && $isSubscriberStore && isset($profile))
        @if(request()->attributes->has('custom_domain_subscriber_id'))
            <link rel="manifest" href="/manifest.json">
        @else
            <link rel="manifest" href="/store/{{ $profile->company_slug }}/manifest.json">
        @endif
    @else
        <link rel="manifest" href="/manifest.json">
    @endif

    <!-- Google Fonts: Poppins & Outfit for modern SaaS feel -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>

    <!-- CDNs for Client-Side PDF & Image catalog generation -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        body {
            font-family: 'Poppins', 'Outfit', sans-serif;
        }
        h1, h2, h3, h4, h5, h6, .navbar-brand {
            font-family: 'Outfit', sans-serif;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-modal-fade-in {
            animation: modalFadeIn 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        @keyframes customPulse {
            0% { transform: scale(1); box-shadow: 0 4px 14px rgba(37, 211, 102, 0.35); }
            50% { transform: scale(1.02); box-shadow: 0 6px 20px rgba(37, 211, 102, 0.55); }
            100% { transform: scale(1); box-shadow: 0 4px 14px rgba(37, 211, 102, 0.35); }
        }
        .btn-custom-pulse {
            animation: customPulse 1.8s infinite ease-in-out !important;
        }


        #selection-bar.floating-bar {
            left: 50% !important;
            right: auto !important;
            bottom: 24px !important;
            transform: translate(-50%, 130%) !important;
            width: fit-content !important;
            min-width: 480px !important; /* Premium tablet/small laptop width */
            max-width: 95vw !important;
            z-index: 1045 !important;
            border: 1.5px solid rgba(255, 255, 255, 0.55) !important;
            border-radius: 100px !important;
            background: rgba(255, 255, 255, 0) !important; /* Transparent background */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12), inset 0 1px 2px rgba(255, 255, 255, 0.8) !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12), inset 0 1px 2px rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            padding: 10px 16px !important;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
        @media (min-width: 992px) {
            #selection-bar.floating-bar {
                min-width: 620px !important; /* Premium grand wide width on desktop/laptops */
            }
        }
        #selection-bar.floating-bar.active {
            transform: translate(-50%, 0) !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        #selection-bar .bar-actions {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 16px !important; /* Spacious premium gaps */
            width: 100% !important;
        }
        /* Pill buttons */
        #selection-bar .bar-pill-btn {
            min-height: 46px !important; /* Taller touch targets */
            border-radius: 50px !important;
            padding: 8px 24px !important; /* Spacious wider button padding */
            font-weight: 800 !important;
            font-size: 14.5px !important; /* Highly readable premium size */
            color: #ffffff !important;
            border: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease !important;
            cursor: pointer !important;
            white-space: nowrap !important;
        }
        #selection-bar .bar-pill-btn:hover {
            transform: translateY(-2px) !important;
            filter: brightness(1.1) !important;
            color: #ffffff !important;
        }

        /* Premium Floating Enquiry Button Styling */
        .floating-enquiry-btn {
            position: fixed;
            bottom: 32px;
            right: 32px;
            z-index: 1040;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 26px;
            border-radius: 50px;
            background: var(--primary-gradient);
            color: #ffffff !important;
            font-family: 'Outfit', 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none !important;
            box-shadow: 0 10px 25px rgba(29, 111, 235, 0.35);
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
        }

        .floating-enquiry-btn i {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .floating-enquiry-btn:hover {
            transform: translateY(-6px) scale(1.04);
            box-shadow: 0 15px 35px rgba(29, 111, 235, 0.5);
            background: linear-gradient(135deg, #0284C7 0%, #1D6FEB 100%);
            border-color: rgba(255, 255, 255, 0.4);
            color: #ffffff !important;
        }

        .floating-enquiry-btn:hover i {
            transform: rotate(-10deg) scale(1.15);
        }

        .floating-enquiry-btn:active {
            transform: translateY(-2px) scale(0.98);
        }

        /* Subtle pulsing attention-getter animation */
        @keyframes enquiryPulse {
            0% {
                box-shadow: 0 10px 25px rgba(29, 111, 235, 0.35), 0 0 0 0 rgba(29, 111, 235, 0.45);
            }
            70% {
                box-shadow: 0 10px 25px rgba(29, 111, 235, 0.35), 0 0 0 12px rgba(29, 111, 235, 0);
            }
            100% {
                box-shadow: 0 10px 25px rgba(29, 111, 235, 0.35), 0 0 0 0 rgba(29, 111, 235, 0);
            }
        }

        .floating-enquiry-btn {
            animation: enquiryPulse 2.5s infinite ease-in-out;
        }

        /* Adaptive mobile viewport alignment to clear bottom selection bar */
        @media (max-width: 576px) {
            .floating-enquiry-btn {
                bottom: 90px;
                right: 20px;
                padding: 12px 20px;
                font-size: 13.5px;
                box-shadow: 0 8px 20px rgba(29, 111, 235, 0.3);
            }
            .floating-enquiry-btn i {
                font-size: 16px;
            }
        }
        #selection-bar .bar-pill-btn i {
            font-size: 15px !important;
            vertical-align: middle !important;
        }
        #selection-bar .selected-btn {
            background: #2b303c !important; /* Charcoal dark */
            box-shadow: 0 4px 12px rgba(43, 48, 60, 0.3) !important;
        }
        #selection-bar .pdf-btn {
            background: #007acc !important; /* Royal Blue */
            box-shadow: 0 4px 12px rgba(0, 122, 204, 0.3) !important;
        }
        #selection-bar .images-btn {
            background: #0ea5e9 !important; /* Cyan / Light Blue */
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3) !important;
        }
        .share-image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            justify-content: center;
            gap: 14px;
            align-items: start;
            padding: 16px;
            width: 100%;
            overflow-x: hidden;
            box-sizing: border-box;
        }
        .share-image-preview-card {
            display: block;
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.1);
            text-decoration: none;
            /* aspect-ratio keeps card tall regardless of width */
            aspect-ratio: 4 / 5;
            width: 100%;
            height: auto;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        .share-image-preview-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
        }
        .share-image-preview-card > div {
            width: 1080px;
            height: 1350px;
            transform: scale(var(--preview-scale, 0.218)) translateZ(0) !important;
            transform-origin: top left;
            will-change: transform;
            image-rendering: -webkit-optimize-contrast;
        }
        /* Pulsing Skeleton Previews */
        .skeleton-pulse {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: skeletonLoading 1.5s infinite linear;
            border-radius: 8px;
        }
        @keyframes skeletonLoading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .preview-skeleton-page {
            box-sizing: border-box;
            width: 100%;
            height: 100%;
            padding: 20px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .preview-skeleton-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .preview-skeleton-logo {
            width: 80px;
            height: 25px;
        }
        .preview-skeleton-date {
            width: 70px;
            height: 12px;
        }
        .preview-skeleton-title {
            width: 160px;
            height: 22px;
            margin: 0 auto;
        }
        .preview-skeleton-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            flex-grow: 1;
        }
        .preview-skeleton-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .preview-skeleton-img {
            width: 100%;
            height: 100px;
            border-radius: 6px;
        }
        .preview-skeleton-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .preview-skeleton-name {
            width: 45%;
            height: 12px;
        }
        .preview-skeleton-price {
            width: 25%;
            height: 12px;
        }
        .preview-skeleton-desc {
            width: 100%;
            height: 22px;
        }
        @media (max-width: 575.98px) {
            #selection-bar.floating-bar {
                border-radius: 100px !important;
                padding: 6px 8px !important;
                width: fit-content !important;
                min-width: 280px !important;
                max-width: 98vw !important;
                bottom: 16px !important;
            }
            #selection-bar .bar-actions {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
            }
            #selection-bar .bar-pill-btn {
                padding: 5px 12px !important;
                font-size: 11.5px !important;
                min-height: 36px;
            }
            #selection-bar .bar-pill-btn i {
                font-size: 13px !important;
                margin-right: 4px !important;
            }
            .share-image-preview-grid {
                /* On mobile: 2 equal columns filling full width */
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 10px;
                padding: 10px;
            }
            .share-image-preview-card {
                width: 100% !important;
                height: auto !important;
                aspect-ratio: 4 / 5 !important;
            }
            #pdf-preview-frame-details,
            #pdf-preview-frame-images,
            #pdf-preview-loader-details,
            #pdf-preview-loader-images {
                min-height: 320px !important;
                max-width: 100%;
            }
            #pdf-preview-scale-wrap-details,
            #pdf-preview-scale-wrap-images {
                justify-content: center !important;
            }
            #sharingModal .modal-body {
                padding-left: 12px;
                padding-right: 12px;
            }
            #sharingModal .btn {
                min-height: 42px;
                white-space: normal;
            }
        }
        
        .progress-bar-animated-premium {
            background: linear-gradient(90deg, #4f46e5, #818cf8, #4f46e5);
            background-size: 200% 100%;
            animation: premium-progress-flow 2s linear infinite;
        }
        @keyframes premium-progress-flow {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        /* Navbar PWA Install Button */
        .header-pwa-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 16px;
            border-radius: 30px;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: #ffffff !important;
            font-family: 'Outfit', 'Poppins', sans-serif;
            font-size: 0.82rem;
            font-weight: 700;
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            text-decoration: none !important;
            white-space: nowrap;
        }
        .header-pwa-btn i {
            font-size: 15px;
            transition: transform 0.3s ease;
        }
        .header-pwa-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.5);
            background: linear-gradient(135deg, #7C3AED 0%, #4F46E5 100%);
            color: #ffffff !important;
        }
        .header-pwa-btn:hover i {
            transform: rotate(-10deg) scale(1.15);
        }
        .header-pwa-btn:active {
            transform: scale(0.97);
        }
        /* Mobile: icon-only compact version */
        .header-pwa-btn-mobile {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: #ffffff !important;
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        .header-pwa-btn-mobile:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 18px rgba(99, 102, 241, 0.55);
            color: #ffffff !important;
        }
    </style>
</head>
<body>

    <!-- Sticky Glassmorphic Navbar -->
    <nav class="navbar navbar-expand-lg navbar-premium">
        <div class="container">
            <!-- Brand Logo -->
            @if(isset($isSubscriberStore) && $isSubscriberStore && isset($profile) && $profile->company_slug !== 'demo' && $profile->user_id != 3 && !request()->is('demo*'))
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('subscriber_store', $profile->company_slug) }}">
                    @if($profile->logo)
                        <img src="{{ asset('uploads/subscriber-logos/' . $profile->logo) }}" alt="{{ $profile->company_name }}" decoding="async" style="max-height: 40px; object-fit: contain;">
                    @else
                        <div class="logo-icon bg-primary text-white" style="background: linear-gradient(135deg, var(--primary), var(--secondary)) !important; color: white !important; display: flex; align-items: center; justify-content: center;">{{ strtoupper(substr($profile->company_name, 0, 1)) }}</div>
                    @endif
                </a>
            @else
                <a class="navbar-brand d-flex align-items-center gap-3" href="{{ route('home') }}">
                    @if($settings && $settings->logo)
                        <img src="{{ asset('uploads/settings/' . $settings->logo) }}" alt="{{ $settings->site_title ?? 'Catasky' }}" decoding="async" style="max-height: 40px; object-fit: contain;">
                    @else
                        <div class="logo-icon">C</div>
                    @endif
                </a>
            @endif

            <!-- Mobile Navbar Controls -->
            <div class="d-flex align-items-center gap-2 d-lg-none">
                <button class="nav-icon-btn" data-bs-toggle="modal" data-bs-target="#searchModal">
                    <i class="bi bi-search"></i>
                </button>
                @if(Auth::check() && Auth::user()->hasRole('Subscriber') && isset($isSubscriberStore) && $isSubscriberStore)
                <button id="pwa-install-btn-mobile" class="header-pwa-btn-mobile">
                    <i class="bi bi-phone-vibrate"></i>
                </button>
                @endif
                <button class="navbar-toggler nav-icon-btn" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1 mt-3 mt-lg-0">
                    <!-- Mobile-Only Auth Items -->
                    @auth
                        <li class="nav-item d-lg-none mt-2 pt-2 border-top">
                            <a href="{{ route('dashboard') }}" class="fw-bold px-3 py-2 text-dark small text-decoration-none">{{ Auth::user()->name }}</a>
                        </li>
                        @if(Auth::user()->hasRole('Subscriber'))
                            <li class="nav-item d-lg-none">
                                <a class="nav-link nav-link-custom" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Subscriber Panel</a>
                            </li>
                            <li class="nav-item d-lg-none">
                                <form action="{{ route('subscriber.logout') }}" method="POST" class="m-0 px-3 py-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm w-100 mt-2 text-start rounded-pill py-2 px-3 border-0 bg-transparent text-danger fw-bold">
                                        <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                    </button>
                                </form>
                            </li>
                        @else
                            <li class="nav-item d-lg-none">
                                <a class="nav-link nav-link-custom" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                            </li>
                            <li class="nav-item d-lg-none">
                                <form action="{{ route('admin.logout') }}" method="POST" class="m-0 px-3 py-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm w-100 mt-2 text-start rounded-pill py-2 px-3 border-0 bg-transparent text-danger fw-bold">
                                        <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                    </button>
                                </form>
                            </li>
                        @endif
                    @else
                        <li class="nav-item d-lg-none px-3 pt-2">
                            <a href="{{ route('subscriber.login') }}" class="btn btn-outline-primary w-100 py-2 fw-semibold" style="border-radius:12px; font-size:0.9rem;">Login</a>
                        </li>
                        <li class="nav-item d-lg-none px-3 pb-2">
                            <a href="{{ route('subscriber.register') }}" class="btn btn-hero-primary w-100 py-2" style="border-radius:12px; font-size:0.9rem;"><i class="bi bi-rocket-takeoff-fill"></i>Register</a>
                        </li>
                    @endauth
                </ul>

                <!-- Desktop Right Action Bar -->
                <div class="d-none d-lg-flex align-items-center gap-2 ms-auto">
                    <button class="nav-icon-btn" data-bs-toggle="modal" data-bs-target="#searchModal" title="Search Catalog">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(Auth::check() && Auth::user()->hasRole('Subscriber') && isset($isSubscriberStore) && $isSubscriberStore)
                    <button id="pwa-install-btn" class="header-pwa-btn">
                        <i class="bi bi-phone-vibrate"></i>
                    </button>
                    @endif

                    @auth
                        <div class="dropdown">
                            
                            <button id="headerUserDropdownBtn" class="btn btn-premium btn-premium-outline py-2 px-3 d-flex align-items-center gap-2" type="button" onclick="this.nextElementSibling.classList.toggle('show'); event.stopPropagation();" style="font-size:0.85rem; border-radius: 12px;">
                                <i class="bi bi-person-fill-check text-primary" style="pointer-events: none;"></i>
                                <span class="fw-semibold" style="pointer-events: none;">{{ Str::limit(Auth::user()->name, 20) }}</span>
                                <i class="bi bi-chevron-down ms-1" style="font-size:0.8rem; opacity: 0.7; pointer-events: none;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2 mt-2" style="min-width:200px;">
                                @if(Auth::user()->hasRole('Subscriber'))
                                    <li><a class="dropdown-item rounded-3 py-2 px-3 small" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item rounded-3 py-2 px-3 small" href="{{ route('subscriber.profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                                    <li>
                                        <form action="{{ route('subscriber.logout') }}" method="POST" class="d-block w-100 m-0">
                                            @csrf
                                            <button type="submit" class="dropdown-item rounded-3 py-2 px-3 small text-danger border-0 bg-transparent w-100 text-start"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                        </form>
                                    </li>
                                @else
                                    <li><a class="dropdown-item rounded-3 py-2 px-3 small" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item rounded-3 py-2 px-3 small" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                                    <li>
                                        <form action="{{ route('admin.logout') }}" method="POST" class="d-block w-100 m-0">
                                            @csrf
                                            <button type="submit" class="dropdown-item rounded-3 py-2 px-3 small text-danger border-0 bg-transparent w-100 text-start"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                            <script>
                                document.addEventListener('click', function(event) {
                                    const btn = document.getElementById('headerUserDropdownBtn');
                                    const menu = btn ? btn.nextElementSibling : null;
                                    if (menu && !btn.contains(event.target) && !menu.contains(event.target)) {
                                        menu.classList.remove('show');
                                    }
                                });
                            </script>
                        </div>
                    @else
                        <a href="{{ route('subscriber.login') }}" class="btn btn-premium btn-premium-outline py-2 px-3 fw-semibold" style="border-radius:12px; font-size:0.85rem;">
                            Login
                        </a>
                        <a href="{{ route('subscriber.register') }}" class="btn-hero-primary py-2 px-4" style="border-radius:12px; font-size:0.9rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                            <i class="bi bi-rocket-takeoff-fill"></i> Register
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Main Content -->
    <main style="min-height: calc(100vh - 400px);">
        @yield('content')
    </main>

    <!-- Premium Dark Footer -->
    <footer class="footer-premium py-5 mt-5">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <!-- Col 1: Bio -->
                <div class="col-lg-4 col-md-6 col-12">
                    @php
                        $isDemo = (isset($profile) && ($profile->company_slug === 'demo' || $profile->user_id == 3)) || request()->is('demo*');
                        $homeUrl = (isset($isSubscriberStore) && $isSubscriberStore && isset($profile) && !$isDemo) 
                            ? route('subscriber_store', $profile->company_slug) 
                            : route('home');
                        $footerBio = (isset($isSubscriberStore) && $isSubscriberStore && isset($profile) && !$isDemo)
                            ? ($profile->bio ?: $siteDescription)
                            : $siteDescription;
                    @endphp
                    <a href="{{ $homeUrl }}" class="d-flex align-items-center gap-2 text-decoration-none mb-3">
                        @if($footerLogoUrl)
                            <img src="{{ $footerLogoUrl }}" alt="{{ isset($profile) ? $profile->company_name : $siteTitle }}" loading="lazy" decoding="async" style="max-height: 42px; max-width: 170px; object-fit: contain;">
                        @else
                            <div class="logo-icon bg-white text-dark fw-bold ">C</div>
                        @endif
                    </a>
                    <p class="text-white-50 small mb-4" style="max-width: 320px;">
                        {{ $footerBio }}
                    </p>
                    <div class="d-flex gap-2">
                        @if(!isset($isSubscriberStore) || !$isSubscriberStore)
                            @foreach([
                                'facebook' => 'bi-facebook',
                                'twitter' => 'bi-twitter-x',
                                'linkedin' => 'bi-linkedin',
                                'instagram' => 'bi-instagram',
                                'youtube' => 'bi-youtube',
                            ] as $social => $icon)
                                @if($settings && $settings->{$social})
                                    <a href="{{ $settings->{$social} }}" target="_blank" rel="noopener" class="social-icon" aria-label="{{ ucfirst($social) }}"><i class="bi {{ $icon }} text-white-50"></i></a>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Col 2: Navigation -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="fw-bold text-white mb-3" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Quick Links</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        @if(isset($isSubscriberStore) && $isSubscriberStore && isset($profile))
                            <li><a href="{{ route('subscriber_store', $profile->company_slug) }}" class="text-white-50 text-decoration-none hover-white">Store Home</a></li>
                            <li><a href="{{ route('subscriber_store', $profile->company_slug) }}" class="text-white-50 text-decoration-none hover-white">Explore Catalog</a></li>
                            <li><a href="{{ route('store.contact', $profile->company_slug) }}" class="text-white-50 text-decoration-none hover-white">Contact Us</a></li>
                        @else
                            <li><a href="{{ route('home') }}" class="text-white-50 text-decoration-none hover-white">Home Page</a></li>
                            <li><a href="{{ route('demo') }}" class="text-white-50 text-decoration-none hover-white">Explore Catalog</a></li>
                            <li><a href="{{ route('contact') }}" class="text-white-50 text-decoration-none hover-white">Contact Us</a></li>
                            <li><a href="{{ route('privacy.policy') }}" class="text-white-50 text-decoration-none hover-white">Privacy Policy</a></li>
                            <li><a href="{{ route('refund.policy') }}" class="text-white-50 text-decoration-none hover-white">Refund Policy</a></li>
                            <li><a href="{{ route('terms.conditions') }}" class="text-white-50 text-decoration-none hover-white">Terms of Service</a></li>
                        @endif
                    </ul>
                </div>

                <!-- Col 3: Hot Categories -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="fw-bold text-white mb-3" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Categories</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        @php
                            $isDemo = (isset($profile) && ($profile->company_slug === 'demo' || $profile->user_id == 3)) || request()->is('demo*');
                            if (isset($isSubscriberStore) && $isSubscriberStore && isset($profile)) {
                                $categoryIds = \App\Models\SubscriberProduct::where('user_id', $profile->user_id)
                                    ->where('status', 'active')
                                    ->pluck('category_id')
                                    ->flatten()
                                    ->filter()
                                    ->unique();
                                $footerCats = \App\Models\Category::withoutGlobalScope('tenant')
                                    ->whereIn('id', $categoryIds)
                                    ->where('status', 1)
                                    ->take(4)
                                    ->get();
                            } else {
                                $footerCats = \App\Models\Category::where('status', 1)->take(4)->get();
                            }
                        @endphp
                        @foreach($footerCats as $fcat)
                            @php
                                if (isset($isSubscriberStore) && $isSubscriberStore && isset($profile)) {
                                    if ($isDemo) {
                                        $catUrl = route('demo') . '?category=' . $fcat->slug;
                                    } else {
                                        $catUrl = route('subscriber_store', $profile->company_slug) . '?category=' . $fcat->slug;
                                    }
                                } else {
                                    $catUrl = route('category.products', $fcat->slug);
                                }
                            @endphp
                            <li><a href="{{ $catUrl }}" class="text-white-50 text-decoration-none hover-white">{{ $fcat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Col 4: Corporate Support & Newsletter -->
                <div class="col-lg-3 col-md-6 col-12">
                    <h6 class="fw-bold text-white mb-3" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Support Center</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-white-50 mb-4">
                        @if(isset($isSubscriberStore) && $isSubscriberStore && isset($profile))
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-envelope-fill text-primary"></i>
                                <span>{{ $profile->email_for_inquiries ?: ($profile->user->email ?? 'support@catasky.com') }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-telephone-fill text-primary"></i>
                                <span>{{ $profile->phone ?: ($profile->whatsapp_number ?: '+91 99999 88888') }}</span>
                            </li>
                            @if($profile->address)
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-geo-alt-fill text-primary"></i>
                                    <span>{{ $profile->address }}{{ $profile->city ? ', ' . $profile->city : '' }}{{ $profile->pincode ? ' - ' . $profile->pincode : '' }}</span>
                                </li>
                            @endif
                        @else
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-envelope-fill text-primary"></i>
                                <span>{{ $settings->email ?? 'support@catasky.com' }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-telephone-fill text-primary"></i>
                                <span>{{ $settings->phone ?? '+91 99999 88888' }}</span>
                            </li>
                            @if($settings && $settings->address)
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-geo-alt-fill text-primary"></i>
                                    <span>{{ $settings->address }}</span>
                                </li>
                            @endif
                        @endif
                    </ul>
                    
                    <h6 class="fw-bold text-white mb-2" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Newsletter</h6>
                    <form action="{{ route('newsletter.submit') }}" method="POST" class="m-0">
                        @csrf
                        <div class="input-group">
                            <input type="email" name="email" class="form-control bg-dark border-secondary text-white small" placeholder="Your email address" required style="font-size: 0.8rem; border-radius: 8px 0 0 8px; border-color: rgba(255,255,255,0.15) !important;">
                            <button type="submit" class="btn btn-primary" style="border-radius: 0 8px 8px 0; background: var(--primary-gradient); border: none;">
                                <i class="bi bi-send-fill text-white"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer Bottom copyright -->
            <div class="border-top border-secondary border-opacity-20 mt-4 pt-4 d-flex flex-column flex-md-row align-items-center justify-content-between text-white-50 small">
                <p class="m-0">&copy; {{ date('Y') }} {{ isset($profile) ? $profile->company_name : $siteTitle }}. All Rights Reserved.</p>
                <div class="d-flex gap-3 mt-2 mt-md-0">
                    <a href="{{ route('terms.conditions') }}" class="text-white-50 text-decoration-none hover-white">Terms of Service</a>
                    <span class="opacity-25">|</span>
                    <a href="{{ route('privacy.policy') }}" class="text-white-50 text-decoration-none hover-white">Privacy Policy</a>
                    <span class="opacity-25">|</span>
                    <a href="{{ route('refund.policy') }}" class="text-white-50 text-decoration-none hover-white">Refund Policy</a>
                </div>
            </div>
        </div>
    </footer>

    @if(!request()->is('dashboard*') && !request()->is('secure-admin-login') && !request()->is('subscriber/login*') && !request()->is('subscriber/register*') && !request()->routeIs('home'))
    <!-- Floating Sticky Multi-Selection Glass Bar -->
    <div id="selection-bar" class="floating-bar" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
        <div class="bar-actions">
            <!-- Left button: Selected count -->
            <button class="bar-pill-btn selected-btn" onclick="openSharingModal('selection')" title="View Selected Blueprints">
                <i class="bi bi-list-task me-2"></i>Selected (<span id="selected-count">0</span>)
            </button>
            <!-- Center button: Share PDF -->
            <button class="bar-pill-btn pdf-btn" onclick="openSharingModal('pdf')" title="Open PDF Specifications">
                <i class="bi bi-file-earmark-pdf-fill me-2"></i>PDF Share
            </button>
            <!-- Right button: Share Image -->
            <button class="bar-pill-btn images-btn" onclick="openSharingModal('image')" title="Open Flyer & Image Sharing">
                <i class="bi bi-images me-2"></i>Image Share
            </button>
        </div>
    </div>
    @endif

    <!-- Product B2B Details Slide Drawer (Overlay + Drawer) -->
    <div class="drawer-overlay" id="drawer-overlay"></div>
    <div class="side-drawer" id="product-drawer">
        <div class="drawer-header">
            <div>
                <h5 class="fw-bold mb-1 text-gradient">Product Blueprint</h5>
                <p class="text-secondary small-text m-0">Corporate Specification & Selection</p>
            </div>
            <button class="drawer-close text-secondary" id="drawer-close" aria-label="Close">
                <i class="bi bi-x-lg fs-5"></i>
            </button>
        </div>
        <div id="drawer-content" class="drawer-body">
            <!-- Details Loaded via AJAX -->
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading blueprint...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Share & Export B2B Modal -->
    <div class="modal fade" id="sharingModal" tabindex="-1" aria-labelledby="sharingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content premium-modal">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bold text-gradient mb-1" id="sharingModalLabel">Share Options</h4>
                        
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs nav-tabs-premium gap-1.5 mb-2" id="sharingTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="selection-tab" data-bs-toggle="tab" data-bs-target="#selection-pane" type="button" role="tab" aria-controls="selection-pane" aria-selected="true">
                                <i class="bi bi-list-stars text-warning" style="margin-right: 8px;"></i>Selected (<span id="modal-selection-count">0</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pdf-tab" data-bs-toggle="tab" data-bs-target="#pdf-pane" type="button" role="tab" aria-controls="pdf-pane" aria-selected="false">
                                <i class="bi bi-file-earmark-pdf text-danger" style="margin-right: 8px;"></i>Share PDF
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="image-tab" data-bs-toggle="tab" data-bs-target="#image-pane" type="button" role="tab" aria-controls="image-pane" aria-selected="false">
                                <i class="bi bi-images text-accent" style="margin-right: 8px;"></i>Share Image
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="sharingTabsContent">
                        <!-- Tab 1: Selected Items Panel -->
                        <div class="tab-pane fade show active" id="selection-pane" role="tabpanel" aria-labelledby="selection-tab" tabindex="0">
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 small fw-bold" onclick="clearFullSelection()">
                                    <i class="bi bi-trash3-fill me-1"></i> Clear All Selection
                                </button>
                            </div>
                            
                            <!-- Selection List Container -->
                            <div id="modal-selection-list" class="d-flex flex-column gap-2 overflow-auto" style="max-height: 400px; padding-right: 4px;">
                                <!-- Loaded dynamically via JavaScript -->
                            </div>
                        </div>

                        <!-- Tab 2: Full Details PDF Panel -->
                        <div class="tab-pane fade" id="pdf-pane" role="tabpanel" aria-labelledby="pdf-tab" tabindex="0">
                            <div class="row g-4">
                                <div class="col-md-6 d-flex flex-column">
                                    <h6 class="fw-bold mb-3 text-uppercase text-dark" style="font-size: 0.92rem; letter-spacing: 0.5px;">Share Settings</h6>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Catalog Cover Color</label>
                                        <select class="form-select rounded-3 p-2" id="pdf-cover-color" style="font-size: 0.8rem;">
                                            <option value="indigo">Catasky Premium (Indigo & Violet)</option>
                                            <option value="slate">Corporate Elite (Slate & Charcoal)</option>
                                            <option value="emerald">Organic Growth (Emerald & Mint)</option>
                                            <option value="amber">Warm Elegance (Amber & Gold)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Share Caption Title</label>
                                        <input type="text" class="form-control rounded-3 p-2 share-setting-mirror" data-share-setting="share-catalog-title" value="Premium Selection" style="font-size: 0.8rem;">
                                    </div>
                                    <div class="d-flex flex-column gap-3 mb-4">
                                        <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center">
                                            <label class="form-check-label fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Show title</label>
                                            <input class="form-check-input ms-0 premium-switch share-setting-mirror" data-share-setting="share-show-title" type="checkbox" checked style="width: 42px; height: 22px;">
                                        </div>
                                        <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center">
                                            <label class="form-check-label fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Show pricing</label>
                                            <input class="form-check-input ms-0 premium-switch share-setting-mirror" data-share-setting="share-show-price" type="checkbox" checked style="width: 42px; height: 22px;">
                                        </div>
                                        <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center">
                                            <label class="form-check-label fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Additional pictures</label>
                                            <input class="form-check-input ms-0 premium-switch share-setting-mirror" data-share-setting="share-show-gallery" type="checkbox" style="width: 42px; height: 22px;">
                                        </div>
                                        <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center">
                                            <label class="form-check-label fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Watermark</label>
                                            <input class="form-check-input ms-0 premium-switch share-setting-mirror" data-share-setting="share-add-watermark" type="checkbox" style="width: 42px; height: 22px;">
                                        </div>
                                        <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center">
                                            <label class="form-check-label fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Notes</label>
                                            <input class="form-check-input ms-0 premium-switch share-setting-mirror" data-share-setting="share-add-note" type="checkbox" style="width: 42px; height: 22px;">
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Include link</h6>
                                                <p class="text-secondary small mb-0" style="max-width: 280px;">Make generated PDF buttons and image calls-to-action open the catalog.</p>
                                            </div>
                                            <div class="form-check form-switch p-0 m-0">
                                                <input class="form-check-input ms-0 premium-switch" type="checkbox" id="share-include-link" checked style="width: 42px; height: 22px; cursor: pointer;">
                                            </div>
                                        </div>
                                    </div>

                                        <!-- Note text input -->
                                        <div class="mb-3" id="note-text-group-details" style="display:none;">
                                            <input type="text" class="form-control rounded-3 p-2 share-setting-mirror" data-share-setting="share-note-text" value="An Award For Every Achievement &amp; Effort" style="font-size: 0.8rem;">
                                        </div>
                                    
                                    <!-- Status Message Overlay -->
                                    <div id="dt-status-log-details" class="alert alert-info py-2 px-3 small rounded-3 d-none mb-3" style="font-size: 0.75rem;"></div>

                                     <div class="d-flex flex-column gap-3 mb-3">
                                         <button id="pdf-share-btn-details" class="btn btn-premium w-100 py-2" onclick="sharePDFSystem('details')" disabled style="opacity: 0.6; pointer-events: none; background: var(--primary-gradient); color: white; font-size: 0.85rem; font-weight: 600; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);">
                                             <i class="bi bi-share-fill me-2"></i> Share PDF to Any App
                                         </button>
                                         
                                         <button id="pdf-direct-btn-details" class="btn btn-premium w-100 py-2" onclick="sharePDFOnWhatsApp('details')" disabled style="opacity: 0.6; pointer-events: none; background: #25D366; color: white; font-size: 0.85rem; font-weight: 600; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.25);">
                                             <i class="bi bi-whatsapp me-2"></i> Share PDF via WhatsApp
                                         </button>

                                         <button id="pdf-download-btn-details" class="btn btn-premium btn-premium-outline w-100 py-2 fw-bold" onclick="generatePDFCatalogue('details')" disabled style="opacity: 0.6; pointer-events: none; font-size: 0.8rem;">
                                             <i class="bi bi-file-earmark-pdf me-2"></i> Download PDF
                                         </button>
                                     </div>
                                    
                                </div>
                                <div class="col-md-6">
                                    <div class="premium-card bg-white border rounded-4 d-flex flex-column h-100" style="min-height: 520px; overflow: hidden;">
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom" style="flex-shrink:0;">
                                            <span class="small fw-bold text-secondary text-uppercase"><i class="bi bi-eye-fill text-danger"></i> PDF Live Preview</span>
                                            <span class="badge bg-danger rounded-pill" id="pdf-preview-status-details">Pending</span>
                                        </div>
                                        
                                        <!-- Loader -->
                                        <div id="pdf-preview-loader-details" class="w-100 flex-grow-1" style="min-height: 460px; overflow: hidden; background: #f1f5f9; display: flex; align-items: center; justify-content: center; position: relative;">
                                            <div class="preview-skeleton-page" style="pointer-events: none; opacity: 0.7; width: 100%; height: 100%;">
                                                <div class="preview-skeleton-header">
                                                    <div class="skeleton-pulse preview-skeleton-logo"></div>
                                                    <div class="skeleton-pulse preview-skeleton-date"></div>
                                                </div>
                                                <div class="skeleton-pulse preview-skeleton-title" style="margin-top: 10px; margin-bottom: 10px;"></div>
                                                <div class="preview-skeleton-grid">
                                                    <div class="preview-skeleton-card">
                                                        <div class="skeleton-pulse preview-skeleton-img"></div>
                                                        <div class="preview-skeleton-row">
                                                            <div class="skeleton-pulse preview-skeleton-name"></div>
                                                            <div class="skeleton-pulse preview-skeleton-price"></div>
                                                        </div>
                                                        <div class="skeleton-pulse preview-skeleton-desc"></div>
                                                    </div>
                                                    <div class="preview-skeleton-card">
                                                        <div class="skeleton-pulse preview-skeleton-img"></div>
                                                        <div class="preview-skeleton-row">
                                                            <div class="skeleton-pulse preview-skeleton-name"></div>
                                                            <div class="skeleton-pulse preview-skeleton-price"></div>
                                                        </div>
                                                        <div class="skeleton-pulse preview-skeleton-desc"></div>
                                                    </div>
                                                    <div class="preview-skeleton-card">
                                                        <div class="skeleton-pulse preview-skeleton-img"></div>
                                                        <div class="preview-skeleton-row">
                                                            <div class="skeleton-pulse preview-skeleton-name"></div>
                                                            <div class="skeleton-pulse preview-skeleton-price"></div>
                                                        </div>
                                                        <div class="skeleton-pulse preview-skeleton-desc"></div>
                                                    </div>
                                                    <div class="preview-skeleton-card">
                                                        <div class="skeleton-pulse preview-skeleton-img"></div>
                                                        <div class="preview-skeleton-row">
                                                            <div class="skeleton-pulse preview-skeleton-name"></div>
                                                            <div class="skeleton-pulse preview-skeleton-price"></div>
                                                        </div>
                                                        <div class="skeleton-pulse preview-skeleton-desc"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Centered Status Text Box -->
                                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(15, 23, 42, 0.88); color: #ffffff; border-radius: 12px; padding: 16px 24px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px); z-index: 5; min-width: 220px;">
                                                <div class="spinner-border text-light mb-2 spinner-border-sm" role="status" style="width: 1.2rem; height: 1.2rem;"></div>
                                                <div id="pdf-preview-loader-text-details" style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Preparing Preview...</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Preview Frame: fills the entire box, no padding -->
                                        <div id="pdf-preview-frame-details" class="d-none w-100 flex-grow-1" style="overflow: hidden; position: relative; background: #f1f5f9; min-height: 460px;">
                                            <div id="pdf-preview-scale-wrap-details" style="width: 100%; height: 100%; overflow: hidden; position: relative; display: flex; align-items: flex-start; justify-content: center;">
                                                <div id="pdf-preview-page-details" style="width: 790px; height: 1117px; transform-origin: top center; background: white; box-shadow: 0 8px 32px rgba(0,0,0,0.18); flex-shrink: 0; position: relative;">
                                                    <div id="pdf-preview-html-details" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="px-3 py-2 border-top text-center" style="flex-shrink:0; background:#f8fafc;">
                                            <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-eye me-1"></i>Full page preview &mdash; actual PDF will match this layout exactly</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Image Share Panel -->
                        <div class="tab-pane fade" id="image-pane" role="tabpanel" aria-labelledby="image-tab" tabindex="0">
                            <div class="row g-4">
                                <div class="col-md-6 d-flex flex-column justify-content-between">
                                    <div>
                                        <h5 class="fw-bold mb-3 text-dark" style="font-size: 1.2rem; letter-spacing: -0.2px;">Share settings</h5>
                                        
                                        <!-- Hidden phone input for backwards compatibility -->
                                        <input type="hidden" id="share-customer-phone" value="">

                                        <!-- Caption Input Styled Nicely -->
                                        <div class="mb-4">
                                            <label class="form-label fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Share Caption Title</label>
                                            <input type="text" class="form-control rounded-3 p-2 bg-light border-0" id="share-catalog-title" value="Premium Selection" style="font-size: 0.85rem; font-weight: 500;">
                                        </div>

                                        <!-- Premium Toggles -->
                                        <div class="d-flex flex-column gap-3 mb-4">
                                            <!-- Show Title Toggle -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Show title</h6>
                                                    <p class="text-secondary small mb-0">Include title in photo</p>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0 premium-switch" type="checkbox" id="share-show-title" checked style="width: 42px; height: 22px; cursor: pointer;">
                                                </div>
                                            </div>

                                            <!-- Show Pricing Toggle -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Show pricing</h6>
                                                    <p class="text-secondary small mb-0">Include price in photo</p>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0 premium-switch" type="checkbox" id="share-show-price" checked style="width: 42px; height: 22px; cursor: pointer;">
                                                </div>
                                            </div>

                                            <!-- Show Additional Pictures Toggle -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Show additional pictures</h6>
                                                    <p class="text-secondary small mb-0" style="max-width: 280px;">if additional pictures are present in the product, they will also be shared</p>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0 premium-switch" type="checkbox" id="share-show-gallery" style="width: 42px; height: 22px; cursor: pointer;">
                                                </div>
                                            </div>

                                            <!-- Add Logo Watermark Toggle -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Add logo watermark</h6>
                                                    <p class="text-secondary small mb-0" style="max-width: 280px;">Show your logo watermark on each photo while sharing</p>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0 premium-switch" type="checkbox" id="share-add-watermark" style="width: 42px; height: 22px; cursor: pointer;">
                                                </div>
                                            </div>

                                            <!-- Add Note Toggle -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Add a note</h6>
                                                    <p class="text-secondary small mb-0" style="max-width: 280px;">Add additional information on your photos like special offers, etc</p>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0 premium-switch" type="checkbox" id="share-add-note" style="width: 42px; height: 22px; cursor: pointer;">
                                                </div>
                                            </div>

                                        </div>

                                        <!-- Inline Note Text Input -->
                                        <div class="mb-4" id="note-text-group">
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-0 text-secondary" style="border-radius: 10px 0 0 10px;"><i class="bi bi-pencil-fill" style="color: #10B981;"></i></span>
                                                <input type="text" class="form-control bg-light border-0 p-2" id="share-note-text" value="An Award For Every Achievement & Effort" style="font-size: 0.85rem; border-radius: 0 10px 10px 0; font-weight: 500;">
                                            </div>
                                        </div>


                                    </div>
                                    
                                    <!-- Status Message Overlay -->
                                    <div id="dt-status-log-images" class="alert alert-info py-2 px-3 small rounded-3 d-none mb-3" style="font-size: 0.75rem;"></div>

                                    <!-- Professional Real-time Progress UI -->
                                    <div id="image-share-progress-container" class="d-none mb-3 p-3.5 rounded-4" style="background:#f8fafc; border:1px solid #e2e8f0; font-family:'Outfit',sans-serif;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 id="isp-title" class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">Preparing WhatsApp Images...</h6>
                                            <span id="isp-speed" class="badge bg-success text-white px-2.5 py-1 rounded-pill" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Processing: Fast</span>
                                        </div>
                                        
                                        <div style="width:100%; height:8px; background:#e2e8f0; border-radius:999px; overflow:hidden; margin:12px 0 10px; position:relative;">
                                            <div id="isp-bar" class="progress-bar-animated-premium" style="width:0%; height:100%; border-radius:999px; transition:width 0.2s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3" style="font-size: 0.8rem; font-weight: 700;">
                                            <span id="isp-count" class="text-secondary">0 / 0 Images Ready</span>
                                            <span id="isp-percent" class="text-primary">0% Completed</span>
                                        </div>
                                        
                                        <div id="isp-status-box" class="p-2.5 rounded-3" style="background:#ffffff; font-size:0.75rem; color:#64748b; font-weight:600; border:1px solid #f1f5f9; box-sizing:border-box;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Estimated Time Left:</span>
                                                <span id="isp-time" class="text-dark fw-bold">Estimating...</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1.5">
                                                <span>Status:</span>
                                                <span id="isp-status" class="text-dark fw-bold">Initializing...</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Interactive Batch Share Button for User Gesture Constraints -->
                                        <div id="isp-action-box" class="mt-2.5 d-none">
                                            <button type="button" id="isp-action-btn" class="btn btn-success w-100 py-2.5 fw-bold text-white font-outfit btn-custom-pulse" style="font-size: 0.85rem; border: 0; border-radius: 12px; background: linear-gradient(135deg, #25D366 0%, #128C7E 100%) !important;">
                                                <i class="bi bi-whatsapp me-2"></i> Share Next Batch
                                            </button>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-3 mt-auto">
                                        <button id="pdf-share-btn-images" class="btn btn-premium w-100 py-2.5" onclick="shareImageSystem()" disabled style="opacity: 0.6; pointer-events: none; background: var(--primary-gradient); color: white; font-size: 0.85rem; font-weight: 600; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);">
                                            <i class="bi bi-share-fill me-2"></i> Share Images to Any App
                                        </button>
                                        
                                        <button id="pdf-direct-btn-images" class="btn btn-premium w-100 py-2.5" onclick="shareSeparateImages()" disabled style="opacity: 0.6; pointer-events: none; background: #25D366; color: white; font-size: 0.85rem; font-weight: 600; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.25);">
                                            <i class="bi bi-whatsapp me-2"></i> Share Images via WhatsApp
                                        </button>

                                        <button id="pdf-download-btn-images" class="btn btn-premium btn-premium-outline w-100 py-2.5 fw-bold" onclick="downloadAllCards()" disabled style="opacity: 0.6; pointer-events: none; font-size: 0.8rem;">
                                            <i class="bi bi-download me-2"></i> Download Images
                                        </button>

                                    </div>
                                    
                                </div>
                                <div class="col-md-6">
                                    <div class="premium-card bg-white border rounded-4 d-flex flex-column h-100" style="min-height: 520px; overflow: hidden;">
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom" style="flex-shrink:0;">
                                            <span class="small fw-bold text-secondary text-uppercase"><i class="bi bi-eye-fill text-accent"></i> Image Share Preview</span>
                                            <span class="badge bg-accent rounded-pill text-white" id="pdf-preview-status-images">Pending</span>
                                        </div>
                                        
                                        <!-- Loader -->
                                        <div id="pdf-preview-loader-images" class="w-100 flex-grow-1" style="min-height: 460px; overflow: hidden; background: #f1f5f9; display: flex; align-items: center; justify-content: center; position: relative;">
                                            <div class="preview-skeleton-page" style="pointer-events: none; opacity: 0.8; width: 100%; height: 100%; padding: 16px;">
                                                <div class="share-image-preview-grid" style="padding: 0; gap: 14px; pointer-events: none;">
                                                    <div class="preview-skeleton-card" style="width: 100%; aspect-ratio: 4/5; border-radius: 16px; border: 1px solid #e2e8f0; background: #ffffff; padding: 0; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                                        <div style="flex-grow: 1; position: relative; background: #f8fafc;">
                                                            <div class="skeleton-pulse" style="width: 100%; height: 100%;"></div>
                                                            <div style="position: absolute; top: 16px; left: 16px; width: 60%; height: 16px; border-radius: 4px;" class="skeleton-pulse"></div>
                                                            <div style="position: absolute; top: 38px; left: 16px; width: 40%; height: 10px; border-radius: 4px;" class="skeleton-pulse"></div>
                                                        </div>
                                                        <div style="height: 40px; background: #000000; padding: 0 16px; display: flex; justify-content: space-between; align-items: center; box-sizing: border-box;">
                                                            <div style="width: 45%; height: 10px; border-radius: 4px; background: #333;" class="skeleton-pulse"></div>
                                                            <div style="width: 25%; height: 12px; border-radius: 4px; background: #333;" class="skeleton-pulse"></div>
                                                        </div>
                                                    </div>
                                                    <div class="preview-skeleton-card" style="width: 100%; aspect-ratio: 4/5; border-radius: 16px; border: 1px solid #e2e8f0; background: #ffffff; padding: 0; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                                        <div style="flex-grow: 1; position: relative; background: #f8fafc;">
                                                            <div class="skeleton-pulse" style="width: 100%; height: 100%;"></div>
                                                            <div style="position: absolute; top: 16px; left: 16px; width: 60%; height: 16px; border-radius: 4px;" class="skeleton-pulse"></div>
                                                            <div style="position: absolute; top: 38px; left: 16px; width: 40%; height: 10px; border-radius: 4px;" class="skeleton-pulse"></div>
                                                        </div>
                                                        <div style="height: 40px; background: #000000; padding: 0 16px; display: flex; justify-content: space-between; align-items: center; box-sizing: border-box;">
                                                            <div style="width: 45%; height: 10px; border-radius: 4px; background: #333;" class="skeleton-pulse"></div>
                                                            <div style="width: 25%; height: 12px; border-radius: 4px; background: #333;" class="skeleton-pulse"></div>
                                                        </div>
                                                    </div>
                                                    <div class="preview-skeleton-card" style="width: 100%; aspect-ratio: 4/5; border-radius: 16px; border: 1px solid #e2e8f0; background: #ffffff; padding: 0; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                                        <div style="flex-grow: 1; position: relative; background: #f8fafc;">
                                                            <div class="skeleton-pulse" style="width: 100%; height: 100%;"></div>
                                                            <div style="position: absolute; top: 16px; left: 16px; width: 60%; height: 16px; border-radius: 4px;" class="skeleton-pulse"></div>
                                                            <div style="position: absolute; top: 38px; left: 16px; width: 40%; height: 10px; border-radius: 4px;" class="skeleton-pulse"></div>
                                                        </div>
                                                        <div style="height: 40px; background: #000000; padding: 0 16px; display: flex; justify-content: space-between; align-items: center; box-sizing: border-box;">
                                                            <div style="width: 45%; height: 10px; border-radius: 4px; background: #333;" class="skeleton-pulse"></div>
                                                            <div style="width: 25%; height: 12px; border-radius: 4px; background: #333;" class="skeleton-pulse"></div>
                                                        </div>
                                                    </div>
                                                    <div class="preview-skeleton-card" style="width: 100%; aspect-ratio: 4/5; border-radius: 16px; border: 1px solid #e2e8f0; background: #ffffff; padding: 0; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                                        <div style="flex-grow: 1; position: relative; background: #f8fafc;">
                                                            <div class="skeleton-pulse" style="width: 100%; height: 100%;"></div>
                                                            <div style="position: absolute; top: 16px; left: 16px; width: 60%; height: 16px; border-radius: 4px;" class="skeleton-pulse"></div>
                                                            <div style="position: absolute; top: 38px; left: 16px; width: 40%; height: 10px; border-radius: 4px;" class="skeleton-pulse"></div>
                                                        </div>
                                                        <div style="height: 40px; background: #000000; padding: 0 16px; display: flex; justify-content: space-between; align-items: center; box-sizing: border-box;">
                                                            <div style="width: 45%; height: 10px; border-radius: 4px; background: #333;" class="skeleton-pulse"></div>
                                                            <div style="width: 25%; height: 12px; border-radius: 4px; background: #333;" class="skeleton-pulse"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Centered Status Text Box -->
                                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(15, 23, 42, 0.88); color: #ffffff; border-radius: 12px; padding: 16px 24px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px); z-index: 5; min-width: 220px;">
                                                <div class="spinner-border text-accent mb-2 spinner-border-sm" role="status" style="width: 1.2rem; height: 1.2rem;"></div>
                                                <div id="pdf-preview-loader-text-images" style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Preparing Preview...</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Preview Frame: fills the entire box, no padding -->
                                        <div id="pdf-preview-frame-images" class="d-none w-100 flex-grow-1" style="overflow: hidden; position: relative; background: #f1f5f9; min-height: 460px;">
                                            <!-- Direct image preview cards render here -->
                                            <div id="pdf-preview-scale-wrap-images" style="width: 100%; height: 100%; overflow: hidden; position: relative; display: flex; align-items: flex-start; justify-content: center;">
                                                <div id="pdf-preview-page-images" style="width: 790px; height: 1117px; transform-origin: top center; background: white; box-shadow: 0 8px 32px rgba(0,0,0,0.18); flex-shrink: 0; position: relative;">
                                                    <div id="pdf-preview-html-images" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="px-3 py-2 border-top text-center" style="flex-shrink:0; background:#f8fafc;">
                                            <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-phone me-1"></i>Vertical social images with clickable product links in preview and captions</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Overlay Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="p-4 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-search text-primary fs-4"></i>
                        <input type="text" id="catalog-search" class="form-control border-0 p-2 fs-5 outline-none shadow-none w-100" placeholder="Type keyword (e.g. Sweater, Polo, Drinkware, Awards)..." autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0 border-top bg-light" style="max-height: 400px; overflow-y: auto;" id="search-results-pane">
                    <div class="p-4 text-center text-secondary small">
                        Type keyword to search among high-end catalog items.
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Off-screen PDF Catalog Layout Container (Invisible to user but rendered in viewport for high-fidelity captures) -->
    <div id="pdf-rendering-container" style="position: fixed; top: 0; left: -10000px; width: 790px; z-index: -9999; opacity: 1; visibility: visible; pointer-events: none; background: white;">
        <div id="pdf-template-wrapper" style="width: 790px; background: white; padding: 40px; font-family: 'Poppins', sans-serif;">
            <!-- Populated dynamically via JS -->
        </div>
    </div>

    <!-- Off-screen Square Card Capture Container (800px x 800px) -->
    <div id="share-card-render-container" style="position: fixed; top: 0; left: -10000px; width: 800px; height: 800px; z-index: -99999; opacity: 1; visibility: visible; pointer-events: none; background: white;">
        <div id="share-card-template-wrapper" style="width: 800px; height: 800px; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; position: relative; box-sizing: border-box;">
            <!-- Populated dynamically during html2canvas captures -->
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ============================================================
        // GLOBAL SCOPE: selectedProducts and core interactive functions
        // must live outside $(document).ready() so inline onclick
        // handlers can always find them, regardless of DOM-ready timing.
        // ============================================================
        const isSubscriber = {{ (isset($isSubscriberStore) && $isSubscriberStore) ? 'true' : 'false' }};
        const companySlug = "{{ isset($profile) ? $profile->company_slug : '' }}";
        const storageKey = isSubscriber ? 'selected_products_store_' + companySlug : 'selected_products_admin';

        var selectedProducts = [];
        window.companyLogoBase64 = "@if($settings && $settings->logo && !empty($logoBase64)){{ $logoBase64 }}@else @endif";
        window.companySiteTitle = "{{ $siteTitle ?? 'CataSky' }}";
        window.isSubscriberStore = {{ (isset($isSubscriberStore) && $isSubscriberStore) ? 'true' : 'false' }};
        window.userIsSubscriber = {{ (auth()->check() && auth()->user()->isSubscriber()) ? 'true' : 'false' }};
        window.currentUserId = {{ auth()->check() ? auth()->id() : 'null' }};
        window.storeOwnerId = {{ isset($profile) ? $profile->user_id : 'null' }};
        const exportSettings = {
            showTitle: true,
            showPrice: true,
            showGallery: false,
            showWatermark: false,
            showNotes: false,
            includeLink: true,
            catalogTitle: 'Premium Selection',
            noteText: 'An Award For Every Achievement & Effort',
            logoPos: 'bottom-right'
        };
        let currentExportMode = 'pdf';
        window.exportSettings = exportSettings;
        window.currentExportMode = currentExportMode;
        window.renderedPreviews = { details: false, images: false };
        window.preparedShareDocs = window.preparedShareDocs || {};
        window.exportBuildTokens = { details: 0, images: 0 };
        window.imageCache = new Map();

        function getRelativeImageUrl(url) {
            if (!url) return '';
            try {
                if (url.indexOf('http://') === 0 || url.indexOf('https://') === 0) {
                    const parsed = new URL(url);
                    if (parsed.pathname.indexOf('/uploads/') > -1 || parsed.pathname.indexOf('/storage/') > -1) {
                        return parsed.pathname;
                    }
                }
            } catch (e) {
                console.error("Error sanitizing URL:", e);
            }
            return url;
        }
        window.getRelativeImageUrl = getRelativeImageUrl;

        function setCurrentExportMode(mode) {
            currentExportMode = mode === 'image' ? 'image' : 'pdf';
            window.currentExportMode = currentExportMode;
            return currentExportMode;
        }

        function getCurrentExportType() {
            return currentExportMode === 'image' ? 'images' : 'details';
        }

        function normalizeExportType(type) {
            if (type === 'images' || type === 'image') return 'images';
            if (type === 'details' || type === 'pdf') return 'details';
            return getCurrentExportType();
        }

        function formatProductPrice(product, fallback = '') {
            if (!product) return fallback;
            const rawPrice = [product.sale_price, product.offer_price, product.price, product.mrp].find(value => {
                return value !== null && value !== undefined && String(value).trim() !== '' && !Number.isNaN(Number(value));
            }) || null;
            if (rawPrice !== null && !Number.isNaN(Number(rawPrice))) {
                return '₹ ' + Number(rawPrice).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            return product.variant || fallback;
        }
        window.formatProductPrice = formatProductPrice;

        function setExportButtonsState(type, enabled, loadingText) {
            type = normalizeExportType(type);
            const buttons = $(`#pdf-download-btn-${type}, #pdf-direct-btn-${type}, #pdf-share-btn-${type}, #pdf-api-btn-${type}`);
            buttons.each(function() {
                const btn = $(this);
                if (!btn.data('ready-html')) {
                    btn.data('ready-html', btn.html());
                }
                if (enabled) {
                    btn.removeAttr('disabled')
                        .css({ opacity: '1', 'pointer-events': 'auto' })
                        .html(btn.data('ready-html'));
                    return;
                }
                btn.attr('disabled', true)
                    .css({ opacity: '0.5', 'pointer-events': 'none' });
                if (loadingText) {
                    btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>' + loadingText);
                }
            });
        }

        function updateProgressText(type, text) {
            type = normalizeExportType(type);
            const textEl = document.getElementById(`pdf-preview-loader-text-${type}`);
            if (textEl) {
                textEl.textContent = text;
            }
            const buttons = $(`#pdf-download-btn-${type}, #pdf-direct-btn-${type}, #pdf-share-btn-${type}, #pdf-api-btn-${type}`);
            buttons.each(function() {
                const btn = $(this);
                if (btn.is(':disabled')) {
                    btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>' + text);
                }
            });
        }
        window.updateProgressText = updateProgressText;
        window.setExportButtonsState = setExportButtonsState;
        try {
            selectedProducts = JSON.parse(localStorage.getItem(storageKey)) || [];
            if (!Array.isArray(selectedProducts)) {
                selectedProducts = [];
            }
            // Sanitize selection to prevent null/undefined/empty string items
            selectedProducts = selectedProducts.filter(id => id !== null && id !== undefined && id.toString().trim() !== "");
        } catch (e) {
            console.error("Error parsing selected_products from localStorage:", e);
            selectedProducts = [];
        }

        // Restrict subscribers: clear selection if they are on an admin or unauthorized catalog/page
        if (window.userIsSubscriber) {
            if (!window.isSubscriberStore || window.currentUserId !== window.storeOwnerId) {
                selectedProducts = [];
                localStorage.setItem(storageKey, JSON.stringify([]));
            }
        }

        // Smart device-aware WhatsApp router (routes to WhatsApp Web on desktops/Chrome, and App on mobile)
        window.openWhatsAppChat = function(msg) {
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const encoded = encodeURIComponent(msg);
            let url = '';
            if (isMobile) {
                url = 'https://api.whatsapp.com/send?text=' + encoded;
            } else {
                url = 'https://web.whatsapp.com/send?text=' + encoded;
            }
            window.open(url, '_blank');
        };

        // Global Cache for product details to avoid repetitive AJAX network requests
        window.cachedProductDetails = window.cachedProductDetails || {};

        function fetchProductDetailsCached(id) {
            if (id === null || id === undefined) {
                return Promise.resolve({ success: false, error: 'Invalid ID' });
            }
            const idStr = id.toString().trim();
            if (!idStr) {
                return Promise.resolve({ success: false, error: 'Empty ID' });
            }
            if (window.cachedProductDetails[idStr]) {
                return Promise.resolve(window.cachedProductDetails[idStr]);
            }
            const url = '/api/product-details/' + idStr + (window.isSubscriberStore ? '?is_subscriber=1&company_slug=' + companySlug : '');
            return fetch(url)
                .then(res => {
                    if (!res.ok) {
                        throw new Error('API HTTP error: ' + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    if (data && data.success) {
                        window.cachedProductDetails[idStr] = data;
                    }
                    return data;
                })
                .catch(err => {
                    console.error("fetchProductDetailsCached error for ID " + idStr + ":", err);
                    return { success: false, error: err.message || err };
                });
        }

        window.activeFetchPromise = null;
        function fetchMultipleProductDetails(ids) {
            if (!ids || ids.length === 0) {
                return Promise.resolve();
            }
            const uncachedIds = ids.map(id => id.toString().trim()).filter(idStr => idStr && !window.cachedProductDetails[idStr]);
            if (uncachedIds.length === 0) {
                return Promise.resolve();
            }
            
            if (window.activeFetchPromise) {
                return window.activeFetchPromise.then(() => fetchMultipleProductDetails(ids));
            }
            
            const url = '/api/products-details?ids=' + encodeURIComponent(uncachedIds.join(',')) + (window.isSubscriberStore ? '&is_subscriber=1&company_slug=' + companySlug : '');
            window.activeFetchPromise = fetch(url)
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Bulk API HTTP error: ' + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    if (data && data.success && data.products) {
                        Object.keys(data.products).forEach(idKey => {
                            window.cachedProductDetails[idKey] = data.products[idKey];
                        });
                    }
                })
                .catch(err => {
                    console.error("fetchMultipleProductDetails bulk error:", err);
                })
                .finally(() => {
                    window.activeFetchPromise = null;
                });
                
            return window.activeFetchPromise;
        }

        function toggleSelection(productId, btn) {
            if (window.userIsSubscriber) {
                if (!window.isSubscriberStore || window.currentUserId !== window.storeOwnerId) {
                    alert("Subscribers can only select and share their own products from their own storefront catalog.");
                    return;
                }
            }
            productId = productId.toString();
            var index = selectedProducts.indexOf(productId);

            if (index > -1) {
                selectedProducts.splice(index, 1);
                if (btn) {
                    $(btn).removeClass('btn-premium-primary btn-premium-dark').addClass('btn-premium-outline').html('<i class="bi bi-bag-plus"></i> Select');
                }
                trackAnalyticsEventSafe('deselect_product', productId);
            } else {
                selectedProducts.push(productId);
                if (btn) {
                    $(btn).removeClass('btn-premium-outline btn-premium-dark').addClass('btn-premium-primary').html('<i class="bi bi-bag-check-fill"></i> Selected');
                }
                trackAnalyticsEventSafe('select_product', productId);
            }

            localStorage.setItem(storageKey, JSON.stringify(selectedProducts));
            if (typeof window.invalidatePreparedShareDocs === 'function') {
                window.invalidatePreparedShareDocs();
            } else if (window.preparedShareDocs) {
                window.preparedShareDocs = {};
            }
            updateSelectionUISafe();

            // If drawer is open, sync its select button too
            if ($('#product-drawer').hasClass('active')) {
                var $drawerBtn = $('#drawer-content').find('.btn-drawer-select');
                if (selectedProducts.indexOf(productId) > -1) {
                    $drawerBtn.removeClass('btn-premium-primary').addClass('btn-premium-dark').html('<i class="bi bi-bag-check-fill"></i> Product Selected');
                } else {
                    $drawerBtn.removeClass('btn-premium-dark').addClass('btn-premium-primary').html('<i class="bi bi-bag-plus"></i> Add to Selection');
                }
            }
        }

        window.toggleSelection = toggleSelection;

        // Safe wrappers that call the ready-block functions if available
        function updateSelectionUISafe() {
            if (typeof window.updateSelectionUI === 'function') {
                window.updateSelectionUI();
            } else if (typeof updateSelectionUI === 'function') {
                updateSelectionUI();
            }
        }
        function trackAnalyticsEventSafe(type, data) {
            if (typeof trackAnalyticsEvent === 'function') trackAnalyticsEvent(type, data);
        }

        $(document).ready(function() {
            // Scroll navbar styling
            $(window).scroll(function() {
                if ($(this).scrollTop() > 30) {
                    $('.navbar-premium').addClass('scrolled');
                } else {
                    $('.navbar-premium').removeClass('scrolled');
                }
            });

            // Toggle Side Drawer
            window.openDrawer = function(productId) {
                $('#drawer-overlay').addClass('active');
                $('#product-drawer').addClass('active');
                $('body').css('overflow', 'hidden'); // prevent bg scroll
                
                // Show spinner
                $('#drawer-content').html(`
                    <div class="d-flex flex-column justify-content-center align-items-center h-100 py-5">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                        <span class="text-secondary small fw-semibold">Retreiving specification sheet...</span>
                    </div>
                `);
                
                // Track drawer open analytics
                trackAnalyticsEvent('view_details', productId);

                // Load product details via AJAX
                const url = '/product/' + productId + '/details' + (window.isSubscriberStore ? '?is_subscriber=1&company_slug=' + companySlug : '');
                $.get(url, function(response) {
                    $('#drawer-content').html(response);
                    syncDrawerButton(productId);
                }).fail(function() {
                    $('#drawer-content').html(`
                        <div class="p-4 text-center">
                            <i class="bi bi-exclamation-triangle text-danger display-4 mb-3"></i>
                            <h6 class="fw-bold">Failed to load spec sheet</h6>
                            <p class="text-secondary small">The requested item details could not be loaded at this time. Please try again.</p>
                        </div>
                    `);
                });
            };

            function syncDrawerButton(productId) {
                const index = selectedProducts.indexOf(productId.toString());
                const btn = $('#drawer-content').find('.btn-drawer-select');
                if (index > -1) {
                    btn.removeClass('btn-premium-primary').addClass('btn-premium-dark').html('<i class="bi bi-bag-check-fill"></i> Product Selected');
                } else {
                    btn.removeClass('btn-premium-dark').addClass('btn-premium-primary').html('<i class="bi bi-bag-plus"></i> Add to Selection');
                }
            }

            window.closeDrawer = function() {
                $('#drawer-overlay').removeClass('active');
                $('#product-drawer').removeClass('active');
                $('body').css('overflow', 'auto');
            };

            $('#drawer-close, #drawer-overlay').click(function() {
                closeDrawer();
            });

            // Selection Logic - now handled by global toggleSelection() above.
            // Keep window alias for any legacy callers.
            window.toggleSelection = toggleSelection;

            function animateButton(btn, type, text) {
                if (!btn) return;
                const $btn = $(btn);
                $btn.addClass('animate-pulse-ripple');
                setTimeout(() => $btn.removeClass('animate-pulse-ripple'), 500);

                if (type === 'primary') {
                    $btn.removeClass('btn-premium-outline btn-premium-dark').addClass('btn-premium-primary').html(text);
                } else {
                    $btn.removeClass('btn-premium-primary btn-premium-dark').addClass('btn-premium-outline').html(text);
                }
            }

            window.renderCardIntoPreview = function() {
                if (selectedProducts.length === 0) return;
                
                const id = selectedProducts[0];
                fetchProductDetailsCached(id).then(data => {
                    if (!data || !data.success) return;
                    
                    const p = data.product;
                    const imgUrl = getRelativeImageUrl(data.thumbnail_url || '');
                    
                    $('#preview-img-src').attr('src', imgUrl);
                    
                    const settings = getShareSettings();
                    const showTitle = settings.showTitle;
                    const showPrice = settings.showPrice;
                    const showWatermark = settings.showWatermark;
                    const showNote = settings.showNotes;
                    const noteText = settings.noteText || '';
                    const logoPos = settings.logoPos || 'bottom-right';

                    // Toggles
                    if (showTitle) {
                        $('#preview-footer-title').text(p.name).show();
                    } else {
                        $('#preview-footer-title').hide();
                    }

                    const displayPrice = formatProductPrice(p);
                    if (showPrice) {
                        $('#preview-footer-price').html(displayPrice).show();
                    } else {
                        $('#preview-footer-price').hide();
                    }

                    if (showWatermark && window.companyLogoBase64) {
                        $('#preview-watermark').show();
                    } else {
                        $('#preview-watermark').hide();
                    }

                    if (showNote) {
                        $('#preview-yellow-bar').css('display', 'flex');
                        $('#preview-bar-code').text('CODE: ' + (p.part_code || ''));
                        $('#preview-bar-note').text(noteText);
                        $('#preview-footer-code').text('CODE: ' + (p.part_code || '')).show();
                    } else {
                        $('#preview-yellow-bar').hide();
                        $('#preview-footer-code').hide();
                    }

                    // Logo Position css adjustments
                    let logoCss = {};
                    if (logoPos === 'top-left') {
                        logoCss = { top: '15px', left: '15px', bottom: 'auto', right: 'auto' };
                    } else if (logoPos === 'top-right') {
                        logoCss = { top: '15px', right: '15px', bottom: 'auto', left: 'auto' };
                    } else if (logoPos === 'bottom-left') {
                        logoCss = { bottom: showNote ? '125px' : '75px', left: '15px', top: 'auto', right: 'auto' };
                    } else { // bottom-right
                        logoCss = { bottom: showNote ? '125px' : '75px', right: '15px', top: 'auto', left: 'auto' };
                    }
                    $('#preview-watermark').css({
                        top: 'auto', left: 'auto', bottom: 'auto', right: 'auto',
                        ...logoCss
                    });
                });
            };

            // Bind settings changes to live CSS preview updates (only on change, preventing keyup rendering storms)
            $(document).on('change', '#share-catalog-title, #share-show-title, #share-show-price, #share-show-gallery, #share-add-watermark, #share-add-note, #share-include-link, #share-note-text', function() {
                updateExportSettingsFromControls();
                invalidatePreparedShareDocs();
                syncShareSettingMirrors();
                if ($('#image-tab').hasClass('active')) {
                    generateLiveImagePreview();
                } else if ($('#pdf-tab').hasClass('active')) {
                    generateLivePDFPreview('details');
                } else {
                    window.renderedPreviews.images = false;
                    window.renderedPreviews.details = false;
                }
            });

            function syncShareSettingMirrors() {
                $('.share-setting-mirror').each(function() {
                    const targetId = $(this).data('share-setting');
                    const target = $('#' + targetId);
                    if (!target.length) return;
                    if ($(this).is(':checkbox')) {
                        $(this).prop('checked', target.is(':checked'));
                    } else {
                        $(this).val(target.val());
                    }
                });
            }

            $(document).on('change', '.share-setting-mirror', function() {
                const targetId = $(this).data('share-setting');
                const target = $('#' + targetId);
                if (!target.length) return;
                if ($(this).is(':checkbox')) {
                    target.prop('checked', $(this).is(':checked'));
                } else {
                    target.val($(this).val());
                }
                updateExportSettingsFromControls();
                target.trigger('change');
                invalidatePreparedShareDocs();
                if ($('#pdf-tab').hasClass('active')) {
                    generateLivePDFPreview('details');
                }
                if ($('#image-tab').hasClass('active')) {
                    generateLiveImagePreview();
                }
            });

            $(document).on('click', '.logo-pos-btn', function() {
                $('.logo-pos-btn').removeClass('active');
                $(this).addClass('active');
                $('#share-logo-pos').val($(this).data('pos'));
                updateExportSettingsFromControls();
                invalidatePreparedShareDocs();
                if ($('#image-tab').hasClass('active')) {
                    generateLiveImagePreview();
                } else if ($('#pdf-tab').hasClass('active')) {
                    generateLivePDFPreview('details');
                } else {
                    window.renderedPreviews.images = false;
                    window.renderedPreviews.details = false;
                }
            });

            $(document).on('change', '#share-add-note', function() {
                if ($(this).is(':checked')) {
                    $('#share-note-text').removeAttr('disabled');
                } else {
                    $('#share-note-text').attr('disabled', true);
                }
            });

            function syncWatermarkPositionControls() {
                $('#watermark-pos-group').toggle($('#share-add-watermark').is(':checked'));
            }

            applyExportSettingsToControls();
            syncShareSettingMirrors();
            syncWatermarkPositionControls();
            $(document).on('change', '#share-add-watermark', syncWatermarkPositionControls);

            function updateExportSettingsFromControls() {
                exportSettings.catalogTitle = $('#share-catalog-title').val() || 'Premium Selection';
                exportSettings.showTitle = $('#share-show-title').is(':checked');
                exportSettings.showPrice = $('#share-show-price').is(':checked');
                exportSettings.showGallery = $('#share-show-gallery').is(':checked');
                exportSettings.showWatermark = $('#share-add-watermark').is(':checked');
                exportSettings.showNotes = $('#share-add-note').is(':checked');
                exportSettings.showNote = exportSettings.showNotes;
                exportSettings.includeLink = $('#share-include-link').is(':checked');
                exportSettings.noteText = $('#share-note-text').val() || '';
                exportSettings.logoPos = $('#share-logo-pos').val() || 'bottom-right';
                return exportSettings;
            }

            function applyExportSettingsToControls() {
                $('#share-catalog-title').val(exportSettings.catalogTitle);
                $('#share-show-title').prop('checked', !!exportSettings.showTitle);
                $('#share-show-price').prop('checked', !!exportSettings.showPrice);
                $('#share-show-gallery').prop('checked', !!exportSettings.showGallery);
                $('#share-add-watermark').prop('checked', !!exportSettings.showWatermark);
                $('#share-add-note').prop('checked', !!exportSettings.showNotes);
                $('#share-include-link').prop('checked', !!exportSettings.includeLink);
                $('#share-note-text').val(exportSettings.noteText || '');
                $('#share-logo-pos').val(exportSettings.logoPos || 'bottom-right');
            }

            function getShareSettings() {
                return updateExportSettingsFromControls();
            }
            window.getShareSettings = getShareSettings;

            function getShareCacheKey(type) {
                type = normalizeExportType(type);
                return JSON.stringify({
                    type: type,
                    products: (selectedProducts || []).map(String),
                    settings: getShareSettings()
                });
            }

            let _webpSupported = null;
            function isWebPSupported() {
                if (_webpSupported !== null) return _webpSupported;
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = canvas.height = 1;
                    _webpSupported = canvas.toDataURL('image/webp').indexOf('image/webp') > -1;
                } catch (e) {
                    _webpSupported = false;
                }
                return _webpSupported;
            }

            function getCardCacheKey(productId, imgUrl) {
                const settings = getShareSettings();
                return JSON.stringify({
                    id: productId,
                    url: imgUrl,
                    showTitle: settings.showTitle,
                    showPrice: settings.showPrice,
                    showWatermark: settings.showWatermark,
                    showNote: settings.showNotes,
                    includeLink: settings.includeLink,
                    noteText: settings.noteText,
                    logoPos: settings.logoPos
                });
            }

            async function processInParallelBatches(items, concurrencyLimit, workerFn, progressFn) {
                const results = new Array(items.length);
                let currentIndex = 0;
                window.imageExportCancelled = false;

                async function worker() {
                    while (currentIndex < items.length) {
                        if (window.imageExportCancelled) {
                            throw new Error('Export cancelled by user');
                        }
                        const index = currentIndex++;
                        if (index >= items.length) break;
                        const item = items[index];
                        try {
                            if (window.imageExportCancelled) {
                                throw new Error('Export cancelled by user');
                            }
                            results[index] = await workerFn(item, index);
                        } catch (err) {
                            console.error(`Error processing batch item at index ${index}:`, err);
                            results[index] = null;
                        }
                        if (progressFn) {
                            progressFn(results.filter(r => r !== undefined).length, items.length);
                        }
                    }
                }

                const workers = [];
                const actualLimit = Math.min(concurrencyLimit, items.length);
                for (let i = 0; i < actualLimit; i++) {
                    workers.push(worker());
                }

                await Promise.all(workers);
                return results.filter(r => r !== null);
            }

            async function preloadProductCardImages(imageItems) {
                const promises = imageItems.map(item => {
                    return new Promise(resolve => {
                        const img = new Image();
                        img.src = getRelativeImageUrl(item.imageUrl || '');
                        if (img.complete) {
                            img.decode().then(resolve).catch(resolve);
                        } else {
                            img.onload = () => img.decode().then(resolve).catch(resolve);
                            img.onerror = resolve;
                        }
                    });
                });
                await Promise.all(promises);
            }

            // Performance-optimized Image Loader with CORS and DOM Element Cache
            window.imageElementCache = window.imageElementCache || {};
            function loadImage(url) {
                return new Promise((resolve) => {
                    if (!url) {
                        resolve(null);
                        return;
                    }
                    if (window.imageElementCache[url]) {
                        resolve(window.imageElementCache[url]);
                        return;
                    }
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = () => {
                        window.imageElementCache[url] = img;
                        resolve(img);
                    };
                    img.onerror = () => {
                        // Fallback loading without crossOrigin if CORS fails
                        const fallbackImg = new Image();
                        fallbackImg.onload = () => {
                            window.imageElementCache[url] = fallbackImg;
                            resolve(fallbackImg);
                        };
                        fallbackImg.onerror = () => resolve(null);
                        fallbackImg.src = url;
                    };
                    img.src = url;
                });
            }

            async function preloadImage(url) {
                return loadImage(url);
            }
            window.preloadImage = preloadImage;


            // Real-time sharing progress UI inside Export Selection modal
            function showImageProgressUI(total, mode = 'share') {
                $('#isp-title').text('Preparing Images...');
                $('#image-share-progress-container').removeClass('d-none').addClass('d-block');
                
                // Reset progress values
                $('#isp-bar').css('width', '0%');
                $('#isp-count').text(`0 / ${total} Ready`);
                $('#isp-percent').text('0% Completed');
                $('#isp-time').text('Estimating...');
                $('#isp-status').text('Initializing graphics...');
                $('#isp-speed').text('Processing: Fast').removeClass('bg-warning bg-danger').addClass('bg-success');
                
                // Reset monotonic counter — progress can only go FORWARD
                window._imageProgressMax = 0;

                window.imageExportCancelled = false;
            }

            function hideImageProgressUI() {
                $('#image-share-progress-container').removeClass('d-block').addClass('d-none');
            }

            function updateImageProgressUI(completed, total, elapsed) {
                if (window.imageExportCancelled) return;

                const percent = Math.min(Math.round((completed / total) * 100), 100);
                $('#isp-bar').css('width', `${percent}%`);
                $('#isp-count').text(`${completed} / ${total} Ready`);
                $('#isp-percent').text(`${percent}% Completed`);

                if (completed > 0) {
                    const avgTime = elapsed / completed;
                    const remaining = total - completed;
                    const timeLeft = Math.max(Math.round(avgTime * remaining), 0);
                    $('#isp-time').text(`${timeLeft}s`);
                    
                    let speedText = 'Fast';
                    if (avgTime < 0.1) {
                        speedText = 'Ultra-Fast';
                    } else if (avgTime < 0.25) {
                        speedText = 'Fast';
                    } else if (avgTime < 0.6) {
                        speedText = 'Normal';
                    } else {
                        speedText = 'Slow';
                    }
                    $('#isp-speed').text(`Processing: ${speedText}`);
                    
                    if (completed === total) {
                        $('#isp-status').text('All product card images compiled!');
                    } else {
                        // Highlight preparing count
                        $('#isp-status').text(`Rendering card ${completed} of ${total}...`);
                    }
                } else {
                    $('#isp-time').text('Estimating...');
                    $('#isp-status').text('Preloading product graphics...');
                }
            }


            function invalidatePreparedShareDocs(type) {
                if (window.imageCache) {
                    window.imageCache.clear();
                }
                if (!window.preparedShareDocs) {
                    window.preparedShareDocs = {};
                    return;
                }
                window.exportBuildTokens.details++;
                window.exportBuildTokens.images++;
                if (type) {
                    delete window.preparedShareDocs[type];
                    setExportButtonsState(type, false);
                } else {
                    window.preparedShareDocs = {};
                    setExportButtonsState('details', false);
                    setExportButtonsState('images', false);
                }
            }
            window.invalidatePreparedShareDocs = invalidatePreparedShareDocs;

            function downloadBlob(blob, filename) {
                const objectUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = objectUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                setTimeout(function() { URL.revokeObjectURL(objectUrl); }, 1500);
            }

            function notifyInfo(title, message) {
                if (window.alertService && typeof window.alertService.infoAlert === 'function') {
                    window.alertService.infoAlert(title, message);
                } else {
                    alert(message || title);
                }
            }

            function notifyError(title, message) {
                if (window.alertService && typeof window.alertService.errorAlert === 'function') {
                    window.alertService.errorAlert(title, message);
                } else {
                    alert(message || title);
                }
            }

            async function fallbackDownloadFiles(files, message) {
                for (let i = 0; i < (files || []).length; i++) {
                    const file = files[i];
                    downloadBlob(file, file.name || 'catasky-share-file');
                    if (i < files.length - 1) {
                        await new Promise(resolve => setTimeout(resolve, 600));
                    }
                }
                notifyInfo('Files downloaded', message || 'Direct sharing was not available, so the files were downloaded instead.');
            }

            function canShareFiles(files) {
                return !!(navigator.share && navigator.canShare && navigator.canShare({ files: files }));
            }

            function showToast(message, title = 'Catalog export') {
                if (window.alertService && typeof window.alertService.infoAlert === 'function') {
                    window.alertService.infoAlert(title, message);
                } else {
                    alert(message);
                }
            }

            function getPreparedPdf(type) {
                type = normalizeExportType(type);
                const prepared = window.preparedShareDocs && window.preparedShareDocs[type];
                if (!prepared || prepared.cacheKey !== getShareCacheKey(type)) return null;
                if (!prepared.file && prepared.blob) {
                    prepared.file = new File([prepared.blob], prepared.filename || 'catasky_catalogue.pdf', { type: 'application/pdf' });
                }
                return prepared && prepared.file ? prepared : null;
            }

            function getPreparedImages() {
                const prepared = window.preparedShareDocs && window.preparedShareDocs.images;
                if (!prepared || prepared.cacheKey !== getShareCacheKey('images')) return null;
                return prepared.files && prepared.files.length ? prepared : null;
            }

            function downloadPreparedPdf(prepared) {
                if (!prepared) return;
                if (prepared.pdf && typeof prepared.pdf.save === 'function') {
                    prepared.pdf.save(prepared.filename || 'catasky_catalogue.pdf');
                    return;
                }
                if (prepared.blob) {
                    downloadBlob(prepared.blob, prepared.filename || 'catasky_catalogue.pdf');
                } else if (prepared.file) {
                    downloadBlob(prepared.file, prepared.file.name || 'catasky_catalogue.pdf');
                }
            }

            async function nativeShareFiles(files, title, text) {
                if (!navigator.share || !canShareFiles(files)) {
                    return { shared: false, unsupported: true };
                }
                const payload = { files: files, title: title || document.title };
                if (text) payload.text = text;
                await navigator.share(payload);
                return { shared: true };
            }

            function preparePDFShareDoc(type, options = {}) {
                type = normalizeExportType(type);
                const cacheKey = getShareCacheKey(type);
                const existing = window.preparedShareDocs[type];
                if (existing && existing.cacheKey === cacheKey && existing.blob) {
                    return Promise.resolve(existing);
                }

                if (options.btn) {
                    $(options.btn).attr('disabled', true).css({ opacity: '0.5', 'pointer-events': 'none' });
                }

                return generatePDFBlob(type).then(function(pdfData) {
                    const prepared = {
                        ...pdfData,
                        cacheKey: cacheKey,
                        file: new File([pdfData.blob], pdfData.filename, { type: 'application/pdf' })
                    };
                    window.preparedShareDocs[type] = prepared;
                    return prepared;
                });
            }

             function prepareImageShareDocs(options = {}) {
                window._imageProgressMax = 0; // RESET progress tracking counter to ensure accuracy
                const type = 'images';
                const cacheKey = getShareCacheKey(type);
                const existing = window.preparedShareDocs[type];
                if (existing && existing.cacheKey === cacheKey && existing.files && existing.files.length) {
                    return Promise.resolve(existing);
                }

                const shareBtn = $('#pdf-share-btn-images');
                const directBtn = $('#pdf-direct-btn-images');
                const downloadBtn = $('#pdf-download-btn-images');

                if (options.showProgressUI && selectedProducts && selectedProducts.length > 0) {
                    showImageProgressUI(selectedProducts.length, options.mode);
                }

                // Disable all action buttons while compiling
                shareBtn.attr('disabled', true).css({ opacity: '0.6', 'pointer-events': 'none' });
                directBtn.attr('disabled', true).css({ opacity: '0.6', 'pointer-events': 'none' });
                downloadBtn.attr('disabled', true).css({ opacity: '0.6', 'pointer-events': 'none' });

                const startTime = Date.now();

                const progressFn = (completed, total) => {
                    const elapsed = (Date.now() - startTime) / 1000;
                    
                    if (window._imageProgressMax === undefined) window._imageProgressMax = 0;
                    // Monotonic guard: only update UI if the completed count goes forward or finishes
                    if (completed > window._imageProgressMax || completed === total) {
                        window._imageProgressMax = Math.max(window._imageProgressMax, completed);
                        const displayCompleted = window._imageProgressMax;

                        if (options.showProgressUI) {
                            updateImageProgressUI(displayCompleted, total, elapsed);
                        }

                        const msg = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Preparing ${total} Images (${displayCompleted}/${total})...`;
                        if (shareBtn.is(':disabled') && shareBtn.html().indexOf('spinner-border') > -1) {
                            shareBtn.html(msg);
                        }
                        if (directBtn.is(':disabled') && directBtn.html().indexOf('spinner-border') > -1) {
                            directBtn.html(msg);
                        }
                        $('#pdf-preview-status-images').removeClass('bg-success bg-danger text-white').addClass('bg-warning text-dark').text(`Rendering (${displayCompleted}/${total})`);
                    }
                };

                return buildShareImageFiles(progressFn).then(function(files) {
                    const prepared = { cacheKey: cacheKey, files: files };
                    window.preparedShareDocs[type] = prepared;

                    // Re-enable and restore action buttons
                    shareBtn.removeAttr('disabled').css({ opacity: '1', 'pointer-events': 'auto' });
                    directBtn.removeAttr('disabled').css({ opacity: '1', 'pointer-events': 'auto' });
                    downloadBtn.removeAttr('disabled').css({ opacity: '1', 'pointer-events': 'auto' });

                    if (options.showProgressUI) {
                        hideImageProgressUI();
                    }
                    return prepared;
                }).catch(function(err) {
                    // Re-enable on failure
                    shareBtn.removeAttr('disabled').css({ opacity: '1', 'pointer-events': 'auto' });
                    directBtn.removeAttr('disabled').css({ opacity: '1', 'pointer-events': 'auto' });
                    downloadBtn.removeAttr('disabled').css({ opacity: '1', 'pointer-events': 'auto' });

                    if (options.showProgressUI) {
                        hideImageProgressUI();
                    }
                    throw err;
                });
            }

            function sharePreparedPdfDocument(type, pdfData, options = {}) {
                const btn = options.btn ? $(options.btn) : null;
                const originalHtml = options.originalHtml || (btn ? btn.html() : '');
                const shareTitle = options.title || pdfData.filename;
                const shareText = options.text || '';
                const successEvent = options.successEvent || 'system_share_pdf_success';

                const restoreButton = function() {
                    if (btn) {
                        btn.removeAttr('disabled').html(originalHtml);
                    }
                };

                const downloadFallback = function(message) {
                    restoreButton();
                    if (window.alertService && typeof window.alertService.infoAlert === 'function') {
                        window.alertService.infoAlert('PDF downloaded', message || 'Your browser could not complete the share action, so the PDF was downloaded instead.');
                    } else {
                        alert(message || 'Your browser could not complete the share action, so the PDF was downloaded instead.');
                    }
                    if (pdfData.pdf && typeof pdfData.pdf.save === 'function') {
                        pdfData.pdf.save(pdfData.filename);
                    } else if (pdfData.blob) {
                        const objectUrl = URL.createObjectURL(pdfData.blob);
                        const link = document.createElement('a');
                        link.href = objectUrl;
                        link.download = pdfData.filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        setTimeout(function() { URL.revokeObjectURL(objectUrl); }, 1500);
                    }
                };

                try {
                    const file = new File([pdfData.blob], pdfData.filename, { type: 'application/pdf' });
                    const canShareFiles = navigator.canShare && navigator.canShare({ files: [file] });
                    if (navigator.share && canShareFiles) {
                        const sharePayload = { files: [file], title: shareTitle };
                        if (shareText) {
                            sharePayload.text = shareText;
                        }
                        return navigator.share(sharePayload).then(function() {
                            restoreButton();
                            trackAnalyticsEventSafe(successEvent, selectedProducts.length);
                        }).catch(function(err) {
                            if (err && err.name === 'NotAllowedError') {
                                downloadFallback('Sharing was blocked by your browser. The PDF has been downloaded instead.');
                            } else {
                                console.log('Native sharing cancelled or failed:', err);
                                restoreButton();
                            }
                        });
                    }

                    downloadFallback('Your browser or device does not support direct file sharing sheets. We have downloaded the PDF file for you instead.');
                } catch (err) {
                    downloadFallback('Your browser could not share this PDF. It has been downloaded instead.');
                }
            }

            window.downloadAllCards = function() {
                setCurrentExportMode('image');
                if (selectedProducts.length === 0) {
                    window.alertService.warningAlert("Please select at least one product first.");
                    return;
                }

                const prepared = getPreparedImages();
                if (prepared) {
                    fallbackDownloadFiles(prepared.files, `Successfully generated and downloaded ${prepared.files.length} product card images.`);
                    trackAnalyticsEventSafe('image_cards_downloaded', prepared.files.length);
                    return;
                }

                prepareImageShareDocs({ showProgressUI: true, mode: 'download' }).then(prepared => {
                    fallbackDownloadFiles(prepared.files, `Successfully generated and downloaded ${prepared.files.length} product card images.`);
                    trackAnalyticsEventSafe('image_cards_downloaded', prepared.files.length);
                }).catch(err => {
                    console.error("Download failed:", err);
                    window.alertService.errorAlert("Failed to download cards", err.message);
                });
            };

            // Opens WhatsApp directly with a prefilled message (catalog link + optional caption)
            function openWhatsAppWithLink(settings, extraMsg) {
                const catalogUrl = window.isSubscriberStore 
                    ? `${window.location.origin}/subscriber_store/${companySlug}?products=${selectedProducts.join(',')}`
                    : `${window.location.origin}/catalog?products=${selectedProducts.join(',')}`;
                const title = settings && settings.catalogTitle ? settings.catalogTitle : 'Premium Selection';
                
                let productListText = '';
                if (settings && settings.includeLink) {
                    selectedProducts.forEach((id, index) => {
                        const cached = window.cachedProductDetails[id.toString()];
                        if (cached && cached.success && cached.product) {
                            const p = cached.product;
                            const productUrl = window.isSubscriberStore
                                ? `${window.location.origin}/product/${p.slug}?is_subscriber=1&company_slug=${companySlug}`
                                : `${window.location.origin}/product/${p.slug}`;
                            productListText += `${index + 1}. *${p.name}*\n👉 ${productUrl}\n\n`;
                        }
                    });
                }
                
                let msg = `*${title}*\n\n${extraMsg ? extraMsg + '\n\n' : ''}`;
                if (productListText) {
                    msg += `Selected Products (Tap to view):\n\n${productListText}`;
                }
                msg += `View Full Curated Catalog:\n👉 ${catalogUrl}`;
                openWhatsAppChat(msg);
            }

            async function whatsappImageShareFallback(files, settings, reason) {
                console.warn('Native file share unavailable:', reason);
                // On desktop/unsupported browsers, instead of downloading or showing alerts,
                // we open WhatsApp Web with a beautiful prefilled catalog link containing all selected products!
                if (typeof openWhatsAppWithLink === 'function') {
                    openWhatsAppWithLink(settings, 'Check out these premium products I selected from the catalog:');
                } else if (typeof showToast === 'function') {
                    showToast('Sharing is only supported on mobile devices.', 'Desktop Browser', 'warning');
                }
            }

            function chunkArray(arr, size = 10) {
                const chunks = [];
                for (let i = 0; i < arr.length; i += size) {
                    chunks.push(arr.slice(i, i + size));
                }
                return chunks;
            }

            function updateShareProgress(data) {
                $('#image-share-progress-container').removeClass('d-none').addClass('d-block');
                
                // Title matches required progress UI: Sharing Batch X / Y
                $('#isp-title').text(`Sharing Batch ${data.currentBatch} / ${data.totalBatches}`);
                
                // Count and status format exactly matched to specifications
                $('#isp-count').text(`${data.sharedImages} / ${data.totalImages} Images Shared`);
                
                if (data.sharedImages === data.totalImages) {
                    $('#isp-status').text('100% Completed');
                } else {
                    $('#isp-status').text(`Sharing batch ${data.currentBatch}... (Send current batch to auto-start next)`);
                }
                
                const percent = Math.min(Math.round((data.sharedImages / data.totalImages) * 100), 100);
                $('#isp-bar').css('width', `${percent}%`);
                $('#isp-percent').text(`${percent}% Completed`);
            }

            async function sequentialNativeShare(files, settings) {
                if (!navigator.share) return { shared: false, unsupported: true };

                // Check if file sharing is supported at all
                if (files.length > 0 && !canShareFiles([files[0]])) {
                    return { shared: false, unsupported: true };
                }

                const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
                const batches = chunkArray(files, 10);
                let sharedCount = 0;

                showImageProgressUI(files.length, 'share');

                // Initialize: Preparing Images... 15 / 15 Ready
                $('#isp-title').text('Preparing Images...');
                $('#isp-count').text(`${files.length} / ${files.length} Ready`);
                $('#isp-bar').css('width', '100%');
                $('#isp-percent').text('100% Completed');
                $('#isp-status').text('All product card images compiled!');
                
                // Show ready state briefly
                await delay(600);

                // Hide any previous action button
                $('#isp-action-box').addClass('d-none');

                for (let i = 0; i < batches.length; i++) {
                    const sharedSoFar = Math.min((i + 1) * 10, files.length);
                    
                    updateShareProgress({
                        currentBatch: i + 1,
                        totalBatches: batches.length,
                        sharedImages: sharedSoFar,
                        totalImages: files.length
                    });

                    let sharedSuccess = false;
                    
                    // Construct caption text with active product links if includeLink is checked
                    const catalogUrl = window.isSubscriberStore 
                        ? `${window.location.origin}/subscriber_store/${companySlug}?products=${selectedProducts.join(',')}`
                        : `${window.location.origin}/catalog?products=${selectedProducts.join(',')}`;
                    const title = settings && settings.catalogTitle ? settings.catalogTitle : 'Premium Selection';
                    let productListText = '';
                    
                    if (settings.includeLink) {
                        batches[i].forEach(file => {
                            const filename = file.name;
                            const matches = filename.match(/^(.*?)_(primary|gallery_\d+)\.jpg$/);
                            if (matches && matches[1]) {
                                const slug = matches[1];
                                const cached = Object.values(window.cachedProductDetails).find(c => c && c.product && c.product.slug === slug);
                                if (cached && cached.product) {
                                    const p = cached.product;
                                    const productUrl = window.isSubscriberStore
                                        ? `${window.location.origin}/product/${p.slug}?is_subscriber=1&company_slug=${companySlug}`
                                        : `${window.location.origin}/product/${p.slug}`;
                                    // Add to caption text
                                    if (!productListText.includes(p.name)) {
                                        productListText += `*${p.name}*:\n👉 ${productUrl}\n\n`;
                                    }
                                }
                            }
                        });
                    }
                    
                    let captionText = `*${title}*\n\n`;
                    if (productListText) {
                        captionText += `Tap to View Products:\n${productListText}`;
                    }
                    captionText += `View Full Curated Catalog:\n👉 ${catalogUrl}`;
                    
                    // We attempt direct automatic native share first!
                    try {
                        await navigator.share({
                            files: batches[i],
                            title: title,
                            text: '' // Requirement 4 & 5: Only share image without caption links/text below the image
                        });
                        sharedCount += batches[i].length;
                        sharedSuccess = true;
                    } catch (shareErr) {
                        console.warn(`Automatic share for Batch ${i+1} failed/blocked:`, shareErr);
                        
                        // If it fails (e.g. because of browser user gesture rule, or if the user dismissed it by mistake),
                        // we show the premium "Tap to Share Next Batch" action button to let them trigger it manually!
                        $('#isp-action-box').removeClass('d-none');
                        $('#isp-action-btn').html(`<i class="bi bi-whatsapp me-2"></i> Tap to Share Batch ${i+1} / ${batches.length} (${batches[i].length} Images)`).off('click').on('click', async function() {
                            $(this).attr('disabled', true).addClass('opacity-50');
                            try {
                                await navigator.share({
                                    files: batches[i],
                                    title: title,
                                    text: '' // Requirement 4 & 5: Only share image without caption links/text below the image
                                });
                                sharedCount += batches[i].length;
                                sharedSuccess = true;
                                $('#isp-action-box').addClass('d-none');
                                $(this).removeAttr('disabled').removeClass('opacity-50');
                                
                                // Resolve the gesture wait
                                if (window._resolveGestureWait) window._resolveGestureWait();
                            } catch (manualErr) {
                                console.error("Manual share trigger failed:", manualErr);
                                $(this).removeAttr('disabled').removeClass('opacity-50');
                                // Keep the button visible so they can try again
                            }
                        });

                        // We pause the loop and wait until the user clicks and successfully shares this batch!
                        await new Promise(resolve => {
                            window._resolveGestureWait = resolve;
                        });
                    }

                    // delay prevents WhatsApp/browser crash
                    await delay(1200);
                }

                // Display final 100% completed progress
                updateShareProgress({
                    currentBatch: batches.length,
                    totalBatches: batches.length,
                    sharedImages: files.length,
                    totalImages: files.length
                });

                setTimeout(() => {
                    hideImageProgressUI();
                }, 2500);

                return {
                    shared: true,
                    unsupported: false,
                    sharedCount: files.length,
                    totalCount: files.length
                };
            }

            window.shareSeparateImages = async function() {
                // Global lock: prevent multiple simultaneous share operations
                if (window._isSharingImages) return;
                window._isSharingImages = true;
                try {
                    setCurrentExportMode('image');
                    const settings = getShareSettings();
                    if (!selectedProducts || selectedProducts.length === 0) {
                        window.alertService.warningAlert('Selection empty', 'Your selection cart is empty.');
                        return;
                    }

                    let prepared = getPreparedImages();
                    if (!prepared) {
                        try {
                            prepared = await prepareImageShareDocs({ showProgressUI: true, mode: 'share' });
                        } catch (err) {
                            console.error('Failed to prepare images:', err);
                            window.alertService.errorAlert('Image sharing failed', err.message || err);
                            return;
                        }
                    }



                    const result = await sequentialNativeShare(prepared.files, settings);

                    if (result.cancelled) return; // User dismissed — do nothing

                    if (result.unsupported) {
                        await whatsappImageShareFallback(prepared.files, settings, 'not supported');
                        return;
                    }

                    if (result.shared) {
                        trackAnalyticsEventSafe('whatsapp_share_images_native_success', result.sharedCount);
                        showToast(`Successfully shared ${result.sharedCount} of ${result.totalCount} product flyers!`, 'Images Shared', 'success');
                    }
                } catch (err) {
                    console.error('shareSeparateImages error:', err);
                    window.alertService && window.alertService.errorAlert('Sharing failed', err.message || String(err));
                } finally {
                    window._isSharingImages = false;
                }
            };

            // Helper for drawing object-fit: cover on 2D Canvas
            function drawCoverImage(ctx, img, x, y, w, h) {
                if (!img) {
                    ctx.fillStyle = '#f1f5f9';
                    ctx.fillRect(x, y, w, h);
                    ctx.fillStyle = '#94a3b8';
                    ctx.font = 'bold 24px "Outfit", "Poppins", sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('No Image', x + w / 2, y + h / 2);
                    return;
                }
                const imgW = img.naturalWidth || img.width;
                const imgH = img.naturalHeight || img.height;
                const imgRatio = imgW / imgH;
                const containerRatio = w / h;
                
                let sx, sy, sw, sh;
                if (imgRatio > containerRatio) {
                    sh = imgH;
                    sw = imgH * containerRatio;
                    sx = (imgW - sw) / 2;
                    sy = 0;
                } else {
                    sw = imgW;
                    sh = imgW / containerRatio;
                    sx = 0;
                    sy = (imgH - sh) / 2;
                }
                
                ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
            }

            // Helper for drawing object-fit: contain on 2D Canvas
            function drawContainImage(ctx, img, x, y, w, h) {
                if (!img) return;
                const imgW = img.naturalWidth || img.width;
                const imgH = img.naturalHeight || img.height;
                const imgRatio = imgW / imgH;
                const containerRatio = w / h;
                
                let dx, dy, dw, dh;
                if (imgRatio > containerRatio) {
                    dw = w;
                    dh = w / imgRatio;
                    dx = x;
                    dy = y + (h - dh) / 2;
                } else {
                    dh = h;
                    dw = h * imgRatio;
                    dx = x + (w - dw) / 2;
                    dy = y;
                }
                
                ctx.drawImage(img, dx, dy, dw, dh);
            }

            // Helper for drawing rounded rectangles on 2D Canvas
            function drawRoundRect(ctx, x, y, w, h, radius, fillStyle, strokeStyle) {
                ctx.beginPath();
                if (typeof ctx.roundRect === 'function') {
                    ctx.roundRect(x, y, w, h, radius);
                } else {
                    ctx.moveTo(x + radius, y);
                    ctx.lineTo(x + w - radius, y);
                    ctx.quadraticCurveTo(x + w, y, x + w, y + radius);
                    ctx.lineTo(x + w, y + h - radius);
                    ctx.quadraticCurveTo(x + w, y + h, x + w - radius, y + h);
                    ctx.lineTo(x + radius, y + h);
                    ctx.quadraticCurveTo(x, y + h, x, y + h - radius);
                    ctx.lineTo(x, y + radius);
                    ctx.quadraticCurveTo(x, y, x + radius, y);
                }
                ctx.closePath();
                if (fillStyle) {
                    ctx.fillStyle = fillStyle;
                    ctx.fill();
                }
                if (strokeStyle) {
                    ctx.strokeStyle = strokeStyle;
                    ctx.stroke();
                }
            }

            async function captureCardAsBlob(pOrItem, imgUrlFallback) {
                let item = pOrItem;
                if (pOrItem && !pOrItem.product && pOrItem.id) {
                    // Supporting legacy signature: captureCardAsBlob(product, imgUrl)
                    item = { product: pOrItem, imageUrl: imgUrlFallback };
                }
                
                const p = item.product || {};
                const imgUrl = getRelativeImageUrl(item.imageUrl || '');
                const settings = getShareSettings();
                const showTitle = settings.showTitle;
                const showPrice = settings.showPrice;
                const showWatermark = settings.showWatermark;
                const showNote = settings.showNotes;
                const includeLink = settings.includeLink;
                const noteText = settings.noteText || 'An Award For Every Achievement & Effort';
                const logoPos = settings.logoPos || 'bottom-right';
                const productName = p.name || 'Product';
                const partCode = p.part_code || '';
                const displayPrice = formatProductPrice(p);
                
                // Requirement 1 & 2: HD+ 4:5 aspect ratio (2160x2700 px resolution for guaranteed crispness!)
                const CANVAS_WIDTH  = 2160;
                const CANVAS_HEIGHT = 2700;
                const DESIGN_WIDTH  = 800;     // internal design coordinate width
                const DESIGN_HEIGHT = 1000;    // internal design coordinate height
                const SCALE         = 2.7;     // CANVAS_WIDTH / DESIGN_WIDTH = 2.7

                const canvas = document.createElement('canvas');
                canvas.width  = CANVAS_WIDTH;
                canvas.height = CANVAS_HEIGHT;
                const ctx = canvas.getContext('2d', { alpha: false });

                // High-quality image smoothing for crisp product photos
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';

                // Scale design context (800x1000 coords mapped to 2160x2700 pixels)
                ctx.scale(SCALE, SCALE);

                const footerHeight   = 170;
                const noteHeight     = showNote ? 60 : 0;
                const imageAreaBottom = footerHeight + noteHeight;

                // ── WHITE BACKGROUND ───────────────────────────────────────────────────
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, DESIGN_WIDTH, DESIGN_HEIGHT);

                // ── PRODUCT IMAGE — object-fit: contain (fits full area, maintains aspect ratio, no cropping) ─
                const img  = await loadImage(imgUrl);
                const imgH = DESIGN_HEIGHT - imageAreaBottom;
                // Light grey background for image area (matching HTML preview background #f8fafc)
                ctx.fillStyle = '#f8fafc';
                ctx.fillRect(0, 0, DESIGN_WIDTH, imgH);
                drawContainImage(ctx, img, 0, 0, DESIGN_WIDTH, imgH);

                // ── TRANSPARENT COMPANY NAME WATERMARK (diagonal, center, once) ───────
                if (showWatermark) {
                    const watermarkText = (window.companySiteTitle || 'CataSky').toUpperCase();
                    ctx.save();
                    ctx.globalAlpha = 0.20; // Increased opacity for readability on all image colors
                    ctx.translate(DESIGN_WIDTH / 2, imgH / 2);
                    ctx.rotate(-30 * Math.PI / 180);
                    ctx.textAlign    = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle    = '#000000';
                    
                    let fontSize = 100;
                    ctx.font = `900 ${fontSize}px "Outfit", "Poppins", sans-serif`;
                    let textWidth = ctx.measureText(watermarkText).width;
                    const maxAllowedWidth = DESIGN_WIDTH * 0.85; // 85% of 800 = 680px
                    if (textWidth > maxAllowedWidth) {
                        fontSize = Math.floor(fontSize * (maxAllowedWidth / textWidth));
                        if (fontSize < 30) fontSize = 30;
                        ctx.font = `900 ${fontSize}px "Outfit", "Poppins", sans-serif`;
                    }
                    
                    ctx.fillText(watermarkText, 0, 0);
                    ctx.restore();
                }

                // ── YELLOW NOTE BAR (above footer) ────────────────────────────────────
                if (showNote) {
                    const noteY = DESIGN_HEIGHT - footerHeight - noteHeight;
                    ctx.fillStyle = '#FFD000';
                    ctx.fillRect(0, noteY, DESIGN_WIDTH, noteHeight);
                    ctx.fillStyle    = '#000000';
                    ctx.font         = '700 16px "Outfit", "Poppins", sans-serif';
                    ctx.textBaseline = 'middle';
                    ctx.textAlign    = 'left';
                    ctx.fillText(`CODE: ${partCode}`, 44, noteY + noteHeight / 2);
                    ctx.textAlign    = 'right';
                    ctx.fillText(noteText, DESIGN_WIDTH - 44, noteY + noteHeight / 2);
                }

                // ── BLACK FOOTER (3 rows) ─────────────────────────────────────────────
                const footerY = DESIGN_HEIGHT - footerHeight;
                ctx.fillStyle = '#000000';
                ctx.fillRect(0, footerY, DESIGN_WIDTH, footerHeight);

                // Extract MRP and Offer Price — guard against null/undefined/empty/zero
                const mrpRawC   = p.mrp ?? p.price;
                const offerRawC = p.offer_price ?? p.sale_price;
                const _parsePrice = v => {
                    if (v === null || v === undefined || String(v).trim() === '') return NaN;
                    const n = Number(v);
                    return (isNaN(n) || n <= 0) ? NaN : n;
                };
                const mrpNumC   = _parsePrice(mrpRawC);
                const offerNumC = _parsePrice(offerRawC);
                const mrpValC   = !isNaN(mrpNumC)   ? '\u20B9 ' + mrpNumC.toLocaleString('en-IN')   : '';
                const offerValC = !isNaN(offerNumC) ? '\u20B9 ' + offerNumC.toLocaleString('en-IN') : '';
                const hasBothPrices = mrpValC && offerValC && mrpValC !== offerValC;

                ctx.textBaseline = 'middle';

                // ─ ROW 1 (y ≈ footerY + 38): Product title (left) | MRP strikethrough (right)
                const row1Y = footerY + 38;
                if (showTitle) {
                    ctx.fillStyle = '#ffffff';
                    ctx.font      = '900 26px "Outfit", "Poppins", sans-serif';
                    ctx.textAlign = 'left';
                    const maxTitleW = showPrice ? 420 : 700;
                    let displayTitle = productName;
                    while (displayTitle.length > 0 && ctx.measureText(displayTitle + '…').width > maxTitleW) {
                        displayTitle = displayTitle.slice(0, -1);
                    }
                    if (displayTitle !== productName) displayTitle += '…';
                    ctx.fillText(displayTitle, 44, row1Y);
                }
                if (showPrice && hasBothPrices) {
                    // MRP with strikethrough (greyed out)
                    ctx.font      = '700 19px "Outfit", "Poppins", sans-serif';
                    ctx.fillStyle = '#999999';
                    ctx.textAlign = 'right';
                    const mrpX = DESIGN_WIDTH - 44;
                    ctx.fillText(mrpValC, mrpX, row1Y);
                    const mrpW = ctx.measureText(mrpValC).width;
                    ctx.strokeStyle = '#999999';
                    ctx.lineWidth   = 1.5;
                    ctx.beginPath();
                    ctx.moveTo(mrpX - mrpW, row1Y);
                    ctx.lineTo(mrpX, row1Y);
                    ctx.stroke();
                }

                // ─ ROW 2 (y ≈ footerY + 82): Part code (left, yellow) | Offer / only price (right, white)
                const row2Y = footerY + 82;
                if (showNote && partCode) {
                    ctx.fillStyle = '#FFD000';
                    ctx.font      = '700 14px "Outfit", "Poppins", sans-serif';
                    ctx.textAlign = 'left';
                    ctx.fillText(`CODE: ${partCode}`, 44, row2Y);
                }
                if (showPrice) {
                    ctx.fillStyle = '#ffffff';
                    ctx.font      = '900 32px "Outfit", "Poppins", sans-serif';
                    ctx.textAlign = 'right';
                    const priceToShow = hasBothPrices ? offerValC : (offerValC || mrpValC || displayPrice);
                    ctx.fillText(priceToShow, DESIGN_WIDTH - 44, row2Y);
                }

                // ─ ROW 3 (y ≈ footerY + 132): Tap to view pill (left)
                if (includeLink) {
                    const pillW = 120, pillH = 30;
                    const pillX = 44;
                    const pillY = footerY + 127;
                    drawRoundRect(ctx, pillX, pillY, pillW, pillH, 15, '#ffffff', null);
                    ctx.fillStyle    = '#000000';
                    ctx.font         = '900 12px "Outfit", "Poppins", sans-serif';
                    ctx.textAlign    = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('TAP TO VIEW', pillX + pillW / 2, pillY + pillH / 2);
                }

                // ── EXPORT ───────────────────────────────────────────────────────────
                return new Promise(resolve => {
                    canvas.toBlob(blob => resolve(blob), 'image/jpeg', 0.90);
                });
            }

            function uploadTempImageBlob(blob, filename) {
                return new Promise((resolve, reject) => {
                    const formData = new FormData();
                    formData.append('image', blob, filename);
                    formData.append('filename', filename);
                    formData.append('_token', '{{ csrf_token() }}');

                    $.ajax({
                        url: "{{ route('image.upload-temp') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success && response.url) {
                                resolve(response.public_url || response.url);
                            } else {
                                resolve(null);
                            }
                        },
                        error: function(xhr) {
                            console.error("Temp image upload failed:", xhr);
                            resolve(null);
                        }
                    });
                });
            }

            function renderCardForExportHtml(p, imgUrl) {
                imgUrl = getRelativeImageUrl(imgUrl);
                const settings = getShareSettings();
                const showTitle = settings.showTitle;
                const showPrice = settings.showPrice;
                const showWatermark = settings.showWatermark;
                const showNote = settings.showNotes;
                const noteText = settings.noteText || '';
                const logoPos = settings.logoPos || 'bottom-right';

                let logoPosStyle = 'bottom: 150px; right: 30px;'; // Default BR
                if (logoPos === 'top-left') {
                    logoPosStyle = 'top: 30px; left: 30px;';
                } else if (logoPos === 'top-right') {
                    logoPosStyle = 'top: 30px; right: 30px;';
                } else if (logoPos === 'bottom-left') {
                    logoPosStyle = 'bottom: 150px; left: 30px;';
                }

                const companyLogo = getRelativeImageUrl(window.companyLogoBase64 || '');
                const displayPrice = formatProductPrice(p);

                return `
                <div style="width: 800px; height: 800px; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; position: relative; box-sizing: border-box; font-family: 'Outfit', 'Poppins', sans-serif;">
                    ${showWatermark && companyLogo ? `
                    <div style="position: absolute; ${logoPosStyle} width: 100px; height: 100px; z-index: 5; pointer-events: none; opacity: 0.7; display: flex; align-items: center; justify-content: center;">
                        <img src="${companyLogo}" decoding="async" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>` : ''}

                    <!-- Product Image Centered -->
                    <div style="flex-grow: 1; display: flex; align-items: center; justify-content: center; background: #ffffff; padding: 15px; box-sizing: border-box; width: 100%; height: 580px;">
                        <img src="${imgUrl}" decoding="async" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>

                    <!-- Yellow Note Bar -->
                    ${showNote ? `
                    <div style="background: #FFD000; color: #000000; display: flex; align-items: center; font-size: 20px; font-weight: bold; padding: 14px 28px; box-sizing: border-box; justify-content: space-between; width: 100%;">
                        <span>CODE: ${p.part_code || ''}</span>
                        <span style="text-transform: uppercase;">${noteText}</span>
                    </div>` : ''}

                    <!-- Solid Black Footer -->
                    <div style="background: #000000; color: #ffffff; display: flex; justify-content: space-between; align-items: center; padding: 24px 30px; box-sizing: border-box; width: 100%; height: 110px; min-height: 110px;">
                        <div style="text-align: left; max-width: 70%;">
                            <div style="font-size: 13px; color: #FFD000; font-weight: 800; text-transform: uppercase; margin-bottom: 2px;">CODE: ${p.part_code || ''}</div>
                            ${showTitle ? `<div style="font-size: 24px; font-weight: bold; color: #ffffff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: 'Outfit', sans-serif;">${p.name}</div>` : ''}
                        </div>
                        ${showPrice ? `<div style="font-size: 28px; font-weight: 900; color: #ffffff; font-family: 'Outfit', sans-serif;">${displayPrice}</div>` : ''}
                    </div>
                </div>
                `;
            }

            function escapeHtml(value) {
                return String(value || '').replace(/[&<>"']/g, function(char) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
                });
            }

            function formatDisplayPrice(value) {
                const price = String(value || '').trim();
                if (!price) return '';
                return price.includes('\u20B9') || price.includes('&#8377;') || price.toLowerCase().includes('request') ? price : '₹ ' + price;
            }

            function getImagePdfItems(validDataList) {
                const showGallery = getShareSettings().showGallery;
                const items = [];

                validDataList.forEach(data => {
                    if (!data || !data.success) return;
                    items.push({
                        product: data.product,
                        imageUrl: getRelativeImageUrl(data.thumbnail_url || ''),
                        label: 'primary'
                    });

                    if (showGallery && Array.isArray(data.gallery_urls)) {
                        data.gallery_urls.forEach((url, index) => {
                            if (url) {
                                items.push({
                                    product: data.product,
                                    imageUrl: getRelativeImageUrl(url),
                                    label: 'gallery_' + (index + 1)
                                });
                            }
                        });
                    }
                });

                return items;
            }

            function renderImagePdfBoxHtml(item, options = {}) {
                const p = item.product || {};
                const imgUrl = getRelativeImageUrl(item.imageUrl || '');
                const settings = getShareSettings();
                const showTitle = settings.showTitle;
                const showPrice = settings.showPrice;
                const showWatermark = settings.showWatermark;
                const showNote = settings.showNotes;
                const includeLink = settings.includeLink;
                const noteText = escapeHtml(settings.noteText || 'An Award For Every Achievement & Effort');
                const productName = escapeHtml(p.name || 'Product');
                const partCode = escapeHtml(p.part_code || '');

                // MRP and Offer Price — guard against null/undefined/empty/zero
                const mrpRawH   = p.mrp ?? p.price;
                const offerRawH = p.offer_price ?? p.sale_price;
                const _parsePriceH = v => {
                    if (v === null || v === undefined || String(v).trim() === '') return NaN;
                    const n = Number(v);
                    return (isNaN(n) || n <= 0) ? NaN : n;
                };
                const mrpNumH   = _parsePriceH(mrpRawH);
                const offerNumH = _parsePriceH(offerRawH);
                const mrpValH   = !isNaN(mrpNumH)   ? '\u20B9\u00A0' + mrpNumH.toLocaleString('en-IN')   : '';
                const offerValH = !isNaN(offerNumH) ? '\u20B9\u00A0' + offerNumH.toLocaleString('en-IN') : '';
                const hasBothH  = mrpValH && offerValH && mrpValH !== offerValH;

                const footerHeight   = 210;
                const noteHeight     = showNote ? 80 : 0;
                const imageAreaBottom = footerHeight + noteHeight;

                // Large diagonal company watermark — centered on image area (rendered once, clearly visible)
                const wmText = escapeHtml((window.companySiteTitle || 'CataSky').toUpperCase());
                const dynamicImageFontSize = wmText.length > 15 ? '55px' : (wmText.length > 8 ? '75px' : '110px');
                const watermarkHtml = showWatermark ? `
                <div style="position:absolute;top:0;left:0;right:0;bottom:0;z-index:3;pointer-events:none;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                    <div style="transform:rotate(-30deg);text-align:center;max-width:90%;">
                        <div style="font-size:${dynamicImageFontSize};font-weight:900;color:rgba(0,0,0,0.18);font-family:'Outfit','Poppins',Arial,sans-serif;letter-spacing:4px;text-transform:uppercase;line-height:1.2;word-wrap:break-word;">${wmText}</div>
                    </div>
                </div>` : '';

                return `
                <div class="render-box-wrapper" style="box-sizing:border-box;width:1080px;height:1350px;overflow:hidden;position:relative;background:#ffffff;font-family:'Outfit','Poppins','Helvetica Neue',Arial,sans-serif;">

                    <!-- Image area: background-size:contain to fill, no white space, no stretching in html2canvas -->
                    <div style="position:absolute;top:0;left:0;right:0;bottom:${imageAreaBottom}px;background-color:#f8fafc;${imgUrl ? `background-image:url('${imgUrl}');background-position:center;background-size:contain;background-repeat:no-repeat;` : ''}overflow:hidden;z-index:1;">
                        ${!imgUrl ? `<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#94A3B8;font-weight:800;font-size:36px;z-index:1;">No Image</div>` : ''}
                        ${watermarkHtml}
                    </div>

                    <!-- Optional yellow note bar -->
                    ${showNote ? `
                    <div style="position:absolute;left:0;right:0;bottom:${footerHeight}px;height:${noteHeight}px;background:#FFD000;color:#000000;display:flex;align-items:center;justify-content:space-between;padding:0 64px;box-sizing:border-box;font-weight:700;font-size:26px;z-index:10;">
                        <div>CODE: ${partCode}</div>
                        <div style="text-transform:uppercase;text-align:right;max-width:600px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${noteText}</div>
                    </div>` : ''}

                    <!-- Black footer: 2-row layout — title+MRP | code+offer -->
                    <div style="position:absolute;left:0;right:0;bottom:0;height:${footerHeight}px;background:#000000;color:#ffffff;display:flex;flex-direction:column;justify-content:center;padding:24px 64px;box-sizing:border-box;z-index:10;gap:16px;">

                        <!-- Row 1: Product title (left) + MRP strikethrough (right) -->
                        <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
                            ${showTitle
                                ? `<div style="font-size:38px;font-weight:900;line-height:1.1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:${showPrice && hasBothH ? '580px' : '950px'};">${productName}</div>`
                                : '<div></div>'
                            }
                            ${showPrice && hasBothH
                                ? `<div style="font-size:28px;color:#aaaaaa;font-weight:700;text-decoration:line-through;white-space:nowrap;flex-shrink:0;margin-left:16px;">${mrpValH}</div>`
                                : ''
                            }
                        </div>

                        <!-- Row 2: Code + Tap-to-view (left) + Offer/single price (right) -->
                        <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
                            <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                                ${showNote ? `<div style="font-size:20px;color:#FFD000;font-weight:700;text-transform:uppercase;">CODE: ${partCode}</div>` : ''}
                                ${includeLink ? `<div style="background:#ffffff;color:#000000;border-radius:999px;padding:10px 28px;font-size:18px;font-weight:900;text-transform:uppercase;white-space:nowrap;">Tap to view</div>` : ''}
                            </div>
                            ${showPrice
                                ? `<div style="font-size:46px;font-weight:900;color:#ffffff;white-space:nowrap;flex-shrink:0;">${hasBothH ? offerValH : (mrpValH || offerValH)}</div>`
                                : ''
                            }
                        </div>
                    </div>
                </div>
                `;
            }

            function renderImagePdfPageHtml(items, options = {}) {
                const companyLogo = getRelativeImageUrl(String(options.companyLogo || window.companyLogoBase64 || '').trim());
                const catalogTitle = escapeHtml(options.catalogTitle || 'Compact Visual Matrix Portfolio');
                const productCount = options.productCount || items.length;
                const titleWords = catalogTitle.split(/\s+/).filter(Boolean);
                const titleHtml = titleWords.length > 3
                    ? titleWords.slice(0, 3).join(' ') + '<br>' + titleWords.slice(3).join(' ')
                    : catalogTitle;
                const pageItems = items.slice(0, 6);
                let slotsHtml = '';

                for (let index = 0; index < 6; index++) {
                    const item = pageItems[index];
                    if (item) {
                        // Each card is 1080x1350 (4:5). Scale to fit display width.
                        // display slot: width=218px → scale=218/1080=0.20185, height=218*(1350/1080)=272px
                        const slotW = 218;
                        const slotH = Math.round(slotW * (1350 / 1080)); // 272px
                        slotsHtml += `
                        <div class="pdf-product-link-target" data-slug="${item.product && item.product.slug ? escapeHtml(item.product.slug) : ''}" style="width:${slotW}px;height:${slotH}px;position:relative;border-radius:8px;overflow:hidden;background:#ffffff;border:1.5px solid #E2E8F0;box-sizing:border-box;cursor:pointer;flex-shrink:0;">
                            <div style="width:1080px;height:1350px;transform:scale(${(slotW/1080).toFixed(5)});transform-origin:top left;will-change:transform;">
                                ${renderImagePdfBoxHtml(item, { companyLogo })}
                            </div>
                        </div>
                        `;
                    } else {
                        const slotW = 218;
                        const slotH = Math.round(slotW * (1350 / 1080));
                        slotsHtml += `
                        <div style="width:${slotW}px;height:${slotH}px;border-radius:8px;background:#ffffff;border:1.5px dashed #E2E8F0;display:flex;align-items:center;justify-content:center;box-sizing:border-box;">
                            <div style="font-size:0.75rem;color:#E2E8F0;font-family:'Outfit',sans-serif;font-weight:800;text-transform:uppercase;">Empty Slot</div>
                        </div>
                        `;
                    }
                }

                return `
                <div class="pdf-page" style="box-sizing:border-box;width:790px;height:1117px;background:#ffffff;padding:30px 48px 44px;position:relative;display:flex;flex-direction:column;justify-content:space-between;overflow:hidden;font-family:'Outfit','Poppins','Helvetica Neue',Arial,sans-serif;page-break-after:always;border:1.5px solid #D2D2D2;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;width:100%;">
                        <div style="display:flex;align-items:flex-start;gap:18px;">
                            ${companyLogo
                                ? `<img src="${companyLogo}" loading="lazy" decoding="async" style="max-width:112px;max-height:42px;object-fit:contain;display:block;">`
                                : `<div style="width:44px;height:44px;border-radius:10px;background:#1D6FEB;color:white;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.25rem;">C</div>`
                            }
                           
                        </div>
                        <div style="font-size:24px;font-weight:900;color:#000000;line-height:1.1;">${productCount} products</div>
                    </div>

                    <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:16px 15px;width:100%;margin-top:82px;box-sizing:border-box;">
                        ${slotsHtml}
                    </div>

                    <div style="text-align:center;font-size:35px;font-weight:900;color:#000000;text-transform:uppercase;letter-spacing:0;font-family:'Outfit',sans-serif;line-height:1.45;margin-top:auto;">
                        ${titleHtml}
                    </div>

                    <div class="pdf-catalog-link-target" style="width:100%;background:#4F46E5;color:#ffffff;text-align:center;font-size:20px;font-weight:900;font-family:'Outfit',sans-serif;padding:19px;border-radius:8px;letter-spacing:0;display:flex;align-items:center;justify-content:center;gap:10px;box-sizing:border-box;text-transform:uppercase;cursor:pointer;">
                        PRESS TO OPEN &rarr;
                    </div>
                </div>
                `;
            }

            window.updateSelectionUI = function() {
                const count = selectedProducts.length;
                const isAuthenticated = $('#selection-bar').data('authenticated') === true || $('#selection-bar').data('authenticated') === 'true';
                
                // Sync badge counters
                $('#cart-count, #mobile-cart-count, #selected-count, .selected-count-span').text(count);

                // Show the export actions for logged-in users, or guest users with items selected.
                // Logged-in users keep the bar visible at 0 selections to open empty states.
                if (isAuthenticated || count > 0) {
                    $('#selection-bar').addClass('active');
                } else {
                    $('#selection-bar').removeClass('active');
                }

                // Sync all selection card UI borders and badges
                $('.premium-card').removeClass('selected');
                $('.select-btn-main').removeClass('btn-premium-primary').addClass('btn-premium-outline').html('<i class="bi bi-bag-plus"></i> Select');

                selectedProducts.forEach(id => {
                    const card = $(`#product-card-${id}`);
                    card.addClass('selected');
                    card.find('.select-btn-main').removeClass('btn-premium-outline').addClass('btn-premium-primary').html('<i class="bi bi-bag-check-fill"></i> Selected');
                });
            }

            // Open selection summary modal
            window.openSelectionModal = function(defaultTab = 'selection') {
                if (selectedProducts.length === 0) {
                    window.alertService.warningAlert('No products selected', 'Please select at least one product first to view or share.');
                    return;
                }
                openSharingModal(defaultTab);
            };

            // Custom search logic
            let searchTimeout = null;
            $('#catalog-search').on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val().trim();
                
                if (query.length < 2) {
                    $('#search-results-pane').html(`
                        <div class="p-4 text-center text-secondary small">
                            Type keyword to search among high-end catalog items.
                        </div>
                    `);
                    return;
                }

                $('#search-results-pane').html(`
                    <div class="p-4 text-center">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="ms-2 small text-secondary">Searching blueprints...</span>
                    </div>
                `);

                searchTimeout = setTimeout(function() {
                    const searchParams = { query: query };
                    @if(isset($isSubscriberStore) && $isSubscriberStore && isset($profile))
                        searchParams.company_slug = '{{ $profile->company_slug }}';
                    @endif
                    $.get('{{ route("search") }}', searchParams, function(response) {
                        const html = $(response).find('#product-grid').html();
                        if (html && html.trim().length > 0) {
                            $('#search-results-pane').html(`
                                <div class="p-3 bg-light border-bottom fw-bold small text-secondary">Search results for "${query}"</div>
                                <div class="p-3"><div class="product-grid" style="grid-template-columns: repeat(2, 1fr);">${html}</div></div>
                            `);
                        } else {
                            $('#search-results-pane').html(`
                                <div class="p-5 text-center text-secondary">
                                    <i class="bi bi-search-heart text-secondary fs-1 mb-2 opacity-50"></i>
                                    <h6 class="fw-bold">No specifications matched</h5>
                                    <p class="small">Try searching another category or product keyword.</p>
                                </div>
                            `);
                        }
                    }).fail(function() {
                        $('#search-results-pane').html(`
                            <div class="p-4 text-center text-danger small">
                                Search failed. Please check connection.
                            </div>
                        `);
                    });
                }, 300);
            });

            // Keep track of rendered status for lazy previews
            window.renderedPreviews = { details: false, images: false };

            // B2B Sharing Modal Open handler
            window.openSharingModal = function(activeTabKey) {
                if (selectedProducts.length === 0) {
                    window.alertService.warningAlert('No products selected', 'Select products before building collaterals.');
                    return;
                }

                // Reset rendered status on open
                window.renderedPreviews = { details: false, images: false };

                // Show spinner in modal list instantly
                $('#modal-selection-list').html(`
                    <div class="d-flex justify-content-center align-items-center py-5">
                        <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                        <span class="ms-2 small text-secondary">Loading selected blueprints...</span>
                    </div>
                `);

                $('#sharingModal').modal('show');
                
                // Pre-fetch all selected products details in a single bulk request first!
                fetchMultipleProductDetails(selectedProducts).then(() => {
                    // Populate the selected items list instantly from cache
                    populateModalSelectionList();

                    let targetTab = 'selection';
                    if (activeTabKey === 'pdf') {
                        setCurrentExportMode('pdf');
                        $('#pdf-tab').click();
                        targetTab = 'pdf';
                    } else if (activeTabKey === 'image') {
                        setCurrentExportMode('image');
                        $('#image-tab').click();
                        targetTab = 'image';
                    } else {
                        $('#selection-tab').click();
                    }

                    // Compile product links preview
                    prepareLinksList();

                    // Trigger live PDF preview compilation only for the active tab (instant, no parallel fetches!)
                    if (targetTab === 'pdf') {
                        generateLivePDFPreview('details');
                    } else if (targetTab === 'image') {
                        generateLiveImagePreview();
                    }

                    // Background prepare images for sharing to achieve instant clicks!
                    setTimeout(() => {
                        prepareImageShareDocs().catch(err => console.log('Background image prep skipped:', err));
                    }, 150);

                }).catch(err => {
                    console.error("openSharingModal pre-fetch error:", err);
                    $('#modal-selection-list').html(`
                        <div class="text-center py-4 text-danger small">
                            <i class="bi bi-exclamation-triangle-fill fs-3 mb-2"></i>
                            <br>Failed to retrieve selection details. Please check connection.
                        </div>
                    `);
                });
            };

            // Force clear full selection without confirmation if needed (fallback for deleted/invalid products)
            window.clearFullSelectionForce = function() {
                selectedProducts = [];
                localStorage.setItem(storageKey, JSON.stringify(selectedProducts));
                updateSelectionUISafe();
                populateModalSelectionList();
                $('#sharingModal').modal('hide');
            };

            // Bind tab click events to lazily compile PDF previews on demand
            $(document).ready(function() {
                $('#pdf-tab').off('click.catalogueExport').on('click.catalogueExport', function() {
                    setCurrentExportMode('pdf');
                    if (!window.renderedPreviews.details && selectedProducts.length > 0) {
                        generateLivePDFPreview('details');
                    }
                });

                $('#image-tab').off('click.catalogueExport').on('click.catalogueExport', function() {
                    setCurrentExportMode('image');
                    if (!window.renderedPreviews.images && selectedProducts.length > 0) {
                        generateLiveImagePreview();
                    }
                });
            });

            function populateModalSelectionList() {
                const listContainer = $('#modal-selection-list');
                const countBadge = $('#modal-selection-count');
                
                // Update the count in the tab header
                countBadge.text(selectedProducts.length);
                
                if (selectedProducts.length === 0) {
                    listContainer.html(`
                        <div class="text-center py-5">
                            <i class="bi bi-bag-x text-secondary display-3 opacity-25 d-block mb-3"></i>
                            <h6 class="fw-bold text-dark">Your selection is empty</h6>
                            <p class="text-secondary small mb-3">Select products from the catalog grid to share or download PDFs.</p>
                            <button class="btn btn-premium btn-premium-primary btn-sm px-4" data-bs-dismiss="modal">Browse Products</button>
                        </div>
                    `);
                    
                    // If selection count is 0, also disable preview actions in other tabs
                    $('#pdf-share-btn-details, #pdf-download-btn-details, #pdf-direct-btn-details, #pdf-api-btn-details, #pdf-share-btn-images, #pdf-download-btn-images, #pdf-direct-btn-images, #pdf-api-btn-images').attr('disabled', true).css({ 'opacity': '0.5', 'pointer-events': 'none' });
                    updateSelectionUISafe();
                    return;
                }
                
                setExportButtonsState('details', false);
                setExportButtonsState('images', false);
                
                listContainer.html(`
                    <div class="d-flex justify-content-center align-items-center py-5">
                        <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                        <span class="ms-2 small text-secondary">Loading selected blueprints...</span>
                    </div>
                `);
                
                fetchMultipleProductDetails(selectedProducts).then(() => {
                    let promises = selectedProducts.map(id => fetchProductDetailsCached(id));

                    Promise.all(promises).then(dataList => {
                        let html = '';
                        let validCount = 0;
                        dataList.forEach(data => {
                            if (data && data.success) {
                                validCount++;
                                const p = data.product;
                                const moq = p.moq ? 'MOQ: ' + p.moq + ' pcs' : 'MOQ: 100 pcs';
                                const price = formatProductPrice(p);
                                const thumbnailUrl = data.thumbnail_url || '';
                                
                                html += `
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-white border rounded-4 mb-2" id="modal-selected-item-${p.id}" style="transition: all 0.25s ease;">
                                        <div class="d-flex align-items-center gap-3" style="min-width: 0; flex-grow: 1; margin-right: 12px;">
                                            <div style="width: 55px; height: 55px; border-radius: 12px; background: #ffffff; border: 1.5px solid #F1F5F9; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                                ` + (thumbnailUrl 
                                                    ? `<img src="${thumbnailUrl}" loading="lazy" decoding="async" style="max-width: 48px; max-height: 48px; object-fit: contain;">`
                                                    : `<div style="font-size: 0.7rem; color: #94A3B8;">No Image</div>`
                                                ) + `
                                            </div>
                                            <div style="min-width: 0; flex-grow: 1;">
                                                <h6 class="fw-bold mb-1 text-dark text-truncate" style="max-width: 100% !important; font-size: 0.85rem;" title="${p.name}">${p.name}</h6>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="badge bg-light text-secondary border small-text" style="font-size: 0.65rem; font-weight: 600;">${moq}</span>
                                                    <span class="text-primary fw-bold" style="font-size: 0.8rem;">${price}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-premium-danger btn-sm rounded-circle d-flex align-items-center justify-content-center" onclick="toggleSelection('${p.id}'); populateModalSelectionList();" style="width: 36px; height: 36px; padding: 0; background: rgba(239, 68, 68, 0.08); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.15); transition: all 0.2s ease; flex-shrink: 0;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        });
                        
                        if (validCount === 0 && selectedProducts.length > 0) {
                            listContainer.html(`
                                <div class="text-center py-5">
                                    <i class="bi bi-exclamation-octagon text-warning display-4 mb-3 d-block"></i>
                                    <h6 class="fw-bold text-dark">Selected items not found</h6>
                                    <p class="text-secondary small mb-3">The items in your selection are no longer available in the catalog database. Please clear your selection to reset.</p>
                                    <button class="btn btn-premium btn-premium-danger btn-sm px-4" onclick="clearFullSelectionForce()">Reset Selection</button>
                                </div>
                            `);
                        } else {
                            listContainer.html(html);
                        }
                        
                        // Lazy-sync PDF preview generation: only rebuild if that tab is active, else mark out of sync!
                        if ($('#pdf-tab').hasClass('active')) {
                            generateLivePDFPreview('details');
                        } else {
                            window.renderedPreviews.details = false;
                        }

                        if ($('#image-tab').hasClass('active')) {
                            generateLiveImagePreview();
                        } else {
                            window.renderedPreviews.images = false;
                        }
                        prepareLinksList();
                    }).catch(err => {
                        console.error("Error loading selection modal list:", err);
                        listContainer.html(`
                            <div class="text-center py-4 text-danger small">
                                <i class="bi bi-exclamation-triangle-fill fs-3 mb-2"></i>
                                <br>Failed to retrieve selection details. Please check connection.
                            </div>
                        `);
                    });
                });
            }
            window.populateModalSelectionList = populateModalSelectionList;

            function clearFullSelection() {
                window.alertService.confirmAction({
                    title: 'Clear selection?',
                    message: 'All selected products will be removed from this catalog draft.',
                    confirmText: 'Clear selection',
                    danger: true
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }
                    selectedProducts = [];
                    localStorage.setItem(storageKey, JSON.stringify(selectedProducts));
                    updateSelectionUISafe();
                    populateModalSelectionList();
                    $('#sharingModal').modal('hide');
                    window.alertService.toastSuccess('Selection cleared.');
                });
            }
            window.clearFullSelection = clearFullSelection;

            function prepareLinksList() {
                let msg = `🔗 *CATASKY DIRECT PRODUCT LINKS*\n`;
                msg += `_Click below to view interactive logo overlays & spec sheets online:_\n`;
                msg += `==========================\n\n`;

                fetchMultipleProductDetails(selectedProducts).then(() => {
                    let promises = selectedProducts.map(id => fetchProductDetailsCached(id));

                    Promise.all(promises).then(dataList => {
                        dataList.forEach((data, index) => {
                            if (data && data.success) {
                                const p = data.product;
                                const name = p.name;
                                const moq = p.moq ? 'MOQ: ' + p.moq : 'MOQ: 100';
                                msg += `📦 *${index + 1}. ${name}* (${moq})\n`;
                                msg += `🔗 ${window.location.origin}/product/${p.slug}\n\n`;
                            }
                        });

                        msg += `==========================\n`;
                        msg += `👉 _Generate your custom catalog online: www.catasky.com_`;

                        $('#links-text-area').val(msg);
                    }).catch(err => {
                        console.error("Error loading links", err);
                    });
                });
            }

            window.copyShareText = function() {
                const text = $('#links-text-area').val();
                navigator.clipboard.writeText(text).then(function() {
                    window.alertService.toastSuccess("Links list copied to clipboard.");
                }).catch(function() {
                    window.alertService.errorAlert("Copy failed", "Please manually select and copy text.");
                });
            };

            window.shareOnWhatsAppDirect = function() {
                const text = $('#links-text-area').val();
                openWhatsAppChat(text);
                trackAnalyticsEvent('share_whatsapp_links', selectedProducts.length);
            };

            // DoubleTick.io WhatsApp outbound sharing & live session tracking integration
            window.shareWithDoubleTick = function() {
                const phone = $('#dt-customer-phone').val();
                const title = $('#dt-catalog-title').val() || 'APPARELS';
                
                if (!phone || phone.trim() === '') {
                    window.alertService.warningAlert('Missing phone number', 'Please enter a valid customer phone number.');
                    return;
                }
                
                if (selectedProducts.length === 0) {
                    window.alertService.warningAlert('Selection empty', 'Your selection cart is empty. Please select products first.');
                    return;
                }

                // Show spinner
                $('#dt-status-log').removeClass('d-none alert-success alert-danger').addClass('alert-info').html(`
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Initiating DoubleTick.io outbound WhatsApp dispatch...
                `);

                $.ajax({
                    url: "/api/doubletick/share",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        phone: phone,
                        product_ids: selectedProducts,
                        catalog_title: title,
                        is_subscriber: window.isSubscriberStore ? 1 : 0,
                        company_slug: typeof companySlug !== 'undefined' ? companySlug : ''
                    },
                    success: function(response) {
                        $('#dt-status-log').removeClass('alert-info alert-danger').addClass('alert-success').html(`
                            <i class="bi bi-check-circle-fill me-2"></i> ${response.message}
                            <br><small class="d-block mt-1">Catalog Code: <b>${response.code}</b></small>
                            <br><a href="${response.url}" target="_blank" class="small text-decoration-underline text-success">View secure client page</a>
                        `);
                        trackAnalyticsEvent('doubletick_share_success', selectedProducts.length);
                    },
                    error: function(xhr) {
                        const errMsg = xhr.responseJSON?.message || 'Error occurred while dispatching WhatsApp.';
                        $('#dt-status-log').removeClass('alert-info alert-success').addClass('alert-danger').html(`
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> ${errMsg}
                        `);
                    }
                });
            };

            // Helper to register secure proposal selections on the fly for interactive tracking
            function registerProposalAndGetTrackingUrl(catalogTitle, callback) {
                if (selectedProducts.length === 0) {
                    callback(`${window.location.origin}/catalog`);
                    return;
                }
                $.ajax({
                    url: '/api/doubletick/share',
                    type: 'POST',
                    data: {
                        phone: 'B2B Client', // placeholder label for automatic generic link shares
                        product_ids: selectedProducts,
                        catalog_title: catalogTitle,
                        is_subscriber: window.isSubscriberStore ? 1 : 0,
                        company_slug: typeof companySlug !== 'undefined' ? companySlug : '',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success && res.url) {
                            callback(res.url);
                        } else {
                            callback(`${window.location.origin}/catalog`);
                        }
                    },
                    error: function() {
                        callback(`${window.location.origin}/catalog`);
                    }
                });
            }

            // WhatsApp structured PDF sharing triggers
            window.sharePDFOnWhatsApp = async function(type) {
                type = normalizeExportType(type);
                if (type === 'images') {
                    return window.shareSeparateImages();
                }
                if (!selectedProducts || selectedProducts.length === 0) {
                    window.alertService.warningAlert('No products selected', 'Please select at least one product first.');
                    return;
                }

                setCurrentExportMode('pdf');
                const settings = getShareSettings();
                let prepared = getPreparedPdf(type);
                
                const btn = $('#pdf-direct-btn-details');
                const origHtml = btn.html();

                try {
                    if (!prepared) {
                        btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Compiling PDF...');
                        showToast('Preparing your PDF Proposal... Please wait.', 'Compiling PDF');
                        
                        const prepData = await preparePDFShareDoc(type);
                        prepared = prepData;
                        
                        btn.removeAttr('disabled').html(origHtml);
                    }

                    const result = await nativeShareFiles(
                        [prepared.file],
                        prepared.filename || document.title,
                        ''
                    );
                    if (result.unsupported) {
                        downloadPreparedPdf(prepared);
                        showToast('PDF compiled successfully! PDF download triggered.', 'PDF Downloaded');
                        return;
                    }
                    trackAnalyticsEventSafe('whatsapp_share_pdf_native_success', type);
                } catch (error) {
                    btn.removeAttr('disabled').html(origHtml);
                    if (error && error.name === 'AbortError') return;
                    console.error(error);
                    downloadPreparedPdf(prepared ? prepared : null);
                    showToast('PDF compiled successfully! PDF download triggered.', 'PDF Downloaded');
                }
            };



            function handleShareError(btn, originalHtml, reason) {
                btn.removeAttr('disabled').html(originalHtml);
                window.alertService.warningAlert("WhatsApp sharing failed", reason + ". Sharing only the interactive link as fallback.");
                // Fallback to link sharing only
                const catalogTitle = $('#share-catalog-title').val() || 'Premium Selection';
                registerProposalAndGetTrackingUrl(catalogTitle, function(trackingUrl) {
                    let msg = `📘 *CATASKY SMART CATALOG*\n\n`;
                    msg += `🔗 *Press to Open Catalog:*\n`;
                    msg += `${trackingUrl}`;
                    openWhatsAppChat(msg);
                });
            }

            // Method 2: Send PDF Directly via WhatsApp / DoubleTick Business API
            window.sendPDFDirectly = function(type) {
                const phone = (type === 'details') ? 
                    $('#pdf-pane .dt-customer-phone-direct').val() : 
                    $('#image-pane .dt-customer-phone-direct').val();

                if (!phone || phone.trim() === '') {
                    window.alertService.warningAlert('Missing phone number', 'Please enter a valid customer WhatsApp phone number.');
                    return;
                }

                const catalogTitle = $('#share-catalog-title').val() || 'Premium Selection';

                const btn = $(`#pdf-api-btn-${type}`);
                const statusLog = $(`#dt-status-log-${type}`);
                const originalHtml = btn.html();

                // Set to loading
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Generating PDF...');
                statusLog.removeClass('d-none alert-success alert-danger').addClass('alert-info').html(`
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Compiling and building high-fidelity PDF specifications catalog...
                `);

                // 1. Generate PDF blob
                generatePDFBlob(type).then((pdfData) => {
                    btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Uploading PDF file...');
                    statusLog.html(`
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Uploading PDF to secure cloud server to get public download link...
                    `);

                    const formData = new FormData();
                    formData.append('pdf', pdfData.blob, pdfData.filename);
                    formData.append('filename', pdfData.filename);
                    formData.append('_token', '{{ csrf_token() }}');

                    // 2. Upload to server
                    $.ajax({
                        url: "{{ route('pdf.upload-temp') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success && response.url) {
                                // public_url is the actually accessible one (tmpfiles.org direct link when local, else production URL)
                                const pdfUrlForApi = response.public_url || response.url;
                                const productionUrl = response.url;

                                btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Dispatching WhatsApp PDF...');
                                statusLog.html(`
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Dispatching actual PDF file directly via DoubleTick.io WhatsApp Business APIs...
                                `);

                                // 3. Share via DoubleTick Outbound API
                                $.ajax({
                                    url: "/api/doubletick/share",
                                    type: "POST",
                                    data: {
                                        phone: phone,
                                        product_ids: selectedProducts,
                                        catalog_title: catalogTitle,
                                        pdf_url: pdfUrlForApi,
                                        send_type: 'pdf',
                                        is_subscriber: window.isSubscriberStore ? 1 : 0,
                                        company_slug: typeof companySlug !== 'undefined' ? companySlug : '',
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(dtResponse) {
                                        btn.removeAttr('disabled').html(originalHtml);
                                        statusLog.removeClass('alert-info alert-danger').addClass('alert-success').html(`
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                <div>
                                                    <span class="fw-bold">PDF Sent Directly!</span>
                                                    <br><small class="d-block mt-1">The PDF file was delivered successfully to <b>${phone}</b>.</small>
                                                    <small class="d-block mt-1">Direct PDF URL: <a href="${productionUrl}" target="_blank" class="text-success text-decoration-underline fw-bold">${productionUrl}</a></small>
                                                    <small class="d-block mt-2"><a href="{{ route('admin.tracking.analytics') }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill py-1 px-3 mt-1 fw-bold text-decoration-none">Open Live Tracking Panel</a></small>
                                                </div>
                                            </div>
                                        `);
                                        trackAnalyticsEvent('doubletick_pdf_direct_success', selectedProducts.length);
                                    },
                                    error: function(xhr) {
                                        btn.removeAttr('disabled').html(originalHtml);
                                        const errMsg = xhr.responseJSON?.message || 'Error occurred while dispatching WhatsApp PDF.';
                                        statusLog.removeClass('alert-info alert-success').addClass('alert-danger').html(`
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Failed to dispatch PDF: ${errMsg}
                                        `);
                                    }
                                });
                            } else {
                                btn.removeAttr('disabled').html(originalHtml);
                                statusLog.removeClass('alert-info alert-success').addClass('alert-danger').html(`
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> PDF upload failed on the server.
                                `);
                            }
                        },
                        error: function(xhr, status, error) {
                            btn.removeAttr('disabled').html(originalHtml);
                            statusLog.removeClass('alert-info alert-success').addClass('alert-danger').html(`
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> Server upload error: ${error}
                            `);
                        }
                    });
                }).catch((err) => {
                    console.error("PDF generation failed:", err);
                    btn.removeAttr('disabled').html(originalHtml);
                    statusLog.removeClass('alert-info alert-success').addClass('alert-danger').html(`
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> PDF compilation failed: ${err.message || err}
                    `);
                });
            };

            window.generateLiveImagePreview = function() {
                setCurrentExportMode('image');
                const type = 'images';
                if (selectedProducts.length === 0) return;
                window.renderedPreviews.images = true;

                const badge = $('#pdf-preview-status-images');
                const loader = $('#pdf-preview-loader-images');
                const frame = $('#pdf-preview-frame-images');
                const token = ++window.exportBuildTokens.images;

                badge.removeClass('bg-success bg-danger text-dark text-white').addClass('bg-warning text-dark').text('Rendering...');
                loader.removeClass('d-none').addClass('d-flex');
                frame.removeClass('d-flex').addClass('d-none');
                setExportButtonsState(type, false, 'Rendering images...');

                fetchMultipleProductDetails(selectedProducts).then(() => {
                    return Promise.all(selectedProducts.map(id => fetchProductDetailsCached(id)));
                }).then(dataList => {
                    const validDataList = dataList.filter(d => d && d.success);
                    const imageItems = getImagePdfItems(validDataList);
                    // Show ALL selected products in the preview (no 12-item cap)
                    const previewItems = imageItems;
                    // preview-scale maps the 1080px internal card to the displayed card width.
                    // We use CSS grid 1fr columns; approximate card width from container.
                    // The card inner div is 1080px wide; scale = cardDisplayWidth / 1080.
                    // We target ~2 columns on mobile (~160px each) and 176px on desktop.
                    const previewScale = (0.218).toFixed(4); // 0.1630
                    const includeLink = getShareSettings().includeLink;
                    const html = `
                        <div class="share-image-preview-grid">
                            ${previewItems.map(item => {
                                const slug = item.product && item.product.slug ? item.product.slug : '';
                                const url = slug 
                                    ? (window.isSubscriberStore 
                                        ? `${window.location.origin}/product/${slug}?is_subscriber=1&company_slug=${companySlug}` 
                                        : `${window.location.origin}/product/${slug}`)
                                    : (window.isSubscriberStore 
                                        ? `${window.location.origin}/subscriber_store/${companySlug}?products=${selectedProducts.join(',')}` 
                                        : `${window.location.origin}/catalog?products=${selectedProducts.join(',')}`);
                                const inner = `<div style="--preview-scale:${previewScale};">${renderImagePdfBoxHtml(item)}</div>`;
                                if (!includeLink) {
                                    return `<div class="share-image-preview-card">${inner}</div>`;
                                }
                                return `
                                    <a class="share-image-preview-card" href="${url}" target="_blank">
                                        ${inner}
                                    </a>
                                `;
                            }).join('')}
                        </div>
                    `;
                    $('#pdf-preview-html-images').html(html);
                    
                    // Initialize dynamic scale synchronization for zero-gap previews on any device
                    if (window.previewResizeObserver) {
                        window.previewResizeObserver.disconnect();
                    }
                    const ro = new ResizeObserver(entries => {
                        for (let entry of entries) {
                            const card = entry.target;
                            const width = card.clientWidth;
                            if (width > 0) {
                                const scale = (width / 1080).toFixed(6);
                                card.style.setProperty('--preview-scale', scale);
                            }
                        }
                    });
                    document.querySelectorAll('.share-image-preview-card').forEach(card => ro.observe(card));
                    window.previewResizeObserver = ro;

                    $('#pdf-preview-page-images').css({ width: '100%', height: 'auto', minHeight: '460px', transform: 'none', boxShadow: 'none', background: '#f8fafc' });
                    $('#pdf-preview-scale-wrap-images').css({ height: 'auto', display: 'block', overflow: 'auto' });
                    loader.removeClass('d-flex').addClass('d-none');
                    frame.removeClass('d-none').addClass('d-flex');

                    badge.removeClass('bg-warning bg-danger text-white').addClass('bg-warning text-dark').text('Compiling images...');
                    return prepareImageShareDocs().then(() => {
                        if (token !== window.exportBuildTokens.images) return;
                        badge.removeClass('bg-warning bg-danger text-dark').addClass('bg-success text-white').text('Ready');
                        setExportButtonsState(type, true);
                    });
                }).catch(err => {
                    console.error("Error generating image preview", err);
                    badge.removeClass('bg-warning bg-success text-dark text-white').addClass('bg-danger text-white').text('Load Error');
                    loader.removeClass('d-flex').addClass('d-none');
                    setExportButtonsState(type, false);
                });
            };

            async function buildShareImageFiles(progressFn) {
                if (!selectedProducts || selectedProducts.length === 0) {
                    throw new Error('Please select at least one product first.');
                }
                await fetchMultipleProductDetails(selectedProducts);
                const dataList = await Promise.all(selectedProducts.map(id => fetchProductDetailsCached(id)));
                const imageItems = getImagePdfItems(dataList.filter(d => d && d.success));

                const totalItems = imageItems.length;
                
                // PERFORMANCE OPTIMIZATION: Preload ALL images in parallel upfront to utilize browser parallel connection pool!
                await Promise.all(imageItems.map(item => preloadImage(item.imageUrl)));

                const BATCH_SIZE = 8;
                const CONCURRENT_RENDERS = 8; // Double the rendering concurrency for ultra super fast loading!
                const results = [];

                for (let i = 0; i < imageItems.length; i += BATCH_SIZE) {
                    const batchItems = imageItems.slice(i, i + BATCH_SIZE);

                    // Process this batch with a concurrent worker queue of CONCURRENT_RENDERS = 8
                    const batchResults = await processInParallelBatches(batchItems, CONCURRENT_RENDERS, async (item, batchIdx) => {
                        const globalIdx = i + batchIdx;
                        const p = item.product || {};
                        const cacheKey = getCardCacheKey(p.id, item.imageUrl);

                        let blob = window.imageCache.get(cacheKey);
                        if (!blob) {
                            blob = await captureCardAsBlob(p, item.imageUrl || '');
                            window.imageCache.set(cacheKey, blob);
                        }

                        const ext = 'jpg';
                        const mime = 'image/jpeg';
                        const filename = `${String(p.slug || p.id || 'product').replace(/[^a-z0-9_-]+/gi, '_')}_${item.label || (globalIdx + 1)}.${ext}`;
                        return new File([blob], filename, { type: mime });
                    }, (completedInBatch, totalInBatch) => {
                        if (progressFn) {
                            const completedTotal = results.length + completedInBatch;
                            progressFn(completedTotal, totalItems);
                        }
                    });

                    results.push(...batchResults);
                }

                return results;
            }

            window.shareImageSystem = async function() {
                // Global lock: prevent multiple simultaneous share operations
                if (window._isSharingImages) return;
                window._isSharingImages = true;
                try {
                    setCurrentExportMode('image');
                    const settings = getShareSettings();
                    if (!selectedProducts || selectedProducts.length === 0) {
                        window.alertService.warningAlert('No products selected', 'Please select at least one product first.');
                        return;
                    }

                    let prepared = getPreparedImages();
                    if (!prepared) {
                        try {
                            prepared = await prepareImageShareDocs({ showProgressUI: true, mode: 'system_share' });
                        } catch (err) {
                            console.error('Failed to prepare images:', err);
                            window.alertService.errorAlert('Image sharing failed', err.message || err);
                            return;
                        }
                    }



                    const result = await sequentialNativeShare(prepared.files, settings);

                    if (result.cancelled) return; // User dismissed — do nothing

                    if (result.unsupported) {
                        await whatsappImageShareFallback(prepared.files, settings, 'not supported');
                        return;
                    }

                    if (result.shared) {
                        trackAnalyticsEventSafe('system_share_images_success', result.sharedCount);
                        showToast(`Successfully shared ${result.sharedCount} of ${result.totalCount} product flyers!`, 'Images Shared', 'success');
                    }
                } catch (err) {
                    console.error('shareImageSystem error:', err);
                    window.alertService && window.alertService.errorAlert('Sharing failed', err.message || String(err));
                } finally {
                    window._isSharingImages = false;
                }
            };

            window.generateLivePDFPreview = function(type = 'details') {
                type = normalizeExportType(type);
                if (type === 'details') setCurrentExportMode('pdf');
                if (type === 'images') setCurrentExportMode('image');
                if (selectedProducts.length === 0) return;

                // Mark preview as compiled
                if (type === 'details') {
                    window.renderedPreviews.details = true;
                } else {
                    window.renderedPreviews.images = true;
                }

                const badge = $(`#pdf-preview-status-${type}`);
                const loader = $(`#pdf-preview-loader-${type}`);
                const frame = $(`#pdf-preview-frame-${type}`);
                const token = ++window.exportBuildTokens[type];

                // Shift previews into loading mode
                badge.removeClass('bg-success bg-danger text-dark text-white').addClass('bg-warning text-dark').text('Compiling...');
                loader.removeClass('d-none').addClass('d-flex');
                frame.removeClass('d-block').addClass('d-none');
                setExportButtonsState(type, false, type === 'details' ? 'Compiling PDF...' : 'Compiling images...');
                updateProgressText(type, 'Preparing Preview...');

                const companyName = "CataSky";
                const companyPhone = "{{ $settings->phone ?? '+91 919871376205' }}";
                const companyLogo = "@if($settings && $settings->logo && !empty($logoBase64)){{ $logoBase64 }}@else @endif";

                const catalogTitle = $('#share-catalog-title').val() || 'Premium Selection';

                const today = new Date();
                const dateStr = String(today.getDate()).padStart(2, '0') + '-' + today.toLocaleString('default', { month: 'short' }) + '-' + today.getFullYear();

                fetchMultipleProductDetails(selectedProducts).then(() => {
                    if (token !== window.exportBuildTokens[type]) return;
                    updateProgressText(type, 'Optimizing Images...');
                    
                    let promises = selectedProducts.map(id => fetchProductDetailsCached(id));

                    Promise.all(promises).then(dataList => {
                        if (token !== window.exportBuildTokens[type]) return;
                        const validDataList = dataList.filter(d => d && d.success);
                        
                        // A4 Page styling variables
                        const pageStyle = `
                            box-sizing: border-box;
                            width: 790px;
                            height: 1117px;
                            padding: 45px 50px;
                            background: #ffffff;
                            position: relative;
                            display: flex;
                            flex-direction: column;
                            justify-content: space-between;
                            overflow: hidden;
                            font-family: 'Outfit', 'Poppins', 'Helvetica Neue', Arial, sans-serif;
                        `;

                        let previewPageHtml = '';

                        if (type === 'details') {
                            const shareSettings = getShareSettings();
                            const detailsItems = getImagePdfItems(validDataList);
                            // Generate the first page of the product cards in a 2x2 grid (exactly items 1 to 4)
                            const chunk = detailsItems.slice(0, 4);
                            let gridHtml = '';
                            
                            chunk.forEach((item, index) => {
                                const p = item.product;
                                const name = p.name || 'Product Model';
                                const priceVal = formatProductPrice(p);
                                const imgUrl = item.imageUrl;
                                const description = escapeHtml(p.short_description || p.specifications || p.additional_info || 'Detailed product specifications available on request.');
                                
                                // Extract MRP and Offer price — guard against null/undefined/empty/zero
                                const mrpRaw = p.mrp ?? p.price;
                                const offerRaw = p.offer_price ?? p.sale_price;
                                const _parseP = v => {
                                    if (v === null || v === undefined || String(v).trim() === '') return NaN;
                                    const n = Number(v); return (isNaN(n) || n <= 0) ? NaN : n;
                                };
                                const mrpNum = _parseP(mrpRaw); const offerNum = _parseP(offerRaw);
                                const mrpValue = !isNaN(mrpNum) ? '\u20B9 ' + mrpNum.toLocaleString('en-IN') : '';
                                const offerValue = !isNaN(offerNum) ? '\u20B9 ' + offerNum.toLocaleString('en-IN') : '';

                                // Remove per-card watermark from details PDF — watermark is now on the full page
                                gridHtml += `
                                <div style="box-sizing:border-box;width:330px;height:420px;border:1.5px solid #d2d2d2;border-radius:12px;padding:15px;background:#ffffff;display:flex;flex-direction:column;justify-content:space-between;font-family:Arial,sans-serif;">
                                    <!-- Image Box: background-size:contain to fill, no white space -->
                                    <div style="position:relative;width:100%;flex:1;border:1px solid #e2e8f0;border-radius:10px;background-color:#f8fafc;${imgUrl ? `background-image:url('${imgUrl}');background-position:center;background-size:contain;background-repeat:no-repeat;` : ''}overflow:hidden;box-sizing:border-box;">
                                        ${!imgUrl ? `<div style="position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:14px;color:#94A3B8;font-weight:bold;">No Image</div>` : ''}
                                    </div>
                                    <!-- Below Image: Name, MRP strikethrough, Offer Price, Description -->
                                    <div style="margin-top:8px;display:flex;flex-direction:column;gap:3px;box-sizing:border-box;text-align:left;width:100%;">
                                        <div style="font-size:13px;font-family:Arial,sans-serif;color:#000000;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(name)}</div>
                                        ${shareSettings.showPrice && (mrpValue || offerValue) ? `
                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                            ${mrpValue && offerValue && mrpValue !== offerValue ? `<span style="font-size:11px;color:#888888;font-family:Arial,sans-serif;text-decoration:line-through;">MRP: ${mrpValue}</span>` : ''}
                                            ${offerValue ? `<span style="font-size:12px;color:#1D6FEB;font-family:Arial,sans-serif;font-weight:bold;">Offer: ${offerValue}</span>` : (mrpValue ? `<span style="font-size:12px;color:#1D6FEB;font-family:Arial,sans-serif;font-weight:bold;">${mrpValue}</span>` : '')}
                                        </div>` : ''}
                                        ${shareSettings.showNote ? `<div style="font-size:11px;color:#555555;font-family:Arial,sans-serif;line-height:1.3;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">Note: ${escapeHtml(shareSettings.noteText || '')}</div>` : ''}
                                        <div style="font-size:10px;color:#777777;font-family:Arial,sans-serif;line-height:1.3;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">${description}</div>
                                    </div>
                                </div>
                                `;
                            });

                            const totalPages = Math.ceil(detailsItems.length / 4);

                            previewPageHtml = `
                            <div class="pdf-page" style="${pageStyle}position:relative;">
                                ${shareSettings.showWatermark ? `
                                <!-- Full-page diagonal watermark (one per page, sits on top of content) -->
                                <div style="position:absolute;top:0;left:0;right:0;bottom:0;z-index:99;pointer-events:none;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                                    <div style="transform:rotate(-30deg);text-align:center;max-width:90%;">
                                        <div style="font-size:${(window.companySiteTitle || 'CataSky').length > 15 ? '42px' : ((window.companySiteTitle || 'CataSky').length > 8 ? '55px' : '75px')};font-weight:900;color:rgba(0,0,0,0.10);font-family:Arial,sans-serif;letter-spacing:${(window.companySiteTitle || 'CataSky').length > 15 ? '2px' : '4px'};text-transform:uppercase;line-height:1.4;word-wrap:break-word;">${(window.companySiteTitle || 'CataSky').toUpperCase()}</div>
                                    </div>
                                </div>` : ''}
                                <!-- Logo and Header -->
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1.5px solid #d2d2d2; font-family: 'Outfit', sans-serif;">
                                    <div style="display: flex; align-items: center;">
                                        ${shareSettings.showWatermark && companyLogo && companyLogo.trim().length > 0
                                            ? `<img src="${companyLogo}" style="max-height: 44px; object-fit: contain;">`
                                            : `<div style="width: 40px; height: 40px; border-radius: 8px; background: #1D6FEB; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.1rem;">C</div>`
                                        }
                                    </div>
                                    <div style="font-size: 0.85rem; color: #333333; font-weight: bold;">
                                        Date: ${dateStr}
                                    </div>
                                </div>

                                <!-- Catalog Title -->
                                ${shareSettings.showTitle ? `
                                <div style="text-align: center; font-size: 1.5rem; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px; margin-top: 15px; margin-bottom: 15px; font-family: 'Outfit', sans-serif;">
                                    ${escapeHtml(catalogTitle)}
                                </div>` : ''}

                                <!-- 2x2 Grid Container -->
                                <div style="flex-grow: 1; display: flex; flex-wrap: wrap; justify-content: center; gap: 20px 24px; margin-top: 10px; width: 100%; box-sizing: border-box;">
                                    ${gridHtml}
                                </div>

                                <!-- Page Footer -->
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1.5px solid #d2d2d2; padding-top: 12px; font-size: 0.78rem; color: #555555; font-family: 'Outfit', sans-serif; margin-top: 15px;">
                                    <div style="font-weight: bold;">
                                        ${companyPhone} &bull; ${shareSettings.showNote ? escapeHtml(shareSettings.noteText || 'Custom catalog notes included') : 'Secure B2B Portfolio'}
                                    </div>
                                    <div style="font-weight: bold;">Page 1 of ${totalPages}</div>
                                </div>
                            </div>
                            `;
                        } else {
                            const imageItems = getImagePdfItems(validDataList);
                            previewPageHtml = renderImagePdfPageHtml(imageItems, { companyLogo, catalogTitle, productCount: validDataList.length });
                        }

                        // Directly populate the scaled live HTML container!
                        const htmlContainer = $(`#pdf-preview-html-${type}`);
                        htmlContainer.html(previewPageHtml);

                        // Show frame & hide loader instantly (0ms delay!)
                        loader.removeClass('d-flex').addClass('d-none');
                        frame.removeClass('d-none').addClass('d-flex');

                        // Dynamically scale PDF page to fill the preview box width for both tabs
                        if (type === 'images' || type === 'details') {
                            setTimeout(function() {
                                const frameEl = document.getElementById('pdf-preview-frame-' + type);
                                const pageEl  = document.getElementById('pdf-preview-page-' + type);
                                const wrapEl  = document.getElementById('pdf-preview-scale-wrap-' + type);
                                if (frameEl && pageEl && wrapEl) {
                                    const containerW = frameEl.offsetWidth;
                                    const containerH = frameEl.offsetHeight || 460;
                                    const pdfW = 790;
                                    const pdfH = 1117;
                                    // Scale to fill width fully
                                    const scaleX = containerW / pdfW;
                                    // Also ensure height fits
                                    const scaleY = containerH / pdfH;
                                    const scale = Math.min(scaleX, scaleY);
                                    pageEl.style.transform = `scale(${scale})`;
                                    pageEl.style.transformOrigin = 'top center';
                                    // Adjust wrap height to match scaled content so no extra gap below
                                    const scaledH = Math.round(pdfH * scale);
                                    wrapEl.style.height = scaledH + 'px';
                                }
                            }, 30);
                        }

                        // Set preview frame as ready, but keep buttons disabled while pre-compiling the actual document in the background!
                        badge.removeClass('bg-warning bg-danger text-dark text-white').addClass('bg-warning text-dark').text('Compiling PDF...');
                        setExportButtonsState(type, false, 'Compiling PDF...');
                        
                        return preparePDFShareDoc(type).then(() => {
                            if (token !== window.exportBuildTokens[type]) return;
                            badge.removeClass('bg-warning bg-danger text-dark').addClass('bg-success text-white').text('Ready');
                            setExportButtonsState(type, true);
                        }).catch(compileErr => {
                            console.error("Background PDF compilation failed:", compileErr);
                            if (token !== window.exportBuildTokens[type]) return;
                            badge.removeClass('bg-warning bg-danger text-dark text-white').addClass('bg-success text-white').text('Ready');
                            setExportButtonsState(type, true);
                        });
                    }).catch(err => {
                        console.error("Error generating PDF preview", err);
                        badge.removeClass('bg-warning bg-success text-dark text-white').addClass('bg-danger text-white').text('Load Error');
                        loader.removeClass('d-flex').addClass('d-none');
                        setExportButtonsState(type, false);
                    });
                });
            };

            // Bind change and keyup inputs to trigger live preview compilations
            $('#pdf-cover-color, #pdf-include-branding').on('change keyup', function() {
                if ($('#pdf-tab').hasClass('active')) {
                    generateLivePDFPreview('details');
                } else {
                    window.renderedPreviews.details = false;
                }
            });

            $('#pdf-grid-columns, #pdf-grid-subtitle').on('change keyup', function() {
                if ($('#image-tab').hasClass('active')) {
                    generateLiveImagePreview();
                } else {
                    window.renderedPreviews.images = false;
                }
            });

            // ================================================================
            // SHARED PDF BUILD ENGINE
            // Uses jsPDF + html2canvas directly for guaranteed non-blank output.
            // Both generatePDFCatalogue (download) and generatePDFBlob (upload)
            // call this single async function.
            // ===============================================            // ================================================================
            // SHARED MULTI-PAGE PDF BUILD ENGINE (A4 Pagination)
            // Uses jsPDF + html2canvas directly for guaranteed non-blank output.
            // Generates beautiful discrete pages and fits them exactly on A4.
            // ================================================================
            async function buildPDFDocument(type) {
                type = normalizeExportType(type);
                const updateExporterProgress = (t, text) => {
                    const spinners = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> ';
                    $(`#pdf-download-btn-${t}:disabled`).html(spinners + text);
                    $(`#pdf-direct-btn-${t}:disabled`).html(spinners + text);
                    $(`#pdf-share-btn-${t}:disabled`).html(spinners + text);
                    $(`#pdf-api-btn-${t}:disabled`).html(spinners + text);
                    const statusLog = $(`#dt-status-log-${t}`);
                    if (statusLog.length && !statusLog.hasClass('d-none')) {
                        statusLog.html(spinners + text);
                    }
                };

                const companyName = "CataSky";
                const companyPhone = "{{ $settings->phone ?? '+91 919871376205' }}";
                const companyLogo = "@if($settings && $settings->logo && !empty($logoBase64)){{ $logoBase64 }}@else @endif";

                const catalogTitle = $('#share-catalog-title').val() || 'Premium Selection';

                const today = new Date();
                const dateStr = String(today.getDate()).padStart(2, '0') + '-'
                    + today.toLocaleString('default', { month: 'short' }) + '-'
                    + today.getFullYear();

                const logoImgHtml = (companyLogo && companyLogo.trim().length > 0)
                    ? `<img src="${companyLogo}" style="max-height:44px;object-fit:contain;display:block;">`
                    : `<div style="width:42px;height:42px;border-radius:10px;background:#4F46E5;color:white;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.2rem;font-family:'Outfit',sans-serif;">C</div>`;

                // Fetch all product data (utilizing caching) in exactly 1 request
                await fetchMultipleProductDetails(selectedProducts);
                const dataList = await Promise.all(
                    selectedProducts.map(id => fetchProductDetailsCached(id))
                );
                const validDataList = dataList.filter(d => d && d.success);

                // Helper to chunk products into pages
                const chunkArray = (arr, size) => {
                    const chunks = [];
                    for (let i = 0; i < arr.length; i += size) {
                        chunks.push(arr.slice(i, i + size));
                    }
                    return chunks;
                };

                let pagesHtml = '';
                let imageChunks = [];

                // A4 Page styling variables
                const pageStyle = `
                    box-sizing: border-box;
                    width: 790px;
                    height: 1117px;
                    padding: 45px 50px;
                    background: #ffffff;
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    overflow: hidden;
                    font-family: 'Outfit', 'Poppins', 'Helvetica Neue', Arial, sans-serif;
                    page-break-after: always;
                `;

                // Cover page layout style (exactly like A4 size in proportion)
                const coverStyle = `
                    box-sizing: border-box;
                    width: 790px;
                    height: 1117px;
                    padding: 60px 50px;
                    background: linear-gradient(135deg, #090A1A 0%, #15183E 50%, #2A0E3C 100%);
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    overflow: hidden;
                    font-family: 'Outfit', 'Poppins', 'Helvetica Neue', Arial, sans-serif;
                    page-break-after: always;
                `;

                // ── 1. STUNNING COVER PAGE (Bypassed for Details Table PDF) ──
                const coverPageHtml = `
                <div class="pdf-page" style="${coverStyle}">
                    <!-- Background Decorative Rings/Glows -->
                    <div style="position: absolute; top: -150px; right: -150px; width: 450px; height: 450px; border-radius: 50%; background: radial-gradient(circle, rgba(124, 58, 237, 0.15) 0%, transparent 70%); pointer-events: none;"></div>
                    <div style="position: absolute; bottom: -200px; left: -200px; width: 550px; height: 550px; border-radius: 50%; background: radial-gradient(circle, rgba(6, 182, 212, 0.12) 0%, transparent 70%); pointer-events: none;"></div>

                    <!-- Header Row -->
                    <div style="display: flex; justify-content: space-between; align-items: center; z-index: 2;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            ${companyLogo && companyLogo.trim().length > 0 
                                ? `<img src="${companyLogo}" style="max-height: 52px; object-fit: contain; filter: brightness(0) invert(1);">`
                                : `<div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.4rem;">C</div>`
                            }
                            <div>
                                <div style="font-weight: 900; font-size: 1.6rem; color: #ffffff; letter-spacing: -0.5px; line-height: 1.1; font-family:'Outfit', sans-serif;">${companyName}</div>
                                <div style="font-size: 0.74rem; color: #38BDF8; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 2px;">Verified B2B Partner</div>
                            </div>
                        </div>
                        <div style="background: rgba(245, 158, 11, 0.08); border: 1.5px solid rgba(245, 158, 11, 0.35); padding: 8px 18px; border-radius: 30px; color: #F59E0B; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; font-family:'Outfit', sans-serif;">
                            Official Proposal
                        </div>
                    </div>

                    <!-- Large Center Title Block -->
                    <div style="margin: auto 0; z-index: 2; display: flex; flex-direction: column; gap: 20px;">
                        <div style="font-size: 0.85rem; color: #818CF8; font-weight: 800; text-transform: uppercase; letter-spacing: 4px; font-family:'Outfit', sans-serif;">B2B Product Portfolio</div>
                        
                        <!-- Glowing Modern Left-Border Line -->
                        <div style="border-left: 6px solid #6366F1; padding-left: 24px; display: flex; flex-direction: column; gap: 12px;">
                            <h1 style="margin: 0; color: #ffffff; font-weight: 900; font-size: 3.2rem; line-height: 1.15; font-family:'Outfit', sans-serif; letter-spacing: -0.8px; text-transform: uppercase; text-shadow: 0 10px 25px rgba(0,0,0,0.3);">
                                ${catalogTitle}
                            </h1>
                            <div style="font-size: 1.05rem; color: rgba(255, 255, 255, 0.75); font-weight: 500; font-family:'Poppins', sans-serif; max-width: 550px; line-height: 1.45;">
                                Curated product specifications, pricing matrices, and commercial guidelines prepared exclusively for your reviewing.
                            </div>
                            <div style="margin-top: 15px;">
                                <div class="pdf-catalog-link-target" style="display: inline-block; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); color: #ffffff; border-radius: 30px; padding: 10px 24px; font-size: 0.85rem; font-weight: bold; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);">
                                    View Full Collection Online &rarr;
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Info Row -->
                    <div style="z-index: 2;">
                        <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 22px 28px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; backdrop-filter: blur(10px);">
                            <div>
                                <div style="font-size: 0.74rem; color: rgba(255,255,255,0.5); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Prepared For</div>
                                <div style="font-size: 1rem; color: #ffffff; font-weight: 800; font-family:'Outfit', sans-serif; margin-top: 3px;">Valued B2B Client</div>
                            </div>
                            <div style="height: 35px; width: 1px; background: rgba(255,255,255,0.15);"></div>
                            <div>
                                <div style="font-size: 0.74rem; color: rgba(255,255,255,0.5); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Collection Items</div>
                                <div style="font-size: 1rem; color: #38BDF8; font-weight: 800; font-family:'Outfit', sans-serif; margin-top: 3px;">${validDataList.length} Products</div>
                            </div>
                            <div style="height: 35px; width: 1px; background: rgba(255,255,255,0.15);"></div>
                            <div>
                                <div style="font-size: 0.74rem; color: rgba(255,255,255,0.5); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Date Compiled</div>
                                <div style="font-size: 1rem; color: #ffffff; font-weight: 800; font-family:'Outfit', sans-serif; margin-top: 3px;">${dateStr}</div>
                            </div>
                        </div>

                        <!-- Mini Footer in Cover -->
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.12); padding-top: 20px; font-size: 0.75rem; color: rgba(255,255,255,0.45);">
                            <div>Secure B2B Portfolio &bull; Confidential</div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="color: #6366F1; font-weight: 900; font-family:'Outfit', sans-serif;">CATASKY</span> Smart Catalog
                            </div>
                        </div>
                    </div>
                </div>
                `;

                // Image sharing is handled as direct PNG files, not as PDF pages.

                // ── 2. PRODUCT DETAILS OR IMAGE GRID PAGES ──────────────────
                if (type === 'details') {
                    const shareSettings = getShareSettings();
                    // Split products into pages (exactly 4 per page for 2x2 grid)
                    const detailsItems = getImagePdfItems(validDataList);
                    const productChunks = chunkArray(detailsItems, 4);
                    const totalProductPages = productChunks.length;

                    productChunks.forEach((chunk, pageIndex) => {
                        let gridHtml = '';
                        chunk.forEach((item, index) => {
                            const p = item.product;
                            const name = p.name || 'Product Model';
                            const priceVal = formatProductPrice(p);
                            const imgUrl = item.imageUrl;
                            const description = escapeHtml(p.short_description || p.specifications || p.additional_info || 'Detailed product specifications available on request.');
                            
                            // Extract MRP and Offer price — guard against null/undefined/empty/zero
                            const mrpRaw = p.mrp ?? p.price;
                            const offerRaw = p.offer_price ?? p.sale_price;
                            const _parseP2 = v => {
                                if (v === null || v === undefined || String(v).trim() === '') return NaN;
                                const n = Number(v); return (isNaN(n) || n <= 0) ? NaN : n;
                            };
                            const mrpNum2 = _parseP2(mrpRaw); const offerNum2 = _parseP2(offerRaw);
                            const mrpValue = !isNaN(mrpNum2) ? '\u20B9 ' + mrpNum2.toLocaleString('en-IN') : '';
                            const offerValue = !isNaN(offerNum2) ? '\u20B9 ' + offerNum2.toLocaleString('en-IN') : '';

                            // Remove per-card watermark — watermark is now one full-page overlay
                            gridHtml += `
                            <div class="pdf-product-link-target" data-slug="${escapeHtml(p.slug)}" style="box-sizing:border-box;width:330px;height:420px;border:1.5px solid #d2d2d2;border-radius:12px;padding:15px;background:#ffffff;display:inline-flex;flex-direction:column;justify-content:space-between;font-family:Arial,sans-serif;cursor:pointer;">
                                <!-- Image Box: background-size:contain to fill, no white space -->
                                <div style="position:relative;width:100%;flex:1;border:1px solid #e2e8f0;border-radius:10px;background-color:#f8fafc;${imgUrl ? `background-image:url('${imgUrl}');background-position:center;background-size:contain;background-repeat:no-repeat;` : ''}overflow:hidden;box-sizing:border-box;">
                                    ${!imgUrl ? `<div style="position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:14px;color:#94A3B8;font-weight:bold;">No Image</div>` : ''}
                                </div>
                                <!-- Below Image: Name, MRP strikethrough, Offer Price, Description -->
                                <div style="margin-top:8px;display:flex;flex-direction:column;gap:3px;box-sizing:border-box;text-align:left;width:100%;">
                                    <div style="font-size:13px;font-family:Arial,sans-serif;color:#000000;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(name)}</div>
                                    ${shareSettings.showPrice && (mrpValue || offerValue) ? `
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                        ${mrpValue && offerValue && mrpValue !== offerValue ? `<span style="font-size:11px;color:#888888;font-family:Arial,sans-serif;text-decoration:line-through;">MRP: ${mrpValue}</span>` : ''}
                                        ${offerValue ? `<span style="font-size:12px;color:#1D6FEB;font-family:Arial,sans-serif;font-weight:bold;">Offer: ${offerValue}</span>` : (mrpValue ? `<span style="font-size:12px;color:#1D6FEB;font-family:Arial,sans-serif;font-weight:bold;">${mrpValue}</span>` : '')}
                                    </div>` : ''}
                                    ${shareSettings.showNote ? `<div style="font-size:11px;color:#555555;font-family:Arial,sans-serif;line-height:1.3;">Note: ${escapeHtml(shareSettings.noteText || '')}</div>` : ''}
                                    <div style="font-size:10px;color:#777777;font-family:Arial,sans-serif;line-height:1.3;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">${description}</div>
                                </div>
                            </div>
                            `;
                        });

                        pagesHtml += `
                        <div class="pdf-page" style="${pageStyle}position:relative;">
                            ${shareSettings.showWatermark ? `
                            <!-- Full-page diagonal watermark (one per page, sits on top of content) -->
                            <div style="position:absolute;top:0;left:0;right:0;bottom:0;z-index:99;pointer-events:none;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                                <div style="transform:rotate(-30deg);text-align:center;max-width:90%;">
                                    <div style="font-size:${(window.companySiteTitle || 'CataSky').length > 15 ? '42px' : ((window.companySiteTitle || 'CataSky').length > 8 ? '55px' : '75px')};font-weight:900;color:rgba(0,0,0,0.10);font-family:Arial,sans-serif;letter-spacing:${(window.companySiteTitle || 'CataSky').length > 15 ? '2px' : '4px'};text-transform:uppercase;line-height:1.4;word-wrap:break-word;">${(window.companySiteTitle || 'CataSky').toUpperCase()}</div>
                                </div>
                            </div>` : ''}
                            <!-- Logo and Header -->
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1.5px solid #d2d2d2; font-family: 'Outfit', sans-serif;">
                                <div style="display: flex; align-items: center;">
                                    ${shareSettings.showWatermark && companyLogo && companyLogo.trim().length > 0
                                        ? `<img src="${companyLogo}" style="max-height: 44px; object-fit: contain;">`
                                        : `<div style="width: 40px; height: 40px; border-radius: 8px; background: #1D6FEB; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.1rem;">C</div>`
                                    }
                                </div>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="pdf-catalog-link-target" style="background: #1D6FEB; color: #ffffff; border-radius: 20px; padding: 5px 12px; font-size: 0.72rem; font-weight: bold; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px;">
                                        View Full Collection
                                    </div>
                                    <div style="font-size: 0.85rem; color: #333333; font-weight: bold;">
                                        Date: ${dateStr}
                                    </div>
                                </div>
                            </div>

                            <!-- Catalog Title -->
                            ${shareSettings.showTitle ? `
                            <div style="text-align: center; font-size: 1.5rem; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px; margin-top: 15px; margin-bottom: 15px; font-family: 'Outfit', sans-serif;">
                                ${escapeHtml(catalogTitle)}
                            </div>` : ''}

                            <!-- 2x2 Grid Container -->
                            <div style="flex-grow: 1; display: flex; flex-wrap: wrap; justify-content: center; gap: 20px 24px; margin-top: 10px; width: 100%; box-sizing: border-box;">
                                ${gridHtml}
                            </div>

                            <!-- Simple Footer -->
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1.5px solid #d2d2d2; padding-top: 12px; font-size: 0.78rem; color: #555555; font-family: 'Outfit', sans-serif; margin-top: 15px;">
                                <div style="font-weight: bold;">
                                    ${companyPhone} &bull; ${shareSettings.showNote ? escapeHtml(shareSettings.noteText || 'Custom catalog notes included') : 'Secure B2B Portfolio'}
                                </div>
                                <div style="font-weight: bold; display: flex; align-items: center; gap: 10px;">
                                    <span class="pdf-catalog-link-target" style="color: #1D6FEB; cursor: pointer; text-decoration: underline; font-weight: bold;">View Selection Online</span>
                                    <span>&bull;</span>
                                    <span>Page ${pageIndex + 1} of ${totalProductPages}</span>
                                </div>
                            </div>
                        </div>
                        `;
                    });

                } else {
                    const imageItems = getImagePdfItems(validDataList);
                    imageChunks = chunkArray(imageItems, 6);
                    (imageChunks.length ? imageChunks : [[]]).forEach(chunk => {
                        pagesHtml += renderImagePdfPageHtml(chunk, { companyLogo, catalogTitle, productCount: validDataList.length });
                    });
                }

                // ── 3. INJECT INTO RENDERING CONTAINER ──────────────────────
                const wrapper = document.getElementById('pdf-template-wrapper');
                const container = document.getElementById('pdf-rendering-container');

                wrapper.innerHTML = pagesHtml;

                // Dynamically extract card coordinate positions in millimeters before compilation
                const allPagesLinks = [];
                const allPagesCatalogLinks = [];
                const pageElements = wrapper.querySelectorAll('.pdf-page');
                pageElements.forEach((pageEl) => {
                    const links = [];
                    const cards = pageEl.querySelectorAll('.pdf-product-link-target');
                    cards.forEach(card => {
                        const slug = card.getAttribute('data-slug');
                        if (slug) {
                            let top = card.offsetTop;
                            let left = card.offsetLeft;
                            let parent = card.offsetParent;
                            while (parent && parent !== pageEl) {
                                top += parent.offsetTop;
                                left += parent.offsetLeft;
                                parent = parent.offsetParent;
                            }
                            const pxToMm = 210 / 790;
                            links.push({
                                slug: slug,
                                x: left * pxToMm,
                                y: top * pxToMm,
                                w: card.offsetWidth * pxToMm,
                                h: card.offsetHeight * pxToMm
                            });
                        }
                    });
                    allPagesLinks.push(links);

                    const catalogLinks = [];
                    const els = pageEl.querySelectorAll('.pdf-catalog-link-target');
                    els.forEach(el => {
                        let top = el.offsetTop;
                        let left = el.offsetLeft;
                        let parent = el.offsetParent;
                        while (parent && parent !== pageEl) {
                            top += parent.offsetTop;
                            left += parent.offsetLeft;
                            parent = parent.offsetParent;
                        }
                        const pxToMm = 210 / 790;
                        catalogLinks.push({
                            x: left * pxToMm,
                            y: top * pxToMm,
                            w: el.offsetWidth * pxToMm,
                            h: el.offsetHeight * pxToMm
                        });
                    });
                    allPagesCatalogLinks.push(catalogLinks);
                });

                // Keep it in the viewport so the browser decodes/paints it, but hide it completely (opacity: 0.02, z-index: -99999)
                const prevStyle = container.getAttribute('style');
                container.setAttribute('style',
                    'position:fixed;top:0;left:0;width:790px;z-index:-99999;opacity:0.02;visibility:visible;pointer-events:none;background:white;overflow:visible;');

                // Wait for all images inside the compiled pages to fully load & decode offscreen
                const imgs = wrapper.querySelectorAll('img');
                await Promise.all(Array.from(imgs).map(async img => {
                    if (img.complete && img.naturalWidth > 0) {
                        try {
                            await img.decode();
                        } catch(e) {}
                        return;
                    }
                    return new Promise(resolve => {
                        img.onload  = async () => {
                            try {
                                await img.decode();
                            } catch(e) {}
                            resolve();
                        };
                        img.onerror = resolve;
                        // Safety timeout: 4s per image
                        setTimeout(resolve, 4000);
                    });
                }));

                // Extra render paint delay so browser can composite the fonts and layers perfectly
                await new Promise(resolve => setTimeout(resolve, 200));

                // ── 4. RENDER INDIVIDUAL PAGES (Pipelined Parallel Captures) ──
                // Reusing pageElements declared above
                const pageCanvases = new Array(pageElements.length);

                try {
                    // Process in parallel batches of 12 pages to maximize multi-threaded canvas compiling speed
                    const batchSize = 12;
                    for (let i = 0; i < pageElements.length; i += batchSize) {
                        const batch = [];
                        for (let j = i; j < Math.min(i + batchSize, pageElements.length); j++) {
                            const pageEl = pageElements[j];
                            const pageIdx = j;
                            
                            // Dynamic B2B SaaS progress updates!
                            if (typeof updateExporterProgress === 'function') {
                                updateExporterProgress(type, `Compiling Page ${pageIdx + 1} of ${pageElements.length}...`);
                            }

                                const renderScale = (selectedProducts && selectedProducts.length > 8) ? 2.5 : 3.0;
                                batch.push(
                                    html2canvas(pageEl, {
                                        scale:           renderScale, // 3.0/2.5 is extremely high-resolution crisp print quality!
                                    useCORS:         true,
                                    allowTaint:      false,
                                    backgroundColor: '#ffffff',
                                    logging:         false,
                                    windowWidth:     790,
                                    windowHeight:    1117,
                                    scrollX:         0,
                                    scrollY:         0,
                                }).then(canvas => {
                                    if (!canvas || canvas.width === 0 || canvas.height === 0) {
                                        throw new Error(`Failed to render canvas for Page ${pageIdx+1}`);
                                    }
                                    pageCanvases[pageIdx] = canvas;
                                })
                            );
                        }
                        await Promise.all(batch);
                    }
                } finally {
                    // Restore styling and clear wrapper
                    container.setAttribute('style', prevStyle);
                    wrapper.innerHTML = '<!-- Compiled offscreen -->';
                }

                if (pageCanvases.length === 0) {
                    throw new Error('No pages were successfully rendered.');
                }

                // ── 5. COMPOSE MULTI-PAGE A4 jsPDF ──────────────────────────
                // Use jsPDF from the standalone CDN (window.jspdf.jsPDF)
                const jsPDFCtor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : null;
                if (!jsPDFCtor) {
                    throw new Error('jsPDF library not loaded. Please check CDN scripts in the page head.');
                }
                const pdf = new jsPDFCtor({ unit: 'mm', format: 'a4', orientation: 'portrait' });

                const includeLink = getShareSettings().includeLink;
                const targetUrl = window.isSubscriberStore 
                    ? `${window.location.origin}/subscriber_store/${companySlug}?products=${selectedProducts.join(',')}`
                    : `${window.location.origin}/catalog?products=${selectedProducts.join(',')}`;

                for (let i = 0; i < pageCanvases.length; i++) {
                    if (i > 0) pdf.addPage();
                    const canvas = pageCanvases[i];
                    // Save as high-quality JPEG to keep sizes small and compile fast (using 0.95 quality for sharp vector look)
                    const imgData = canvas.toDataURL('image/jpeg', 0.95);
                    // Render exactly across full page A4 (210mm x 297mm) with zero margin
                    pdf.addImage(imgData, 'JPEG', 0, 0, 210, 297);

                    if (includeLink) {
                        // 1. Precise collection links on the header/footer/cover catalog button areas:
                        const catalogLinks = allPagesCatalogLinks[i];
                        if (catalogLinks && catalogLinks.length) {
                            catalogLinks.forEach(link => {
                                pdf.link(link.x, link.y, link.w, link.h, { url: targetUrl });
                            });
                        }

                        // 2. Highly precise dynamic product card links: "jis box par click karke vo product open hoga"
                        const pageLinks = allPagesLinks[i];
                        if (pageLinks && pageLinks.length) {
                            pageLinks.forEach(link => {
                                if (link.slug) {
                                    const productUrl = window.isSubscriberStore
                                        ? `${window.location.origin}/product/${link.slug}?is_subscriber=1&company_slug=${companySlug}`
                                        : `${window.location.origin}/product/${link.slug}`;
                                    pdf.link(link.x, link.y, link.w, link.h, { url: productUrl });
                                }
                            });
                        }
                    }
                }

                const filename = catalogTitle.toLowerCase().replace(/[^a-z0-9]+/g, '_') + '_' + type + '.pdf';
                return { pdf, filename };
            }
// ── Download PDF (triggers browser save-as dialog) ────────────
            window.generatePDFCatalogue = function(type = 'details') {
                type = normalizeExportType(type);
                if (type === 'images') {
                    return window.downloadAllCards();
                }
                if (!selectedProducts || selectedProducts.length === 0) {
                    window.alertService.warningAlert('No products selected', 'Please select at least one product first.');
                    return;
                }

                const btn = $(`#pdf-download-btn-${type}`);
                const origHtml = btn.html();
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Building PDF...');

                const prepared = getPreparedPdf(type);
                if (prepared) {
                    downloadPreparedPdf(prepared);
                    trackAnalyticsEventSafe('pdf_generated_' + type, selectedProducts.length);
                    btn.removeAttr('disabled').html(origHtml);
                    return;
                }

                buildPDFDocument(type).then(function({ pdf, filename }) {
                    pdf.save(filename);
                    trackAnalyticsEventSafe('pdf_generated_' + type, selectedProducts.length);
                    btn.removeAttr('disabled').html(origHtml);
                }).catch(function(err) {
                    console.error('[Catasky PDF] Download failed:', err);
                    window.alertService.errorAlert('PDF generation failed', (err.message || err) + ' Please ensure all product images have loaded.');
                    btn.removeAttr('disabled').html(origHtml);
                });
            };

            // ── Generate Blob for server upload / WhatsApp share ──────────
            window.generatePDFBlob = function(type = 'details') {
                type = normalizeExportType(type);
                return buildPDFDocument(type).then(function({ pdf, filename }) {
                    const blob = pdf.output('blob');
                    // Validate: a real PDF must be > 1KB
                    if (!blob || blob.size < 1024) {
                        throw new Error('Generated PDF blob is empty or too small (size=' + (blob ? blob.size : 0) + 'B). Rendering failed.');
                    }
                    return { blob, filename, pdf };
                });
            };

            // Web Share API System Sharing for any app (WhatsApp, Gmail, Instagram, Facebook etc.)
            window.sharePDFSystem = async function(type) {
                type = normalizeExportType(type);
                if (type === 'images') {
                    return window.shareImageSystem();
                }
                if (!selectedProducts || selectedProducts.length === 0) {
                    window.alertService.warningAlert('No products selected', 'Please select at least one product first.');
                    return;
                }

                setCurrentExportMode('pdf');
                const settings = getShareSettings();
                let prepared = getPreparedPdf(type);
                
                const btn = $('#pdf-share-btn-details');
                const origHtml = btn.html();

                try {
                    if (!prepared) {
                        btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Compiling PDF...');
                        showToast('Preparing your PDF Proposal... Please wait.', 'Compiling PDF');
                        
                        const prepData = await preparePDFShareDoc(type);
                        prepared = prepData;
                        
                        btn.removeAttr('disabled').html(origHtml);
                    }

                    const result = await nativeShareFiles(
                        [prepared.file],
                        prepared.filename || document.title,

                    );
                    if (result.unsupported) {
                        downloadPreparedPdf(prepared);
                        showToast('PDF compiled successfully! PDF download triggered.', 'PDF Downloaded');
                        return;
                    }
                    trackAnalyticsEventSafe('system_share_pdf_success', selectedProducts.length);
                } catch (error) {
                    btn.removeAttr('disabled').html(origHtml);
                    if (error && error.name === 'AbortError') return;
                    console.error(error);
                    downloadPreparedPdf(prepared ? prepared : null);
                    showToast('PDF compiled successfully! PDF download triggered.', 'PDF Downloaded');
                }
            };

            /*
            ==================================================
            SMART ANALYTICS & VISITOR ENGAGEMENT ENGINE
            ==================================================
            */
            let visitStartTime = Date.now();
            let analyticsStore = JSON.parse(localStorage.getItem('catasky_analytics')) || {
                visits_count: 0,
                total_seconds: 0,
                viewed_products: [],
                shares: 0,
                actions: []
            };

            if (sessionStorage.getItem('catasky_session_counted') === null) {
                analyticsStore.visits_count += 1;
                sessionStorage.setItem('catasky_session_counted', 'true');
            }

            window.addEventListener('beforeunload', function() {
                const elapsedSeconds = Math.round((Date.now() - visitStartTime) / 1000);
                analyticsStore.total_seconds += elapsedSeconds;
                localStorage.setItem('catasky_analytics', JSON.stringify(analyticsStore));
            });

            function trackAnalyticsEvent(eventType, metadata) {
                analyticsStore.actions.push({
                    type: eventType,
                    data: metadata,
                    time: new Date().toISOString()
                });

                if (eventType === 'view_details') {
                    if (analyticsStore.viewed_products.indexOf(metadata.toString()) === -1) {
                        analyticsStore.viewed_products.push(metadata.toString());
                    }
                } else if (eventType.indexOf('pdf_generated') > -1 || eventType.indexOf('share_whatsapp') > -1) {
                    analyticsStore.shares += 1;
                }

                const viewScore = analyticsStore.viewed_products.length * 10;
                const shareScore = analyticsStore.shares * 25;
                const timeScore = Math.round(analyticsStore.total_seconds / 3);
                const visitScore = analyticsStore.visits_count * 5;
                analyticsStore.engagement_score = viewScore + shareScore + timeScore + visitScore;

                localStorage.setItem('catasky_analytics', JSON.stringify(analyticsStore));
                console.log(`[Smart Analytics] Event: ${eventType} | Target: ${metadata} | Dynamic Engagement Score: ${analyticsStore.engagement_score}`);
            }

            window.trackAnalyticsEvent = trackAnalyticsEvent;



            // Re-sync selectedProducts with localStorage and run initial UI sync at the end of ready block
            try {
                selectedProducts = JSON.parse(localStorage.getItem(storageKey)) || [];
                if (!Array.isArray(selectedProducts)) {
                    selectedProducts = [];
                }
            } catch (e) {
                console.error("Error parsing selected_products from localStorage:", e);
                selectedProducts = [];
            }
            updateSelectionUISafe();
        });
    </script>
    
    <!-- Premium Bootstrap Toast Notification Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">

        @if(session('success'))
            <div id="sessionToastSuccess" class="toast premium-toast border-0 shadow-lg rounded-4 animate-fade-in" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="toast-header border-0 bg-success text-white py-2.5 rounded-top-4">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <strong class="me-auto font-outfit" style="font-size: 0.85rem;">Success Update</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body py-3 px-3 bg-white text-dark small rounded-bottom-4">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('info'))
            <div id="sessionToastInfo" class="toast premium-toast border-0 shadow-lg rounded-4 animate-fade-in" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="toast-header border-0 bg-info text-dark py-2.5 rounded-top-4">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <strong class="me-auto font-outfit" style="font-size: 0.85rem;">System Alert</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body py-3 px-3 bg-white text-dark small rounded-bottom-4">
                    {{ session('info') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div id="sessionToastError" class="toast premium-toast border-0 shadow-lg rounded-4 animate-fade-in" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="toast-header border-0 bg-danger text-white py-2.5 rounded-top-4">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <strong class="me-auto font-outfit" style="font-size: 0.85rem;">System Alert</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body py-3 px-3 bg-white text-dark small rounded-bottom-4">
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </div>

    @if(!isset($isSubscriberStore) || !$isSubscriberStore)
    <!-- Premium Floating Enquiry Button -->
    <a href="{{ route('contact') }}" class="floating-enquiry-btn shadow-lg" title="Enquire Now">
        <i class="bi bi-chat-left-text-fill"></i>
        <span>Enquire Now</span>
    </a>
    @endif

    {{-- PWA install buttons moved to header navbar --}}

    @stack('scripts')
    <script>
      $(document).ready(function () {

    $('.toast').each(function () {
        var toast = new bootstrap.Toast(this);
        toast.show();
    });

});


    </script>

    @if(Auth::check() && Auth::user()->hasRole('Subscriber') && isset($isSubscriberStore) && $isSubscriberStore)
    <script>
        // PWA Install Script for Logged-in Subscribers
        (function () {
            let deferredPrompt;
            const installBtnDesktop = document.getElementById('pwa-install-btn');
            const installBtnMobile = document.getElementById('pwa-install-btn-mobile');

            // Check install status
            function isPWAInstalled() {
                return localStorage.getItem('pwaInstalled') === 'true' ||
                    window.matchMedia('(display-mode: standalone)').matches ||
                    window.navigator.standalone === true;
            }

            // Update button state (keep them visible at all times)
            function updateInstallButtonState() {
                const installed = isPWAInstalled();
                const icon = installed
                    ? '<i class="bi bi-check-circle-fill text-success"></i>'
                    : '<i class="bi bi-phone-vibrate text-white"></i>';
                const label = installed ? ' Installed' : '';
                
                if (installBtnDesktop) {
                    installBtnDesktop.innerHTML = icon + '<span>' + label + '</span>';
                    installBtnDesktop.title = installed ? 'App is Installed' : 'Install App';
                }
                if (installBtnMobile) {
                    installBtnMobile.innerHTML = icon + '<span>' + label + '</span>';
                    installBtnMobile.title = installed ? 'App is Installed' : 'Install App';
                }
            }

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                updateInstallButtonState();
                console.log('PWA install prompt available');
            });

            const triggerPwaInstall = async () => {
                if (isPWAInstalled()) {
                    if (window.alertService) {
                        window.alertService.toastSuccess('App is already installed on this device.');
                    } else {
                        alert('App is already installed on this device.');
                    }
                    return;
                }

                if (!deferredPrompt) {
                    // No warning toast if prompt is unavailable - just check matches display-mode or show installed, or do nothing.
                    if (window.matchMedia('(display-mode: standalone)').matches || localStorage.getItem('pwaInstalled') === 'true') {
                        if (window.alertService) {
                            window.alertService.toastSuccess('App is already installed on this device.');
                        } else {
                            alert('App is already installed on this device.');
                        }
                    }
                    return;
                }

                try {
                    deferredPrompt.prompt();
                    const choiceResult = await deferredPrompt.userChoice;
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    deferredPrompt = null;
                } catch (error) {
                    console.error('Installation failed:', error);
                }
            };

            if (installBtnDesktop) {
                installBtnDesktop.addEventListener('click', triggerPwaInstall);
            }
            if (installBtnMobile) {
                installBtnMobile.addEventListener('click', triggerPwaInstall);
            }

            window.addEventListener('appinstalled', () => {
                console.log('PWA installed successfully');
                localStorage.setItem('pwaInstalled', 'true');
                deferredPrompt = null;
                updateInstallButtonState();
                if (window.alertService) {
                    window.alertService.toastSuccess('App installed successfully.');
                } else {
                    alert('App installed successfully.');
                }
            });

            // Register Service Worker
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register('/sw.js')
                        .then(function (registration) {
                            console.log('ServiceWorker registration successful:', registration.scope);
                        })
                        .catch(function (err) {
                            console.log('ServiceWorker registration failed:', err);
                        });
                });
            }

            // Initial check
            updateInstallButtonState();
        })();
    </script>
    @endif

    {{-- ─── GLOBAL FRONTEND EVENT TRACKER (sendBeacon-first, no page reload) ─── --}}
    <script>
    /**
     * trackEvent() - Global frontend-only event tracker.
     *
     * Usage:
     *   trackEvent('pdf_download', { product_id: 123, file_type: 'pdf' });
     *   trackEvent('whatsapp_share', { product_id: 456 });
     *   trackEvent('product_view', { product_id: 789 });
     *
     * Events: pdf_download | image_download | whatsapp_share | other_share | product_view
     *
     * Uses sendBeacon for reliability (works even after page unload).
     * Falls back to fetch with keepalive if sendBeacon is unavailable.
     * Queues to localStorage if offline and retries on next page load.
     */
    (function() {
        var TRACK_URL = '/api/track-event';
        var QUEUE_KEY = '_catasky_fe_queue';

        // Flush any queued events from previous page loads
        function flushQueue() {
            try {
                var raw = localStorage.getItem(QUEUE_KEY);
                if (!raw) return;
                var queue = JSON.parse(raw);
                if (!Array.isArray(queue) || queue.length === 0) return;
                localStorage.removeItem(QUEUE_KEY);
                queue.forEach(function(evt) { _send(evt); });
            } catch(e) { localStorage.removeItem(QUEUE_KEY); }
        }

        function _send(payload) {
            try {
                if (navigator.sendBeacon) {
                    var blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
                    var ok = navigator.sendBeacon(TRACK_URL, blob);
                    if (!ok) { _queueLocally(payload); }
                } else {
                    fetch(TRACK_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                        keepalive: true
                    }).catch(function() { _queueLocally(payload); });
                }
            } catch(e) {
                _queueLocally(payload);
            }
        }

        function _queueLocally(payload) {
            try {
                var raw = localStorage.getItem(QUEUE_KEY);
                var queue = raw ? JSON.parse(raw) : [];
                queue.push(payload);
                // Cap at 50 events to avoid storage bloat
                if (queue.length > 50) queue = queue.slice(-50);
                localStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
            } catch(e) {}
        }

        /**
         * Public API: window.trackEvent(eventName, options)
         * @param {string} eventName - pdf_download|image_download|whatsapp_share|other_share|product_view
         * @param {object} options - { product_id, file_type, user_id, meta }
         */
        window.trackEvent = function(eventName, options) {
            options = options || {};
            var payload = {
                event:      eventName,
                product_id: options.product_id || null,
                file_type:  options.file_type  || null,
                user_id:    options.user_id    || null,
                meta:       Object.assign({
                    page:      window.location.pathname,
                    referrer:  document.referrer,
                    timestamp: new Date().toISOString()
                }, options.meta || {})
            };
            _send(payload);
        };

        // Flush on load + on visibility change (catches back-forward cache)
        flushQueue();
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') flushQueue();
        });

        // Flush remaining events before page unload
        window.addEventListener('beforeunload', function() {
            // sendBeacon handles this automatically for in-flight calls
        });
    })();
    </script>

    {{-- ─── FRONTEND SHARE TRACKING ─────────────────────────────────────────── --}}
    @if(isset($profile) && isset($isSubscriberStore) && $isSubscriberStore)
    <script>
    (function() {
        const SUBSCRIBER_USER_ID = {{ $profile->user_id ?? 'null' }};
        const COMPANY_SLUG = '{{ $profile->company_slug ?? '' }}';
        const CSRF = '{{ csrf_token() }}';
        const API_BASE = '/api/analytics';

        function getVisitorUuid() {
            let uuid = localStorage.getItem('_catasky_visitor');
            if (!uuid) {
                uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                    const r = Math.random() * 16 | 0;
                    return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
                });
                localStorage.setItem('_catasky_visitor', uuid);
            }
            return uuid;
        }

        const visitorUuid = getVisitorUuid();
        const sessionId = 'fss_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        window._frontendSessionId = sessionId;

        function trackEngagement(eventType, extraData) {
            const payload = Object.assign({
                session_id: sessionId,
                user_id: SUBSCRIBER_USER_ID,
                event_type: eventType,
                metadata: { company_slug: COMPANY_SLUG, source: 'frontend_store' }
            }, extraData || {});

            if (navigator.sendBeacon) {
                navigator.sendBeacon(API_BASE + '/engagement', new Blob([
                    JSON.stringify(Object.assign(payload, { _token: CSRF }))
                ], { type: 'application/json' }));
            } else {
                fetch(API_BASE + '/engagement', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(payload),
                    keepalive: true
                }).catch(function() {});
            }
        }

        // Track download events to download_logs table (for Downloads KPI in analytics)
        function trackDownload(fileType) {
            var payload = {
                session_id: sessionId,
                user_id: SUBSCRIBER_USER_ID,
                file_type: fileType || 'pdf',
                _token: CSRF
            };
            // Use sendBeacon for reliability, fallback to fetch
            if (navigator.sendBeacon) {
                navigator.sendBeacon(API_BASE + '/download', new Blob([
                    JSON.stringify(payload)
                ], { type: 'application/json' }));
            } else {
                fetch(API_BASE + '/download', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(payload),
                    keepalive: true
                }).catch(function() {});
            }
        }

        // Expose trackDownload globally so other scripts can call it
        window._trackDownload = trackDownload;

        function getSelectedProductIds() {
            if (typeof selectedProducts !== 'undefined' && Array.isArray(selectedProducts)) {
                return selectedProducts.map(function(p) { return p.id || p; });
            }
            return [];
        }

        // Wait for all JS to load, then wrap existing share functions
        function wrapShareFunctions() {
            // Wrap openSharingModal - track when share modal opens
            var _origOpenSharingModal = window.openSharingModal;
            if (typeof _origOpenSharingModal === 'function') {
                window.openSharingModal = function(tab) {
                    trackEngagement('catalogue_open', { metadata: { tab: tab, company_slug: COMPANY_SLUG } });
                    return _origOpenSharingModal.apply(this, arguments);
                };
            }

            // Wrap sharePDFSystem - track PDF share to any app
            var _origSharePDFSystem = window.sharePDFSystem;
            if (typeof _origSharePDFSystem === 'function') {
                window.sharePDFSystem = async function() {
                    trackEngagement('pdf_share', { product_ids: getSelectedProductIds() });
                    if (window.trackEvent) {
                        window.trackEvent('other_share', { product_id: null, file_type: 'pdf', meta: { share_type: 'any_app', product_ids: getSelectedProductIds() } });
                    }
                    return _origSharePDFSystem.apply(this, arguments);
                };
            }

            // Wrap sharePDFOnWhatsApp - track WhatsApp PDF share
            var _origSharePDFOnWhatsApp = window.sharePDFOnWhatsApp;
            if (typeof _origSharePDFOnWhatsApp === 'function') {
                window.sharePDFOnWhatsApp = async function() {
                    trackEngagement('whatsapp_pdf_share', { product_ids: getSelectedProductIds() });
                    if (window.trackEvent) {
                        window.trackEvent('whatsapp_share', { product_id: null, file_type: 'pdf', meta: { product_ids: getSelectedProductIds() } });
                    }
                    return _origSharePDFOnWhatsApp.apply(this, arguments);
                };
            }

            // Wrap shareImageSystem - track image share to any app
            var _origShareImageSystem = window.shareImageSystem;
            if (typeof _origShareImageSystem === 'function') {
                window.shareImageSystem = async function() {
                    trackEngagement('image_share', { product_ids: getSelectedProductIds() });
                    if (window.trackEvent) {
                        window.trackEvent('other_share', { product_id: null, file_type: 'image', meta: { share_type: 'any_app', product_ids: getSelectedProductIds() } });
                    }
                    return _origShareImageSystem.apply(this, arguments);
                };
            }

            // Wrap shareSeparateImages - track WhatsApp image share
            var _origShareSeparateImages = window.shareSeparateImages;
            if (typeof _origShareSeparateImages === 'function') {
                window.shareSeparateImages = async function() {
                    trackEngagement('whatsapp_image_share', { product_ids: getSelectedProductIds() });
                    if (window.trackEvent) {
                        window.trackEvent('whatsapp_share', { product_id: null, file_type: 'image', meta: { product_ids: getSelectedProductIds() } });
                    }
                    return _origShareSeparateImages.apply(this, arguments);
                };
            }

            // Wrap generatePDFCatalogue - track PDF download to download_logs
            var _origGeneratePDF = window.generatePDFCatalogue;
            if (typeof _origGeneratePDF === 'function') {
                window.generatePDFCatalogue = function() {
                    trackEngagement('pdf_download', { product_ids: getSelectedProductIds() });
                    trackDownload('pdf');
                    if (window.trackEvent) {
                        window.trackEvent('pdf_download', { product_id: null, file_type: 'pdf', meta: { product_ids: getSelectedProductIds() } });
                    }
                    return _origGeneratePDF.apply(this, arguments);
                };
            }

            // Wrap downloadAllCards - track image download to download_logs
            var _origDownloadAllCards = window.downloadAllCards;
            if (typeof _origDownloadAllCards === 'function') {
                window.downloadAllCards = function() {
                    trackEngagement('image_download', { product_ids: getSelectedProductIds() });
                    trackDownload('image');
                    if (window.trackEvent) {
                        window.trackEvent('image_download', { product_id: null, file_type: 'image', meta: { product_ids: getSelectedProductIds() } });
                    }
                    return _origDownloadAllCards.apply(this, arguments);
                };
            }

            // Wrap shareWithDoubleTick - track DoubleTick WhatsApp shares
            var _origShareWithDoubleTick = window.shareWithDoubleTick;
            if (typeof _origShareWithDoubleTick === 'function') {
                window.shareWithDoubleTick = function() {
                    trackEngagement('whatsapp_click', { product_ids: getSelectedProductIds() });
                    return _origShareWithDoubleTick.apply(this, arguments);
                };
            }

            // Wrap sendPDFDirectly - track DoubleTick PDF direct shares
            var _origSendPDFDirectly = window.sendPDFDirectly;
            if (typeof _origSendPDFDirectly === 'function') {
                window.sendPDFDirectly = function() {
                    trackEngagement('whatsapp_pdf_share', { product_ids: getSelectedProductIds() });
                    return _origSendPDFDirectly.apply(this, arguments);
                };
            }

            // Wrap shareOnWhatsAppDirect - track generic WhatsApp link share
            var _origShareOnWhatsAppDirect = window.shareOnWhatsAppDirect;
            if (typeof _origShareOnWhatsAppDirect === 'function') {
                window.shareOnWhatsAppDirect = function() {
                    trackEngagement('whatsapp_click', { product_ids: getSelectedProductIds() });
                    return _origShareOnWhatsAppDirect.apply(this, arguments);
                };
            }

            // Wrap copyShareText - track copying links
            var _origCopyShareText = window.copyShareText;
            if (typeof _origCopyShareText === 'function') {
                window.copyShareText = function() {
                    trackEngagement('copy_link', { product_ids: getSelectedProductIds() });
                    return _origCopyShareText.apply(this, arguments);
                };
            }
        }

        // Wait for DOM and all scripts to be ready
        if (document.readyState === 'complete') {
            setTimeout(wrapShareFunctions, 500);
        } else {
            window.addEventListener('load', function() {
                setTimeout(wrapShareFunctions, 500);
            });
        }
    })();
    </script>
    @endif
</body>
</html>
