@extends('subscriber-panel.layouts.app')

@section('title', 'Edit Product — Subscriber Panel')
@section('page-title')
    Edit: <span class="text-primary">{{ $product->name }}</span>
@endsection
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('subscriber.products.index') }}">Products</a></li>
            <li class="breadcrumb-item active">Edit Product</li>
        </ol>
    </nav>
@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    .form-section-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: box-shadow 0.2s ease;
    }
    .form-section-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }

    .card-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0F172A;
        margin: 0;
    }
    .card-section-sub {
        font-size: 0.8rem;
        color: #94A3B8;
        margin: 2px 0 0;
    }

    .form-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748B;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: block;
    }
    .form-label .req { color: #EF4444; margin-left: 2px; }

    .form-control, .form-select {
        border: 1.5px solid #E2E8F0;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #1E293B;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: #FAFBFC;
        width: 100%;
    }
    .form-control:focus, .form-select:focus {
        border-color: #4F46E5;
        box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
        background: #fff;
        outline: none;
    }
    .form-control::placeholder { color: #CBD5E1; }

    /* Select2 */
    .select2-container--bootstrap-5 .select2-selection {
        border: 1.5px solid #E2E8F0 !important;
        border-radius: 10px !important;
        background: #FAFBFC !important;
        min-height: 46px !important;
        padding: 4px 8px !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple { padding: 4px 6px !important; }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        border-color: #4F46E5 !important;
        box-shadow: 0 0 0 3px rgba(79,70,229,0.12) !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background: #EEF2FF !important; border: 1px solid #C7D2FE !important;
        color: #4338CA !important; border-radius: 6px !important;
        padding: 2px 8px !important; font-weight: 600 !important; font-size: 0.8rem !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove { color: #6366F1 !important; margin-right: 4px !important; }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover { color: #DC2626 !important; background: transparent !important; }
    .select2-dropdown { border: 1.5px solid #E2E8F0 !important; border-radius: 10px !important; box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
    .select2-container--bootstrap-5 .select2-results__option--highlighted { background: #4F46E5 !important; }

    /* Price */
    .price-input-wrap { position: relative; }
    .price-input-wrap .currency-symbol {
        position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
        font-weight: 700; color: #94A3B8; font-size: 0.9rem; pointer-events: none; z-index: 1;
    }
    .price-input-wrap .form-control { padding-left: 32px; }
    .discount-badge {
        display: none; position: absolute; top: -10px; right: 0;
        background: #10B981; color: #fff; font-size: 0.65rem; font-weight: 700;
        padding: 2px 8px; border-radius: 20px; white-space: nowrap;
    }

    /* Upload */
    .upload-dropzone {
        border: 2px dashed #CBD5E1; background: #F8FAFC; border-radius: 12px;
        padding: 22px 16px; text-align: center; cursor: pointer; transition: all 0.2s ease;
    }
    .upload-dropzone:hover { border-color: #4F46E5; background: rgba(79,70,229,0.02); }

    /* Publish sticky */
    .sticky-publish-card {
        position: sticky; top: 88px; z-index: 10;
        border: 1.5px solid rgba(79,70,229,0.18) !important;
        background: linear-gradient(145deg,#ffffff,#F8FAFC);
    }
    .btn-premium {
        background: linear-gradient(135deg,#4F46E5,#6366F1);
        color: white; font-weight: 700; border: none;
        border-radius: 10px; padding: 12px; transition: all 0.2s ease;
        display: block; width: 100%;
    }
    .btn-premium:hover {
        background: linear-gradient(135deg,#4338CA,#4F46E5);
        transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79,70,229,0.3); color: white;
    }

    /* Quick Add */
    .quick-add-row { display: none; margin-top: 8px; align-items: center; gap: 6px; }
    .quick-add-row.show { display: flex; }
    .quick-add-row .form-control { border-radius: 8px; font-size: 0.85rem; height: 38px; padding: 6px 12px; }
    .quick-add-row .btn { border-radius: 8px; font-size: 0.8rem; white-space: nowrap; height: 38px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <form action="{{ route('subscriber.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="product-form" novalidate>
        @csrf
        @method('PUT')
        <div class="row g-4">

            {{-- ═══ LEFT ═══ --}}
            <div class="col-xl-8 col-lg-7">

                {{-- 1. Basic Information --}}
                <div class="card form-section-card border-0 mb-4">
                    <div class="card-header bg-white border-0 rounded-4 px-4 pt-4 pb-0">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                            </div>
                            <div>
                                <p class="card-section-title">Basic Information</p>
                                <p class="card-section-sub">Product Name and SKU are required. All other fields are optional.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Product Name <span class="req">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       placeholder="e.g. Industrial Grade Patch Panel" value="{{ old('name', $product->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SKU / Part Code <span class="req">*</span></label>
                                <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                                       placeholder="e.g. SKU-001" value="{{ old('sku', $product->sku) }}" required>
                                @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Short Description</label>
                                <textarea name="short_description" class="form-control" rows="2"
                                          placeholder="A brief overview shown in catalogue listings">{{ old('short_description', $product->short_description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Full Description</label>
                                <textarea name="full_description" class="form-control editor" rows="5">{{ old('full_description', $product->full_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Classification --}}
                <div class="card form-section-card border-0 mb-4">
                    <div class="card-header bg-white border-0 rounded-4 px-4 pt-4 pb-0">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="bi bi-diagram-3-fill text-primary fs-5"></i>
                            </div>
                            <div>
                                <p class="card-section-title">Classification</p>
                                <p class="card-section-sub">Assign brand, category and subcategory.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Brand</label>
                                <select name="brand_id[]" id="brand-select" class="form-select search-select" multiple>
                                    <option value="new_brand">＋ Add New Brand...</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ is_array(old('brand_id', $product->brand_id)) && in_array($brand->id, old('brand_id', $product->brand_id ?? [])) ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                <div class="quick-add-row" id="new-brand-container">
                                    <input type="text" id="new-brand-name" class="form-control" placeholder="Enter new brand name">
                                    <button type="button" id="btn-save-new-brand" class="btn btn-sm btn-primary px-3">Add</button>
                                    <button type="button" id="btn-cancel-new-brand" class="btn btn-sm btn-outline-secondary px-3">✕</button>
                                </div>
                                <div class="text-danger small mt-1 d-none" id="new-brand-error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category_id[]" id="category-select" class="form-select search-select" multiple>
                                    <option value="new_category">＋ Add New Category...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ is_array(old('category_id', $product->category_id)) && in_array($category->id, old('category_id', $product->category_id ?? [])) ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="quick-add-row" id="new-category-container">
                                    <input type="text" id="new-category-name" class="form-control" placeholder="New category name">
                                    <button type="button" id="btn-save-new-category" class="btn btn-sm btn-primary px-3">Add</button>
                                    <button type="button" id="btn-cancel-new-category" class="btn btn-sm btn-outline-secondary px-3">✕</button>
                                </div>
                                <div class="text-danger small mt-1 d-none" id="new-category-error"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subcategory</label>
                                <select name="subcategory_id[]" id="subcategory-select" class="form-select search-select"
                                        data-attributes-url="{{ route('subscriber.api.subcategory-attributes', '') }}" multiple>
                                    <option value="new_subcategory">＋ Add New Subcategory...</option>
                                    @foreach($subcategories as $s)
                                        <option value="{{ $s->id }}" {{ is_array(old('subcategory_id', $product->subcategory_id)) && in_array($s->id, old('subcategory_id', $product->subcategory_id ?? [])) ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <div class="quick-add-row" id="new-subcategory-container">
                                    <input type="text" id="new-subcategory-name" class="form-control" placeholder="New subcategory name">
                                    <button type="button" id="btn-save-new-subcategory" class="btn btn-sm btn-primary px-3">Add</button>
                                    <button type="button" id="btn-cancel-new-subcategory" class="btn btn-sm btn-outline-secondary px-3">✕</button>
                                </div>
                                <div class="text-danger small mt-1 d-none" id="new-subcategory-error"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Pricing --}}
                <div class="card form-section-card border-0 mb-4">
                    <div class="card-header bg-white border-0 rounded-4 px-4 pt-4 pb-0">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="bi bi-currency-rupee text-primary fs-5"></i>
                            </div>
                            <div>
                                <p class="card-section-title">Pricing</p>
                                <p class="card-section-sub">Set MRP and Offer Price. Leave blank for "Price on Request".</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">MRP (₹)</label>
                                <div class="price-input-wrap">
                                    <span class="currency-symbol">₹</span>
                                    <input type="number" step="0.01" min="0" name="mrp" id="field-mrp" class="form-control"
                                           placeholder="0.00" value="{{ old('mrp', $product->mrp) }}" oninput="recalcDiscount()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Offer Price (₹)</label>
                                <div class="price-input-wrap position-relative">
                                    <span class="currency-symbol">₹</span>
                                    <input type="number" step="0.01" min="0" name="offer_price" id="field-offer-price" class="form-control"
                                           placeholder="0.00" value="{{ old('offer_price', $product->offer_price) }}" oninput="recalcDiscount()">
                                    <span class="discount-badge" id="discount-badge">0% OFF</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">MOQ</label>
                                <input type="number" min="1" name="moq" class="form-control" placeholder="1" value="{{ old('moq', $product->moq ?? 1) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Dynamic Attributes --}}
                <div class="card form-section-card border-0 mb-4 d-none" id="dynamic-attributes-card">
                    <div class="card-header bg-white border-0 rounded-4 px-4 pt-4 pb-0">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="bi bi-sliders2 text-primary fs-5"></i>
                            </div>
                            <div>
                                <p class="card-section-title">Product Specifications</p>
                                <p class="card-section-sub">Category-specific attributes loaded automatically.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4" id="dynamic-attributes-container">
                        <div class="text-muted small"><i class="bi bi-info-circle me-1"></i>Select a subcategory to load specifications.</div>
                    </div>
                </div>

            </div>{{-- /col-xl-8 --}}

            {{-- ═══ RIGHT: Sidebar ═══ --}}
            <div class="col-xl-4 col-lg-5">

                {{-- Save Changes --}}
                <div class="card form-section-card sticky-publish-card border-0 mb-4">
                    <div class="card-header bg-white border-0 rounded-4 px-4 pt-4 pb-0">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="bi bi-floppy-fill text-primary fs-5"></i>
                            </div>
                            <div>
                                <p class="card-section-title">Save Changes</p>
                                <p class="card-section-sub">Manage product visibility and status.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-3">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="featured" id="featuredCheck" value="1" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold text-secondary ms-1" for="featuredCheck">Featured Product</label>
                        </div>
                        <button type="submit" class="btn btn-premium w-100 mb-2">
                            <i class="bi bi-check-circle me-2"></i>Save Changes
                        </button>
                        <a href="{{ route('subscriber.products.index') }}" class="btn btn-light w-100 border text-secondary fw-semibold">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Product Media --}}
                <div class="card form-section-card border-0 mb-4">
                    <div class="card-header bg-white border-0 rounded-4 px-4 pt-4 pb-0">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="bi bi-images text-primary fs-5"></i>
                            </div>
                            <div>
                                <p class="card-section-title">Product Media</p>
                                <p class="card-section-sub">Upload main thumbnail and gallery images.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label">Main Thumbnail</label>
                            <div class="upload-dropzone mb-2" onclick="document.getElementById('thumbnail-input').click()">
                                <i class="bi bi-cloud-arrow-up text-primary fs-3"></i>
                                <p class="small text-muted mb-0 mt-1">Click to replace thumbnail</p>
                                <input type="file" name="thumbnail" class="d-none" accept="image/*" id="thumbnail-input" onchange="previewThumbnail(event)">
                            </div>
                            <div id="thumbnail-preview" class="text-center mt-2">
                                <input type="hidden" name="remove_thumbnail" id="remove-thumbnail-input" value="0">
                                @if($product->thumbnail)
                                    <div id="thumb-container">
                                        <img id="thumb-img" src="{{ $product->thumbnail_url }}" alt="Thumbnail" style="max-height:110px;border-radius:10px;object-fit:cover;" class="p-1 bg-white border mb-2">
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearThumbnail()">Remove Image</button>
                                        </div>
                                    </div>
                                @else
                                    <div id="thumb-container" style="display:none;">
                                        <img id="thumb-img" src="" alt="Thumbnail" style="max-height:110px;border-radius:10px;object-fit:cover;" class="p-1 bg-white border mb-2">
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearThumbnail()">Remove Image</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="form-label mb-2">Gallery Images</label>
                            @if($product->images && $product->images->count() > 0)
                            <div class="mb-3">
                                <p class="text-muted small mb-2">Existing photos</p>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($product->images as $img)
                                    <div class="position-relative img-wrap-{{ $img->id }}" style="height:70px;width:70px;">
                                        <img src="{{ asset('uploads/subscriber-products/' . $img->image_path) }}" style="height:70px;width:70px;border-radius:8px;object-fit:cover;border:1px solid #E2E8F0;">
                                        <button type="button" class="btn-delete-gallery" data-delete-url="{{ route('subscriber.product-images.destroy', $img->id) }}" onclick="deleteProductImage(this, {{ $img->id }})"
                                                style="position:absolute;top:4px;right:4px;background:rgba(239,68,68,0.9);color:white;border:none;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:0.7rem;cursor:pointer;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            <div class="upload-dropzone" onclick="document.getElementById('gallery-input').click()">
                                <i class="bi bi-plus-circle-dotted text-secondary fs-4"></i>
                                <p class="small text-muted mb-0 mt-1">Upload additional photos</p>
                                <input type="file" name="images[]" class="d-none" accept="image/*" multiple id="gallery-input" onchange="previewImages(event)">
                            </div>
                            <div id="gallery-preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </div>

                {{-- Inventory --}}
                <div class="card form-section-card border-0 mb-4">
                    <div class="card-header bg-white border-0 rounded-4 px-4 pt-4 pb-0">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="bi bi-boxes text-primary fs-5"></i>
                            </div>
                            <div>
                                <p class="card-section-title">Inventory</p>
                                <p class="card-section-sub">Manage product stock level and status.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock" class="form-control" placeholder="0" value="{{ old('stock', $product->stock ?? 0) }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Stock Status</label>
                            <select name="stock_status" class="form-select">
                                <option value="in_stock" {{ old('stock_status', $product->stock_status) === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                <option value="out_of_stock" {{ old('stock_status', $product->stock_status) === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>{{-- /col-xl-4 --}}

        </div>
    </form>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // CKEditor
    document.querySelectorAll('.editor').forEach((el) => {
        ClassicEditor.create(el).catch(error => { console.error(error); });
    });

    // Select2
    $(document).ready(function() {
        $('.search-select').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Select...' });
    });

    // Existing attribute values
    const existingValues = @json($existingValues->mapWithKeys(function($val) {
        return [$val->attribute_id => $val->value];
    }));

    // Discount Calculator
    function recalcDiscount() {
        const mrp = parseFloat(document.getElementById('field-mrp').value) || 0;
        const op = parseFloat(document.getElementById('field-offer-price').value) || 0;
        const badge = document.getElementById('discount-badge');
        if (mrp > 0 && op > 0 && mrp > op) {
            badge.textContent = Math.round(((mrp - op) / mrp) * 100) + '% OFF';
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }
    // Init on page load
    recalcDiscount();

    // Media Previews
    function previewThumbnail(e) {
        const file = e.target.files[0];
        if (file) {
            const img = document.getElementById('thumb-img');
            img.src = URL.createObjectURL(file);
            document.getElementById('thumb-container').style.display = 'block';
            document.getElementById('remove-thumbnail-input').value = '0';
        }
    }

    function clearThumbnail() {
        document.getElementById('thumbnail-input').value = '';
        document.getElementById('thumb-img').src = '';
        document.getElementById('thumb-container').style.display = 'none';
        document.getElementById('remove-thumbnail-input').value = '1';
    }
    let galleryFiles = [];

    function previewImages(e) {
        galleryFiles = galleryFiles.concat(Array.from(e.target.files));
        renderGalleryPreview();
    }

    function removeGalleryFile(index) {
        galleryFiles.splice(index, 1);
        renderGalleryPreview();
    }

    function renderGalleryPreview() {
        const container = document.getElementById('gallery-preview');
        const input = document.getElementById('gallery-input');
        const dt = new DataTransfer();
        container.innerHTML = '';
        galleryFiles.forEach((file, index) => {
            dt.items.add(file);
            const wrapper = document.createElement('div');
            wrapper.style.cssText = 'position:relative;height:70px;width:70px;';
            wrapper.innerHTML = `<img src="${URL.createObjectURL(file)}" style="height:70px;width:70px;border-radius:8px;object-fit:cover;border:1px solid #E2E8F0;">
                <button type="button" onclick="removeGalleryFile(${index})" style="position:absolute;top:4px;right:4px;background:rgba(239,68,68,0.9);color:white;border:none;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:.7rem;cursor:pointer;"><i class="bi bi-x"></i></button>`;
            container.appendChild(wrapper);
        });
        input.files = dt.files;
    }

    // Delete gallery image
    function deleteProductImage(btn, imageId) {
        Swal.fire({
            title: 'Delete this photo?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(btn.getAttribute('data-delete-url'), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        document.querySelector(`.img-wrap-${imageId}`).remove();
                        Swal.fire('Deleted!', 'Image removed successfully.', 'success');
                    }
                });
            }
        });
    }

    $(document).ready(function() {
        const categorySelect = $('#category-select');
        const subcategorySelect = $('#subcategory-select');
        const attrCard = $('#dynamic-attributes-card');
        const container = $('#dynamic-attributes-container');

        // ── Brand Quick-Add ──
        const brandSelect = $('#brand-select');
        brandSelect.on('change', function() {
            let vals = $(this).val() || [];
            if (vals.includes('new_brand')) {
                $('#new-brand-container').addClass('show');
                $('#new-brand-name').focus();
                brandSelect.val(vals.filter(v => v !== 'new_brand')).trigger('change.select2');
            }
        });
        $('#btn-cancel-new-brand').on('click', function() {
            $('#new-brand-container').removeClass('show');
            $('#new-brand-name').val('');
            $('#new-brand-error').addClass('d-none').text('');
        });
        $('#btn-save-new-brand').on('click', function() {
            const name = $('#new-brand-name').val().trim();
            if (!name) { $('#new-brand-error').removeClass('d-none').text('Brand name is required.'); return; }
            $(this).prop('disabled', true).text('Adding...');
            $.ajax({
                url: '{{ route("subscriber.brands.quick-store") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', name },
                success: function(r) {
                    if (r.success) {
                        brandSelect.append(new Option(r.brand.name, r.brand.id, true, true)).trigger('change.select2');
                        $('#new-brand-container').removeClass('show');
                        $('#new-brand-name').val('');
                        $('#btn-save-new-brand').prop('disabled', false).text('Add');
                    }
                },
                error: function(xhr) {
                    $('#btn-save-new-brand').prop('disabled', false).text('Add');
                    $('#new-brand-error').removeClass('d-none').text(xhr.responseJSON?.errors?.name?.[0] || 'Failed.');
                }
            });
        });

        // ── Category Change ──
        categorySelect.on('change', function() {
            let catIds = $(this).val() || [];
            if (catIds.includes('new_category')) {
                $('#new-category-container').addClass('show');
                $('#new-category-name').focus();
                categorySelect.val(catIds.filter(v => v !== 'new_category')).trigger('change.select2');
                return;
            }
            if (!catIds.length) {
                subcategorySelect.html('').trigger('change.select2');
                container.html('<div class="text-muted small"><i class="bi bi-info-circle me-1"></i>Select a subcategory to load specifications.</div>');
                return;
            }
            subcategorySelect.html('<option>Loading...</option>').trigger('change.select2');
            $.get('{{ route("subscriber.get-subcategories") }}', { category_id: catIds }, function(data) {
                let opts = '<option value="new_subcategory">＋ Add New Subcategory...</option>';
                data.forEach(s => { opts += `<option value="${s.id}">${s.name}</option>`; });
                subcategorySelect.html(opts).trigger('change.select2');
            }).fail(function() {
                subcategorySelect.html('<option value="new_subcategory">＋ Add New Subcategory...</option>').trigger('change.select2');
            });
        });

        $('#btn-cancel-new-category').on('click', function() {
            $('#new-category-container').removeClass('show');
            $('#new-category-name').val('');
            $('#new-category-error').addClass('d-none').text('');
        });
        $('#btn-save-new-category').on('click', function() {
            const name = $('#new-category-name').val().trim();
            if (!name) { $('#new-category-error').removeClass('d-none').text('Category name is required.'); return; }
            $(this).prop('disabled', true).text('Adding...');
            $.ajax({
                url: '{{ route("subscriber.categories.quick-store") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', name },
                success: function(r) {
                    if (r.success) {
                        categorySelect.append(new Option(r.category.name, r.category.id, true, true)).trigger('change.select2');
                        $('#new-category-container').removeClass('show');
                        $('#new-category-name').val('');
                        $('#btn-save-new-category').prop('disabled', false).text('Add');
                        categorySelect.trigger('change');
                    }
                },
                error: function(xhr) {
                    $('#btn-save-new-category').prop('disabled', false).text('Add');
                    $('#new-category-error').removeClass('d-none').text(xhr.responseJSON?.errors?.name?.[0] || 'Failed.');
                }
            });
        });

        // ── Subcategory Change ──
        subcategorySelect.on('change', function() {
            let subcatIds = $(this).val() || [];
            if (subcatIds.includes('new_subcategory')) {
                const cats = categorySelect.val() || [];
                if (!cats.length) {
                    alert('Please select a Category first.');
                    subcategorySelect.val(subcatIds.filter(v => v !== 'new_subcategory')).trigger('change.select2');
                    return;
                }
                $('#new-subcategory-container').addClass('show');
                $('#new-subcategory-name').focus();
                subcategorySelect.val(subcatIds.filter(v => v !== 'new_subcategory')).trigger('change.select2');
                return;
            }
            loadAttributes(subcatIds);
        });

        $('#btn-cancel-new-subcategory').on('click', function() {
            $('#new-subcategory-container').removeClass('show');
            $('#new-subcategory-name').val('');
            $('#new-subcategory-error').addClass('d-none').text('');
        });
        $('#btn-save-new-subcategory').on('click', function() {
            const name = $('#new-subcategory-name').val().trim();
            const cats = categorySelect.val() || [];
            if (!name) { $('#new-subcategory-error').removeClass('d-none').text('Subcategory name is required.'); return; }
            if (!cats.length) { $('#new-subcategory-error').removeClass('d-none').text('Select a category first.'); return; }
            $(this).prop('disabled', true).text('Adding...');
            $.ajax({
                url: '{{ route("subscriber.subcategories.quick-store") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', category_id: cats[0], name },
                success: function(r) {
                    if (r.success) {
                        subcategorySelect.append(new Option(r.subcategory.name, r.subcategory.id, true, true)).trigger('change.select2');
                        $('#new-subcategory-container').removeClass('show');
                        $('#new-subcategory-name').val('');
                        $('#btn-save-new-subcategory').prop('disabled', false).text('Add');
                        subcategorySelect.trigger('change');
                    }
                },
                error: function(xhr) {
                    $('#btn-save-new-subcategory').prop('disabled', false).text('Add');
                    $('#new-subcategory-error').removeClass('d-none').text(xhr.responseJSON?.errors?.name?.[0] || 'Failed.');
                }
            });
        });

        // ── Load Attributes ──
        function loadAttributes(subcatIds) {
            let subcatId = Array.isArray(subcatIds) ? subcatIds[0] : subcatIds;
            container.empty();
            if (!subcatId) { attrCard.addClass('d-none'); return; }
            const skeletonHtml = `<div class="row g-3 placeholder-glow">
                <div class="col-md-6"><span class="placeholder col-5 rounded mb-1" style="height:12px;display:block;"></span><span class="placeholder col-12 rounded" style="height:44px;display:block;background:#f1f5f9;"></span></div>
                <div class="col-md-6"><span class="placeholder col-4 rounded mb-1" style="height:12px;display:block;"></span><span class="placeholder col-12 rounded" style="height:44px;display:block;background:#f1f5f9;"></span></div>
            </div>`;
            container.html(skeletonHtml);
            attrCard.removeClass('d-none');
            const attrUrlBase = subcategorySelect.data('attributes-url');
            $.get(attrUrlBase + '/' + subcatId, function(groups) {
                container.empty();
                if (!groups.length) {
                    container.html('<div class="text-secondary small py-2"><i class="bi bi-info-circle me-1"></i>No attributes mapped for this subcategory.</div>');
                    return;
                }
                groups.forEach(group => {
                    let html = `<div class="mb-4"><div style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#94A3B8;border-bottom:2px solid #F1F5F9;padding-bottom:6px;margin-bottom:14px;">${group.group_name}</div><div class="row g-3">`;
                    group.attributes.forEach(attr => { html += buildAttrInput(attr); });
                    html += `</div></div>`;
                    container.append(html);
                });
                container.find('.search-select').select2({ theme: 'bootstrap-5', width: '100%' });
            }).fail(function() {
                container.html('<div class="text-danger small py-2"><i class="bi bi-x-circle me-1"></i>Failed to load attributes.</div>');
            });
        }

        function buildAttrInput(attr) {
            const req = attr.is_required ? '<span style="color:#EF4444">*</span>' : '';
            const unit = attr.unit ? ` <small style="font-weight:400;color:#94A3B8">(${attr.unit})</small>` : '';
            const reqAttr = attr.is_required ? 'required' : '';
            const isWide = ['textarea','image','file','rich_text'].includes(attr.type);
            const col = isWide ? 'col-12' : 'col-md-6';
            let rawVal = existingValues[attr.id];
            let parsedVal = null;
            if (rawVal !== undefined && rawVal !== null) {
                try { parsedVal = JSON.parse(rawVal); } catch(e) { parsedVal = rawVal; }
            }
            let inp = '';
            switch(attr.type) {
                case 'text': case 'url':
                    const tv = rawVal !== undefined ? rawVal : (attr.default_value || '');
                    inp = `<input type="${attr.type==='url'?'url':'text'}" name="attributes[${attr.id}]" class="form-control" placeholder="${attr.placeholder||''}" value="${tv}" ${reqAttr}>`;
                    break;
                case 'number':
                    const nv = rawVal !== undefined ? rawVal : (attr.default_value || '');
                    inp = `<input type="number" name="attributes[${attr.id}]" class="form-control" placeholder="${attr.placeholder||'0'}" value="${nv}" ${reqAttr}>`;
                    break;
                case 'decimal':
                    const dv = rawVal !== undefined ? rawVal : (attr.default_value || '');
                    inp = `<input type="number" name="attributes[${attr.id}]" class="form-control" step="any" placeholder="0.00" value="${dv}" ${reqAttr}>`;
                    break;
                case 'date':
                    const dtv = rawVal !== undefined ? rawVal : (attr.default_value || '');
                    inp = `<input type="date" name="attributes[${attr.id}]" class="form-control" value="${dtv}" ${reqAttr}>`;
                    break;
                case 'textarea':
                    const tav = rawVal !== undefined ? rawVal : (attr.default_value || '');
                    inp = `<textarea name="attributes[${attr.id}]" class="form-control" rows="3" placeholder="${attr.placeholder||''}" ${reqAttr}>${tav}</textarea>`;
                    break;
                case 'boolean':
                    const bc = rawVal=='1'||rawVal=='yes'||(rawVal===undefined&&(attr.default_value=='1'||attr.default_value=='yes'));
                    inp = `<div class="form-check form-switch mt-2"><input type="checkbox" name="attributes[${attr.id}]" value="1" class="form-check-input" id="sw-${attr.id}" style="width:2.5em;height:1.25em;" ${bc?'checked':''}><label class="form-check-label small text-secondary ms-2" for="sw-${attr.id}" style="text-transform:none;letter-spacing:0;">Yes</label></div>`;
                    break;
                case 'select':
                    let sopts = `<option value="">-- Select --</option>`;
                    (attr.options||[]).forEach(o => {
                        const sel = (rawVal!==undefined && String(o.value)===String(rawVal)) || (rawVal===undefined && o.is_default);
                        sopts += `<option value="${o.value}" ${sel?'selected':''}>${o.label}</option>`;
                    });
                    inp = `<select name="attributes[${attr.id}]" class="form-select" ${reqAttr}>${sopts}</select>`;
                    break;
                case 'multiselect':
                    let mopts = '';
                    (attr.options||[]).forEach(o => {
                        const sel = Array.isArray(parsedVal) && parsedVal.map(String).includes(String(o.value));
                        mopts += `<option value="${o.value}" ${sel?'selected':''}>${o.label}</option>`;
                    });
                    inp = `<select name="attributes[${attr.id}][]" class="form-select search-select" multiple ${reqAttr}>${mopts}</select>`;
                    break;
                case 'checkbox':
                    let copts = '<div class="d-flex flex-wrap gap-3 mt-1">';
                    (attr.options||[]).forEach(o => {
                        const chk = Array.isArray(parsedVal) && parsedVal.map(String).includes(String(o.value));
                        copts += `<label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:#475569;font-size:.875rem;text-transform:none;letter-spacing:0;"><input type="checkbox" name="attributes[${attr.id}][]" value="${o.value}" ${chk?'checked':''} style="accent-color:#4F46E5;width:16px;height:16px;">${o.label}</label>`;
                    });
                    copts += '</div>';
                    inp = copts;
                    break;
                default:
                    const fv = rawVal !== undefined ? rawVal : (attr.default_value || '');
                    inp = `<input type="text" name="attributes[${attr.id}]" class="form-control" placeholder="${attr.placeholder||''}" value="${fv}" ${reqAttr}>`;
            }
            return `<div class="${col}"><div class="mb-2"><label class="form-label">${attr.name}${unit}${req}</label>${inp}</div></div>`;
        }

        // On load: trigger attributes if subcategory already selected
        const existingSubcats = subcategorySelect.val() || [];
        if (existingSubcats.length) {
            loadAttributes(existingSubcats);
        }
    });
</script>
@endpush
