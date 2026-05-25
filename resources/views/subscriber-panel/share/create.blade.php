@extends('subscriber-panel.layouts.app')

@section('title', 'Create Share Link')
@section('page-title', 'Create Share Link')
@section('breadcrumb', '<a href="' . route('subscriber.share.index') . '">Share Links</a> → Create')

@section('content')
@php
    $productPreviewPayload = $products->map(fn($prod) => [
        'id' => $prod->id,
        'name' => $prod->name,
        'sku' => $prod->sku,
        'mrp' => $prod->mrp,
        'offer_price' => $prod->offer_price,
        'thumbnail_url' => $prod->thumbnail_url,
        'thumbnail_srcset' => $prod->thumbnail_srcset,
        'short_description' => \Illuminate\Support\Str::limit($prod->short_description ?? '', 120),
    ])->values();
@endphp

@push('css')
<style>
    .share-workbench {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 16px;
    }
    .preview-shell {
        min-height: 580px;
        position: sticky;
        top: 86px;
    }
    .preview-toolbar {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: space-between;
        padding: 12px;
        border-bottom: 1px solid #E2E8F0;
    }
    .preview-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        padding: 12px;
    }
    .preview-stat {
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 10px;
        background: #F8FAFC;
    }
    .preview-stat strong {
        display: block;
        color: var(--text-primary);
        font-size: 1rem;
        line-height: 1.1;
    }
    .preview-stat span {
        color: #64748B;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .virtual-preview-list {
        height: 350px;
        overflow: auto;
        padding: 8px 12px 12px;
        contain: content;
    }
    .preview-product {
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
        min-height: 76px;
        padding: 9px;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        background: #FFFFFF;
        margin-bottom: 8px;
        content-visibility: auto;
        contain-intrinsic-size: 76px;
    }
    .preview-product img {
        width: 58px;
        height: 58px;
        border-radius: 8px;
        object-fit: cover;
        background: #EEF2F7;
    }
    .preview-product h6 {
        margin: 0 0 2px;
        color: var(--text-primary);
        font-size: 0.82rem;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .preview-product p {
        margin: 0;
        color: #64748B;
        font-size: 0.72rem;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .preview-price {
        color: var(--subscriber-primary);
        font-weight: 800;
        white-space: nowrap;
        font-size: 0.82rem;
    }
    .export-status {
        border-top: 1px solid #E2E8F0;
        padding: 12px;
    }
    .export-progress {
        height: 7px;
        background: #E2E8F0;
        border-radius: 999px;
        overflow: hidden;
    }
    .export-progress span {
        display: block;
        width: 12%;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--subscriber-primary), #14B8A6);
        transition: width .22s ease;
    }
    .skeleton-row {
        height: 76px;
        border-radius: 8px;
        margin: 8px 12px;
        background: linear-gradient(90deg, #F1F5F9 25%, #E2E8F0 37%, #F1F5F9 63%);
        background-size: 400% 100%;
        animation: shimmer 1.2s ease infinite;
    }
    @keyframes shimmer {
        0% { background-position: 100% 0; }
        100% { background-position: 0 0; }
    }
    @media (max-width: 991.98px) {
        .share-workbench { grid-template-columns: 1fr; }
        .preview-shell { position: static; min-height: auto; }
        .virtual-preview-list { height: 300px; }
    }
</style>
@endpush

<form action="{{ route('subscriber.share.store') }}" method="POST">
@csrf

<div class="share-workbench">
    {{-- Left: Configure Options --}}
    <div>
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-sliders me-2"></i>Configure Catalog Share</h6>
            </div>
            <div class="vp-card-body">
                
                {{-- Title & Target --}}
                <div class="vp-form-group">
                    <label class="vp-label">Share Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="vp-input @error('title') is-invalid @enderror" 
                           placeholder="e.g. Summer 2026 Switches Catalog" value="{{ old('title') }}" required>
                    @error('title') <div class="invalid-feedback d-block" style="color:#EF4444;font-size:0.78rem;">{{ $message }}</div> @enderror
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="vp-form-group">
                            <label class="vp-label">Share Type</label>
                            <select name="type" class="vp-select" required id="share-type">
                                <option value="catalog" {{ old('type') === 'catalog' ? 'selected' : '' }}>Catalog (Whole/Selected Catalog page)</option>
                                <option value="pdf" {{ old('type') === 'pdf' ? 'selected' : '' }}>PDF (Direct Download/Share link)</option>
                                <option value="image" {{ old('type') === 'image' ? 'selected' : '' }}>Images Gallery</option>
                                <option value="whatsapp" {{ old('type') === 'whatsapp' ? 'selected' : '' }}>WhatsApp Sharing</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="vp-form-group">
                            <label class="vp-label">Specific Product <small style="color:#94A3B8;">(Optional)</small></label>
                            <select name="subscriber_product_id" class="vp-select" id="product-select">
                                <option value="">-- Share Whole Catalog --</option>
                                @foreach($products as $prod)
                                <option value="{{ $prod->id }}" {{ (old('subscriber_product_id') == $prod->id || ($selectedProduct && $selectedProduct->id == $prod->id)) ? 'selected' : '' }}>
                                    {{ $prod->name }} {{ $prod->sku ? '['.$prod->sku.']' : '' }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted" style="font-size:0.72rem;margin-top:4px;display:block;">If left empty, all active products will be included in the share catalog.</small>
                        </div>
                    </div>
                </div>

                <div class="vp-form-group">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <label class="vp-label mb-0">Selected Catalog Products</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-light border" id="select-all-products">
                                <i class="bi bi-check2-square"></i> Select all
                            </button>
                            <button type="button" class="btn btn-sm btn-light border" id="clear-products">
                                <i class="bi bi-x-circle"></i> Clear
                            </button>
                        </div>
                    </div>
                    <select name="selected_product_ids[]" class="vp-select mt-2" id="product-multi-select" multiple size="10">
                        @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ ($selectedProduct && $selectedProduct->id == $prod->id) ? 'selected' : '' }}>
                            {{ $prod->name }} {{ $prod->sku ? '['.$prod->sku.']' : '' }}
                        </option>
                        @endforeach
                    </select>
                    <small class="text-muted" style="font-size:0.72rem;margin-top:4px;display:block;">Use this for fast selected-catalog demos. If empty, the public share uses every active product.</small>
                </div>

                {{-- Date expiry --}}
                <div class="vp-form-group">
                    <label class="vp-label">Link Expiry Date <small style="color:#94A3B8;">(Optional)</small></label>
                    <input type="datetime-local" name="expires_at" class="vp-input" value="{{ old('expires_at') }}">
                    <small class="text-muted" style="font-size:0.72rem;margin-top:4px;display:block;">Leave blank for a permanent link. You can manually delete it anytime.</small>
                </div>

            </div>
        </div>
    </div>

    {{-- Right: Visibility Overrides --}}
    <div>
        <div class="vp-card mb-3">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-eye-slash me-2"></i>Visibility Settings</h6>
                <small style="color:#94A3B8;font-size:0.72rem;">Override visibility rules</small>
            </div>
            <div class="vp-card-body">
                @foreach([
                    ['show_mrp', 'Show MRP (Original Price)', true],
                    ['show_offer_price', 'Show Offer/Discounted Price', true],
                    ['show_description', 'Show Descriptions', true],
                    ['show_attributes', 'Show Custom Attributes', true],
                    ['show_images', 'Show Gallery/Additional Images', true],
                    ['show_contact', 'Show Contact Buttons', true],
                    ['allow_download', 'Allow PDF Downloads', true],
                ] as [$field, $label, $default])
                <label class="vp-toggle mb-3 d-flex">
                    <input type="checkbox" name="{{ $field }}" {{ old($field, $default) ? 'checked' : '' }}>
                    <span class="vp-toggle-label">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn-subscriber">
                <i class="bi bi-link-45deg"></i> Generate Share Link
            </button>
            <a href="{{ route('subscriber.share.index') }}" class="btn-subscriber-outline text-center">
                Cancel
            </a>
        </div>

        <div class="vp-card preview-shell mt-3">
            <div class="preview-toolbar">
                <div>
                    <h6 class="vp-card-title mb-0"><i class="bi bi-lightning-charge me-2"></i>Instant Preview</h6>
                    <small style="color:#64748B;font-size:0.72rem;">Virtualized for large catalogs</small>
                </div>
                <span class="badge badge-active" id="preview-mode">Ready</span>
            </div>
            <div class="preview-stats">
                <div class="preview-stat"><strong id="stat-products">0</strong><span>Products</span></div>
                <div class="preview-stat"><strong id="stat-images">0</strong><span>Images</span></div>
                <div class="preview-stat"><strong id="stat-batch">0</strong><span>Batches</span></div>
            </div>
            <div id="preview-skeletons" aria-hidden="true">
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
            </div>
            <div class="virtual-preview-list" id="preview-list"></div>
            <div class="export-status">
                <div class="d-flex justify-content-between mb-2" style="font-size:0.76rem;color:#64748B;">
                    <span id="export-label">Preview cache warming</span>
                    <span id="export-percent">12%</span>
                </div>
                <div class="export-progress"><span id="export-bar"></span></div>
            </div>
        </div>
    </div>
</div>

</form>

@endsection

@push('js')
<script>
const shareProducts = @json($productPreviewPayload);
const productSelect = document.getElementById('product-select');
const multiSelect = document.getElementById('product-multi-select');
const previewList = document.getElementById('preview-list');
const skeletons = document.getElementById('preview-skeletons');
const cache = new Map(shareProducts.map(product => [String(product.id), product]));
let previewFrame = null;

function debounce(fn, wait = 90) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), wait);
    };
}

function selectedIds() {
    const ids = Array.from(multiSelect.selectedOptions).map(option => option.value);
    if (ids.length) return ids;
    if (productSelect.value) return [productSelect.value];
    return shareProducts.map(product => String(product.id));
}

function renderPreview() {
    const ids = selectedIds();
    const selected = ids.map(id => cache.get(String(id))).filter(Boolean);
    const batchCount = Math.max(1, Math.ceil(selected.length / 12));

    document.getElementById('stat-products').textContent = selected.length;
    document.getElementById('stat-images').textContent = selected.length;
    document.getElementById('stat-batch').textContent = batchCount;
    document.getElementById('preview-mode').textContent = selected.length > 30 ? 'Virtual' : 'Instant';
    document.getElementById('export-label').textContent = selected.length > 80 ? 'Large export queued in chunks' : 'Preview cache ready';
    document.getElementById('export-percent').textContent = selected.length > 80 ? '64%' : '100%';
    document.getElementById('export-bar').style.width = selected.length > 80 ? '64%' : '100%';

    skeletons.style.display = 'block';
    previewList.innerHTML = '';
    cancelAnimationFrame(previewFrame);
    previewFrame = requestAnimationFrame(() => {
        const fragment = document.createDocumentFragment();
        selected.slice(0, 60).forEach(product => {
            const row = document.createElement('div');
            row.className = 'preview-product';
            row.innerHTML = `
                <img src="${product.thumbnail_url}" srcset="${product.thumbnail_srcset || ''}" sizes="58px" loading="lazy" decoding="async" alt="">
                <div>
                    <h6>${product.name}</h6>
                    <p>${product.short_description || product.sku || 'Catalog-ready demo product.'}</p>
                </div>
                <div class="preview-price">${product.offer_price ? 'Rs. ' + Number(product.offer_price).toLocaleString('en-IN') : 'Quote'}</div>
            `;
            fragment.appendChild(row);
        });
        if (selected.length > 60) {
            const more = document.createElement('div');
            more.className = 'text-center py-3';
            more.style.color = '#64748B';
            more.style.fontSize = '0.78rem';
            more.textContent = `${selected.length - 60} more products will render lazily on the public share.`;
            fragment.appendChild(more);
        }
        previewList.appendChild(fragment);
        skeletons.style.display = 'none';
    });
}

const debouncedRender = debounce(renderPreview, 80);
productSelect.addEventListener('change', () => {
    if (productSelect.value) {
        Array.from(multiSelect.options).forEach(option => option.selected = option.value === productSelect.value);
    }
    debouncedRender();
});
multiSelect.addEventListener('change', debouncedRender);
document.getElementById('select-all-products').addEventListener('click', () => {
    Array.from(multiSelect.options).forEach(option => option.selected = true);
    productSelect.value = '';
    renderPreview();
});
document.getElementById('clear-products').addEventListener('click', () => {
    Array.from(multiSelect.options).forEach(option => option.selected = false);
    productSelect.value = '';
    renderPreview();
});

renderPreview();
</script>
@endpush
