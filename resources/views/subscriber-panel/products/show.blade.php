@extends('subscriber-panel.layouts.app')

@section('title', 'Product Preview')
@section('page-title', 'Product Details')
@section('breadcrumb', '<a href="' . route('subscriber.products.index') . '">Products</a> → ' . $product->name)

@section('content')

<div class="row g-3">
    {{-- Left: Details & Images --}}
    <div class="col-lg-8">
        {{-- Product Card --}}
        <div class="vp-card mb-3">
            <div class="vp-card-body">
                <div class="row g-3">
                    {{-- Images Gallery --}}
                    <div class="col-md-5">
                        <div class="product-gallery">
                            <div class="main-preview mb-2">
                                @if($product->thumbnail)
                                    <img id="active-gallery-img" src="{{ $product->thumbnail_url }}" alt="" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:12px;border:1px solid #E2E8F0;">
                                @else
                                    <div class="product-card-img-placeholder" style="aspect-ratio:1/1;border-radius:12px;">📦</div>
                                @endif
                            </div>
                            
                            {{-- Carousel items --}}
                            @if($product->images->count() > 0)
                            <div class="d-flex gap-2 overflow-x-auto pb-1" style="scrollbar-width:thin;">
                                @if($product->thumbnail)
                                <img src="{{ $product->thumbnail_url }}" onclick="setActiveImg(this.src)" style="height:50px;width:50px;object-fit:cover;border-radius:6px;cursor:pointer;border:2px solid var(--subscriber-primary);">
                                @endif
                                @foreach($product->images as $img)
                                <img src="{{ $img->image_url }}" onclick="setActiveImg(this.src)" style="height:50px;width:50px;object-fit:cover;border-radius:6px;cursor:pointer;border:1px solid #E2E8F0;">
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Summary info --}}
                    <div class="col-md-7">
                        <span class="badge badge-{{ $product->status }} mb-2" style="border-radius:20px;padding:4px 10px;font-size:0.68rem;font-weight:700;">
                            {{ ucfirst($product->status) }}
                        </span>
                        
                        <h4 style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--text-primary);margin-bottom:8px;">{{ $product->name }}</h4>
                        
                        @if($product->sku)
                        <div style="font-size:0.8rem;color:#94A3B8;margin-bottom:12px;">SKU: <strong style="color:#64748B;">{{ $product->sku }}</strong></div>
                        @endif

                        <hr style="border-top:1px solid #F1F5F9;margin:12px 0;">

                        {{-- Pricing details --}}
                        <div class="d-flex align-items-baseline gap-3 mb-3">
                            @if($product->offer_price)
                                <div style="font-family:'Outfit',sans-serif;font-size:1.8rem;font-weight:800;color:var(--subscriber-primary);">₹{{ number_format($product->offer_price, 2) }}</div>
                                @if($product->mrp)
                                    <div style="font-size:1rem;color:#94A3B8;text-decoration:line-through;">₹{{ number_format($product->mrp, 2) }}</div>
                                    <span class="price-discount-badge" style="font-size:0.75rem;">{{ $product->discount_percentage }}% OFF</span>
                                @endif
                            @elseif($product->mrp)
                                <div style="font-family:'Outfit',sans-serif;font-size:1.8rem;font-weight:800;color:var(--text-primary);">₹{{ number_format($product->mrp, 2) }}</div>
                            @else
                                <div style="color:#94A3B8;font-style:italic;">No Price Specified</div>
                            @endif
                        </div>

                        @if($product->short_description)
                        <p style="color:#475569;font-size:0.875rem;line-height:1.6;margin-bottom:16px;">{{ $product->short_description }}</p>
                        @endif

                        <div class="d-flex gap-2">
                            <a href="{{ route('subscriber.products.edit', $product->id) }}" class="btn-subscriber" style="padding:8px 18px;">
                                <i class="bi bi-pencil"></i> Edit Product
                            </a>
                            <a href="{{ route('subscriber.share.create', ['product_id' => $product->id]) }}" class="btn-subscriber-outline" style="padding:8px 18px;">
                                <i class="bi bi-share"></i> Share Catalog
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attributes / Specifications --}}
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-sliders me-2"></i>Product Specifications</h6>
            </div>
            <div class="vp-card-body p-0">
                @if($product->attributeValues->isEmpty())
                <div class="empty-state" style="padding:40px 20px;">
                    <div class="empty-state-icon">📋</div>
                    <div class="empty-state-title" style="font-size:1.1rem;">No Attributes Assigned</div>
                    <div class="empty-state-text" style="font-size:0.82rem;">This product doesn't have any custom attribute values assigned yet.</div>
                    <a href="{{ route('subscriber.products.edit', $product->id) }}" class="btn-subscriber-outline" style="font-size:0.78rem;padding:6px 14px;">
                        Add Specifications
                    </a>
                </div>
                @else
                <div class="table-responsive">
                    <table class="vp-table">
                        <tbody>
                            @foreach($product->attributeValues as $val)
                            @php 
                                $displayVal = $val->value;
                                if ($val->attribute?->type === 'multiselect' || $val->attribute?->type === 'checkbox') {
                                    $arr = json_decode($val->value, true) ?? [$val->value];
                                    $displayVal = implode(', ', $arr);
                                }
                            @endphp
                            <tr>
                                <td style="width:250px;font-weight:600;color:#64748B;font-size:0.85rem;">{{ $val->attribute?->name ?? 'Attribute' }}</td>
                                <td style="color:#0F172A;font-weight:500;font-size:0.85rem;">
                                    @if($val->attribute?->type === 'color')
                                        <div class="d-flex align-items-center gap-2">
                                            <span style="display:inline-block;width:18px;height:18px;border-radius:4px;background-color:{{ $displayVal }};border:1px solid #CBD5E1;"></span>
                                            <span>{{ $displayVal }}</span>
                                        </div>
                                    @elseif($val->attribute?->type === 'url')
                                        <a href="{{ $displayVal }}" target="_blank" style="color:var(--subscriber-primary);">{{ $displayVal }} <i class="bi bi-box-arrow-up-right ms-1" style="font-size:0.7rem;"></i></a>
                                    @else
                                        {{ $displayVal }} {{ $val->attribute?->unit }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right side: PDF, Share Overview, Logs --}}
    <div class="col-lg-4">
        {{-- PDF Overview --}}
        <div class="vp-card mb-3">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-file-pdf me-2" style="color:#EF4444;"></i>Visibility & Catalog Rules</h6>
            </div>
            <div class="vp-card-body p-0">
                <table class="vp-table">
                    <tbody>
                        <tr>
                            <td>Show MRP in PDF</td>
                            <td class="text-end">
                                <span class="badge {{ $product->pdf_show_mrp ? 'badge-active' : 'badge-inactive' }}">{{ $product->pdf_show_mrp ? 'Yes' : 'No' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Show Offer in PDF</td>
                            <td class="text-end">
                                <span class="badge {{ $product->pdf_show_offer_price ? 'badge-active' : 'badge-inactive' }}">{{ $product->pdf_show_offer_price ? 'Yes' : 'No' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Show Images in PDF</td>
                            <td class="text-end">
                                <span class="badge {{ $product->pdf_show_images ? 'badge-active' : 'badge-inactive' }}">{{ $product->pdf_show_images ? 'Yes' : 'No' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Show MRP on Share Page</td>
                            <td class="text-end">
                                <span class="badge {{ $product->share_show_mrp ? 'badge-active' : 'badge-inactive' }}">{{ $product->share_show_mrp ? 'Yes' : 'No' }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Share stats summary --}}
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-graph-up-arrow me-2" style="color:#10B981;"></i>Sharing Analytics</h6>
            </div>
            <div class="vp-card-body">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div style="background:#F8FAFC;border-radius:10px;padding:12px;">
                            <div style="font-size:0.75rem;color:#94A3B8;font-weight:600;text-transform:uppercase;">Views</div>
                            <div style="font-family:'Outfit',sans-serif;font-size:1.4rem;font-weight:800;color:#0F172A;margin-top:4px;">{{ $product->shares()->sum('view_count') }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#F8FAFC;border-radius:10px;padding:12px;">
                            <div style="font-size:0.75rem;color:#94A3B8;font-weight:600;text-transform:uppercase;">Downloads</div>
                            <div style="font-family:'Outfit',sans-serif;font-size:1.4rem;font-weight:800;color:#0F172A;margin-top:4px;">{{ $product->shares()->sum('download_count') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
function setActiveImg(src) {
    document.getElementById('active-gallery-img').src = src;
    
    // Highlight thumbnail
    document.querySelectorAll('.product-gallery img').forEach(img => {
        if (img.src === src) {
            img.style.borderColor = 'var(--subscriber-primary)';
        } else {
            img.style.borderColor = '#E2E8F0';
        }
    });
}
</script>
@endpush
