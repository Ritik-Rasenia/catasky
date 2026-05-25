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
        $logoBase64 = '';
        if ($settings && $settings->logo) {
            $logoPath = public_path('uploads/settings/' . $settings->logo);
            if (file_exists($logoPath) && is_file($logoPath)) {
                $logoBase64 = asset('uploads/settings/' . $settings->logo);
            }
        }
        $siteTitle = $settings->site_title ?? 'Catasky';
        $siteDescription = $settings->site_description ?? 'Premium B2B catalogue and product sharing platform.';
        $faviconUrl = ($settings && $settings->favicon) ? asset('uploads/settings/' . $settings->favicon) : asset('uploads/fav.png');
        $footerLogoUrl = ($settings && $settings->footer_logo) ? asset('uploads/settings/' . $settings->footer_logo) : $logoBase64;
    @endphp

    <title>@yield('title', $siteTitle . ' - Premium B2B Catalogue')</title>
    <meta name="description" content="@yield('meta_description', Str::limit(strip_tags($siteDescription), 160, ''))">
    @if($settings && $settings->meta_keywords)
        <meta name="keywords" content="{{ $settings->meta_keywords }}">
    @endif
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    <!-- Google Fonts: Poppins & Outfit for modern SaaS feel -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- CDNs for Client-Side PDF & Image catalogue generation -->
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
        #selection-bar.floating-bar {
            left: 50% !important;
            right: auto !important;
            bottom: 20px !important;
            transform: translate(-50%, 130%) !important;
            width: min(720px, calc(100vw - 24px)) !important;
            z-index: 1045 !important;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            border-radius: 999px !important;
            background: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.12) !important;
            backdrop-filter: blur(24px) saturate(1.6) !important;
            -webkit-backdrop-filter: blur(24px) saturate(1.6) !important;
            padding: 12px !important;
            transition: transform 0.34s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.24s ease !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
        #selection-bar.floating-bar.active {
            transform: translate(-50%, 0) !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        #selection-bar .bar-actions {
            gap: 10px !important;
        }
        #selection-bar .bar-btn {
            min-height: 44px;
            border-radius: 999px !important;
            padding: 0 18px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
            white-space: nowrap;
        }
        #selection-bar .bar-btn:hover {
            transform: translateY(-2px);
            filter: saturate(1.08);
        }
        .share-image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 14px;
            align-items: start;
            padding: 16px;
        }
        .share-image-preview-card {
            display: block;
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
            text-decoration: none;
        }
        .share-image-preview-card > div {
            width: 800px;
            height: 800px;
            transform: scale(var(--preview-scale, 0.22));
            transform-origin: top left;
        }
        @media (max-width: 575.98px) {
            #selection-bar.floating-bar {
                border-radius: 24px !important;
                padding: 10px !important;
                width: min(480px, calc(100vw - 16px)) !important;
                bottom: 12px !important;
            }
            #selection-bar .bar-actions {
                display: grid !important;
                grid-template-columns: 1fr;
            }
            #selection-bar .bar-btn {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>

    <!-- Sticky Glassmorphic Navbar -->
    <nav class="navbar navbar-expand-lg navbar-premium">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand d-flex align-items-center gap-3" href="{{ route('home') }}">
                @if($settings && $settings->logo)
                    <img src="{{ asset('uploads/settings/' . $settings->logo) }}" alt="{{ $settings->site_title ?? 'Catasky' }}" style="max-height: 40px; object-fit: contain;">
                @else
                    <div class="logo-icon">C</div>
                @endif
               
            </a>

            <!-- Mobile Navbar Controls -->
            <div class="d-flex align-items-center gap-2 d-lg-none">
                <button class="nav-icon-btn" data-bs-toggle="modal" data-bs-target="#searchModal">
                    <i class="bi bi-search"></i>
                </button>
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
                            <a href="{{ route('subscriber.login') }}" class="btn btn-outline-primary w-100 py-2 fw-semibold" style="border-radius:12px; font-size:0.9rem;">Subscriber Login</a>
                        </li>
                        <li class="nav-item d-lg-none px-3 pb-2">
                            <a href="{{ route('subscriber.register') }}" class="btn btn-hero-primary w-100 py-2" style="border-radius:12px; font-size:0.9rem;"><i class="bi bi-rocket-takeoff-fill"></i> Start Free</a>
                        </li>
                    @endauth
                </ul>

                <!-- Desktop Right Action Bar -->
                <div class="d-none d-lg-flex align-items-center gap-2 ms-auto">
                    <button class="nav-icon-btn" data-bs-toggle="modal" data-bs-target="#searchModal" title="Search Catalogue">
                        <i class="bi bi-search"></i>
                    </button>

                    @auth
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-link text-decoration-none py-2 px-3 d-flex align-items-center gap-2" style="font-size:0.85rem;">
                                <i class="bi bi-person-fill-check text-primary"></i>
                                <span>{{ Str::limit(Auth::user()->name, 20) }}</span>
                            </a>

                            <div class="dropdown">
                                <button class="btn btn-premium btn-premium-outline py-2 px-2 d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:0.85rem; border-radius: 12px;">
                                    <i class="bi bi-chevron-down" style="font-size:0.9rem;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2 mt-2" style="min-width:200px;">
                                    <li><a href="{{ route('dashboard') }}" class="dropdown-header fw-bold text-dark text-decoration-none">{{ Auth::user()->name }}</a></li>
                                    @if(Auth::user()->hasRole('Subscriber'))
                                        <li><a class="dropdown-item rounded-3 py-2 px-3 small" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Subscriber Panel</a></li>
                                        <li><a class="dropdown-item rounded-3 py-2 px-3 small" href="{{ route('subscriber.profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                                        <li><hr class="dropdown-divider my-2 opacity-50"></li>
                                        <li>
                                            <form action="{{ route('subscriber.logout') }}" method="POST" class="d-block w-100 m-0">
                                                @csrf
                                                <button type="submit" class="dropdown-item rounded-3 py-2 px-3 small text-danger border-0 bg-transparent w-100 text-start"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</button>
                                            </form>
                                        </li>
                                    @else
                                        <li><a class="dropdown-item rounded-3 py-2 px-3 small" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                        <li><a class="dropdown-item rounded-3 py-2 px-3 small" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                                        <li><hr class="dropdown-divider my-2 opacity-50"></li>
                                        <li>
                                            <form action="{{ route('admin.logout') }}" method="POST" class="d-block w-100 m-0">
                                                @csrf
                                                <button type="submit" class="dropdown-item rounded-3 py-2 px-3 small text-danger border-0 bg-transparent w-100 text-start"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</button>
                                            </form>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('subscriber.login') }}" class="btn btn-premium btn-premium-outline py-2 px-3 fw-semibold" style="border-radius:12px; font-size:0.85rem;">
                            Subscriber Login
                        </a>
                        <a href="{{ route('subscriber.register') }}" class="btn-hero-primary py-2 px-4" style="border-radius:12px; font-size:0.9rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                            <i class="bi bi-rocket-takeoff-fill"></i> Start Free
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
                    <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none mb-3">
                        @if($footerLogoUrl)
                            <img src="{{ $footerLogoUrl }}" alt="{{ $siteTitle }}" style="max-height: 42px; max-width: 170px; object-fit: contain;">
                        @else
                            <div class="logo-icon bg-white text-dark fw-bold shadow-sm">C</div>
                        @endif
                       
                    </a>
                    <p class="text-white-50 small mb-4" style="max-width: 320px;">
                        {{ $siteDescription }}
                    </p>
                    <div class="d-flex gap-2">
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
                    </div>
                </div>

                <!-- Col 2: Navigation -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="fw-bold text-white mb-3" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Quick Links</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="{{ route('home') }}" class="text-white-50 text-decoration-none hover-white">Home Page</a></li>
                        <li><a href="{{ route('catalogue') }}" class="text-white-50 text-decoration-none hover-white">Explore Catalogue</a></li>
                        <li><a href="{{ route('contact') }}" class="text-white-50 text-decoration-none hover-white">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Col 3: Hot Categories -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="fw-bold text-white mb-3" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Categories</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        @php
                            $footerCats = \App\Models\Category::where('status', 1)->take(4)->get();
                        @endphp
                        @foreach($footerCats as $fcat)
                            <li><a href="{{ route('category.products', $fcat->slug) }}" class="text-white-50 text-decoration-none hover-white">{{ $fcat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Col 4: Corporate Support & Newsletter -->
                <div class="col-lg-3 col-md-6 col-12">
                    <h6 class="fw-bold text-white mb-3" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Support Center</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-white-50 mb-4">
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
                <p class="m-0">&copy; {{ date('Y') }} {{ $siteTitle }}. All Rights Reserved.</p>
                <div class="d-flex gap-3 mt-2 mt-md-0">
                    <a href="#" class="text-white-50 text-decoration-none hover-white">Terms of Service</a>
                    <span class="opacity-25">|</span>
                    <a href="#" class="text-white-50 text-decoration-none hover-white">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Sticky Multi-Selection Glass Bar -->
    <div id="selection-bar" class="floating-bar">
        <div class="bar-actions w-100 d-flex justify-content-center gap-3">
            <button class="bar-btn" onclick="openSharingModal('selection')" style="background: linear-gradient(135deg, #374151 0%, #111827 100%); border: none; box-shadow: 0 4px 10px rgba(17, 24, 39, 0.25); color: white; font-weight: 700;">
                <i class="bi bi-list-stars text-white d-none d-sm-inline-block"></i> <span class="d-none d-sm-inline">Selected (<span id="selected-count">0</span>)</span><span class="d-inline d-sm-none">Selected (<span class="selected-count-span">0</span>)</span>
            </button>
            <button class="bar-btn" onclick="openSharingModal('pdf')" style="background: var(--primary-gradient); border: none; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35); color: white; font-weight: 700;">
                <i class="bi bi-file-earmark-pdf-fill text-white d-none d-sm-inline-block"></i> <span class="d-none d-sm-inline">Details PDF</span><span class="d-inline d-sm-none">Details PDF</span>
            </button>
            <button class="bar-btn" onclick="openSharingModal('image')" style="background: var(--accent-gradient); border: none; box-shadow: 0 4px 10px rgba(6, 182, 212, 0.35); color: white; font-weight: 700;">
                <i class="bi bi-images text-white d-none d-sm-inline-block"></i> <span class="d-none d-sm-inline">Image Share</span><span class="d-inline d-sm-none">Images</span>
            </button>
        </div>
    </div>

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
                        <h4 class="modal-title fw-bold text-gradient mb-1" id="sharingModalLabel">Export Selection</h4>
                        <p class="text-secondary small mb-0">Generate B2B ready files and WhatsApp shares instantly</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs nav-tabs-premium gap-1.5 mb-4" id="sharingTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="selection-tab" data-bs-toggle="tab" data-bs-target="#selection-pane" type="button" role="tab" aria-controls="selection-pane" aria-selected="true">
                                <i class="bi bi-list-stars text-warning" style="margin-right: 8px;"></i>Selected (<span id="modal-selection-count">0</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pdf-tab" data-bs-toggle="tab" data-bs-target="#pdf-pane" type="button" role="tab" aria-controls="pdf-pane" aria-selected="false">
                                <i class="bi bi-file-earmark-pdf text-danger" style="margin-right: 8px;"></i>Details PDF
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="image-tab" data-bs-toggle="tab" data-bs-target="#image-pane" type="button" role="tab" aria-controls="image-pane" aria-selected="false">
                                <i class="bi bi-images text-accent" style="margin-right: 8px;"></i>Image Share
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="sharingTabsContent">
                        <!-- Tab 1: Selected Items Panel -->
                        <div class="tab-pane fade show active" id="selection-pane" role="tabpanel" aria-labelledby="selection-tab" tabindex="0">
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 text-gradient">Selected Blueprints</h5>
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
                                        <label class="form-label fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Catalogue Cover Color</label>
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
                                            <input class="form-check-input ms-0 premium-switch share-setting-mirror" data-share-setting="share-show-gallery" type="checkbox" checked style="width: 42px; height: 22px;">
                                        </div>
                                        <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center">
                                            <label class="form-check-label fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Watermark</label>
                                            <input class="form-check-input ms-0 premium-switch share-setting-mirror" data-share-setting="share-add-watermark" type="checkbox" checked style="width: 42px; height: 22px;">
                                        </div>
                                        <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center">
                                            <label class="form-check-label fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Notes</label>
                                            <input class="form-check-input ms-0 premium-switch share-setting-mirror" data-share-setting="share-add-note" type="checkbox" checked style="width: 42px; height: 22px;">
                                        </div>
                                        <input type="text" class="form-control rounded-3 p-2 share-setting-mirror" data-share-setting="share-note-text" value="An Award For Every Achievement & Effort" style="font-size: 0.8rem;">
                                        <div>
                                            <label class="form-label fw-bold text-secondary text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Logo position</label>
                                            <div class="d-flex gap-3 align-items-center">
                                                <button type="button" class="logo-pos-btn" data-pos="bottom-left" title="Bottom Left">
                                                    <span class="dot-indicator" style="bottom: 6px; left: 6px;"></span>
                                                </button>
                                                <button type="button" class="logo-pos-btn" data-pos="top-left" title="Top Left">
                                                    <span class="dot-indicator" style="top: 6px; left: 6px;"></span>
                                                </button>
                                                <button type="button" class="logo-pos-btn" data-pos="top-right" title="Top Right">
                                                    <span class="dot-indicator" style="top: 6px; right: 6px;"></span>
                                                </button>
                                                <button type="button" class="logo-pos-btn active" data-pos="bottom-right" title="Bottom Right">
                                                    <span class="dot-indicator" style="bottom: 6px; right: 6px;"></span>
                                                </button>
                                            </div>
                                        </div>
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
                                        <div id="pdf-preview-loader-details" class="d-flex flex-column justify-content-center align-items-center flex-grow-1 py-5">
                                            <div class="spinner-border text-danger mb-3" style="width: 2.5rem; height: 2.5rem;" role="status"></div>
                                            <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;">Building details preview...</span>
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
                                                    <input class="form-check-input ms-0 premium-switch" type="checkbox" id="share-show-gallery" checked style="width: 42px; height: 22px; cursor: pointer;">
                                                </div>
                                            </div>

                                            <!-- Add Logo Watermark Toggle -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Add logo watermark</h6>
                                                    <p class="text-secondary small mb-0" style="max-width: 280px;">Show your logo watermark on each photo while sharing</p>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0 premium-switch" type="checkbox" id="share-add-watermark" checked style="width: 42px; height: 22px; cursor: pointer;">
                                                </div>
                                            </div>

                                            <!-- Add Note Toggle -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Add a note</h6>
                                                    <p class="text-secondary small mb-0" style="max-width: 280px;">Add additional information on your photos like special offers, etc</p>
                                                </div>
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0 premium-switch" type="checkbox" id="share-add-note" checked style="width: 42px; height: 22px; cursor: pointer;">
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

                                        <!-- Premium Logo Corner position selector -->
                                        <div class="mb-4" id="watermark-pos-group">
                                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">Logo position</h6>
                                            <div class="d-flex gap-3 align-items-center mt-2">
                                                <button type="button" class="logo-pos-btn" data-pos="bottom-left" title="Bottom Left">
                                                    <span class="dot-indicator" style="bottom: 6px; left: 6px;"></span>
                                                </button>
                                                <button type="button" class="logo-pos-btn" data-pos="top-left" title="Top Left">
                                                    <span class="dot-indicator" style="top: 6px; left: 6px;"></span>
                                                </button>
                                                <button type="button" class="logo-pos-btn" data-pos="top-right" title="Top Right">
                                                    <span class="dot-indicator" style="top: 6px; right: 6px;"></span>
                                                </button>
                                                <button type="button" class="logo-pos-btn active" data-pos="bottom-right" title="Bottom Right">
                                                    <span class="dot-indicator" style="bottom: 6px; right: 6px;"></span>
                                                </button>
                                            </div>
                                            <input type="hidden" id="share-logo-pos" value="bottom-right">
                                        </div>
                                    </div>
                                    
                                    <!-- Status Message Overlay -->
                                    <div id="dt-status-log-images" class="alert alert-info py-2 px-3 small rounded-3 d-none mb-3" style="font-size: 0.75rem;"></div>

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
                                        <div id="pdf-preview-loader-images" class="d-flex flex-column justify-content-center align-items-center flex-grow-1 py-5">
                                            <div class="spinner-border text-accent mb-3" style="width: 2.5rem; height: 2.5rem;" role="status"></div>
                                            <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;">Building image share preview...</span>
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
                        <input type="text" id="catalogue-search" class="form-control border-0 p-2 fs-5 outline-none shadow-none w-100" placeholder="Type keyword (e.g. Sweater, Polo, Drinkware, Awards)..." autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0 border-top bg-light" style="max-height: 400px; overflow-y: auto;" id="search-results-pane">
                    <div class="p-4 text-center text-secondary small">
                        Type keyword to search among high-end catalogue items.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Off-screen PDF Catalogue Layout Container (Invisible to user but rendered in viewport for high-fidelity captures) -->
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
        var selectedProducts = [];
        window.companyLogoBase64 = "@if($settings && $settings->logo && !empty($logoBase64)){{ $logoBase64 }}@else @endif";
        try {
            selectedProducts = JSON.parse(localStorage.getItem('selected_products')) || [];
            if (!Array.isArray(selectedProducts)) {
                selectedProducts = [];
            }
            // Sanitize selection to prevent null/undefined/empty string items
            selectedProducts = selectedProducts.filter(id => id !== null && id !== undefined && id.toString().trim() !== "");
        } catch (e) {
            console.error("Error parsing selected_products from localStorage:", e);
            selectedProducts = [];
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
            return fetch('/api/product-details/' + idStr)
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
            
            window.activeFetchPromise = fetch('/api/products-details?ids=' + encodeURIComponent(uncachedIds.join(',')))
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

            localStorage.setItem('selected_products', JSON.stringify(selectedProducts));
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
                $.get('/product/' + productId + '/details', function(response) {
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
                    const imgUrl = data.thumbnail_url || '';
                    
                    $('#preview-img-src').attr('src', imgUrl);
                    
                    const showTitle = $('#share-show-title').is(':checked');
                    const showPrice = $('#share-show-price').is(':checked');
                    const showWatermark = $('#share-add-watermark').is(':checked');
                    const showNote = $('#share-add-note').is(':checked');
                    const noteText = $('#share-note-text').val() || '';
                    const logoPos = $('#share-logo-pos').val() || 'bottom-right';

                    // Toggles
                    if (showTitle) {
                        $('#preview-footer-title').text(p.name).show();
                    } else {
                        $('#preview-footer-title').hide();
                    }

                    const displayPrice = String(p.variant || 'On Request').includes('₹') ? String(p.variant || 'On Request') : '₹ ' + String(p.variant || 'On Request');
                    if (showPrice) {
                        $('#preview-footer-price').text(displayPrice).show();
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

            // Bind settings changes to live CSS preview updates
            $(document).on('change keyup', '#share-catalog-title, #share-show-title, #share-show-price, #share-show-gallery, #share-add-watermark, #share-add-note, #share-note-text', function() {
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

            $(document).on('change keyup', '.share-setting-mirror', function() {
                const targetId = $(this).data('share-setting');
                const target = $('#' + targetId);
                if (!target.length) return;
                if ($(this).is(':checkbox')) {
                    target.prop('checked', $(this).is(':checked'));
                } else {
                    target.val($(this).val());
                }
                target.trigger('change');
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

            syncWatermarkPositionControls();
            $(document).on('change', '#share-add-watermark', syncWatermarkPositionControls);

            window.downloadAllCards = function() {
                if (selectedProducts.length === 0) {
                    alertService.warningAlert("Please select at least one product first.");
                    return;
                }

                const btn = $('#pdf-download-btn-images');
                const origHtml = btn.html();
                fetchMultipleProductDetails(selectedProducts).then(() => {
                    let promises = selectedProducts.map(id => fetchProductDetailsCached(id));

                    Promise.all(promises).then(async dataList => {
                        const validDataList = dataList.filter(d => d && d.success);
                        let count = 0;

                        for (let i = 0; i < validDataList.length; i++) {
                            const data = validDataList[i];
                            const p = data.product;
                            
                            // Primary image
                            const imgUrl = data.thumbnail_url || '';
                            await captureAndDownloadCard(p, imgUrl, `card_${p.id}_primary.png`);
                            count++;

                            // Gallery images (if enabled)
                            const showGallery = $('#share-show-gallery').is(':checked');
                            if (showGallery && data.gallery_urls && data.gallery_urls.length > 0) {
                                for (let gIndex = 0; gIndex < data.gallery_urls.length; gIndex++) {
                                    const gImgUrl = data.gallery_urls[gIndex];
                                    await captureAndDownloadCard(p, gImgUrl, `card_${p.id}_gallery_${gIndex+1}.png`);
                                    count++;
                                }
                            }
                        }

                        btn.removeAttr('disabled').html(origHtml);
                        window.alertService.successAlert('Cards downloaded', `Successfully generated and downloaded ${count} product card images.`);
                    }).catch(err => {
                        console.error("Download failed:", err);
                        btn.removeAttr('disabled').html(origHtml);
                        window.alertService.errorAlert("Failed to download cards", err.message);
                    });
                });
            };

            async function captureAndDownloadCard(p, imgUrl, filename) {
                const wrapper = document.getElementById('share-card-template-wrapper');
                const container = document.getElementById('share-card-render-container');

                // Build the high-fidelity 800px x 800px social image template
                const cardHtml = renderImagePdfBoxHtml({ product: p, imageUrl: imgUrl });
                wrapper.innerHTML = cardHtml;

                // Shift off-screen container slightly to paint
                const prevStyle = container.getAttribute('style');
                container.setAttribute('style', 'position:fixed;top:0;left:0;width:800px;height:800px;z-index:-99999;opacity:0.02;visibility:visible;pointer-events:none;background:white;overflow:visible;');

                // Wait for image inside compiled page to fully load & decode offscreen
                const img = wrapper.querySelector('img');
                if (img) {
                    await new Promise(resolve => {
                        if (img.complete && img.naturalWidth > 0) {
                            img.decode().then(resolve).catch(resolve);
                            return;
                        }
                        img.onload = () => {
                            img.decode().then(resolve).catch(resolve);
                        };
                        img.onerror = resolve;
                        setTimeout(resolve, 4000); // 4s timeout
                    });
                }

                // Extra render paint delay
                await new Promise(resolve => setTimeout(resolve, 200));

                const canvas = await html2canvas(wrapper, {
                    scale: 1.5, // 1.5 is extremely fast (2-3x speedup) and crisp!
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    logging: false,
                    windowWidth: 800,
                    windowHeight: 800,
                    scrollX: 0,
                    scrollY: 0
                });

                // Restore styling
                container.setAttribute('style', prevStyle);
                wrapper.innerHTML = '';

                const dataUrl = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.download = filename;
                link.href = dataUrl;
                link.click();
            }

            window.shareSeparateImages = async function() {
                let phone = $('#share-customer-phone').val();
                const title = $('#share-catalog-title').val() || 'Premium Selection';

                if (!phone || phone.trim() === '') {
                    const promptResult = await window.alertService.promptText({
                        title: 'WhatsApp number',
                        message: "Enter customer's WhatsApp number with country code to share separate cards.",
                        placeholder: '91xxxxxxxxxx'
                    });
                    phone = promptResult.isConfirmed ? promptResult.value : '';
                }

                if (!phone || phone.trim() === '') {
                    return;
                }

                if (selectedProducts.length === 0) {
                    window.alertService.warningAlert('Selection empty', 'Your selection cart is empty.');
                    return;
                }

                const btn = $('#pdf-direct-btn-images');
                const statusLog = $('#dt-status-log-images');
                const originalHtml = btn.html();
                
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Generating...');
                statusLog.removeClass('d-none alert-success alert-danger').addClass('alert-info').html(`
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Fetching and preparing selection details...
                `);

                fetchMultipleProductDetails(selectedProducts).then(() => {
                    let promises = selectedProducts.map(id => fetchProductDetailsCached(id));

                    Promise.all(promises).then(async dataList => {
                        const validDataList = dataList.filter(d => d && d.success);
                        const cardsToProcess = [];

                        // 1. Flatten all cards to render
                        validDataList.forEach(data => {
                            const p = data.product;
                            
                            // Primary image card
                            cardsToProcess.push({
                                product: p,
                                imageUrl: data.thumbnail_url || '',
                                filename: `card_${p.id}_primary.png`
                            });

                            // Gallery image cards
                            const showGallery = $('#share-show-gallery').is(':checked');
                            if (showGallery && Array.isArray(data.gallery_urls)) {
                                data.gallery_urls.forEach((gImgUrl, gIndex) => {
                                    if (gImgUrl) {
                                        cardsToProcess.push({
                                            product: p,
                                            imageUrl: gImgUrl,
                                            filename: `card_${p.id}_gallery_${gIndex+1}.png`
                                        });
                                    }
                                });
                            }
                        });

                        const imageUrls = [];
                        let completedUploads = 0;
                        const uploadPromises = [];

                        // 2. Process sequential captures & concurrent pipelined uploads
                        for (let i = 0; i < cardsToProcess.length; i++) {
                            const card = cardsToProcess[i];
                            
                            // Update capture progress
                            statusLog.html(`
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Compiling product card image ${i + 1} of ${cardsToProcess.length}...
                            `);

                            // DOM Capture (sequential, extremely fast offscreen)
                            const blob = await captureCardAsBlob(card.product, card.imageUrl);
                            
                            if (blob) {
                                // Pipelined background parallel upload
                                const uploadPromise = uploadTempImageBlob(blob, card.filename).then(uploadUrl => {
                                    if (uploadUrl) {
                                        imageUrls.push(uploadUrl);
                                    }
                                    completedUploads++;
                                    statusLog.html(`
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Uploading card images... (${completedUploads} of ${cardsToProcess.length} completed)
                                    `);
                                });
                                uploadPromises.push(uploadPromise);
                            }
                        }

                        // 3. Await all pipelined uploads concurrently!
                        if (uploadPromises.length > 0) {
                            statusLog.html(`
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Finalizing parallel image card uploads...
                            `);
                            await Promise.all(uploadPromises);
                        }

                        if (imageUrls.length === 0) {
                            btn.removeAttr('disabled').html(originalHtml);
                            statusLog.removeClass('alert-info').addClass('alert-danger').html(`
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> Failed to generate/upload any product cards.
                            `);
                            return;
                        }

                        statusLog.html(`
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Dispatching separate images to WhatsApp via DoubleTick.io API...
                        `);

                        // Dispatch share request to DoubleTick
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
                                image_urls: imageUrls,
                                send_type: 'images'
                            },
                            success: function(dtResponse) {
                                btn.removeAttr('disabled').html(originalHtml);
                                statusLog.removeClass('alert-info alert-danger').addClass('alert-success').html(`
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        <div>
                                            <span class="fw-bold">Product Cards Shared Successfully!</span>
                                            <br><small class="d-block mt-1">Sent <b>${imageUrls.length} separate product cards</b> to <b>${phone}</b>.</small>
                                            <small class="d-block mt-2"><a href="{{ route('admin.tracking.analytics') }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill py-1 px-3 mt-1 fw-bold text-decoration-none">Open Live Tracking Panel</a></small>
                                        </div>
                                    </div>
                                `);
                                trackAnalyticsEvent('whatsapp_share_images_success', imageUrls.length);
                            },
                            error: function(xhr) {
                                btn.removeAttr('disabled').html(originalHtml);
                                const errMsg = xhr.responseJSON?.message || 'Error occurred while sharing cards.';
                                statusLog.removeClass('alert-info alert-success').addClass('alert-danger').html(`
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Failed to share cards: ${errMsg}
                                `);
                            }
                        });

                    }).catch(err => {
                        console.error("Sharing failed:", err);
                        btn.removeAttr('disabled').html(originalHtml);
                        statusLog.removeClass('alert-info alert-success').addClass('alert-danger').html(`
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Error: ${err.message || err}
                        `);
                    });
                });
            };

            async function captureCardAsBlob(p, imgUrl) {
                const wrapper = document.getElementById('share-card-template-wrapper');
                const container = document.getElementById('share-card-render-container');

                // Build the template
                const cardHtml = renderImagePdfBoxHtml({ product: p, imageUrl: imgUrl });
                wrapper.innerHTML = cardHtml;

                const prevStyle = container.getAttribute('style');
                container.setAttribute('style', 'position:fixed;top:0;left:0;width:800px;height:800px;z-index:-99999;opacity:0.02;visibility:visible;pointer-events:none;background:white;overflow:visible;');

                // Wait for image inside compiled page to fully load & decode offscreen
                const img = wrapper.querySelector('img');
                if (img) {
                    await new Promise(resolve => {
                        if (img.complete && img.naturalWidth > 0) {
                            img.decode().then(resolve).catch(resolve);
                            return;
                        }
                        img.onload = () => {
                            img.decode().then(resolve).catch(resolve);
                        };
                        img.onerror = resolve;
                        setTimeout(resolve, 4000);
                    });
                }

                await new Promise(resolve => setTimeout(resolve, 200));

                const canvas = await html2canvas(wrapper, {
                    scale: 1.5, // 1.5 is extremely fast (2-3x speedup) and crisp!
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    logging: false,
                    windowWidth: 800,
                    windowHeight: 800,
                    scrollX: 0,
                    scrollY: 0
                });

                container.setAttribute('style', prevStyle);
                wrapper.innerHTML = '';

                return new Promise(resolve => {
                    canvas.toBlob(blob => resolve(blob), 'image/png');
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
                const showTitle = $('#share-show-title').is(':checked');
                const showPrice = $('#share-show-price').is(':checked');
                const showWatermark = $('#share-add-watermark').is(':checked');
                const showNote = $('#share-add-note').is(':checked');
                const noteText = $('#share-note-text').val() || '';
                const logoPos = $('#share-logo-pos').val() || 'bottom-right';

                let logoPosStyle = 'bottom: 150px; right: 30px;'; // Default BR
                if (logoPos === 'top-left') {
                    logoPosStyle = 'top: 30px; left: 30px;';
                } else if (logoPos === 'top-right') {
                    logoPosStyle = 'top: 30px; right: 30px;';
                } else if (logoPos === 'bottom-left') {
                    logoPosStyle = 'bottom: 150px; left: 30px;';
                }

                const companyLogo = window.companyLogoBase64 || '';
                const displayPrice = String(p.variant || 'On Request').includes('₹') ? String(p.variant || 'On Request') : '₹ ' + String(p.variant || 'On Request');

                return `
                <div style="width: 800px; height: 800px; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; position: relative; box-sizing: border-box; font-family: 'Outfit', 'Poppins', sans-serif;">
                    ${showWatermark && companyLogo ? `
                    <div style="position: absolute; ${logoPosStyle} width: 100px; height: 100px; z-index: 5; pointer-events: none; opacity: 0.7; display: flex; align-items: center; justify-content: center;">
                        <img src="${companyLogo}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>` : ''}

                    <!-- Product Image Centered -->
                    <div style="flex-grow: 1; display: flex; align-items: center; justify-content: center; background: #ffffff; padding: 15px; box-sizing: border-box; width: 100%; height: 580px;">
                        <img src="${imgUrl}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
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
                const price = String(value || 'On Request').trim();
                return price.includes('\u20B9') || price.toLowerCase().includes('request') ? price : '&#8377; ' + price;
            }

            function getImagePdfItems(validDataList) {
                const showGallery = $('#share-show-gallery').is(':checked');
                const items = [];

                validDataList.forEach(data => {
                    if (!data || !data.success) return;
                    items.push({
                        product: data.product,
                        imageUrl: data.thumbnail_url || '',
                        label: 'primary'
                    });

                    if (showGallery && Array.isArray(data.gallery_urls)) {
                        data.gallery_urls.forEach((url, index) => {
                            if (url) {
                                items.push({
                                    product: data.product,
                                    imageUrl: url,
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
                const imgUrl = item.imageUrl || '';
                const companyLogo = String(options.companyLogo || window.companyLogoBase64 || '').trim();
                const showTitle = $('#share-show-title').is(':checked');
                const showPrice = $('#share-show-price').is(':checked');
                const showWatermark = $('#share-add-watermark').is(':checked');
                const showNote = $('#share-add-note').is(':checked');
                const noteText = escapeHtml($('#share-note-text').val() || 'An Award For Every Achievement & Effort');
                const logoPos = $('#share-logo-pos').val() || 'top-right';
                const productName = escapeHtml(p.name || 'Product');
                const partCode = escapeHtml(p.part_code || '');
                const displayPrice = formatDisplayPrice(p.variant || 'On Request');
                const footerHeight = 104;
                const noteHeight = showNote ? 58 : 0;
                const imageAreaBottom = footerHeight + noteHeight;

                let logoPosStyle = 'top: 22px; right: 24px;';
                if (logoPos === 'top-left') {
                    logoPosStyle = 'top: 22px; left: 24px;';
                } else if (logoPos === 'bottom-left') {
                    logoPosStyle = `bottom: ${imageAreaBottom + 18}px; left: 24px;`;
                } else if (logoPos === 'bottom-right') {
                    logoPosStyle = `bottom: ${imageAreaBottom + 18}px; right: 24px;`;
                }

                return `
                <div style="box-sizing:border-box;width:800px;height:800px;background:#ffffff;position:relative;overflow:hidden;font-family:'Outfit','Poppins','Helvetica Neue',Arial,sans-serif;">
                    <div style="position:absolute;top:0;left:0;right:0;bottom:${imageAreaBottom}px;background:#ffffff;">
                        ${showTitle ? `
                        <div style="position:absolute;top:34px;left:44px;right:210px;z-index:4;color:#000000;">
                            <div style="font-size:23px;font-weight:900;line-height:1.12;letter-spacing:0;">${productName}</div>
                            ${p.short_description ? `<div style="font-size:13px;font-weight:700;line-height:1.2;margin-top:4px;">${escapeHtml(p.short_description)}</div>` : ''}
                        </div>` : ''}

                        ${showWatermark && companyLogo ? `
                        <div style="position:absolute;${logoPosStyle}width:90px;height:42px;z-index:5;display:flex;align-items:center;justify-content:center;">
                            <img src="${companyLogo}" style="max-width:100%;max-height:100%;object-fit:contain;display:block;">
                        </div>` : ''}

                        ${imgUrl
                            ? `<img src="${imgUrl}" style="position:absolute;left:26px;right:26px;top:${showTitle ? '95px' : '28px'};bottom:26px;width:748px;height:${showTitle ? '515px' : '582px'};object-fit:contain;display:block;">`
                            : `<div style="position:absolute;left:26px;right:26px;top:80px;bottom:40px;display:flex;align-items:center;justify-content:center;color:#94A3B8;font-weight:800;">No Image</div>`
                        }
                    </div>

                    ${showNote ? `
                    <div style="position:absolute;left:0;right:0;bottom:${footerHeight}px;height:${noteHeight}px;background:#FFD000;color:#000000;display:flex;align-items:center;justify-content:space-between;padding:0 48px;box-sizing:border-box;font-weight:900;">
                        <div style="font-size:17px;">CODE: ${partCode}</div>
                        <div style="font-size:17px;text-transform:uppercase;text-align:right;max-width:430px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${noteText}</div>
                    </div>` : ''}

                    <div style="position:absolute;left:0;right:0;bottom:0;height:${footerHeight}px;background:#000000;color:#ffffff;display:flex;align-items:center;justify-content:space-between;padding:0 48px;box-sizing:border-box;">
                        <div style="min-width:0;max-width:${showPrice ? '500px' : '680px'};">
                            <div style="font-size:14px;color:#FFD000;font-weight:900;text-transform:uppercase;margin-bottom:6px;">CODE: ${partCode}</div>
                            ${showTitle ? `<div style="font-size:25px;font-weight:900;line-height:1.1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${productName}</div>` : ''}
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                            ${showPrice ? `<div style="font-size:28px;font-weight:900;white-space:nowrap;">${displayPrice}</div>` : ''}
                            <div style="background:#ffffff;color:#000000;border-radius:999px;padding:7px 16px;font-size:13px;font-weight:900;text-transform:uppercase;letter-spacing:0;">Tap to view</div>
                        </div>
                    </div>
                </div>
                `;
            }

            function renderImagePdfPageHtml(items, options = {}) {
                const companyLogo = String(options.companyLogo || window.companyLogoBase64 || '').trim();
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
                        slotsHtml += `
                        <div style="width:218px;height:218px;position:relative;border-radius:8px;overflow:hidden;background:#ffffff;border:1.5px solid #E2E8F0;box-sizing:border-box;">
                            <div style="width:800px;height:800px;transform:scale(0.2725);transform-origin:top left;">
                                ${renderImagePdfBoxHtml(item, { companyLogo })}
                            </div>
                        </div>
                        `;
                    } else {
                        slotsHtml += `
                        <div style="width:218px;height:218px;border-radius:8px;background:#ffffff;border:1.5px dashed #E2E8F0;display:flex;align-items:center;justify-content:center;box-sizing:border-box;">
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
                                ? `<img src="${companyLogo}" style="max-width:112px;max-height:42px;object-fit:contain;display:block;">`
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

                    <div style="width:100%;background:#4F46E5;color:#ffffff;text-align:center;font-size:20px;font-weight:900;font-family:'Outfit',sans-serif;padding:19px;border-radius:8px;letter-spacing:0;display:flex;align-items:center;justify-content:center;gap:10px;box-sizing:border-box;text-transform:uppercase;">
                        PRESS TO OPEN &rarr;
                    </div>
                </div>
                `;
            }

            window.updateSelectionUI = function() {
                const count = selectedProducts.length;
                
                // Sync badge counters
                $('#cart-count, #mobile-cart-count, #selected-count, .selected-count-span').text(count);

                // Show or hide floating selection bar
                if (count > 0) {
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
            $('#catalogue-search').on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val().trim();
                
                if (query.length < 2) {
                    $('#search-results-pane').html(`
                        <div class="p-4 text-center text-secondary small">
                            Type keyword to search among high-end catalogue items.
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
                    $.get('{{ route("search") }}', { query: query }, function(response) {
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
                        $('#pdf-tab').click();
                        targetTab = 'pdf';
                    } else if (activeTabKey === 'image') {
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
                localStorage.setItem('selected_products', JSON.stringify(selectedProducts));
                updateSelectionUISafe();
                populateModalSelectionList();
                $('#sharingModal').modal('hide');
            };

            // Bind tab click events to lazily compile PDF previews on demand
            $(document).ready(function() {
                $('#pdf-tab').on('click', function() {
                    if (!window.renderedPreviews.details && selectedProducts.length > 0) {
                        generateLivePDFPreview('details');
                    }
                });

                $('#image-tab').on('click', function() {
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
                    // Hide the floating bar on the page
                    $('#selection-bar').removeClass('active');
                    return;
                }
                
                // Enable sharing buttons
                $('#pdf-share-btn-details, #pdf-download-btn-details, #pdf-direct-btn-details, #pdf-api-btn-details, #pdf-share-btn-images, #pdf-download-btn-images, #pdf-direct-btn-images, #pdf-api-btn-images').removeAttr('disabled').css({ 'opacity': '1', 'pointer-events': 'auto' });
                
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
                                const moq = p.part_code || 'MOQ: 100 pcs';
                                const price = p.variant || 'Price on Request';
                                const thumbnailUrl = data.thumbnail_url || '';
                                
                                html += `
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-white border rounded-4 mb-2" id="modal-selected-item-${p.id}" style="transition: all 0.25s ease;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="width: 55px; height: 55px; border-radius: 12px; background: #ffffff; border: 1.5px solid #F1F5F9; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                                ` + (thumbnailUrl 
                                                    ? `<img src="${thumbnailUrl}" style="max-width: 48px; max-height: 48px; object-fit: contain;">`
                                                    : `<div style="font-size: 0.7rem; color: #94A3B8;">No Image</div>`
                                                ) + `
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-1 text-dark text-truncate" style="max-width: 480px; font-size: 0.85rem;">${p.name}</h6>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="badge bg-light text-secondary border small-text" style="font-size: 0.65rem; font-weight: 600;">${moq}</span>
                                                    <span class="text-primary fw-bold" style="font-size: 0.8rem;">${price}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-premium-danger btn-sm rounded-circle d-flex align-items-center justify-content-center" onclick="toggleSelection('${p.id}'); populateModalSelectionList();" style="width: 36px; height: 36px; padding: 0; background: rgba(239, 68, 68, 0.08); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.15); transition: all 0.2s ease;">
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
                                    <p class="text-secondary small mb-3">The items in your selection are no longer available in the catalogue database. Please clear your selection to reset.</p>
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
                    message: 'All selected products will be removed from this catalogue draft.',
                    confirmText: 'Clear selection',
                    danger: true
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }
                    selectedProducts = [];
                    localStorage.setItem('selected_products', JSON.stringify(selectedProducts));
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
                                const moq = p.part_code || 'MOQ: 100';
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
                        catalog_title: title
                    },
                    success: function(response) {
                        $('#dt-status-log').removeClass('alert-info alert-danger').addClass('alert-success').html(`
                            <i class="bi bi-check-circle-fill me-2"></i> ${response.message}
                            <br><small class="d-block mt-1">Catalogue Code: <b>${response.code}</b></small>
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
                    callback(`${window.location.origin}/catalogue`);
                    return;
                }
                $.ajax({
                    url: '/api/doubletick/share',
                    type: 'POST',
                    data: {
                        phone: 'B2B Client', // placeholder label for automatic generic link shares
                        product_ids: selectedProducts,
                        catalog_title: catalogTitle,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success && res.url) {
                            callback(res.url);
                        } else {
                            callback(`${window.location.origin}/catalogue`);
                        }
                    },
                    error: function() {
                        callback(`${window.location.origin}/catalogue`);
                    }
                });
            }

            // WhatsApp structured PDF sharing triggers
            window.sharePDFOnWhatsApp = function(type) {
                const typeName = (type === 'details') ? 'B2B Specifications' : 'Image Share';
                const catalogTitle = $('#share-catalog-title').val() || 'Premium Selection';

                // Shift sharing button to loading state
                const btn = $(`#pdf-direct-btn-${type}`);
                const originalHtml = btn.html();
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Generating PDF...');

                // 1. First trigger the PDF generation and get blob
                generatePDFBlob(type).then(async (pdfData) => {
                    // Create a File object from the blob so it can be shared via Web Share API
                    const file = new File([pdfData.blob], pdfData.filename, { type: 'application/pdf' });
                    
                    // Check if Web Share API supports file sharing natively (mobile devices etc.)
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Sharing Document...');
                        navigator.share({
                            files: [file],
                            title: pdfData.filename,
                            text: `Please review our B2B Product Specifications Portfolio: ${catalogTitle}`
                        })
                        .then(() => {
                            btn.removeAttr('disabled').html(originalHtml);
                            trackAnalyticsEventSafe('whatsapp_share_pdf_native_success', type);
                        })
                        .catch((err) => {
                            console.log('Native document sharing cancelled or failed:', err);
                            btn.removeAttr('disabled').html(originalHtml);
                        });
                    } else {
                        // Desktop/fallback: collect WhatsApp number in the CATASKY modal system.
                        const phonePrompt = await window.alertService.promptText({
                            title: 'WhatsApp number',
                            message: "Enter customer's WhatsApp number with country code to send the PDF document.",
                            placeholder: '91xxxxxxxxxx'
                        });
                        const phone = phonePrompt.isConfirmed ? phonePrompt.value : '';
                        
                        if (phone && phone.trim() !== '') {
                            btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Uploading PDF...');
                            
                            const formData = new FormData();
                            formData.append('pdf', pdfData.blob, pdfData.filename);
                            formData.append('filename', pdfData.filename);
                            formData.append('_token', '{{ csrf_token() }}');
                            
                            $.ajax({
                                url: "{{ route('pdf.upload-temp') }}",
                                type: "POST",
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(response) {
                                    if (response.success && response.url) {
                                        const pdfUrlForApi = response.public_url || response.url;
                                        
                                        btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Dispatching WhatsApp PDF...');
                                        
                                        $.ajax({
                                            url: "/api/doubletick/share",
                                            type: "POST",
                                            data: {
                                                phone: phone,
                                                product_ids: selectedProducts,
                                                catalog_title: catalogTitle,
                                                pdf_url: pdfUrlForApi,
                                                send_type: 'pdf',
                                                _token: '{{ csrf_token() }}'
                                            },
                                            success: function(dtResponse) {
                                                btn.removeAttr('disabled').html(originalHtml);
                                                window.alertService.successAlert('PDF sent', `PDF file successfully sent to WhatsApp number: ${phone}`);
                                                trackAnalyticsEvent('whatsapp_share_pdf_api_success', type);
                                            },
                                            error: function(xhr) {
                                                btn.removeAttr('disabled').html(originalHtml);
                                                window.alertService.errorAlert("Failed to send PDF", xhr.responseJSON?.message || 'Error sending PDF via API');
                                            }
                                        });
                                    } else {
                                        btn.removeAttr('disabled').html(originalHtml);
                                        window.alertService.errorAlert("PDF upload failed", "The server could not store the PDF.");
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("PDF upload failed:", error);
                                    btn.removeAttr('disabled').html(originalHtml);
                                    window.alertService.errorAlert("Server upload error", "Server upload error occurred.");
                                }
                            });
                        } else {
                            // If they cancel the prompt, fall back to opening WhatsApp Web chat with standard links and download URL
                            btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Preparing chat link...');
                            
                            const formData = new FormData();
                            formData.append('pdf', pdfData.blob, pdfData.filename);
                            formData.append('filename', pdfData.filename);
                            formData.append('_token', '{{ csrf_token() }}');
                            
                            $.ajax({
                                url: "{{ route('pdf.upload-temp') }}",
                                type: "POST",
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(response) {
                                    if (response.success && response.url) {
                                        const downloadUrl = response.url;
                                        
                                        registerProposalAndGetTrackingUrl(catalogTitle, function(trackingUrl) {
                                            btn.removeAttr('disabled').html(originalHtml);

                                            let msg = `📘 *CATASKY SMART CATALOGUE*\n\n`;
                                            msg += `🔗 *Press to Open Catalogue:*\n`;
                                            msg += `${trackingUrl}\n\n`;
                                            msg += `📄 *Download PDF Brochure:*\n`;
                                            msg += `${downloadUrl}\n\n`;

                                            openWhatsAppChat(msg);
                                            trackAnalyticsEvent('whatsapp_share_pdf', type);
                                            
                                            window.alertService.infoAlert("Opening WhatsApp", "No WhatsApp number entered. Opening WhatsApp chat with tracking links, and downloaded the PDF for manual attachment.");
                                            pdfData.pdf.save(pdfData.filename);
                                        });
                                    } else {
                                        handleShareError(btn, originalHtml, "Invalid upload response");
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("PDF upload failed:", error);
                                    handleShareError(btn, originalHtml, "PDF Upload failed");
                                }
                            });
                        }
                    }
                }).catch((err) => {
                    console.error("PDF auto-generation failed during share:", err);
                    handleShareError(btn, originalHtml, "PDF Generation failed");
                });
            };

            function handleShareError(btn, originalHtml, reason) {
                btn.removeAttr('disabled').html(originalHtml);
                window.alertService.warningAlert("WhatsApp sharing failed", reason + ". Sharing only the interactive link as fallback.");
                // Fallback to link sharing only
                const catalogTitle = $('#share-catalog-title').val() || 'Premium Selection';
                registerProposalAndGetTrackingUrl(catalogTitle, function(trackingUrl) {
                    let msg = `📘 *CATASKY SMART CATALOGUE*\n\n`;
                    msg += `🔗 *Press to Open Catalogue:*\n`;
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
                    Compiling and building high-fidelity PDF specifications catalogue...
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
                const type = 'images';
                if (selectedProducts.length === 0) return;
                window.renderedPreviews.images = true;

                const badge = $('#pdf-preview-status-images');
                const loader = $('#pdf-preview-loader-images');
                const frame = $('#pdf-preview-frame-images');
                const downloadBtn = $('#pdf-download-btn-images');
                const shareBtn = $('#pdf-direct-btn-images');
                const shareSystemBtn = $('#pdf-share-btn-images');

                badge.removeClass('bg-success bg-danger text-dark text-white').addClass('bg-warning text-dark').text('Rendering...');
                loader.removeClass('d-none').addClass('d-flex');
                frame.removeClass('d-flex').addClass('d-none');
                downloadBtn.add(shareBtn).add(shareSystemBtn).attr('disabled', true).css({ opacity: '0.5', 'pointer-events': 'none' });

                fetchMultipleProductDetails(selectedProducts).then(() => {
                    return Promise.all(selectedProducts.map(id => fetchProductDetailsCached(id)));
                }).then(dataList => {
                    const validDataList = dataList.filter(d => d && d.success);
                    const imageItems = getImagePdfItems(validDataList);
                    const previewItems = imageItems.slice(0, 12);
                    const html = `
                        <div class="share-image-preview-grid">
                            ${previewItems.map(item => {
                                const slug = item.product && item.product.slug ? item.product.slug : '';
                                const url = slug ? `${window.location.origin}/product/${slug}` : `${window.location.origin}/catalogue?products=${selectedProducts.join(',')}`;
                                return `
                                    <a class="share-image-preview-card" href="${url}" target="_blank" style="width:176px;height:176px;">
                                        <div style="--preview-scale:0.22;">${renderImagePdfBoxHtml(item)}</div>
                                    </a>
                                `;
                            }).join('')}
                        </div>
                    `;
                    $('#pdf-preview-html-images').html(html);
                    $('#pdf-preview-page-images').css({ width: '100%', height: 'auto', minHeight: '460px', transform: 'none', boxShadow: 'none', background: '#f8fafc' });
                    $('#pdf-preview-scale-wrap-images').css({ height: 'auto', display: 'block', overflow: 'auto' });
                    loader.removeClass('d-flex').addClass('d-none');
                    frame.removeClass('d-none').addClass('d-flex');
                    badge.removeClass('bg-warning bg-danger text-dark text-white').addClass('bg-success text-white').text('Ready');
                    downloadBtn.add(shareBtn).add(shareSystemBtn).removeAttr('disabled').css({ opacity: '1', 'pointer-events': 'auto' });
                }).catch(err => {
                    console.error("Error generating image preview", err);
                    badge.removeClass('bg-warning bg-success text-dark text-white').addClass('bg-danger text-white').text('Load Error');
                    loader.removeClass('d-flex').addClass('d-none');
                });
            };

            async function buildShareImageFiles() {
                if (!selectedProducts || selectedProducts.length === 0) {
                    throw new Error('Please select at least one product first.');
                }
                await fetchMultipleProductDetails(selectedProducts);
                const dataList = await Promise.all(selectedProducts.map(id => fetchProductDetailsCached(id)));
                const imageItems = getImagePdfItems(dataList.filter(d => d && d.success));
                const files = [];

                for (let i = 0; i < imageItems.length; i++) {
                    const item = imageItems[i];
                    const p = item.product || {};
                    const blob = await captureCardAsBlob(p, item.imageUrl || '');
                    const filename = `${String(p.slug || p.id || 'product').replace(/[^a-z0-9_-]+/gi, '_')}_${item.label || i + 1}.png`;
                    files.push(new File([blob], filename, { type: 'image/png' }));
                }
                return files;
            }

            window.shareImageSystem = async function() {
                const btn = $('#pdf-share-btn-images');
                const originalHtml = btn.html();
                try {
                    btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Generating images...');
                    const files = await buildShareImageFiles();
                    btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Opening share sheet...');
                    const title = $('#share-catalog-title').val() || 'Premium Selection';
                    const catalogueUrl = `${window.location.origin}/catalogue?products=${selectedProducts.join(',')}`;
                    if (navigator.canShare && navigator.canShare({ files })) {
                        await navigator.share({
                            files,
                            title,
                            text: `${title}\nOpen catalogue: ${catalogueUrl}`
                        });
                        trackAnalyticsEventSafe('system_share_images_success', files.length);
                    } else {
                        for (const file of files) {
                            const link = document.createElement('a');
                            link.download = file.name;
                            link.href = URL.createObjectURL(file);
                            link.click();
                            setTimeout(() => URL.revokeObjectURL(link.href), 1500);
                        }
                        window.alertService.infoAlert('Images downloaded', 'This browser does not support direct image file sharing, so the generated images were downloaded.');
                    }
                } catch (err) {
                    console.error('Image share failed:', err);
                    window.alertService.errorAlert('Image share failed', err.message || err);
                } finally {
                    btn.removeAttr('disabled').html(originalHtml);
                }
            };

            window.generateLivePDFPreview = function(type = 'details') {
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
                const downloadBtn = $(`#pdf-download-btn-${type}`);
                const shareBtn = $(`#pdf-direct-btn-${type}`);
                const shareSystemBtn = $(`#pdf-share-btn-${type}`);

                // Shift previews into loading mode
                badge.removeClass('bg-success bg-danger text-dark text-white').addClass('bg-warning text-dark').text('Compiling...');
                loader.removeClass('d-none').addClass('d-flex');
                frame.removeClass('d-block').addClass('d-none');
                
                // Disable exporter triggers
                downloadBtn.attr('disabled', true).css({ 'opacity': '0.5', 'pointer-events': 'none' });
                shareBtn.attr('disabled', true).css({ 'opacity': '0.5', 'pointer-events': 'none' });
                shareSystemBtn.attr('disabled', true).css({ 'opacity': '0.5', 'pointer-events': 'none' });

                const companyName = "CataSky";
                const companyPhone = "{{ $settings->phone ?? '+91 919871376205' }}";
                const companyLogo = "@if($settings && $settings->logo && !empty($logoBase64)){{ $logoBase64 }}@else @endif";

                const catalogTitle = $('#share-catalog-title').val() || 'Premium Selection';

                const today = new Date();
                const dateStr = String(today.getDate()).padStart(2, '0') + '-' + today.toLocaleString('default', { month: 'short' }) + '-' + today.getFullYear();

                fetchMultipleProductDetails(selectedProducts).then(() => {
                    let promises = selectedProducts.map(id => fetchProductDetailsCached(id));

                    Promise.all(promises).then(dataList => {
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
                        `;

                        let previewPageHtml = '';

                        if (type === 'details') {
                            // Generate the first page of the product table (exactly items 1 to 5)
                            const chunk = validDataList.slice(0, 5);
                            let rowsHtml = '';
                            chunk.forEach((data, index) => {
                                const p = data.product;
                                const name = p.name || 'Product Model';
                                const priceVal = p.variant || 'Price on Request';
                                const imgUrl = data.thumbnail_url || '';
                                const serialNo = index + 1;
                                const brandName = escapeHtml((p.brand && p.brand.name) ? p.brand.name : 'CATASKY');
                                const categoryName = escapeHtml((p.category && p.category.name) ? p.category.name : 'Catalogue');
                                const description = escapeHtml(p.short_description || p.specifications || p.additional_info || 'Detailed product specifications available on request.');
                                const galleryHtml = (data.gallery_urls || []).slice(0, 3).map(url => `<img src="${url}" style="width:38px;height:38px;object-fit:contain;border:1px solid #E2E8F0;border-radius:6px;margin-right:5px;">`).join('');
                                const displayPrice = String(priceVal).includes('₹') ? priceVal : '₹ ' + priceVal;

                                rowsHtml += `
                                <tr style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; color: #000000; height: 130px;">
                                    <td style="padding: 10px; border-left: 1.5px solid #000000; border-right: 1.5px solid #000000; border-bottom: 1.5px solid #E2E8F0; text-align: center; font-weight: bold; vertical-align: middle;">
                                        ${serialNo}
                                    </td>
                                    <td style="padding: 10px; border-right: 1.5px solid #000000; border-bottom: 1.5px solid #E2E8F0; text-align: center; vertical-align: middle;">
                                        ${imgUrl 
                                            ? `<img src="${imgUrl}" style="max-height: 100px; max-width: 120px; object-fit: contain; display: inline-block;">`
                                            : `<div style="font-size: 0.75rem; color: #94A3B8;">No Image</div>`
                                        }
                                    </td>
                                    <td style="padding: 10px 15px; border-right: 1.5px solid #000000; border-bottom: 1.5px solid #E2E8F0; font-weight: 700; font-size: 0.85rem; text-align: left; vertical-align: middle;">
                                        <div style="font-size:0.92rem;font-weight:900;margin-bottom:6px;">${name}</div>
                                        <div style="font-size:0.7rem;color:#475569;line-height:1.35;font-weight:600;">${description}</div>
                                        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;font-size:0.66rem;font-weight:800;color:#334155;">
                                            <span style="background:#F1F5F9;border-radius:999px;padding:3px 8px;">Brand: ${brandName}</span>
                                            <span style="background:#F1F5F9;border-radius:999px;padding:3px 8px;">Category: ${categoryName}</span>
                                        </div>
                                        ${galleryHtml ? `<div style="margin-top:8px;">${galleryHtml}</div>` : ''}
                                    </td>
                                    <td style="padding: 8px 12px; border-right: 1.5px solid #000000; border-bottom: 1.5px solid #E2E8F0; font-weight: 800; font-size: 0.82rem; text-align: left; vertical-align: middle; line-height: 1.5; word-break: break-word;">
                                        ${displayPrice}
                                    </td>
                                </tr>
                                `;
                            });

                            const totalPages = Math.ceil(validDataList.length / 5);

                            previewPageHtml = `
                            <div class="pdf-page" style="${pageStyle}">
                                <!-- Logo Only Header -->
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; margin-bottom: 15px; font-family: 'Outfit', sans-serif;">
                                    <div style="display: flex; align-items: center;">
                                        ${companyLogo && companyLogo.trim().length > 0 
                                            ? `<img src="${companyLogo}" style="max-height: 44px; object-fit: contain;">`
                                            : `<div style="width: 40px; height: 40px; border-radius: 8px; background: #1D6FEB; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.1rem;">C</div>`
                                        }
                                    </div>
                                    <div style="font-size: 0.85rem; color: #333333; font-weight: bold;">
                                        Date: ${dateStr}
                                    </div>
                                </div>

                                <!-- Catalogue Title -->
                                <div style="text-align: center; font-size: 1.6rem; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; font-family: 'Outfit', sans-serif;">
                                    ${catalogTitle}
                                </div>

                                <!-- Simple & Clean Table -->
                                <div style="flex-grow: 1; width: 100%;">
                                    <table style="width: 100%; border-collapse: collapse; box-sizing: border-box;">
                                        <thead>
                                            <tr style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: bold; text-align: left; height: 35px; border-top: 2.5px solid #000000; border-bottom: 2.5px solid #000000;">
                                                <th style="padding: 10px; width: 60px; border-left: 1.5px solid #000000; border-right: 1.5px solid #000000; text-align: center;">No.</th>
                                                <th style="padding: 10px; width: 150px; border-right: 1.5px solid #000000; text-align: left;">Product</th>
                                                <th style="padding: 10px; border-right: 1.5px solid #000000; text-align: left;">Detail</th>
                                                <th style="padding: 10px; width: 130px; border-right: 1.5px solid #000000; text-align: left;">Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${rowsHtml}
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Simple Footer -->
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1.5px solid #000000; padding-top: 15px; font-size: 0.78rem; color: #555555; font-family: 'Outfit', sans-serif; margin-top: auto; padding-bottom: 5px;">
                                    <div style="font-weight: bold;">{{ $settings->phone ?? '+91 919871376205' }} &bull; ${$('#share-add-note').is(':checked') ? escapeHtml($('#share-note-text').val() || 'Custom catalogue notes included') : 'Secure B2B Portfolio'}</div>
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

                        // Update badge to ready
                        badge.removeClass('bg-warning bg-danger text-dark text-white').addClass('bg-success text-white').text('Ready');

                        // Enable exporter buttons!
                        downloadBtn.removeAttr('disabled').css({ 'opacity': '1', 'pointer-events': 'auto' });
                        shareBtn.removeAttr('disabled').css({ 'opacity': '1', 'pointer-events': 'auto' });
                        shareSystemBtn.removeAttr('disabled').css({ 'opacity': '1', 'pointer-events': 'auto' });
                    }).catch(err => {
                        console.error("Error generating PDF preview", err);
                        badge.removeClass('bg-warning bg-success text-dark text-white').addClass('bg-danger text-white').text('Load Error');
                        loader.removeClass('d-flex').addClass('d-none');
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
                                <span style="color: #6366F1; font-weight: 900; font-family:'Outfit', sans-serif;">CATASKY</span> Smart Catalogue
                            </div>
                        </div>
                    </div>
                </div>
                `;

                // Image sharing is handled as direct PNG files, not as PDF pages.

                // ── 2. PRODUCT DETAILS OR IMAGE GRID PAGES ──────────────────
                if (type === 'details') {
                    // Split products into pages (exactly 5 per page for A4 fit)
                    const productChunks = chunkArray(validDataList, 5);
                    const totalProductPages = productChunks.length;

                    productChunks.forEach((chunk, pageIndex) => {
                        let rowsHtml = '';
                        chunk.forEach((data, index) => {
                            const p = data.product;
                            const name = p.name || 'Product Model';
                            const priceVal = p.variant || 'Price on Request';
                            const imgUrl = data.thumbnail_url || '';
                            const serialNo = (pageIndex * 5) + index + 1;
                            const brandName = escapeHtml((p.brand && p.brand.name) ? p.brand.name : 'CATASKY');
                            const categoryName = escapeHtml((p.category && p.category.name) ? p.category.name : 'Catalogue');
                            const description = escapeHtml(p.short_description || p.specifications || p.additional_info || 'Detailed product specifications available on request.');
                            const galleryHtml = (data.gallery_urls || []).slice(0, 3).map(url => `<img src="${url}" style="width:38px;height:38px;object-fit:contain;border:1px solid #E2E8F0;border-radius:6px;margin-right:5px;">`).join('');
                            const displayPrice = priceVal.includes('₹') ? priceVal : '₹ ' + priceVal;

                            rowsHtml += `
                            <tr style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; color: #000000; height: 130px;">
                                <td style="padding: 10px; border-left: 1.5px solid #000000; border-right: 1.5px solid #000000; border-bottom: 1.5px solid #E2E8F0; text-align: center; font-weight: bold; vertical-align: middle;">
                                    ${serialNo}
                                </td>
                                <td style="padding: 10px; border-right: 1.5px solid #000000; border-bottom: 1.5px solid #E2E8F0; text-align: center; vertical-align: middle;">
                                    ${imgUrl 
                                        ? `<img src="${imgUrl}" style="max-height: 100px; max-width: 120px; object-fit: contain; display: inline-block;">`
                                        : `<div style="font-size: 0.75rem; color: #94A3B8;">No Image</div>`
                                    }
                                </td>
                                <td style="padding: 10px 15px; border-right: 1.5px solid #000000; border-bottom: 1.5px solid #E2E8F0; font-weight: 700; font-size: 0.85rem; text-align: left; vertical-align: middle;">
                                    <div style="font-size:0.92rem;font-weight:900;margin-bottom:6px;">${name}</div>
                                    <div style="font-size:0.7rem;color:#475569;line-height:1.35;font-weight:600;">${description}</div>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;font-size:0.66rem;font-weight:800;color:#334155;">
                                        <span style="background:#F1F5F9;border-radius:999px;padding:3px 8px;">Brand: ${brandName}</span>
                                        <span style="background:#F1F5F9;border-radius:999px;padding:3px 8px;">Category: ${categoryName}</span>
                                    </div>
                                    ${galleryHtml ? `<div style="margin-top:8px;">${galleryHtml}</div>` : ''}
                                </td>
                                <td style="padding: 8px 12px; border-right: 1.5px solid #000000; border-bottom: 1.5px solid #E2E8F0; font-weight: 800; font-size: 0.82rem; text-align: left; vertical-align: middle; line-height: 1.5; word-break: break-word;">
                                    ${displayPrice}
                                </td>
                            </tr>
                            `;
                        });

                        pagesHtml += `
                        <div class="pdf-page" style="${pageStyle}">
                            <!-- Logo Only Header -->
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; margin-bottom: 15px; font-family: 'Outfit', sans-serif;">
                                <div style="display: flex; align-items: center;">
                                    ${companyLogo && companyLogo.trim().length > 0 
                                        ? `<img src="${companyLogo}" style="max-height: 44px; object-fit: contain;">`
                                        : `<div style="width: 40px; height: 40px; border-radius: 8px; background: #1D6FEB; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.1rem;">C</div>`
                                    }
                                </div>
                                <div style="font-size: 0.85rem; color: #333333; font-weight: bold;">
                                    Date: ${dateStr}
                                </div>
                            </div>

                            <!-- Catalogue Title -->
                            <div style="text-align: center; font-size: 1.6rem; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; font-family: 'Outfit', sans-serif;">
                                ${catalogTitle}
                            </div>

                            <!-- Simple & Clean Table -->
                            <div style="flex-grow: 1; width: 100%;">
                                <table style="width: 100%; border-collapse: collapse; box-sizing: border-box;">
                                    <thead>
                                        <tr style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: bold; text-align: left; height: 35px; border-top: 2.5px solid #000000; border-bottom: 2.5px solid #000000;">
                                            <th style="padding: 10px; width: 60px; border-left: 1.5px solid #000000; border-right: 1.5px solid #000000; text-align: center;">No.</th>
                                            <th style="padding: 10px; width: 150px; border-right: 1.5px solid #000000; text-align: left;">Product</th>
                                            <th style="padding: 10px; border-right: 1.5px solid #000000; text-align: left;">Detail</th>
                                            <th style="padding: 10px; width: 130px; border-right: 1.5px solid #000000; text-align: left;">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${rowsHtml}
                                    </tbody>
                                </table>
                            </div>

                            <!-- Simple Footer -->
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1.5px solid #000000; padding-top: 15px; font-size: 0.78rem; color: #555555; font-family: 'Outfit', sans-serif; margin-top: auto; padding-bottom: 5px;">
                                <div style="font-weight: bold;">{{ $settings->phone ?? '+91 919871376205' }} &bull; ${$('#share-add-note').is(':checked') ? escapeHtml($('#share-note-text').val() || 'Custom catalogue notes included') : 'Secure B2B Portfolio'}</div>
                                <div style="font-weight: bold;">Page ${pageIndex + 1} of ${totalProductPages}</div>
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
                const pageElements = wrapper.querySelectorAll('.pdf-page');
                const pageCanvases = new Array(pageElements.length);

                try {
                    // Process in parallel batches of 4 pages to balance memory and speed
                    const batchSize = 4;
                    for (let i = 0; i < pageElements.length; i += batchSize) {
                        const batch = [];
                        for (let j = i; j < Math.min(i + batchSize, pageElements.length); j++) {
                            const pageEl = pageElements[j];
                            const pageIdx = j;
                            
                            // Dynamic B2B SaaS progress updates!
                            if (typeof updateExporterProgress === 'function') {
                                updateExporterProgress(type, `Compiling Page ${pageIdx + 1} of ${pageElements.length}...`);
                            }

                            batch.push(
                                html2canvas(pageEl, {
                                    scale:           1.5, // 1.5 is extremely fast (2-3x speedup) and crisp!
                                    useCORS:         true,
                                    allowTaint:      true,
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

                const targetUrl = window.location.origin + '/catalogue?products=' + selectedProducts.join(',');

                for (let i = 0; i < pageCanvases.length; i++) {
                    if (i > 0) pdf.addPage();
                    const canvas = pageCanvases[i];
                    // Save as high-quality JPEG to keep sizes small and compile fast
                    const imgData = canvas.toDataURL('image/jpeg', 0.9);
                    // Render exactly across full page A4 (210mm x 297mm) with zero margin
                    pdf.addImage(imgData, 'JPEG', 0, 0, 210, 297);

                    if (type !== 'details') {
                        const chunk = imageChunks[i];
                        if (chunk && chunk.length) {
                            chunk.forEach((item, j) => {
                                const col = j % 3;
                                const row = Math.floor(j / 3);
                                const x = 14.1 + col * 62.0;
                                const y = 37.2 + row * 62.2;
                                if (item.product && item.product.slug) {
                                    const productUrl = window.location.origin + '/product/' + item.product.slug;
                                    pdf.link(x, y, 58.0, 58.0, { url: productUrl });
                                }
                            });
                        }
                        pdf.link(13, 269, 184, 16, { url: targetUrl });
                    }
                }

                const filename = catalogTitle.toLowerCase().replace(/[^a-z0-9]+/g, '_') + '_' + type + '.pdf';
                return { pdf, filename };
            }
// ── Download PDF (triggers browser save-as dialog) ────────────
            window.generatePDFCatalogue = function(type = 'details') {
                if (!selectedProducts || selectedProducts.length === 0) {
                    window.alertService.warningAlert('No products selected', 'Please select at least one product first.');
                    return;
                }

                const btn = $(`#pdf-download-btn-${type}`);
                const origHtml = btn.html();
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Building PDF...');

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
            window.sharePDFSystem = function(type) {
                const btn = $(`#pdf-share-btn-${type}`);
                const originalHtml = btn.html();
                
                // Show loading spinner
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Generating PDF for sharing...');
                
                generatePDFBlob(type).then((pdfData) => {
                    btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Opening Share Sheet...');
                    
                    // Create a File object from the blob so it can be shared via Web Share API
                    const file = new File([pdfData.blob], pdfData.filename, { type: 'application/pdf' });
                    
                    // Check if Web Share API and file sharing is supported
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        navigator.share({
                            files: [file]
                        })
                        .then(() => {
                            btn.removeAttr('disabled').html(originalHtml);
                            trackAnalyticsEventSafe('system_share_pdf_success', selectedProducts.length);
                        })
                        .catch((err) => {
                            console.log('Native sharing cancelled or failed:', err);
                            btn.removeAttr('disabled').html(originalHtml);
                        });
                    } else {
                        btn.removeAttr('disabled').html(originalHtml);
                        // Fallback: If sharing files is not supported (like on desktop), trigger download and tell user
                        window.alertService.infoAlert('PDF downloaded', 'Your browser or device does not support direct file sharing sheets. We have downloaded the PDF file for you instead.');
                        pdfData.pdf.save(pdfData.filename);
                    }
                }).catch((err) => {
                    console.error("PDF generation failed for system share:", err);
                    btn.removeAttr('disabled').html(originalHtml);
                    window.alertService.errorAlert("Failed to generate PDF", err.message || err);
                });
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
                selectedProducts = JSON.parse(localStorage.getItem('selected_products')) || [];
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

    @stack('scripts')
    <script>
        $(document).ready(function() {
            // Trigger floating toasts automatically on load if session variables exist
            $('.toast').each(function() {
                var toast = new bootstrap.Toast(this);
                toast.show();
            });
        });
    </script>
</body>
</html>
