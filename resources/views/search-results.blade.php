@extends('layouts.frontend')

@section('title', 'Search Results for "' . $query . '" - Catasky')

@section('content')

<!-- Header Section -->
<section class="py-5 bg-white border-bottom">
    <div class="container text-center">
        <h2 class="fw-bold mb-2">Search Results</h2>
        <p class="text-secondary mb-0">Showing results for "<span class="text-primary fw-semibold">{{ $query }}</span>"</p>
    </div>
</section>

<!-- Catalog Grid Section -->
<section class="py-5">
    <div class="container">
        <!-- Products Results Area -->
        <div class="row justify-content-center">
            <div class="col-lg-10 col-12">
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
                                    {{ $product->category?->name ?? 'Corporate Segment' }}
                                </span>
                                <h6 class="product-title" onclick="window.location.href='{{ route('product.details', $product->slug) }}'">
                                    {{ $product->name }}
                                </h6>
                                
                                @if($product->sku)
                                    <div class="product-meta mb-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-hash me-1 text-primary"></i>SKU: <strong class="text-dark">{{ $product->sku }}</strong>
                                    </div>
                                @endif
                                
                                @if($product->brand)
                                    <div class="product-meta mb-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-tag me-1 text-primary"></i>Brand: <strong class="text-dark">{{ $product->brand?->name }}</strong>
                                    </div>
                                @endif

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
                            <h4 class="fw-bold mt-4 text-dark">No products found</h4>
                            <p class="text-secondary mx-auto" style="max-width: 350px;">We couldn't locate any product matching "<span class="fw-semibold">{{ $query }}</span>".</p>
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
        background: #f8fafc !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        overflow: hidden !important;
        padding: 10px !important;
        aspect-ratio: 1/1 !important;
        position: relative !important;
    }
    .product-image-container img {
        max-width: 90% !important;
        max-height: 90% !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
        transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
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

    /* Tablet View */
    @media (min-width: 768px) and (max-width: 1023.98px) {
        .product-grid {
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 20px !important;
        }
    }

    /* Desktop/Laptop View */
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
            padding: 8px !important;
        }
        .product-image-container img {
            max-width: 90% !important;
            max-height: 90% !important;
            width: auto !important;
            height: auto !important;
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

    .premium-card:hover .card-hover-btn {
        opacity: 1 !important;
        transform: translate(-50%, -5px) !important;
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
</script>
@endpush
