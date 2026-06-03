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
            MOQ: {{ $product->moq ?? 100 }} pcs
        </span>
        <span class="text-gradient fw-bold fs-5 ms-auto">
            @if($product->price)
                &#8377;{{ number_format($product->price, 2) }}
            @else
                {{ $product->variant ?: '' }}
            @endif
        </span>
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
            {!! $product->short_description ?: 'High quality customized item suitable for corporate needs. Features durable construction and sleek layout.' !!}
        </div>
        <div class="tab-pane fade" id="spec-pane" role="tabpanel" aria-labelledby="spec-tab" tabindex="0">
            <div class="d-grid gap-2">
                @if($product->sku)
                    <div class="d-flex justify-content-between border-bottom pb-1">
                        <span class="fw-bold">SKU / Item Code</span>
                        <span>{{ $product->sku }}</span>
                    </div>
                @endif
                @if($product->part_code)
                    <div class="d-flex justify-content-between border-bottom pb-1">
                        <span class="fw-bold">Part Code</span>
                        <span>{{ $product->part_code }}</span>
                    </div>
                @endif
                @if($product->part_number)
                    <div class="d-flex justify-content-between border-bottom pb-1">
                        <span class="fw-bold">Part Number</span>
                        <span>{{ $product->part_number }}</span>
                    </div>
                @endif
                @if($product->stock)
                    <div class="d-flex justify-content-between border-bottom pb-1">
                        <span class="fw-bold">Stock Available</span>
                        <span>{{ $product->stock }} Units</span>
                    </div>
                @endif
                @if($product->tax)
                    <div class="d-flex justify-content-between border-bottom pb-1">
                        <span class="fw-bold">Tax Detail</span>
                        <span>{{ $product->tax }}% B2B Tax</span>
                    </div>
                @endif
                
                @php
                    $specs = [];
                    if($product->specifications) {
                        $specs = json_decode($product->specifications, true) ?: [];
                    }
                @endphp
                
                @forelse($specs as $key => $value)
                    @php
                        if (is_string($value)) {
                            $decoded = json_decode($value, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $value = implode(', ', $decoded);
                            }
                        } elseif (is_array($value)) {
                            $value = implode(', ', $value);
                        }
                    @endphp
                    <div class="d-flex justify-content-between border-bottom pb-1">
                        <span class="fw-bold">{{ $key }}</span>
                        <span>{{ $value }}</span>
                    </div>
                @empty
                    @if(!$product->sku && !$product->part_code && !$product->part_number && !$product->stock && !$product->tax)
                        <div class="text-muted text-center py-2" style="font-size:0.75rem;">No technical specifications defined for this product.</div>
                    @endif
                @endforelse
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
