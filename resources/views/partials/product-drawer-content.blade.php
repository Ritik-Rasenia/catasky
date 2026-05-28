@php
    $thumbnail = $product->thumbnail;
    if (!filter_var($thumbnail, FILTER_VALIDATE_URL)) {
        $thumbnail = asset('uploads/products/' . $thumbnail);
    }
    $gallery = $product->images;
@endphp

<!-- Image Showcase with branding mockup overlay support -->
<div class="position-relative bg-light rounded-4 overflow-hidden mb-3 border p-3" style="aspect-ratio: 16/11; display:flex; align-items:center; justify-content:center;">
    <!-- Core Product Image -->
    <img src="{{ $thumbnail }}" id="drawer-main-preview-img" loading="lazy" decoding="async" style="max-height:100%; max-width:100%; object-fit:contain; mix-blend-mode:multiply; transition: all 0.3s ease;">
    
    <!-- Branding Logo Mockup Overlay -->
    <div id="mockup-logo-overlay" class="position-absolute top-50 start-50 translate-middle text-center p-2 rounded-3 border border-primary border-dashed bg-white bg-opacity-75 backdrop-blur-sm" style="display:none; max-width: 130px; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.15);">
        <i class="bi bi-patch-check-fill text-primary d-block fs-5 mb-1"></i>
        <span class="fw-bold text-dark d-block" style="font-size: 0.65rem; letter-spacing: 0.5px; text-transform: uppercase;">[ Your Brand ]</span>
        <span class="text-secondary d-block" style="font-size: 0.5rem;">Custom Engraved</span>
    </div>

    <!-- Category Floating pill -->
    <span class="position-absolute top-3 start-3 badge bg-white text-primary border  rounded-pill fw-bold" style="font-size: 0.75rem;">
        {{ $product->category->name ?? 'Corporate Elite' }}
    </span>
</div>

<!-- Gallery Swatch Thumbnails -->
@if(count($gallery) > 0)
    <div class="d-flex gap-2 overflow-x-auto pb-3 mb-4 border-bottom">
        <img src="{{ $thumbnail }}" class="img-swatch active" loading="lazy" decoding="async" onclick="swapDrawerImage('{{ $thumbnail }}', this)" style="width: 54px; height: 54px; border-radius: 8px; object-fit: contain; border: 2px solid var(--primary);">
        @foreach($gallery as $idx => $g)
            @php
                $gUrl = $g->image;
                if (!filter_var($gUrl, FILTER_VALIDATE_URL)) {
                    $gUrl = asset('uploads/products/gallery/' . $gUrl);
                }
            @endphp
            <img src="{{ $gUrl }}" class="img-swatch" loading="lazy" decoding="async" onclick="swapDrawerImage('{{ $gUrl }}', this)" style="width: 54px; height: 54px; border-radius: 8px; object-fit: contain; border: 2px solid var(--border);">
        @endforeach
    </div>
@endif

<!-- Core Metadata Sheet -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.3rem;">{{ $product->name }}</h4>
        @if($product->brand)
            <span class="badge bg-light text-secondary border rounded-3 fw-semibold small" style="font-size:0.75rem;">
                {{ $product->brand->name }}
            </span>
        @endif
    </div>
    
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill fw-bold py-1 px-3 small-text">
            {{ $product->part_code ?: 'MOQ: 100 pcs' }}
        </span>
        <span class="text-gradient fw-bold fs-5 ms-auto">
            @if($product->price)
                &#8377;{{ number_format($product->price, 2) }}
            @else
                {{ $product->variant ?: 'On Request' }}
            @endif
        </span>
    </div>

    <!-- Canva / Shopify Customization Mock Widget -->
    <div class="premium-card p-3 bg-light border-0 mb-4">
        <h6 class="fw-bold small text-secondary text-uppercase mb-3 d-flex justify-content-between align-items-center">
            <span>Canva Styling Customizer</span>
            <span class="badge bg-primary text-white rounded-pill" style="font-size: 0.6rem;">Exclusive</span>
        </h6>
        
        <!-- Color swatches selector -->
        <div class="mb-3">
            <label class="small text-secondary mb-2 d-block">Select Product Shell Color</label>
            <div class="d-flex align-items-center gap-1">
                <span class="color-dot active" style="background: #3B82F6;" onclick="selectSwatchColor(this, 'Royal Blue')"></span>
                <span class="color-dot" style="background: #10B981;" onclick="selectSwatchColor(this, 'Forest Green')"></span>
                <span class="color-dot" style="background: #F59E0B;" onclick="selectSwatchColor(this, 'Matte Amber')"></span>
                <span class="color-dot" style="background: #EF4444;" onclick="selectSwatchColor(this, 'Crimson Red')"></span>
                <span class="color-dot" style="background: #1E293B;" onclick="selectSwatchColor(this, 'Midnight Carbon')"></span>
                <span class="small-text text-secondary ms-2 fw-semibold" id="swatch-color-label">Royal Blue</span>
            </div>
        </div>

        <!-- Corporate Brand Overlay Switch -->
        <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center m-0">
            <label class="form-check-label small text-secondary fw-semibold" for="branding-preview-switch">Mockup Custom Laser Logo Overlay</label>
            <input class="form-check-input ms-0 premium-switch" type="checkbox" id="branding-preview-switch" style="width: 44px; height: 22px; cursor:pointer;" onchange="toggleLaserMockupOverlay(this)">
        </div>
    </div>

    <!-- Description & Spec Sheets Tabs -->
    <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 mb-3 small" id="drawerTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active py-2 rounded-3 fw-bold border-0 bg-transparent text-secondary" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-pane" type="button" role="tab" aria-controls="desc-pane" aria-selected="true" style="font-size: 0.8rem;">
                About Item
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link py-2 rounded-3 fw-bold border-0 bg-transparent text-secondary" id="spec-tab" data-bs-toggle="tab" data-bs-target="#spec-pane" type="button" role="tab" aria-controls="spec-pane" aria-selected="false" style="font-size: 0.8rem;">
                Specs Sheet
            </button>
        </li>
    </ul>

    <div class="tab-content text-secondary small p-1 mb-4" id="drawerTabsContent" style="font-size: 0.85rem; line-height: 1.5;">
        <div class="tab-pane fade show active" id="desc-pane" role="tabpanel" aria-labelledby="desc-tab" tabindex="0">
            {{ $product->short_description ?: 'High quality customized item suitable for corporate needs. Features durable construction and sleek layout.' }}
        </div>
        <div class="tab-pane fade" id="spec-pane" role="tabpanel" aria-labelledby="spec-tab" tabindex="0">
            <div class="d-grid gap-2">
                @if($product->part_code)
                    <div class="d-flex justify-content-between border-bottom pb-1">
                        <span class="fw-bold">Standard MOQ</span>
                        <span>{{ $product->part_code }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between border-bottom pb-1">
                    <span class="fw-bold">Lead Time</span>
                    <span>7 - 12 Working Days</span>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-1">
                    <span class="fw-bold">Variants Option</span>
                    <span>Standard Elite Shells</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">B2B Customization</span>
                    <span class="text-primary fw-bold">Laser / Screen print</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Drawer Sticky Selector actions -->
<div class="d-flex gap-2">
    <button class="btn btn-premium btn-premium-primary flex-grow-1 py-3 btn-drawer-select" onclick="toggleSelection('{{ $product->id }}', this); closeDrawer()">
        <i class="bi bi-bag-plus"></i> Add to Selection
    </button>
    <button class="btn btn-premium btn-premium-outline px-3 py-3" onclick="closeDrawer(); openSharingModal('whatsapp')" title="WhatsApp Share Product">
        <i class="bi bi-whatsapp text-success fs-5"></i>
    </button>
</div>

<script>
    // Drawer specific inner interaction handlers
    function swapDrawerImage(url, swatch) {
        $('#drawer-main-preview-img').attr('src', url);
        $('.img-swatch').css('border-color', 'var(--border)');
        $(swatch).css('border-color', 'var(--primary)');
    }

    function selectSwatchColor(dot, colorName) {
        $('.color-dot').removeClass('active');
        $(dot).addClass('active');
        $('#swatch-color-label').text(colorName);
    }

    function toggleLaserMockupOverlay(checkbox) {
        const overlay = $('#mockup-logo-overlay');
        if (checkbox.checked) {
            overlay.fadeIn(250);
            $('#drawer-main-preview-img').css('filter', 'grayscale(20%) opacity(85%)');
        } else {
            overlay.fadeOut(200);
            $('#drawer-main-preview-img').css('filter', 'none');
        }
    }
</script>
