@extends('layouts.frontend')

@section('title', ($category->name ?? 'Premium Products') . ' - Catasky')

@section('content')

@php
    // Fetch all active categories dynamically in the view if not overridden by controller
    if (!isset($allCategories)) {
        $allCategories = \App\Models\Category::where('status', 1)->whereNull('subscriber_id')->get();
    }
@endphp

<!-- Catalog Header & Categories Pill bar -->
<section class="py-4 border-bottom bg-white">
    <div class="container">
      

        <div class="d-flex flex-column gap-3">
            <!-- Horizontal Scrollable Category Chips -->
            <div class="category-scroll pt-2">
                @if(isset($isSubscriberStore) && $isSubscriberStore)
                    <a href="{{ request()->url() }}{{ request('search') ? '?search='.urlencode(request('search')) : '' }}{{ request('sort') ? (request('search') ? '&' : '?') . 'sort='.urlencode(request('sort')) : '' }}" class="category-chip {{ !request('category') ? 'active' : '' }}">
                        🔥 All
                    </a>
                    @foreach($allCategories as $cat)
                        <a href="?category={{ $cat->slug }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}{{ request('sort') ? '&sort='.urlencode(request('sort')) : '' }}" class="category-chip {{ request('category') === $cat->slug ? 'active' : '' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('catalogue') }}" class="category-chip {{ ($category->id ?? 0) == 0 ? 'active' : '' }}">
                        🔥 All
                    </a>
                    @foreach($allCategories as $cat)
                        <a href="{{ route('category.products', $cat->slug) }}" class="category-chip {{ ($category->id ?? 0) == $cat->id ? 'active' : '' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Catalogue Grid & Filters Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Sidebar Filters (Desktop viewport) -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="premium-card p-4 bg-white sticky-top-sidebar">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-funnel-fill text-primary small-text"></i> Specifications</h5>
                        <button class="btn btn-link text-primary text-decoration-none p-0 small fw-bold" onclick="resetFilters()">Reset</button>
                    </div>
                    
                    <!-- Subcategories Group -->
                    <div class="mb-4">
                        <label class="small-text fw-bold text-uppercase text-secondary mb-3 d-block">Subcategories</label>
                        <div class="d-grid gap-2">
                            @php
                                $currentSub = request()->query('subcategory');
                                if (!isset($subcategories)) {
                                    $subcategories = isset($category->id) && $category->id > 0
                                        ? \App\Models\Subcategory::where('category_id', $category->id)->get()
                                        : \App\Models\Subcategory::take(10)->get();
                                }
                            @endphp
                            
                            @forelse($subcategories as $sub)
                                <a href="javascript:void(0)" onclick="selectSubcategory('{{ $sub->slug }}')" class="btn btn-premium-outline text-start py-2 px-3 small-text d-flex justify-content-between align-items-center {{ $currentSub == $sub->slug ? 'border-primary text-primary fw-bold' : '' }}">
                                    <span>{{ $sub->name }}</span>
                                    <i class="bi bi-chevron-right fs-6 opacity-50"></i>
                                </a>
                            @empty
                                <div class="text-secondary small py-2 px-3 bg-light rounded-3 text-center">
                                    No subcategory specifications.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Brands Group -->
                    <div class="mb-4">
                        <label class="small-text fw-bold text-uppercase text-secondary mb-3 d-block">Brands</label>
                        <div class="d-grid gap-2">
                            @php
                                $currentBrand = request()->query('brand');
                                if (!isset($brands)) {
                                    if (isset($isSubscriberStore) && $isSubscriberStore && isset($profile)) {
                                        $brandIds = \App\Models\SubscriberProduct::where('user_id', $profile->user_id)
                                            ->where('status', 'active')
                                            ->where('approval_status', 'approved')
                                            ->whereNotNull('brand_id')
                                            ->pluck('brand_id')
                                            ->flatten()
                                            ->filter()
                                            ->unique();
                                        $brands = \App\Models\Brand::withoutGlobalScope('tenant')->whereIn('id', $brandIds)->get();
                                    } else {
                                        $brands = \App\Models\Brand::where('status', 1)->whereNull('subscriber_id')->get();
                                    }
                                }
                            @endphp
                            
                            @forelse($brands as $b)
                                <a href="javascript:void(0)" onclick="selectBrand('{{ $b->slug }}')" class="btn btn-premium-outline text-start py-2 px-3 small-text d-flex justify-content-between align-items-center {{ $currentBrand == $b->slug ? 'border-primary text-primary fw-bold' : '' }}">
                                    <span>{{ $b->name }}</span>
                                    <i class="bi bi-chevron-right fs-6 opacity-50"></i>
                                </a>
                            @empty
                                <div class="text-secondary small py-2 px-3 bg-light rounded-3 text-center">
                                    No brand specifications.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Price Filter -->
                    <div class="mb-4">
                        <label class="small-text fw-bold text-uppercase text-secondary mb-3 d-block">Price Range (INR)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0">₹</span>
                                    <input type="number" id="filter-price-min" class="form-control rounded-end-3 small-text border-start-0 ps-1" placeholder="Min" value="{{ request()->query('min_price') }}" min="0">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0">₹</span>
                                    <input type="number" id="filter-price-max" class="form-control rounded-end-3 small-text border-start-0 ps-1" placeholder="Max" value="{{ request()->query('max_price') }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-premium btn-premium-primary w-100 py-3 mt-2" onclick="applyB2BFilters()">
                        <i class="bi bi-funnel"></i> Apply Filter Rules
                    </button>
                </div>
            </div>

            <!-- Products Results Area -->
            <div class="col-lg-9 col-12">
                <!-- Sorting & Stats Bar -->
                <div class="row align-items-center g-3 mb-4 animate-fade-in">
                    <!-- Stats Text Column (Removed per request) -->
                    <!-- Controls Column -->
                    <div class="col-12 d-flex gap-2 justify-content-start justify-content-md-end">
                        <!-- Mobile Offcanvas Trigger -->
                        <button class="btn btn-premium btn-premium-outline d-block d-md-none flex-fill py-2 px-3 m-0" data-bs-toggle="offcanvas" data-bs-target="#mobileFiltersOffcanvas" aria-controls="mobileFiltersOffcanvas">
                            <i class="bi bi-sliders"></i> Filters
                        </button>
                        <!-- Tablet Collapse Trigger -->
                        <button class="btn btn-premium btn-premium-outline d-none d-md-block d-lg-none py-2" data-bs-toggle="collapse" data-bs-target="#tabletFiltersCollapse" aria-expanded="false" aria-controls="tabletFiltersCollapse">
                            <i class="bi bi-sliders"></i> Filters
                        </button>
                        <select id="sort-select" class="form-select border  rounded-3 small-text py-2 px-3 bg-white flex-fill flex-md-initial" style="width: auto; min-width: 140px;" onchange="applySort(this.value)">
                            <option value="default" {{ request('sort','default')=='default' ? 'selected' : '' }}>Default Sorting</option>
                            <option value="name_asc" {{ request('sort')=='name_asc' ? 'selected' : '' }}>Alphabetical A-Z</option>
                            <option value="price_asc" {{ request('sort')=='price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ request('sort')=='price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>
                </div>

                <!-- Collapsible Filters (Tablet viewport only) -->
                <div class="collapse d-none d-md-block d-lg-none mb-4 animate-fade-in" id="tabletFiltersCollapse">
                    <div class="premium-card p-4 bg-white border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-funnel-fill text-primary small-text"></i> Tablet Filter Rules</h5>
                            <button class="btn btn-link text-primary text-decoration-none p-0 small fw-bold" onclick="resetFilters()">Reset</button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="small-text fw-bold text-secondary text-uppercase mb-2 d-block">Subcategories</label>
                                <select class="form-select rounded-3 small-text p-2" id="tablet-sub-select">
                                    <option value="">All subcategories</option>
                                    @foreach($subcategories as $sub)
                                        <option value="{{ $sub->slug }}" {{ $currentSub == $sub->slug ? 'selected' : '' }}>{{ $sub->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small-text fw-bold text-secondary text-uppercase mb-2 d-block">Brands</label>
                                <select class="form-select rounded-3 small-text p-2" id="tablet-brand-select">
                                    <option value="">All brands</option>
                                    @foreach($brands as $b)
                                        <option value="{{ $b->slug }}" {{ (request()->query('brand') == $b->slug) ? 'selected' : '' }}>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small-text fw-bold text-secondary text-uppercase mb-2 d-block">Price Range (INR)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light border-end-0">₹</span>
                                            <input type="number" id="tablet-price-min" class="form-control rounded-end-3 small-text border-start-0 ps-1" placeholder="Min" value="{{ request()->query('min_price') }}" min="0">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light border-end-0">₹</span>
                                            <input type="number" id="tablet-price-max" class="form-control rounded-end-3 small-text border-start-0 ps-1" placeholder="Max" value="{{ request()->query('max_price') }}" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button class="btn btn-premium btn-premium-primary py-2 px-4" onclick="applyTabletFilters()">Apply Rules</button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Offcanvas Filters Drawer (Mobile viewport only) -->
                <div class="offcanvas offcanvas-start border-end-0 shadow" tabindex="-1" id="mobileFiltersOffcanvas" aria-labelledby="mobileFiltersOffcanvasLabel" style="width: 320px; border-top-right-radius: 24px; border-bottom-right-radius: 24px; background-color: var(--background); transition: transform 0.25s ease-in-out;">
                    <div class="offcanvas-header bg-white border-bottom p-4">
                        <h5 class="offcanvas-title fw-bold text-dark d-flex align-items-center gap-2" id="mobileFiltersOffcanvasLabel">
                            <i class="bi bi-funnel-fill text-primary"></i> Filter Specifications
                        </h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body p-4 d-flex flex-column justify-content-between" style="overflow-y: auto;">
                        <div>
                            <!-- Subcategories Group -->
                            <div class="mb-4">
                                <label class="small-text fw-bold text-uppercase text-secondary mb-3 d-block">Subcategories</label>
                                <div class="d-grid gap-2">
                                    @foreach($subcategories as $sub)
                                        <a href="javascript:void(0)" onclick="selectSubcategory('{{ $sub->slug }}')" class="btn btn-premium-outline text-start py-2 px-3 small-text d-flex justify-content-between align-items-center {{ $currentSub == $sub->slug ? 'border-primary text-primary fw-bold' : '' }}">
                                            <span>{{ $sub->name }}</span>
                                            <i class="bi bi-chevron-right fs-6 opacity-50"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Brands Group -->
                            <div class="mb-4">
                                <label class="small-text fw-bold text-uppercase text-secondary mb-3 d-block">Brands</label>
                                <div class="d-grid gap-2">
                                    @foreach($brands as $b)
                                        <a href="javascript:void(0)" onclick="selectBrand('{{ $b->slug }}')" class="btn btn-premium-outline text-start py-2 px-3 small-text d-flex justify-content-between align-items-center {{ $currentBrand == $b->slug ? 'border-primary text-primary fw-bold' : '' }}">
                                            <span>{{ $b->name }}</span>
                                            <i class="bi bi-chevron-right fs-6 opacity-50"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Price Filter -->
                            <div class="mb-4">
                                <label class="small-text fw-bold text-uppercase text-secondary mb-3 d-block">Price Range (INR)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light border-end-0">₹</span>
                                            <input type="number" id="mobile-price-min" class="form-control rounded-end-3 small-text border-start-0 ps-1" placeholder="Min" value="{{ request()->query('min_price') }}" min="0">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light border-end-0">₹</span>
                                            <input type="number" id="mobile-price-max" class="form-control rounded-end-3 small-text border-start-0 ps-1" placeholder="Max" value="{{ request()->query('max_price') }}" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-4 border-top mt-auto">
                            <button class="btn btn-premium btn-premium-primary flex-grow-1 py-3" onclick="applyMobileFilters()">
                                Apply Rules
                            </button>
                            <button class="btn btn-premium btn-premium-outline px-4 py-3" onclick="resetFilters()">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Responsive Grid -->
                <div class="product-grid" id="product-grid">
                    @forelse($products as $product)
                        <div class="premium-card animate-fade-in" id="product-card-{{ $product->id }}">
                            <!-- Checked/Selection icon overlay badge -->
                            <div class="selection-indicator">
                                <i class="bi bi-check-lg"></i>
                            </div>

                            <!-- Image Frame -->
                            <div class="product-image-container" onclick="openDrawer('{{ $product->id }}')">
                                @php
                                    $thumbnail = $product->thumbnail_url;
                                @endphp
                                <img src="{{ $thumbnail }}" alt="{{ $product->name }}" loading="lazy" decoding="async">
                                
                                <!-- Floating Quick Look button -->
                                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3 w-85 opacity-0 card-hover-btn" style="transition: all 0.3s ease;">
                                    <button class="btn btn-premium btn-premium-dark w-100 py-2 small shadow-lg" onclick="event.stopPropagation(); openDrawer('{{ $product->id }}')" style="white-space: nowrap;">
                                        <i class="bi bi-eye"></i> Quick View
                                    </button>
                                </div>
                            </div>

                            <!-- Details Frame -->
                            <div class="product-details">
                                <span class="small text-secondary mb-1" style="font-size:0.75rem; font-weight: 500;">
                                    {{ $product->category->name ?? 'Corporate Segment' }}
                                </span>
                                <h6 class="product-title" onclick="window.location.href='{{ route('product.details', $product->slug) }}'">
                                    {{ $product->name }}
                                </h6>
                                
                                <div class="product-meta mb-1">
                                    <i class="bi bi-layers me-1 text-primary"></i> MOQ: {{ $product->moq ?? 100 }} pcs
                                </div>

                                <div class="product-price-val mb-3" style="font-size: 0.8rem !important; font-weight: 700 !important; color: var(--primary) !important;">
                                    @if($product->price)
                                        ₹{{ number_format($product->price, 2) }}
                                    @else
                                        {{ $product->variant ?: '' }}
                                    @endif
                                </div>

                                <div class="product-price-row pt-2" style="border-top: 1px dashed var(--border); display: flex; justify-content: center; width: 100%;">
                                    <button class="btn btn-premium btn-premium-outline select-btn-main w-100 py-2 px-3" onclick="toggleSelection('{{ $product->id }}', this)">
                                        <i class="bi bi-bag-plus"></i> Select
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 bg-white rounded-4 border animate-fade-in">
                            <i class="bi bi-search text-secondary display-1 opacity-25 d-block mb-3"></i>
                            <h4 class="fw-bold mt-4 text-dark">No products match selection</h4>
                            <p class="text-secondary mx-auto" style="max-width: 350px;">We couldn't locate any product matching the active subcategory or price tiers.</p>
                            @if(isset($isSubscriberStore) && $isSubscriberStore)
                                <a href="javascript:void(0)" onclick="resetFilters()" class="btn btn-premium btn-premium-primary mt-3">
                                    @if(isset($profile) && $profile->company_slug === 'demo')
                                        Reset Demo
                                    @else
                                        Reset Catalogue
                                    @endif
                                </a>
                            @else
                                <a href="{{ route('catalogue') }}" class="btn btn-premium btn-premium-primary mt-3">Reset Catalogue</a>
                            @endif
                        </div>
                    @endforelse
                </div>

                <!-- Custom Pagination Links -->
                <div class="mt-5 d-flex justify-content-center">
                    <div class="pagination-container w-100">
                        {{ $products->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .product-image-container {
        background: #ffffff !important;
    }
    .product-image-container img {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
    }

    /* Desktop Sticky Sidebar styling to prevent overflowing footer & scroll internally */
    .sticky-top-sidebar {
        position: sticky;
        top: 90px;
        max-height: calc(100vh - 125px);
        overflow-y: auto;
        z-index: 10;
        padding-right: 4px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Custom premium scrollbar for sidebar */
    .sticky-top-sidebar::-webkit-scrollbar {
        width: 4px;
    }
    .sticky-top-sidebar::-webkit-scrollbar-track {
        background: transparent;
    }
    .sticky-top-sidebar::-webkit-scrollbar-thumb {
        background: rgba(79, 70, 229, 0.15);
        border-radius: 4px;
    }
    .sticky-top-sidebar::-webkit-scrollbar-thumb:hover {
        background: var(--primary);
    }

    /* Softer, premium card transition and hover scale (250ms) */
    .premium-card {
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .premium-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 12px 24px -10px rgba(79, 70, 229, 0.15), 0 4px 6px -2px rgba(0, 0, 0, 0.03) !important;
        border-color: rgba(79, 70, 229, 0.4) !important;
    }

    /* Softer category chip transitions and state indicators */
    .category-chip {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .category-chip:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.1) !important;
        border-color: var(--primary) !important;
        color: var(--primary) !important;
    }
    .category-chip.active {
        background: var(--primary-gradient) !important;
        color: white !important;
        box-shadow: 0 6px 14px rgba(79, 70, 229, 0.2) !important;
    }

    /* Grid layout spacing & mobile/tablet/desktop product cards optimizations */
    .product-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important; /* Default 2 columns for mobile */
        gap: 16px !important;
        padding: 20px 0 !important;
    }

    /* Large Mobile / Phablet View */
    @media (min-width: 576px) {
        .product-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 20px !important;
        }
    }

    /* Tablet View (Sidebar collapsed, full width container) */
    @media (min-width: 768px) and (max-width: 1023.98px) {
        .product-grid {
            grid-template-columns: repeat(3, 1fr) !important; /* 3 columns look extremely spacious on full-width tablet */
            gap: 20px !important;
        }
    }

    /* Desktop/Laptop View (Sidebar visible, exactly 4 columns per row) */
    @media (min-width: 1024px) {
        .product-grid {
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 20px !important;
        }
    }

    /* Mobile product cards styles override */
    @media (max-width: 767px) {
        .product-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
            padding: 10px 0 !important;
        }
        .premium-card {
            padding: 10px !important;
            border-radius: 16px !important;
        }
        .product-image-container {
            border-radius: 12px !important;
            margin-bottom: 8px !important;
            aspect-ratio: 1/1 !important;
            height: auto !important;
        }
        .product-image-container img {
            object-fit: contain !important;
        }
        .product-title {
            font-size: 0.85rem !important;
            height: 34px !important;
            line-height: 1.3 !important;
            margin-bottom: 4px !important;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-meta {
            font-size: 0.72rem !important;
            margin-bottom: 8px !important;
        }
        .product-price-row {
            padding-top: 8px !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 6px !important;
        }
        .product-price-val {
            font-size: 0.85rem !important;
            font-weight: 700 !important;
        }
        .select-btn-main {
            width: 100% !important;
            padding: 6px 12px !important;
            font-size: 0.75rem !important;
            border-radius: 8px !important;
            text-align: center !important;
            display: flex !important;
            justify-content: center !important;
        }
        .card-hover-btn {
            display: none !important;
        }
    }

    /* Trigger visibility of hover widgets in CSS */
    .premium-card:hover .card-hover-btn {
        opacity: 1 !important;
        transform: translate(-50%, -5px) !important;
    }

    /* Catasky Premium Pagination Styles */
    .pagination {
        display: flex;
        gap: 6px;
        align-items: center;
        border: none;
        margin: 0;
        padding: 0;
    }

    .pagination .page-item {
        border: none;
        margin: 0;
    }

    .pagination .page-item .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        padding: 0 16px !important;
        white-space: nowrap !important;
        height: 38px;
        border-radius: 10px !important; /* Premium rounded curves */
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--primary) !important;
        background: white !important;
        border: 1px solid var(--border) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--) !important;
    }

    .pagination .page-item .page-link:hover:not(.disabled):not(.active) {
        color: white !important;
        background: var(--primary-gradient) !important;
        border-color: transparent !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.15) !important;
    }

    .pagination .page-item.active .page-link {
        background: var(--primary-gradient) !important;
        color: white !important;
        border-color: transparent !important;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3) !important;
    }

    .pagination .page-item.disabled .page-link {
        color: #94A3B8 !important;
        background: #F8FAFC !important;
        border-color: #E2E8F0 !important;
        box-shadow: none !important;
        transform: none !important;
        pointer-events: none !important;
        opacity: 0.65 !important;
    }

    .pagination-container p.small.text-muted,
    nav p.small.text-muted {
        font-family: 'Outfit', sans-serif !important;
        font-size: 0.9rem !important;
        color: var(--text-secondary) !important;
        margin-bottom: 0 !important;
        margin-top: 0 !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }
    nav p.small.text-muted span.fw-semibold {
        color: var(--text-primary) !important;
        font-weight: 700 !important;
    }
</style>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.05 });

        document.querySelectorAll('.animate-fade-in').forEach(el => observer.observe(el));
    });

    // Helper: build a new URL preserving all existing params then overriding/deleting the given ones
    function buildFilterUrl(overrides, removes) {
        const url = new URL(window.location.href);
        // Always reset to page 1 when filters change
        url.searchParams.delete('page');
        if (removes) removes.forEach(k => url.searchParams.delete(k));
        if (overrides) Object.entries(overrides).forEach(([k, v]) => {
            if (v !== null && v !== undefined && v !== '') url.searchParams.set(k, v);
            else url.searchParams.delete(k);
        });
        return url.toString();
    }

    function selectSubcategory(slug) {
        const url = new URL(window.location.href);
        const currentSub = url.searchParams.get('subcategory');
        if (currentSub === slug) {
            // Toggle off subcategory if clicked again
            window.location.href = buildFilterUrl({ subcategory: null });
        } else {
            window.location.href = buildFilterUrl({ subcategory: slug });
        }
    }

    function selectBrand(slug) {
        const url = new URL(window.location.href);
        const currentBrand = url.searchParams.get('brand');
        if (currentBrand === slug) {
            // Toggle off brand if clicked again
            window.location.href = buildFilterUrl({ brand: null });
        } else {
            window.location.href = buildFilterUrl({ brand: slug });
        }
    }

    // Desktop sidebar filter
    function applyB2BFilters() {
        const min = document.getElementById('filter-price-min').value;
        const max = document.getElementById('filter-price-max').value;
        // subcategory is preserved from current URL
        window.location.href = buildFilterUrl({
            min_price: min || null,
            max_price: max || null
        });
    }

    // Tablet collapsible filter
    function applyTabletFilters() {
        const sub = document.getElementById('tablet-sub-select').value;
        const brand = document.getElementById('tablet-brand-select').value;
        const min = document.getElementById('tablet-price-min').value;
        const max = document.getElementById('tablet-price-max').value;
        window.location.href = buildFilterUrl({
            subcategory: sub || null,
            brand: brand || null,
            min_price: min || null,
            max_price: max || null
        });
    }

    // Mobile offcanvas filter
    function applyMobileFilters() {
        const min = document.getElementById('mobile-price-min').value;
        const max = document.getElementById('mobile-price-max').value;
        // subcategory preserved from current URL
        window.location.href = buildFilterUrl({
            min_price: min || null,
            max_price: max || null
        });
    }

    // Sorting dropdown
    function applySort(sortValue) {
        window.location.href = buildFilterUrl({ sort: sortValue === 'default' ? null : sortValue });
    }

    function resetFilters() {
        window.location.href = window.location.origin + window.location.pathname;
    }
</script>
@endpush
