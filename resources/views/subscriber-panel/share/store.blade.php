<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $primaryColor = $profile?->primary_color ?? '#4F46E5';
        $secondaryColor = $profile?->secondary_color ?? '#7C3AED';
        $companyName = $profile?->company_name ?? ($subscriber?->name ?? 'Catalog Storefront');
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
            padding-bottom: 80px;
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        /* ─── Grid Products ────────────────────────────── */
        .pub-product-card {
            background: rgba(17, 24, 39, 0.55);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.25s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .pub-product-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.25);
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
            transition: transform 0.3s;
        }

        .pub-product-card:hover .pub-product-img {
            transform: scale(1.05);
        }

        /* Hero block */
        .share-hero {
            padding: 60px 0 40px;
            text-align: center;
        }

        .share-hero-logo {
            width: 84px; height: 84px;
            border-radius: 22px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; color: white;
            font-weight: 800; font-family: 'Outfit', sans-serif;
            margin: 0 auto 20px;
            border: 2px solid rgba(255,255,255,0.1);
            object-fit: cover;
        }

        .share-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 2.5rem;
            letter-spacing: -0.02em;
            background: linear-gradient(to right, #FFF, #E2E8F0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        /* Category filters */
        .filter-badge {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: var(--text-muted);
            border-radius: 30px;
            padding: 8px 18px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .filter-badge:hover, .filter-badge.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .search-wrapper {
            max-width: 500px;
            margin: 0 auto 30px;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            border-radius: 30px;
            color: white;
            padding: 12px 24px;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 15px rgba(79, 70, 229, 0.2);
        }

        /* ─── Detail Modal ─────────────────────────────── */
        .pub-modal-content {
            background: #0F172A;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            color: var(--text);
        }

        .badge-discount {
            font-size: 0.72rem;
            font-weight: 700;
            background: #10B981;
            color: white;
            padding: 3px 8px;
            border-radius: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    {{-- Hero Section --}}
    <div class="share-hero">
        @if($profile?->logo)
            <img class="share-hero-logo" src="{{ asset('uploads/subscriber-logos/' . $profile->logo) }}" alt="Logo">
        @else
            <div class="share-hero-logo">{{ strtoupper(substr($companyName, 0, 1)) }}</div>
        @endif
        <h1 class="share-title">{{ $companyName }}</h1>
        @if($profile?->bio)
            <p class="text-muted mx-auto mb-3" style="max-width: 600px; font-size:0.92rem; line-height: 1.6;">{{ $profile->bio }}</p>
        @endif
        <div class="d-flex align-items-center justify-content-center flex-wrap gap-3 text-muted" style="font-size:0.85rem;">
            @if($profile?->website)
                <span><i class="bi bi-globe text-primary me-1.5"></i> <a href="{{ $profile->website }}" target="_blank" style="color:var(--text-muted);text-decoration:none;">{{ str_replace(['https://','http://','www.'], '', $profile->website) }}</a></span>
            @endif
            @if($profile?->email_for_inquiries)
                <span>· &nbsp;<i class="bi bi-envelope text-primary me-1.5"></i> {{ $profile->email_for_inquiries }}</span>
            @endif
            @if($profile?->phone)
                <span>· &nbsp;<i class="bi bi-phone text-primary me-1.5"></i> {{ $profile->phone }}</span>
            @endif
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="search-wrapper">
        <form action="" method="GET">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <div class="position-relative">
                <input type="text" name="search" placeholder="Search our digital catalog..." class="search-input" value="{{ request('search') }}">
                <button type="submit" class="position-absolute border-0 bg-transparent text-muted" style="right: 20px; top: 50%; transform: translateY(-50%);"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>

    <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 mb-5">
        <a href="?{{ request('search') ? 'search='.request('search') : '' }}" class="filter-badge {{ !request('category') ? 'active' : '' }}">All Products</a>
        @foreach($subscriberCategories as $cat)
            <a href="?category={{ $cat->slug }}{{ request('search') ? '&search='.request('search') : '' }}" class="filter-badge {{ request('category') === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
        @endforeach
    </div>

    {{-- Product Grid --}}
    @if($catalogProducts->isEmpty())
        <div class="glass-card p-5 text-center text-muted">
            <i class="bi bi-inboxes fs-1 mb-3 text-secondary d-block"></i>
            <h5 class="text-light">No products found</h5>
            <p class="mb-0">There are no approved active products matching your search criteria in our catalog storefront.</p>
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
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:#374151;">📦</div>
                        @endif
                    </div>
                    
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <h6 style="font-family:'Outfit',sans-serif;font-weight:700;margin-bottom:6px;line-height:1.4;">{{ $prod->name }}</h6>
                        
                        @if($prod->short_description)
                            <p class="text-muted" style="font-size:0.78rem;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;flex-grow:1;">{{ $prod->short_description }}</p>
                        @endif

                        {{-- Price block --}}
                        <div class="d-flex align-items-baseline gap-2 mt-auto pt-2 border-top" style="border-color:rgba(255,255,255,0.06);">
                            @if($prod->offer_price && ($settings['show_offer_price'] ?? true))
                                <div style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--primary);font-size:1.05rem;">₹{{ number_format($prod->offer_price, 2) }}</div>
                                @if($prod->mrp && ($settings['show_mrp'] ?? true))
                                    <div style="font-size:0.75rem;color:var(--text-muted);text-decoration:line-through;">₹{{ number_format($prod->mrp, 2) }}</div>
                                @endif
                            @elseif($prod->mrp && ($settings['show_mrp'] ?? true))
                                <div style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--text);font-size:1.05rem;">₹{{ number_format($prod->mrp, 2) }}</div>
                            @else
                                <div style="color:var(--text-muted);font-style:italic;font-size:0.8rem;">Contact for Price</div>
                            @endif
                        </div>

                        <button type="button" class="btn btn-sm mt-3" style="border-radius:10px;background:rgba(255,255,255,0.05);border:1px solid var(--border);color:white;font-weight:500;font-size:0.75rem;"
                                onclick="openProductDetail({{ $prod->id }})">
                            View Specifications
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
            <div class="modal-header border-bottom-0" style="padding:20px 24px 10px;">
                <h5 class="modal-title" style="font-family:'Outfit',sans-serif;font-weight:700;color:white;" id="modal-product-name"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:10px 24px 24px;">
                <div class="row g-3">
                    <div class="col-md-5">
                        <img id="modal-product-img" src="" alt="" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:12px;">
                    </div>
                    <div class="col-md-7">
                        <div id="modal-price-block" class="d-flex align-items-baseline gap-2 mb-3"></div>
                        <p id="modal-product-desc" style="font-size:0.875rem;color:var(--text-muted);line-height:1.6;"></p>
                        
                        <div id="modal-spec-block" class="mt-3">
                            <h6 style="font-family:'Outfit',sans-serif;font-weight:700;color:white;margin-bottom:8px;">Specifications</h6>
                            <table class="table table-dark table-borderless table-sm" style="--bs-table-bg:transparent;font-size:0.8rem;">
                                <tbody id="modal-spec-tbody"></tbody>
                            </table>
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
        priceBlock.innerHTML = `<div style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--primary);font-size:1.6rem;">₹${parseFloat(p.offer_price).toFixed(2)}</div>`;
        if (p.mrp) {
            priceBlock.innerHTML += `<div style="font-size:0.95rem;color:var(--text-muted);text-decoration:line-through;">₹${parseFloat(p.mrp).toFixed(2)}</div>`;
        }
    } else if (p.mrp) {
        priceBlock.innerHTML = `<div style="font-family:'Outfit',sans-serif;font-weight:800;color:white;font-size:1.6rem;">₹${parseFloat(p.mrp).toFixed(2)}</div>`;
    } else {
        priceBlock.innerHTML = `<div style="color:var(--text-muted);font-style:italic;font-size:0.9rem;">Contact for pricing</div>`;
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
                    <td style="color:var(--text-muted);width:150px;padding:6px 0;">${val.attribute.name}</td>
                    <td style="color:white;font-weight:600;padding:6px 0;">${displayVal} ${val.attribute.unit || ''}</td>
                `;
                tbody.appendChild(tr);
            }
        });
    }

    const modal = new bootstrap.Modal(document.getElementById('productDetailModal'));
    modal.show();
}
@endif
</script>
</body>
</html>
