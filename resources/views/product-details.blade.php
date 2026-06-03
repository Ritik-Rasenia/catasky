@extends('layouts.frontend')

@section('title', ($product->name ?? 'Product Details') . ' - Catasky')

@section('content')
<div class="py-4 border-bottom bg-white">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0 small-text">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary"><i class="bi bi-house-door-fill"></i> Home</a></li>
                @if(isset($profile) && $profile->company_slug === 'demo')
                    <li class="breadcrumb-item"><a href="{{ route('demo') }}" class="text-secondary">Demo</a></li>
                @elseif(isset($isSubscriberStore) && $isSubscriberStore && isset($profile))
                    <li class="breadcrumb-item"><a href="{{ route('subscriber_store', $profile->company_slug) }}" class="text-secondary">Catalogue</a></li>
                @else
                    <li class="breadcrumb-item"><a href="{{ route('catalogue') }}" class="text-secondary">Catalogue</a></li>
                @endif
                @if($product->category)
                    <li class="breadcrumb-item"><a href="{{ route('category.products', $product->category->slug) }}" class="text-secondary">{{ $product->category->name }}</a></li>
                @endif
                <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-5 bg-light">
    <div class="container">
        <!-- Alert Success for enquiry logging -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0  p-4 mb-4" role="alert">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-check-lg fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Inquiry Sent Successfully!</h6>
                        <p class="mb-0 small opacity-75">{{ session('success') }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-5">
            <!-- Left Panel: Product Image Gallery -->
            <div class="col-lg-6">
                <div class="position-sticky" style="top: 100px;">
                    <div class="row g-3">
                        <!-- Vertical Thumbnails Column (hidden on mobile, shown beautifully vertically on desktop) -->
                        <div class="col-md-2 order-2 order-md-1">
                            <div class="d-flex flex-row flex-md-column gap-2 overflow-auto pb-2 pb-md-0 thumbnail-vertical-strip" style="max-height: 480px;">
                                <!-- Thumbnail 1: Main Image -->
                                <div class="gallery-thumb active rounded-3 border p-1 bg-white cursor-pointer overflow-hidden position-relative" 
                                     onclick="changeMainImage('{{ $product->thumbnail_url }}', this)" 
                                     onmouseover="changeMainImage('{{ $product->thumbnail_url }}', this)"
                                     style="width: 100%; min-width: 60px; aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                    <img src="{{ $product->thumbnail_url }}" loading="lazy" decoding="async" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                </div>
                                <!-- Loop other gallery images -->
                                @if($product->images && $product->images->count() > 0)
                                    @foreach($product->images as $img)
                                        @php
                                            $imgUrl = filter_var($img->image, FILTER_VALIDATE_URL) ? $img->image : asset('uploads/products/gallery/' . $img->image);
                                        @endphp
                                        <div class="gallery-thumb rounded-3 border p-1 bg-white cursor-pointer overflow-hidden position-relative" 
                                             onclick="changeMainImage('{{ $imgUrl }}', this)" 
                                             onmouseover="changeMainImage('{{ $imgUrl }}', this)"
                                             style="width: 100%; min-width: 60px; aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                            <img src="{{ $imgUrl }}" loading="lazy" decoding="async" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Main Product Image Frame (takes remaining space) -->
                        <div class="col-md-10 order-1 order-md-2">
                            <div class="main-image-zoom-container premium-card bg-white p-3 border-0 rounded-4 mb-3 position-relative overflow-hidden text-center d-flex align-items-center justify-content-center" 
                                 style="aspect-ratio: 1/1;"
                                 onmousemove="zoomImage(event)"
                                 onmouseleave="resetZoom()">
                                <img id="main-product-image" src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="img-fluid rounded-3" style="max-height: 95%; max-width: 95%; object-fit: contain; transform-origin: center center; transition: transform 0.1s ease-out;">
                                
                                @if($product->part_code)
                                    <span class="position-absolute top-3 start-3 badge rounded-pill px-3 py-2 small fw-bold bg-dark text-white " style="font-size: 0.75rem; letter-spacing: 0.5px; z-index: 5;">
                                        <i class="bi bi-tag-fill me-1 text-primary"></i> {{ $product->part_code }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Product Specifications & Inquiry Card -->
            <div class="col-lg-6">
                <div class="d-flex flex-column gap-4">
                    <!-- Brand & Title Block -->
                    <div>
                        @if($product->category)
                            <a href="{{ route('category.products', $product->category->slug) }}" class="text-decoration-none badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-2 fw-semibold" style="font-size: 0.8rem;">
                                {{ $product->category->name }}
                            </a>
                        @endif
                        @if($product->brand)
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2 mb-2 fw-semibold ms-1" style="font-size: 0.8rem;">
                                {{ $product->brand->name }}
                            </span>
                        @endif

                        <h1 class="display-6 fw-bold text-dark mb-2" style="letter-spacing: -1px; font-family: 'Outfit', sans-serif;">{{ $product->name }}</h1>
                        
                        <div class="d-flex align-items-center gap-3 mt-3">
                            <div class="fs-3 fw-bold text-primary">
                                @if($product->price)
                                    &#8377;{{ number_format($product->price, 2) }}
                                @else
                                    {{ $product->variant ?: '' }}
                                @endif
                            </div>
                            @if($product->moq || $product->part_code)
                                <div class="text-secondary small border-start ps-3"><i class="bi bi-boxes"></i> Min. MOQ: {{ $product->moq ?? 100 }} units</div>
                            @endif
                        </div>
                    </div>

                    <!-- Product Short Description -->
                    <div class="bg-white p-4 rounded-4 border-0 ">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-text-fill text-primary me-2"></i> Short Description</h6>
                        <div class="text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                            {!! $product->short_description ?: 'No description provided. Please submit an inquiry for detailed customized corporate specifications, packaging variants, and branding mockups.' !!}
                        </div>
                    </div>

                    <!-- Key Product Information Grid -->
                    <div class="bg-white p-4 rounded-4 border-0">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle-fill text-primary me-2"></i> Product Information</h6>
                        <div class="row g-3">
                            @if($product->brand)
                                <div class="col-sm-6 col-12">
                                    <div class="small text-secondary">Brand</div>
                                    <div class="fw-semibold text-dark">{{ $product->brand->name }}</div>
                                </div>
                            @endif
                            @if($product->category)
                                <div class="col-sm-6 col-12">
                                    <div class="small text-secondary">Category</div>
                                    <div class="fw-semibold text-dark">{{ $product->category->name }}</div>
                                </div>
                            @endif
                            @if($product->subcategory)
                                <div class="col-sm-6 col-12">
                                    <div class="small text-secondary">Subcategory</div>
                                    <div class="fw-semibold text-dark">{{ $product->subcategory->name }}</div>
                                </div>
                            @endif
                            @if($product->sku)
                                <div class="col-sm-6 col-12">
                                    <div class="small text-secondary">SKU / Item Code</div>
                                    <div class="fw-semibold text-dark">{{ $product->sku }}</div>
                                </div>
                            @endif
                            @if($product->part_code)
                                <div class="col-sm-6 col-12">
                                    <div class="small text-secondary">Part Code</div>
                                    <div class="fw-semibold text-dark">{{ $product->part_code }}</div>
                                </div>
                            @endif
                            @if($product->part_number)
                                <div class="col-sm-6 col-12">
                                    <div class="small text-secondary">Part Number</div>
                                    <div class="fw-semibold text-dark">{{ $product->part_number }}</div>
                                </div>
                            @endif
                            @if($product->stock)
                                <div class="col-sm-6 col-12">
                                    <div class="small text-secondary">Stock Quantity</div>
                                    <div class="fw-semibold text-dark">{{ $product->stock }} Units Available</div>
                                </div>
                            @endif
                            @if($product->tax)
                                <div class="col-sm-6 col-12">
                                    <div class="small text-secondary">Tax Detail</div>
                                    <div class="fw-semibold text-dark">{{ $product->tax }}% B2B Tax</div>
                                </div>
                            @endif
                        </div>
                        
                        @if($product->tags)
                            @php
                                $tagsArray = is_string($product->tags) ? explode(',', $product->tags) : (is_array($product->tags) ? $product->tags : []);
                            @endphp
                            @if(!empty($tagsArray))
                                <div class="mt-3 pt-3 border-top">
                                    <div class="small text-secondary mb-2">Keywords / Tags</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($tagsArray as $tag)
                                            @if(trim($tag))
                                                <span class="badge bg-light text-secondary rounded-pill border px-2-5 py-1 small" style="font-size: 0.72rem;">#{{ trim($tag) }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Custom B2B Inquiry Card Form -->
                    <div class="card border-0 rounded-4  overflow-hidden bg-white">
                        <div class="card-header bg-dark p-4 border-0 position-relative text-white overflow-hidden">
                            <div class="position-relative z-index-1">
                                <h5 class="fw-bold mb-1 text-white" style="font-family: 'Outfit', sans-serif;"><i class="bi bi-send-fill text-primary me-2"></i> B2B Corporate Inquiry</h5>
                                <p class="mb-0 small text-white-50">Submit queries directly to our verified dispatch office for corporate bulk discounts.</p>
                            </div>
                            <div class="position-absolute end-0 bottom-0 opacity-10" style="transform: translate(20px, 20px);">
                                <i class="bi bi-send display-1 text-white"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('enquiry.submit') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="brand_id" value="{{ is_array($product->brand_id) ? (collect($product->brand_id)->first() ?? '') : $product->brand_id }}">
                                <input type="hidden" name="is_subscriber_product" value="{{ $product instanceof \App\Models\SubscriberProduct ? '1' : '0' }}">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label small fw-bold text-secondary">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-3 p-2-5 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="John Doe" required>
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label small fw-bold text-secondary">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control rounded-3 p-2-5 @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+91 XXXXX XXXXX" required>
                                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label small fw-bold text-secondary">Business Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control rounded-3 p-2-5 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="corporate@company.com" required>
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="message" class="form-label small fw-bold text-secondary">Inquiry Message <span class="text-danger">*</span></label>
                                        <textarea class="form-control rounded-3 p-2-5 @error('message') is-invalid @enderror" id="message" name="message" rows="4" placeholder="Hello, I want to inquire about custom laser engraving and pricing for 200 units. Please get back to me." required>{{ old('message') }}</textarea>
                                        @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-premium w-100 py-3 rounded-pill fw-bold text-white d-flex align-items-center justify-content-center gap-2" style="background: var(--primary-gradient); border: none; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);">
                                            <i class="bi bi-send-check"></i> Dispatch Corporate Inquiry
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details & Description Block -->
        <div class="row mt-5 pt-3">
            <div class="col-12">
                <div class="premium-card bg-white p-4 p-md-5 border-0 rounded-4">
                    <h4 class="fw-bold text-dark mb-4 brand-font" style="font-family: 'Outfit', sans-serif;"><i class="bi bi-file-text text-primary me-2"></i> Product Information & Description</h4>
                    
                    <div class="text-secondary mb-0 leading-relaxed" style="font-size: 1rem;">
                        <!-- Main Description / Short Description -->
                        <div class="mb-4">
                            @if($product->description)
                                {!! $product->description !!}
                            @else
                                {!! $product->short_description !!}
                            @endif
                        </div>

                        <!-- Additional Information -->
                        @if($product->additional_info)
                            <div class="mt-4 pt-4 border-top">
                                <h5 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;"><i class="bi bi-info-square text-primary me-2"></i> Additional Information</h5>
                                <div class="text-secondary">
                                    {!! $product->additional_info !!}
                                </div>
                            </div>
                        @endif

                        <!-- Packaging Details -->
                        @if($product->packaging)
                            <div class="mt-4 pt-4 border-top">
                                <h5 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;"><i class="bi bi-box-seam text-primary me-2"></i> Packaging & Logistics</h5>
                                <div class="text-secondary">
                                    {!! $product->packaging !!}
                                </div>
                            </div>
                        @endif

                        <!-- Technical Specifications Table -->
                        @php
                            $specs = [];
                            if($product->specifications) {
                                $specs = json_decode($product->specifications, true) ?: [];
                            }
                        @endphp

                        @if(!empty($specs))
                            <div class="mt-4 pt-4 border-top">
                                <h5 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;"><i class="bi bi-info-circle text-primary me-2"></i> Technical Specifications</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0" style="border-radius: 8px; overflow: hidden; font-size: 0.9rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 35%;">Parameter</th>
                                                <th>Details</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($specs as $key => $value)
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
                                                <tr>
                                                    <td class="fw-semibold text-secondary">{{ $key }}</td>
                                                    <td class="text-dark">{{ $value }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        @if($relatedProducts && $relatedProducts->count() > 0)
            <div class="row mt-5 pt-4">
                <div class="col-12 mb-4">
                    <h3 class="fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;">Related Collections</h3>
                    <p class="text-secondary small mb-0">Explore other high-converting corporate gift solutions in the same category.</p>
                </div>
                
                @foreach($relatedProducts as $rel)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                        <div class="premium-card bg-white p-3 border-0 rounded-4  h-100 d-flex flex-column transition-transform cursor-pointer" onclick="window.location.href='{{ route('product.details', $rel->slug) }}'" style="transition: transform 0.2s, box-shadow 0.2s;">
                            <div class="position-relative overflow-hidden bg-light rounded-3 mb-3 text-center d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">
                                <img src="{{ $rel->thumbnail_url }}" alt="{{ $rel->name }}" loading="lazy" decoding="async" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                            </div>
                            <div class="d-flex flex-column flex-grow-1">
                                <h6 class="fw-bold text-dark mb-2 text-truncate">{{ $rel->name }}</h6>
                                <p class="text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.78rem; line-height: 1.4;">
                                    {{ $rel->short_description ?: 'Corporate grade customizable selection.' }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-2">
                                    <div class="small fw-bold text-primary">
                                        @if($rel->price)
                                            &#8377;{{ number_format($rel->price, 2) }}
                                        @else
                                            {{ $rel->variant ?: '' }}
                                        @endif
                                    </div>
                                    <span class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold text-white small" style="background: var(--primary-gradient); border: none; font-size: 0.75rem;">View</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<!-- Page specific CSS and JS -->
<style>
    .gallery-thumb {
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        opacity: 0.6;
    }
    .gallery-thumb:hover, .gallery-thumb.active {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15);
        opacity: 1;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .p-2-5 {
        padding: 0.65rem 0.85rem;
    }
    .main-image-zoom-container {
        position: relative;
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        aspect-ratio: 1/1;
        cursor: zoom-in;
    }
    .main-image-zoom-container img {
        max-height: 95%;
        max-width: 95%;
        object-fit: contain;
        transition: transform 0.1s ease-out;
        transform-origin: center center;
    }
    /* Vertical strip custom scrollbar */
    .thumbnail-vertical-strip::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }
    .thumbnail-vertical-strip::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .thumbnail-vertical-strip::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .thumbnail-vertical-strip::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<script>
    function changeMainImage(url, thumbElement) {
        document.getElementById('main-product-image').src = url;
        
        // Remove active class from all thumbnails
        document.querySelectorAll('.gallery-thumb').forEach(function(el) {
            el.classList.remove('active');
        });
        
        // Add active class to clicked thumbnail
        thumbElement.classList.add('active');
    }

    function zoomImage(e) {
        const container = e.currentTarget;
        const img = container.querySelector('#main-product-image');
        const rect = container.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        
        img.style.transformOrigin = `${x}% ${y}%`;
        img.style.transform = 'scale(2.2)';
    }

    function resetZoom() {
        const img = document.getElementById('main-product-image');
        img.style.transformOrigin = 'center center';
        img.style.transform = 'scale(1)';
    }
</script>
@endsection
