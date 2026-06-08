<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $product = $link->product;
        $subscriber = $link->subscriber;
        $profile = $subscriber?->subscriberProfile;
        $primaryColor = $profile?->primary_color ?? '#4F46E5';
        $secondaryColor = $profile?->secondary_color ?? '#7C3AED';
        $companyName = $profile?->company_name ?? ($subscriber?->name ?? 'Catalog Gallery');
        
        // Load catalog products with images if catalog-wide share and the controller did not preload them.
        if (!$product && !isset($catalogProducts)) {
            $catalogProducts = \App\Models\SubscriberProduct::where('user_id', $subscriber->id)
                ->where('status', 'active')
                ->with('images')
                ->orderBy('sort_order')
                ->get();
        }
        $imageSharePayload = ($product ? collect([$product]) : ($catalogProducts ?? collect()))->map(fn($prod) => [
            'id' => $prod->id,
            'name' => $prod->name,
            'sku' => $prod->sku,
            'price' => $prod->offer_price ?: $prod->mrp,
            'image' => $prod->share_image_url,
            'thumbnail' => $prod->thumbnail_url,
            'description' => \Illuminate\Support\Str::limit($prod->short_description ?? '', 95),
        ])->values();
    @endphp
    <title>Image Gallery | {{ $link->title }} | {{ $companyName }}</title>
    
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
            padding-bottom: 110px; /* Space for floating bottom bar */
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .gallery-header {
            padding: 35px 0 20px;
            text-align: center;
        }

        .gallery-logo {
            width: 64px; height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; color: white;
            font-weight: 800; font-family: 'Outfit', sans-serif;
            margin: 0 auto 12px;
            border: 2px solid rgba(255,255,255,0.1);
            object-fit: cover;
        }

        .gallery-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(to right, #FFF, #D1D5DB);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ─── Main Gallery Layout ─── */
        .viewer-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .active-img-wrap {
            position: relative;
            background: #090D16;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            aspect-ratio: 16/9;
        }

        @media (max-width: 767.98px) {
            .active-img-wrap {
                aspect-ratio: 4/3;
            }
        }

        .active-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: opacity 0.25s ease-in-out;
        }

        .active-img.fade-out {
            opacity: 0;
        }

        .thumbnail-carousel {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 8px 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
        }

        .thumbnail-carousel::-webkit-scrollbar {
            height: 5px;
        }
        .thumbnail-carousel::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
        }

        .thumb-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            border: 2px solid transparent;
            opacity: 0.6;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .thumb-img.active {
            border-color: var(--primary);
            opacity: 1;
            transform: scale(1.05);
        }

        .thumb-img:hover {
            opacity: 0.9;
        }

        /* Sidebar catalog selector for large screens */
        .product-sidebar-list {
            max-height: 520px;
            overflow-y: auto;
            border-radius: 14px;
        }

        .product-sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .product-sidebar-item:last-child {
            border-bottom: none;
        }

        .product-sidebar-item:hover {
            background: rgba(255,255,255,0.04);
        }

        .product-sidebar-item.active {
            background: rgba(79, 70, 229, 0.15);
            border-left: 3px solid var(--primary);
        }

        .product-sidebar-thumb {
            width: 44px; height: 44px;
            border-radius: 8px;
            object-fit: cover;
            background: #0F172A;
        }

        /* ─── Floating Bottom Bar ─── */
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

        .gallery-controls {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            width: 44px; height: 44px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 10;
        }

        .gallery-controls:hover {
            background: var(--primary);
            scale: 1.1;
        }

        .gallery-prev { left: 16px; }
        .gallery-next { right: 16px; }

        .batch-tools {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            padding: 14px;
        }

        .batch-progress {
            flex: 1;
            height: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
        }

        .batch-progress span {
            display: block;
            width: 0%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--primary), #14B8A6);
            transition: width .18s ease;
        }

        @media (max-width: 575.98px) {
            .batch-tools,
            .floating-bar {
                align-items: stretch;
                flex-direction: column;
                border-radius: 16px;
            }
            .btn-floating,
            .btn-floating-primary,
            .btn-floating-outline {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="container">
    {{-- Header --}}
    <div class="gallery-header">
        @if($profile?->logo)
            <img class="gallery-logo" src="{{ asset('uploads/subscriber-logos/' . $profile->logo) }}" alt="Logo">
        @else
            <div class="gallery-logo">{{ strtoupper(substr($companyName, 0, 1)) }}</div>
        @endif
        <h1 class="gallery-title">{{ $link->title }} — Visual Gallery</h1>
        <p class="text-muted mb-0" style="font-size:0.9rem;">
            Branded Assets by <span style="color:#FFF;font-weight:600;">{{ $companyName }}</span>
        </p>
    </div>

    <div class="glass-card batch-tools">
        <div>
            <div style="font-family:'Outfit',sans-serif;font-weight:700;color:white;">Fast Image Export</div>
            <div id="batch-status" style="font-size:0.78rem;color:var(--text-muted);">Templates preloaded. Ready for batch sharing.</div>
        </div>
        <div class="batch-progress"><span id="batch-progress-bar"></span></div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn-floating btn-floating-outline" id="btn-generate-current">
                <i class="bi bi-image"></i> Current
            </button>
            <button type="button" class="btn-floating btn-floating-primary" id="btn-generate-bulk">
                <i class="bi bi-lightning-charge"></i> Batch
            </button>
        </div>
    </div>

    @if($product)
        {{-- Single Product Gallery --}}
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="glass-card p-4">
                    <div class="viewer-container">
                        {{-- Main Active Image --}}
                        <div class="active-img-wrap">
                            <img id="main-gallery-img" src="{{ $product->preview_image_url }}" alt="" class="active-img" loading="eager" decoding="async">
                            
                            @php 
                                $tempImages = collect([$product->preview_image_url])
                                    ->concat($product->images->pluck('preview_url'))
                                    ->filter()
                                    ->values();
                                
                                $shownUrls = [];
                                $shownBasenames = [];
                                $allImages = collect();

                                foreach ($tempImages as $url) {
                                    $basename = basename(parse_url($url, PHP_URL_PATH));
                                    if (!in_array($url, $shownUrls) && !in_array($basename, $shownBasenames)) {
                                        $shownUrls[] = $url;
                                        $shownBasenames[] = $basename;
                                        $allImages->push($url);
                                    }
                                }
                            @endphp

                            @if($allImages->count() > 1)
                                <button class="gallery-controls gallery-prev" onclick="prevImg()"><i class="bi bi-chevron-left"></i></button>
                                <button class="gallery-controls gallery-next" onclick="nextImg()"><i class="bi bi-chevron-right"></i></button>
                            @endif
                        </div>

                        {{-- Action buttons for active asset --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2">
                            <div>
                                <h5 class="mb-1" style="font-family:'Outfit',sans-serif;font-weight:700;color:white;">{{ $product->name }}</h5>
                                <p class="text-muted mb-0" style="font-size:0.8rem;">Image <span id="current-index-label">1</span> of {{ $allImages->count() }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a id="btn-download-asset" href="{{ $product->thumbnail_url }}" download class="btn btn-sm btn-outline-light" style="border-radius:10px;padding:8px 16px;font-weight:500;">
                                    <i class="bi bi-download me-1"></i> Download Asset
                                </a>
                                @if($profile?->phone)
                                    <a id="btn-wa-inquire" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile->phone) }}?text=Hi, I am interested in this product: {{ urlencode($product->name) }}" target="_blank" class="btn btn-sm btn-success" style="border-radius:10px;padding:8px 16px;font-weight:500;background-color:#25D366;border-color:#25D366;">
                                        <i class="bi bi-whatsapp me-1"></i> WhatsApp Inquiry
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Thumbnail strip --}}
                        <div class="thumbnail-carousel mt-2">
                            @foreach($allImages as $idx => $imgUrl)
                                <img src="{{ $imgUrl }}" onclick="setActiveImg({{ $idx }})" class="thumb-img {{ $idx === 0 ? 'active' : '' }}" data-index="{{ $idx }}" loading="lazy" decoding="async">
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- Catalog Wide Gallery (Multi-Product Selection Sidebar) --}}
        <div class="row g-4">
            {{-- Product Selection Sidebar --}}
            <div class="col-md-4 col-lg-3">
                <div class="glass-card p-3">
                    <h6 class="mb-3" style="font-family:'Outfit',sans-serif;font-weight:700;color:white;letter-spacing:0.04em;">CATALOG PRODUCTS</h6>
                    <div class="product-sidebar-list">
                        @foreach($catalogProducts as $idx => $prod)
                            @php 
                                $tempImages = collect([$prod->thumbnail_url])
                                    ->concat($prod->images->pluck('image_url'))
                                    ->filter()
                                    ->values();
                                $shownUrls = [];
                                $shownBasenames = [];
                                $pImages = collect();
                                foreach ($tempImages as $url) {
                                    $basename = basename(parse_url($url, PHP_URL_PATH));
                                    if (!in_array($url, $shownUrls) && !in_array($basename, $shownBasenames)) {
                                        $shownUrls[] = $url;
                                        $shownBasenames[] = $basename;
                                        $pImages->push($url);
                                    }
                                }
                            @endphp
                            @if($pImages->count() > 0)
                                <div class="product-sidebar-item {{ $idx === 0 ? 'active' : '' }}" onclick="selectCatalogProduct({{ $prod->id }}, this)">
                                    <img src="{{ $prod->thumbnail_url }}" alt="" class="product-sidebar-thumb" loading="lazy" decoding="async">
                                    <div class="overflow-hidden">
                                        <div style="font-weight:600;font-size:0.85rem;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $prod->name }}</div>
                                        <div class="text-muted" style="font-size:0.75rem;">{{ $pImages->count() }} Images</div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Slider Box --}}
            <div class="col-md-8 col-lg-9">
                <div class="glass-card p-4">
                    <div class="viewer-container">
                        {{-- Main Active Image --}}
                        <div class="active-img-wrap">
                            <img id="main-gallery-img" src="" alt="" class="active-img">
                            
                            <button class="gallery-controls gallery-prev" onclick="prevImg()"><i class="bi bi-chevron-left"></i></button>
                            <button class="gallery-controls gallery-next" onclick="nextImg()"><i class="bi bi-chevron-right"></i></button>
                        </div>

                        {{-- Details --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2">
                            <div>
                                <h5 id="gallery-prod-title" class="mb-1" style="font-family:'Outfit',sans-serif;font-weight:700;color:white;"></h5>
                                <p class="text-muted mb-0" style="font-size:0.8rem;">Image <span id="current-index-label">1</span> of <span id="total-count-label">1</span></p>
                            </div>
                            <div class="d-flex gap-2">
                                <a id="btn-download-asset" href="" download class="btn btn-sm btn-outline-light" style="border-radius:10px;padding:8px 16px;font-weight:500;">
                                    <i class="bi bi-download me-1"></i> Download Asset
                                </a>
                                @if($profile?->phone)
                                    <a id="btn-wa-inquire" href="" target="_blank" class="btn btn-sm btn-success" style="border-radius:10px;padding:8px 16px;font-weight:500;background-color:#25D366;border-color:#25D366;">
                                        <i class="bi bi-whatsapp me-1"></i> WhatsApp Inquiry
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Thumb strip --}}
                        <div id="gallery-thumbnails" class="thumbnail-carousel mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Floating bottom bar --}}
<div class="floating-bar-wrap">
    <div class="floating-bar">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('subscriber.share.public', $link->token) }}" class="btn-floating btn-floating-outline">
                <i class="bi bi-arrow-left"></i> Back to Catalog
            </a>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            @if($link->settings['allow_download'] ?? true)
                <a href="{{ route('subscriber.share.pdf', $link->token) }}" class="btn-floating btn-floating-primary">
                    <i class="bi bi-file-pdf"></i> Download PDF
                </a>
            @endif

            @if(($link->settings['show_contact'] ?? true) && $profile?->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile->phone) }}" target="_blank" class="btn-floating btn-floating-outline" style="border-color:#25D366;color:#25D366 !important;">
                    <i class="bi bi-whatsapp"></i> Chat
                </a>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let images = [];
    let currentIndex = 0;
    const phoneNo = "{{ $profile?->phone ? preg_replace('/[^0-9]/', '', $profile->phone) : '' }}";
    const imageShareProducts = @json($imageSharePayload);
    const batchStatus = document.getElementById('batch-status');
    const batchBar = document.getElementById('batch-progress-bar');

    @if($product)
        // Bind single product images
        images = {!! json_encode($allImages) !!};
        initGallery();
    @else
        // Catalog data structured
        const catalogProducts = {!! json_encode($catalogProducts->keyBy('id')) !!};
        
        function selectCatalogProduct(id, element) {
            // Remove active classes from sidebar
            document.querySelectorAll('.product-sidebar-item').forEach(item => item.classList.remove('active'));
            if(element) element.classList.add('active');

            const p = catalogProducts[id];
            if(!p) return;

            // Collect and deduplicate images
            const rawImages = [];
            if(p.preview_image_url || p.thumbnail_url) rawImages.push(p.preview_image_url || p.thumbnail_url);
            if(p.images && p.images.length > 0) {
                p.images.forEach(img => rawImages.push(img.preview_url || img.image_url));
            }
            
            images = [];
            const shownUrls = new Set();
            const shownBasenames = new Set();
            
            rawImages.forEach(url => {
                if (!url) return;
                let basename = '';
                try {
                    const urlObj = new URL(url, window.location.origin);
                    const parts = urlObj.pathname.split('/');
                    basename = parts[parts.length - 1];
                } catch(e) {
                    const parts = url.split('/');
                    basename = parts[parts.length - 1];
                }
                
                if (!shownUrls.has(url) && !shownBasenames.has(basename)) {
                    shownUrls.add(url);
                    shownBasenames.add(basename);
                    images.push(url);
                }
            });

            // Set titles
            document.getElementById('gallery-prod-title').textContent = p.name;
            if(document.getElementById('btn-wa-inquire')) {
                document.getElementById('btn-wa-inquire').href = `https://wa.me/${phoneNo}?text=Hi, I am interested in this product: ${encodeURIComponent(p.name)}`;
            }

            // Rebuild thumbnails container
            const container = document.getElementById('gallery-thumbnails');
            container.innerHTML = '';
            images.forEach((imgUrl, idx) => {
                const img = document.createElement('img');
                img.src = imgUrl;
                img.className = 'thumb-img' + (idx === 0 ? ' active' : '');
                img.setAttribute('data-index', idx);
                img.onclick = () => setActiveImg(idx);
                container.appendChild(img);
            });

            currentIndex = 0;
            renderActiveImage();
        }

        // Initialize with first product
        const firstProdId = "{{ $catalogProducts->first()?->id }}";
        if(firstProdId) {
            selectCatalogProduct(firstProdId, document.querySelector('.product-sidebar-item'));
        }
    @endif

    function initGallery() {
        currentIndex = 0;
        renderActiveImage();
    }

    function setActiveImg(idx) {
        currentIndex = idx;
        renderActiveImage();
    }

    function prevImg() {
        if(images.length <= 1) return;
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        renderActiveImage();
    }

    function nextImg() {
        if(images.length <= 1) return;
        currentIndex = (currentIndex + 1) % images.length;
        renderActiveImage();
    }

    function renderActiveImage() {
        if(images.length === 0) return;

        const mainImg = document.getElementById('main-gallery-img');
        
        // Add fade transition
        mainImg.classList.add('fade-out');
        
        setTimeout(() => {
            mainImg.src = images[currentIndex];
            mainImg.classList.remove('fade-out');
            
            // Update labels
            document.getElementById('current-index-label').textContent = currentIndex + 1;
            if(document.getElementById('total-count-label')) {
                document.getElementById('total-count-label').textContent = images.length;
            }
            
            // Update download button
            document.getElementById('btn-download-asset').href = images[currentIndex];

            // Highlight thumbnail
            document.querySelectorAll('.thumb-img').forEach(img => {
                const idx = parseInt(img.getAttribute('data-index'));
                if(idx === currentIndex) {
                    img.classList.add('active');
                    img.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    img.classList.remove('active');
                }
            });
        }, 150);
    }

    async function loadImage(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.decoding = 'async';
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = url;
        });
    }

    async function renderProductShareCard(product) {
        const canvas = document.createElement('canvas');
        canvas.width = 1200;
        canvas.height = 1500;
        const ctx = canvas.getContext('2d', { alpha: false });
        ctx.fillStyle = '#F8FAFC';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '{{ $primaryColor }}';
        ctx.fillRect(0, 0, canvas.width, 120);
        ctx.fillStyle = '#FFFFFF';
        ctx.font = '700 42px Arial';
        ctx.fillText('{{ addslashes($companyName) }}', 64, 76);

        try {
            const img = await loadImage(product.image || product.thumbnail);
            const size = 980;
            const x = 110;
            const y = 185;
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(x, y, size, size);
            const scale = Math.min(size / img.width, size / img.height);
            const width = img.width * scale;
            const height = img.height * scale;
            ctx.drawImage(img, x + (size - width) / 2, y + (size - height) / 2, width, height);
        } catch (error) {
            ctx.fillStyle = '#E2E8F0';
            ctx.fillRect(110, 185, 980, 980);
        }

        ctx.fillStyle = '#0F172A';
        ctx.font = '700 50px Arial';
        wrapText(ctx, product.name, 90, 1260, 1020, 58);
        ctx.fillStyle = '#475569';
        ctx.font = '400 30px Arial';
        wrapText(ctx, product.description || product.sku || 'Premium catalog product', 90, 1370, 1020, 40);
        if (product.price) {
            ctx.fillStyle = '{{ $primaryColor }}';
            ctx.font = '700 44px Arial';
            ctx.fillText('Rs. ' + Number(product.price).toLocaleString('en-IN'), 90, 1450);
        }

        return new Promise(resolve => canvas.toBlob(resolve, 'image/webp', 0.86));
    }

    function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
        const words = String(text).split(' ');
        let line = '';
        for (const word of words) {
            const testLine = line + word + ' ';
            if (ctx.measureText(testLine).width > maxWidth && line) {
                ctx.fillText(line, x, y);
                line = word + ' ';
                y += lineHeight;
            } else {
                line = testLine;
            }
        }
        ctx.fillText(line, x, y);
    }

    async function shareBlob(blob, filename) {
        const file = new File([blob], filename, { type: 'image/webp' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({ files: [file], title: filename });
            return;
        }
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.click();
        setTimeout(() => URL.revokeObjectURL(url), 1200);
    }

    async function generateImageBatch(limit = null) {
        const products = limit ? imageShareProducts.slice(0, limit) : imageShareProducts;
        const concurrency = 4;
        let completed = 0;
        batchStatus.textContent = `Generating ${products.length} optimized images...`;
        batchBar.style.width = '4%';

        for (let i = 0; i < products.length; i += concurrency) {
            const chunk = products.slice(i, i + concurrency);
            const blobs = await Promise.all(chunk.map(product => renderProductShareCard(product)));
            for (let j = 0; j < blobs.length; j++) {
                completed++;
                batchBar.style.width = `${Math.round((completed / products.length) * 100)}%`;
                await shareBlob(blobs[j], `${chunk[j].sku || 'catasky'}-${chunk[j].id}.webp`);
            }
            await new Promise(resolve => setTimeout(resolve, 20));
        }
        batchStatus.textContent = 'Image export complete.';
    }

    document.getElementById('btn-generate-current').addEventListener('click', () => generateImageBatch(1));
    document.getElementById('btn-generate-bulk').addEventListener('click', () => generateImageBatch());
</script>
</body>
</html>
