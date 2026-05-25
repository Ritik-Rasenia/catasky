<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $primaryColor = $profile?->primary_color ?? '#4F46E5';
        $secondaryColor = $profile?->secondary_color ?? '#7C3AED';
        $companyName = $profile?->company_name ?? ($subscriber?->name ?? 'Catalog Share');
        $catalogPayload = (!$product && $catalogProducts)
            ? $catalogProducts->map(fn($prod) => [
                'id' => $prod->id,
                'name' => $prod->name,
                'sku' => $prod->sku,
                'mrp' => $prod->mrp,
                'offer_price' => $prod->offer_price,
                'discount_percentage' => $prod->discount_percentage,
                'thumbnail_url' => $prod->thumbnail_url,
                'thumbnail_srcset' => $prod->thumbnail_srcset,
                'preview_image_url' => $prod->preview_image_url,
                'short_description' => $prod->short_description,
                'category' => $prod->category?->name,
                'tags' => $prod->tags,
                'attribute_values' => $prod->attributeValues->map(fn($val) => [
                    'value' => $val->value,
                    'attribute' => $val->attribute ? [
                        'name' => $val->attribute->name,
                        'type' => $val->attribute->type,
                        'unit' => $val->attribute->unit,
                        'show_in_share' => $val->attribute->show_in_share,
                    ] : null,
                ])->values(),
            ])->values()
            : collect();
    @endphp
    <title>{{ $link->title }} | {{ $companyName }}</title>
    
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
            padding-bottom: 100px; /* Space for floating bottom bar */
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        /* ─── Floating Bottom Bar ──────────────────────── */
        .floating-bar-wrap {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 720px;
            z-index: 1050;
        }

        .floating-bar {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 50px;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .btn-floating {
            border-radius: 40px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
        }

        .btn-floating-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white !important;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }

        .btn-floating-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5);
        }

        .btn-floating-outline {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.15);
            color: white !important;
        }

        .btn-floating-outline:hover {
            background: rgba(255,255,255,0.12);
            transform: translateY(-2px);
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
            padding: 50px 0 35px;
            text-align: center;
        }

        .share-hero-logo {
            width: 72px; height: 72px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; color: white;
            font-weight: 800; font-family: 'Outfit', sans-serif;
            margin: 0 auto 16px;
            border: 2px solid rgba(255,255,255,0.1);
            object-fit: cover;
        }

        .share-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 2.2rem;
            letter-spacing: -0.02em;
            background: linear-gradient(to right, #FFF, #D1D5DB);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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

        .catalog-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .catalog-search {
            min-width: 240px;
            flex: 1;
            max-width: 420px;
            color: #F8FAFC;
            background: rgba(15, 23, 42, 0.74);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            outline: none;
        }

        .catalog-skeleton {
            aspect-ratio: 4/3;
            border-radius: 12px;
            background: linear-gradient(90deg, rgba(255,255,255,.05) 25%, rgba(255,255,255,.11) 37%, rgba(255,255,255,.05) 63%);
            background-size: 400% 100%;
            animation: shimmer 1.2s ease infinite;
        }

        @keyframes shimmer {
            0% { background-position: 100% 0; }
            100% { background-position: 0 0; }
        }

        @media (max-width: 575.98px) {
            .catalog-controls {
                align-items: stretch;
                flex-direction: column;
            }
            .catalog-search {
                min-width: 0;
                max-width: none;
            }
            .floating-bar {
                border-radius: 16px;
                flex-direction: column;
                align-items: stretch;
            }
            .btn-floating {
                justify-content: center;
            }
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
        <h1 class="share-title">{{ $link->title }}</h1>
        <p class="text-muted mb-0" style="font-size:0.9rem;">
            Offered by <span style="color:#FFF;font-weight:600;">{{ $companyName }}</span>
            @if($profile?->website)
                · <a href="{{ $profile->website }}" target="_blank" style="color:var(--primary);text-decoration:none;">{{ str_replace(['https://','http://','www.'], '', $profile->website) }}</a>
            @endif
        </p>
    </div>

    {{-- Main catalog view (Grid of products) --}}
    @if(!$product)
        {{-- Catalog share --}}
        <div class="catalog-controls">
            <input id="catalog-search" class="catalog-search" type="search" placeholder="Search products, SKU, category..." autocomplete="off">
            <div style="color:var(--text-muted);font-size:0.82rem;">
                <span id="catalog-render-count">0</span> of {{ $catalogPayload->count() }} rendered
            </div>
        </div>
        <div class="row g-3" id="catalog-grid" aria-live="polite">
            @for($i = 0; $i < min(8, $catalogPayload->count()); $i++)
            <div class="col-sm-6 col-md-4 col-lg-3"><div class="catalog-skeleton"></div></div>
            @endfor
        </div>

    @else
        {{-- Single product share --}}
        <div class="glass-card p-4">
            <div class="row g-4">
                {{-- Product gallery --}}
                <div class="col-md-5">
                    <div style="position:relative;background:#111827;border-radius:14px;overflow:hidden;border:1px solid var(--border);">
                        <img id="main-active-img" src="{{ $product->preview_image_url }}" srcset="{{ $product->thumbnail_srcset }}" sizes="(max-width: 767px) 100vw, 42vw" alt="" loading="eager" decoding="async" style="width:100%;aspect-ratio:1/1;object-fit:cover;">
                    </div>
                    @if($product->images->count() > 0 && ($settings['show_images'] ?? true))
                    <div class="d-flex gap-2 overflow-x-auto mt-2 pb-1">
                        <img src="{{ $product->thumbnail_url }}" onclick="setGalleryActive(this.src)" loading="lazy" decoding="async" style="height:55px;width:55px;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid var(--primary);">
                        @foreach($product->images as $img)
                        <img src="{{ $img->image_url }}" onclick="setGalleryActive(this.src)" loading="lazy" decoding="async" style="height:55px;width:55px;object-fit:cover;border-radius:8px;cursor:pointer;border:1px solid var(--border);">
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Product details --}}
                <div class="col-md-7">
                    <h2 style="font-family:'Outfit',sans-serif;font-weight:800;color:white;margin-bottom:8px;">{{ $product->name }}</h2>
                    @if($product->sku)
                        <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:12px;">SKU: <strong style="color:white;">{{ $product->sku }}</strong></div>
                    @endif

                    <hr style="border-color:rgba(255,255,255,0.1);margin:16px 0;">

                    {{-- Pricing --}}
                    <div class="d-flex align-items-baseline gap-3 mb-3">
                        @if($product->offer_price && ($settings['show_offer_price'] ?? true))
                            <div style="font-family:'Outfit',sans-serif;font-size:1.8rem;font-weight:800;color:var(--primary);">₹{{ number_format($product->offer_price, 2) }}</div>
                            @if($product->mrp && ($settings['show_mrp'] ?? true))
                                <div style="font-size:1rem;color:var(--text-muted);text-decoration:line-through;">₹{{ number_format($product->mrp, 2) }}</div>
                                <span class="badge-discount">{{ $product->discount_percentage }}% OFF</span>
                            @endif
                        @elseif($product->mrp && ($settings['show_mrp'] ?? true))
                            <div style="font-family:'Outfit',sans-serif;font-size:1.8rem;font-weight:800;color:white;">₹{{ number_format($product->mrp, 2) }}</div>
                        @else
                            <div style="color:var(--text-muted);font-style:italic;">Contact Subscriber for Pricing</div>
                        @endif
                    </div>

                    @if($product->short_description && ($settings['show_description'] ?? true))
                        <p style="color:var(--text-muted);font-size:0.875rem;line-height:1.6;margin-bottom:16px;">{{ $product->short_description }}</p>
                    @endif

                    @if($product->full_description && ($settings['show_description'] ?? true))
                        <div style="font-size:0.85rem;color:var(--text-muted);line-height:1.6;margin-bottom:24px;">
                            <strong style="color:white;display:block;margin-bottom:6px;">Product Description</strong>
                            {!! nl2br(e($product->full_description)) !!}
                        </div>
                    @endif

                    {{-- Dynamic attributes spec sheet --}}
                    @if($product->attributeValues->count() > 0 && ($settings['show_attributes'] ?? true))
                    <div class="mt-4 pt-3 border-top" style="border-color:rgba(255,255,255,0.08);">
                        <h6 style="font-family:'Outfit',sans-serif;font-weight:700;color:white;margin-bottom:12px;">Technical Specifications</h6>
                        <div class="table-responsive">
                            <table class="table table-dark table-borderless" style="--bs-table-bg:transparent;font-size:0.82rem;">
                                <tbody>
                                    @foreach($product->attributeValues as $val)
                                    @if($val->attribute?->show_in_share)
                                    @php 
                                        $displayVal = $val->value;
                                        if ($val->attribute?->type === 'multiselect' || $val->attribute?->type === 'checkbox') {
                                            $arr = json_decode($val->value, true) ?? [$val->value];
                                            $displayVal = implode(', ', $arr);
                                        }
                                    @endphp
                                    <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                                        <td style="width:200px;color:var(--text-muted);font-weight:500;padding:8px 0;">{{ $val->attribute?->name }}</td>
                                        <td style="color:white;font-weight:600;padding:8px 0;">
                                            @if($val->attribute?->type === 'color')
                                                <span style="display:inline-block;width:16px;height:16px;border-radius:4px;background-color:{{ $displayVal }};border:1px solid rgba(255,255,255,0.2);vertical-align:middle;margin-right:6px;"></span>
                                                <span>{{ $displayVal }}</span>
                                            @elseif($val->attribute?->type === 'url')
                                                <a href="{{ $displayVal }}" target="_blank" style="color:var(--primary);text-decoration:none;">{{ $displayVal }}</a>
                                            @else
                                                {{ $displayVal }} {{ $val->attribute?->unit }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    @endif
</div>

{{-- ─── FLOATING BOTTOM ACTION BAR ─────────────────── --}}
<div class="floating-bar-wrap">
    <div class="floating-bar">
        <div class="d-flex align-items-center gap-2">
            @if($profile?->company_name)
                <span style="font-family:'Outfit',sans-serif;font-weight:700;color:white;font-size:0.9rem;">{{ Str::limit($profile->company_name, 16) }}</span>
            @endif
        </div>
        
        <div class="d-flex align-items-center gap-2">
            @if($settings['allow_download'] ?? true)
                <a href="{{ route('subscriber.share.pdf', $link->token) }}" class="btn-floating btn-floating-primary">
                    <i class="bi bi-file-pdf"></i> Download PDF
                </a>
            @endif
            
            <a href="{{ route('subscriber.share.gallery', $link->token) }}" class="btn-floating btn-floating-outline d-none d-sm-inline-flex">
                <i class="bi bi-images"></i> Images
            </a>

            @if(($settings['show_contact'] ?? true) && $profile?->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile->phone) }}" target="_blank" class="btn-floating btn-floating-outline" style="border-color:#25D366;color:#25D366 !important;">
                    <i class="bi bi-whatsapp"></i> Chat
                </a>
            @endif
        </div>
    </div>
</div>

{{-- Detail Modal for catalog view --}}
@if(!$product)
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
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setGalleryActive(src) {
    document.getElementById('main-active-img').src = src;
    
    // Highlight thumbnail
    document.querySelectorAll('.col-md-5 img').forEach(img => {
        if (img.src === src) {
            img.style.borderColor = 'var(--primary)';
        } else {
            img.style.borderColor = 'var(--border)';
        }
    });
}

@if(!$product)
const catalogProductList = @json($catalogPayload);
const catalogProducts = new Map(catalogProductList.map(product => [Number(product.id), product]));
const catalogGrid = document.getElementById('catalog-grid');
const catalogSearch = document.getElementById('catalog-search');
const renderCount = document.getElementById('catalog-render-count');
let filteredProducts = catalogProductList;
let visibleCount = 16;
let renderTick = null;
const cShowOffer = {{ ($settings['show_offer_price'] ?? true) ? 'true' : 'false' }};
const cShowMrp = {{ ($settings['show_mrp'] ?? true) ? 'true' : 'false' }};
const cShowDescription = {{ ($settings['show_description'] ?? true) ? 'true' : 'false' }};

function productCard(product) {
    const price = product.offer_price && cShowOffer
        ? `<div style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--primary);font-size:1.05rem;">Rs. ${Number(product.offer_price).toLocaleString('en-IN')}</div>${product.mrp && cShowMrp ? `<div style="font-size:0.75rem;color:var(--text-muted);text-decoration:line-through;">Rs. ${Number(product.mrp).toLocaleString('en-IN')}</div>` : ''}`
        : (product.mrp && cShowMrp ? `<div style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--text);font-size:1.05rem;">Rs. ${Number(product.mrp).toLocaleString('en-IN')}</div>` : `<div style="color:var(--text-muted);font-style:italic;font-size:0.8rem;">Contact for Price</div>`);

    return `
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="pub-product-card">
                <div class="pub-img-wrap">
                    <img src="${product.thumbnail_url}" srcset="${product.thumbnail_srcset || ''}" sizes="(max-width: 575px) 50vw, (max-width: 991px) 33vw, 25vw" alt="" class="pub-product-img" loading="lazy" decoding="async">
                </div>
                <div class="p-3 d-flex flex-column flex-grow-1">
                    <h6 style="font-family:'Outfit',sans-serif;font-weight:700;margin-bottom:6px;line-height:1.4;">${product.name}</h6>
                    ${cShowDescription && product.short_description ? `<p class="text-muted" style="font-size:0.78rem;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;flex-grow:1;">${product.short_description}</p>` : ''}
                    <div class="d-flex align-items-baseline gap-2 mt-auto pt-2 border-top" style="border-color:rgba(255,255,255,0.06);">${price}</div>
                    <button type="button" class="btn btn-sm mt-3" style="border-radius:8px;background:rgba(255,255,255,0.05);border:1px solid var(--border);color:white;font-weight:500;font-size:0.75rem;" onclick="openProductDetail(${product.id})">
                        View Specifications
                    </button>
                </div>
            </div>
        </div>
    `;
}

function renderCatalog() {
    cancelAnimationFrame(renderTick);
    renderTick = requestAnimationFrame(() => {
        const products = filteredProducts.slice(0, visibleCount);
        catalogGrid.innerHTML = products.map(productCard).join('');
        renderCount.textContent = products.length;
    });
}

function filterCatalog() {
    const term = catalogSearch.value.trim().toLowerCase();
    visibleCount = 16;
    filteredProducts = !term ? catalogProductList : catalogProductList.filter(product => {
        return [product.name, product.sku, product.category, (product.tags || []).join(' ')]
            .join(' ')
            .toLowerCase()
            .includes(term);
    });
    renderCatalog();
}

function debounce(fn, wait = 120) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), wait);
    };
}

catalogSearch.addEventListener('input', debounce(filterCatalog, 120));
window.addEventListener('scroll', () => {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 900 && visibleCount < filteredProducts.length) {
        visibleCount += 16;
        renderCatalog();
    }
}, { passive: true });
renderCatalog();

function openProductDetail(productId) {
    const p = catalogProducts.get(Number(productId));
    if (!p) return;

    document.getElementById('modal-product-name').textContent = p.name;
    document.getElementById('modal-product-img').src = p.preview_image_url || p.thumbnail_url;
    document.getElementById('modal-product-desc').textContent = p.short_description || '';

    // Price block
    const priceBlock = document.getElementById('modal-price-block');
    priceBlock.innerHTML = '';
    
    const showOffer = {{ ($settings['show_offer_price'] ?? true) ? 'true' : 'false' }};
    const showMrp = {{ ($settings['show_mrp'] ?? true) ? 'true' : 'false' }};

    if (p.offer_price && showOffer) {
        priceBlock.innerHTML = `<div style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--primary);font-size:1.6rem;">₹${parseFloat(p.offer_price).toFixed(2)}</div>`;
        if (p.mrp && showMrp) {
            priceBlock.innerHTML += `<div style="font-size:0.95rem;color:var(--text-muted);text-decoration:line-through;">₹${parseFloat(p.mrp).toFixed(2)}</div>`;
        }
    } else if (p.mrp && showMrp) {
        priceBlock.innerHTML = `<div style="font-family:'Outfit',sans-serif;font-weight:800;color:white;font-size:1.6rem;">₹${parseFloat(p.mrp).toFixed(2)}</div>`;
    } else {
        priceBlock.innerHTML = `<div style="color:var(--text-muted);font-style:italic;font-size:0.9rem;">Contact for pricing</div>`;
    }

    // Specifications
    const tbody = document.getElementById('modal-spec-tbody');
    tbody.innerHTML = '';
    
    const showAttrs = {{ ($settings['show_attributes'] ?? true) ? 'true' : 'false' }};
    if (p.attribute_values && p.attribute_values.length > 0 && showAttrs) {
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
