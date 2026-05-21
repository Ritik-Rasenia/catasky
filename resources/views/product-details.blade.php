@extends('layouts.frontend')

@section('title', ($product->name ?? 'Product Details') . ' - Catasky')

@section('content')
<div class="py-4 border-bottom bg-white">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0 small-text">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary"><i class="bi bi-house-door-fill"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('catalogue') }}" class="text-secondary">Catalogue</a></li>
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
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm p-4 mb-4" role="alert">
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
                    <!-- Main Product Image Frame -->
                    <div class="premium-card bg-white p-3 border-0 rounded-4 shadow-sm mb-3 position-relative overflow-hidden text-center d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">
                        <img id="main-product-image" src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="img-fluid rounded-3" style="max-height: 100%; object-fit: contain; transition: transform 0.3s ease;">
                        
                        @if($product->part_code)
                            <span class="position-absolute top-3 start-3 badge rounded-pill px-3 py-2 small fw-bold bg-dark text-white shadow-sm" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <i class="bi bi-tag-fill me-1 text-primary"></i> {{ $product->part_code }}
                            </span>
                        @endif
                    </div>

                    <!-- Gallery Thumbnails (only if more images are present) -->
                    @if($product->images && $product->images->count() > 0)
                        <div class="d-flex gap-2 overflow-x-auto pb-2 justify-content-center">
                            <!-- Thumbnail 1: Main Image -->
                            <div class="gallery-thumb active rounded-3 border p-1 bg-white cursor-pointer overflow-hidden" onclick="changeMainImage('{{ $product->thumbnail_url }}', this)" style="width: 80px; height: 80px; aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center;">
                                <img src="{{ $product->thumbnail_url }}" style="max-height: 100%; object-fit: contain;">
                            </div>
                            <!-- Loop other gallery images -->
                            @foreach($product->images as $img)
                                @php
                                    $imgUrl = filter_var($img->image, FILTER_VALIDATE_URL) ? $img->image : asset('uploads/products/gallery/' . $img->image);
                                @endphp
                                <div class="gallery-thumb rounded-3 border p-1 bg-white cursor-pointer overflow-hidden" onclick="changeMainImage('{{ $imgUrl }}', this)" style="width: 80px; height: 80px; aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center;">
                                    <img src="{{ $imgUrl }}" style="max-height: 100%; object-fit: contain;">
                                </div>
                            @endforeach
                        </div>
                    @endif
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
                            <div class="fs-3 fw-bold text-primary">{{ $product->variant ?: 'Price on Request' }}</div>
                            @if($product->part_code)
                                <div class="text-secondary small border-start ps-3"><i class="bi bi-boxes"></i> Min. MOQ: 100 units</div>
                            @endif
                        </div>
                    </div>

                    <!-- Product Short Description -->
                    <div class="bg-white p-4 rounded-4 border-0 shadow-sm">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-text-fill text-primary me-2"></i> Short Description</h6>
                        <p class="text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                            {{ $product->short_description ?: 'No description provided. Please submit an inquiry for detailed customized corporate specifications, packaging variants, and branding mockups.' }}
                        </p>
                    </div>

                    <!-- Custom B2B Inquiry Card Form -->
                    <div class="card border-0 rounded-4 shadow-sm overflow-hidden bg-white">
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
                                <input type="hidden" name="brand_id" value="{{ $product->brand_id }}">

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

        <!-- Details & Description Tabs -->
        <div class="row mt-5 pt-3">
            <div class="col-12">
                <div class="premium-card bg-white p-4 p-md-5 border-0 rounded-4 shadow-sm">
                    <ul class="nav nav-tabs border-bottom-2 mb-4" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold px-4 py-2 border-0 bg-transparent text-primary position-relative" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane" type="button" role="tab" aria-controls="details-pane" aria-selected="true" style="transition: all 0.3s; border-bottom: 2px solid transparent;">
                                Description
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-4 py-2 border-0 bg-transparent text-secondary position-relative ms-2" id="spec-tab" data-bs-toggle="tab" data-bs-target="#spec-pane" type="button" role="tab" aria-controls="spec-pane" aria-selected="false" style="transition: all 0.3s; border-bottom: 2px solid transparent;">
                                B2B Packaging & Delivery
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="productTabsContent">
                        <div class="tab-pane fade show active" id="details-pane" role="tabpanel" aria-labelledby="details-tab">
                            <p class="text-secondary mb-0 leading-relaxed" style="font-size: 1rem;">
                                {!! nl2br(e($product->description ?: 'No detailed description available for this B2B corporate gift item. It is built using standard-compliant premium grade materials and supports highly optimized branding options.')) !!}
                            </p>
                        </div>
                        <div class="tab-pane fade" id="spec-pane" role="tabpanel" aria-labelledby="spec-tab">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-box-seam text-primary me-2"></i> Bulk Packaging Specs</h6>
                                    <ul class="text-secondary small ps-3">
                                        <li class="mb-2">Individual high-grade corporate velvet pouch or standard box included.</li>
                                        <li class="mb-2">Secure master box packaging with extra cushioning to prevent delivery breaks.</li>
                                        <li class="mb-2">Supports custom-printed brand cardboard boxes (extra charge).</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-truck text-primary me-2"></i> Shipping & Turnaround</h6>
                                    <ul class="text-secondary small ps-3">
                                        <li class="mb-2">Mockup Creation: 24-48 Hours post design receipt.</li>
                                        <li class="mb-2">Production Timeline: 7-10 Business days under standard MOQs.</li>
                                        <li class="mb-2">Delivery: Secure nationwide bulk freight from central warehouse.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
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
                        <div class="premium-card bg-white p-3 border-0 rounded-4 shadow-sm h-100 d-flex flex-column transition-transform cursor-pointer" onclick="window.location.href='{{ route('product.details', $rel->slug) }}'" style="transition: transform 0.2s, box-shadow 0.2s;">
                            <div class="position-relative overflow-hidden bg-light rounded-3 mb-3 text-center d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">
                                <img src="{{ $rel->thumbnail_url }}" alt="{{ $rel->name }}" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                            </div>
                            <div class="d-flex flex-column flex-grow-1">
                                <h6 class="fw-bold text-dark mb-2 text-truncate">{{ $rel->name }}</h6>
                                <p class="text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.78rem; line-height: 1.4;">
                                    {{ $rel->short_description ?: 'Corporate grade customizable selection.' }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-2">
                                    <div class="small fw-bold text-primary">{{ $rel->variant ?: 'On Request' }}</div>
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
    .nav-tabs .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--primary-gradient);
        border-radius: 3px;
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
</script>
@endsection
