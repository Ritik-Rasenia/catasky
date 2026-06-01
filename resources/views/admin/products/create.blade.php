@extends('admin.layouts.app')

@section('title', 'Add Product — Admin Panel')
@section('page-title', 'Add New Product')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
            <li class="breadcrumb-item active">Add New Product</li>
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
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form" novalidate>
        @csrf
        <div class="row g-4">

            {{-- ══════════ LEFT ══════════ --}}
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
                                       placeholder="e.g. Industrial Grade Patch Panel"
                                       value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SKU <span class="req">*</span></label>
                                <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                                       placeholder="e.g. SKU-001"
                                       value="{{ old('sku') }}" required>
                                @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Part Code</label>
                                <input type="text" name="part_code" class="form-control" placeholder="e.g. PC-001" value="{{ old('part_code') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Part Number</label>
                                <input type="text" name="part_number" class="form-control" placeholder="Manufacturer Part Number" value="{{ old('part_number') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Short Description</label>
                                <textarea name="short_description" class="form-control" rows="2"
                                          placeholder="Brief overview shown in catalogue listings">{{ old('short_description') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Full Description</label>
                                <textarea name="additional_info" class="form-control editor" rows="5">{{ old('additional_info') }}</textarea>
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
                            <div class="col-12">
                                <label class="form-label">Brand</label>
                                <select name="brand_id[]" id="brand-select" class="form-select search-select" multiple>
                                    <option value="new_brand">＋ Add New Brand...</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ is_array(old('brand_id')) && in_array($brand->id, old('brand_id')) ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                <div class="quick-add-row" id="new-brand-container">
                                    <input type="text" id="new-brand-name" class="form-control" placeholder="New brand name">
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
                                        <option value="{{ $category->id }}" {{ is_array(old('category_id')) && in_array($category->id, old('category_id')) ? 'selected' : '' }}>{{ $category->name }}</option>
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
                                        data-attributes-url="{{ url('dashboard/attributes/subcategory') }}" multiple>
                                    <option value="new_subcategory">＋ Add New Subcategory...</option>
                                    @foreach($subcategories as $s)
                                        <option value="{{ $s->id }}" {{ is_array(old('subcategory_id')) && in_array($s->id, old('subcategory_id')) ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <div class="quick-add-row" id="new-subcategory-container">
                                    <input type="text" id="new-subcategory-name" class="form-control" placeholder="New subcategory name">
                                    <button type="button" id="btn-save-new-subcategory" class="btn btn-sm btn-primary px-3">Add</button>
                                    <button type="button" id="btn-cancel-new-subcategory" class="btn btn-sm btn-outline-secondary px-3">✕</button>
                                </div>
                                <div class="text-danger small mt-1 d-none" id="new-subcategory-error"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tags</label>
                                <input type="text" name="tags" class="form-control" placeholder="e.g. industrial, cable, patch" value="{{ old('tags') }}">
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
                                           placeholder="0.00" value="{{ old('mrp') }}" oninput="recalcDiscount()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Offer Price (₹)</label>
                                <div class="price-input-wrap position-relative">
                                    <span class="currency-symbol">₹</span>
                                    <input type="number" step="0.01" min="0" name="offer_price" id="field-offer-price" class="form-control"
                                           placeholder="0.00" value="{{ old('offer_price') }}" oninput="recalcDiscount()">
                                    <span class="discount-badge" id="discount-badge">0% OFF</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">MOQ (Min. Order Qty)</label>
                                <input type="number" min="1" name="moq" class="form-control" placeholder="1" value="{{ old('moq', 1) }}">
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
                    <div class="card-body p-4" id="dynamic-attributes-container"></div>
                </div>

            </div>{{-- /col-xl-8 --}}

            {{-- ══════════ RIGHT SIDEBAR ══════════ --}}
            <div class="col-xl-4 col-lg-5">

                {{-- Publish Product --}}
                <div class="card form-section-card sticky-publish-card border-0 mb-4">
                    <div class="card-header bg-white border-0 rounded-4 px-4 pt-4 pb-0">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="bi bi-rocket-takeoff text-primary fs-5"></i>
                            </div>
                            <div>
                                <p class="card-section-title">Publish Product</p>
                                <p class="card-section-sub">Manage product visibility and status.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-3">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="featured" id="featuredCheck" value="1" {{ old('featured') ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold text-secondary ms-1" for="featuredCheck">Featured Product</label>
                        </div>
                        <button type="submit" class="btn-premium mb-2">
                            <i class="bi bi-check-circle me-2"></i>Publish Product
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-light w-100 border text-secondary fw-semibold" style="border-radius:10px;padding:10px;">
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
                            <div class="upload-dropzone" onclick="document.getElementById('thumbnail-input').click()">
                                <i class="bi bi-cloud-arrow-up text-primary" style="font-size:1.8rem;"></i>
                                <p class="small text-muted mb-0 mt-1">Click to upload main image</p>
                                <input type="file" name="image" class="d-none" accept="image/*" id="thumbnail-input" onchange="previewThumbnail(event)">
                            </div>
                            <div id="thumbnail-preview" class="mt-2 text-center" style="display:none;">
                                <img id="thumb-img" src="" alt="Thumbnail"
                                     style="max-height:110px;border-radius:10px;object-fit:cover;border:1px solid #E2E8F0;" class="p-1 bg-white">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Gallery Images</label>
                            <div class="upload-dropzone" onclick="document.getElementById('gallery-input').click()">
                                <i class="bi bi-plus-circle-dotted text-secondary" style="font-size:1.6rem;"></i>
                                <p class="small text-muted mb-0 mt-1">Upload multiple photos</p>
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
                            <input type="number" name="stock" class="form-control" placeholder="0" value="{{ old('stock', 0) }}">
                        </div>
                        <div>
                            <label class="form-label">Stock Status</label>
                            <select name="stock_status" class="form-select">
                                <option value="in_stock">In Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
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

<script>
    document.querySelectorAll('.editor').forEach(el => {
        ClassicEditor.create(el).catch(e => console.error(e));
    });

    $(document).ready(function() {
        $('.search-select').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Select...' });
    });

    function recalcDiscount() {
        const mrp = parseFloat(document.getElementById('field-mrp').value) || 0;
        const op  = parseFloat(document.getElementById('field-offer-price').value) || 0;
        const badge = document.getElementById('discount-badge');
        if (mrp > 0 && op > 0 && mrp > op) {
            badge.textContent = Math.round(((mrp - op) / mrp) * 100) + '% OFF';
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }

    function previewThumbnail(e) {
        const file = e.target.files[0];
        if (file) {
            document.getElementById('thumb-img').src = URL.createObjectURL(file);
            document.getElementById('thumbnail-preview').style.display = 'block';
        }
    }
    function previewImages(e) {
        const container = document.getElementById('gallery-preview');
        container.innerHTML = '';
        Array.from(e.target.files).forEach(file => {
            const w = document.createElement('div');
            w.style.cssText = 'height:70px;width:70px;';
            w.innerHTML = `<img src="${URL.createObjectURL(file)}" style="height:70px;width:70px;border-radius:8px;object-fit:cover;border:1px solid #E2E8F0;">`;
            container.appendChild(w);
        });
    }

    $(document).ready(function() {
        const categorySelect    = $('#category-select');
        const subcategorySelect = $('#subcategory-select');
        const attrCard  = $('#dynamic-attributes-card');
        const container = $('#dynamic-attributes-container');

        // Brand Quick-Add
        $('#brand-select').on('change', function() {
            const vals = $(this).val() || [];
            if (vals.includes('new_brand')) {
                $('#new-brand-container').addClass('show');
                $('#new-brand-name').focus();
                $(this).val(vals.filter(v => v !== 'new_brand')).trigger('change.select2');
            }
        });
        $('#btn-cancel-new-brand').on('click', () => {
            $('#new-brand-container').removeClass('show');
            $('#new-brand-name').val('');
            $('#new-brand-error').addClass('d-none').text('');
        });
        $('#btn-save-new-brand').on('click', function() {
            const name = $('#new-brand-name').val().trim();
            if (!name) { $('#new-brand-error').removeClass('d-none').text('Brand name is required.'); return; }
            $(this).prop('disabled', true).text('Adding...');
            $.ajax({
                url: '{{ route("admin.brands.quick-store") }}', method: 'POST',
                data: { _token: '{{ csrf_token() }}', name },
                success: r => {
                    if (r.success) {
                        $('#brand-select').append(new Option(r.brand.name, r.brand.id, true, true)).trigger('change.select2');
                        $('#new-brand-container').removeClass('show');
                        $('#new-brand-name').val('');
                        $('#btn-save-new-brand').prop('disabled', false).text('Add');
                    }
                },
                error: xhr => {
                    $('#btn-save-new-brand').prop('disabled', false).text('Add');
                    $('#new-brand-error').removeClass('d-none').text(xhr.responseJSON?.errors?.name?.[0] || 'Failed to add brand.');
                }
            });
        });

        // Category → Subcategories
        categorySelect.on('change', function() {
            const catIds = $(this).val() || [];
            if (catIds.includes('new_category')) {
                $('#new-category-container').addClass('show');
                $('#new-category-name').focus();
                categorySelect.val(catIds.filter(v => v !== 'new_category')).trigger('change.select2');
                return;
            }
            if (!catIds.length) {
                subcategorySelect.html('<option value="new_subcategory">＋ Add New Subcategory...</option>').trigger('change.select2');
                container.empty(); attrCard.addClass('d-none'); return;
            }
            subcategorySelect.html('<option>Loading...</option>').trigger('change.select2');
            $.get('{{ url("dashboard/get-subcategories") }}/' + catIds[0], function(data) {
                let opts = '<option value="new_subcategory">＋ Add New Subcategory...</option>';
                data.forEach(s => { opts += `<option value="${s.id}">${s.name}</option>`; });
                subcategorySelect.html(opts).trigger('change.select2');
            }).fail(function() {
                subcategorySelect.html('<option value="new_subcategory">＋ Add New Subcategory...</option>').trigger('change.select2');
            });
        });

        // Category Quick-Add
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
                url: '{{ route("admin.categories.quick-store") }}',
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

        // Subcategory Change
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

        // Subcategory Quick-Add
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
                url: '{{ route("admin.subcategories.quick-store") }}',
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

        function loadAttributes(subcatIds) {
            const subcatId = Array.isArray(subcatIds) ? subcatIds[0] : subcatIds;
            container.empty();
            if (!subcatId) { attrCard.addClass('d-none'); return; }
            container.html(`<div class="row g-3 placeholder-glow">
                <div class="col-md-6 mb-2"><span class="placeholder col-5 rounded mb-1" style="height:11px;display:block;opacity:.3;"></span><span class="placeholder col-12 rounded" style="height:44px;display:block;background:#f1f5f9;"></span></div>
                <div class="col-md-6 mb-2"><span class="placeholder col-4 rounded mb-1" style="height:11px;display:block;opacity:.3;"></span><span class="placeholder col-12 rounded" style="height:44px;display:block;background:#f1f5f9;"></span></div>
            </div>`);
            attrCard.removeClass('d-none');
            $.get(subcategorySelect.data('attributes-url') + '/' + subcatId, function(groups) {
                container.empty();
                if (!groups.length) { container.html('<div class="text-muted small py-2"><i class="bi bi-info-circle me-1"></i>No attributes mapped for this category.</div>'); return; }
                groups.forEach(group => {
                    let html = `<div class="mb-4"><div class="text-uppercase fw-bold mb-3" style="font-size:.7rem;color:#94A3B8;border-bottom:1.5px solid #F1F5F9;padding-bottom:6px;">${group.group_name}</div><div class="row g-3">`;
                    group.attributes.forEach(attr => { html += buildAttrInput(attr, {}); });
                    html += `</div></div>`;
                    container.append(html);
                });
                container.find('.search-select').select2({ theme: 'bootstrap-5', width: '100%' });
            }).fail(() => container.html('<div class="text-danger small py-2"><i class="bi bi-x-circle me-1"></i>Failed to load attributes.</div>'));
        }

        function buildAttrInput(attr, existingValues) {
            const req = attr.is_required ? '<span style="color:#EF4444;margin-left:2px;">*</span>' : '';
            const unit = attr.unit ? ` <small style="font-weight:400;color:#94A3B8;text-transform:none;">(${attr.unit})</small>` : '';
            const reqAttr = attr.is_required ? 'required' : '';
            const isWide = ['textarea','image','file','rich_text'].includes(attr.type);
            const col = isWide ? 'col-12' : 'col-md-6';
            const rawVal = existingValues[attr.id];
            let inp = '';
            switch(attr.type) {
                case 'text': case 'url':
                    inp = `<input type="${attr.type==='url'?'url':'text'}" name="attributes[${attr.id}]" class="form-control" placeholder="${attr.placeholder||''}" value="${rawVal||attr.default_value||''}" ${reqAttr}>`; break;
                case 'number':
                    inp = `<input type="number" name="attributes[${attr.id}]" class="form-control" placeholder="${attr.placeholder||'0'}" value="${rawVal||attr.default_value||''}" ${reqAttr}>`; break;
                case 'decimal':
                    inp = `<input type="number" name="attributes[${attr.id}]" class="form-control" step="any" placeholder="0.00" value="${rawVal||attr.default_value||''}" ${reqAttr}>`; break;
                case 'date':
                    inp = `<input type="date" name="attributes[${attr.id}]" class="form-control" value="${rawVal||attr.default_value||''}" ${reqAttr}>`; break;
                case 'textarea':
                    inp = `<textarea name="attributes[${attr.id}]" class="form-control" rows="3" placeholder="${attr.placeholder||''}" ${reqAttr}>${rawVal||attr.default_value||''}</textarea>`; break;
                case 'boolean':
                    inp = `<div class="form-check form-switch mt-2"><input type="checkbox" name="attributes[${attr.id}]" value="1" class="form-check-input" id="sw-${attr.id}" style="width:2.5em;height:1.25em;" ${(rawVal=='1'||rawVal=='yes'||(!rawVal&&(attr.default_value=='1'||attr.default_value=='yes')))?'checked':''}><label class="form-check-label small text-secondary ms-2" for="sw-${attr.id}" style="text-transform:none;letter-spacing:0;">Yes</label></div>`; break;
                case 'select':
                    let sopts = `<option value="">-- Select --</option>`;
                    (attr.options||[]).forEach(o => { sopts+=`<option value="${o.value}" ${(rawVal!==undefined&&String(o.value)===String(rawVal))||(rawVal===undefined&&o.is_default)?'selected':''}>${o.label}</option>`; });
                    inp = `<select name="attributes[${attr.id}]" class="form-select" ${reqAttr}>${sopts}</select>`; break;
                case 'multiselect':
                    let mopts='';
                    (attr.options||[]).forEach(o=>{mopts+=`<option value="${o.value}">${o.label}</option>`;});
                    inp=`<select name="attributes[${attr.id}][]" class="form-select search-select" multiple ${reqAttr}>${mopts}</select>`; break;
                case 'checkbox':
                    let copts='<div class="d-flex flex-wrap gap-3 mt-1">';
                    (attr.options||[]).forEach(o=>{copts+=`<label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:#475569;font-size:.875rem;text-transform:none;letter-spacing:0;"><input type="checkbox" name="attributes[${attr.id}][]" value="${o.value}" ${o.is_default?'checked':''} style="accent-color:#4F46E5;width:16px;height:16px;">${o.label}</label>`;});
                    copts+='</div>'; inp=copts; break;
                default:
                    inp=`<input type="text" name="attributes[${attr.id}]" class="form-control" placeholder="${attr.placeholder||''}" value="${rawVal||attr.default_value||''}" ${reqAttr}>`;
            }
            return `<div class="${col}"><div class="mb-2"><label class="form-label">${attr.name}${unit}${req}</label>${inp}</div></div>`;
        }
    });
</script>
@endpush