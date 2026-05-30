<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $primaryColor = $profile?->primary_color ?? '#4F46E5';
        $secondaryColor = $profile?->secondary_color ?? '#7C3AED';
        $companyName = $profile?->company_name ?? ($subscriber?->name ?? 'Catalog Storefront');
        $bannerUrl = $profile?->banner_url;
    @endphp
    <title>{{ $companyName }} | Catalog Store</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: {{ $primaryColor }};
            --secondary: {{ $secondaryColor }};
            --bg: #0B0F19;
            --card-bg: rgba(17, 24, 39, 0.7);
            --border: rgba(255, 255, 255, 0.08);
            --text: #F3F4F6;
            --text-muted: #9CA3AF;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.12) 0, transparent 50%),
                radial-gradient(at 100% 100%, rgba(124, 58, 237, 0.12) 0, transparent 50%);
            color: var(--text);
            min-height: 100vh;
            padding-bottom: 80px;
        }

        /* ─── Hero Section with Panoramic Banner ─── */
        .store-hero-banner-wrapper {
            position: relative;
            border-radius: 0 0 32px 32px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            border-bottom: 1.5px solid var(--border);
            margin-bottom: 80px;
        }

        .store-panoramic-backdrop {
            width: 100%;
            height: 280px;
            background-color: #1e1b4b;
            background-image: url('{{ $bannerUrl }}');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        @media(min-width: 768px) {
            .store-panoramic-backdrop {
                height: 340px;
            }
        }

        .store-panoramic-backdrop::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to bottom, rgba(11, 15, 25, 0.2) 0%, rgba(11, 15, 25, 0.75) 100%);
        }

        /* Floating Store Profile Overlay */
        .store-floating-profile-card {
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 800px;
            background: rgba(22, 28, 45, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 20px 24px;
            z-index: 10;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 16px;
        }

        @media(min-width: 768px) {
            .store-floating-profile-card {
                flex-direction: row;
                text-align: left;
                align-items: center;
                gap: 24px;
                bottom: -50px;
            }
        }

        .store-brand-logo {
            width: 88px; height: 88px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; color: white;
            font-weight: 800; font-family: 'Outfit', sans-serif;
            border: 3px solid rgba(255,255,255,0.15);
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .store-title-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            letter-spacing: -0.02em;
            background: linear-gradient(to right, #ffffff, #E2E8F0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }

        /* Category Filter Badges */
        .filter-badge {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            color: var(--text-muted);
            border-radius: 30px;
            padding: 9px 20px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-block;
        }

        .filter-badge:hover, .filter-badge.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.35);
            transform: translateY(-1px);
        }

        /* Glassmorphic Product Cards */
        .pub-product-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .pub-product-card:hover {
            transform: translateY(-6px);
            border-color: var(--primary);
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.25);
        }

        .pub-img-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 4/3;
            background: #111827;
            overflow: hidden;
        }

        .pub-product-img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .pub-product-card:hover .pub-product-img {
            transform: scale(1.06);
        }

        /* Search input bar */
        .search-wrapper {
            max-width: 550px;
            margin: 40px auto 30px;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1.5px solid var(--border);
            border-radius: 30px;
            color: white;
            padding: 13px 26px;
            font-size: 0.92rem;
            width: 100%;
            transition: all 0.25s ease;
            backdrop-filter: blur(8px);
        }

        .search-input:focus {
            background: rgba(255, 255, 255, 0.07);
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 20px rgba(79, 70, 229, 0.25);
        }

        /* Detail Modal Design */
        .pub-modal-content {
            background: #0B0F19;
            border: 1.5px solid var(--border);
            border-radius: 24px;
            color: var(--text);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            background-image: radial-gradient(circle at top right, rgba(99, 102, 241, 0.08), transparent 60%);
        }

        .spec-label-column {
            color: var(--text-muted);
            width: 160px;
            font-weight: 500;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }

        .spec-value-column {
            color: white;
            font-weight: 600;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }
    </style>
</head>
<body>

{{-- Banner and Floating Header wrapper --}}
<div class="store-hero-banner-wrapper">
    <div class="store-panoramic-backdrop"></div>
    
    {{-- Floating Profile Card --}}
    <div class="store-floating-profile-card">
        @if($profile?->logo)
            <img class="store-brand-logo" src="{{ asset('uploads/subscriber-logos/' . $profile->logo) }}" alt="Logo">
        @else
            <div class="store-brand-logo">{{ strtoupper(substr($companyName, 0, 1)) }}</div>
        @endif
        
        <div class="flex-grow-1">
            <h1 class="store-title-text">{{ $companyName }}</h1>
            @if($profile?->bio)
                <p class="text-muted small mb-3" style="max-width: 600px; line-height: 1.5;">{{ $profile->bio }}</p>
            @endif
            
            <div class="d-flex align-items-center justify-content-center justify-content-md-start flex-wrap gap-x-3 gap-y-1.5 text-muted" style="font-size:0.82rem;">
                @if($profile?->website)
                    <span class="me-3"><i class="bi bi-globe text-primary me-1.5"></i><a href="{{ $profile->website }}" target="_blank" style="color:var(--text-muted); text-decoration:none;">{{ str_replace(['https://','http://','www.'], '', $profile->website) }}</a></span>
                @endif
                @if($profile?->email_for_inquiries)
                    <span class="me-3"><i class="bi bi-envelope text-primary me-1.5"></i>{{ $profile->email_for_inquiries }}</span>
                @endif
                @if($profile?->phone)
                    <span><i class="bi bi-telephone text-primary me-1.5"></i>{{ $profile->phone }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="container">
    {{-- Search Form --}}
    <div class="search-wrapper">
        <form action="" method="GET">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <div class="position-relative">
                <input type="text" name="search" placeholder="Search catalog collection..." class="search-input" value="{{ request('search') }}">
                <button type="submit" class="position-absolute border-0 bg-transparent text-muted" style="right: 22px; top: 50%; transform: translateY(-50%);"><i class="bi bi-search fs-5"></i></button>
            </div>
        </form>
    </div>

    {{-- Category Filters --}}
    <div class="d-flex align-items-center justify-content-center flex-wrap gap-2.5 mb-5">
        <a href="?{{ request('search') ? 'search='.request('search') : '' }}" class="filter-badge {{ !request('category') ? 'active' : '' }}">All Products</a>
        @foreach($subscriberCategories as $cat)
            <a href="?category={{ $cat->slug }}{{ request('search') ? '&search='.request('search') : '' }}" class="filter-badge {{ request('category') === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
        @endforeach
    </div>

    {{-- Catalog Grid --}}
    @if($catalogProducts->isEmpty())
        <div class="glass-card p-5 text-center text-muted" style="border-radius:24px; background:rgba(22, 28, 45, 0.45);">
            <i class="bi bi-inboxes-fill fs-1 mb-3 text-secondary d-block"></i>
            <h5 class="text-light fw-bold">No products found</h5>
            <p class="mb-0 extra-small">No approved active products are currently matching your filters in this storefront catalog.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach($catalogProducts as $prod)
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="pub-product-card">
                    <div class="pub-img-wrap">
                        @if($prod->thumbnail)
                            <img src="{{ $prod->thumbnail_url }}" alt="" class="pub-product-img">
                        @else
                            <div class="w-100 h-100 bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-secondary opacity-30 fs-1">📦</div>
                        @endif
                    </div>
                    
                    <div class="p-3.5 d-flex flex-column flex-grow-1">
                        <h6 class="text-white fw-bold mb-2" style="font-family:'Outfit', sans-serif; font-size:0.95rem; line-height:1.4;">{{ $prod->name }}</h6>
                        
                        @if($prod->short_description)
                            <p class="text-muted" style="font-size:0.78rem; line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; flex-grow:1;">{{ $prod->short_description }}</p>
                        @endif

                        {{-- Pricing --}}
                        <div class="d-flex align-items-baseline gap-2 mt-auto pt-3 border-top" style="border-color:rgba(255,255,255,0.06);">
                            @if($prod->offer_price && ($settings['show_offer_price'] ?? true))
                                <div class="fw-bold fs-5 text-primary" style="font-family:'Outfit', sans-serif; color:var(--primary) !important;">₹{{ number_format($prod->offer_price, 2) }}</div>
                                @if($prod->mrp && ($settings['show_mrp'] ?? true))
                                    <div class="text-muted text-decoration-line-through" style="font-size:0.75rem;">₹{{ number_format($prod->mrp, 2) }}</div>
                                @endif
                            @elseif($prod->mrp && ($settings['show_mrp'] ?? true))
                                <div class="fw-bold fs-5 text-white" style="font-family:'Outfit', sans-serif;">₹{{ number_format($prod->mrp, 2) }}</div>
                            @else
                                <div class="text-muted italic" style="font-size:0.78rem;">Contact for pricing</div>
                            @endif
                        </div>

                        <button type="button" class="btn btn-sm w-100 mt-3 py-2 fw-semibold text-white d-flex align-items-center justify-content-center gap-1.5" style="border-radius:10px; background:rgba(255,255,255,0.04); border:1px solid var(--border); transition:all 0.2s;"
                                onclick="openProductDetail({{ $prod->id }})" onmouseover="this.style.background='rgba(255,255,255,0.09)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                            <i class="bi bi-info-circle"></i> View Specifications
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content pub-modal-content">
            <div class="modal-header border-bottom-0" style="padding:24px 28px 12px;">
                <h5 class="modal-title fw-bold text-white fs-4" style="font-family:'Outfit',sans-serif;" id="modal-product-name"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:12px 28px 28px;">
                <div class="row g-4">
                    <div class="col-md-5">
                        <img id="modal-product-img" src="" alt="" class="w-100 border shadow-sm" style="aspect-ratio:1/1; object-fit:cover; border-radius:16px; border-color:var(--border) !important;">
                    </div>
                    <div class="col-md-7">
                        <div id="modal-price-block" class="d-flex align-items-baseline gap-2.5 mb-3"></div>
                        <p id="modal-product-desc" class="text-muted mb-4" style="font-size:0.88rem; line-height:1.6;"></p>
                        
                        <div id="modal-spec-block">
                            <h6 class="text-white fw-bold mb-2.5 d-flex align-items-center gap-1.5" style="font-family:'Outfit',sans-serif;"><i class="bi bi-sliders text-primary"></i> Product Specifications</h6>
                            <div class="table-responsive">
                                <table class="table table-dark table-borderless table-sm mb-0" style="--bs-table-bg:transparent; font-size:0.82rem;">
                                    <tbody id="modal-spec-tbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
@if(!$catalogProducts->isEmpty())
const catalogProducts = {!! json_encode($catalogProducts->keyBy('id')) !!};

function openProductDetail(productId) {
    const p = catalogProducts[productId];
    if (!p) return;

    document.getElementById('modal-product-name').textContent = p.name;
    document.getElementById('modal-product-img').src = p.thumbnail_url || 'https://via.placeholder.com/400';
    document.getElementById('modal-product-desc').textContent = p.short_description || '';

    // Price block
    const priceBlock = document.getElementById('modal-price-block');
    priceBlock.innerHTML = '';
    
    if (p.offer_price) {
        priceBlock.innerHTML = `<div class="fw-bold text-primary" style="font-family:'Outfit',sans-serif;font-size:1.8rem;color:var(--primary) !important;">₹${parseFloat(p.offer_price).toFixed(2)}</div>`;
        if (p.mrp) {
            priceBlock.innerHTML += `<div class="text-muted text-decoration-line-through" style="font-size:1rem;margin-bottom:4px;">₹${parseFloat(p.mrp).toFixed(2)}</div>`;
        }
    } else if (p.mrp) {
        priceBlock.innerHTML = `<div class="fw-bold text-white" style="font-family:'Outfit',sans-serif;font-size:1.8rem;">₹${parseFloat(p.mrp).toFixed(2)}</div>`;
    } else {
        priceBlock.innerHTML = `<div class="text-muted italic" style="font-size:0.95rem;">Contact for pricing</div>`;
    }

    // Specifications
    const tbody = document.getElementById('modal-spec-tbody');
    tbody.innerHTML = '';
    
    if (p.attribute_values && p.attribute_values.length > 0) {
        p.attribute_values.forEach(val => {
            if (val.attribute && val.attribute.show_in_share) {
                let displayVal = val.value;
                if (val.attribute.type === 'multiselect' || val.attribute.type === 'checkbox') {
                    try {
                        const parsed = JSON.parse(val.value);
                        displayVal = Array.isArray(parsed) ? parsed.join(', ') : val.value;
                    } catch(e) {}
                }
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="spec-label-column">${val.attribute.name}</td>
                    <td class="spec-value-column">${displayVal} ${val.attribute.unit || ''}</td>
                `;
                tbody.appendChild(tr);
            }
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="2" class="text-muted text-center py-2" style="font-size:0.75rem;">No technical specifications defined for this product.</td></tr>';
    }

    const modal = new bootstrap.Modal(document.getElementById('productDetailModal'));
    modal.show();
}
@endif
</script>
</body>
</html>
