@extends('layouts.frontend')

@section('title', 'Catasky — A Smarter Way to Share Products | Select. Share. Sell Faster.')

@section('content')

@php
$categoryImages = [
    'apparel'        => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=600&q=80',
    'tech'           => 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&w=600&q=80',
    'drinkware'      => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80',
    'awards'         => 'https://images.unsplash.com/photo-1578269174936-2709b5a8c0e6?auto=format&fit=crop&w=600&q=80',
    'bags'           => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80',
    'office'         => 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=600&q=80',
    'lifestyle'      => 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=600&q=80',
    'partner-brands' => 'https://images.unsplash.com/photo-1560179707-f14e90ef3623?auto=format&fit=crop&w=600&q=80',
];

$demoProducts = [
    [
        'name'     => 'Premium Branded Drinkware',
        'desc'     => 'Stainless steel insulated bottle with custom logo engraving',
        'mrp'      => '₹ 850',
        'price'    => '₹ 549',
        'discount' => '35% OFF',
        'img'      => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=300&q=80',
        'sku'      => 'DRK-001',
    ],
    [
        'name'     => 'Executive Leather Bag',
        'desc'     => 'Full-grain leather laptop bag with branded stitching & hardware',
        'mrp'      => '₹ 3,200',
        'price'    => '₹ 1,999',
        'discount' => '38% OFF',
        'img'      => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=300&q=80',
        'sku'      => 'BAG-042',
    ],
    [
        'name'     => 'Custom Award Trophy',
        'desc'     => 'Crystal glass trophy with UV laser engraving on base plate',
        'mrp'      => '₹ 1,400',
        'price'    => '₹ 899',
        'discount' => '36% OFF',
        'img'      => 'https://images.unsplash.com/photo-1578269174936-2709b5a8c0e6?auto=format&fit=crop&w=300&q=80',
        'sku'      => 'AWD-018',
    ],
    [
        'name'     => 'Smart Wireless Earbuds',
        'desc'     => 'True wireless earbuds with 24h battery & custom brand printing',
        'mrp'      => '₹ 2,500',
        'price'    => '₹ 1,499',
        'discount' => '40% OFF',
        'img'      => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=300&q=80',
        'sku'      => 'TEC-089',
    ],
];
@endphp

{{-- ================================================================
     HERO SECTION
     ================================================================ --}}
<section class="saas-hero" id="hero">
    <div class="container">
        <div class="row align-items-center g-5">

            {{-- Left: Text --}}
            <div class="col-lg-6 animate-fade-in" style="position:relative; z-index:2;">
                <div class="hero-badge">
                    <span class="badge-dot"></span>
                    B2B Sales Conversion Platform
                </div>

                <h1>
                    A Smarter Way to<br>
                    <span class="text-gradient">Share Products</span>
                </h1>

                <p class="hero-sub" style="font-size:1.25rem; font-weight:600; color:#1D6FEB; margin-bottom:8px;">
                    Select. Share. Sell Faster.
                </p>
                <p class="hero-sub">
                    Transform the way your business presents products to clients with a modern, mobile-first platform built for faster sales conversion. Catasky helps businesses instantly create, customize, and share product selections through beautifully organized digital experiences — without messy PDFs, endless WhatsApp messages, or outdated ERP-style systems.
                </p>

                <div class="hero-cta-group">
                    <a href="{{ route('subscriber.register') }}" class="btn-hero-primary" id="start-free-btn">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                        Start Free Trial
                    </a>
                    <a href="{{ route('demo') }}" class="btn-hero-outline" id="watch-demo-btn">
                        <i class="bi bi-search text-primary"></i>
                        Explore Catalogue
                    </a>
                </div>

                {{-- Social proof --}}
                <div class="d-flex align-items-center gap-3 mt-4 pt-2">
                    <div class="d-flex">
                        @foreach(['#1D6FEB','#0284C7','#06B6D4','#10B981','#F59E0B'] as $c)
                        <div style="width:30px;height:30px;border-radius:50%;background:{{$c}};border:2px solid white;margin-left:-8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:0.7rem;">
                            {{ ['R','A','S','M','K'][array_search($c, ['#1D6FEB','#0284C7','#06B6D4','#10B981','#F59E0B'])] }}
                        </div>
                        @endforeach
                    </div>
                    <div>
                        <div class="d-flex gap-1" style="color:#F59E0B; font-size:0.75rem;">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <div style="font-size:0.75rem; color:#64748B; margin-top:1px;"><strong style="color:#1E293B;">2,400+</strong> B2B sales teams trust Catasky</div>
                    </div>
                </div>
            </div>

            {{-- Right: Dashboard Mockup --}}
            <div class="col-lg-6 animate-fade-in">
                <div class="hero-mockup-wrap">

                    {{-- Float: Analytics --}}
                    <div class="hero-float-widget w-top-left">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:10px;background:rgba(29,111,235,0.1);display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-eye-fill text-primary fs-5"></i>
                            </div>
                            <div>
                                <div style="font-size:0.85rem;font-weight:800;color:#1E293B;">+142% Views</div>
                                <div style="font-size:0.7rem;color:#94A3B8;">Catalogue Analytics</div>
                            </div>
                        </div>
                    </div>

                    {{-- Float: WhatsApp --}}
                    <div class="hero-float-widget w-bottom-right">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:10px;background:rgba(37,211,102,0.1);display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-whatsapp fs-5" style="color:#25D366;"></i>
                            </div>
                            <div>
                                <div style="font-size:0.8rem;font-weight:700;color:#1E293B;">Shared on WhatsApp</div>
                                <div style="font-size:0.68rem;color:#94A3B8;">Just now · 8 products</div>
                            </div>
                        </div>
                    </div>

                    {{-- Float: PDF badge --}}
                    <div class="hero-float-widget w-middle-right" style="min-width:unset; padding:10px 14px;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                            <span style="font-size:0.78rem;font-weight:700;color:#1E293B;">PDF Ready</span>
                        </div>
                    </div>

                    {{-- Main Card --}}
                    <div class="hero-mockup-card">
                        <div class="mockup-topbar">
                            <span class="mockup-dot red"></span>
                            <span class="mockup-dot yellow"></span>
                            <span class="mockup-dot green"></span>
                            <div class="mockup-title-bar"></div>
                            <span style="font-size:0.7rem;color:#94A3B8;font-weight:600;">Catasky — Share Smarter</span>
                        </div>
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div style="font-size:0.75rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.5px;">Your Catalogue</div>
                                    <div style="font-size:1rem;font-weight:800;color:#1E293B;">B2B Summer Digest 2026</div>
                                </div>
                                <span class="badge" style="background:rgba(16,185,129,0.1);color:#10B981;font-size:0.72rem;padding:5px 12px;border-radius:100px;font-weight:700;">Active</span>
                            </div>

                            <div class="row g-2 mb-3">
                                @foreach([
                                    ['img'=>'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=100&q=80','name'=>'Drinkware','price'=>'₹549'],
                                    ['img'=>'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=100&q=80','name'=>'Exec Bag','price'=>'₹1,999'],
                                    ['img'=>'https://images.unsplash.com/photo-1578269174936-2709b5a8c0e6?auto=format&fit=crop&w=100&q=80','name'=>'Trophy','price'=>'₹899'],
                                    ['img'=>'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=100&q=80','name'=>'Earbuds','price'=>'₹1,499'],
                                ] as $mp)
                                <div class="col-3">
                                    <div style="background:#F8FAFC;border-radius:12px;padding:8px;border:1px solid #F1F5F9;">
                                        <img src="{{ $mp['img'] }}" style="width:100%;aspect-ratio:1;object-fit:contain;border-radius:8px;margin-bottom:4px;" loading="lazy">
                                        <div style="font-size:0.6rem;font-weight:700;color:#1E293B;line-height:1.2;">{{ $mp['name'] }}</div>
                                        <div style="font-size:0.65rem;font-weight:800;color:#1D6FEB;">{{ $mp['price'] }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="d-flex gap-2">
                                <div style="flex:1;background:linear-gradient(135deg,#1D6FEB,#0284C7);color:white;border-radius:10px;padding:8px;text-align:center;font-size:0.72rem;font-weight:700;">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
                                </div>
                                <div style="flex:1;background:rgba(37,211,102,0.1);color:#25D366;border-radius:10px;padding:8px;text-align:center;font-size:0.72rem;font-weight:700;border:1px solid rgba(37,211,102,0.2);">
                                    <i class="bi bi-whatsapp"></i> WhatsApp
                                </div>
                                <div style="flex:1;background:#F1F5F9;color:#64748B;border-radius:10px;padding:8px;text-align:center;font-size:0.72rem;font-weight:700;">
                                    <i class="bi bi-link-45deg"></i> Share Link
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ================================================================
     STATS STRIP
     ================================================================ --}}
<section class="trust-strip">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-0">
            <div class="trust-stat animate-fade-in">
                <div class="trust-stat-num" data-target="2400">2,400+</div>
                <div class="trust-stat-label">B2B Teams</div>
            </div>
            <div class="trust-stat animate-fade-in">
                <div class="trust-stat-num" data-target="85000">85K+</div>
                <div class="trust-stat-label">Catalogues Shared</div>
            </div>
            <div class="trust-stat animate-fade-in">
                <div class="trust-stat-num" data-target="320">320K+</div>
                <div class="trust-stat-label">PDFs Exported</div>
            </div>
            <div class="trust-stat animate-fade-in">
                <div class="trust-stat-num">4.9★</div>
                <div class="trust-stat-label">Average Rating</div>
            </div>
            <div class="trust-stat animate-fade-in">
                <div class="trust-stat-num">99.9%</div>
                <div class="trust-stat-label">Uptime SLA</div>
            </div>
        </div>
    </div>
</section>

{{-- ================================================================
     SHORT INTRO: PRODUCT SHARING REINVENTED
     ================================================================ --}}
<section class="home-section home-section-soft" id="about">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 animate-fade-in">
                <div class="section-tag">What is Catasky?</div>
                <h2 class="section-heading">Product Sharing,<br><span class="text-gradient">Reinvented</span></h2>
                <p class="section-sub" style="text-align:left;">Catasky is not just another catalogue platform. It is a modern <strong>sales conversion tool</strong> designed to help businesses:</p>
                <div class="d-flex flex-column gap-3 mt-4">
                    @foreach([
                        ['icon'=>'bi-stars','color'=>'#1D6FEB','text'=>'Showcase products beautifully'],
                        ['icon'=>'bi-send-fill','color'=>'#10B981','text'=>'Share options instantly'],
                        ['icon'=>'bi-chat-dots-fill','color'=>'#F59E0B','text'=>'Simplify customer communication'],
                        ['icon'=>'bi-lightning-charge-fill','color'=>'#0284C7','text'=>'Close deals faster'],
                    ] as $pt)
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:38px;height:38px;border-radius:10px;background:rgba(29,111,235,0.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi {{ $pt['icon'] }}" style="color:{{ $pt['color'] }};font-size:1rem;"></i>
                        </div>
                        <span style="font-size:0.95rem;font-weight:600;color:#1E293B;">{{ $pt['text'] }}</span>
                    </div>
                    @endforeach
                </div>
                <p class="mt-4" style="font-size:0.9rem;color:#64748B;line-height:1.7;">Unlike cluttered lead-generation marketplaces, Catasky focuses on one thing: <strong style="color:#1D6FEB;">helping sales teams convert faster.</strong></p>
            </div>
            <div class="col-lg-7 animate-fade-in">
                <div class="pdf-catalogue-grid">
                    @foreach($demoProducts as $dp)
                    <div class="pdf-product-card">
                        <span class="pdf-watermark-tag">CATASKY</span>
                        <div class="pdf-product-img">
                            <img src="{{ $dp['img'] }}" alt="{{ $dp['name'] }}" loading="lazy">
                        </div>
                        <div class="pdf-product-info">
                            <div style="font-size:0.6rem;color:#94A3B8;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">{{ $dp['sku'] }}</div>
                            <div class="pdf-product-name">{{ $dp['name'] }}</div>
                            <div class="pdf-product-desc">{{ $dp['desc'] }}</div>
                            <div class="pdf-product-price-row">
                                <div class="pdf-mrp">MRP {{ $dp['mrp'] }}</div>
                                <div class="d-flex align-items-center">
                                    <span class="pdf-offer-price">{{ $dp['price'] }}</span>
                                    <span class="pdf-discount-badge">{{ $dp['discount'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ================================================================
     PROBLEM vs SOLUTION
     ================================================================ --}}
<section class="home-section bg-white">
    <div class="container">
        <div class="text-center mb-5 animate-fade-in">
            <div class="section-tag">Problem → Solution</div>
            <h2 class="section-heading">Still Sharing Products <span class="text-gradient">the Old Way?</span></h2>
            <p class="section-sub">Traditional product sharing is slow, messy, and inefficient.</p>
        </div>
        <div class="row g-4">
            {{-- Problem --}}
            <div class="col-md-6 animate-fade-in">
                <div class="premium-card h-100" style="border-left: 4px solid #EF4444; padding: 28px;">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-x-circle-fill" style="color:#EF4444;"></i>
                        </div>
                        <h5 class="mb-0 fw-bold" style="color:#EF4444;">Old Way — Painful</h5>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        @foreach([
                            'Large PDF catalogues nobody reads',
                            'Manual product selection every time',
                            'Endless WhatsApp forwarding',
                            'Repeated client follow-ups',
                            'Outdated ERP-style interfaces',
                            'Time-consuming quotation preparation',
                        ] as $pain)
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-x-lg" style="color:#EF4444;font-size:0.8rem;flex-shrink:0;"></i>
                            <span style="font-size:0.9rem;color:#475569;">{{ $pain }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            {{-- Solution --}}
            <div class="col-md-6 animate-fade-in">
                <div class="premium-card h-100" style="border-left: 4px solid #1D6FEB; padding: 28px; background: linear-gradient(135deg, rgba(29,111,235,0.03) 0%, rgba(2,132,199,0.03) 100%);">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(29,111,235,0.1);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-check-circle-fill" style="color:#1D6FEB;"></i>
                        </div>
                        <h5 class="mb-0 fw-bold" style="color:#1D6FEB;">Catasky — Instant</h5>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        @foreach([
                            'Browse products effortlessly',
                            'Filter and sort in seconds',
                            'Select multiple products instantly',
                            'Generate branded presentations',
                            'Share via PDF, links, or images',
                            'All from a clean, mobile-first interface',
                        ] as $sol)
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-check-lg" style="color:#1D6FEB;font-size:0.8rem;flex-shrink:0;"></i>
                            <span style="font-size:0.9rem;color:#1E293B;font-weight:500;">{{ $sol }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ================================================================
     FEATURES: DESIGNED FOR MODERN SALES TEAMS
     ================================================================ --}}
<section id="features" class="home-section home-section-soft">
    <div class="container">
        <div class="text-center mb-5 animate-fade-in">
            <div class="section-tag">Catalogue Capabilities</div>
            <h2 class="section-heading">Designed for <span class="text-gradient">Modern Sales Teams</span></h2>
            <p class="section-sub">Everything your team needs to present, share, and close — all in one place.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4 animate-fade-in">
                <div class="feature-card-v2">
                    <div class="feature-icon-v2" style="background: linear-gradient(135deg,#1D6FEB,#0284C7);">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                    </div>
                    <h5>Smart Product Browsing</h5>
                    <p>Beautiful catalogue experience with fast product discovery, intuitive navigation, and instant search.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 animate-fade-in">
                <div class="feature-card-v2">
                    <div class="feature-icon-v2" style="background: linear-gradient(135deg,#06B6D4,#0284C7);">
                        <i class="bi bi-funnel-fill"></i>
                    </div>
                    <h5>Advanced Filters & Sorting</h5>
                    <p>Help clients find exactly what they need using smart filters, categories, pricing, and sorting options.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 animate-fade-in">
                <div class="feature-card-v2">
                    <div class="feature-icon-v2" style="background: linear-gradient(135deg,#F59E0B,#D97706);">
                        <i class="bi bi-check2-all"></i>
                    </div>
                    <h5>Multi-Product Selection</h5>
                    <p>Create product shortlists instantly with a seamless multi-select experience and smart floating dock.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 animate-fade-in">
                <div class="feature-card-v2">
                    <div class="feature-icon-v2" style="background: linear-gradient(135deg,#25D366,#128C7E);">
                        <i class="bi bi-share-fill"></i>
                    </div>
                    <h5>Instant Sharing Options</h5>
                    <p>Share via branded PDFs, shareable links, individual product images, or WhatsApp-ready presentations.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 animate-fade-in">
                <div class="feature-card-v2">
                    <div class="feature-icon-v2" style="background: linear-gradient(135deg,#EC4899,#D946EF);">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <h5>Dynamic Pricing & Personalization</h5>
                    <p>Include MRP, offer price, margins, descriptions, custom notes, watermarks, and company branding per client.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 animate-fade-in">
                <div class="feature-card-v2 dark">
                    <div class="feature-icon-v2" style="background:rgba(255,255,255,0.15);">
                        <i class="bi bi-phone-fill"></i>
                    </div>
                    <h5>Mobile-First Experience</h5>
                    <p style="color:rgba(255,255,255,0.7);">Built for modern sales teams on the go. Works beautifully across mobile phones, tablets, and desktops.</p>
                </div>
            </div>
        </div>

        {{-- Real-Time Analytics row --}}
        <div class="row g-4 mt-2">
            <div class="col-md-12 animate-fade-in">
                <div class="premium-card" style="padding:28px; background: linear-gradient(135deg, rgba(29,111,235,0.04) 0%, rgba(2,132,199,0.04) 100%); border: 1.5px solid rgba(29,111,235,0.12);">
                    <div class="row align-items-center g-4">
                        <div class="col-md-5">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#1D6FEB,#0284C7);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="bi bi-bar-chart-line-fill text-white fs-5"></i>
                                </div>
                                <h5 class="mb-0 fw-bold">Real-Time Analytics</h5>
                            </div>
                            <p style="color:#64748B;font-size:0.9rem;line-height:1.7;">Make smarter sales decisions with real-time data. Track what matters most to close deals.</p>
                        </div>
                        <div class="col-md-7">
                            <div class="row g-3">
                                @foreach([
                                    ['icon'=>'bi-eye-fill','color'=>'#1D6FEB','label'=>'Viewed Products'],
                                    ['icon'=>'bi-graph-up-arrow','color'=>'#10B981','label'=>'Customer Engagement'],
                                    ['icon'=>'bi-star-fill','color'=>'#F59E0B','label'=>'Popular Selections'],
                                    ['icon'=>'bi-file-earmark-pdf-fill','color'=>'#EF4444','label'=>'Shared Catalogues'],
                                    ['icon'=>'bi-lightning-fill','color'=>'#0284C7','label'=>'Conversion Insights'],
                                ] as $stat)
                                <div class="col-6 col-md-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi {{ $stat['icon'] }}" style="color:{{ $stat['color'] }};font-size:0.9rem;"></i>
                                        <span style="font-size:0.82rem;font-weight:600;color:#1E293B;">{{ $stat['label'] }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ================================================================
     HOW IT WORKS
     ================================================================ --}}
<section class="home-section bg-white">
    <div class="container">
        <div class="text-center mb-5 animate-fade-in">
            <div class="section-tag">Simple Workflow</div>
            <h2 class="section-heading">Go from Products to <span class="text-gradient">Shared Catalogue</span> in 3 Steps</h2>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach([
                ['step'=>'01','icon'=>'bi-box-seam','color'=>'#1D6FEB','bg'=>'rgba(29,111,235,0.08)','title'=>'Add Your Products','desc'=>'Upload products with images, variants, specifications, pricing, and brand details into your Catasky account.'],
                ['step'=>'02','icon'=>'bi-check2-all','color'=>'#10B981','bg'=>'rgba(16,185,129,0.08)','title'=>'Select & Curate','desc'=>'Pick products across categories with one click. Build a focused, curated catalogue selection for your buyer.'],
                ['step'=>'03','icon'=>'bi-send-fill','color'=>'#0284C7','bg'=>'rgba(2,132,199,0.08)','title'=>'Export & Share','desc'=>'Generate a branded PDF, WhatsApp image cards, or a shareable link and send it to your client instantly.'],
            ] as $step)
            <div class="col-md-4 animate-fade-in">
                <div class="text-center" style="padding: 20px 10px;">
                    <div style="width:80px;height:80px;border-radius:24px;background:{{ $step['bg'] }};display:flex;align-items:center;justify-content:center;margin:0 auto 20px;position:relative;">
                        <i class="bi {{ $step['icon'] }}" style="font-size:2rem;color:{{ $step['color'] }};"></i>
                        <span style="position:absolute;top:-10px;right:-10px;width:28px;height:28px;background:{{ $step['color'] }};color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:800;border:2px solid white;">{{ $step['step'] }}</span>
                    </div>
                    <h5 style="font-weight:800;color:#1E293B;margin-bottom:10px;">{{ $step['title'] }}</h5>
                    <p style="font-size:0.9rem;color:#64748B;line-height:1.7;max-width:280px;margin:0 auto;">{{ $step['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================================================================
     WHY CATASKY
     ================================================================ --}}
<section class="home-section home-section-soft" id="why-catasky">
    <div class="container">
        <div class="text-center mb-5 animate-fade-in">
            <div class="section-tag">Why Choose Us</div>
            <h2 class="section-heading">Why Businesses <span class="text-gradient">Choose Catasky</span></h2>
            <p class="section-sub">Not just a tool — a competitive advantage for your sales team.</p>
        </div>
        <div class="row g-4">
            @foreach([
                ['icon'=>'bi-lightning-charge-fill','color'=>'#1D6FEB','bg'=>'rgba(29,111,235,0.08)','title'=>'Close Sales Faster','desc'=>'Reduce the time between product selection and client approval. Share in seconds, not hours.'],
                ['icon'=>'bi-clock-fill','color'=>'#10B981','bg'=>'rgba(16,185,129,0.08)','title'=>'Save Valuable Time','desc'=>'Share curated product options instantly instead of manually creating presentations every time.'],
                ['icon'=>'bi-hand-thumbs-up-fill','color'=>'#F59E0B','bg'=>'rgba(245,158,11,0.08)','title'=>'Effortless & Convenient','desc'=>'A simple workflow that eliminates repetitive manual tasks so your team can focus on selling.'],
                ['icon'=>'bi-file-earmark-richtext-fill','color'=>'#0284C7','bg'=>'rgba(2,132,199,0.08)','title'=>'Better Than Traditional PDFs','desc'=>'Interactive, dynamic, and customizable sharing experiences that look modern and professional.'],
                ['icon'=>'bi-shield-check-fill','color'=>'#EC4899','bg'=>'rgba(236,72,153,0.08)','title'=>'Better Than Old ERP Systems','desc'=>'No clutter. No complexity. Just a clean and intuitive interface designed for sales conversion.'],
                ['icon'=>'bi-graph-up-arrow','color'=>'#06B6D4','bg'=>'rgba(6,182,212,0.08)','title'=>'Built for Scale','desc'=>'A frontend-driven, multi-tenant SaaS architecture ready for future growth and expansion.'],
            ] as $why)
            <div class="col-md-6 col-lg-4 animate-fade-in">
                <div class="feature-card-v2">
                    <div class="feature-icon-v2" style="background:{{ $why['bg'] }}; box-shadow: none; border: 1.5px solid rgba(0,0,0,0.05);">
                        <i class="bi {{ $why['icon'] }}" style="color:{{ $why['color'] }};"></i>
                    </div>
                    <h5>{{ $why['title'] }}</h5>
                    <p>{{ $why['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================================================================
     NOT ANOTHER LEAD MARKETPLACE
     ================================================================ --}}
<section class="home-section bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 animate-fade-in">
                <div class="section-tag">Differentiation</div>
                <h2 class="section-heading">Not Another <span class="text-gradient">Lead Marketplace</span></h2>
                <p style="color:#64748B;font-size:1rem;line-height:1.8;">Platforms like IndiaMART and Justdial focus on <strong>generating leads</strong>.</p>
                <p style="color:#1E293B;font-size:1rem;line-height:1.8;font-weight:600;">Catasky focuses on: <span class="text-gradient">converting those leads into sales faster.</span></p>
                <p style="color:#64748B;font-size:0.9rem;line-height:1.8;">It is built specifically for businesses that need to:</p>
                <div class="d-flex flex-column gap-2 mt-3">
                    @foreach([
                        'Present products professionally',
                        'Simplify client communication',
                        'Speed up approvals',
                        'Increase conversions',
                    ] as $diff)
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:8px;height:8px;border-radius:50%;background:#1D6FEB;flex-shrink:0;"></div>
                        <span style="font-size:0.95rem;color:#1E293B;font-weight:500;">{{ $diff }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6 animate-fade-in">
                <div class="premium-card" style="padding:40px; background: linear-gradient(135deg, rgba(29,111,235,0.04), rgba(2,132,199,0.06)); border: 1.5px solid rgba(29,111,235,0.15); text-align:center;">
                    <div style="font-size:3rem; font-weight:900; color:#1D6FEB; line-height:1; margin-bottom:16px;">The Future<br>of Product Sharing</div>
                    <p style="color:#64748B;font-size:0.95rem;line-height:1.8; margin-bottom:24px;">Catasky combines the simplicity of modern SaaS platforms with the power of smart product presentation.</p>
                    <div class="d-flex flex-column gap-2">
                        @foreach(['No clutter.','No complicated workflows.','No outdated catalogues.','Just a smarter way to share products.'] as $promise)
                        <div style="padding:10px 20px;background:white;border-radius:12px;font-size:0.9rem;font-weight:600;color:#1E293B;border:1.5px solid rgba(29,111,235,0.1);">{{ $promise }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ================================================================
     PRICING
     ================================================================ --}}
<section class="pricing-section" id="pricing">
    <div class="container" style="position:relative;z-index:2;">
        <div class="text-center mb-2 animate-fade-in">
            <div class="section-tag">Simple Pricing</div>
            <h2 class="section-heading">Plans That Grow <span class="text-gradient">With Your Business</span></h2>
            <p class="section-sub">Start free, upgrade when you need. No hidden fees. Cancel anytime.</p>
        </div>

        <div class="d-flex justify-content-center mt-4 mb-5 animate-fade-in">
            <div class="pricing-toggle-wrap">
                <button class="pricing-toggle-btn active" id="monthly-btn">Monthly</button>
                <button class="pricing-toggle-btn" id="annual-btn">Annual</button>
                <span class="pricing-save-badge ms-2">Save 30%</span>
            </div>
        </div>

        <div class="row g-4 align-items-stretch">
            <div class="col-md-4 animate-fade-in">
                <div class="pricing-card">
                    <div class="pricing-plan-name">Starter</div>
                    <div class="pricing-price">
                        <span class="pricing-currency">₹</span>
                        <span class="pricing-amount" id="starter-price">499</span>
                        <span class="pricing-period">/mo</span>
                    </div>
                    <p class="pricing-desc">Perfect for small teams and freelancers just getting started with digital catalogues.</p>
                    <hr class="pricing-divider">
                    <ul class="pricing-features">
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> Up to 50 Products</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> 20 Attributes</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> 100 Share Links</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> WhatsApp Sharing</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> PDF & Image Sharing</li>
                        <li><span class="pricing-x"><i class="bi bi-x-lg"></i></span> <span style="color:#CBD5E1;">Custom Domain</span></li>
                        <li><span class="pricing-x"><i class="bi bi-x-lg"></i></span> <span style="color:#CBD5E1;">Advanced Analytics</span></li>
                    </ul>
                    <a href="{{ route('subscriber.register') }}?plan=starter" class="btn-pricing-outline">Get Started Free</a>
                </div>
            </div>

            <div class="col-md-4 animate-fade-in">
                <div class="pricing-card featured">
                    <div class="pricing-popular-badge">⚡ Most Popular</div>
                    <div class="pricing-plan-name">Business</div>
                    <div class="pricing-price">
                        <span class="pricing-currency" style="color:rgba(255,255,255,0.7);">₹</span>
                        <span class="pricing-amount" style="color:white;" id="pro-price">1,299</span>
                        <span class="pricing-period">/mo</span>
                    </div>
                    <p class="pricing-desc">For growing B2B sales teams that need advanced sharing tools and custom branding.</p>
                    <hr class="pricing-divider">
                    <ul class="pricing-features">
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> Up to 250 Products</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> 100 Attributes</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> 500 Share Links</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> Custom Branding & Logo</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> Analytics Dashboard</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> Watermark Removal</li>
                        <li><span class="pricing-x"><i class="bi bi-x-lg"></i></span> <span style="color:#CBD5E1;">Custom Domain</span></li>
                    </ul>
                    <a href="{{ route('subscriber.register') }}?plan=business" class="btn-pricing-white">Start Business</a>
                </div>
            </div>

            <div class="col-md-4 animate-fade-in">
                <div class="pricing-card">
                    <div class="pricing-plan-name">Enterprise</div>
                    <div class="pricing-price">
                        <span class="pricing-currency">₹</span>
                        <span class="pricing-amount" id="ent-price">3,999</span>
                        <span class="pricing-period">/mo</span>
                    </div>
                    <p class="pricing-desc">For large enterprises needing white-label solutions, custom domains, and dedicated support.</p>
                    <hr class="pricing-divider">
                    <ul class="pricing-features">
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> Unlimited Products</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> Unlimited Attributes</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> Unlimited Share Links</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> Custom Domain Ready</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> REST API Access</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> Multi-User + Roles</li>
                        <li><span class="pricing-check"><i class="bi bi-check-lg"></i></span> Priority Support (24/7)</li>
                    </ul>
                    <a href="{{ route('subscriber.register') }}?plan=enterprise" class="btn-pricing-primary">Contact Sales</a>
                </div>
            </div>
        </div>

        <p class="pricing-note">
            <i class="bi bi-shield-check text-success me-1"></i>
            All plans include 14-day free trial. No credit card required. Cancel anytime.
        </p>
    </div>
</section>

{{-- ================================================================
     TESTIMONIALS
     ================================================================ --}}
<section class="testimonials-section">
    <div class="container">
        <div class="text-center mb-5 animate-fade-in">
            <div class="section-tag">Customer Love</div>
            <h2 class="section-heading">Trusted by <span class="text-gradient">B2B Sales Teams</span></h2>
        </div>
        <div class="row g-4">
            @foreach([
                ['name'=>'Rahul Sharma','role'=>'Sales Head, TechGifts Pvt Ltd','text'=>'"Catasky completely transformed how we share our product catalogue. Our clients are impressed by the professional PDFs, and our close rate has jumped by 40%."','color'=>'#1D6FEB','initial'=>'R'],
                ['name'=>'Priya Mehta','role'=>'Procurement Manager, EventCo','text'=>'"The WhatsApp sharing feature is a game-changer. I can send a curated selection of 10 products with prices and images in under 2 minutes. Incredible tool."','color'=>'#10B981','initial'=>'P'],
                ['name'=>'Suresh Kumar','role'=>'MD, BrandMerch Solutions','text'=>'"We tried 5 different tools before Catasky. Nothing came close to this level of polish. The PDF output looks exactly like a printed catalogue. Worth every rupee."','color'=>'#0284C7','initial'=>'S'],
            ] as $t)
            <div class="col-md-4 animate-fade-in">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">{{ $t['text'] }}</p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="testimonial-avatar" style="background:{{ $t['color'] }};">{{ $t['initial'] }}</div>
                        <div>
                            <div class="testimonial-name">{{ $t['name'] }}</div>
                            <div class="testimonial-role">{{ $t['role'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================================================================
     CTA BANNER
     ================================================================ --}}
<section class="py-5" style="background:white; padding-top:80px!important; padding-bottom:80px!important;">
    <div class="container">
        <div class="cta-banner animate-fade-in">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill mb-4" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);">
                        <i class="bi bi-rocket-takeoff-fill text-warning"></i>
                        <span style="font-size:0.78rem;font-weight:700;color:rgba(255,255,255,0.9);letter-spacing:0.5px;text-transform:uppercase;">Ready to Simplify Product Sharing?</span>
                    </div>
                    <h2 style="font-size:clamp(1.8rem,4vw,2.8rem);font-weight:800;color:white;line-height:1.15;margin-bottom:16px;letter-spacing:-0.03em;">
                        Start Sharing Smarter,<br>Faster, <span style="color:#93C5FD;">More Professionally</span>
                    </h2>
                    <p style="color:rgba(255,255,255,0.7);font-size:1rem;line-height:1.7;max-width:480px;margin:0;">
                        Join thousands of B2B sales teams who use Catasky to create, share and close product catalogues in seconds — not hours.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="cta-glass-panel">
                        <h5 style="font-weight:800;color:white;margin-bottom:8px;font-size:1.1rem;">Get Started Today</h5>
                        <p style="color:rgba(255,255,255,0.65);font-size:0.875rem;margin-bottom:24px;line-height:1.6;">No credit card required. Start free and upgrade when your team is ready to scale.</p>
                        <div class="d-grid gap-3">
                            <a href="{{ route('subscriber.register') }}" class="btn-hero-primary" style="justify-content:center;">
                                <i class="bi bi-rocket-takeoff-fill"></i> Start Free Trial
                            </a>
                            <a href="{{ route('demo') }}" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:14px;background:transparent;color:rgba(255,255,255,0.8);border-radius:14px;font-weight:600;border:1.5px solid rgba(255,255,255,0.2);text-decoration:none;transition:all 0.3s ease;font-size:0.95rem;" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">
                                <i class="bi bi-search"></i> Explore Catalogue
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // Intersection Observer for scroll-reveal — fast, no debounce
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => { entry.target.classList.add('active'); }, i * 60);
            }
        });
    }, { threshold: 0.04, rootMargin: '0px 0px -30px 0px' });

    document.querySelectorAll('.animate-fade-in').forEach(el => observer.observe(el));

    // Pricing toggle
    const prices = {
        monthly: { starter: '499', pro: '1,299', ent: '3,999' },
        annual:  { starter: '349', pro: '899', ent: '2,799' },
    };

    function setPrices(mode) {
        const p = prices[mode];
        $('#starter-price').text(p.starter);
        $('#pro-price').text(p.pro);
        $('#ent-price').text(p.ent);
    }

    $('#monthly-btn').on('click', function () {
        $(this).addClass('active'); $('#annual-btn').removeClass('active'); setPrices('monthly');
    });
    $('#annual-btn').on('click', function () {
        $(this).addClass('active'); $('#monthly-btn').removeClass('active'); setPrices('annual');
    });

    // Navbar scroll effect
    $(window).on('scroll', function () {
        $('.navbar-premium').toggleClass('scrolled', $(this).scrollTop() > 30);
    });
});
</script>
@endpush
