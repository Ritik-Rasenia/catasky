@forelse($products as $product)
    <div class="premium-card position-relative animate-fade-in" id="product-card-{{ $product->id }}" data-product-id="{{ $product->id }}" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 20px; padding: 14px; box-shadow: 0 8px 30px rgba(0,0,0,0.02); overflow: hidden; transition: all 0.3s ease;">
        
        <!-- Hover Action Widgets (Wishlist & Compare) -->
        <div class="position-absolute top-3 end-3 d-flex flex-column gap-2" style="z-index: 5;">
            <button class="wishlist-btn btn btn-light rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center" 
                    onclick="event.stopPropagation(); toggleWishlist('{{ $product->id }}', this)" 
                    title="Add to Wishlist" 
                    style="width: 36px; height: 36px; border: none; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(5px); color: #64748b; transition: all 0.2s;">
                <i class="bi bi-heart"></i>
            </button>
            <button class="compare-btn btn btn-light rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center" 
                    onclick="event.stopPropagation(); toggleCompare('{{ $product->id }}', this)" 
                    title="Compare Product" 
                    style="width: 36px; height: 36px; border: none; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(5px); color: #64748b; transition: all 0.2s;">
                <i class="bi bi-arrow-left-right"></i>
            </button>
        </div>

        <!-- Checked / Selection overlay indicator -->
        <div class="selection-indicator position-absolute top-3 start-3 bg-indigo text-white rounded-circle shadow d-flex align-items-center justify-content-center" 
             style="width: 28px; height: 28px; z-index: 4; opacity: 0; transform: scale(0.6); transition: all 0.25s ease;">
            <i class="bi bi-check-lg fw-bold"></i>
        </div>

        <!-- Image Frame -->
        <div class="product-image-container position-relative overflow-hidden rounded-4 mb-3 text-center d-flex align-items-center justify-content-center cursor-pointer" 
             onclick="openDrawer('{{ $product->id }}')" 
             style="aspect-ratio: 1/1; background: #f8fafc; border: 1px solid rgba(226, 232, 240, 0.6);">
            
            <!-- Skeleton Loader Placeholder (Prevents Layout Shift) -->
            <div class="skeleton-placeholder position-absolute top-0 start-0 w-100 h-100 bg-light" style="z-index: 1;"></div>
            
            <img src="{{ $product->thumbnail_url }}" 
                 alt="{{ $product->name }}" 
                 loading="lazy" 
                 decoding="async"
                 onload="this.previousElementSibling.style.display='none';" 
                 class="img-fluid rounded-3" 
                 style="max-height: 90%; max-width: 90%; object-fit: contain; z-index: 2; transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);">
            
            <!-- Floating Quick Look -->
            <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3 w-75 card-hover-btn" style="transition: all 0.3s ease; opacity: 0; transform: translate(-50%, 10px); z-index: 3;">
                <button class="btn btn-dark w-100 py-2 small shadow-lg rounded-pill fw-bold border-0" 
                        onclick="event.stopPropagation(); openDrawer('{{ $product->id }}')" 
                        style="font-size: 0.75rem; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(8px);">
                    <i class="bi bi-eye me-1"></i> Quick View
                </button>
            </div>
        </div>

        <!-- Details Frame -->
        <div class="product-details text-start">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-secondary fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.2px;">
                    {{ $product->category->name ?? 'Corporate Segment' }}
                </span>
                
                <!-- Ratings display -->
                @if($product->reviews_count > 0)
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-star-fill text-warning" style="font-size: 0.72rem;"></i>
                        <span class="fw-bold text-dark" style="font-size: 0.75rem;">{{ $product->average_rating }}</span>
                    </div>
                @endif
            </div>

            <h6 class="product-title fw-bold text-dark mb-2 cursor-pointer text-truncate" 
                onclick="openDrawer('{{ $product->id }}')" 
                title="{{ $product->name }}" 
                style="font-size: 0.95rem; font-family: 'Outfit', sans-serif; transition: color 0.2s;">
                {{ $product->name }}
            </h6>

            <div class="product-meta text-muted small mb-3" style="font-size: 0.75rem;">
                <i class="bi bi-tag me-1 text-indigo"></i> SKU: {{ $product->sku ?: $product->part_code }}
            </div>

            <div class="product-price-row d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                <div class="price-wrap">
                    @if($product->price)
                        <div class="product-price-val fw-bold text-dark" style="font-size: 1.05rem; font-family: 'Outfit', sans-serif;">
                            ₹{{ number_format($product->price, 2) }}
                        </div>
                        @if($product->sale_price)
                            <div class="text-muted text-decoration-line-through" style="font-size: 0.75rem;">
                                ₹{{ number_format($product->sale_price, 2) }}
                            </div>
                        @endif
                    @else
                        <div class="product-price-val text-secondary fw-semibold" style="font-size: 0.85rem;">
                            {{ $product->variant ?: 'On Request' }}
                        </div>
                    @endif
                </div>
                
                <button class="btn btn-outline-primary select-btn-main py-2 px-3 rounded-pill fw-bold" 
                        onclick="toggleSelection('{{ $product->id }}', this)" 
                        style="font-size: 0.78rem; border-color: rgba(79, 70, 229, 0.35); color: #4f46e5; transition: all 0.25s;">
                    <i class="bi bi-bag-plus"></i> Select
                </button>
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center py-5 bg-white rounded-4 border animate-fade-in w-100">
        <i class="bi bi-search text-secondary display-1 opacity-25 d-block mb-3"></i>
        <h4 class="fw-bold mt-4 text-dark">No Products Found</h4>
        <p class="text-secondary mx-auto mb-0" style="max-width: 350px;">We couldn't locate any products matching your query or selected filters.</p>
    </div>
@endforelse
