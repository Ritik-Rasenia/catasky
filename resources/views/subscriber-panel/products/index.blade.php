@extends('subscriber-panel.layouts.app')

@section('title', 'My Products')
@section('page-title', 'My Products')
@section('breadcrumb', 'Manage your product catalog')

@section('content')

<div class="vp-page-header">
    <div>
        <h1 class="vp-page-title">Products</h1>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('subscriber.share.create') }}" class="btn-subscriber-outline">
            <i class="bi bi-share"></i> Share Catalog
        </a>
        <a href="{{ route('subscriber.products.create') }}" class="btn-subscriber">
            <i class="bi bi-plus-lg"></i> Add Product
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="vp-card mb-4">
    <div class="vp-card-body">
        <form action="{{ route('subscriber.products.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="vp-label">Search</label>
                <div class="position-relative">
                    <i class="bi bi-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#94A3B8;"></i>
                    <input type="text" name="search" class="vp-input" style="padding-left:38px;"
                           placeholder="Search products..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="vp-label">Status</label>
                <select name="status" class="vp-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="vp-label">Category</label>
                <select name="category_id" class="vp-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn-subscriber w-100" style="padding:10px;">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Products Grid --}}
@if($products->isEmpty())
<div class="vp-card">
    <div class="empty-state">
        <div class="empty-state-icon">📦</div>
        <div class="empty-state-title">No Products Found</div>
        <div class="empty-state-text">
            {{ request()->hasAny(['search','status','category_id'])
                ? 'Try adjusting your filters to find products.'
                : 'Start adding products to your catalog. They can be shared via PDF, WhatsApp, or direct links.' }}
        </div>
        @if(!request()->hasAny(['search','status','category_id']))
        <a href="{{ route('subscriber.products.create') }}" class="btn-subscriber">
            <i class="bi bi-plus-lg"></i> Add Your First Product
        </a>
        @else
        <a href="{{ route('subscriber.products.index') }}" class="btn-subscriber-outline">Clear Filters</a>
        @endif
    </div>
</div>
@else

<div class="row g-3 mb-4">
    @foreach($products as $product)
    <div class="col-sm-6 col-lg-4 col-xl-3">
        <div class="product-card">
            {{-- Product Image --}}
            <div style="position:relative;">
                @if($product->thumbnail)
                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="product-card-img">
                @else
                    <div class="product-card-img-placeholder">📦</div>
                @endif
                {{-- Status Badge --}}
                <span class="badge badge-{{ $product->status }}" style="position:absolute;top:10px;left:10px;border-radius:20px;padding:4px 10px;font-size:0.68rem;font-weight:700;">
                    {{ ucfirst($product->status) }}
                </span>
                {{-- Image count --}}
                @if($product->images->count() > 0)
                <span style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,0.6);color:white;border-radius:20px;padding:3px 9px;font-size:0.7rem;">
                    <i class="bi bi-images"></i> {{ $product->images->count() }}
                </span>
                @endif
            </div>

            {{-- Card Body --}}
            <div class="product-card-body">
                <div class="product-card-name">{{ $product->name }}</div>
                @if($product->short_description)
                <div class="product-card-desc">{{ $product->short_description }}</div>
                @endif
                @if($product->sku)
                <div style="font-size:0.7rem;color:#94A3B8;margin-top:4px;">SKU: {{ $product->sku }}</div>
                @endif

                {{-- Pricing --}}
                <div class="product-card-pricing">
                    <div>
                        @if($product->mrp)
                            <div class="price-mrp">MRP: ₹{{ number_format($product->mrp, 2) }}</div>
                        @endif
                        @if($product->offer_price)
                            <div class="price-offer">₹{{ number_format($product->offer_price, 2) }}</div>
                        @elseif($product->mrp)
                            <div class="price-offer">₹{{ number_format($product->mrp, 2) }}</div>
                        @endif
                    </div>
                    @if($product->discount_percentage)
                    <span class="price-discount-badge">{{ $product->discount_percentage }}% OFF</span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="product-card-actions">
                <a href="{{ route('subscriber.products.edit', $product) }}" class="btn btn-sm flex-grow-1" style="border-radius:8px;background:#F8FAFC;border:1px solid #E2E8F0;color:#475569;font-size:0.78rem;">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('subscriber.share.create', ['product_id' => $product->id]) }}" class="btn btn-sm" style="border-radius:8px;background:rgba(79,70,229,0.08);border:1px solid rgba(79,70,229,0.2);color:#4F46E5;font-size:0.78rem;padding:4px 10px;">
                    <i class="bi bi-share"></i>
                </a>
                <form action="{{ route('subscriber.products.destroy', $product) }}" method="POST" id="del-{{ $product->id }}">
                    @csrf @method('DELETE')
                    <button type="button" onclick="confirmDelete('del-{{ $product->id }}', 'Delete {{ addslashes($product->name) }}?')"
                            class="btn btn-sm" style="border-radius:8px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2);color:#EF4444;font-size:0.78rem;padding:4px 10px;">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
<div class="admin-pagination-wrap">
    {{ $products->withQueryString()->links() }}
</div>

@endif

@endsection
